<?php

namespace App\Services\Matrimonial;

use App\Enums\MatrimonialStatus;
use App\Enums\MembershipStatus;
use App\Enums\MemberStatus;
use App\Models\AdminUser;
use App\Models\MatrimonialPhoto;
use App\Models\MatrimonialProfile;
use App\Models\MatrimonialReport;
use App\Models\Member;
use App\Models\Membership;
use App\Services\Audit\AuditLogger;
use App\Services\Media\ImageService;
use App\Services\Sms\SmsService;
use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Private matrimonial module (CLAUDE.md §11).
 *
 * - Only active Lifetime Members (verified, not suspended) may create or browse.
 * - Every create / edit goes to admin review (pending). Only "approved"
 *   profiles are visible, and only to eligible members.
 * - Photos and bio-data PDFs live on the private disk; canView() guards
 *   every file request.
 */
class MatrimonialService
{
    public function __construct(
        private readonly ImageService $images,
        private readonly SmsService $sms,
        private readonly AuditLogger $audit,
    ) {}

    public function isEligible(?Member $member): bool
    {
        return $member !== null
            && $member->status === MemberStatus::Verified
            && Membership::where('member_id', $member->id)->where('status', MembershipStatus::Active)->exists();
    }

    /** Who may see a profile (and its photos / bio-data). */
    public function canView(MatrimonialProfile $profile, ?Member $member, ?AdminUser $admin): bool
    {
        if ($admin?->can('matrimonial.view')) {
            return true;
        }
        if ($member && $profile->member_id === $member->id) {
            return true;
        }

        return $profile->status === MatrimonialStatus::Approved && $this->isEligible($member);
    }

    /**
     * Owner creates or edits a profile; it (re-)enters admin review.
     *
     * @param  list<UploadedFile>  $newPhotos
     * @param  list<int>  $removePhotoIds
     */
    public function save(Member $member, ?MatrimonialProfile $profile, array $data, array $newPhotos, array $removePhotoIds, ?UploadedFile $bioData, bool $removeBio): MatrimonialProfile
    {
        if (! $profile && $member->matrimonialProfiles()->count() >= MatrimonialProfile::MAX_PER_MEMBER) {
            throw new DomainException(__('web.matrimonial.limit', ['max' => MatrimonialProfile::MAX_PER_MEMBER]));
        }

        $keep = $profile ? $profile->photos()->whereNotIn('id', $removePhotoIds)->count() : 0;
        $total = $keep + count($newPhotos);
        if ($total < 1) {
            throw ValidationException::withMessages(['photos' => __('web.matrimonial.photo_required')]);
        }
        if ($total > MatrimonialProfile::MAX_PHOTOS) {
            throw ValidationException::withMessages(['photos' => __('web.matrimonial.photo_max', ['max' => MatrimonialProfile::MAX_PHOTOS])]);
        }

        return DB::transaction(function () use ($member, $profile, $data, $newPhotos, $removePhotoIds, $bioData, $removeBio) {
            $profile ??= new MatrimonialProfile;
            $profile->fill($data);
            $profile->member_id = $member->id;
            $profile->status = MatrimonialStatus::Pending;
            $profile->submitted_at = now();
            $profile->admin_note = null;
            $profile->save();

            if ($removePhotoIds) {
                $profile->photos()->whereIn('id', $removePhotoIds)->get()->each(function (MatrimonialPhoto $p) {
                    $this->images->delete($p->image);
                    $p->delete();
                });
            }
            $next = (int) $profile->photos()->max('sort_order');
            foreach ($newPhotos as $file) {
                $photo = new MatrimonialPhoto(['sort_order' => ++$next]);
                $photo->image = $this->images->store($file, 'matrimonial', [320, 800], 4 / 5, 'private');
                $profile->photos()->save($photo);
            }

            if ($bioData) {
                $this->deleteBio($profile);
                $path = $bioData->storeAs('matrimonial-biodata', Str::lower(Str::ulid()).'.pdf', 'private');
                $profile->bio_data = ['disk' => 'private', 'path' => $path, 'size' => $bioData->getSize()];
            } elseif ($removeBio) {
                $this->deleteBio($profile);
                $profile->bio_data = null;
            }
            $profile->save();

            return $profile;
        });
    }

    public function deleteByOwner(MatrimonialProfile $profile): void
    {
        $this->purgeFiles($profile);
        $profile->delete();
    }

    // ---- Admin workflow ----

    public function approve(MatrimonialProfile $profile): void
    {
        $this->guard($profile, [MatrimonialStatus::Pending, MatrimonialStatus::Rejected, MatrimonialStatus::Hidden, MatrimonialStatus::Suspended], 'approve');
        $profile->status = MatrimonialStatus::Approved;
        $profile->admin_note = null;
        $profile->approved_at = now();
        $profile->approved_by = auth('admin')->id();
        $profile->save();
        $this->log('matrimonial.approved', $profile, 'Approved');
        $this->notify($profile, 'matrimonial_approved');
    }

    public function reject(MatrimonialProfile $profile, string $reason): void
    {
        $this->guard($profile, [MatrimonialStatus::Pending, MatrimonialStatus::Approved, MatrimonialStatus::Hidden], 'reject');
        $profile->status = MatrimonialStatus::Rejected;
        $profile->admin_note = $reason;
        $profile->save();
        $this->log('matrimonial.rejected', $profile, "Rejected: {$reason}");
        $this->notify($profile, 'matrimonial_rejected');
    }

    public function hide(MatrimonialProfile $profile): void
    {
        $this->guard($profile, [MatrimonialStatus::Approved], 'hide');
        $profile->status = MatrimonialStatus::Hidden;
        $profile->save();
        $this->log('matrimonial.hidden', $profile, 'Hidden from members');
    }

    public function suspend(MatrimonialProfile $profile, string $reason): void
    {
        $this->guard($profile, [MatrimonialStatus::Pending, MatrimonialStatus::Approved, MatrimonialStatus::Hidden], 'suspend');
        $profile->status = MatrimonialStatus::Suspended;
        $profile->admin_note = $reason;
        $profile->save();
        $this->log('matrimonial.suspended', $profile, "Suspended: {$reason}");
    }

    public function adminUpdate(MatrimonialProfile $profile, array $data): void
    {
        $old = $profile->only(['name', 'dob', 'education', 'occupation', 'about']);
        $profile->fill($data);
        $profile->save();
        $this->audit->record('matrimonial.updated', $profile, "Edited matrimonial profile {$profile->name}", $old, $profile->only(array_keys($old)));
    }

    public function adminDelete(MatrimonialProfile $profile): void
    {
        $this->purgeFiles($profile);
        $profile->delete();
        $this->log('matrimonial.deleted', $profile, 'Deleted');
    }

    // ---- Reports ----

    public function report(MatrimonialProfile $profile, Member $reporter, string $reason, ?string $details): void
    {
        if ($profile->member_id === $reporter->id) {
            throw new DomainException(__('web.matrimonial.report_own'));
        }

        $report = MatrimonialReport::where('profile_id', $profile->id)->where('reporter_id', $reporter->id)->first() ?? new MatrimonialReport;
        $report->profile_id = $profile->id;
        $report->reporter_id = $reporter->id;
        $report->fill(['reason' => $reason, 'details' => $details]);
        $report->status = 'open';
        $report->save();
    }

    public function resolveReport(MatrimonialReport $report): void
    {
        $report->status = 'resolved';
        $report->resolved_by = auth('admin')->id();
        $report->resolved_at = now();
        $report->save();
        $this->audit->record('matrimonial.report_resolved', $report, "Resolved report on {$report->loadMissing('profile')->profile?->name}");
    }

    // ---- Files ----

    public function bioDataResponse(MatrimonialProfile $profile)
    {
        abort_unless($profile->bio_data && Storage::disk('private')->exists($profile->bio_data['path']), 404);

        return response(Storage::disk('private')->get($profile->bio_data['path']), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="biodata-'.Str::slug($profile->name).'.pdf"',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }

    private function deleteBio(MatrimonialProfile $profile): void
    {
        if ($profile->bio_data) {
            Storage::disk('private')->delete($profile->bio_data['path']);
        }
    }

    private function purgeFiles(MatrimonialProfile $profile): void
    {
        $profile->photos()->get()->each(fn (MatrimonialPhoto $p) => $this->images->delete($p->image));
        $profile->photos()->delete();
        $this->deleteBio($profile);
        $profile->bio_data = null;
        $profile->save();
    }

    private function guard(MatrimonialProfile $profile, array $from, string $action): void
    {
        if (! in_array($profile->status, $from, true)) {
            throw new DomainException("Cannot {$action} a profile that is “{$profile->status->label()}”.");
        }
    }

    private function log(string $action, MatrimonialProfile $profile, string $what): void
    {
        // Name + ULID only: no personal details in the audit trail.
        $this->audit->record($action, $profile, "Matrimonial profile {$profile->name} ({$profile->ulid}): {$what}");
    }

    private function notify(MatrimonialProfile $profile, string $template): void
    {
        if ($member = $profile->loadMissing('member')->member) {
            $this->sms->queue($member->mobile, $template, ['name' => $member->firstName(), 'profile' => Str::limit($profile->name, 40)], $member->locale);
        }
    }
}

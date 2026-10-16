<?php

namespace App\Services\Business;

use App\Enums\BusinessStatus;
use App\Models\BusinessProfile;
use App\Models\Member;
use App\Services\Audit\AuditLogger;
use App\Services\Media\ImageService;
use App\Services\Notifications\Notifier;
use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Business directory workflow (CLAUDE.md §10):
 *   member submits → pending → admin approve | reject (reason) | request changes (note)
 *   approved ↔ hidden, featured on/off. Only "approved" is public.
 * A member editing key fields of an approved listing sends it back to
 * pending when the "business_reapproval_on_edit" setting is on.
 */
class BusinessService
{
    public function __construct(
        private readonly ImageService $images,
        private readonly Notifier $notifier,
        private readonly AuditLogger $audit,
    ) {}

    /** Member creates or updates their own listing. Returns true if it (re)entered review. */
    public function saveByMember(Member $member, array $data, ?UploadedFile $logo, bool $removeLogo): array
    {
        $business = $member->business()->first() ?? new BusinessProfile;
        $isNew = ! $business->exists;
        $before = $business->only(BusinessProfile::KEY_FIELDS);

        $business->fill($data);
        $business->member_id = $member->id;
        if ($isNew) {
            $business->slug = $this->uniqueSlug($business->name);
        }
        $logoChanged = $this->applyLogo($business, $logo, $removeLogo);

        $keyChanged = $logoChanged || $before != $business->only(BusinessProfile::KEY_FIELDS);
        $needsReview = $isNew
            || in_array($business->status, [BusinessStatus::Rejected, BusinessStatus::ChangesRequested, BusinessStatus::Draft], true)
            || ($business->status === BusinessStatus::Approved && $keyChanged && setting('business_reapproval_on_edit'));

        if ($needsReview) {
            $business->status = BusinessStatus::Pending;
            $business->submitted_at = now();
            $business->is_featured = false;
        }
        $business->save();

        return [$business, $needsReview];
    }

    public function approve(BusinessProfile $business): void
    {
        $this->guard($business, [BusinessStatus::Pending, BusinessStatus::ChangesRequested, BusinessStatus::Rejected, BusinessStatus::Hidden], 'approve');
        $business->status = BusinessStatus::Approved;
        $business->admin_note = null;
        $business->approved_at = now();
        $business->approved_by = auth('admin')->id();
        $business->save();

        $this->log('business.approved', $business, 'Approved');
        $this->notify($business, 'business_approved');
    }

    public function reject(BusinessProfile $business, string $reason): void
    {
        $this->guard($business, [BusinessStatus::Pending, BusinessStatus::ChangesRequested, BusinessStatus::Approved, BusinessStatus::Hidden], 'reject');
        $business->status = BusinessStatus::Rejected;
        $business->admin_note = $reason;
        $business->is_featured = false;
        $business->save();

        $this->log('business.rejected', $business, "Rejected: {$reason}");
        $this->notify($business, 'business_rejected');
    }

    public function requestChanges(BusinessProfile $business, string $note): void
    {
        $this->guard($business, [BusinessStatus::Pending, BusinessStatus::Approved], 'request changes for');
        $business->status = BusinessStatus::ChangesRequested;
        $business->admin_note = $note;
        $business->is_featured = false;
        $business->save();

        $this->log('business.changes_requested', $business, "Changes requested: {$note}");
        $this->notify($business, 'business_changes_requested');
    }

    public function hide(BusinessProfile $business): void
    {
        $this->guard($business, [BusinessStatus::Approved], 'hide');
        $business->status = BusinessStatus::Hidden;
        $business->is_featured = false;
        $business->save();
        $this->log('business.hidden', $business, 'Hidden from the directory');
    }

    public function toggleFeatured(BusinessProfile $business): void
    {
        $this->guard($business, [BusinessStatus::Approved], 'feature');
        $business->is_featured = ! $business->is_featured;
        $business->save();
        $this->log($business->is_featured ? 'business.featured' : 'business.unfeatured', $business, $business->is_featured ? 'Featured' : 'Unfeatured');
    }

    /** Admin edits the listing directly (status unchanged). */
    public function saveByAdmin(BusinessProfile $business, array $data, ?UploadedFile $logo, bool $removeLogo): void
    {
        $old = $business->only(['name', 'category_id', 'area_id', 'phone', 'website']);
        $business->fill($data);
        $this->applyLogo($business, $logo, $removeLogo);
        $business->save();
        $this->audit->record('business.updated', $business, "Edited listing {$business->name}", $old, $business->only(array_keys($old)));
    }

    public function delete(BusinessProfile $business): void
    {
        $this->images->delete($business->logo);
        $business->logo = null;
        $business->save();
        $business->delete();
        $this->log('business.deleted', $business, 'Deleted');
    }

    private function applyLogo(BusinessProfile $business, ?UploadedFile $logo, bool $remove): bool
    {
        if ($logo) {
            $old = $business->logo;
            $business->logo = $this->images->store($logo, 'businesses', [400, 800], 4 / 3);
            $this->images->delete($old);

            return true;
        }
        if ($remove && $business->logo) {
            $this->images->delete($business->logo);
            $business->logo = null;

            return true;
        }

        return false;
    }

    private function guard(BusinessProfile $business, array $from, string $action): void
    {
        if (! in_array($business->status, $from, true)) {
            throw new DomainException("Cannot {$action} a listing that is “{$business->status->label()}”.");
        }
    }

    private function log(string $action, BusinessProfile $business, string $what): void
    {
        $this->audit->record($action, $business, "{$business->name}: {$what}");
    }

    private function notify(BusinessProfile $business, string $template): void
    {
        $member = $business->member;
        if ($member) {
            $this->notifier->member($member, $template, ['name' => $member->firstName(), 'business' => Str::limit($business->name, 40)]);
        }
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'business';
        $slug = $base;
        $i = 2;
        while (BusinessProfile::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}

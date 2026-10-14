<?php

namespace App\Http\Controllers;

use App\Models\FamilyMember;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Streams photos stored on the private disk (member + family photos) after
 * checking who is asking. There is no public URL for these files.
 *   - a member may see only their own photo and their own family's photos
 *   - an admin with members.view may see any
 */
class PrivateImageController extends Controller
{
    public function memberPhoto(Request $request, Member $member): Response
    {
        $this->authorizeFor($request, $member->id);

        return $this->stream($member->photo);
    }

    public function familyPhoto(Request $request, FamilyMember $family): Response
    {
        $this->authorizeFor($request, $family->member_id);

        return $this->stream($family->photo);
    }

    private function authorizeFor(Request $request, int $memberId): void
    {
        $member = $request->user('member');
        $admin = $request->user('admin');

        abort_unless(($member && $member->id === $memberId) || ($admin && $admin->can('members.view')), 404);
    }

    private function stream(?array $image): Response
    {
        abort_unless($image && ($image['disk'] ?? null) === 'private', 404);

        $disk = Storage::disk('private');
        abort_unless($disk->exists($image['path']), 404);

        return response($disk->get($image['path']), 200, [
            'Content-Type' => 'image/webp',
            'Cache-Control' => 'private, max-age=86400',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Enums\MatrimonialStatus;
use App\Enums\MemberStatus;
use App\Models\Area;
use App\Models\MatrimonialPhoto;
use App\Models\MatrimonialProfile;
use App\Models\Member;
use App\Services\Media\ImageService;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;

/**
 * SAMPLE matrimonial profiles for local development / demos only (is_sample).
 * Photos are plain generated placeholders (coloured tile + initials), never
 * real people. Never run on production.
 */
class SampleMatrimonialSeeder extends Seeder
{
    public function run(ImageService $images): void
    {
        if (MatrimonialProfile::exists() || ! Area::exists()) {
            return;
        }

        $rows = [
            // owner mobile, owner name, relation, name, gender, dob, height cm, education, occupation, area, status, show contact
            ['9876543210', 'Ramesh Khandelwal', 'son', 'Rohit Khandelwal', 'male', '1996-03-22', 175, 'B.Tech', 'Business Owner', 'Chandpole', MatrimonialStatus::Approved, true],
            ['9000000101', 'Dr. Anjali Khandelwal', 'daughter', 'Neha Khandelwal', 'female', '1998-06-14', 160, 'MBA (Finance)', 'Financial Analyst', 'Malviya Nagar', MatrimonialStatus::Approved, true],
            ['9000000102', 'Mahesh Khandelwal', 'son', 'Ankit Khandelwal', 'male', '1994-12-02', 178, 'CA', 'Chartered Accountant', 'Johari Bazaar', MatrimonialStatus::Approved, false],
            ['9000000104', 'Deepak Mittal', 'daughter', 'Pooja Mittal', 'female', '1999-02-27', 157, 'M.Sc. Chemistry', 'Teacher', 'Mansarovar', MatrimonialStatus::Approved, true],
            ['9000000105', 'Suresh Agarwal', 'son', 'Rahul Agarwal', 'male', '1997-08-09', 170, 'B.Com', 'Family Business', 'Vaishali Nagar', MatrimonialStatus::Approved, false],
            ['9000000106', 'Vinod Mittal', 'daughter', 'Shreya Mittal', 'female', '2000-04-18', 162, 'B.Des', 'Interior Designer', 'Tonk Road', MatrimonialStatus::Pending, true],
        ];

        foreach ($rows as $i => [$mobile, $owner, $relation, $name, $gender, $dob, $height, $edu, $occ, $area, $status, $contact]) {
            $member = Member::where('mobile', $mobile)->first() ?? tap(new Member(['name' => $owner, 'mobile' => $mobile, 'locale' => 'en']), function ($m) use ($area) {
                $m->area_id = Area::where('name_en', $area)->value('id') ?? Area::value('id');
                $m->status = MemberStatus::Verified;
                $m->verified_at = now();
                $m->profile_submitted_at = now();
                $m->save();
            });

            $p = new MatrimonialProfile([
                'relation' => $relation, 'name' => $name, 'gender' => $gender, 'dob' => $dob, 'height_cm' => $height,
                'marital_status' => 'unmarried', 'father_name' => $owner, 'gotra' => 'Sample', 'native_place' => 'Jaipur',
                'area_id' => Area::where('name_en', $area)->value('id'), 'education' => $edu, 'occupation' => $occ,
                'about' => 'Sample profile for demonstration. Family-oriented, values Samaj traditions.', 'show_contact' => $contact,
            ]);
            $p->member_id = $member->id;
            $p->status = $status;
            $p->is_sample = true;
            $p->submitted_at = now()->subDays(10 - $i);
            $p->approved_at = $status === MatrimonialStatus::Approved ? now()->subDays(8 - $i) : null;
            $p->save();

            if (function_exists('imagecreatetruecolor')) {
                $photo = new MatrimonialPhoto(['sort_order' => 1]);
                $photo->image = $images->store($this->placeholder($i), 'matrimonial', [320, 800], 4 / 5, 'private');
                $p->photos()->save($photo);
            }
        }
    }

    private function placeholder(int $i): UploadedFile
    {
        $colors = [[30, 58, 110], [169, 121, 31], [86, 63, 128], [32, 102, 90], [140, 60, 50], [60, 80, 100]];
        [$r, $g, $b] = $colors[$i % count($colors)];
        $img = imagecreatetruecolor(800, 1000);
        imagefill($img, 0, 0, imagecolorallocate($img, $r, $g, $b));
        // Neutral silhouette (head + shoulders) so it is obviously a placeholder, not a person.
        $light = imagecolorallocatealpha($img, 255, 255, 255, 80);
        imagefilledellipse($img, 400, 400, 300, 300, $light);
        imagefilledellipse($img, 400, 950, 620, 560, $light);
        $path = tempnam(sys_get_temp_dir(), 'mat').'.png';
        imagepng($img, $path);
        imagedestroy($img);

        return new UploadedFile($path, 'sample.png', 'image/png', null, true);
    }
}

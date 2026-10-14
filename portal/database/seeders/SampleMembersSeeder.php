<?php

namespace Database\Seeders;

use App\Enums\MemberStatus;
use App\Models\Area;
use App\Models\FamilyMember;
use App\Models\Member;
use App\Models\MemberVerification;
use Illuminate\Database\Seeder;

/**
 * SAMPLE members for local development / demos (login with these mobiles and
 * the OTP from storage/logs/otp.log). Never run on production.
 */
class SampleMembersSeeder extends Seeder
{
    public function run(): void
    {
        $areas = Area::orderBy('sort_order')->pluck('id')->values();
        if ($areas->isEmpty()) {
            return;
        }

        $rows = [
            ['9876543210', 'Ramesh Khandelwal', 'Late Shri Mohan Lal Khandelwal', 'male', '1978-04-12', MemberStatus::Verified, 0],
            ['9876543211', 'Sunita Agarwal', 'Shri Vinod Agarwal', 'female', '1985-09-02', MemberStatus::UnderVerification, 2],
            ['9876543212', 'Vikas Mittal', 'Shri Anil Mittal', 'male', '1990-01-20', MemberStatus::UnderVerification, 3],
        ];

        foreach ($rows as [$mobile, $name, $father, $gender, $dob, $status, $area]) {
            if (Member::where('mobile', $mobile)->exists()) {
                continue;
            }
            $m = new Member(['name' => $name, 'father_or_husband_name' => $father, 'mobile' => $mobile, 'gender' => $gender,
                'dob' => $dob, 'area_id' => $areas[$area % $areas->count()], 'address' => 'Jaipur', 'locale' => 'en']);
            $m->status = $status;
            $m->profile_submitted_at = now()->subDays(10);
            $m->consented_at = now()->subDays(10);
            $m->verified_at = $status === MemberStatus::Verified ? now()->subDays(5) : null;
            $m->save();
            MemberVerification::create(['member_id' => $m->id, 'action' => 'submitted']);
        }

        $ramesh = Member::where('mobile', '9876543210')->first();
        if ($ramesh && ! $ramesh->family()->exists()) {
            foreach ([
                ['spouse', 'Smt. Kamla Khandelwal', 'female', '1981-07-15', 'married', 'Homemaker', null],
                ['son', 'Rohit Khandelwal', 'male', '1996-03-22', 'unmarried', 'Business Owner', 'B.Tech'],
                ['daughter', 'Priya Khandelwal', 'female', '2000-11-05', 'unmarried', 'Software Engineer', 'MCA'],
                ['mother', 'Smt. Shanti Devi Khandelwal', 'female', '1952-01-01', 'widowed', null, null],
            ] as $i => [$relation, $name, $gender, $dob, $marital, $occupation, $education]) {
                $p = new FamilyMember(compact('relation', 'name', 'gender', 'dob', 'occupation', 'education') + ['marital_status' => $marital, 'sort_order' => $i + 1, 'lives_with_member' => true]);
                $p->member_id = $ramesh->id;
                $p->save();
            }
        }
    }
}

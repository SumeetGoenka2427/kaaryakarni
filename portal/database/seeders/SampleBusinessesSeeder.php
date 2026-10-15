<?php

namespace Database\Seeders;

use App\Enums\BusinessStatus;
use App\Enums\MemberStatus;
use App\Models\Area;
use App\Models\BusinessCategory;
use App\Models\BusinessProfile;
use App\Models\Member;
use App\Services\Media\ImageService;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * SAMPLE business listings (from the original HTML demo data) for local
 * development / demos only. Flagged is_sample. Never run on production.
 */
class SampleBusinessesSeeder extends Seeder
{
    public function run(ImageService $images): void
    {
        if (BusinessProfile::exists() || ! BusinessCategory::exists() || ! Area::exists()) {
            return;
        }

        $rows = [
            ['9876543210', 'Ramesh Khandelwal', 'Khandelwal Textiles', 'textiles', 'Chandpole', 'Wholesale and retail fabrics in Chandpole market for over 20 years. Suiting, shirting, sarees and dress materials for every occasion, with special rates for Samaj families.', 'Shop 14, Textile Market, Chandpole Bazaar', true, 1, BusinessStatus::Approved],
            ['9000000101', 'Dr. Anjali Khandelwal', 'Dr. Anjali Khandelwal — Dental Clinic', 'healthcare', 'Malviya Nagar', 'General and cosmetic dental clinic offering check-ups, root canal treatment, braces and smile design in a modern, hygienic setting.', 'Plot 22, Sector 3, Malviya Nagar', false, 2, BusinessStatus::Approved],
            ['9000000102', 'Mahesh Khandelwal', 'Khandelwal Jewellers', 'jewellery', 'Johari Bazaar', 'Traditional gold and kundan jewellery, trusted by Samaj families for three generations. Bridal sets, custom designs and certified gold.', 'Shop 22, Johari Bazaar', true, 3, BusinessStatus::Approved],
            ['9000000103', 'Pramod Gupta', 'Jaipur Sweets & Namkeen Bhandar', 'food-catering', 'Chandpole', 'Traditional Rajasthani sweets and namkeen, plus festival and event catering for Samaj functions of every size.', 'Near Chandpole Gate', false, 4, BusinessStatus::Approved],
            ['9000000104', 'Deepak Mittal', 'Bright Future Coaching Institute', 'education', 'Mansarovar', 'Coaching for school, JEE and NEET aspirants, with a special scholarship batch for meritorious Samaj students.', 'Plot 8, Mansarovar', false, 5, BusinessStatus::Approved],
            ['9000000105', 'Suresh Agarwal', 'Agarwal Real Estate Consultants', 'real-estate', 'Vaishali Nagar', 'Residential and commercial property buying, selling and rental consultancy across Jaipur, with transparent documentation support.', 'Office 5, Vaishali Nagar Main Road', false, 6, BusinessStatus::Approved],
            ['9000000106', 'Vinod Mittal', 'Mittal Computers & Electronics', 'electronics', 'Tonk Road', 'Laptops, desktops and accessories with sales and repair services for homes and offices. Annual maintenance contracts available.', 'Shop 3, Tonk Road', false, 1, BusinessStatus::Approved],
            ['9876543212', 'Vikas Mittal', 'Vikas Interiors', 'real-estate', 'Vaishali Nagar', 'Interior design and turnkey home furnishing for apartments and offices, from concept drawings to final handover.', 'Vaishali Nagar', false, 2, BusinessStatus::Pending],
        ];

        foreach ($rows as [$mobile, $owner, $name, $cat, $area, $desc, $addr, $featured, $img, $status]) {
            $member = Member::where('mobile', $mobile)->first() ?? tap(new Member(['name' => $owner, 'mobile' => $mobile, 'locale' => 'en']), function ($m) use ($area) {
                $m->area_id = Area::where('name_en', $area)->value('id') ?? Area::value('id');
                $m->status = MemberStatus::Verified;
                $m->verified_at = now();
                $m->profile_submitted_at = now();
                $m->save();
            });

            $b = new BusinessProfile([
                'name' => $name, 'description' => $desc, 'address' => $addr.', Jaipur', 'phone' => $mobile,
                'category_id' => BusinessCategory::where('slug', $cat)->value('id'),
                'area_id' => Area::where('name_en', $area)->value('id') ?? Area::value('id'),
                'hours' => 'Mon–Sat, 10 AM – 8 PM', 'show_phone' => true, 'show_whatsapp' => false, 'show_email' => false,
            ]);
            $b->slug = Str::slug($name);
            $b->member_id = $member->id;
            $b->status = $status;
            $b->is_featured = $featured;
            $b->is_sample = true;
            $b->submitted_at = now()->subDays(7);
            $b->approved_at = $status === BusinessStatus::Approved ? now()->subDays(5) : null;
            $b->logo = $images->store(new UploadedFile(public_path("images/sample/{$img}.jpg"), "{$img}.jpg", 'image/jpeg', null, true), 'businesses', [400, 800], 4 / 3);
            $b->save();
        }
    }
}

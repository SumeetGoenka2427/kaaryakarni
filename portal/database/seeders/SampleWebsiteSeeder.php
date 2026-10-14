<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\BannerSlide;
use App\Models\CommitteeMember;
use App\Models\ContentBlock;
use App\Services\Media\ImageService;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;

/**
 * SAMPLE data for local development / demos (committee names, events,
 * banner slides, page images). Committee rows are flagged is_sample; replace
 * everything from the admin panel before go-live.
 */
class SampleWebsiteSeeder extends Seeder
{
    public function run(ImageService $images): void
    {
        $photo = fn (string $n) => new UploadedFile(public_path("images/sample/{$n}.jpg"), "{$n}.jpg", 'image/jpeg', null, true);

        if (! BannerSlide::exists()) {
            foreach ([
                [1, 'Become a Lifetime Member of Khandelwal Samaj', 'खंडेलवाल समाज के आजीवन सदस्य बनें', 'One-time registration. No renewals, no recurring fees — membership stays valid for life.', 'एक बार का पंजीकरण। कोई नवीनीकरण नहीं, कोई आवर्ती शुल्क नहीं — सदस्यता जीवनभर मान्य।', 'Explore Membership', 'सदस्यता देखें', '/membership'],
                [3, 'Diwali Utsav — Celebrate Together', 'दीवाली उत्सव — साथ मिलकर मनाएं', 'Community Diwali gathering with cultural programs at Samaj Bhawan.', 'समाज भवन में सांस्कृतिक कार्यक्रमों के साथ सामुदायिक दीवाली मिलन।', 'Contact the Office', 'कार्यालय से संपर्क करें', '/contact'],
                [6, 'Discover Trusted Businesses Within the Samaj', 'समाज के विश्वसनीय व्यवसाय खोजें', 'Browse admin-approved businesses and professionals from the Khandelwal community in Jaipur.', 'जयपुर के खंडेलवाल समुदाय के स्वीकृत व्यवसाय और पेशेवर देखें।', 'About the Samaj', 'समाज के बारे में', '/about'],
            ] as $i => [$img, $ten, $thi, $sen, $shi, $cen, $chi, $url]) {
                $slide = BannerSlide::create([
                    'title_en' => $ten, 'title_hi' => $thi, 'subtitle_en' => $sen, 'subtitle_hi' => $shi,
                    'cta_label_en' => $cen, 'cta_label_hi' => $chi, 'cta_url' => $url, 'sort_order' => $i + 1, 'is_active' => true,
                ]);
                $slide->forceFill(['image' => $images->store($photo((string) $img), 'banners', [640, 1280], 16 / 8)])->save();
            }
        }

        foreach ([['home', 'about', 1], ['about', 'history', 2]] as [$page, $section, $img]) {
            $block = ContentBlock::where('page', $page)->where('section', $section)->first();
            if ($block && ! $block->image) {
                $block->forceFill(['image' => $images->store($photo((string) $img), 'content', [480, 960], 16 / 10)])->save();
            }
        }

        if (! Announcement::exists()) {
            Announcement::create(['type' => 'event', 'sort_order' => 1, 'title_en' => 'Samaj Annual General Meeting', 'title_hi' => 'समाज की वार्षिक आम सभा',
                'body_en' => 'All lifetime members are invited to the AGM at Samaj Bhawan. Details to follow via SMS notification.',
                'body_hi' => 'सभी आजीवन सदस्य समाज भवन में होने वाली वार्षिक आम सभा में आमंत्रित हैं। विवरण एसएमएस द्वारा भेजा जाएगा।']);
            Announcement::create(['type' => 'event', 'sort_order' => 2, 'title_en' => 'Diwali Utsav Celebration', 'title_hi' => 'दीवाली उत्सव समारोह',
                'body_en' => 'Community Diwali gathering with cultural programs, sponsored through the Diwali Utsav Donation fund.',
                'body_hi' => 'दीवाली उत्सव दान कोष के सहयोग से सांस्कृतिक कार्यक्रमों के साथ सामुदायिक दीवाली मिलन।']);
            Announcement::create(['type' => 'notice', 'sort_order' => 3, 'title_en' => 'Business Directory Now Open for Submissions', 'title_hi' => 'व्यवसाय निर्देशिका में सूची जमा करना शुरू',
                'body_en' => 'Members can now submit their business or professional profile for admin approval and public listing.',
                'body_hi' => 'सदस्य अब अपने व्यवसाय या पेशेवर प्रोफ़ाइल को स्वीकृति और सार्वजनिक सूची के लिए जमा कर सकते हैं।']);
        }

        if (! CommitteeMember::exists()) {
            $leaders = [
                ['Shri R. Khandelwal', 'श्री आर. खंडेलवाल', 'President', 'अध्यक्ष'],
                ['Smt. S. Agarwal', 'श्रीमती एस. अग्रवाल', 'General Secretary', 'महासचिव'],
                ['Shri M. Goyal', 'श्री एम. गोयल', 'Treasurer', 'कोषाध्यक्ष'],
                ['Shri V. Mittal', 'श्री वी. मित्तल', 'Joint Secretary', 'संयुक्त सचिव'],
            ];
            foreach ($leaders as $i => [$nen, $nhi, $den, $dhi]) {
                $m = new CommitteeMember(['name_en' => $nen, 'name_hi' => $nhi, 'designation_en' => $den, 'designation_hi' => $dhi, 'tier' => 'leadership', 'sort_order' => $i + 1]);
                $m->is_sample = true;
                $m->save();
            }

            $names = ['Ashok Bansal', 'Kamla Jindal', 'Suresh Singhal', 'Meena Garg', 'Rajesh Tayal', 'Anita Kandoi', 'Pramod Khandelwal', 'Sarita Agarwal',
                'Deepak Mittal', 'Rekha Goyal', 'Naveen Bansal', 'Poonam Singhal', 'Sanjay Jindal', 'Shobha Garg', 'Vinod Tayal', 'Manisha Kandoi',
                'Arun Khandelwal', 'Usha Agarwal', 'Ramesh Mittal', 'Geeta Goyal', 'Mahesh Bansal', 'Kavita Singhal', 'Dinesh Jindal', 'Nirmala Garg'];
            $roles = [['Executive Committee Member', 'कार्यकारिणी सदस्य'], ['Managing Committee Member', 'प्रबंध समिति सदस्य'],
                ["Women's Wing Coordinator", 'महिला प्रकोष्ठ संयोजक'], ['Youth Wing Coordinator', 'युवा प्रकोष्ठ संयोजक'],
                ['Welfare Committee Member', 'कल्याण समिति सदस्य'], ['Event Committee Member', 'कार्यक्रम समिति सदस्य']];
            foreach ($names as $i => $name) {
                [$den, $dhi] = $roles[$i % count($roles)];
                $m = new CommitteeMember(['name_en' => $name, 'designation_en' => $den, 'designation_hi' => $dhi, 'tier' => 'general', 'sort_order' => $i + 1]);
                $m->is_sample = true;
                $m->save();
            }
        }
    }
}

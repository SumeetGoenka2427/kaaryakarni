<?php

namespace Database\Seeders;

use App\Models\BannerPanel;
use App\Models\ContentBlock;
use Illuminate\Database\Seeder;

/**
 * Initial page content (text from the final HTML design, EN + HI).
 * Runs on production too, but only fills a page section that is still empty,
 * so it never overwrites anything admin has edited.
 */
class WebsiteContentSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->blocks() as [$page, $section, $items]) {
            if (ContentBlock::where('page', $page)->where('section', $section)->exists()) {
                continue;
            }
            foreach ($items as $i => $item) {
                ContentBlock::create(array_merge(['page' => $page, 'section' => $section, 'sort_order' => $i + 1, 'is_active' => true], $item));
            }
        }

        BannerPanel::firstOrCreate(['position' => 'left'], ['mode' => 'campaign_progress', 'is_active' => true]);
        BannerPanel::firstOrCreate(['position' => 'right'], ['mode' => 'advertisement', 'is_active' => true,
            'ad_label_en' => 'Advertise with the Samaj', 'ad_label_hi' => 'समाज के साथ विज्ञापन करें', 'ad_url' => '/contact']);
    }

    private function blocks(): array
    {
        return [
            ['home', 'about', [[
                'title_en' => 'A Community Rooted in Trust and Tradition',
                'title_hi' => 'विश्वास और परंपरा में निहित एक समुदाय',
                'body_en' => 'The Khandelwal Samaj, Jaipur has long served as a home for our community — supporting members through education, welfare, business collaboration and shared cultural life. The membership portal brings these services online while keeping the values of trust, service and belonging at the center.',
                'body_hi' => 'खंडेलवाल समाज, जयपुर लंबे समय से हमारे समुदाय का घर रहा है — शिक्षा, कल्याण, व्यावसायिक सहयोग और साझा सांस्कृतिक जीवन के माध्यम से सदस्यों का साथ देते हुए। यह सदस्यता पोर्टल विश्वास, सेवा और अपनत्व के मूल्यों को केंद्र में रखते हुए इन सेवाओं को ऑनलाइन लाता है।',
                'label_en' => 'Read Our Story', 'label_hi' => 'हमारी कहानी पढ़ें', 'link_url' => '/about',
            ]]],
            ['home', 'links', [
                ['title_en' => 'Become a Member', 'title_hi' => 'सदस्य बनें', 'body_en' => 'Register with your mobile number for one-time lifetime membership.', 'body_hi' => 'एक बार की आजीवन सदस्यता के लिए अपने मोबाइल नंबर से पंजीकरण करें।', 'link_url' => '/register'],
                ['title_en' => 'Business Directory', 'title_hi' => 'व्यवसाय निर्देशिका', 'body_en' => 'Browse admin-approved Samaj businesses.', 'body_hi' => 'समाज के स्वीकृत व्यवसाय देखें।', 'link_url' => '/business-directory'],
                ['title_en' => 'Donation Campaigns', 'title_hi' => 'दान अभियान', 'body_en' => 'Support ongoing and permanent Samaj causes.', 'body_hi' => 'समाज के चालू और स्थायी कार्यों में सहयोग करें।', 'link_url' => '/donations'],
                ['title_en' => 'Contact Samaj Office', 'title_hi' => 'समाज कार्यालय से संपर्क', 'body_en' => 'Reach the Samaj Bhawan office in Jaipur.', 'body_hi' => 'जयपुर स्थित समाज भवन कार्यालय से संपर्क करें।', 'link_url' => '/contact'],
            ]],
            ['about', 'history', [[
                'title_en' => 'Our History', 'title_hi' => 'हमारा इतिहास',
                'body_en' => "The Khandelwal Samaj of Jaipur has served the community for over four decades, providing a platform for members to connect, support one another and preserve shared traditions. What began as informal gatherings has grown into a structured organization offering welfare, education support and community services.\n\nThis membership portal continues that legacy by making Samaj services — membership, business networking, donations and community information — accessible online for every member.",
                'body_hi' => "जयपुर के खंडेलवाल समाज ने चार दशकों से अधिक समय तक समुदाय की सेवा की है, सदस्यों को आपस में जुड़ने, एक-दूसरे का सहयोग करने और साझा परंपराओं को संरक्षित रखने का मंच प्रदान करते हुए। अनौपचारिक सभाओं के रूप में शुरू हुआ यह प्रयास आज कल्याण, शिक्षा सहायता और सामुदायिक सेवाएं प्रदान करने वाला एक सुव्यवस्थित संगठन बन चुका है।\n\nयह सदस्यता पोर्टल उसी विरासत को आगे बढ़ाते हुए समाज की सेवाओं — सदस्यता, व्यावसायिक नेटवर्किंग, दान और सामुदायिक जानकारी — को हर सदस्य के लिए ऑनलाइन सुलभ बनाता है।",
            ]]],
            ['about', 'values', [
                ['title_en' => 'Our Mission', 'title_hi' => 'हमारा उद्देश्य', 'body_en' => 'To strengthen the Khandelwal community in Jaipur through membership services, welfare support and trustworthy business collaboration.', 'body_hi' => 'सदस्यता सेवाओं, कल्याण सहायता और विश्वसनीय व्यावसायिक सहयोग के माध्यम से जयपुर में खंडेलवाल समुदाय को सशक्त बनाना।'],
                ['title_en' => 'Our Vision', 'title_hi' => 'हमारी दृष्टि', 'body_en' => 'A connected, self-reliant Samaj where every member has access to community support, opportunity and shared cultural identity.', 'body_hi' => 'एक जुड़ा हुआ, आत्मनिर्भर समाज जहां हर सदस्य को सामुदायिक सहयोग, अवसर और साझा सांस्कृतिक पहचान उपलब्ध हो।'],
                ['title_en' => 'Our Values', 'title_hi' => 'हमारे मूल्य', 'body_en' => 'Trust, service, unity and respect for tradition — guiding every initiative undertaken by the Samaj.', 'body_hi' => 'विश्वास, सेवा, एकता और परंपरा का सम्मान — समाज की हर पहल का मार्गदर्शन करने वाले मूल्य।'],
            ]],
        ];
    }
}

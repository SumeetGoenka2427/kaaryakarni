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
                ['title_en' => 'Membership Details', 'title_hi' => 'सदस्यता विवरण', 'body_en' => 'One-time lifetime membership process and fees.', 'body_hi' => 'एक बार की आजीवन सदस्यता प्रक्रिया और शुल्क।', 'link_url' => '/membership'],
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
            ['membership', 'benefits', [
                ['label_en' => 'Lifetime Validity', 'label_hi' => 'आजीवन वैधता', 'title_en' => 'One-Time Payment', 'title_hi' => 'एक बार का भुगतान', 'body_en' => 'Pay the membership fee once. There is no renewal date, no recurring billing and no membership expiry.', 'body_hi' => 'सदस्यता शुल्क केवल एक बार दें। कोई नवीनीकरण तिथि नहीं, कोई आवर्ती बिलिंग नहीं और सदस्यता कभी समाप्त नहीं होती।'],
                ['label_en' => 'Verified Community', 'label_hi' => 'सत्यापित समुदाय', 'title_en' => 'Admin-Verified Members', 'title_hi' => 'प्रशासन द्वारा सत्यापित सदस्य', 'body_en' => 'Every member profile is reviewed by the Samaj admin team before verification is granted.', 'body_hi' => 'हर सदस्य प्रोफ़ाइल को सत्यापन से पहले समाज प्रशासन टीम द्वारा जांचा जाता है।'],
                ['label_en' => 'Local Focus', 'label_hi' => 'स्थानीय केंद्र', 'title_en' => 'Jaipur Area Coverage', 'title_hi' => 'जयपुर क्षेत्र कवरेज', 'body_en' => 'Select your Jaipur area/locality from the Samaj-managed list during registration.', 'body_hi' => 'पंजीकरण के समय समाज द्वारा प्रबंधित सूची से अपना जयपुर क्षेत्र/मोहल्ला चुनें।'],
            ]],
            ['membership', 'included', [[
                'body_en' => "Lifetime Member status — never expires\nMember profile with verification badge\nAccess to Business Directory submission\nAccess to Matrimonial module (private, approval-based)\nDigital membership receipt (PDF)\nNotifications for Samaj announcements and events",
                'body_hi' => "आजीवन सदस्य का दर्जा — कभी समाप्त नहीं होता\nसत्यापन बैज के साथ सदस्य प्रोफ़ाइल\nव्यवसाय निर्देशिका में सूची जमा करने की सुविधा\nवैवाहिक मॉड्यूल की सुविधा (निजी, स्वीकृति आधारित)\nडिजिटल सदस्यता रसीद (PDF)\nसमाज की घोषणाओं और कार्यक्रमों की सूचनाएं",
            ]]],
            ['membership', 'steps', [
                ['title_en' => 'Verify Mobile Number', 'title_hi' => 'मोबाइल नंबर सत्यापित करें', 'body_en' => 'Register and verify using a one-time OTP sent to your mobile.', 'body_hi' => 'अपने मोबाइल पर भेजे गए ओटीपी से पंजीकरण और सत्यापन करें।'],
                ['title_en' => 'Complete Your Profile', 'title_hi' => 'अपनी प्रोफ़ाइल पूरी करें', 'body_en' => 'Provide personal details and select your Jaipur area/locality.', 'body_hi' => 'व्यक्तिगत विवरण दें और अपना जयपुर क्षेत्र/मोहल्ला चुनें।'],
                ['title_en' => 'Pay Membership Fee', 'title_hi' => 'सदस्यता शुल्क भुगतान करें', 'body_en' => 'Pay online or submit an offline payment for admin verification.', 'body_hi' => 'ऑनलाइन भुगतान करें या ऑफ़लाइन भुगतान का विवरण सत्यापन हेतु जमा करें।'],
                ['title_en' => 'Admin Verification', 'title_hi' => 'प्रशासन द्वारा सत्यापन', 'body_en' => 'Once verified, your status changes to Lifetime Member permanently.', 'body_hi' => 'सत्यापन के बाद आपका दर्जा स्थायी रूप से आजीवन सदस्य हो जाता है।'],
            ]],
            ['membership', 'faqs', [
                ['title_en' => 'Does membership ever need renewal?', 'title_hi' => 'क्या सदस्यता का कभी नवीनीकरण करना होता है?', 'body_en' => 'No. Khandelwal Samaj membership is a one-time lifetime membership. There is no renewal date, no recurring fee and no expiry.', 'body_hi' => 'नहीं। खंडेलवाल समाज की सदस्यता एक बार की आजीवन सदस्यता है। इसमें कोई नवीनीकरण तिथि, कोई आवर्ती शुल्क और कोई समाप्ति नहीं है।'],
                ['title_en' => 'Can I pay the membership fee offline?', 'title_hi' => 'क्या मैं सदस्यता शुल्क ऑफ़लाइन दे सकता/सकती हूं?', 'body_en' => 'Yes. You may submit an offline payment which will be verified by the Samaj admin before your membership is approved.', 'body_hi' => 'हां। आप ऑफ़लाइन भुगतान कर सकते हैं, जिसे आपकी सदस्यता स्वीकृत होने से पहले समाज प्रशासन द्वारा सत्यापित किया जाएगा।'],
                ['title_en' => 'What happens after I submit my application?', 'title_hi' => 'आवेदन जमा करने के बाद क्या होता है?', 'body_en' => 'Your profile enters "Under Verification" status. Once the Samaj admin verifies your details and payment, your status becomes Lifetime Member.', 'body_hi' => 'आपकी प्रोफ़ाइल "सत्यापन प्रक्रिया में" स्थिति में आ जाती है। समाज प्रशासन द्वारा आपके विवरण और भुगतान के सत्यापन के बाद आपका दर्जा आजीवन सदस्य हो जाता है।'],
                ['title_en' => 'Can my membership be suspended?', 'title_hi' => 'क्या मेरी सदस्यता निलंबित की जा सकती है?', 'body_en' => 'An admin may suspend or deactivate a membership only in exceptional circumstances. Under normal use, your Lifetime Member status remains permanent.', 'body_hi' => 'केवल असाधारण परिस्थितियों में ही प्रशासन सदस्यता निलंबित या निष्क्रिय कर सकता है। सामान्य स्थिति में आपका आजीवन सदस्य का दर्जा स्थायी रहता है।'],
            ]],
        ];
    }
}

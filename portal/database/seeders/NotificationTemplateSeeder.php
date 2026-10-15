<?php

namespace Database\Seeders;

use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

/**
 * SMS texts (EN/HI). Real sending in India needs each text registered on DLT;
 * paste the approved DLT / MSG91 template id into dlt_template_id. Existing
 * rows are never overwritten (admin may have edited them).
 */
class NotificationTemplateSeeder extends Seeder
{
    public const TEMPLATES = [
        'otp' => [
            'description' => 'Login / registration OTP',
            'sms_en' => '{otp} is your OTP for the Khandelwal Samaj Jaipur portal. Valid for {minutes} minutes. Do not share it with anyone.',
            'sms_hi' => '{otp} खंडेलवाल समाज जयपुर पोर्टल के लिए आपका ओटीपी है। यह {minutes} मिनट तक मान्य है। इसे किसी से साझा न करें।',
        ],
        'registration_received' => [
            'description' => 'Member registration submitted',
            'sms_en' => 'Dear {name}, your Khandelwal Samaj Jaipur member registration has been received and is under verification.',
            'sms_hi' => 'प्रिय {name}, आपका खंडेलवाल समाज जयपुर सदस्य पंजीकरण प्राप्त हो गया है और सत्यापन प्रक्रिया में है।',
        ],
        'member_verified' => [
            'description' => 'Member profile verified',
            'sms_en' => 'Dear {name}, your Khandelwal Samaj Jaipur member profile has been verified. Thank you.',
            'sms_hi' => 'प्रिय {name}, आपकी खंडेलवाल समाज जयपुर सदस्य प्रोफ़ाइल सत्यापित हो गई है। धन्यवाद।',
        ],
        'member_rejected' => [
            'description' => 'Member profile could not be verified',
            'sms_en' => 'Dear {name}, your Khandelwal Samaj Jaipur member profile could not be verified. Please log in to see the reason or contact the Samaj office.',
            'sms_hi' => 'प्रिय {name}, आपकी खंडेलवाल समाज जयपुर सदस्य प्रोफ़ाइल सत्यापित नहीं हो सकी। कारण जानने के लिए लॉगिन करें या समाज कार्यालय से संपर्क करें।',
        ],
        'member_suspended' => [
            'description' => 'Member account suspended',
            'sms_en' => 'Dear {name}, your Khandelwal Samaj Jaipur member account has been suspended. Please contact the Samaj office.',
            'sms_hi' => 'प्रिय {name}, आपका खंडेलवाल समाज जयपुर सदस्य खाता निलंबित कर दिया गया है। कृपया समाज कार्यालय से संपर्क करें।',
        ],
        'member_reinstated' => [
            'description' => 'Member account reinstated',
            'sms_en' => 'Dear {name}, your Khandelwal Samaj Jaipur member account is active again.',
            'sms_hi' => 'प्रिय {name}, आपका खंडेलवाल समाज जयपुर सदस्य खाता पुनः सक्रिय हो गया है।',
        ],
        'membership_active' => [
            'description' => 'Lifetime membership activated',
            'sms_en' => 'Congratulations {name}! You are now a Lifetime Member of Khandelwal Samaj Jaipur. Membership ID: {membership_no}.',
            'sms_hi' => 'बधाई हो {name}! अब आप खंडेलवाल समाज जयपुर के आजीवन सदस्य हैं। सदस्यता आईडी: {membership_no}।',
        ],
        'payment_received' => [
            'description' => 'Payment received (online or verified offline)',
            'sms_en' => 'Dear {name}, we have received your payment of Rs {amount} for {purpose}. Receipt: {receipt_no}. Thank you - Khandelwal Samaj Jaipur.',
            'sms_hi' => 'प्रिय {name}, {purpose} हेतु आपका Rs {amount} का भुगतान प्राप्त हुआ। रसीद: {receipt_no}। धन्यवाद - खंडेलवाल समाज जयपुर।',
        ],
        'payment_submitted' => [
            'description' => 'Offline payment details submitted',
            'sms_en' => 'Dear {name}, your offline payment details of Rs {amount} have been received and will be verified by the Samaj office.',
            'sms_hi' => 'प्रिय {name}, आपके Rs {amount} के ऑफ़लाइन भुगतान का विवरण प्राप्त हुआ, समाज कार्यालय द्वारा सत्यापन किया जाएगा।',
        ],
        'payment_rejected' => [
            'description' => 'Offline payment could not be verified',
            'sms_en' => 'Dear {name}, your offline payment of Rs {amount} could not be verified. Please log in or contact the Samaj office.',
            'sms_hi' => 'प्रिय {name}, आपके Rs {amount} के ऑफ़लाइन भुगतान का सत्यापन नहीं हो सका। कृपया लॉगिन करें या समाज कार्यालय से संपर्क करें।',
        ],
        'business_approved' => [
            'description' => 'Business listing approved',
            'sms_en' => 'Dear {name}, your business "{business}" is now live in the Khandelwal Samaj Jaipur Business Directory.',
            'sms_hi' => 'प्रिय {name}, आपका व्यवसाय "{business}" अब खंडेलवाल समाज जयपुर व्यवसाय निर्देशिका में प्रकाशित है।',
        ],
        'business_rejected' => [
            'description' => 'Business listing rejected',
            'sms_en' => 'Dear {name}, your business listing "{business}" was not approved. Please log in to see the reason.',
            'sms_hi' => 'प्रिय {name}, आपकी व्यवसाय सूची "{business}" स्वीकृत नहीं हुई। कारण जानने के लिए लॉगिन करें।',
        ],
        'business_changes_requested' => [
            'description' => 'Changes requested on business listing',
            'sms_en' => 'Dear {name}, the Samaj office has requested changes to your business listing "{business}". Please log in to update it.',
            'sms_hi' => 'प्रिय {name}, समाज कार्यालय ने आपकी व्यवसाय सूची "{business}" में बदलाव का अनुरोध किया है। कृपया लॉगिन करके अपडेट करें।',
        ],
    ];

    public function run(): void
    {
        foreach (self::TEMPLATES as $key => $template) {
            NotificationTemplate::firstOrCreate(['key' => $key], $template);
        }
    }
}

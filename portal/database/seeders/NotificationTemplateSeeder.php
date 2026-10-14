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
    ];

    public function run(): void
    {
        foreach (self::TEMPLATES as $key => $template) {
            NotificationTemplate::firstOrCreate(['key' => $key], $template);
        }
    }
}

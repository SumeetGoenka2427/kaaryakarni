<?php

namespace Database\Seeders;

use App\Models\BusinessCategory;
use Illuminate\Database\Seeder;

/** Default directory categories (EN/HI). Editable from Admin → Business Categories. Never overwrites. */
class BusinessCategorySeeder extends Seeder
{
    public const CATEGORIES = [
        ['textiles', 'Textiles & Garments', 'वस्त्र एवं परिधान'],
        ['jewellery', 'Jewellery', 'आभूषण'],
        ['healthcare', 'Healthcare & Doctors', 'स्वास्थ्य एवं चिकित्सक'],
        ['education', 'Education & Coaching', 'शिक्षा एवं कोचिंग'],
        ['real-estate', 'Real Estate & Construction', 'रियल एस्टेट एवं निर्माण'],
        ['food-catering', 'Food, Sweets & Catering', 'खाद्य, मिठाई एवं कैटरिंग'],
        ['electronics', 'Electronics & Computers', 'इलेक्ट्रॉनिक्स एवं कंप्यूटर'],
        ['professional-services', 'CA, Legal & Professional Services', 'सीए, विधि एवं पेशेवर सेवाएं'],
        ['retail-trading', 'Retail & Trading', 'खुदरा एवं व्यापार'],
        ['manufacturing', 'Manufacturing & Industry', 'निर्माण एवं उद्योग'],
        ['travel-events', 'Travel, Events & Hospitality', 'यात्रा, आयोजन एवं आतिथ्य'],
        ['other', 'Other', 'अन्य'],
    ];

    public function run(): void
    {
        if (BusinessCategory::exists()) {
            return;
        }

        foreach (self::CATEGORIES as $i => [$slug, $en, $hi]) {
            BusinessCategory::create(['slug' => $slug, 'name_en' => $en, 'name_hi' => $hi, 'is_active' => true, 'sort_order' => $i + 1]);
        }
    }
}

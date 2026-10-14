<?php

namespace Database\Seeders;

use App\Models\Area;
use Illuminate\Database\Seeder;

/**
 * SAMPLE Jaipur areas (is_sample = true) until the client provides the final
 * list. Replace them from Admin → Jaipur Areas.
 */
class SampleAreasSeeder extends Seeder
{
    public const AREAS = [
        ['Chandpole', 'चांदपोल'], ['Johari Bazaar', 'जौहरी बाज़ार'], ['Malviya Nagar', 'मालवीय नगर'],
        ['Vaishali Nagar', 'वैशाली नगर'], ['C-Scheme', 'सी-स्कीम'], ['Mansarovar', 'मानसरोवर'],
        ['Raja Park', 'राजा पार्क'], ['Bani Park', 'बनी पार्क'], ['Tonk Road', 'टोंक रोड'],
        ['Jagatpura', 'जगतपुरा'], ['Vidhyadhar Nagar', 'विद्याधर नगर'], ['Sanganer', 'सांगानेर'],
        ['Jhotwara', 'झोटवाड़ा'], ['Shastri Nagar', 'शास्त्री नगर'], ['Civil Lines', 'सिविल लाइंस'],
        ['Adarsh Nagar', 'आदर्श नगर'], ['Amer Road', 'आमेर रोड'], ['Durgapura', 'दुर्गापुरा'],
        ['Pratap Nagar', 'प्रताप नगर'], ['Sodala', 'सोडाला'],
    ];

    public function run(): void
    {
        foreach (self::AREAS as $i => [$en, $hi]) {
            Area::firstOrCreate(['name_en' => $en], ['name_hi' => $hi, 'sort_order' => $i + 1, 'is_active' => true, 'is_sample' => true]);
        }
    }
}

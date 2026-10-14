<?php

namespace Database\Seeders;

use App\Models\DonationCampaign;
use Illuminate\Database\Seeder;

/**
 * The permanent "Samaj Donation" campaign. Required on every installation
 * (production included); it can be edited but never deleted or deactivated.
 */
class DonationCampaignSeeder extends Seeder
{
    public function run(): void
    {
        if (DonationCampaign::withTrashed()->where('is_permanent', true)->exists()) {
            return;
        }

        $campaign = new DonationCampaign([
            'slug' => 'samaj-donation',
            'title_en' => 'Samaj Donation',
            'title_hi' => 'समाज दान',
            'description_en' => 'Our permanent, always-open donation fund supporting general Samaj welfare, Samaj Bhawan upkeep and community services in Jaipur.',
            'description_hi' => 'हमारा स्थायी, सदैव खुला दान कोष — समाज के सामान्य कल्याण, समाज भवन के रख-रखाव और जयपुर में सामुदायिक सेवाओं के लिए।',
            'allow_online' => true,
            'allow_offline' => true,
            'sort_order' => 0,
        ]);
        $campaign->is_permanent = true;
        $campaign->is_active = true;
        $campaign->save();
    }
}

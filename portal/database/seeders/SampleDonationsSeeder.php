<?php

namespace Database\Seeders;

use App\Models\DonationCampaign;
use App\Services\Donations\DonationService;
use App\Services\Media\ImageService;
use App\Services\Payments\PaymentService;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;

/**
 * SAMPLE campaigns and donations for local development / demos only.
 * Donations go through the real services (offline submit → verify) so
 * totals, receipts and statuses are genuine. Never run on production.
 */
class SampleDonationsSeeder extends Seeder
{
    public function run(DonationService $donations, PaymentService $payments, ImageService $images): void
    {
        if (DonationCampaign::where('is_permanent', false)->exists()) {
            return;
        }

        $photo = fn (int $n) => new UploadedFile(public_path("images/sample/{$n}.jpg"), "{$n}.jpg", 'image/jpeg', null, true);
        $samples = [
            ['diwali-utsav', 'Diwali Utsav Donation', 'दीवाली उत्सव दान', 'Contribute towards this year\'s community Diwali celebrations, decoration and cultural programmes, or buy an entry pass for the Utsav evening.', 'इस वर्ष के सामुदायिक दीवाली उत्सव, सजावट और सांस्कृतिक कार्यक्रमों में योगदान दें, या उत्सव संध्या के लिए प्रवेश पास खरीदें।', 400000, 3, true],
            ['education-support', 'Education Support Fund', 'शिक्षा सहायता कोष', 'Helping meritorious Samaj students with school and college education expenses.', 'समाज के मेधावी विद्यार्थियों की स्कूल और कॉलेज शिक्षा के खर्च में सहायता।', 300000, 6, false],
            ['medical-assistance', 'Medical Assistance Fund', 'चिकित्सा सहायता कोष', 'Supporting Samaj families facing medical emergencies with financial assistance.', 'चिकित्सा आपात स्थिति का सामना कर रहे समाज परिवारों को आर्थिक सहायता।', 200000, 4, false],
        ];

        foreach ($samples as $i => [$slug, $en, $hi, $den, $dhi, $target, $img, $tickets]) {
            $c = new DonationCampaign(['slug' => $slug, 'title_en' => $en, 'title_hi' => $hi, 'description_en' => $den, 'description_hi' => $dhi,
                'target_amount' => $target, 'allow_online' => true, 'allow_offline' => true, 'sort_order' => $i + 1,
                'has_tickets' => $tickets, 'ticket_label_en' => $tickets ? 'Diwali Utsav Entry Pass' : null, 'ticket_label_hi' => $tickets ? 'दीवाली उत्सव प्रवेश पास' : null, 'ticket_price' => $tickets ? 501 : null]);
            $c->is_active = true;
            $c->save();
            $c->forceFill(['image' => $images->store($photo($img), 'campaigns', [480, 960], 16 / 10)])->save();
        }

        $samaj = DonationCampaign::where('is_permanent', true)->first();
        $gifts = [
            [$samaj, 'Ramesh Khandelwal', '9876543210', 11000, false],
            [$samaj, 'Kamla Jindal', '9000000011', 5100, false],
            [$samaj, 'Anonymous Donor', '9000000012', 21000, true],
            [DonationCampaign::where('slug', 'diwali-utsav')->first(), 'Suresh Singhal', '9000000013', 2100, false],
            [DonationCampaign::where('slug', 'diwali-utsav')->first(), 'Meena Garg', '9000000014', 1100, false],
            [DonationCampaign::where('slug', 'education-support')->first(), 'Deepak Mittal', '9000000015', 15000, false],
        ];

        foreach ($gifts as [$campaign, $name, $mobile, $amount, $anon]) {
            [$donation] = $donations->donate($campaign, ['type' => 'donation', 'amount' => $amount, 'donor_name' => $name, 'donor_mobile' => $mobile, 'is_anonymous' => $anon],
                'offline', null, ['offline_method' => 'upi', 'offline_reference' => 'SAMPLE-'.$mobile, 'offline_paid_on' => now()->toDateString()]);
            $payments->verifyOffline($donation->payment);
        }
    }
}

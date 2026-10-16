<?php

namespace Database\Seeders;

use App\Models\GalleryAlbum;
use App\Services\Gallery\GalleryService;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;

/**
 * SAMPLE gallery albums for local development / demos only (is_sample).
 * Uses the bundled sample photos. Never run on production.
 */
class SampleGallerySeeder extends Seeder
{
    public function run(GalleryService $gallery): void
    {
        if (GalleryAlbum::exists()) {
            return;
        }

        $albums = [
            ['Diwali Milan 2026', 'दीवाली मिलन 2026', 'Families came together at Samaj Bhawan for the annual Diwali Milan.', 'समाज भवन में वार्षिक दीवाली मिलन के लिए परिवार एकत्र हुए।', '2026-10-05', [1, 2, 3, 4, 5, 6]],
            ['Annual Samaj Sammelan', 'वार्षिक समाज सम्मेलन', 'Members, committee and guests at the annual Samaj gathering.', 'वार्षिक समाज सम्मेलन में सदस्य, समिति और अतिथि।', '2026-08-15', [3, 1, 5]],
            ['Holi Utsav', 'होली उत्सव', null, null, '2026-03-14', [2, 6, 4, 1]],
            ['Student Felicitation', 'विद्यार्थी सम्मान समारोह', 'Celebrating meritorious students of the Samaj.', 'समाज के मेधावी विद्यार्थियों का सम्मान।', '2026-06-20', [5, 3]],
        ];

        foreach ($albums as $i => [$en, $hi, $descEn, $descHi, $date, $photos]) {
            $album = $gallery->saveAlbum(new GalleryAlbum, [
                'title_en' => $en, 'title_hi' => $hi, 'description_en' => $descEn, 'description_hi' => $descHi,
                'event_date' => $date, 'sort_order' => $i + 1,
            ], true);
            $album->is_sample = true;
            $album->save();

            $gallery->addImages($album, array_map(
                fn (int $n) => new UploadedFile(public_path("images/sample/{$n}.jpg"), "{$n}.jpg", 'image/jpeg', null, true),
                $photos,
            ));
        }
    }
}

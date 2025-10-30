<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\OrganizerProfile;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ReviewsSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Review::truncate();
        Schema::enableForeignKeyConstraints();

        $events = Event::all();
        $users = User::where('role', User::ROLE_CUSTOMER)->get();

        if ($events->isEmpty() || $users->isEmpty()) {
            return;
        }

        $sampleComments = [
            'Luar biasa! Acara sangat menyenangkan.',
            'Sound system perlu ditingkatkan.',
            'Organisasi sangat baik dan rapi.',
            'Kurang parkiran tapi musiknya keren!',
            'Sangat direkomendasikan, akan datang lagi!',
            'Dekorasi panggung keren sekali!',
            'Antrian masuk agak lama.',
            'Lighting dan ambience luar biasa.',
            'Harga tiket sepadan dengan pengalaman.',
            'MC-nya seru dan profesional.',
        ];

        $usedPairs = [];

        foreach (range(0, 19) as $index) {
            $event = $events[$index % $events->count()];
            $user = $users[$index % $users->count()];
            $key = $event->id . '-' . $user->id;

            if (in_array($key, $usedPairs, true)) {
                continue;
            }

            $usedPairs[] = $key;

            Review::create([
                'eventId' => $event->id,
                'userId' => $user->id,
                'rating' => 3 + ($index % 3),
                'comment' => $sampleComments[$index % count($sampleComments)],
            ]);
        }

        $organizerProfiles = OrganizerProfile::with('events.reviews')->get();

        foreach ($organizerProfiles as $profile) {
            $reviews = $profile->events->flatMap->reviews;
            $profile->update([
                'ratingsAvg' => $reviews->avg('rating') ?? 0,
                'ratingsCount' => $reviews->count(),
            ]);
        }
    }
}

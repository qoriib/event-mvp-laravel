<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\OrganizerProfile;
use App\Models\TicketType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class EventsSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        TicketType::truncate();
        Event::truncate();
        OrganizerProfile::truncate();
        Schema::enableForeignKeyConstraints();

        $organizerUser = User::where('role', User::ROLE_ORGANIZER)->firstOrFail();

        $organizerProfile = OrganizerProfile::create([
            'userId' => $organizerUser->id,
            'displayName' => 'Soundwave Productions',
            'bio' => 'Professional concert and festival management team.',
            'ratingsAvg' => 0,
            'ratingsCount' => 0,
        ]);

        $locations = [
            'Jakarta',
            'Bandung',
            'Yogyakarta',
            'Bali',
            'Surabaya',
            'Medan',
            'Semarang',
            'Makassar',
            'Lampung',
            'Malang',
        ];

        $categories = [
            'Music',
            'Festival',
            'Workshop',
            'Seminar',
            'Charity',
            'Art',
            'Conference',
            'Tech',
            'Culture',
            'Startup',
        ];

        foreach (range(0, 11) as $index) {
            $title = 'Event ' . ($index + 1) . ' - ' . $categories[$index % count($categories)] . ' Fiesta';
            $isPaid = $index % 3 !== 0;

            $event = Event::create([
                'organizerId' => $organizerProfile->id,
                'title' => $title,
                'description' => 'Sebuah acara yang menampilkan berbagai aktivitas menarik dan hiburan live.',
                'category' => $categories[$index % count($categories)],
                'location' => $locations[$index % count($locations)],
                'startAt' => Carbon::create(2025, 6 + ($index % 6), 5, 18, 0, 0),
                'endAt' => Carbon::create(2025, 6 + ($index % 6), 6, 23, 0, 0),
                'isPaid' => $isPaid,
                'capacity' => 2000 + $index * 100,
                'seatsAvailable' => 1500 + $index * 50,
            ]);

            TicketType::create([
                'eventId' => $event->id,
                'name' => 'Regular',
                'priceIDR' => $isPaid ? 150000 : 0,
                'quota' => 1000,
            ]);

            TicketType::create([
                'eventId' => $event->id,
                'name' => 'VIP',
                'priceIDR' => $isPaid ? 300000 : 0,
                'quota' => 500,
            ]);
        }
    }
}

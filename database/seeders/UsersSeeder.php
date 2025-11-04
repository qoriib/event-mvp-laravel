<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class UsersSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        User::truncate();
        Schema::enableForeignKeyConstraints();

        $customers = [
            'Andi',
            'Budi',
            'Citra',
            'Dewi',
            'Eka',
            'Fajar',
            'Gilang',
            'Hana',
            'Indra',
            'Joko',
            'Kiki',
            'Lina',
        ];

        foreach ($customers as $name) {
            User::create([
                'email' => strtolower($name) . '@mail.com',
                'name' => $name,
                'passwordHash' => 'password',
                'role' => User::ROLE_CUSTOMER,
            ]);
        }

        User::create([
            'email' => 'soundwave@eventlink.com',
            'name' => 'Soundwave Organizer',
            'passwordHash' => 'password',
            'role' => User::ROLE_ORGANIZER,
        ]);
    }
}

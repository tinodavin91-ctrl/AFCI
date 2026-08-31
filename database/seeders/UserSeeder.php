<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\LiveDataFetcher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Always ensure admin account exists
        $admin = User::firstOrCreate(
            ['email' => 'creator@afce.com'],
            [
                'name' => 'AFCE Admin',
                'password' => bcrypt('password123'),
                'role' => 'admin',
            ]
        );

        $this->command->info("✔ Admin user ready: {$admin->email}");

        // Fetch live users from RandomUser API
        $fetcher = new LiveDataFetcher();
        $liveUsers = $fetcher->fetchUsers(8);

        if (count($liveUsers) > 0) {
            $this->command->info("⬇ Fetched " . count($liveUsers) . " users from RandomUser API");

            foreach ($liveUsers as $userData) {
                User::firstOrCreate(
                    ['email' => $userData['email']],
                    $userData
                );
            }
        } else {
            $this->command->warn("⚠ RandomUser API unavailable — using fallback users");

            $fallbackUsers = [
                ['name' => 'Amara Okafor', 'email' => 'amara@afce.com', 'password' => bcrypt('password123'), 'role' => 'user'],
                ['name' => 'Kwame Mensah', 'email' => 'kwame@afce.com', 'password' => bcrypt('password123'), 'role' => 'user'],
                ['name' => 'Zuri Ndlovu', 'email' => 'zuri@afce.com', 'password' => bcrypt('password123'), 'role' => 'user'],
                ['name' => 'Tendai Moyo', 'email' => 'tendai@afce.com', 'password' => bcrypt('password123'), 'role' => 'user'],
                ['name' => 'Fatima Diallo', 'email' => 'fatima@afce.com', 'password' => bcrypt('password123'), 'role' => 'user'],
            ];

            foreach ($fallbackUsers as $userData) {
                User::firstOrCreate(['email' => $userData['email']], $userData);
            }
        }

        $this->command->info("✔ Total users: " . User::count());
    }
}

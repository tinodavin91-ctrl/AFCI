<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Order: Users first (so content seeders have users to assign to),
     * then content (tracks, videos, articles).
     *
     * Each seeder fetches live data from third-party APIs when available
     * and falls back to hardcoded seed data if the APIs are unreachable.
     */
    public function run(): void
    {
        $this->command->info('🌐 Starting live-data seeding...');

        $this->call([
            UserSeeder::class,
            TrackSeeder::class,
            VideoSeeder::class,
            ArticleSeeder::class,
        ]);

        $this->command->info('✅ Database seeding complete!');
    }
}

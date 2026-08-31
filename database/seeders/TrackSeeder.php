<?php

namespace Database\Seeders;

use App\Models\Track;
use App\Models\User;
use App\Services\LiveDataFetcher;
use Illuminate\Database\Seeder;

class TrackSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first() ?? User::create([
            'name' => 'Media Creator',
            'email' => 'creator@afce.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        // Try fetching live tracks from Deezer API (free, no key)
        $fetcher = new LiveDataFetcher();
        $liveTracks = $fetcher->fetchTracks(15);

        if (count($liveTracks) > 0) {
            $this->command->info("⬇ Fetched " . count($liveTracks) . " tracks from Deezer API");

            foreach ($liveTracks as $trackData) {
                Track::create(array_merge($trackData, ['user_id' => $user->id]));
            }
        } else {
            $this->command->warn("⚠ Deezer API unavailable — using fallback tracks");

            $fallbackTracks = [
                [
                    'title' => 'Lagos Vibe (Summer Mix)',
                    'artist' => 'Burna Beats & DJ Spin',
                    'album' => 'African Rhythms Vol. 1',
                    'audio_url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3',
                    'cover_art_url' => 'https://images.unsplash.com/photo-1470225620780-dba8ba36b745?w=800',
                    'duration' => 215,
                    'genre' => 'Afrobeats',
                    'plays' => 84200,
                    'status' => 'published',
                    'published_at' => now()->subDays(3),
                ],
                [
                    'title' => 'Midnight Highlife Groove',
                    'artist' => 'Kofi & The Harmony Band',
                    'album' => 'Gold Coast Nights',
                    'audio_url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-2.mp3',
                    'cover_art_url' => 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?w=800',
                    'duration' => 198,
                    'genre' => 'Highlife',
                    'plays' => 42100,
                    'status' => 'published',
                    'published_at' => now()->subDays(6),
                ],
                [
                    'title' => 'Amapiano Heatwave',
                    'artist' => 'Kabayaza Producer',
                    'album' => 'Johannesburg Nights',
                    'audio_url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-3.mp3',
                    'cover_art_url' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?w=800',
                    'duration' => 245,
                    'genre' => 'Amapiano',
                    'plays' => 128900,
                    'status' => 'published',
                    'published_at' => now()->subHours(8),
                ],
                [
                    'title' => 'Acoustic Soul Ballad',
                    'artist' => 'Amina Vance',
                    'album' => 'Unplugged Sessions',
                    'audio_url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-4.mp3',
                    'cover_art_url' => 'https://images.unsplash.com/photo-1459749411175-04bf5292ceea?w=800',
                    'duration' => 260,
                    'genre' => 'Soul',
                    'plays' => 19400,
                    'status' => 'published',
                    'published_at' => now()->subDays(10),
                ],
            ];

            foreach ($fallbackTracks as $trackData) {
                Track::create(array_merge($trackData, ['user_id' => $user->id]));
            }
        }

        $this->command->info("✔ Total tracks seeded: " . Track::count());
    }
}

<?php

namespace Database\Seeders;

use App\Models\Image;
use App\Models\User;
// 
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Get all files from images directory
        $allFiles = \Illuminate\Support\Facades\Storage::disk('public')->files('images');
        
        // 2. Separate source images from previously generated ones (gen_)
        $sourceImages = array_filter($allFiles, function($file) {
            $basename = basename($file);
            return !str_starts_with($basename, 'gen_') && 
                   preg_match('/\.(jpg|jpeg|png|gif)$/i', $basename);
        });

        // 3. Delete old generated images to start fresh
        $genImages = array_filter($allFiles, fn($file) => str_starts_with(basename($file), 'gen_'));
        \Illuminate\Support\Facades\Storage::disk('public')->delete($genImages);

        // 4. Create 30 users using source images randomly
        if (empty($sourceImages)) {
            // Fallback: create 30 generic users
            User::factory(30)->has(Image::factory(), 'image')->create();
            return;
        }

        // Reset array keys to ensure array_rand works consistently
        $sourceImages = array_values($sourceImages);
        $count = count($sourceImages);

        for ($i = 0; $i < 30; $i++) {
            // Pick a random image from sources to ensure we have 30 users even if fewer source images exist
            // Use modulo to cycle through or array_rand/shuffle for randomness. 
            // array_rand is better for randomness.
            $randomImage = $sourceImages[array_rand($sourceImages)];
            
            User::factory()
                ->has(Image::factory()->fromFile($randomImage), 'image')
                ->create();
        }
        
        // Create Test User (optional, using a random image or a new one if available)
        // Check if we didn't use all source images? No, we used all. 
        // Just create test user with a random one re-used or a generic one.
        // Let's create test user separately without image or reusing one, 
        // but user requested "justos" (exact). 
        // If "exact" means ONLY users with photos, maybe we skip the extra test user?
        // But the test user is usually needed for login. 
        // Let's CREATE the test user separately, reusing one random image logic (will create another gen_ file).
        
        // Create Test User (optional, using a random image or a new one if available)
        if (!User::where('email', 'test@example.com')->exists()) {
            User::factory()
                ->has(Image::factory(), 'image')
                ->create([
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'phone' => '1234567890',
                ]);
        }
    }
}
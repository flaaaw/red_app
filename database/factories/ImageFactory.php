<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Image>
 */
class ImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $files = \Illuminate\Support\Facades\Storage::disk('public')->files('images');
        
        // Filter out generated files preventing recursion/duplication
        $sourceFiles = array_filter($files, fn($f) => !str_starts_with(basename($f), 'gen_'));
        
        $randomFile = !empty($sourceFiles) ? $sourceFiles[array_rand($sourceFiles)] : null;
        
        if ($randomFile) {
            return $this->processImage($randomFile);
        }

        return [
            'url' => fake()->imageUrl(640, 480, 'people', true),
        ];
    }

    /**
     * State to use a specific source file.
     */
    public function fromFile(string $filePath): static
    {
        return $this->state(fn (array $attributes) => $this->processImage($filePath));
    }

    /**
     * Helper to process an image file exactly like the app does.
     */
    private function processImage(string $sourcePath): array
    {
        $fullPath = \Illuminate\Support\Facades\Storage::disk('public')->path($sourcePath);
        
        try {
            // Mimic app compression logic
            $manager = app(\Intervention\Image\ImageManager::class);
            $image = $manager->read($fullPath);
            
            // Resize aggressively to 200px max
            if ($image->width() > 200 || $image->height() > 200) {
                $image->scaleDown(width: 200, height: 200);
            }
            
            // Compress heavily with 50% quality and add prefix
            $filename = 'gen_' . uniqid() . '.jpg';
            $relPath = 'images/' . $filename;
            $savePath = \Illuminate\Support\Facades\Storage::disk('public')->path($relPath);
            
            $image->toJpeg(50)->save($savePath);
            
            return ['url' => 'storage/' . $relPath];
            
        } catch (\Exception $e) {
            // Fallback
            return ['url' => 'storage/' . $sourcePath];
        }
    }
}

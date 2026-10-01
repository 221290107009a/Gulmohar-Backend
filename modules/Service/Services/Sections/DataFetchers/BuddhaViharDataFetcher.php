<?php

namespace Modules\Service\Services\Sections\DataFetchers;

use Modules\BuddhaVihar\Entities\BuddhaVihar;

/**
 * Buddha Vihar Data Fetcher
 * 
 * Handles fetching Buddha Vihar data for featured destinations section
 */
class BuddhaViharDataFetcher
{
    /**
     * Fetch featured Buddha Vihars for home screen
     * 
     * @return array
     */
    public static function fetch(): array
    {
        try {
            $vihars = BuddhaVihar::where('is_active', 1)
                ->inRandomOrder()
                ->limit(10)
                ->get();
            
            $destinations = [];
            foreach ($vihars as $vihar) {
                $imageUrl = $vihar->logo->path ?? self::getRandomFallbackImage();
                $destination = [
                    'id' => $vihar->id,
                    'image_url' => $imageUrl,
                    'mainImage' => $imageUrl, // Added for compatibility
                    'title' => $vihar->name ?? '',
                    'name' => $vihar->name ?? '', // Added for compatibility
                    'category' => 'Buddha Vihar',
                    'description' => $vihar->address ?? $vihar->city ?? 'Peaceful Buddha Vihar',
                    'latitude' => $vihar->latitude,
                    'longitude' => $vihar->longitude,
                    'city' => $vihar->city,
                    'state' => $vihar->state,
                    'service_type' => 'buddha-vihar',
                    'is_saved' => false,
                    'vihar' => $vihar
                ];
                $destinations[] = $destination;
            }
            
            $titleData = self::generateDynamicTitle();
            
            return [
                'destinations' => $destinations,
                'title' => $titleData['title'],
                'subtitle' => $titleData['subtitle']
            ];
        } catch (\Exception $e) {
            return [
                'destinations' => [],
                'title' => 'Buddha Vihars',
                'subtitle' => 'Peaceful places to visit'
            ];
        }
    }

    /**
     * Generate dynamic title for Buddha Vihar section
     *
     * @return array
     */
    private static function generateDynamicTitle(): array
    {
        $titles = [
            ['title' => '🕉️ Peaceful Buddha Vihars', 'subtitle' => 'Shanti aur sukoon ki talash mein'],
            ['title' => '🏠 Nearby Buddha Vihars', 'subtitle' => 'Apne aas paas ke Viharon ko jaaniye'],
            ['title' => '✨ Sacred Places to Visit', 'subtitle' => 'Pavitra sthanon ki yatra karein'],
            ['title' => '☸️ Explore Buddha Vihars', 'subtitle' => 'Dhamma ki raah par chaliye'],
        ];

        return $titles[array_rand($titles)];
    }

    /**
     * Get a random fallback image URL
     *
     * @return string
     */
    private static function getRandomFallbackImage(): string
    {
        $fallbackImages = [
            'https://sangho.app/storage/media/VXNaKaDqTzyvRgfcNC0r94M9EXnUo5W6H4FI8mBD.jpg',
            'https://sangho.app/storage/media/hy5JnPCHbyLASsv7jFJV2moNcmbqXWTZfsTCEizA.jpg',
            'https://sangho.app/storage/media/0bYvmU6rSxUHbuESXZVSbECCtmCautpOKpKTtpAq.jpg',
        ];

        return $fallbackImages[array_rand($fallbackImages)];
    }
}

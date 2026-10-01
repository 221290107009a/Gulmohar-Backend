<?php

namespace Modules\Service\Services\Sections\DataFetchers;

use Modules\TouristPlace\Entities\TouristPlace;

/**
 * Tourist Places Data Fetcher
 * 
 * Handles fetching tourist place data for featured destinations section
 */
class TouristPlacesDataFetcher
{
    /**
     * Fetch featured tourist places for home screen
     * 
     * @return array
     */
    public static function fetch(): array
    {
        try {
            $touristPlaces = TouristPlace::where('is_active', 1)
                ->inRandomOrder()
                ->limit(10)
                ->get();
            
            $destinations = [];
            foreach ($touristPlaces as $place) {
                $destination = [
                    'id' => $place->id,
                    'image_url' => $place->logo->path ?? '',
                    'mainImage' => $place->logo->path ?? '', // Added for compatibility
                    'title' => $place->name ?? '',
                    'name' => $place->name ?? '', // Added for compatibility
                    'category' => $place->category ?? 'Tourist Destination',
                    'description' => $place->short_description ?? $place->description ?? 'Explore this amazing destination',
                    'latitude' => $place->latitude,
                    'longitude' => $place->longitude,
                    'city' => $place->city,
                    'district' => $place->district,
                    'state' => $place->state,
                    'timing' => $place->timing ?? "{'monday': '9:00 AM - 6:00 PM', 'tuesday': '9:00 AM - 6:00 PM', 'wednesday': '9:00 AM - 6:00 PM', 'thursday': '9:00 AM - 6:00 PM', 'friday': '9:00 AM - 6:00 PM', 'saturday': '9:00 AM - 6:00 PM', 'sunday': '9:00 AM - 6:00 PM'}",
                    'entryFee' => $place->entryFee ?? 'Free',
                    'is_saved' => false,
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
                'title' => 'Featured Destinations',
                'subtitle' => 'Explore beautiful places around you'
            ];
        }
    }

    /**
     * Generate dynamic title for tourist places section
     *
     * @return array
     */
    private static function generateDynamicTitle(): array
    {
        $titles = [
            ['title' => '🌍 Explore Nearby Hidden Gems', 'subtitle' => 'Khubsurat jagahon ko khoj karo'],
            ['title' => '✈️ Travel & Adventure Awaits', 'subtitle' => 'Safar ki exciting dunyaa mein swagat'],
            ['title' => '🏛️ Historic & Cultural Sites', 'subtitle' => 'Itihas aur sanskriti ke raaste par'],
            ['title' => '🏖️ Best Destinations To Visit', 'subtitle' => 'Ek ek jagah hai dhoondhne laayak'],
            ['title' => '🎭 Unique Places, Unique Stories', 'subtitle' => 'Har jagah ka ek niya kissa hain'],
            ['title' => '🌟 Top Rated Places', 'subtitle' => 'Log sabse zyada jahan pasand karte hain'],
            ['title' => '🗺️ Journey Into Discovery', 'subtitle' => 'Naye raaste, naye rishte, naye sapne'],
            ['title' => '💎 Must-Visit Destinations', 'subtitle' => 'Zamane bhar mein famous jagahon se mulaqat'],
        ];

        return $titles[array_rand($titles)];
    }
}

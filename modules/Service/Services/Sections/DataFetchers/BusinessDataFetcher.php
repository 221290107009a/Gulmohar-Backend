<?php

namespace Modules\Service\Services\Sections\DataFetchers;

use Modules\Business\Entities\Business;
use Modules\Business\Entities\BusinessReview;
use Modules\Business\Entities\UserBusinessLikes;
use Illuminate\Support\Facades\Storage;

/**
 * Business Data Fetcher
 * 
 * Handles fetching business data with reviews and ratings
 */
class BusinessDataFetcher
{
    /**
     * Fetch businesses for home screen
     * 
     * @param string $listingType Either 'all' for all businesses, 'random' for random category, or specific category name
     * @return array
     */
    public static function fetch(string $listingType = 'random'): array
    {
        try {
            $selectedCategory = null;
            $title = 'Business Listings';
            $subtitle = 'Explore local businesses and services';
            
            if ($listingType === 'all') {
                // Show all businesses in random order
                $businesses = Business::where('is_active', 1)
                    ->inRandomOrder()
                    ->limit(12)
                    ->get();
                $titleData = self::generateDynamicTitle('all');
                $title = $titleData['title'];
                $subtitle = $titleData['subtitle'];
            } elseif ($listingType === 'random') {
                // Get all unique categories from active businesses
                $categories = Business::where('is_active', 1)
                    ->select('business_type')
                    ->distinct()
                    ->pluck('business_type')
                    ->filter(function ($businessType) {
                        return !empty($businessType);
                    })
                    ->toArray();
                
                if (empty($categories)) {
                    // Fallback to all businesses if no categories found
                    $businesses = Business::where('is_active', 1)
                        ->inRandomOrder()
                        ->limit(12)
                        ->get();
                    $titleData = self::generateDynamicTitle('all');
                    $title = $titleData['title'];
                    $subtitle = $titleData['subtitle'];
                } else {
                    // Pick a random category
                    $selectedCategory = $categories[array_rand($categories)];
                    
                    // Show businesses from random category in random order
                    $businesses = Business::where('is_active', 1)
                        ->where('business_type', $selectedCategory)
                        ->inRandomOrder()
                        ->limit(12)
                        ->get();
                    
                    $titleData = self::generateDynamicTitle('category', ['category_name' => $selectedCategory]);
                    $title = $titleData['title'];
                    $subtitle = $titleData['subtitle'];
                }
            } else {
                // Show specific category businesses in random order
                $selectedCategory = $listingType;
                $businesses = Business::where('is_active', 1)
                    ->where('business_type', $listingType)
                    ->inRandomOrder()
                    ->limit(12)
                    ->get();
                $titleData = self::generateDynamicTitle('category', ['category_name' => $listingType]);
                $title = $titleData['title'];
                $subtitle = $titleData['subtitle'];
            }
            
            foreach ($businesses as $business) {
                // Process media URLs
                $mediaItems = [];
                $mediaIds = explode(',', $business->media);
                
                foreach ($mediaIds as $mediaId) {
                    if (!empty($mediaId)) {
                        $mediaUrl = Storage::url('business_media/' . $mediaId);
                        $mediaItems[] = $mediaUrl;
                    }
                }
                $business->media_urls = $mediaItems;
                
                // Get reviews
                $reviews = BusinessReview::where([
                    'business_id' => $business->id,
                    'status' => 'verified'
                ])->get();
                
                $business->total_reviews = $reviews->count();
                $business->average_rating = $reviews->avg('rating') ?? 0;
                $business->like_count = UserBusinessLikes::where('business_id', $business->id)->count();
            }
            
            return [
                'businesses' => $businesses,
                'listing_type' => $listingType,
                'selected_category' => $selectedCategory,
                'title' => $title,
                'subtitle' => $subtitle
            ];
        } catch (\Exception $e) {
            return [
                'businesses' => [],
                'listing_type' => $listingType,
                'selected_category' => null,
                'title' => 'Business Listings',
                'subtitle' => 'Explore local businesses and services'
            ];
        }
    }

    /**
     * Generate dynamic title for business sections
     *
     * @param string $context 'all' for all businesses or 'category' for specific category
     * @param array $meta Additional metadata like category_name
     * @return array
     */
    private static function generateDynamicTitle(string $context, array $meta = []): array
    {
        $contextTitles = [
            'all' => [
                ['title' => '✨ Local Favourites For You', 'subtitle' => 'Rozmarra ki zarooraton ke liye best businesses'],
                ['title' => '🏪 Trusted Shops & Services', 'subtitle' => 'Jin par log bharosa karte hain'],
                ['title' => '💼 Daily Life Made Easy', 'subtitle' => 'Shopping, services, sab ek hi jagah'],
                ['title' => '🌟 Most Loved Businesses', 'subtitle' => 'Rating aur reviews ke saath top choices'],
            ],
            'category' => [
                ['title' => 'Best ' . ($meta['category_name'] ?? 'Businesses'), 'subtitle' => 'Is category ke popular options dekhiye'],
                ['title' => '🏆 Top Rated ' . ($meta['category_name'] ?? 'Businesses'), 'subtitle' => 'Achhe reviews wale vyapar ek list mein'],
                ['title' => ($meta['category_name'] ?? 'Businesses') . ' For Daily Use', 'subtitle' => 'Roz ke kaam ke liye helpful services'],
                ['title' => '⭐ Popular ' . ($meta['category_name'] ?? 'Businesses'), 'subtitle' => 'Jinhe users baar‑baar choose karte hain'],
            ],
        ];

        $pool = $contextTitles[$context] ?? [];
        
        return !empty($pool) ? $pool[array_rand($pool)] : ['title' => 'Business Listings', 'subtitle' => 'Explore local businesses and services'];
    }
}
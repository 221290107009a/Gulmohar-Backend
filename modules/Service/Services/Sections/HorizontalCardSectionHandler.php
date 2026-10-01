<?php

namespace Modules\Service\Services\Sections;

use Modules\Service\Services\Sections\Contracts\SectionHandlerInterface;
use Modules\Community\Entities\Community;
use Modules\Community\Entities\CommunityMembers;
use Modules\Business\Entities\Business;
use Modules\Business\Entities\UserBusinessLikes;
use Modules\TouristPlace\Entities\TouristPlace;
use Illuminate\Support\Facades\Storage;

/**
 * Horizontal Card Section Handler
 * 
 * Handles horizontal card listings for:
 * - Communities
 * - Businesses
 * - Tourist Places
 */
class HorizontalCardSectionHandler implements SectionHandlerInterface
{
    
    public function handle(string $sectionId, ?int $userId = null): ?array
    {
        return $this->createCommunityListing($sectionId, $userId);
    }

    public function createSpecificListing(string $sectionId, ?int $userId, string $listingType, ?string $businessListingType = 'random', ?string $stateListingType = 'random'): ?array
    {
        switch ($listingType) {
            case 'community':
                return $this->createCommunityListing($sectionId, $userId);
            case 'business':
                return $this->createBusinessListing($sectionId, $userId, $businessListingType);
            case 'tourist-place':
                return $this->createTouristPlaceListing($sectionId, $userId, $stateListingType);
            default:
                return null;
        }
    }

    /**
     * Create Community Listing
     */
    private function createCommunityListing(string $id, ?int $userId): array
    {
        // Get all unique categories from active communities
        $categories = Community::where('is_active', 1)
            ->select('category')
            ->distinct()
            ->pluck('category')
            ->filter(function ($category) {
                return !empty($category);
            })
            ->toArray();
        
        $selectedCategory = null;
        $title = 'Vibrant Communities';
        $subtitle = 'Join and grow with your community';
        
        // Pick a random category if available
        if (!empty($categories)) {
            $selectedCategory = $categories[array_rand($categories)];
            
            // Generate attractive titles based on category
            $titlePairs = $this->getAttractiveTitle($selectedCategory);
            $title = $titlePairs['title'];
            $subtitle = $titlePairs['subtitle'];
            
            // Get top communities from selected category by member count
            $communities = Community::where('communities.is_active', true)
                ->where('communities.category', $selectedCategory)
                ->leftJoin(\DB::raw('(SELECT community_id, COUNT(id) as members_count FROM community_members WHERE is_active = 1 GROUP BY community_id) as member_counts'), 
                    'communities.id', '=', 'member_counts.community_id')
                ->select('communities.*', \DB::raw('COALESCE(member_counts.members_count, 0) as members_count'))
                ->orderBy('members_count', 'desc')
                ->limit(15)
                ->get();
        } else {
            // Fallback to all communities if no categories found
            $communities = Community::where('communities.is_active', true)
                ->leftJoin(\DB::raw('(SELECT community_id, COUNT(id) as members_count FROM community_members WHERE is_active = 1 GROUP BY community_id) as member_counts'), 
                    'communities.id', '=', 'member_counts.community_id')
                ->select('communities.*', \DB::raw('COALESCE(member_counts.members_count, 0) as members_count'))
                ->orderBy('members_count', 'desc')
                ->limit(15)
                ->get();
        }

        return [
            'id' => $id,
            'type' => $this->getSectionType(),
            'service_type' => 'community',
            'data' => [
                'title' => $title,
                'subtitle' => $subtitle,
                'communities' => $communities->map(function ($community) use ($userId) {
                    // Get accurate member count
                    $membersCount = CommunityMembers::where('community_id', $community->id)
                        ->where('is_active', 1)
                        ->count();
                    
                    $isJoined = 0;
                    if ($userId) {
                        $isJoined = CommunityMembers::where('community_id', $community->id)
                            ->where('user_id', $userId)
                            ->where('is_active', 1)
                            ->exists() ? 1 : 0;
                    }
                    
                    return [
                        'id' => $community->id,
                        'name' => $community->name,
                        'description' => $community->description,
                        'category' => $community->category,
                        'image' => asset('storage/community/' . $community->image) ?? '',
                        'total_joined_members' => $membersCount,
                        'members_count' => $membersCount,
                        'is_active' => $community->is_active,
                        'is_joined' => $isJoined,
                        'is_verified' => $community->is_verified ?? 0,
                    ];
                })->toArray(),
            ],
        ];
    }

    private function createBusinessListing(string $id, ?int $userId, string $listingType = 'random'): array
    {
        $title = 'Business Listings';
        $subtitle = 'Explore local businesses and services';
        $selectedCategory = null;
        
        if ($listingType === 'all') {
            // Show all businesses in random order
            $businesses = Business::where('is_active', 1)
                ->with(['reviews', 'likes'])
                ->inRandomOrder()
                ->limit(12)
                ->get();
            $title = 'Business Listings';
            $subtitle = 'Explore all local businesses and services';
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
                    ->with(['reviews', 'likes'])
                    ->inRandomOrder()
                    ->limit(12)
                    ->get();
            } else {
                // Pick a random category
                $selectedCategory = $categories[array_rand($categories)];
                
                // Show businesses from random category in random order
                $businesses = Business::where('is_active', 1)
                    ->where('business_type', $selectedCategory)
                    ->with(['reviews', 'likes'])
                    ->inRandomOrder()
                    ->limit(12)
                    ->get();
                
                $title = ucfirst($selectedCategory) . ' Businesses';
                $subtitle = 'Discover great ' . strtolower($selectedCategory) . ' options';
            }
        } else {
            // Show specific category businesses in random order
            $selectedCategory = $listingType;
            $businesses = Business::where('is_active', 1)
                ->where('business_type', $listingType)
                ->with(['reviews', 'likes'])
                ->inRandomOrder()
                ->limit(12)
                ->get();
            $title = ucfirst($listingType) . ' Businesses';
            $subtitle = 'Discover great ' . strtolower($listingType) . ' options';
        }

        return [
            'id' => $id,
            'type' => $this->getSectionType(),
            'service_type' => 'business',
            'data' => [
                'title' => $title,
                'subtitle' => $subtitle,
                'listing_type' => $listingType,
                'selected_category' => $selectedCategory,
                'communities' => $businesses->map(function ($business) use ($userId) {
                    // Get media URLs
                    $mediaItems = [];
                    if ($business->media) {
                        $mediaIds = explode(',', $business->media);
                        foreach ($mediaIds as $mediaId) {
                            if (!empty($mediaId)) {
                                $mediaUrl = Storage::url('business_media/' . $mediaId);
                                $mediaItems[] = $mediaUrl;
                            }
                        }
                    }
                    
                    // Calculate average rating from reviews
                    $reviews = $business->reviews ?? [];
                    $averageRating = 0;
                    $totalReviews = count($reviews);
                    if ($totalReviews > 0) {
                        $totalRating = 0;
                        foreach ($reviews as $review) {
                            $totalRating += $review->rating;
                        }
                        $averageRating = round($totalRating / $totalReviews, 1);
                    }
                    
                    // Get like count
                    $likeCount = $business->likes ? count($business->likes) : 0;
                    
                    // Check if user liked this business
                    $userLike = 0;
                    if ($userId) {
                        $userLike = UserBusinessLikes::where('business_id', $business->id)
                            ->where('user_id', $userId)
                            ->exists() ? 1 : 0;
                    }
                    
                    return [
                        'id' => $business->id,
                        'title' => $business->title ?? '',
                        'name' => $business->title ?? '',
                        'description' => $business->description ?? '',
                        'business_type' => $business->business_type ?? '',
                        'category' => $business->category ?? $business->business_type ?? '',
                        'image' => !empty($mediaItems) ? $mediaItems[0] : '',
                        'media_urls' => $mediaItems,
                        'address' => $business->address ?? '',
                        'location' => $business->address ?? '',
                        'number' => $business->number ?? '',
                        'latitude' => $business->latitude,
                        'longitude' => $business->longitude,
                        'average_rating' => $averageRating,
                        'total_reviews' => $totalReviews,
                        'like_count' => $likeCount,
                        'business_like' => $userLike,
                        'slug' => $business->slug ?? '',
                        'is_active' => $business->is_active,
                        'is_verified' => 0,
                    ];
                })->toArray(),
            ],
        ];
    }

    private function createTouristPlaceListing(string $id, ?int $userId, string $listingType = 'random'): array
    {
        $title = 'Tourist Places';
        $subtitle = 'Discover beautiful destinations';
        $selectedState = null;
        
        if ($listingType === 'all') {
            // Show all tourist places in random order
            $touristPlaces = TouristPlace::where('is_active', 1)
                ->inRandomOrder()
                ->limit(12)
                ->get();
            $title = 'Tourist Places';
            $subtitle = 'Discover beautiful destinations';
        } elseif ($listingType === 'random') {
            // Get all unique states from active tourist places
            $states = TouristPlace::where('is_active', 1)
                ->with('translations')
                ->get()
                ->pluck('state')
                ->filter(function ($state) {
                    return !empty($state);
                })
                ->unique()
                ->toArray();
            
            if (empty($states)) {
                // Fallback to all places if no states found
                $touristPlaces = TouristPlace::where('is_active', 1)
                    ->inRandomOrder()
                    ->limit(12)
                    ->get();
            } else {
                // Pick a random state
                $selectedState = $states[array_rand($states)];
                
                // Show tourist places from random state in random order
                $touristPlaces = TouristPlace::where('is_active', 1)
                    ->with('translations')
                    ->get()
                    ->filter(function ($place) use ($selectedState) {
                        return $place->state === $selectedState;
                    })
                    ->shuffle()
                    ->take(12)
                    ->values();
                
                $title = $selectedState . ' Tourist Places';
                $subtitle = 'Explore amazing destinations in ' . $selectedState;
            }
        } else {
            // Show tourist places from specific state in random order
            $selectedState = $listingType;
            $touristPlaces = TouristPlace::where('is_active', 1)
                ->with('translations')
                ->get()
                ->filter(function ($place) use ($listingType) {
                    return $place->state === $listingType;
                })
                ->shuffle()
                ->take(12)
                ->values();
            $title = $listingType . ' Tourist Places';
            $subtitle = 'Explore amazing destinations in ' . $listingType;
        }

        return [
            'id' => $id,
            'type' => $this->getSectionType(),
            'service_type' => 'tourist-place',
            'data' => [
                'title' => $title,
                'subtitle' => $subtitle,
                'listing_type' => $listingType,
                'selected_state' => $selectedState,
                'communities' => $touristPlaces->map(function ($place) use ($userId) {
                    return [
                        'id' => $place->id,
                        'name' => $place->name ?? '',
                        'description' => $place->description ?? '',
                        'category' => $place->category ?? '',
                        'image' => $place->logo->path ?? '',
                        'address' => $place->address ?? '',
                        'state' => $place->state ?? '',
                        'city' => $place->city ?? '',
                        'latitude' => $place->latitude,
                        'longitude' => $place->longitude,
                    ];
                })->toArray(),
            ],
        ];
    }

    /**
     * Generate attractive titles and subtitles based on category
     * 
     * @param string $category
     * @return array
     */
    private function getAttractiveTitle(string $category): array
    {
        $category = strtolower(trim($category));
        
        $attractiveTitles = [
            'religious' => [
                'title' => '🙏 Sacred Communities',
                'subtitle' => 'Connect through faith & spiritual growth'
            ],
            'political' => [
                'title' => '🗳️ Political Forum',
                'subtitle' => 'Share your voice & ideas'
            ],
            'social' => [
                'title' => '👥 Social Circles',
                'subtitle' => 'Make meaningful connections'
            ],
            'profession' => [
                'title' => '💼 Career Network',
                'subtitle' => 'Grow with professionals'
            ],
            'educational' => [
                'title' => '📚 Knowledge Hub',
                'subtitle' => 'Learn & inspire together'
            ],
            'business' => [
                'title' => '🏢 Business Marketplace',
                'subtitle' => 'Build partnerships & grow'
            ],
            'entertainment' => [
                'title' => '🎉 Entertainment Zone',
                'subtitle' => 'Enjoy & share fun moments'
            ],
            'ngo' => [
                'title' => '❤️ Social Impact',
                'subtitle' => 'Make a real difference'
            ],
            'other' => [
                'title' => '🌟 Discover More',
                'subtitle' => 'Explore unique communities'
            ],
        ];

        // Return specific title if category matches, otherwise generate generic one
        if (isset($attractiveTitles[$category])) {
            return $attractiveTitles[$category];
        }

        // Generate generic attractive title for unknown categories
        return [
            'title' => '🌟 ' . ucfirst($category),
            'subtitle' => 'Join & connect with ' . strtolower($category)
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionType(): string
    {
        return 'horizontal-card';
    }
}

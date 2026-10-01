<?php

namespace Modules\Service\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Service\Entities\Service;
use Modules\Service\Constants\HomeScreenConstants;
use Modules\User\Entities\UserService;
use Modules\ProgramNotification\Entities\ProgramNotification;
use Modules\ProgramNotification\Entities\ProgramRegistration;
use Modules\Community\Entities\Community;
use Modules\BuddhaVihar\Entities\BuddhaVihar;  
use Modules\Product\Entities\Product;
use Modules\BiodataFrame\Entities\BiodataFrame;
use Modules\Template\Entities\Template;
use Modules\Business\Entities\Business;
use Modules\BiodataFrame\Entities\MarriageBiodata;
use Modules\Community\Entities\CommunityMembers;
use Modules\Community\Entities\CommunityPost;
use Modules\Audiobook\Entities\Audiobook;
use Modules\AudioSong\Entities\AudioSongAlbum;
use Modules\Business\Entities\BusinessReview;
use Modules\Business\Entities\UserBusinessLikes;
use Modules\Badge\Entities\Badge;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Modules\Service\Services\HomeScreenService;

class HomeScreenController
{
    protected $homeScreenService;

    public function __construct(HomeScreenService $homeScreenService)
    {
        $this->homeScreenService = $homeScreenService;
    }

    public function index(Request $request)
    {
        try {
            \Log::info('[HomeScreenController.index] START', [
                'method' => $request->method(),
                'all_params' => $request->all(),
                'user_id_raw' => $request->input('user_id'),
            ]);
            
            // Validate input
            $userId = $request->input('user_id') ?? auth()->id() ?? null;
            
            \Log::info('[HomeScreenController.index] userId resolved', [
                'userId' => $userId,
                'isEmpty' => empty($userId),
            ]);
            
            if (empty($userId)) {
                \Log::warning('[HomeScreenController.index] REJECTING - No userId');
                return response()->json([
                    'success' => false,
                    'message' => 'User ID is required',
                ], 422);
            }
            
            $page = (int) $request->input('page', 1);
            if ($page < 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Page must be a positive integer',
                ], 422);
            }
            
            $perPage = (int) $request->input('per_page', HomeScreenConstants::PER_PAGE_DEFAULT);
            if ($perPage < HomeScreenConstants::PER_PAGE_MIN || $perPage > HomeScreenConstants::PER_PAGE_MAX) {
                return response()->json([
                    'success' => false,
                    'message' => sprintf('Per page must be between %d and %d', HomeScreenConstants::PER_PAGE_MIN, HomeScreenConstants::PER_PAGE_MAX),
                ], 422);
            }
            
            $serviceType = $request->input('service_type', null); // Optional service filter
            $sessionId = $request->input('session_id', null); // Session ID for infinite scroll
            
            // Generate sections using service layer with session management
            $result = $this->homeScreenService->generateSections(
                $page, 
                $perPage, 
                $serviceType, 
                $userId,
                $sessionId
            );
            
            \Log::info('[HomeScreenController.index] SUCCESS', [
                'userId' => $userId,
                'page' => $page,
                'sectionCount' => count($result['data'] ?? []),
                'sessionId' => $result['session_id'] ?? null,
            ]);
            
            return response()->json([
                'success' => true,
                'sections' => $result['data'],
                'pagination' => [
                    'current_page' => $result['current_page'],
                    'has_more' => $result['has_more'],
                    'total' => $result['total'] ?? null,
                ],
                'session_id' => $result['session_id'],
                'meta' => $result['meta'] ?? []
            ], 200);
            
        } catch (\Throwable $e) {
            \Log::error('[HomeScreen] Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error loading home screen',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Get daily highlights for user
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDailyHighlights(Request $request)
    {
        try {
            $userId = $request->user_id ?? null;
            
            // Get user's enabled services
            $enabledServiceIds = $this->getUserEnabledServices($userId);
            
            // Build highlight items based on enabled services
            $highlights = $this->buildHighlightItems($userId, $enabledServiceIds);
            
            return response()->json([
                'success' => true,
                'highlights' => $highlights,
                'last_updated' => now()->toIso8601String()
            ], 200);
            
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get user's enabled service IDs
     */
    private function getUserEnabledServices($userId)
    {
        if (!$userId) {
            // Return all active services if no user
            return Service::where('is_active', 1)->pluck('id')->toArray();
        }
        
        // Get services user has NOT disabled
        $disabledServiceIds = UserService::where('user_id', $userId)
            ->where('is_enabled', false)
            ->pluck('service_id')
            ->toArray();
        
        return Service::where('is_active', 1)
            ->whereNotIn('id', $disabledServiceIds)
            ->pluck('id')
            ->toArray();
    }
    
    /**
     * Build highlight items with user-specific data in fixed order
     */
    private function buildHighlightItems($userId, $enabledServiceIds)
    {
        $highlights = [];
        
        // Fixed order as per requirements:
        // 1. Day wise Quote
        // 2. Community Activity (today's posts count or join message)
        // 3. Upcoming Programs
        // 4. Near Buddha Vihar
        // 5. Other content
        
        $orderedHighlights = [
            'share_quotes' => null,
            'our_community' => null,
            'program_notifications' => null,
            'buddh_vihar' => null,
            'marriage_biodata' => null,
            'bahujan_sahity' => null,
            'online_store' => null,
        ];
        
        // Get all services and map them
        $services = Service::whereIn('id', $enabledServiceIds)->get();
        
        foreach ($services as $service) {
            // Clean service name - handle both actual newlines and escaped newline strings
            $cleanName = $service->name;
            $cleanName = str_replace(['\\n', '\\r', '\\t', "\n", "\r", "\t"], ' ', $cleanName);
            $cleanName = preg_replace('/\s+/', ' ', $cleanName); // Replace multiple spaces with single space
            $cleanName = trim($cleanName);
            $serviceName = strtolower(str_replace(' ', '_', $cleanName));
            
            // Debug logging
            /* \Log::info('Processing service', [
                'original_name' => $service->name,
                'cleaned_name' => $cleanName,
                'service_name' => $serviceName
            ]); */
            
            $highlightItem = $this->getServiceHighlight($service, $userId);
            
            // Debug logging
            /* \Log::info('Highlight item generated', [
                'service_name' => $serviceName,
                'highlight' => $highlightItem
            ]); */
            
            if ($highlightItem) {
                // Map service names to ordered keys
                if ($serviceName === 'share_quotes' || $serviceName === 'shrestha_vichar') {
                    $orderedHighlights['share_quotes'] = $highlightItem;
                } elseif ($serviceName === 'our_community' || $serviceName === 'community') {
                    $orderedHighlights['our_community'] = $highlightItem;
                } elseif ($serviceName === 'program_notifications') {
                    $orderedHighlights['program_notifications'] = $highlightItem;
                } elseif ($serviceName === 'buddh_vihar') {
                    $orderedHighlights['buddh_vihar'] = $highlightItem;
                } elseif ($serviceName === 'marriage_biodata') {
                    $orderedHighlights['marriage_biodata'] = $highlightItem;
                } elseif ($serviceName === 'bahujan_sahity' || $serviceName === 'online_store') {
                    if (!$orderedHighlights['bahujan_sahity']) {
                        $orderedHighlights['bahujan_sahity'] = $highlightItem;
                    }
                }
            }
        }
        
        // Build final array in order, excluding nulls
        foreach ($orderedHighlights as $highlight) {
            if ($highlight !== null) {
                $highlights[] = $highlight;
            }
        }
        
        // Debug final highlights
        /* \Log::info('Final highlights', ['count' => count($highlights), 'highlights' => $highlights]); */
        
        return $highlights;
    }
    
    /**
     * Get highlight data for specific service
     */
    private function getServiceHighlight($service, $userId)
    {
        $cleanName = str_replace(["\n", "\r", "\t"], ' ', $service->name);
        $cleanName = trim($cleanName);
        $serviceName = strtolower(str_replace(' ', '_', $cleanName));
        
        switch ($serviceName) {
            case 'our_community':
            case 'community':
                return $this->getCommunityHighlight($userId);
                
            case 'program_notifications':
                return $this->getProgramHighlight($userId);
                
            case 'marriage_biodata':
                return $this->getBiodataHighlight($userId);
                
            case 'share_quotes':
            case 'shrestha_vichar':
                return $this->getQuoteHighlight($userId);
                
            case 'bahujan_sahity':
            case 'online_store':
                return $this->getProductHighlight($userId);
                
            case 'buddh_vihar':
                return $this->getViharHighlight($userId);
                
            default:
                return null;
        }
    }
    
    /**
     * Community highlight with user stats
     */
    private function getCommunityHighlight($userId)
    {
        try {
            $subtitle = "Join communities";
            $newPosts = 0;
            
            if ($userId) {
                // First check if user has joined any communities
                $userCommunitiesCount = CommunityMembers::where('user_id', $userId)
                    ->where('is_active', 1)
                    ->where('approval_status', 'approved')
                    ->count();
                
                if ($userCommunitiesCount > 0) {
                    // User has joined communities, count today's new posts
                    // Get user's community IDs
                    $userCommunityIds = CommunityMembers::where('user_id', $userId)
                        ->where('is_active', 1)
                        ->where('approval_status', 'approved')
                        ->pluck('community_id')
                        ->toArray();
                    
                    // Count today's posts in user's communities
                    $newPosts = CommunityPost::whereIn('community_id', $userCommunityIds)
                        ->whereDate('created_at', Carbon::today())
                        ->count();
                    
                    $subtitle = $newPosts > 0 ? "$newPosts new posts today" : "No new posts today";
                } else {
                    // User hasn't joined any community
                    $subtitle = "Join communities to see updates";
                }
            } else {
                // For guest users, show total active communities
                $totalCommunities = Community::where('is_active', 1)->count();
                $subtitle = "$totalCommunities active communities";
            }
            
            return [
                'id' => 'community_activity',
                'icon' => 'people',
                'icon_color' => '#EF4444',
                'title' => 'Community Activity',
                'subtitle' => $subtitle,
                'priority' => $newPosts > 0 ? 10 : 5,
                'action_type' => 'navigate',
                'action_data' => ['screen' => 'Communities']
            ];
        } catch (\Throwable $e) {
            \Log::error('getCommunityHighlight failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return null;
        }
    }
    
    /**
     * Program highlight with upcoming events
     */
    private function getProgramHighlight($userId)
    {
        try {
            $upcomingPrograms = ProgramNotification::where('is_active', 1)
                ->where(function($query) {
                    $query->whereDate('start_date', '>=', Carbon::today())
                        ->orWhereDate('end_date', '>=', Carbon::today());
                })
                ->count();
            
            $registeredCount = 0;
            if ($userId) {
                // Get upcoming program IDs
                $upcomingProgramIds = ProgramNotification::where('is_active', 1)
                    ->where(function($query) {
                        $query->whereDate('start_date', '>=', Carbon::today())
                            ->orWhereDate('end_date', '>=', Carbon::today());
                    })
                    ->pluck('id')
                    ->toArray();
                
                // Count user's registrations for upcoming programs
                $registeredCount = ProgramRegistration::where('user_id', $userId)
                    ->whereIn('program_notification_id', $upcomingProgramIds)
                    ->count();
            }
            
            $subtitle = $registeredCount > 0 
                ? "You're registered for $registeredCount events"
                : "$upcomingPrograms events this week";
            
            return [
                'id' => 'upcoming_events',
                'icon' => 'calendar',
                'icon_color' => '#10B981',
                'title' => 'Upcoming Events',
                'subtitle' => $subtitle,
                'priority' => $registeredCount > 0 ? 15 : 8,
                'action_type' => 'navigate',
                'action_data' => ['screen' => 'Programs']
            ];
        } catch (\Throwable $e) {
            \Log::error('getProgramHighlight failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return null;
        }
    }
    
    /**
     * Biodata highlight
     */
    private function getBiodataHighlight($userId)
    {
        try {
            $userBiodataCount = 0;
            if ($userId) {
                $userBiodataCount = MarriageBiodata::where('user_id', $userId)->count();
            }
            
            $subtitle = $userBiodataCount > 0 
                ? "You have $userBiodataCount biodata" 
                : "Create your biodata";
            
            return [
                'id' => 'marriage_biodata',
                'icon' => 'heart',
                'icon_color' => '#EC4899',
                'title' => 'Marriage Biodata',
                'subtitle' => $subtitle,
                'priority' => $userBiodataCount === 0 ? 12 : 6,
                'action_type' => 'navigate',
                'action_data' => ['screen' => 'Biodata']
            ];
        } catch (\Throwable $e) {
            \Log::error('getBiodataHighlight failed', ['error' => $e->getMessage()]);
            return null;
        }
    }
    
    /**
     * Quote of the day with category-based filtering
     */
    private function getQuoteHighlight($userId)
    {
        try {
            // Priority 1: Try to get quote from category 1 (special days) matching today's date
            $today = date('m-d');
            $quote = Template::where('is_active', 1)
                ->whereHas('templateCategory', function($q) {
                    $q->where('is_active', 1);
                })
                ->whereRaw("FIND_IN_SET(1, template_category_id)")
                ->whereRaw("DATE_FORMAT(day_special_date, '%m-%d') = ?", [$today])
                ->inRandomOrder()
                ->first();
            
            // Priority 2: If no special day quote, get any random quote from category 1
            if (!$quote) {
                $quote = Template::where('is_active', 1)
                    ->whereHas('templateCategory', function($q) {
                        $q->where('is_active', 1);
                    })
                    ->whereRaw("FIND_IN_SET(1, template_category_id)")
                    ->inRandomOrder()
                    ->first();
            }
            
            // Priority 3: If still no quote, get any random active quote
            if (!$quote) {
                $quote = Template::where('is_active', 1)
                    ->whereHas('templateCategory', function($q) {
                        $q->where('is_active', 1);
                    })
                    ->inRandomOrder()
                    ->first();
            }
            
            $subtitle = 'Inspire yourself today';
            if ($quote && isset($quote->name)) {
                $quoteName = strip_tags($quote->name);
                $subtitle = mb_strlen($quoteName) > 50 ? mb_substr($quoteName, 0, 50) . '...' : $quoteName;
            }
            
            return [
                'id' => 'quote_of_day',
                'icon' => 'leaf',
                'icon_color' => '#A855F7',
                'title' => 'Quote of the Day',
                'subtitle' => $subtitle,
                'priority' => 7,
                'action_type' => 'navigate',
                'action_data' => ['screen' => 'Quotes']
            ];
        } catch (\Throwable $e) {
            \Log::error('getQuoteHighlight failed', ['error' => $e->getMessage()]);
            return null;
        }
    }
    
    /**
     * Product highlight
     */
    private function getProductHighlight($userId)
    {
        try {
            $newProducts = Product::where('is_active', 1)
                ->where('created_at', '>', now()->subWeek())
                ->count();
            
            $subtitle = $newProducts > 0 
                ? "$newProducts new products this week" 
                : "Explore our products";
            
            return [
                'id' => 'new_products',
                'icon' => 'cart',
                'icon_color' => '#F59E0B',
                'title' => 'New Arrivals',
                'subtitle' => $subtitle,
                'priority' => $newProducts > 0 ? 9 : 4,
                'action_type' => 'navigate',
                'action_data' => ['screen' => 'Products']
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }
    
    /**
     * Buddha Vihar highlight (location-based)
     */
    private function getViharHighlight($userId)
    {
        try {
            $totalVihars = BuddhaVihar::where('is_active', 1)->count();
            
            return [
                'id' => 'near_you',
                'icon' => 'location',
                'icon_color' => '#3B82F6',
                'title' => 'Near You',
                'subtitle' => "Find Buddha Vihars nearby",
                'priority' => 5,
                'action_type' => 'navigate',
                'action_data' => ['screen' => 'BuddhaVihar']
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }
}

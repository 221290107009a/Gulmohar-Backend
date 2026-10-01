<?php

namespace Modules\Service\Services;

use Illuminate\Support\Facades\Log;
use Modules\Service\Config\ServiceSectionConfig;
use Modules\Service\Config\SectionVariationConfig;
use Modules\Service\Entities\Service;
use Modules\User\Entities\UserService;
use Modules\Service\Services\Sections\Contracts\SectionHandlerInterface;
use Modules\Service\Services\Sections\DailyHighlightsSectionHandler;
use Modules\Service\Services\Sections\FocusZoomSectionHandler;
use Modules\Service\Services\Sections\AdBannerSectionHandler;
use Modules\Service\Services\Sections\HorizontalCardSectionHandler;
use Modules\Service\Services\Sections\ProductGridSectionHandler;
use Modules\Service\Services\Sections\BusinessCardsSectionHandler;
use Modules\Service\Services\Sections\HeroCarouselSectionHandler;
use Modules\Service\Services\Sections\BiodataCarouselSectionHandler;
use Modules\Service\Services\Sections\AlbumQuickPicksSectionHandler;
use Modules\Service\Services\Sections\TemplateGridSectionHandler;
use Modules\Service\Services\Sections\ExpandableCardsSectionHandler;
use Modules\Service\Services\Sections\ModernCollageSectionHandler;
use Modules\Service\Services\Sections\BiodataGallerySectionHandler;
use Modules\Service\Services\Sections\EventTimelineSectionHandler;
use Modules\Service\Services\Sections\EventCardSectionHandler;
use Modules\Service\Services\Sections\DualRowScrollSectionHandler;
use Modules\Service\Services\Sections\InfoCardsSectionHandler;
use Modules\Service\Services\Sections\InviteFriendsSectionHandler;
use Modules\Service\Services\Sections\FooterSpaceSectionHandler;
use Modules\Service\Services\Sections\FeaturedDestinationsSectionHandler;
use Modules\Service\Services\Sections\SponsoredPostSectionHandler;

class HomeScreenService
{
    private array $handlers;

    private array $variationMap;

    private SectionStateManager $stateManager;

    public function __construct(SectionStateManager $stateManager)
    {
        $this->registerHandlers();
        $this->variationMap = SectionVariationConfig::map();
        $this->stateManager = $stateManager;
    }

    /**
     * Register all section handlers
     */
    private function registerHandlers(): void
    {
        $handlerClasses = [
            'focus-zoom' => FocusZoomSectionHandler::class,
            'ad-banner' => AdBannerSectionHandler::class,
            'horizontal-card' => HorizontalCardSectionHandler::class,
            'product-grid' => ProductGridSectionHandler::class,
            'business-cards' => BusinessCardsSectionHandler::class,
            'hero-carousel' => HeroCarouselSectionHandler::class,
            'biodata-carousel' => BiodataCarouselSectionHandler::class,
            'album-quick-picks' => AlbumQuickPicksSectionHandler::class,
            'template-grid' => TemplateGridSectionHandler::class,
            'expandable-cards' => ExpandableCardsSectionHandler::class,
            'modern-collage' => ModernCollageSectionHandler::class,
            'biodata-gallery' => BiodataGallerySectionHandler::class,
            'event-timeline' => EventTimelineSectionHandler::class,
            'event-card' => EventCardSectionHandler::class,
            'dual-row-scroll' => DualRowScrollSectionHandler::class,
            'info-cards' => InfoCardsSectionHandler::class,
            'invite-friends' => InviteFriendsSectionHandler::class,
            'featured-destinations' => FeaturedDestinationsSectionHandler::class,
            'footer-space' => FooterSpaceSectionHandler::class,
            'sponsored-posts' => SponsoredPostSectionHandler::class,
        ];

        foreach ($handlerClasses as $type => $class) {
            $this->handlers[$type] = new $class();
        }
    }

    public function generateSections(
        int $page = 1,
        int $perPage = 12,
        ?string $serviceType = null,
        ?int $userId = null,
        ?string $sessionId = null
    ): array {
        $serviceType ??= 'general';
        $config = ServiceSectionConfig::getServiceConfig($serviceType);

        if (!$config) {
            return $this->emptyResponse($page, $sessionId);
        }

        // Generate session ID if not provided
        if (!$userId) {
            return $this->emptyResponse($page, $sessionId);
        }

        // Get allowed service identifiers (active services + user-enabled services, minus disabled)
        $allowedServiceIdentifiers = $this->getAllowedServiceIdentifiers($userId);

        // Check if input session is valid BEFORE generating new one
        $isNewSession = !SessionIdGenerator::isValid($sessionId);
        $sessionId = SessionIdGenerator::getOrGenerate($sessionId, $userId);

        $sectionsConfig = $config['sections'];
        $adsEnabled = in_array('sponsored-posts', $sectionsConfig);
        if ($adsEnabled) {
            $sectionsConfig = array_values(array_diff($sectionsConfig, ['sponsored-posts']));
        }

        // Generate all possible combinations
        $allCombinations = $this->generateAllCombinations($sectionsConfig);
        
        // Filter out combinations for services that are not allowed
        $allCombinations = array_filter($allCombinations, function($combination) use ($allowedServiceIdentifiers) {
            // If combination has no service, keep it (e.g., footer-space)
            if (!isset($combination['service']) || is_null($combination['service'])) {
                return true;
            }
            // Include only if service is in allowed list
            return in_array($combination['service'], $allowedServiceIdentifiers);
        });
        
        // Re-index array after filtering
        $allCombinations = array_values($allCombinations);

        // Get or create session state
        $state = $this->stateManager->getOrCreateState($userId, $sessionId, $allCombinations);

        // Get sections for this request (returns sections and selected indices)
        $result = $this->stateManager->getSectionsForPage($state, $perPage);
        
        // Handle both old and new response formats
        if (is_array($result) && isset($result['sections'])) {
            // New format with selected_indices
            $sectionsToShow = $result['sections'] ?? [];
            $selectedIndices = $result['selected_indices'] ?? [];
        } elseif (is_array($result) && !isset($result['sections']) && !isset($result['selected_indices'])) {
            // Old format - just array of sections
            $sectionsToShow = $result;
            $selectedIndices = [];
        } else {
            // Fallback
            $sectionsToShow = [];
            $selectedIndices = [];
        }

        // Build actual section data
        $data = array_values(array_filter(array_map(
            fn ($meta) => is_array($meta) && isset($meta['type']) ? $this->buildSection($meta, $userId, $page) : null,
            $sectionsToShow
        )));

        // Inject sponsored posts if enabled
        if ($adsEnabled) {
            $this->injectSponsoredPosts($data, $state, $userId, $page);
        }

        // Calculate totals
        $totalUniqueSections = count($state['grouped_sections'] ?? []);
        $totalCombinations = array_sum(array_map('count', $state['grouped_sections'] ?? []));
        $currentCycle = $state['cycle'] ?? 1;
        $shownInCycle = ($state['page_number'] ?? 0) * $perPage;

        // Update state with the actual selected indices
        $this->stateManager->updateState($userId, $sessionId, $state, $selectedIndices);

        return [
            'data' => $data,
            'current_page' => $page,
            'has_more' => true, // Always true for infinite scroll
            'total' => $totalCombinations,
            'session_id' => $sessionId,
            'meta' => [
                'is_new_session' => $isNewSession,
                'shown_count' => $shownInCycle + count($data),
                'cycle' => $currentCycle,
                'content_exhausted' => false,
                'unique_section_types' => $totalUniqueSections,
            ]
        ];
    }

    private function injectSponsoredPosts(array &$data, array &$state, ?int $userId, int $page): void
    {
        try {
            $handler = $this->handlers['sponsored-posts'] ?? null;
            if (!$handler || !($handler instanceof SponsoredPostSectionHandler)) {
                return;
            }

            $totalAds = $handler->getActiveCount();
            if ($totalAds === 0) {
                return;
            }

            $currentIndex = $state['sponsored_post_index'] ?? 0;
            $newData = [];
            $counter = 0;

            foreach ($data as $section) {
                $newData[] = $section;
                $counter++;

                // Inject an ad every 4 sections
                if ($counter % 4 === 0) {
                    // Use a descriptive index for the injected section ID
                    $uniqueId = $this->generateUniqueId('sponsored-posts', $page) . "_inj_" . $counter;
                    $adSection = $handler->handleSingle($uniqueId, $userId, $currentIndex % $totalAds);
                    
                    if ($adSection) {
                        $newData[] = $adSection;
                        $currentIndex++;
                    }
                }
            }

            $data = $newData;
            $state['sponsored_post_index'] = $currentIndex;
        } catch (\Throwable $e) {
            \Log::warning("[HomeScreenService] Failed to inject sponsored posts: " . $e->getMessage());
        }
    }

    private function generateAllCombinations(array $sections): array
    {
        $combinations = [];
        $top = [];
        $middle = [];
        $bottom = [];

        foreach ($sections as $type) {
            // Footer sections always go to bottom
            if ($type === 'footer-space') {
                $bottom[] = $this->meta($type);
                continue;
            }

            // Sections with service variations
            if (isset($this->variationMap[$type])) {
                foreach ($this->variationMap[$type]['services'] as $service) {
                    $middle[] = $this->meta($type, $service);
                }
                continue;
            }

            // Regular sections
            $middle[] = $this->meta($type);
        }

        // Note: shuffle happens in SectionStateManager with deterministic seed
        // Don't shuffle here - return ordered combinations
        return array_merge($top, $middle, $bottom);
    }

    private function buildSection(array $meta, ?int $userId, int $page = 1): ?array
    {
        // Validate meta structure
        if (!isset($meta['type'])) {
            Log::error('[HomeScreen] Invalid section metadata - missing type', ['meta' => $meta]);
            return null;
        }


        $handler = $this->handlers[$meta['type']] ?? null;
        if (!$handler) {
            \Log::warning('[HomeScreenService] No handler found', ['type' => $meta['type']]);
            return null;
        }

        // Generate truly unique ID with timestamp + random + page
        $uniqueId = $this->generateUniqueId($meta['type'], $page);

        try {
            if (!isset($this->variationMap[$meta['type']])) {
                $section = $handler->handle($uniqueId, $userId);
            } else {
                $method = $this->variationMap[$meta['type']]['method'];
                $section = $handler->$method(
                    $uniqueId,
                    $userId,
                    $meta['service'] ?? null
                );
            }
        } catch (\Throwable $e) {
            \Log::warning("[HomeScreenService] Failed to build section of type {$meta['type']}: " . $e->getMessage());
            return null;
        }

        // Ensure section includes the unique ID
        if (is_array($section)) {
            $section['id'] = $uniqueId;
        }

        return $section;
    }

    private function generateUniqueId(string $type, int $page = 1): string
    {
        // Generate unique ID with: type_timestamp_randomstring_page
        // This ensures uniqueness across all pages and requests
        $timestamp = round(microtime(true) * 10000); // milliseconds precision
        $randomStr = bin2hex(random_bytes(4)); // 8 char random string
        return "{$type}_{$timestamp}_{$randomStr}_{$page}";
    }

    private function meta(string $type, ?string $service = null): array
    {
        return [
            'type' => $type,
            'service' => $service,
            'id' => "{$type}_" . uniqid(),
        ];
    }

    private function getAllowedServiceIdentifiers(int $userId): array
    {
        // Get services that are disabled for this user
        $disabledServiceIds = UserService::where('user_id', $userId)
            ->where('is_enabled', false)
            ->pluck('service_id')
            ->toArray();
        
        // Get services that are enabled for this user (even if globally inactive)
        $userEnabledServiceIds = UserService::where('user_id', $userId)
            ->where('is_enabled', true)
            ->pluck('service_id')
            ->toArray();
        
        // Get all globally active services
        $activeServiceIds = Service::where('is_active', 1)
            ->pluck('id')
            ->toArray();
        
        // Combine: active services + user-enabled services (excluding disabled ones)
        $allowedServiceIds = array_unique(array_merge($activeServiceIds, $userEnabledServiceIds));
        
        // Remove disabled services
        $allowedServiceIds = array_diff($allowedServiceIds, $disabledServiceIds);
        
        // Get the full Service models to convert to identifiers
        if (empty($allowedServiceIds)) {
            return [];
        }
        
        $allowedServices = Service::whereIn('id', $allowedServiceIds)->get();
        
        // Get service ID to config identifier mapping
        $serviceIdToConfigMap = $this->getServiceIdToConfigIdentifierMap();
        
        // Convert service IDs to their config identifiers
        $identifiers = [];
        foreach ($allowedServices as $service) {
            // First try to map by service ID
            if (isset($serviceIdToConfigMap[$service->id])) {
                $configIdentifier = $serviceIdToConfigMap[$service->id];
                // Skip if mapping is null (service not in config)
                if ($configIdentifier === null) {
                    continue;
                }
                if (!in_array($configIdentifier, $identifiers)) {
                    $identifiers[] = $configIdentifier;
                }
            } else {
                // Fallback: try to derive from service name
                $name = $service->name ?? '';
                if (empty($name)) {
                    continue;
                }
                
                $variations = $this->generateIdentifierVariations($name);
                $configServiceIdentifiers = $this->getConfigServiceIdentifiers();
                
                foreach ($variations as $variation) {
                    if (in_array($variation, $configServiceIdentifiers)) {
                        if (!in_array($variation, $identifiers)) {
                            $identifiers[] = $variation;
                        }
                        break;
                    }
                }
            }
        }

        return $identifiers;
    }

    private function getServiceIdToConfigIdentifierMap(): array
    {
        return [
            1 => 'share-quotes',      // Shrestha Vichar (wise thoughts/quotes)
            2 => 'community',         // Community
            3 => 'bahujan-sahity',    // Bahujan Sahity (literature/publications)
            4 => 'business',          // Bussiness List
            5 => 'share-quotes',      // Boudh Aachar Sanhita (ethical quotes)
            6 => 'buddha-vihar',      // Buddh Vihar (monastery/community)
            7 => 'biodata-frame',     // Marriage Biodata
            8 => 'tourist-place',     // Tourist Places
            9 => 'program',           // Program Notifications
            13 => 'online-store',     // Online Store (product listings)
            14 => 'audiobook',        // Audio Book
            15 => 'political-quotes', // Political
            16 => 'biodata-frame',    // Matrimony
            17 => 'audiosong',        // Audio Song
        ];
    }

    private function generateIdentifierVariations(string $name): array
    {
        // Clean the name: remove special characters
        $cleanName = str_replace(["\n", "\r", "\t"], ' ', $name);
        $cleanName = trim($cleanName);
        $cleanName = strtolower($cleanName);
        
        $variations = [];
        
        // Variation 1: Replace spaces with hyphens
        $variations[] = str_replace(' ', '-', $cleanName);
        
        // Variation 2: Remove spaces entirely
        $variations[] = str_replace(' ', '', $cleanName);
        
        // Variation 3: Replace spaces with underscores
        $variations[] = str_replace(' ', '_', $cleanName);
        
        return $variations;
    }

    private function getConfigServiceIdentifiers(): array
    {
        $configMap = $this->variationMap;
        $identifiers = [];
        
        foreach ($configMap as $sectionType => $config) {
            if (isset($config['services']) && is_array($config['services'])) {
                foreach ($config['services'] as $serviceId) {
                    if (!in_array($serviceId, $identifiers)) {
                        $identifiers[] = $serviceId;
                    }
                }
            }
        }
        
        return $identifiers;
    }

    private function emptyResponse(int $page, ?string $sessionId = null): array
    {
        return [
            'data' => [],
            'current_page' => $page,
            'has_more' => false,
            'total' => 0,
            'session_id' => $sessionId,
            'meta' => [
                'is_new_session' => false,
                'shown_count' => 0,
                'cycle' => 1,
                'content_exhausted' => true,
                'unique_section_types' => 0,
            ]
        ];
    }
}

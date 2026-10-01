<?php

namespace Modules\Service\Config;

class ServiceSectionConfig
{
    /**
     * Get configuration for a specific service
     * 
     * @param string $serviceType
     * @return array|null
     */
    public static function getServiceConfig(string $serviceType): ?array
    {
        $configs = self::getAllConfigs();
        return $configs[$serviceType] ?? null;
    }

    /**
     * Get all service configurations
     * 
     * @return array
     */
    private static function getAllConfigs(): array
    {
        return [
            'general' => self::getGeneralConfig(),
            'community' => self::getCommunityConfig(),
            'biodata_frame' => self::getBiodataFrameConfig(),
            'business' => self::getBusinessConfig(),
            'tourism' => self::getTourismConfig(),
            'product' => self::getProductConfig(),
            'audiosong' => self::getAudiosongConfig(),
            'audiobook' => self::getAudiobookConfig(),
            'share_quotes' => self::getShareQuotesConfig(),
            'political_quotes' => self::getPoliticalQuotesConfig(),
            'buddha_acharsahinta' => self::getBuddhaAcharsahintaConfig(),
            'promotional_banners' => self::getPromotionalBannersConfig(),
            'promote_sangho_app' => self::getPromoteSanghoAppConfig(),
            'bahujan_sahitya' => self::getBahujanSahityaConfig(),
        ];
    }

    /**
     * General/Default Configuration
     * Shows a mix of all services
     */
    private static function getGeneralConfig(): array
    {
        return [
            'sections' => [
                'horizontal-card',       // Communities grid
                'hero-carousel',         // Programs carousel
                'business-cards',        // Business listings
                'template-grid',         // Quote templates
                'featured-destinations', // Featured tourist destinations
                'dual-row-scroll',       // Tourist places
                'modern-collage',        // Collage layout
                'focus-zoom',            // Single quote
                'album-quick-picks',     // Audio quick picks
                'expandable-cards',      // Multi-content (songs/books)
                'biodata-carousel',      // Biodata carousel
                'biodata-gallery',       // Biodata gallery
                'ad-banner',             // Advertisement
                'product-grid',          // Product listings
                'event-timeline',        // Event timeline
                'event-card',            // Single event card (Hero style)
                'sponsored-posts',       // Sponsored posts section
            ],
            'randomize' => true,
            'sections_per_page' => 30,
        ];
    }

    /**
     * Community Service Configuration
     */
    private static function getCommunityConfig(): array
    {
        return [
            'sections' => [
                'horizontal-card',       // Section 1
                'hero-carousel',         // Section 2 - Community Programs & Listing
                'event-card',            // Section 15 - Community Programs & Listing
                'event-timeline',        // Event timeline for community programs
                'album-quick-picks',     // Section 8 - Active Communities
                'ad-banner',             // Advertisement
            ],
            'randomize' => true,
            'sections_per_page' => 1,
        ];
    }

    /**
     * Biodata Frame Service Configuration
     */
    private static function getBiodataFrameConfig(): array
    {
        return [
            'sections' => [
                'biodata-carousel',
                'biodata-gallery',
            ],
            'randomize' => false,
            'sections_per_page' => 1,
        ];
    }

    /**
     * Business Service Configuration
     */
    private static function getBusinessConfig(): array
    {
        return [
            'sections' => [
                'horizontal-card',       // Section 1
                'hero-carousel',         // Section 2 - Business Listing
                'business-cards',        // Section 4 - Business List
                'event-card',            // Section 15 - Business Listing
            ],
            'randomize' => false,
            'sections_per_page' => 1,
        ];
    }

    /**
     * Tourism Service Configuration
     */
    private static function getTourismConfig(): array
    {
        return [
            'sections' => [
                'featured-destinations', // Featured tourist destinations
                'horizontal-card',       // Section 1 - Tourist Places
                'hero-carousel',         // Section 2 - Tourist Places
                'event-card',            // Section 15 - Tourist Places
                'ad-banner',             // Section 9
                'dual-row-scroll',       // Tourist places scroll
            ],
            'randomize' => false,
            'sections_per_page' => 1,
        ];
    }

    /**
     * Product Service Configuration
     */
    private static function getProductConfig(): array
    {
        return [
            'sections' => [
                'product-grid',          // Section 1 - Product Grid
            ],
            'randomize' => false,
            'sections_per_page' => 1,
        ];
    }

    /**
     * Audiosong Service Configuration
     */
    private static function getAudiosongConfig(): array
    {
        return [
            'sections' => [
                'business-cards',        // Section 4 - Audio Songs
                'dual-row-scroll',       // Section 7 - Audio Songs (Show title below image)
                'album-quick-picks',     // Section 8 - Audio Songs
                'expandable-cards',      // Section 11 - Audio Songs
                'template-grid',
            ],
            'randomize' => false,
            'sections_per_page' => 1,
        ];
    }

    /**
     * Audiobook Service Configuration
     */
    private static function getAudiobookConfig(): array
    {
        return [
            'sections' => [
                'business-cards',        // Section 4 - Audio Books
                'dual-row-scroll',       // Section 7 - Audio Books (Show title below image)
                'album-quick-picks',     // Section 8 - Audio Books
                'expandable-cards',      // Section 11 - Audio Books
                'template-grid',
            ],
            'randomize' => false,
            'sections_per_page' => 1,
        ];
    }

    /**
     * Share Quotes Service Configuration
     */
    private static function getShareQuotesConfig(): array
    {
        return [
            'sections' => [
                'dual-row-scroll',       // Section 7
                'modern-collage',        // Section 10
                'focus-zoom',            // Section 14
                'template-grid',
            ],
            'randomize' => false,
            'sections_per_page' => 1,
        ];
    }

    /**
     * Political Quotes Service Configuration
     */
    private static function getPoliticalQuotesConfig(): array
    {
        return [
            'sections' => [
                'dual-row-scroll',       // Section 7
                'modern-collage',        // Section 10
                'focus-zoom',            // Section 14
                'template-grid',
            ],
            'randomize' => false,
            'sections_per_page' => 1,
        ];
    }

    /**
     * Buddha Acharsahinta Service Configuration
     */
    private static function getBuddhaAcharsahintaConfig(): array
    {
        return [
            'sections' => [
                'info-cards',
            ],
            'randomize' => false,
            'sections_per_page' => 1,
        ];
    }

    /**
     * Promotional Banners Service Configuration
     */
    private static function getPromotionalBannersConfig(): array
    {
        return [
            'sections' => [
                'ad-banner',
            ],
            'randomize' => false,
            'sections_per_page' => 1,
        ];
    }

    /**
     * Promote Sangho App Service Configuration
     */
    private static function getPromoteSanghoAppConfig(): array
    {
        return [
            'sections' => [
                'invite-friends',
            ],
            'randomize' => false,
            'sections_per_page' => 1,
        ];
    }

    /**
     * Bahujan Sahitya Service Configuration
     */
    private static function getBahujanSahityaConfig(): array
    {
        return [
            'sections' => [
                'product-grid',
            ],
            'randomize' => false,
            'sections_per_page' => 1,
        ];
    }
}


<?php

namespace Modules\Service\Factories;

require_once __DIR__ . '/../Sections/HomeSections.php';

use Modules\Service\Sections\BaseSection;
use Illuminate\Support\Facades\Log;

class HomeScreenSectionFactory
{
    /**
     * Create a section instance based on type.
     *
     * @param string $type
     * @return BaseSection|null
     */
    public static function make(string $type): ?BaseSection
    {
        return match ($type) {
            'daily-highlights', 'today-on-sangho' => new \Modules\Service\Sections\DailyHighlightsSection(),
            'quote' => new \Modules\Service\Sections\QuoteSection(),
            'ad-banner' => new \Modules\Service\Sections\AdBannerSection(),
            'horizontal-card', 'grid-listing', 'trending-communities', 'trending-list', 'generic-listing' => new \Modules\Service\Sections\GridListingSection(),
            'product-grid', 'product-listing' => new \Modules\Service\Sections\ProductGridSection(),
            'business-cards', 'business-listing2', 'business-listing' => new \Modules\Service\Sections\BusinessCardsSection(),
            'hero-carousel', 'program-vichar', 'program-carousel', 'featured-carousel' => new \Modules\Service\Sections\HeroCarouselSection(),
            'biodata-carousel', 'marriage-biodata2' => new \Modules\Service\Sections\BiodataCarouselSection(),
            'album-quick-picks', 'audiosong-quick-picks', 'audiobook-quick-picks' => new \Modules\Service\Sections\AlbumQuickPicksSection(),
            'template-grid', 'quotes-speed-dial', 'quotes-grid' => new \Modules\Service\Sections\TemplateGridSection(),
            'expandable-cards', 'multi-content-section', 'community-content' => new \Modules\Service\Sections\ExpandableCardsSection(),
            'modern-collage' => new \Modules\Service\Sections\ModernCollageSection(),
            'biodata-gallery', 'marriage-biodata' => new \Modules\Service\Sections\BiodataGallerySection(),
            'event-timeline', 'happening-soon', 'upcoming-events' => new \Modules\Service\Sections\EventTimelineSection(),
            'event-card', 'upcoming-program', 'upcoming-programs' => new \Modules\Service\Sections\EventCardSection(),
            'dual-row-scroll', 'tourist-place', 'tourist-places' => new \Modules\Service\Sections\DualRowScrollSection(),
            'info-cards', 'boudh-aachar', 'buddhist-practices' => new \Modules\Service\Sections\InfoCardsSection(),
            'invite-friends', 'share-button' => new \Modules\Service\Sections\InviteFriendsSection(),
            'mini-player' => new \Modules\Service\Sections\MiniPlayerSection(),
            'footer-space' => new \Modules\Service\Sections\FooterSpaceSection(),
            default => self::handleUnknownType($type),
        };
    }

    /**
     * Handle unknown section type.
     */
    private static function handleUnknownType(string $type): ?BaseSection
    {
        Log::warning("Unknown section type in HomeScreenSectionFactory: {$type}");
        return null;
    }
}

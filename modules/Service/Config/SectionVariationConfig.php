<?php

namespace Modules\Service\Config;

class SectionVariationConfig
{
    public static function map(): array
    {
        return [
            'horizontal-card' => [
                'method' => 'createSpecificListing',
                'services' => ['community', 'business', 'tourist-place'],
            ],

            'hero-carousel' => [
                'method' => 'createSpecificCarousel',
                'services' => ['program', 'community', 'business', 'tourist-place'],
            ],

            'template-grid' => [
                'method' => 'createSpecificGrid',
                'services' => ['day-special', 'share-quotes', 'political-quotes', 'audiosong', 'audiobook', 'most-liked'],
            ],

            'product-grid' => [
                'method' => 'createSpecificGrid',
                'services' => ['online-store', 'bahujan-sahity'],
            ],

            'dual-row-scroll' => [
                'method' => 'createSpecificScroll',
                'services' => ['share-quotes', 'political-quotes', 'audiosong', 'audiobook', 'tourist-place', 'most-liked'],
            ],

            'modern-collage' => [
                'method' => 'createSpecificCollage',
                'services' => ['share-quotes', 'political-quotes', 'day-special','most-liked'],
            ],

            'event-card' => [
                'method' => 'createSpecificCard',
                'services' => ['program', 'community', 'business', 'tourist-place'],
            ],

            'focus-zoom' => [
                'method' => 'createSpecificZoom',
                'services' => ['share-quotes', 'political-quotes', 'day-special', 'most-liked'],
            ],

            'business-cards' => [
                'method' => 'createSpecificCards',
                'services' => ['business', 'audiosong', 'audiobook'],
            ],

            'album-quick-picks' => [
                'method' => 'createSpecificQuickPicks',
                'services' => ['community', 'audiobook', 'audiosong'],
            ],

            'expandable-cards' => [
                'method' => 'createSpecificExpandableCards',
                'services' => ['audiobook', 'audiosong'],
            ],

            'ad-banner' => [
                'method' => 'createSpecificAdBanner',
                'services' => ['program', 'community', 'business', 'tourist-place'],
            ],

            'biodata-carousel' => [
                'method' => 'createSpecificBiodataCarousel',
                'services' => ['biodata-frame'],
            ],

            'biodata-gallery' => [
                'method' => 'createSpecificBiodataGallery',
                'services' => ['biodata-frame'],
            ],

            'featured-destinations' => [
                'method' => 'createSpecificDestinations',
                'services' => ['tourist-place', 'buddha-vihar'],
            ],
        ];
    }
}
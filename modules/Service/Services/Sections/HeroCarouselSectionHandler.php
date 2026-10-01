<?php

namespace Modules\Service\Services\Sections;

use Modules\Service\Services\Sections\Contracts\SectionHandlerInterface;
use Modules\Service\Services\Sections\DataFetchers\ProgramDataFetcher;
use Modules\Service\Services\Sections\HorizontalCardSectionHandler;

/**
 * Hero Carousel Section Handler
 * 
 * Handles full-width carousel for:
 * - Programs
 * - Communities
 * - Tourist Places
 * - Businesses
 */
class HeroCarouselSectionHandler implements SectionHandlerInterface
{
    private HorizontalCardSectionHandler $horizontalCardHandler;

    public function __construct()
    {
        $this->horizontalCardHandler = new HorizontalCardSectionHandler();
    }

    /**
     * {@inheritdoc}
     */
    public function handle(string $sectionId, ?int $userId = null): ?array
    {
        // Randomly rotate between 4 service types for Hero Carousel
        // 0 = Program, 1 = Community, 2 = Tourist Place, 3 = Business
        $carouselType = rand(0, 3);

        $data = $this->horizontalCardHandler->createSpecificListing($sectionId, $userId, 'business');
        $data['type'] = $this->getSectionType();
        return $data;
        
        switch ($carouselType) {
            case 0:
                return [
                    'id' => $sectionId,
                    'type' => $this->getSectionType(),
                    'service_type' => 'program',
                    'data' => ProgramDataFetcher::fetch($userId),
                ];
            case 1:
                $data = $this->horizontalCardHandler->createSpecificListing($sectionId, $userId, 'community');
                $data['type'] = $this->getSectionType();
                return $data;
            case 2:
                $data = $this->horizontalCardHandler->createSpecificListing($sectionId, $userId, 'tourist-place');
                $data['type'] = $this->getSectionType();
                return $data;
            case 3:
                $data = $this->horizontalCardHandler->createSpecificListing($sectionId, $userId, 'business');
                $data['type'] = $this->getSectionType();
                return $data;
            default:
                return [
                    'id' => $sectionId,
                    'type' => $this->getSectionType(),
                    'service_type' => 'program',
                    'data' => ProgramDataFetcher::fetch($userId),
                ];
        }
    }

    /**
     * Create a specific carousel type (used by multi-service sections)
     * 
     * @param string $sectionId
     * @param int|null $userId
     * @param string $carouselType One of: program, community, tourist-place, business
     * @return array|null
     */
    public function createSpecificCarousel(string $sectionId, ?int $userId, string $carouselType): ?array
    {
        switch ($carouselType) {
            case 'program':
                return [
                    'id' => $sectionId,
                    'type' => $this->getSectionType(),
                    'service_type' => 'program',
                    'data' => ProgramDataFetcher::fetch($userId),
                ];
            case 'community':
            case 'business':
            case 'tourist-place':
                $data = $this->horizontalCardHandler->createSpecificListing($sectionId, $userId, $carouselType);
                $data['type'] = $this->getSectionType();
                return $data;
            default:
                return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionType(): string
    {
        return 'hero-carousel';
    }
}

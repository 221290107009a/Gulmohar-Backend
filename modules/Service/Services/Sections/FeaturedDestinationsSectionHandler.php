<?php

namespace Modules\Service\Services\Sections;

use Modules\Service\Services\Sections\Contracts\SectionHandlerInterface;
use Modules\Service\Services\Sections\DataFetchers\TouristPlacesDataFetcher;
use Modules\Service\Services\Sections\DataFetchers\BuddhaViharDataFetcher;

/**
 * Featured Destinations Section Handler
 * 
 * Handles featured tourist places/destinations section
 */
class FeaturedDestinationsSectionHandler implements SectionHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle(string $sectionId, ?int $userId = null): ?array
    {
        return $this->createSpecificDestinations($sectionId, $userId, 'tourist-place');
    }

    /**
     * Create a specific destinations section for a service type
     * 
     * @param string $sectionId
     * @param int|null $userId
     * @param string $serviceType One of: tourist-place, buddha-vihar
     * @return array|null
     */
    public function createSpecificDestinations(string $sectionId, ?int $userId, string $serviceType): ?array
    {
        try {
            $data = match($serviceType) {
                'tourist-place' => TouristPlacesDataFetcher::fetch(),
                'buddha-vihar' => BuddhaViharDataFetcher::fetch(),
                default => null
            };

            if (!$data) {
                return null;
            }
            
            return [
                'id' => $sectionId,
                'type' => $this->getSectionType(),
                'service_type' => $serviceType,
                'data' => $data,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionType(): string
    {
        return 'featured-destinations';
    }
}

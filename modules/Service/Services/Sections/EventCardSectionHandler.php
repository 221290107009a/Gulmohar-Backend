<?php

namespace Modules\Service\Services\Sections;

use Modules\Service\Services\Sections\Contracts\SectionHandlerInterface;
use Modules\Service\Services\Sections\DataFetchers\ProgramDataFetcher;

/**
 * Event Card Section Handler
 * 
 * Handles single event card display
 */
class EventCardSectionHandler implements SectionHandlerInterface
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
        // Randomly rotate between service types for Event Card if not specified
        // 0 = Program, 1 = Community, 2 = Tourist Place, 3 = Business
        $cardType = rand(0, 3);
        
        switch ($cardType) {
            case 0:
                $data = ProgramDataFetcher::fetch($userId);
                $data['title'] = 'Upcoming Programs';
                $data['subtitle'] = 'Join our upcoming community events';
                return [
                    'id' => $sectionId,
                    'type' => $this->getSectionType(),
                    'service_type' => 'program',
                    'data' => $data,
                ];
            case 1:
                return $this->createSpecificCard($sectionId, $userId, 'community');
            case 2:
                return $this->createSpecificCard($sectionId, $userId, 'tourist-place');
            case 3:
                return $this->createSpecificCard($sectionId, $userId, 'business');
            default:
                $data = ProgramDataFetcher::fetch($userId);
                $data['title'] = 'Upcoming Programs';
                $data['subtitle'] = 'Join our upcoming community events';
                return [
                    'id' => $sectionId,
                    'type' => $this->getSectionType(),
                    'service_type' => 'program',
                    'data' => $data,
                ];
        }
    }

    /**
     * Create a specific card type (used by multi-service sections)
     * 
     * @param string $sectionId
     * @param int|null $userId
     * @param string $serviceType One of: program, community, tourist-place, business
     * @return array|null
     */
    public function createSpecificCard(string $sectionId, ?int $userId, string $serviceType): ?array
    {
        switch ($serviceType) {
            case 'program':
                $data = ProgramDataFetcher::fetch($userId);
                $data['title'] = 'Upcoming Programs';
                $data['subtitle'] = 'Join our upcoming community events';
                return [
                    'id' => $sectionId,
                    'type' => $this->getSectionType(),
                    'service_type' => 'program',
                    'data' => $data,
                ];
            case 'community':
            case 'business':
            case 'tourist-place':
                $data = $this->horizontalCardHandler->createSpecificListing($sectionId, $userId, $serviceType);
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
        return 'event-card';
    }
}

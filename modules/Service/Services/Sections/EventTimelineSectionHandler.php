<?php

namespace Modules\Service\Services\Sections;

use Modules\Service\Services\Sections\Contracts\SectionHandlerInterface;
use Modules\Service\Services\Sections\DataFetchers\ProgramDataFetcher;

/**
 * Event Timeline Section Handler
 * 
 * Handles timeline-style event display
 */
class EventTimelineSectionHandler implements SectionHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle(string $sectionId, ?int $userId = null): ?array
    {
        return [
            'id' => $sectionId,
            'type' => $this->getSectionType(),
            'data' => ProgramDataFetcher::fetch($userId),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionType(): string
    {
        return 'event-timeline';
    }
}

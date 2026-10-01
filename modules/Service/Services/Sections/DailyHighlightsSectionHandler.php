<?php

namespace Modules\Service\Services\Sections;

use Modules\Service\Services\Sections\Contracts\SectionHandlerInterface;

/**
 * Daily Highlights Section Handler
 * 
 * Handles the "Today on Sangho" section
 */
class DailyHighlightsSectionHandler implements SectionHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle(string $sectionId, ?int $userId = null): ?array
    {
        return [
            'id' => $sectionId,
            'type' => $this->getSectionType(),
            'data' => [],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionType(): string
    {
        return 'today-on-sangho';
    }
}

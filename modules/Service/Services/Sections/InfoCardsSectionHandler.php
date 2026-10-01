<?php

namespace Modules\Service\Services\Sections;

use Modules\Service\Services\Sections\Contracts\SectionHandlerInterface;

/**
 * Info Cards Section Handler
 * 
 * Handles information cards section
 */
class InfoCardsSectionHandler implements SectionHandlerInterface
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
        return 'info-cards';
    }
}

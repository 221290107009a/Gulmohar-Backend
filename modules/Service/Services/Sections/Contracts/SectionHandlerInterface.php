<?php

namespace Modules\Service\Services\Sections\Contracts;

/**
 * Section Handler Interface
 * 
 * All section handlers must implement this interface to ensure consistency
 * across different section types.
 */
interface SectionHandlerInterface
{
    /**
     * Handle section generation
     * 
     * @param string $sectionId Unique identifier for this section instance
     * @param int|null $userId Optional user ID for personalized content
     * @return array|null Section data array or null if section cannot be generated
     */
    public function handle(string $sectionId, ?int $userId = null): ?array;

    /**
     * Get the section type identifier
     * 
     * @return string Section type (e.g., 'hero-carousel', 'product-grid')
     */
    public function getSectionType(): string;
}

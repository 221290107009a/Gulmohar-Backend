<?php

namespace Modules\Service\Services\Sections;

use Modules\Service\Services\Sections\Contracts\SectionHandlerInterface;
use Modules\Service\Services\Sections\DataFetchers\TemplateDataFetcher;

/**
 * Modern Collage Section Handler
 * 
 * Handles modern collage layout for trending quotes
 */
class ModernCollageSectionHandler implements SectionHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle(string $sectionId, ?int $userId = null): ?array
    {
        return $this->createSpecificCollage($sectionId, $userId, 'share-quotes');
    }

    /**
     * Create a specific collage type (used by multi-service sections)
     * 
     * @param string $sectionId
     * @param int|null $userId
     * @param string $collageType One of: share-quotes, political-quotes, day-special
     * @return array|null
     */
    public function createSpecificCollage(string $sectionId, ?int $userId, string $collageType): ?array
    {
        
        $data = null;
        $serviceType = $collageType;

        switch ($collageType) {
            case 'share-quotes':
                $data = TemplateDataFetcher::fetchQuotesByRandomCategory($userId);
                break;
            case 'political-quotes':
                $data = TemplateDataFetcher::fetchPoliticalTemplates($userId);
                break;
            case 'day-special':
                $data = TemplateDataFetcher::fetchDaySpecialTemplates($userId);
                break;
            case 'most-liked':
                $data = TemplateDataFetcher::fetchMostLikedQuotes($userId);
                break;
            default:
                return null;
        }

        return [
            'id' => $sectionId,
            'type' => $this->getSectionType(),
            'service_type' => $serviceType,
            'data' => [
                'items' => $data['templates'] ?? [],
                'title' => $data['title'] ?? '',
                'subtitle' => $data['subtitle'] ?? '',
                'categoryId' => $data['categoryId'] ?? null
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionType(): string
    {
        return 'modern-collage';
    }
}

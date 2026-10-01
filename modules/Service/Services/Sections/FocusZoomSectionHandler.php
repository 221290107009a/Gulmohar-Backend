<?php

namespace Modules\Service\Services\Sections;

use Modules\Service\Services\Sections\Contracts\SectionHandlerInterface;
use Modules\Service\Services\Sections\DataFetchers\TemplateDataFetcher;

/**
 * Focus Zoom Section Handler
 * 
 * Handles the focus zoom section with templates
 */
class FocusZoomSectionHandler implements SectionHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle(string $sectionId, ?int $userId = null): ?array
    {
        return $this->createSpecificZoom($sectionId, $userId, 'share-quotes');
    }

    /**
     * Create a specific zoom type (used by multi-service sections)
     * 
     * @param string $sectionId
     * @param int|null $userId
     * @param string $zoomType One of: share-quotes, political-quotes, day-special
     * @return array|null
     */
    public function createSpecificZoom(string $sectionId, ?int $userId, string $zoomType): ?array
    {
        $data = null;
        $serviceType = $zoomType;

        switch ($zoomType) {
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
                'categoryId' => (string)($data['categoryId'] ?? '')
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionType(): string
    {
        return 'focus-zoom';
    }
}

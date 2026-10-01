<?php

namespace Modules\Service\Services\Sections;

use Modules\Service\Services\Sections\Contracts\SectionHandlerInterface;
use Modules\Service\Services\Sections\DataFetchers\TemplateDataFetcher;

/**
 * Template Grid Section Handler
 * 
 * Handles template/quote grid with day-special logic
 */
class TemplateGridSectionHandler implements SectionHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle(string $sectionId, ?int $userId = null): ?array
    {
        $data = TemplateDataFetcher::fetchDaySpecialTemplates($userId);
        return [
            'id' => $sectionId,
            'type' => $this->getSectionType(),
            'service_type' => 'day-special',
            'data' => [
                'items' => $data['templates'] ?? [],
                'title' => $data['title'] ?? '',
                'subtitle' => $data['subtitle'] ?? '',
                'categoryId' => $data['categoryId'] ?? null
            ],
        ];
    }

    /**
     * Create a specific grid type (used by multi-service sections)
     * 
     * @param string $sectionId
     * @param int|null $userId
     * @param string $gridType One of: day-special, share-quotes, political-quotes, audiosong, most-liked, random-category
     * @return array|null
     */
    public function createSpecificGrid(string $sectionId, ?int $userId, string $gridType): ?array
    {
        $data = null;
        $serviceType = $gridType;

        switch ($gridType) {
            case 'day-special':
                $data = TemplateDataFetcher::fetchDaySpecialTemplates($userId);
                break;
            case 'share-quotes':
                $data = TemplateDataFetcher::fetchQuotesByRandomCategory($userId);
                //$data = TemplateDataFetcher::fetchQuotesByCategory(43, 'Share Quotes');
                break;
            case 'political-quotes':
                $data = TemplateDataFetcher::fetchPoliticalTemplates($userId);
                break;
            case 'audiosong':
                $data = TemplateDataFetcher::fetchAudioSongsForGrid();
                break;
            // case 'audiobook':
            //     $data = TemplateDataFetcher::fetchAudioBooksForGrid();
            //     break;
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
        return 'template-grid';
    }
}

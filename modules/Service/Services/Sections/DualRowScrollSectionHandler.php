<?php

namespace Modules\Service\Services\Sections;

use Modules\Service\Services\Sections\Contracts\SectionHandlerInterface;
use Modules\Service\Services\Sections\DataFetchers\TemplateDataFetcher;

/**
 * Dual Row Scroll Section Handler
 * 
 * Handles dual synchronized row scrolling
 */
class DualRowScrollSectionHandler implements SectionHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle(string $sectionId, ?int $userId = null): ?array
    {
        return $this->createSpecificScroll($sectionId, $userId, 'tourist-place');
    }

    /**
     * Create a specific scroll type (used by multi-service sections)
     * 
     * @param string $sectionId
     * @param int|null $userId
     * @param string $scrollType One of: share-quotes, political-quotes, audiosong, tourist-place
     * @return array|null
     */
    public function createSpecificScroll(string $sectionId, ?int $userId, string $scrollType): ?array
    {
        $data = null;
        $serviceType = $scrollType;

        switch ($scrollType) {
            case 'share-quotes':
                $data = TemplateDataFetcher::fetchQuotesByRandomCategory($userId);
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
            case 'tourist-place':
                $data = $this->fetchTouristPlaces();
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
     * Fetch popular tourist places
     * 
     * @return array
     */
    private function fetchTouristPlaces(): array
    {
        try {
            $places = \Modules\TouristPlace\Entities\TouristPlace::where('is_active', 1)
                ->where('is_popular', 1)
                ->inRandomOrder()
                ->limit(10)
                ->get();

            $items = $places->map(function($place) {
                return [
                    'id' => $place->id,
                    'name' => $place->name,
                    'title' => $place->name,
                    'city' => $place->city,
                    'district' => $place->district,
                    'state' => $place->state,
                    'latitude' => $place->latitude ?? '26.65', // Default placeholder if missing
                    'longitude' => $place->longitude ?? '84.97', // Default placeholder if missing
                    'description' => $place->short_description ?? $place->description ?? 'Explore this amazing destination',
                    'mainImage' => $place->logo->path ?? '',
                    'service_type' => 'tourist-place'
                ];
            });

            return [
                'templates' => $items,
                'title' => 'Popular Tourist Places',
                'subtitle' => 'Discover amazing destinations'
            ];
        } catch (\Exception $e) {
            return ['templates' => [], 'title' => 'Popular Tourist Places', 'subtitle' => 'Discover amazing destinations'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionType(): string
    {
        return 'dual-row-scroll';
    }
}

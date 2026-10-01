<?php

namespace Modules\Service\Services\Sections;

use Modules\Service\Services\Sections\Contracts\SectionHandlerInterface;
use Modules\Service\Services\Sections\DataFetchers\BiodataDataFetcher;

/**
 * Biodata Carousel Section Handler
 * 
 * Handles biodata frames in carousel view
 */
class BiodataCarouselSectionHandler implements SectionHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle(string $sectionId, ?int $userId = null): ?array
    {
        try {
            $data = BiodataDataFetcher::fetch();
            
            // Ensure required fields exist
            if (empty($data)) {
                $data = [
                    'biodata_frames' => [],
                    'title' => 'Marriage Biodata',
                    'subtitle' => 'Choose a design & create your biodata'
                ];
            }
            
            if (!isset($data['title'])) {
                $data['title'] = 'Marriage Biodata';
            }
            if (!isset($data['subtitle'])) {
                $data['subtitle'] = 'Choose a design & create your biodata';
            }
            
            return [
                'id' => $sectionId,
                'type' => $this->getSectionType(),
                'data' => $data,
            ];
        } catch (\Exception $e) {
            return [
                'id' => $sectionId,
                'type' => $this->getSectionType(),
                'data' => [
                    'biodata_frames' => [],
                    'title' => 'Marriage Biodata',
                    'subtitle' => 'Choose a design & create your biodata'
                ],
            ];
        }
    }

    /**
     * Create a specific biodata carousel (used by multi-service sections)
     * 
     * @param string $sectionId
     * @param int|null $userId
     * @param string $carouselType One of: biodata-frame
     * @return array|null
     */
    public function createSpecificBiodataCarousel(string $sectionId, ?int $userId, string $carouselType): ?array
    {
        try {
            $data = BiodataDataFetcher::fetch();
            
            // Ensure required fields exist
            if (empty($data)) {
                $data = [
                    'biodata_frames' => [],
                    'title' => 'Marriage Biodata',
                    'subtitle' => 'Choose a design & create your biodata'
                ];
            }
            
            if (!isset($data['title'])) {
                $data['title'] = 'Marriage Biodata';
            }
            if (!isset($data['subtitle'])) {
                $data['subtitle'] = 'Choose a design & create your biodata';
            }
            
            return [
                'id' => $sectionId,
                'type' => $this->getSectionType(),
                'service_type' => $carouselType,
                'data' => $data,
            ];
        } catch (\Exception $e) {
            return [
                'id' => $sectionId,
                'type' => $this->getSectionType(),
                'service_type' => $carouselType,
                'data' => [
                    'biodata_frames' => [],
                    'title' => 'Marriage Biodata',
                    'subtitle' => 'Choose a design & create your biodata'
                ],
            ];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionType(): string
    {
        return 'biodata-carousel';
    }
}

<?php

namespace Modules\Service\Services\Sections;

use Modules\Service\Services\Sections\Contracts\SectionHandlerInterface;
use Modules\Service\Services\Sections\DataFetchers\BiodataDataFetcher;

/**
 * Biodata Gallery Section Handler
 * 
 * Handles biodata frames in gallery view
 */
class BiodataGallerySectionHandler implements SectionHandlerInterface
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
     * Create a specific biodata gallery (used by multi-service sections)
     * 
     * @param string $sectionId
     * @param int|null $userId
     * @param string $galleryType One of: biodata-frame
     * @return array|null
     */
    public function createSpecificBiodataGallery(string $sectionId, ?int $userId, string $galleryType): ?array
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
            
            return [
                'id' => $sectionId,
                'type' => $this->getSectionType(),
                'service_type' => $galleryType,
                'data' => $data,
            ];
        } catch (\Exception $e) {
            return [
                'id' => $sectionId,
                'type' => $this->getSectionType(),
                'service_type' => $galleryType,
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
        return 'biodata-gallery';
    }
}

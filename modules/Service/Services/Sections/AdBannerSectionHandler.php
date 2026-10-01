<?php

namespace Modules\Service\Services\Sections;

use Modules\Slider\Entities\Slider;
use Modules\Service\Services\Sections\Contracts\SectionHandlerInterface;

/**
 * Ad Banner Section Handler
 * 
 * Handles the ad banner section
 */
class AdBannerSectionHandler implements SectionHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle(string $sectionId, ?int $userId = null): ?array
    {
        return [
            'id' => $sectionId,
            'type' => $this->getSectionType(),
            'data' => $this->getBannerData(),
        ];
    }

    /**
     * Create a specific ad banner (used by multi-service sections)
     * 
     * @param string $sectionId
     * @param int|null $userId
     * @param string $bannerType One of: program, community, business, tourist-place
     * @return array|null
     */
    public function createSpecificAdBanner(string $sectionId, ?int $userId, string $bannerType): ?array
    {
        return [
            'id' => $sectionId,
            'type' => $this->getSectionType(),
            'service_type' => $bannerType,
            'data' => $this->getBannerData(),
        ];
    }

    /**
     * Get the banner data from Slider ID 2
     * 
     * @return array
     */
    private function getBannerData(): array
    {
        $adSlider = Slider::where('id', 2)->first();
        $sliderUrls = [];

        if ($adSlider) {
            foreach ($adSlider->slides as $slider) {
                if ($slider->file) {
                    $sliderUrls[] = [
                        'image' => $slider->file->path,
                        'action_url' => $slider->call_to_action_url
                    ];
                }
            }
        }

        return [
            'success' => $adSlider ? true : false,
            'slides' => $sliderUrls,
            'total_slides' => count($sliderUrls)
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionType(): string
    {
        return 'ad-banner';
    }
}

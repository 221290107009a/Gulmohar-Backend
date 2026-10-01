<?php

namespace Modules\Service\Services\Sections\DataFetchers;

use Modules\BiodataFrame\Entities\BiodataFrame;

/**
 * Biodata Data Fetcher
 * 
 * Handles fetching biodata frame data
 */
class BiodataDataFetcher
{
    /**
     * Fetch biodata frames for home screen
     * 
     * @return array
     */
    public static function fetch(): array
    {
        try {
            $biodataFrames = BiodataFrame::where('is_active', 1)
                ->inRandomOrder()
                ->limit(10)
                ->get();
            
            foreach ($biodataFrames as $frame) {
                $frame->id = $frame->id;
                if ($frame->logo && $frame->logo->path) {
                    $frame->frame_url = $frame->logo->path;
                }
                if ($frame->list_image && $frame->list_image->path) {
                    $frame->fill_frame_url = $frame->list_image->path;
                }
            }
            
            $titleData = self::generateDynamicTitle();
            
            return [
                'biodata_frames' => $biodataFrames,
                'title' => $titleData['title'],
                'subtitle' => $titleData['subtitle']
            ];
        } catch (\Exception $e) {
            return [
                'biodata_frames' => [],
                'title' => 'Biodata Frames',
                'subtitle' => 'Beautiful frames for your profile'
            ];
        }
    }

    /**
     * Generate dynamic title for biodata sections
     *
     * @return array
     */
    private static function generateDynamicTitle(): array
    {
        $titles = [
            ['title' => '✨ Aapka Perfect Biodata', 'subtitle' => 'Profile ko banaye aur bhi khaas'],
            ['title' => '🖼️ Simple & Stylish Frames', 'subtitle' => 'Ek hi frame mein saari kahani'],
            ['title' => '🌟 Clean, Clear, Classy', 'subtitle' => 'First impression ko banaye strong'],
            ['title' => '💫 Modern Look, Traditional Feel', 'subtitle' => 'Smart biodata, simple presentation'],
            ['title' => '🎨 Photo + Details, Perfect Match', 'subtitle' => 'Sab kuch ek hi jagah, sundar tareeke se'],
            ['title' => '📄 Ready‑to‑Share Biodata', 'subtitle' => 'WhatsApp par bhejne ke liye perfect'],
        ];

        return $titles[array_rand($titles)];
    }
}

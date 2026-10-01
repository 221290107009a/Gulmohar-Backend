<?php

namespace Modules\Slider\Http\Controllers\Admin;

use Modules\Slider\Entities\Slider;
use Modules\Admin\Traits\HasCrudActions;
use Modules\Slider\Http\Requests\SaveSliderRequest;
use Illuminate\Http\JsonResponse;

class SliderController
{
    use HasCrudActions;

    /**
     * Model for the resource.
     *
     * @var string
     */
    protected $model = Slider::class;

    /**
     * Label of the resource.
     *
     * @var string
     */
    protected $label = 'slider::sliders.slider';

    /**
     * View path of the resource.
     *
     * @var string
     */
    protected $viewPath = 'slider::admin.sliders';

    /**
     * Form requests for the resource.
     *
     * @var array
     */
    protected $validation = SaveSliderRequest::class;

    public function sliderItem(){
        $slider = \Modules\Slider\Entities\Slider::with('slides.file')->get()->toArray();
        if ($slider) {
            return response()->json([
                'success' => true,
                'slider' => $slider,
            ], 200);            
        } else{
            return response()->json([
                'success' => false,
                'message' => 'Slider not found.',
            ], 401);
        }
    }

    public function adBannerSlider(): JsonResponse
    {
        try {
            $adSlider = Slider::where('id', 2)->first();

            if (!$adSlider) {
                return response()->json([
                    'success' => false,
                    'message' => 'Slider not found.',
                ], 404);
            }

            $sliderUrls = [];
            
            foreach ($adSlider->slides as $slider) {
                if ($slider->file) {
                    $sliderUrls[] = [
                        'image' => $slider->file->path,
                        'action_url' => $slider->call_to_action_url
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'slides' => $sliderUrls,
                'total_slides' => count($sliderUrls)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching slider data',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function storeSlider(): JsonResponse
    {
        try {
            // Fetch all sliders
            $allSliders = \Modules\Slider\Entities\Slider::with('slides.file')->get();

            if ($allSliders->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No sliders found.',
                ], 404);
            }

            $sliderUrls = [];
            
            foreach ($allSliders as $slider) {
                foreach ($slider->slides as $slide) {
                    if ($slide->file) {
                        $sliderUrls[] = [
                            'id' => $slide->id,
                            'image' => $slide->file->path,
                            'title' => $slide->caption_1,
                            'content' => $slide->caption_2,
                            'action_url' => $slide->call_to_action_url
                        ];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => $sliderUrls,
                'total_slides' => count($sliderUrls)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching store slider data',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}

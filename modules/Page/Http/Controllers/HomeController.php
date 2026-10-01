<?php

namespace Modules\Page\Http\Controllers;

use Illuminate\Http\Response;
use Modules\Media\Entities\File;
use Illuminate\Support\Facades\Cache;
use Modules\Slider\Entities\Slider;
use Modules\Menu\Entities\Menu;
use Modules\Service\Entities\Service;

class HomeController
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $slider = Slider::findWithSlides(1) ?? Slider::first();
        $sliders = $slider ? $slider->slides : collect();
        $footerMenuOne = $this->getFooterMenuOne();
        $footerMenuTwo = $this->getFooterMenuTwo();
        $logo = $this->getAdminLogo();
        $services = Service::where('is_active', true)->orderBy('position')->get();
        $solutions = [];
        for ($i = 1; $i <= 30; $i++) {
            if (setting("storefront_intelligent_digital_solutions_item_{$i}_active")) {
                $imageSetting = setting("storefront_intelligent_digital_solutions_item_{$i}_images", []);
                
                // Handle different storage formats (JSON, Serialized, or Array)
                $imageIds = [];
                if (is_array($imageSetting)) {
                    $imageIds = $imageSetting;
                } elseif (!empty($imageSetting)) {
                    $decoded = json_decode($imageSetting, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $imageIds = $decoded;
                    } else {
                        // Might be comma separated or serialized
                        $imageIds = is_string($imageSetting) ? explode(',', $imageSetting) : (array) $imageSetting;
                    }
                }
                
                $solutions[] = (object) [
                    'title' => setting("storefront_intelligent_digital_solutions_item_{$i}_title"),
                    'desc' => setting("storefront_intelligent_digital_solutions_item_{$i}_desc"),
                    'images' => File::whereIn('id', array_filter((array) $imageIds))->get(),
                ];
            }
        }       

        $solutions = collect($solutions)->shuffle()->take(3);

        return view('storefront::public.home.app', compact('logo', 'sliders', 'footerMenuOne', 'footerMenuTwo', 'services', 'solutions'));
    }

    public function getAdminLogo()
    {
        return $this->getMedia(setting('admin_logo'))->path;
    }

    public function getMedia($fileId)
    {
        return Cache::rememberForever(md5("files.{$fileId}"), function () use ($fileId) {
            return File::findOrNew($fileId);
        });
    }

    private function getFooterMenuOne()
    {
        return $this->getFooterMenu(setting('storefront_footer_menu_one'));
    }

    private function getFooterMenu($menuId)
    {
        if ($menuId) {
            return Menu::for($menuId);
        }
    }

    private function getFooterMenuTwo()
    {
        return $this->getFooterMenu(setting('storefront_footer_menu_two'));
    }
}

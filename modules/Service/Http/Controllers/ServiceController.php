<?php

namespace Modules\Service\Http\Controllers;

use Modules\Service\Entities\Service;
use Modules\User\Entities\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\User\Entities\User;
use Intervention\Image\Facades\Image;

class ServiceController
{
    public function apiServices(Request $request)
    {
        $type = $request->type ?? 'app';

        $services = Service::where(function($q) use ($type) {
            if ($type == 'web') {
                $q->where('is_active_web', 1);
            } else {
                $q->where('is_active', 1);
            }
        })->orderBy('position')->get();
        
        if ($services->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Services not found',
            ], 404);
        }
        
        // Get authenticated user ID from request or auth
        $userId = $request->user_id ?? auth()->id();
        
        $disabledServiceIds = [];
        $disabledServices = [];
        
        // Filter services based on user's enabled services
        if ($userId) {
            // Get services that are disabled for this user
            $disabledServiceIds = UserService::where('user_id', $userId)
                ->where('is_enabled', false)
                ->pluck('service_id')
                ->toArray();
            
            // Get services that are enabled for this user (even if globally inactive)
            $enabledServiceIds = UserService::where('user_id', $userId)
                ->where('is_enabled', true)
                ->pluck('service_id')
                ->toArray();
            
            // Fetch inactive services that are enabled for this user
            if (!empty($enabledServiceIds)) {
                $inactiveEnabledServices = Service::where(function($q) use ($type) {
                        if ($type == 'web') {
                            $q->where('is_active_web', 0);
                        } else {
                            $q->where('is_active', 0);
                        }
                    })
                    ->whereIn('id', $enabledServiceIds)
                    ->orderBy('position')
                    ->get();
                
                // Merge inactive enabled services with active services
                $services = $services->merge($inactiveEnabledServices)->sortBy('position')->values();
            }
            
            // Get disabled services details
            if (!empty($disabledServiceIds)) {
                $disabledServices = Service::whereIn('id', $disabledServiceIds)
                    ->orderBy('position')
                    ->get()
                    ->map(function ($service) {
                        // Clean the name: remove newlines, trim, convert to lowercase, replace spaces with underscores
                        $cleanName = str_replace(["\\n", "\n", "\r", "\t"], ' ', $service->name);
                        $cleanName = trim($cleanName);
                        $cleanName = strtolower(str_replace(' ', '_', $cleanName));
                        
                        return [
                            'id' => $service->id,
                            'name' => $cleanName,
                        ];
                    });
            }
            
            // Remove disabled services from the collection
            $services = $services->filter(function ($service) use ($disabledServiceIds) {
                return !in_array($service->id, $disabledServiceIds);
            })->values(); // Reset array keys
        }
        
        foreach ($services as $service) {
            $service->icon = $service->logo->path ?? '';
            unset($service->files);
        }

        $allService = [
            "id" => 'last',
            "position" => 'last',
            "name" => "All",
            "icon" => asset('storage/images/select-all.png'),
            "gradient_colors" => "['#FDFCFB', '#E2D1C3']",
            "is_active" => true,
            "link_url" => "",
            "webpage_url" => "",
        ];
        $services->push((object) $allService);

        return response()->json([
            'status' => true,            
            'data' => $services,
            'disabled_services' => $disabledServices,
        ], 200);
    }
}

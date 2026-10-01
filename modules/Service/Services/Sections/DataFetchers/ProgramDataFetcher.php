<?php

namespace Modules\Service\Services\Sections\DataFetchers;

use Modules\ProgramNotification\Entities\ProgramNotification;
use Modules\ProgramNotification\Entities\ProgramRegistration;

/**
 * Program Data Fetcher
 * 
 * Handles fetching and formatting program notification data
 */
class ProgramDataFetcher
{
    /**
     * Get program notifications with user registration status
     * 
     * @param int|null $userId
     * @return array
     */
    public static function fetch(?int $userId = null): array
    {
        try {
            $programs = ProgramNotification::where('is_active', 1)
                ->where(function($query) {
                    $query->whereDate('start_date', '>=', \Carbon\Carbon::today())
                        ->orWhereDate('end_date', '>=', \Carbon\Carbon::today());
                })
                ->with(['community'])
                ->latest()
                ->limit(10)
                ->get();
            
            $programsData = [];
            foreach ($programs as $program) {
                // Initialize default values
                $is_registered = 0;
                $is_attended = 0;
                $attended_at = null;
                $is_paid = 0;

                if ($userId) {
                    $registration = ProgramRegistration::where('program_notification_id', $program->id)
                        ->where('user_id', $userId)
                        ->first();
                    
                    if ($registration) {
                        $is_registered = 1;
                        $is_attended = $registration->is_attended ? 1 : 0;
                        $attended_at = $registration->attended_at;
                        $is_paid = $registration->is_paid ? 1 : 0;
                    }
                }

                $programsData[] = [
                    'id' => $program->id,
                    'name' => $program->name ?? $program->title,
                    'title' => $program->title,
                    'description' => $program->description,
                    'banner' => $program->logo->path ?? '',
                    'donation_qr_image' => $program->image->path ?? '',
                    'start_date' => $program->start_date,
                    'end_date' => $program->end_date,
                    'start_time' => $program->start_time,
                    'end_time' => $program->end_time,
                    'start_date_time' => $program->start_date . ' ' . $program->start_time,
                    'end_date_time' => $program->end_date . ' ' . $program->end_time,
                    'location' => $program->location,
                    'address' => $program->address ?? $program->location,
                    'latitude' => $program->latitude,
                    'longitude' => $program->longitude,
                    'registration_required' => $program->registration_required,
                    'registration_type' => $program->registration_type,
                    'registration_amount' => $program->registration_amount,
                    'is_registered' => $is_registered,
                    'is_attended' => $is_attended,
                    'attended_at' => $attended_at,
                    'is_paid' => $is_paid,
                    'total_registered' => ProgramRegistration::where('program_notification_id', $program->id)->count(),
                    'total_attended' => ProgramRegistration::where('program_notification_id', $program->id)->where('is_attended', 1)->count(),
                    'community' => [
                        'id' => $program->community->id ?? null,
                        'name' => $program->community->name ?? null,
                    ],
                ];
            }
            
            return ['programs' => $programsData];
        } catch (\Exception $e) {
            \Log::error('Error in ProgramDataFetcher: ' . $e->getMessage());
            return ['programs' => []];
        }
    }
}

<?php

namespace Modules\Service\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SectionStateManager
{
    private const CACHE_PREFIX = 'home_screen_state';
    private const CACHE_TTL = 86400; // 24 hours

    public function getOrCreateState(int $userId, string $sessionId, array $allCombinations): array
    {
        $cacheKey = $this->getCacheKey($userId, $sessionId);
        
        $state = Cache::get($cacheKey);
        
        if ($state) {
            return $state;
        }
        
        // Group combinations by section type
        $groupedByType = $this->groupByType($allCombinations);
        
        // Shuffle the order of section types (not the variants within each type)
        $sectionTypeOrder = array_keys($groupedByType);
        shuffle($sectionTypeOrder);
        
        $state = [
            'session_id' => $sessionId,
            'grouped_sections' => $groupedByType,
            'section_order' => $sectionTypeOrder, // Order in which section types appear
            'service_indices' => [], // Track which service variant to show for each type
            'page_number' => 0,
            'cycle' => 1,
            'created_at' => Carbon::now()->toDateTimeString(),
        ];
        
        Cache::put($cacheKey, $state, self::CACHE_TTL);
        
        return $state;
    }

    public function updateState(int $userId, string $sessionId, array $state, array $selectedIndices = []): void
    {
        $cacheKey = $this->getCacheKey($userId, $sessionId);
        
        $state['page_number'] = ($state['page_number'] ?? 0) + 1;
        $state['updated_at'] = Carbon::now()->toDateTimeString();
        
        // Update service indices based on what was actually selected
        foreach ($selectedIndices as $type => $selectedIndex) {
            if (!isset($state['grouped_sections'][$type])) {
                continue;
            }
            
            $variants = $state['grouped_sections'][$type];
            
            // Move to next variant for this section type
            $state['service_indices'][$type] = ($selectedIndex + 1) % count($variants);
        }
        
        // Check if we need to start a new cycle
        $allCycled = $this->checkIfCycleComplete($state);
        if ($allCycled) {
            $state['cycle'] = ($state['cycle'] ?? 1) + 1;
            $state['service_indices'] = []; // Reset indices
            
            // Reshuffle section order for new cycle
            shuffle($state['section_order']);
        }
        
        Cache::put($cacheKey, $state, self::CACHE_TTL);
    }

    public function getSectionsForPage(array $state, int $perPage): array
    {
        $sections = [];
        $selectedIndices = []; // Track which index was selected for each section type
        $usedServices = []; // Track services used on this page
        $groupedSections = $state['grouped_sections'] ?? [];
        $sectionOrder = $state['section_order'] ?? [];
        $serviceIndices = $state['service_indices'] ?? [];
        
        // Get sections by cycling through section types as many times as needed
        $count = 0;
        $cycleCount = 0;
        $maxCycles = ceil($perPage / max(1, count($sectionOrder))); // Prevent infinite loop
        
        while ($count < $perPage && $cycleCount < $maxCycles) {
            foreach ($sectionOrder as $sectionType) {
                if ($count >= $perPage) {
                    break;
                }
                
                if (!isset($groupedSections[$sectionType])) {
                    continue;
                }
                
                $variants = $groupedSections[$sectionType];
                if (empty($variants)) {
                    continue;
                }
                
                // Get current service index for this section type (rotates through variants)
                $currentIndex = $serviceIndices[$sectionType] ?? 0;
                
                // Try to find a variant that doesn't use an already-used service
                $selectedVariant = null;
                $selectedIndex = $currentIndex;
                $totalVariants = count($variants);
                
                // Start from current index and check all variants
                for ($i = 0; $i < $totalVariants; $i++) {
                    $testIndex = ($currentIndex + $i) % $totalVariants;
                    $testVariant = $variants[$testIndex] ?? null;
                    
                    // Skip invalid variants
                    if (!is_array($testVariant) || !isset($testVariant['type'])) {
                        continue;
                    }
                    
                    $variant = $testVariant;
                    $service = $variant['service'] ?? 'no-service-' . $sectionType; // Unique for sections without service
                    
                    // If this service hasn't been used yet on this page, use it
                    if (!isset($usedServices[$service])) {
                        $selectedVariant = $variant;
                        $selectedIndex = $testIndex;
                        break;
                    }
                }
                
                // If all variants use already-used services, just use the current index
                if ($selectedVariant === null) {
                    $selectedVariant = $variants[$currentIndex % $totalVariants] ?? null;
                    if (!is_array($selectedVariant)) {
                        continue;
                    }
                    $selectedIndex = $currentIndex % $totalVariants;
                }
                
                // Track the service as used
                $service = $selectedVariant['service'] ?? 'no-service-' . $sectionType;
                $usedServices[$service] = true;
                
                // Add to sections and track which index was used
                $sections[] = $selectedVariant;
                $selectedIndices[$sectionType] = $selectedIndex;
                
                $count++;
            }
            $cycleCount++;
        }
        
        return [
            'sections' => $sections,
            'selected_indices' => $selectedIndices,
        ];
    }

    /**
     * Group combinations by section type
     * Filters out footer-space sections and validates data
     * 
     * @param array $combinations
     * @return array Grouped by type
     */
    private function groupByType(array $combinations): array
    {
        $grouped = [];
        
        foreach ($combinations as $combo) {
            // Validate combo has required fields
            if (!is_array($combo) || !isset($combo['type'])) {
                Log::warning('[SectionStateManager] Invalid combo structure', ['combo' => $combo]);
                continue;
            }
            
            $type = $combo['type'];
            
            // Skip footer-space sections - we don't want them in pages
            if ($type === 'footer-space') {
                continue;
            }
            
            if (!isset($grouped[$type])) {
                $grouped[$type] = [];
            }
            
            $grouped[$type][] = $combo;
        }
        
        return $grouped;
    }

    /**
     * Check if all section types have completed their cycle
     * 
     * @param array $state
     * @return bool
     */
    private function checkIfCycleComplete(array $state): bool
    {
        $groupedSections = $state['grouped_sections'] ?? [];
        $serviceIndices = $state['service_indices'] ?? [];
        
        // If no indices tracked yet, cycle not complete
        if (empty($serviceIndices)) {
            return false;
        }
        
        // Check if all section types have cycled back to 0
        foreach ($groupedSections as $type => $variants) {
            $currentIndex = $serviceIndices[$type] ?? 0;
            
            // If any section hasn't completed its cycle, return false
            if ($currentIndex !== 0) {
                return false;
            }
        }
        
        return true;
    }

    private function getCacheKey(int $userId, string $sessionId): string
    {
        return self::CACHE_PREFIX . ":{$userId}:{$sessionId}";
    }

    public function clearState(int $userId, string $sessionId): void
    {
        $cacheKey = $this->getCacheKey($userId, $sessionId);
        Cache::forget($cacheKey);
    }
}

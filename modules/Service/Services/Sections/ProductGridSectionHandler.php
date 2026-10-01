<?php

namespace Modules\Service\Services\Sections;

use Modules\Service\Services\Sections\Contracts\SectionHandlerInterface;
use Modules\Service\Services\Sections\DataFetchers\ProductDataFetcher;

/**
 * Product Grid Section Handler
 * 
 * Handles product grid layout for online store with seller-based filtering
 */
class ProductGridSectionHandler implements SectionHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle(string $sectionId, ?int $userId = null): ?array
    {
        return $this->createSpecificGrid($sectionId, $userId, 'random');
    }

    /**
     * Create a specific product grid (used by multi-service sections)
     * 
     * @param string $sectionId
     * @param int|null $userId
     * @param string $productListingType Either 'all' for all products, 'random' for random seller, or specific seller ID
     * @return array|null
     */
    public function createSpecificGrid(string $sectionId, ?int $userId, string $productListingType = 'random'): ?array
    {
        $data = ProductDataFetcher::fetch($productListingType);
        
        // Override title if needed for specific sections
        if ($productListingType === 'all') {
            $data['title'] = $data['title'] ?? 'All Products';
        }
        
        return [
            'id' => $sectionId,
            'type' => $this->getSectionType(),
            'service_type' => 'product-grid',
            'data' => $data,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionType(): string
    {
        return 'product-grid';
    }
}

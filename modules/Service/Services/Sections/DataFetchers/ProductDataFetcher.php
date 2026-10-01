<?php

namespace Modules\Service\Services\Sections\DataFetchers;

use Modules\Product\Entities\Product;
use Modules\Seller\Entities\Seller;

/**
 * Product Data Fetcher
 * 
 * Handles fetching product data for home screen with seller-based filtering
 */
class ProductDataFetcher
{
    /**
     * Fetch products for home screen
     * 
     * @param string $listingType Either 'all', 'random' for random seller, or specific seller ID
     * @return array
     */
    public static function fetch(string $listingType = 'random'): array
    {
        try {
            $selectedSeller = null;
            $title = 'Products';
            $subtitle = 'Shop from our collection';
            
            if ($listingType === 'all') {
                // Show all products in random order
                $products = Product::where('is_active', 1)
                    ->with('sellers')
                    ->inRandomOrder()
                    ->limit(10)
                    ->get();
                $title = 'All Products';
                $subtitle = 'Browse our complete collection';
            } elseif ($listingType === 'random') {
                // Get a random seller that has active products
                $randomSeller = Seller::whereHas('products', function ($q) {
                    $q->where('is_active', 1);
                })
                ->where('is_active', 1)
                ->inRandomOrder()
                ->first();
                
                if ($randomSeller) {
                    $selectedSeller = $randomSeller;
                    
                    // Get products from this seller
                    $products = Product::where('is_active', 1)
                        ->whereHas('sellers', function ($q) use ($randomSeller) {
                            $q->where('seller_id', $randomSeller->id);
                        })
                        ->with('sellers')
                        ->inRandomOrder()
                        ->limit(10)
                        ->get();
                    
                    if ($products->count() > 0) {
                        $title = $randomSeller->shop_name . ' Products';
                        $subtitle = 'Shop from ' . $randomSeller->shop_name;
                    } else {
                        // Fallback to all products
                        $products = Product::where('is_active', 1)
                            ->with('sellers')
                            ->inRandomOrder()
                            ->limit(10)
                            ->get();
                    }
                } else {
                    // No sellers found, get all products
                    $products = Product::where('is_active', 1)
                        ->with('sellers')
                        ->inRandomOrder()
                        ->limit(10)
                        ->get();
                }
            } else {
                // Show specific seller products
                $selectedSeller = Seller::find($listingType);
                
                if ($selectedSeller) {
                    $products = Product::where('is_active', 1)
                        ->whereHas('sellers', function ($q) use ($listingType) {
                            $q->where('seller_id', $listingType);
                        })
                        ->with('sellers')
                        ->inRandomOrder()
                        ->limit(10)
                        ->get();
                    
                    if ($products->count() > 0) {
                        $title = $selectedSeller->shop_name . ' Products';
                        $subtitle = 'Shop from ' . $selectedSeller->shop_name;
                    } else {
                        // Fallback to all products
                        $products = Product::where('is_active', 1)
                            ->with('sellers')
                            ->inRandomOrder()
                            ->limit(10)
                            ->get();
                    }
                } else {
                    // Seller not found, get all products
                    $products = Product::where('is_active', 1)
                        ->with('sellers')
                        ->inRandomOrder()
                        ->limit(10)
                        ->get();
                }
            }
            
            return [
                'products' => $products,
                'listing_type' => $listingType,
                'selected_seller' => $selectedSeller,
                'title' => $title,
                'subtitle' => $subtitle
            ];
        } catch (\Exception $e) {
            \Log::error('ProductDataFetcher::fetch error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return [
                'products' => [],
                'listing_type' => $listingType,
                'selected_seller' => null,
                'title' => 'Products',
                'subtitle' => 'Shop from our collection'
            ];
        }
    }

    /**
     * Fetch products by category
     * 
     * @param int $categoryId
     * @return array
     */
    public static function fetchByCategory(int $categoryId): array
    {
        try {
            // Get category details
            $category = \Modules\Catalog\Entities\Category::find($categoryId);
            
            $title = 'Products';
            $subtitle = 'Browse our collection';
            
            if ($category) {
                $title = $category->name . ' Products';
                $subtitle = 'Explore ' . $category->name . ' collection';
            }
            
            // Fetch products from specific category in random order
            $products = Product::where('is_active', 1)
                ->whereHas('categories', function ($q) use ($categoryId) {
                    $q->where('id', $categoryId);
                })
                ->with('translations', 'attributes.attribute.attributeSet', 'sellers')
                ->inRandomOrder()
                ->limit(10)
                ->get();
            
            // Fallback if no products found
            if ($products->isEmpty()) {
                $products = Product::where('is_active', 1)
                    ->with('translations', 'attributes.attribute.attributeSet', 'sellers')
                    ->inRandomOrder()
                    ->limit(10)
                    ->get();
            }
            
            return [
                'products' => $products,
                'category_id' => $categoryId,
                'category' => $category,
                'title' => $title,
                'subtitle' => $subtitle
            ];
        } catch (\Exception $e) {
            return [
                'products' => [],
                'category_id' => $categoryId,
                'category' => null,
                'title' => 'Products',
                'subtitle' => 'Browse our collection'
            ];
        }
    }
}

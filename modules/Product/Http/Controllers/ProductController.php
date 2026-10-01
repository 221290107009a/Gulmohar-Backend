<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Review\Entities\Review;
use Illuminate\Contracts\View\View;
use Modules\Product\Entities\Product;
use Illuminate\Contracts\View\Factory;
use Modules\Product\Events\ProductViewed;
use Modules\Product\Filters\ProductFilter;
use Illuminate\Contracts\Foundation\Application;
use Modules\Product\Repositories\ProductRepository;
use Modules\Product\Http\Middleware\SetProductSortOption;
use Modules\Attribute\Entities\Attribute;
use Modules\Attribute\Entities\AttributeSet;
use Modules\Category\Entities\Category;
use DB;
use Modules\Product\Http\Requests\SaveProductRequest;
use Modules\Product\Transformers\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Media\Entities\File;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    use ProductSearch;

    private $groupColumns = [
        'products.id',
        'slug',
        'price',
        'selling_price',
        'special_price',
        'special_price_type',
        'special_price_start',
        'special_price_end',
        'in_stock',
        'manage_stock',
        'qty',
        'new_from',
        'new_to',
    ];
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(SetProductSortOption::class)->only('index');
    }


    /**
     * Display a listing of the resource.
     *
     * @param Product $model
     * @param ProductFilter $productFilter
     *
     * @return JsonResponse|Application|Factory|View
     */
    public function index(Product $model, ProductFilter $productFilter)
    {
        if (request()->expectsJson()) {
            return $this->searchProducts($model, $productFilter);
        }

        return view('storefront::public.products.index');
    }


    /**
     * Show the specified resource.
     *
     * @param string $slug
     *
     * @return Response
     */
    public function show($slug)
    {
        $product = ProductRepository::findBySlug($slug);
        $relatedProducts = $product->relatedProducts()->with('variants')->forCard()->get();
        $upSellProducts = $product->upSellProducts()->with('variants')->forCard()->get();
        $review = $this->getReviewData($product);

        $product->append([
            'is_in_flash_sale',
            'flash_sale_end_date',
            'formatted_price_range',
        ]);

        $requestedVariant = request()->query('variant');

        if ($requestedVariant) {
            $product->variant = $product->variants()
                ->withoutGlobalScope('active')
                ->where('uid', $requestedVariant)
                ->firstOrFail();
        }

        event(new ProductViewed($product));

        return view('storefront::public.products.show', compact('product', 'relatedProducts', 'upSellProducts', 'review'));
    }


    private function getReviewData(Product $product)
    {
        if (!setting('reviews_enabled')) {
            return null;
        }

        return Review::countAndAvgRating($product);
    }

    private function getProducts(Product $model)
    {
        return $model->search(request('query'))
            ->query()
            ->limit(10)
            ->withName()
            ->withBaseImage()
            ->withPrice()
            ->addSelect([
                'products.id',
                'products.slug',
                'products.in_stock',
                'products.manage_stock',
                'products.qty',
            ])
            ->with(['files', 'categories' => function ($query) {
                $query->limit(5);
            }])
            ->when(request()->filled('category'), $this->categoryQuery())
            ->get();
    }


    /**
     * Returns categories condition closure.
     *
     * @return Closure
     */
    private function categoryQuery()
    {
        return function (Builder $query) {
            $query->whereHas('categories', function ($categoryQuery) {
                $categoryQuery->where('slug', request('category'));
            });
        };
    }

    public function apiProducts()
    {        
        try {
            $productIds = [];
            $query = Product::where('is_active', 1);
            
            if (request()->filled('query')) {
                $search = request('query');
                $query = $query->whereHas('translations', function($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%");   
                });
            }            

            if (request()->filled('category') && request('category') !== 'all') {
                $slug = request('category');
                
                $categoryIds = Category::query()
                    ->where('is_active', 1)
                    ->where(function($q) use ($slug) {
                        $q->where('slug', $slug)
                          ->orWhereHas('translations', function($t) use ($slug) {
                              $t->where('name', 'LIKE', "%{$slug}%");
                          });
                    })
                    ->pluck('id')
                    ->toArray();

                if (!empty($categoryIds)) {
                    $productIds = DB::table('product_categories')
                        ->whereIn('category_id', $categoryIds)
                        ->distinct()
                        ->pluck('product_id')
                        ->toArray();

                    if (!empty($productIds)) {
                        $query->whereIn('id', $productIds);
                    }
                }
            }
            
            $productIds = (clone $query)->select('products.id')->resetOrders()->pluck('id');
            
            if (request()->filled('sort')) {
                if (request('sort') == 'alphabetical') {
                    $query->join('product_translations', 'products.id', '=', 'product_translations.product_id')
                          ->where('product_translations.locale', app()->getLocale())
                          ->orderBy('product_translations.name');
                } elseif (request('sort') == 'priceLowToHigh') {
                    $query->orderBy('price');
                } elseif (request('sort') == 'priceHighToLow') {
                    $query->orderByDesc('price');
                } elseif (request('sort') == 'latest') {
                    $query->latest();
                }
            }
            $products = $query->with('translations', 'attributes.attribute.attributeSet', 'sellers', 'reviews', 'relatedProducts', 'upSellProducts', 'crossSellProducts', 'variants', 'sizeInventory')->get();
            
            foreach($products as $product){
                if (request()->filled('user_id')) {
                    $exists = DB::table('wish_lists')
                    ->where('product_id', $product->id)
                    ->where('user_id', request('user_id'))
                    ->exists();
                    $product->wish_list = $exists ? 1 : 0;
                }

                // Prefer dedicated size_inventory table; fall back to variants
                if ($product->sizeInventory && $product->sizeInventory->isNotEmpty()) {
                    $product->size_inventory = $product->sizeInventory->map(fn($s) => [
                        'size'     => $s->size,
                        'qty'      => (int) $s->qty,
                        'in_stock' => (bool) $s->in_stock && $s->qty > 0,
                    ]);
                } elseif ($product->variants && $product->variants->isNotEmpty()) {
                    $product->size_inventory = $product->variants->map(function($v) {
                        return [
                            'size'     => $v->name,
                            'qty'      => (int) $v->qty,
                            'in_stock' => (bool) ($v->in_stock && ($v->manage_stock ? $v->qty > 0 : true)),
                        ];
                    });
                }
            }

            return response()->json([
                'status' => 'success',  
                'data' => [
                    'products' => $products,                    
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch products',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function apiOnlineStoreProducts()
    {        
        try {
            $productIds = [];
            $query = Product::where('is_active', 1);
                
            if (request()->filled('query')) {
                $search = request('query');
                $query = $query->whereHas('translations', function($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%");   
                });
            }            

            if (request()->filled('category') && request('category') !== 'all') {
                $slug = request('category');
                
                $categoryIds = Category::query()
                    ->where('is_active', 1)
                    ->where(function($q) use ($slug) {
                        $q->where('slug', $slug)
                          ->orWhereHas('translations', function($t) use ($slug) {
                              $t->where('name', 'LIKE', "%{$slug}%");
                          });
                    })
                    ->pluck('id')
                    ->toArray();

                if (!empty($categoryIds)) {
                    $productIds = DB::table('product_categories')
                        ->whereIn('category_id', $categoryIds)
                        ->distinct()
                        ->pluck('product_id')
                        ->toArray();

                    if (!empty($productIds)) {
                        $query->whereIn('id', $productIds);
                    }
                }
            }
            
            $productIds = (clone $query)->select('products.id')->resetOrders()->pluck('id');
            
            if (request()->filled('sort')) {
                if (request('sort') == 'alphabetical') {
                    $query->join('product_translations', 'products.id', '=', 'product_translations.product_id')
                          ->where('product_translations.locale', app()->getLocale())
                          ->orderBy('product_translations.name');
                } elseif (request('sort') == 'priceLowToHigh') {
                    $query->orderBy('price');
                } elseif (request('sort') == 'priceHighToLow') {
                    $query->orderByDesc('price');
                } elseif (request('sort') == 'latest') {
                    $query->latest();
                }
            }
            $products = $query->with('translations', 'attributes.attribute.attributeSet', 'sellers', 'reviews', 'relatedProducts', 'upSellProducts', 'crossSellProducts', 'variants', 'sizeInventory')->get();
            
            foreach ($products as $product) {
                if ($product->sizeInventory && $product->sizeInventory->isNotEmpty()) {
                    $product->size_inventory = $product->sizeInventory->map(fn($s) => [
                        'size'     => $s->size,
                        'qty'      => (int) $s->qty,
                        'in_stock' => (bool) ($s->in_stock && $s->qty > 0),
                    ]);
                } elseif ($product->variants && $product->variants->isNotEmpty()) {
                    $product->size_inventory = $product->variants->map(fn($v) => [
                        'size'     => $v->name,
                        'qty'      => (int) $v->qty,
                        'in_stock' => (bool) ($v->in_stock && ($v->manage_stock ? $v->qty > 0 : true)),
                    ]);
                }
            }

            if (request()->filled('user_id')) {
                foreach($products as $product){
                    $exists = DB::table('wish_lists')
                    ->where('product_id', $product->id)
                    ->where('user_id', request('user_id'))
                    ->exists();
                    if ($exists) {
                        $product->wish_list = 1;
                    } else {
                        $product->wish_list = 0;
                    }
                }
            }

            return response()->json([
                'status' => 'success',  
                'data' => [
                    'products' => $products,                    
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch products',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function apiProductShow($slug)
    {
        $product = Product::where('slug', $slug)
            ->orWhere('id', $slug)
            ->first();

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found'
            ], 404);
        }

        $product->load('categories', 'files', 'sellers', 'reviews', 'relatedProducts.reviews', 'upSellProducts.reviews', 'crossSellProducts.reviews', 'variants.files', 'sizeInventory');
        
        $product->append([
            'is_in_flash_sale',
            'flash_sale_end_date', 
            'formatted_price_range',
        ]);

        // Prefer dedicated size_inventory table; fall back to product variants
        if ($product->sizeInventory && $product->sizeInventory->isNotEmpty()) {
            $product->size_inventory = $product->sizeInventory->map(fn($s) => [
                'size'     => $s->size,
                'qty'      => (int) $s->qty,
                'in_stock' => (bool) ($s->in_stock && $s->qty > 0),
            ]);
        } elseif ($product->variants && $product->variants->isNotEmpty()) {
            $product->size_inventory = $product->variants->map(function($v) {
                return [
                    'size'     => $v->name,
                    'qty'      => (int) $v->qty,
                    'in_stock' => (bool) ($v->in_stock && ($v->manage_stock ? $v->qty > 0 : true)),
                ];
            });
        }
        
        try {
            event(new ProductViewed($product));
        } catch (\Exception $e) {
            // ignore event errors if any
        }

        if (request()->filled('user_id')) {        
            $exists = DB::table('wish_lists')
            ->where('product_id', $product->id)
            ->where('user_id', request('user_id'))
            ->exists();
            $product->wish_list = $exists ? 1 : 0;
        }

        return response()->json([
            'status' => 'success',
            'product' => $product,
        ]);
    }

    public function alphabetic($query)
    {
        $query->join('product_translations', function (JoinClause $join) {
            $join->on('products.id', '=', 'product_translations.product_id');
        })
            ->groupBy(array_merge($this->groupColumns, ['product_translations.name']))
            ->orderBy('product_translations.name');
    }

    public function sellerProductsIndexApi(Request $request, $userId)
    {
        try {
            // Get authenticated seller
            $seller = \DB::table('sellers')
                ->where('user_id', $userId)
                ->where('current_status', 'approved')
                ->first();

            if (!$seller) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Seller not found or not approved'
                ], 403);
            }
            
            // Check if seller has any products
            $sellerProducts = \DB::table('product_seller')
                ->where('seller_id', $seller->id)
                ->get();

            // Check product status
            $productIds = $sellerProducts->pluck('product_id')->toArray();
            $products = \DB::table('products')
                ->whereIn('id', $productIds)
                ->get();

            // Get paginated products with relationships
            $query = Product::withoutGlobalScope('active')
                ->with([
                    'translations',  // Need translations for the name
                    'files'  // Need files for base_image
                ])
                ->whereHas('sellers', function($query) use ($seller) {
                    $query->where('seller_id', $seller->id);
                }, '>=', 1);  

            $products = $query->paginate($request->input('per_page', 10));

            // Transform the collection to include only required fields
            $products->getCollection()->transform(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'selling_price' => [
                        'formatted' => $product->selling_price->format() 
                    ],
                    'qty' => (int) $product->qty,
                    'base_image' => [
                        'path' => $product->base_image->path ?? null
                    ],
                    'is_active' => (bool) $product->is_active
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $products
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Products Fetch Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch products'
            ], 500);
        }
    }

    /**
     * Store a new product.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sellerProductStoreApi(Request $request)
    {
        try {
            $input = $request->all();
            
            if ($request->has('sellers') && !is_array($request->sellers)) {
                $input['sellers'] = [$request->sellers];
            }

            $seller = \DB::table('sellers')
                ->where('id', $request->sellers)
                ->where('current_status', 'approved')
                ->first();

            if (!$seller) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid or unapproved seller',
                ], 400);
            }

            $fileIds = [];
            if ($request->hasFile('base_image')) {
                $baseImage = $request->file('base_image');
                $path = Storage::putFile('media', $baseImage);
                $file = File::create([
                    'user_id' => $seller->user_id,
                    'disk' => 'public_storage',
                    'filename' => $baseImage->getClientOriginalName(),
                    'path' => $path,
                    'extension' => $baseImage->getClientOriginalExtension(),
                    'mime' => $baseImage->getMimeType(),
                    'size' => $baseImage->getSize(),
                ]);
                $input['base_image'] = $file->id;
                $fileIds['base_image'] = $file->id;
            }

            if ($request->hasFile('additional_images')) {
                $additionalImages = [];
                foreach ($request->file('additional_images') as $image) {
                    $path = Storage::putFile('media', $image);
                    $file = File::create([
                        'user_id' => $seller->user_id,
                        'disk' => 'public_storage',
                        'filename' => $image->getClientOriginalName(),
                        'path' => $path,
                        'extension' => $image->getClientOriginalExtension(),
                        'mime' => $image->getMimeType(),
                        'size' => $image->getSize(),
                    ]);
                    $additionalImages[] = $file->id;
                }
                $input['additional_images'] = $additionalImages;
                $fileIds['additional_images'] = $additionalImages;
            }

            $product = new Product();

            $product->name = $input['name'];
            $product->description = $input['description'];
            $product->price = $input['price'];
            $product->special_price = $input['special_price'] ?? null;
            $product->special_price_type = $input['special_price_type'] ?? null;
            $product->special_price_start = $input['special_price_start'] ?? null;
            $product->special_price_end = $input['special_price_end'] ?? null;
            $product->manage_stock = 1;
            $product->in_stock = isset($input['stock']) ? $input['stock'] > 0 : true;
            $product->qty = $input['stock'] ?? 0;
            $product->is_active = false;

            $product->save();

            if (isset($input['category'])) {
                $product->categories()->attach($input['category']);
            }
            
            if (isset($input['parentCategory'])) {
                $product->categories()->attach($input['parentCategory']);
            }

            // Sync files using the file IDs we created
            if (!empty($fileIds)) {
                $product->syncFiles($fileIds);
            }

            if ($request->has('sellers')) {
                $approvedSellerIds = \DB::table('sellers')
                    ->whereIn('id', $input['sellers'])
                    ->where('current_status', 'approved')
                    ->pluck('id')
                    ->toArray();
                
                $product->sellers()->sync($approvedSellerIds);
            }

            return response()->json([
                'status' => 'success',
                'message' => trans('product::messages.product_created'),
                'data' => $product
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Product Creation Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function sellerProductUpdateApi(Request $request)
    {
        try {
            /* \Log::info('Product Update Request:', [
                'all_data' => $request->all(),
                'id' => $request->id,
                'has_base_image' => $request->hasFile('base_image'),
                'has_additional_images' => $request->hasFile('additional_images'),
            ]); */

            if (!$request->has('id')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Product ID is required'
                ], 400);
            }

            $product = Product::withoutGlobalScope('active')->findOrFail($request->id);
            $input = $request->all();
            $fileIds = [];

            // Delete existing files physically from storage
            $existingFiles = $product->files()->get();
            foreach ($existingFiles as $file) {
                try {
                    if (Storage::exists($file->path)) {
                        Storage::delete($file->path);
                        /* \Log::info('Deleted file from storage:', ['path' => $file->path]); */
                    }
                    // Delete the file record from database
                    $file->delete();
                } catch (\Exception $e) {
                    \Log::error('Error deleting file:', [
                        'path' => $file->path,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Handle base image - always replace if provided
            if ($request->hasFile('base_image')) {
                $baseImage = $request->file('base_image');
                $path = Storage::putFile('media', $baseImage);
                $file = File::create([
                    'user_id' => $request->seller_user_id,
                    'disk' => 'public_storage',
                    'filename' => $baseImage->getClientOriginalName(),
                    'path' => $path,
                    'extension' => $baseImage->getClientOriginalExtension(),
                    'mime' => $baseImage->getClientMimeType(),
                    'size' => $baseImage->getSize(),
                ]);
                $fileIds[$file->id] = ['zone' => 'base_image'];
            }

            // Handle additional images - always replace if provided
            if ($request->hasFile('additional_images')) {
                foreach ($request->file('additional_images') as $additionalImage) {
                    $path = Storage::putFile('media', $additionalImage);
                    $file = File::create([
                        'user_id' => $request->seller_user_id,
                        'disk' => 'public_storage',
                        'filename' => $additionalImage->getClientOriginalName(),
                        'path' => $path,
                        'extension' => $additionalImage->getClientOriginalExtension(),
                        'mime' => $additionalImage->getClientMimeType(),
                        'size' => $additionalImage->getSize(),
                    ]);
                    $fileIds[$file->id] = ['zone' => 'additional_images'];
                }
            }

            // Update product fields
            $product->update([
                'name' => $input['name'] ?? $product->name,
                'description' => $input['description'] ?? $product->description,
                'price' => isset($input['price']) ? $input['price'] : ($product->price ? $product->price->amount() : null),
                'special_price' => isset($input['special_price']) ? $input['special_price'] : ($product->special_price ? $product->special_price->amount() : null),
                'special_price_type' => isset($input['special_price_type']) ? $input['special_price_type'] : $product->special_price_type,
                'special_price_start' => isset($input['special_price_start']) ? $input['special_price_start'] : $product->special_price_start,
                'special_price_end' => isset($input['special_price_end']) ? $input['special_price_end'] : $product->special_price_end,
                'qty' => $input['qty'] ?? $product->qty,
                'manage_stock' => isset($input['qty']) ? 1 : $product->manage_stock,
                'in_stock' => isset($input['qty']) ? $input['qty'] > 0 : $product->in_stock,
            ]);

            // Update relationships
            if (isset($input['category'])) {
                $product->categories()->sync($input['category']);
            }

            // Sync files - this will remove all old files and add new ones
            $product->files()->sync($fileIds);

            return response()->json([
                'status' => 'success',
                'message' => trans('product::messages.product_updated'),
                'data' => $product
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Product Update Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove a product
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function sellersProductsRemoveApi($id)
    {
        try {
            // Find product regardless of is_active status
            $product = Product::where('id', $id)->withoutGlobalScopes()->first();
            if (!$product) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Product not found'
                ], 404);
            }

            // Delete all files associated with the product
            $files = $product->files()->get();
            foreach ($files as $file) {
                try {
                    if (Storage::exists($file->path)) {
                        Storage::delete($file->path);
                    }
                    // Delete the file record from database
                    $file->delete();
                } catch (\Exception $e) {
                    \Log::error('Error deleting file:', [
                        'path' => $file->path,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Delete product categories
            $product->categories()->detach();

            // Delete the product
            $product->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Product deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Product Delete Error:', [
                'id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get product details by ID
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function productShowApi($id)
    {
        try {
            $product = Product::withoutGlobalScope('active')
                ->with([
                    'translations',
                    'files',
                    'categories'
                ])
                ->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => [
                        'amount' => number_format($product->price->amount(), 2),
                        'formatted' => $product->price->format()
                    ],
                    'selling_price' => [
                        'formatted' => $product->selling_price->format()
                    ],
                    'description' => $product->description,
                    'category' => $product->categories->whereNotNull('parent_id')->pluck('id')->values(),
                    'parentCategory' => $product->categories->whereNotNull('parent_id')->pluck('parent_id')->values(),
                    'qty' => (int) $product->qty,
                    'base_image' => [
                        'id' => $product->base_image->id ?? null,
                        'path' => $product->base_image->path ?? null
                    ],
                    'additional_images' => $product->additional_images->map(function($image) {
                        return [
                            'id' => $image->id,
                            'path' => $image->path
                        ];
                    }),
                    'special_price_type' => $product->special_price_type,
                    'special_price' => $product->special_price ? [
                        'amount' => number_format($product->special_price->amount(), 2),
                        'formatted' => $product->special_price->format()
                    ] : null,
                    'special_price_start' => $product->special_price_start?->format('Y-m-d'),
                    'special_price_end' => $product->special_price_end?->format('Y-m-d'),
                    'is_active' => (bool) $product->is_active
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Product Fetch Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch product details'
            ], 500);
        }
    }

    public function apiOnlineStoreMessages()
    {   
        return response()->json([
            'success' => true,
            'data' => [
                'app_online_store_message' => setting('app_online_store_message'),
                'app_online_store_message_enabled' => setting('app_online_store_message_enabled'),
            ]
        ]);
    }

    /**
     * Handle product share/referral URL and redirect to app, playstore or web.
     *
     * @param string|int $idOrSlug
     * @param string $referralCode
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function getProductShareUrl($idOrSlug, $referralCode)
    {
        $product = Product::where('id', $idOrSlug)
            ->orWhere('slug', $idOrSlug)
            ->where('is_active', true)
            ->first();

        $utmSource = $referralCode;
        $utmMedium = 'user_referral';
        $utmCampaign = 'product_share';
        $applicationId = 'sangho.app';
        $scheme = 'sangho';

        // Deep link path and URL for mobile app
        $deepLinkPath = $product
            ? "app/referral/{$referralCode}/share_source/store/product/{$product->id}"
            : "app/referral/{$referralCode}/share_source/store";
        $deepLinkUrl = "{$scheme}://{$deepLinkPath}";

        // Referrer parameters to track in Play Store
        $referrerData = [
            'utm_source' => $utmSource,
            'utm_medium' => $utmMedium,
            'utm_campaign' => $utmCampaign,
            'referral' => $referralCode,
            'share_source' => 'store',
            'deep_link' => $deepLinkUrl,
        ];
        if ($product) {
            $referrerData['product_id'] = $product->id;
        }

        $referrerParams = http_build_query($referrerData);
        $playStoreUrl = "https://play.google.com/store/apps/details?id={$applicationId}&referrer=" . urlencode($referrerParams);

        if (!$product) {
            return redirect($playStoreUrl);
        }

        // Smart Android Intent link with fallback
        $currentUrlWithFallback = request()->fullUrlWithQuery(['fallback' => 1]);
        $intentUrl = "intent://{$deepLinkPath}#Intent;scheme={$scheme};package={$applicationId};S.browser_fallback_url=" . urlencode($currentUrlWithFallback) . ";end";

        $logo = $this->getMedia(setting('storefront_header_logo'));

        $webUrl = env('WEB_APP_URL', 'https://sangho-app-next.vercel.app');
        $webRedirectUrl = rtrim($webUrl, '/') . "/online-store/product/{$product->slug}?ref={$referralCode}&source=product-share";

        // Format cover image
        $coverImage = null;
        if ($product->base_image && !empty($product->base_image->path)) {
            $path = $product->base_image->path;
            $coverImage = filter_var($path, FILTER_VALIDATE_URL)
                ? $path
                : asset('storage/' . $path);
        }

        $productData = [
            'id' => (string) $product->id,
            'title' => $product->name ?? '',
            'coverImage' => $coverImage,
            'description' => $product->description ?? '',
        ];

        return view('product::share.product', compact(
            'productData',
            'intentUrl',
            'deepLinkUrl',
            'referralCode',
            'playStoreUrl',
            'webRedirectUrl',
            'logo'
        ));
    }

    /**
     * Handle Bahujan Sahitya product share/referral URL and redirect to app, playstore or web.
     *
     * @param string|int $idOrSlug
     * @param string $referralCode
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function getBahujanProductShareUrl($idOrSlug, $referralCode)
    {
        $product = Product::where('id', $idOrSlug)
            ->orWhere('slug', $idOrSlug)
            ->where('is_active', true)
            ->first();

        $utmSource = $referralCode;
        $utmMedium = 'user_referral';
        $utmCampaign = 'product_share';
        $applicationId = 'sangho.app';
        $scheme = 'sangho';

        // Deep link path and URL for mobile app (bahujan tab)
        $deepLinkPath = $product
            ? "app/referral/{$referralCode}/share_source/bahujan/product/{$product->id}"
            : "app/referral/{$referralCode}/share_source/bahujan";
        $deepLinkUrl = "{$scheme}://{$deepLinkPath}";

        // Referrer parameters to track in Play Store
        $referrerData = [
            'utm_source' => $utmSource,
            'utm_medium' => $utmMedium,
            'utm_campaign' => $utmCampaign,
            'referral' => $referralCode,
            'share_source' => 'bahujan',
            'deep_link' => $deepLinkUrl,
        ];
        if ($product) {
            $referrerData['product_id'] = $product->id;
        }

        $referrerParams = http_build_query($referrerData);
        $playStoreUrl = "https://play.google.com/store/apps/details?id={$applicationId}&referrer=" . urlencode($referrerParams);

        if (!$product) {
            return redirect($playStoreUrl);
        }

        // Smart Android Intent link with fallback
        $currentUrlWithFallback = request()->fullUrlWithQuery(['fallback' => 1]);
        $intentUrl = "intent://{$deepLinkPath}#Intent;scheme={$scheme};package={$applicationId};S.browser_fallback_url=" . urlencode($currentUrlWithFallback) . ";end";

        $logo = $this->getMedia(setting('storefront_header_logo'));

        $webUrl = env('WEB_APP_URL', 'https://sangho-app-next.vercel.app');
        $webRedirectUrl = rtrim($webUrl, '/') . "/online-store/bahujan-sahitya/product/{$product->slug}?ref={$referralCode}&source=product-share";

        // Format cover image
        $coverImage = null;
        if ($product->base_image && !empty($product->base_image->path)) {
            $path = $product->base_image->path;
            $coverImage = filter_var($path, FILTER_VALIDATE_URL)
                ? $path
                : asset('storage/' . $path);
        }

        $productData = [
            'id' => (string) $product->id,
            'title' => $product->name ?? '',
            'coverImage' => $coverImage,
            'description' => $product->description ?? '',
        ];

        return view('product::share.product', compact(
            'productData',
            'intentUrl',
            'deepLinkUrl',
            'referralCode',
            'playStoreUrl',
            'webRedirectUrl',
            'logo'
        ));
    }

    private function getMedia($fileId)
    {
        return \Illuminate\Support\Facades\Cache::rememberForever(md5("files.{$fileId}"), function () use ($fileId) {
            return File::findOrNew($fileId);
        });
    }
}
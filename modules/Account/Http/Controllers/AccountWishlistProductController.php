<?php

namespace Modules\Account\Http\Controllers;

use Illuminate\Http\Response;
use Modules\Product\Entities\Product;
use Modules\User\Entities\User;
use Illuminate\Http\Request;
use DB;

class AccountWishlistProductController
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return auth()->user()
            ->wishlist()
            ->with('files')
            ->orderByPivot('created_at', 'desc')
            ->paginate(10);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        if (!auth()->user()->wishlistHas(request('productId'))) {
            auth()->user()->wishlist()->attach(request('productId'));
        }
    }


    /**
     * Destroy resources by the given id.
     *
     * @param Product $product
     *
     * @return void
     */
    public function destroy(Product $product)
    {
        auth()->user()->wishlist()->detach($product);
    }

    public function apiIndex($uid)
    {
        if ($uid == null) {
            return response()->json([
                'status' => false,
                'message' => 'User ID is required'
            ], 400);
        }
        
        $wishListsPId = DB::table('wish_lists')            
        ->where('user_id', $uid)
        ->pluck('product_id');
        
        if ($wishListsPId->count() == 0 ) {
            return response()->json([
                'status' => false,
                'message' => 'Wish List not Found'
            ], 400);
        }

        $products = Product::with('categories')->whereIn('id', $wishListsPId)->get();
        
        $formattedWishlist = $products->map(function($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $product->price,
                'special_price' => $product->special_price,
                'images' => $product->base_image->path,
                'in_stock' => $product->in_stock,
                'categories' => $product->categories->map(function($cat) {
                    return [
                        'id' => $cat->id,
                        'parent_id' => $cat->parent_id
                    ];
                })
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $formattedWishlist
        ]);
               
    }

    public function apiStore(Request $request)
    {        
        $exists = DB::table('wish_lists')
            ->where('product_id', $request->product_id)
            ->where('user_id', $request->user_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => false,
                'message' => 'Product already in wishlist'
            ], 400);
        }

        try {
            $wishList = DB::table('wish_lists')->insert([
                'product_id' => $request->product_id,
                'user_id' => $request->user_id,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Product added to wishlist',
                'data' => $wishList
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to add product to wishlist'
            ], 500);
        }
    } 

    public function apiDestroy(Request $request)
    {
        try {
            $deleted = DB::table('wish_lists')
                ->where('product_id', $request->product_id)
                ->where('user_id', $request->user_id)
                ->delete();

            if (!$deleted) {
                return response()->json([
                    'status' => false,
                    'message' => 'Item not found in wishlist'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Product removed from wishlist'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to remove product from wishlist'
            ], 500);
        }
    }
}

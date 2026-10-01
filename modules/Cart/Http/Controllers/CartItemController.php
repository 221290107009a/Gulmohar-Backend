<?php

namespace Modules\Cart\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Modules\Cart\Facades\Cart;
use Modules\Cart\Entities\Cart as CartEntity;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Routing\Controller;
use Modules\Coupon\Entities\Coupon;
use Modules\Coupon\Checkers\ValidCoupon;
use Modules\Coupon\Checkers\MaximumSpend;
use Modules\Coupon\Checkers\MinimumSpend;
use Modules\Coupon\Checkers\CouponExists;
use Modules\Coupon\Checkers\AlreadyApplied;
use Modules\Coupon\Checkers\ExcludedProducts;
use Modules\Coupon\Checkers\ApplicableProducts;
use Modules\Coupon\Checkers\ExcludedCategories;
use Modules\Coupon\Checkers\UsageLimitPerCoupon;
use Modules\Cart\Http\Middleware\CheckItemStock;
use Modules\Coupon\Checkers\ApplicableCategories;
use Modules\Coupon\Checkers\UsageLimitPerCustomer;
use Modules\Cart\Http\Requests\StoreCartItemRequest;

class CartItemController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(CheckItemStock::class)
            ->only(['store', 'update']);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param StoreCartItemRequest $request
     *
     * @return \Modules\Cart\Cart
     */
    public function store(StoreCartItemRequest $request)
    {
        $uid = auth()->id() ?? '';
        Cart::store(
            $uid,
            $request->product_id,
            $request->variant_id,
            $request->qty,
            $request->options ?? [],
        );

        return Cart::instance();
    }


    /**
     * Update the specified resource in storage.
     *
     * @param string $id
     *
     * @return \Modules\Cart\Cart
     */
    public function update(string $id)
    {
        Cart::updateQuantity($id, request('qty'));

        return Cart::instance();
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param string $id
     *
     * @return \Modules\Cart\Cart
     */
    public function destroy(string $id)
    {
        Cart::remove($id);

        return Cart::instance();
    }

    public function apiStore(Request $request)
    {
        try {
            // Load the existing cart for this customer if one exists
            if ($request->customer_id) {
                $cartRow = CartEntity::where('customer_id', $request->customer_id)->latest('updated_at')->first();
                if ($cartRow) {
                    session()->put('customer_id', $request->customer_id);
                    session()->setId($cartRow->id);
                    session()->start();
                }
            }

            Cart::store(
                $request->customer_id,
                $request->product_id,
                $request->variant_id,
                $request->qty,
                $request->options ?? [],
            );

            return response()->json([
                'success' => true,
                'message' => 'Item added to cart successfully',
                'data' => Cart::instance()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add item to cart',
                'error' => $e->getMessage()
            ], 400);
        } 
    }

    public function apiUpdate(Request $request, string $id)
    {
        try {
            // Load the correct customer cart
            if ($request->customer_id) {
                $cartRow = CartEntity::where('customer_id', $request->customer_id)->latest('updated_at')->first();
                if ($cartRow) {
                    session()->put('customer_id', $request->customer_id);
                    session()->setId($cartRow->id);
                    session()->start();
                }
            }

            Cart::updateQuantity($id, $request->qty);

            return response()->json([
                'status' => 'success',
                'message' => 'Cart updated successfully',
                'data' => Cart::instance()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function apiDestroy(Request $request, string $id)
    {
        try {
            // Load the correct customer cart
            if ($request->customer_id) {
                $cartRow = CartEntity::where('customer_id', $request->customer_id)->latest('updated_at')->first();
                if ($cartRow) {
                    session()->put('customer_id', $request->customer_id);
                    session()->setId($cartRow->id);
                    session()->start();
                }
            }

            Cart::remove($id);

            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart successfully',
                'data' => Cart::instance()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to remove item from cart',
                'error' => $e->getMessage() 
            ]);
        }
    }
}

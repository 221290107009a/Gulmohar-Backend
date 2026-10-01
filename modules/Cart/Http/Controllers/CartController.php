<?php

namespace Modules\Cart\Http\Controllers;

use Modules\Cart\Facades\Cart;
use Modules\Cart\Entities\Cart as CartEntity;
use Modules\Address\Entities\Address;
use Modules\Account\Entities\DefaultAddress;
use Illuminate\Http\Request;

class CartController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        return view('storefront::public.cart.index');
    }


    /**
     * Clear the cart.
     *
     * @return \Modules\Cart\Cart
     */
    public function clear()
    {
        Cart::clear();

        return Cart::instance();
    }

    public function apiGetUserAddress($userId)
    {
        if ($userId) {            
            $address = Address::where('customer_id', $userId)->get();
            if ($address->count() > 0) {
                $defaultAddress = DefaultAddress::where('customer_id', $userId)->first();
                if ($defaultAddress) {
                    return response()->json([
                        'status' => true,
                        'data' => $address,
                        'defaultAddress' => $defaultAddress->address_id
                    ]);
                } else {
                    return response()->json([
                        'status' => true,
                        'data' => $address,
                        'defaultAddress' => null
                    ]);
                }
            }
            return response()->json([
                'status' => false,
                'message' => 'Address not found',
            ]);
        }
        return response()->json([
            'status' => false,
            'message' => 'Address not found',
        ]);
    }
    
    public function apiSaveUserAddress(Request $request)    
    {
        $address = Address::create([
            'customer_id' => $request->customer_id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone ?? '',
            'address_1' => $request->address_1,
            'address_2' => $request->address_2,
            'area_type' => $request->area_type ?? '',
            'taluka' => $request->taluka ?? '',
            'village' => $request->village ?? '',
            'city' => $request->city,
            'state' => $request->state,
            'zip' => $request->zip,
            'country' => 'IN',
            'default_billing' => $request->default_billing ? 1 : 0,
        ]);

        if ($request->default_address) {
            $defaultAddress = DefaultAddress::where('customer_id', $request->customer_id)->first();
            if ($defaultAddress) {
                $defaultAddress->update([
                    'address_id' => $address->id,
                ]);
            } else {
                DefaultAddress::create([
                    'customer_id' => $request->customer_id,
                    'address_id' => $address->id,
                ]);
            }
        }
            
        if ($address) {
            return response()->json([
                'status' => true,
                'message' => 'Address saved successfully',
                'data' => $address
            ]);
        }
        return response()->json([
            'status' => false,
            'message' => 'Address not saved',
        ]);
    }

    public function apiUpdateUserAddress(Request $request)    
    {
        try {
            $address = Address::findOrFail((int)$request->id);
            
            // Type-safe comparison: cast both to int to avoid string vs int mismatch
            if ((int)$address->customer_id !== (int)$request->customer_id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access to this address',
                    'debug' => ['db_customer_id' => $address->customer_id, 'request_customer_id' => $request->customer_id]
                ], 403);
            }
    
            $address->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone ?? '',
                'address_1' => $request->address_1,
                'address_2' => $request->address_2,
                'area_type' => $request->area_type ?? '',
                'taluka' => $request->taluka ?? '',
                'village' => $request->village ?? '',
                'city' => $request->city,
                'state' => $request->state,
                'zip' => $request->zip,
                'default_billing' => $request->default_billing ? 1 : 0,
            ]);
    
            if ($request->default_address) {
                $defaultAddress = DefaultAddress::where('customer_id', $request->customer_id)->first();
                if ($defaultAddress) {
                    $defaultAddress->update([
                        'address_id' => $address->id,
                    ]);
                } else {
                    DefaultAddress::create([
                        'customer_id' => $address->customer_id,
                        'address_id' => $address->id,
                    ]);
                }
            }
            
            return response()->json([
                'status' => true,
                'message' => 'Address updated successfully',
                'data' => $address
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update address',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function apiDeleteUserAddress($id)
    {
        try {
            $address = Address::find($id);
            if (!$address) {
                return response()->json([
                    'status' => false,
                    'message' => 'Address not found',
                ], 404);
            }
            $address->delete();
            return response()->json([
                'status' => true,
                'message' => 'Address deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete address',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function apiCartIndex(Request $request)
    {
        $customerId = $request->input('customer_id');

        if ($customerId) {
            // Find the cart row for this customer in the database
            $cartRow = CartEntity::where('customer_id', $customerId)->latest('updated_at')->first();
            if ($cartRow) {
                // Set session to use this cart's ID so Cart::instance() loads the right cart
                session()->put('customer_id', $customerId);
                session()->setId($cartRow->id);
                session()->start();
            } else {
                // No cart found for this customer
                return response()->json([
                    'status' => false,
                    'message' => 'Cart is empty',
                ]);
            }
        }

        $cart = Cart::instance();
        if ($cart->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Cart is empty',
            ]);
        } else {            
            return response()->json([
                'status' => true,
                'cart' => $cart
            ]);
        }
    }

    public function apiClear(Request $request)
    {
        $customerId = $request->input('customer_id');
        if ($customerId) {
            $cartRow = CartEntity::where('customer_id', $customerId)->latest('updated_at')->first();
            if ($cartRow) {
                session()->put('customer_id', $customerId);
                session()->setId($cartRow->id);
                session()->start();
            }
        }

        Cart::clear();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Cart cleared successfully',
            'cart' => Cart::instance()
        ]);
    }
}
<?php

namespace Modules\Seller\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Modules\Seller\Entities\Seller;
use Modules\Seller\Entities\SellerTranslation;
use Modules\Media\Entities\File;
use Illuminate\Support\Facades\Storage;
use Modules\Product\Entities\Product;

class SellerController
{
    /**
     * Update order status for seller's order
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiUpdateOrderStatus(Request $request)
    {
        try {
            $seller = Seller::where('user_id', $request->user_id)->first();
            
            if (!$seller) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seller not found',
                ], 404);
            }

            // Validate request
            $request->validate([
                'order_id' => 'required|exists:orders,id',
                'status' => 'required|in:pending,processing,completed,canceled,refunded,on_hold,pending_payment'
            ]);

            // Get the order
            $order = \Modules\Order\Entities\Order::find($request->order_id);
            
            // Check if order exists
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            // Check if the order contains products from this seller
            $hasSellerProducts = \Modules\Order\Entities\OrderProduct::where('order_id', $order->id)
                ->whereHas('product.sellers', function($query) use ($seller) {
                    $query->where('sellers.id', $seller->id);
                })
                ->exists();

            if (!$hasSellerProducts) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to update this order'
                ], 403);
            }

            // Update order status
            $oldStatus = $order->status;
            $order->status = $request->status;
            $order->save();

            // Trigger order status changed event
            event(new \Modules\Order\Events\OrderStatusChanged($order));

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'data' => [
                    'order_id' => $order->id,
                    'old_status' => $oldStatus,
                    'new_status' => $order->status
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Format money amount with currency symbol
     *
     * @param float $amount
     * @return string
     */
    private function money_format($amount)
    {
        return '₹' . number_format($amount, 2);
    }

    /**
     * Get all orders for a seller via API.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetSellerOrders(Request $request)
    {
        try {
            $seller = Seller::where('user_id', $request->user_id)->first();
            
            if (!$seller) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seller not found',
                ], 404);
            }

            // Get all products associated with this seller
            $products = Product::whereHas('sellers', function($query) use ($seller) {
                $query->where('sellers.id', $seller->id);
            })->get();

            // Get all orders that contain these products
            $orderProducts = \Modules\Order\Entities\OrderProduct::whereIn('product_id', $products->pluck('id'))
                ->with(['order' => function($query) {
                    $query->select('id', 'customer_first_name', 'customer_last_name', 'customer_email', 
                        'customer_phone', 'status', 'currency', 'shipping_method', 'payment_method', 
                        'sub_total', 'shipping_cost', 'discount', 'total', 'created_at',
                        'shipping_address_1', 'shipping_address_2', 'shipping_city', 'shipping_state', 'shipping_zip');
                }])
                ->get();

            // Group order products by order ID
            $groupedOrderProducts = $orderProducts->groupBy(function($orderProduct) {
                return $orderProduct->order ? $orderProduct->order->id : null;
            })->filter(function($group, $orderId) {
                return $orderId !== null;
            });

            $orders = $groupedOrderProducts->map(function($orderProducts) {
                $order = $orderProducts->first()->order;
                
                try {
                    return [
                        'id' => $order->id,
                        'customer_first_name' => $order->customer_first_name,
                        'customer_last_name' => $order->customer_last_name,
                        'customer_email' => $order->customer_email,
                        'customer_phone' => $order->customer_phone,
                        'status' => $order->status,
                        'payment_method' => $order->payment_method,
                        'shipping_method' => $order->shipping_method,
                        'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                        'total' => [
                            'formatted' => $this->money_format($order->total->amount()),
                            'amount' => $order->total->amount()
                        ],
                        'sub_total' => [
                            'formatted' => $this->money_format($order->sub_total->amount()),
                            'amount' => $order->sub_total->amount()
                        ],
                        'shipping_address_1' => $order->shipping_address_1,
                        'shipping_address_2' => $order->shipping_address_2,
                        'shipping_city' => $order->shipping_city,
                        'shipping_state' => $order->shipping_state,
                        'shipping_zip' => $order->shipping_zip,
                        'products' => $orderProducts->map(function($orderProduct) {
                            return [
                                'id' => $orderProduct->product_id,
                                'name' => $orderProduct->name,
                                'qty' => $orderProduct->qty,
                                'unit_price' => [
                                    'formatted' => $this->money_format($orderProduct->unit_price->amount()),
                                    'amount' => $orderProduct->unit_price->amount()
                                ],
                                'line_total' => [
                                    'formatted' => $this->money_format($orderProduct->line_total->amount()),
                                    'amount' => $orderProduct->line_total->amount()
                                ]
                            ];
                        })->values()->all(),
                    ];
                } catch (\Exception $e) {
                    // Skip any orders that have invalid or missing data
                    return null;
                }
            })->filter()->values();

            return response()->json([
                'success' => true,
                'orders' => $orders,
                'total_orders' => $orders->count(),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Store a seller via API.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiSellerRegistration(Request $request)
    {
        try {
            // Check if seller already exists for this user_id
            $existingSeller = Seller::where('user_id', $request->user_id)->first();
            
            if ($existingSeller) {
                return response()->json([
                    'success' => false,
                    'message' => 'A seller account already exists for this user.',
                    'seller' => [
                        'existing_active_status' => 'true',
                    ],
                ], 400);
            }

            $seller = Seller::create($request->except(['logo', 'image']));
            // Handle file uploads
            if ($request->hasFile('logo') || $request->hasFile('image')) {
                $files = [];
                
                if ($request->hasFile('logo')) {
                    $logo = $request->file('logo');
                    $path = Storage::putFile('media', $logo);
                    $file = File::create([
                        'user_id' => $seller->user_id,
                        'disk' => 'public_storage',
                        'filename' => $logo->getClientOriginalName(),
                        'path' => $path,
                        'extension' => $logo->getClientOriginalExtension(),
                        'mime' => $logo->getMimeType(),
                        'size' => $logo->getSize(),
                    ]);
                    $files['logo'] = [$file->id];
                }
                
                if ($request->hasFile('image')) {
                    $image = $request->file('image');
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
                    $files['image'] = [$file->id];
                }

                $seller->syncFiles($files);
            }

            // Send email to admin
            $adminEmail = 'sangho.contact@gmail.com';
            Mail::send('seller::admin.sellers.emails.seller_registration', ['seller' => $seller], function ($message) use ($adminEmail) {
                $message->to($adminEmail)
                        ->subject('New Seller Registration');
            });

            // Send email to seller
            $sellerEmail = $seller->email;
            Mail::send('seller::emails.seller_registration', ['seller' => $seller], function ($message) use ($sellerEmail) {
                $message->to($sellerEmail)
                        ->subject('Your Seller Registration Status');
            });

            // Load the seller with translations
            $seller->load('translations');
            
            return response()->json([
                'success' => true,
                'message' => 'Seller created successfully.',
                'seller' => [
                    'current_status' => $seller->current_status,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function apiGetSellerStatus(Request $request)
    {
        try {
            $seller = Seller::where('user_id', $request->user_id)->first();
            
            if (!$seller) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seller not found',
                ], 404);
            }

            // Get product count for this seller
            $productCount = Product::whereHas('sellers', function($query) use ($seller) {
                $query->where('sellers.id', $seller->id);
            })->where('is_active', 1)->count();

            return response()->json([
                'success' => true,
                'seller' => [
                    'id' => $seller->id,
                    'user_id' => $seller->user_id,
                    'current_status' => $seller->current_status,
                    'shop_name' => $seller->shop_name,
                    'owner_name' => $seller->owner_name,
                    'email' => $seller->email,
                    'phone' => $seller->phone,
                    'logo' => $seller->logo,
                    'image' => $seller->image,
                    'product_count' => $productCount,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get seller details by user ID
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetSellerById(Request $request)
    {
        try {
            $seller = Seller::with('translations')->where('user_id', $request->user_id)->first();
            
            if (!$seller) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seller not found',
                ], 404);
            }

            // Get product count for this seller
            $productCount = Product::whereHas('sellers', function($query) use ($seller) {
                $query->where('sellers.id', $seller->id);
            })->where('is_active', 1)->count();

            return response()->json([
                'success' => true,
                'seller' => [
                    'id' => $seller->id,
                    'user_id' => $seller->user_id,
                    'current_status' => $seller->current_status,
                    'shop_name' => $seller->shop_name,
                    'owner_name' => $seller->owner_name,
                    'description' => $seller->description,
                    'category' => $seller->category,
                    'gst_number' => $seller->gst_number,
                    'pan_number' => $seller->pan_number,
                    'email' => $seller->email,
                    'phone' => $seller->phone,
                    'address1' => $seller->address1,
                    'address2' => $seller->address2,
                    'city' => $seller->city,
                    'state' => $seller->state,
                    'country' => $seller->country,
                    'pincode' => $seller->pincode,
                    'logo' => $seller->logo,
                    'image' => $seller->image,
                    'product_count' => $productCount,
                    'created_at' => $seller->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $seller->updated_at->format('Y-m-d H:i:s'),
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update seller details
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiUpdateSellerDetails(Request $request, $user_id)
    {
        
        try {
            $request->validate([
                'shop_name' => 'sometimes|string|max:255',
                'owner_name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|max:255',
                'phone' => 'sometimes|string|max:20',
                'address1' => 'sometimes|string|max:500',
                'address2' => 'sometimes|string|max:500',
                'city' => 'sometimes|string|max:100',
                'state' => 'sometimes|string|max:100',
                'country' => 'sometimes|string|max:100',
                'pincode' => 'sometimes|string|max:20',
            ]);

            $seller = Seller::where('user_id', $user_id)->first();

            if (!$seller) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seller not found',
                ], 404);
            }

            // Update seller details
            $seller->update($request->except(['logo', 'image', 'seller_id', 'remove_logo', 'remove_image']));

            // Handle image removal
            if ($request->has('remove_logo') && $request->remove_logo == '1') {
                // Detach logo files using filterFiles
                $seller->filterFiles('logo', app()->getLocale())->detach();
            }

            if ($request->has('remove_image') && $request->remove_image == '1') {
                // Detach banner image files using filterFiles
                $seller->filterFiles('image', app()->getLocale())->detach();
            }

            // Handle file uploads
            if ($request->hasFile('logo') || $request->hasFile('image')) {
                $files = [];
                
                if ($request->hasFile('logo')) {
                    $logo = $request->file('logo');
                    $path = Storage::putFile('media', $logo);
                    $file = File::create([
                        'user_id' => $seller->user_id,
                        'disk' => 'public_storage',
                        'filename' => $logo->getClientOriginalName(),
                        'path' => $path,
                        'extension' => $logo->getClientOriginalExtension(),
                        'mime' => $logo->getMimeType(),
                        'size' => $logo->getSize(),
                    ]);
                    $files['logo'] = [$file->id];
                }
                
                if ($request->hasFile('image')) {
                    $image = $request->file('image');
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
                    $files['image'] = [$file->id];
                }

                $seller->syncFiles($files);
            }

            // Refresh seller data
            $seller->refresh();
            $seller->load('translations');

            return response()->json([
                'success' => true,
                'message' => 'Seller details updated successfully',
                'seller' => [
                    'id' => $seller->id,
                    'user_id' => $seller->user_id,
                    'current_status' => $seller->current_status,
                    'shop_name' => $seller->shop_name,
                    'owner_name' => $seller->owner_name,
                    'email' => $seller->email,
                    'phone' => $seller->phone,
                    'address1' => $seller->address1,
                    'address2' => $seller->address2,
                    'city' => $seller->city,
                    'state' => $seller->state,
                    'country' => $seller->country,
                    'pincode' => $seller->pincode,
                    'logo' => $seller->logo,
                    'image' => $seller->image,
                    'updated_at' => $seller->updated_at->format('Y-m-d H:i:s'),
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

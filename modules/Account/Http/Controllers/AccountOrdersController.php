<?php

namespace Modules\Account\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Modules\Order\Entities\Order;

class AccountOrdersController
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $orders = auth()->user()
            ->orders()
            ->latest()
            ->paginate(20);

        return view('storefront::public.account.orders.index', compact('orders'));
    }


    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $order = auth()->user()
            ->orders()
            ->with(['products', 'coupon', 'taxes'])
            ->where('id', $id)
            ->firstOrFail();

        return view('storefront::public.account.orders.show', compact('order'));
    }

    public function apiIndex(Request $request)
    {
        $userId = $request->user_id ?? null;
        $email  = $request->email ?? null;

        if (!$userId && !$email) {
            return response()->json([
                'status'  => 'error',
                'message' => 'User ID or email required'
            ], 400);
        }

        $query = Order::latest();

        if ($userId && $email) {
            // Match by customer_id OR by email — whichever the order was stored with
            $query->where(function($q) use ($userId, $email) {
                $q->where('customer_id', $userId)
                  ->orWhere('customer_email', $email);
            });
        } elseif ($userId) {
            $query->where('customer_id', $userId);
        } else {
            $query->where('customer_email', $email);
        }

        $orders = $query->with('products.product.files')->get();

        return response()->json([
            'status'  => 'success',
            'data'    => $orders,
            'message' => 'Orders retrieved successfully'
        ], 200);
    }

    public function apiCancel($id)
    {
        $order = Order::where('id', $id)
            ->orWhere('note', 'LIKE', '%"order_code":"' . $id . '"%')
            ->first();

        if (!$order) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Order not found'
            ], 404);
        }

        if (in_array($order->status, [Order::CANCELED, Order::REFUNDED])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Order is already canceled'
            ], 400);
        }

        if (in_array($order->status, [Order::PROCESSING, Order::COMPLETED])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Order is already in processing and cannot be canceled'
            ], 400);
        }

        // Restore sizes and stock
        \Modules\Order\Http\Controllers\Admin\OrderStatusController::restoreFootwearSizes($order);

        $order->update(['status' => Order::CANCELED]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Order canceled successfully and stock restored'
        ], 200);
    }

    public function apiShow($id)
    {
        if (isset($id) && $id != null) {            
            $order = Order::where('id', $id)
                    ->orWhere('note', 'LIKE', '%"order_code":"' . $id . '"%')
                    ->with(['products.product.files'])
                    ->first();
            
            if ($order) {                
                return response()->json([
                    'status' => 'success',
                    'data' => $order,
                    'message' => 'Orders retrieved successfully'
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Orders not found'
                ], 400);
            }
        } else {            
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found'
            ], 400);
        }
    }
}

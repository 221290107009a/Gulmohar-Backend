<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Http\Response;
use Modules\Order\Entities\Order;

class OrderController
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return view('order::index');
    }


    /**
     * Show the specified resource.
     *
     * @return Response
     */
    public function show()
    {
        return view('order::show');
    }

    public function printOrderReceipt(Order $order)
    {
        $order->load('products', 'coupon', 'taxes');

        return view('order::admin.orders.print.new_order', compact('order'));
    }
}

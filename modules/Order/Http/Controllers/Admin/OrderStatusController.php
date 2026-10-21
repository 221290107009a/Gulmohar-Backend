<?php

namespace Modules\Order\Http\Controllers\Admin;

use Illuminate\Http\Response;
use Modules\Order\Entities\Order;
use Modules\Order\Entities\OrderProduct;
use Modules\Order\Events\OrderStatusChanged;

class OrderStatusController
{
    /**
     * Update the specified resource in storage.
     *
     * @param Order $request
     *
     * @return Response
     */
    public function update(Order $order)
    {
        $this->adjustStock($order);

        $order->update(['status' => request('status')]);

        $message = trans('order::messages.status_updated');

        event(new OrderStatusChanged($order));

        return $message;
    }


    private function adjustStock(Order $order)
    {
        if ($this->canceledOrRefunded(request('status'))) {
            $this->restoreStock($order);
        }

        if ($this->canceledOrRefunded($order->status)) {
            $this->reduceStock($order);
        }
    }


    private function canceledOrRefunded($status)
    {
        return in_array($status, [Order::CANCELED, Order::REFUNDED]);
    }


    public static function restoreFootwearSizes(Order $order)
    {
        $handled = false;
        if (!empty($order->note)) {
            $data = json_decode($order->note, true);
            if (is_array($data) && !empty($data['items'])) {
                foreach ($data['items'] as $item) {
                    $prodId = $item['product_id'] ?? ($item['id'] ?? null);
                    $sizeStr = $item['size'] ?? ($item['selectedSize'] ?? null);
                    $qty = (int)($item['quantity'] ?? ($item['qty'] ?? 1));

                    if ($prodId && $sizeStr && $qty > 0) {
                        $sizeRow = \Modules\Product\Entities\ProductSizeInventory::where('product_id', $prodId)
                            ->where('size', $sizeStr)
                            ->first();

                        if ($sizeRow) {
                            $sizeRow->increment('qty', $qty);
                            $sizeRow->update(['in_stock' => true]);
                        }

                        $prod = \Modules\Product\Entities\Product::find($prodId);
                        if ($prod) {
                            if ($prod->manage_stock) {
                                $prod->increment('qty', $qty);
                            }
                            $prod->update(['in_stock' => true]);
                        }
                    }
                }
                $handled = true;
            }
        }

        if (!$handled && $order->products) {
            foreach ($order->products as $orderProduct) {
                if ($orderProduct->product) {
                    if ($orderProduct->product->manage_stock) {
                        $orderProduct->product->increment('qty', $orderProduct->qty);
                    }
                    $orderProduct->product->update(['in_stock' => true]);
                }
            }
        }
    }

    public static function reduceFootwearSizes(Order $order)
    {
        if (!empty($order->note)) {
            $data = json_decode($order->note, true);
            if (is_array($data) && !empty($data['items'])) {
                foreach ($data['items'] as $item) {
                    $prodId = $item['product_id'] ?? ($item['id'] ?? null);
                    $sizeStr = $item['size'] ?? ($item['selectedSize'] ?? null);
                    $qty = (int)($item['quantity'] ?? ($item['qty'] ?? 1));

                    if ($prodId && $sizeStr && $qty > 0) {
                        $sizeRow = \Modules\Product\Entities\ProductSizeInventory::where('product_id', $prodId)
                            ->where('size', $sizeStr)
                            ->first();

                        if ($sizeRow) {
                            $newQty = max(0, $sizeRow->qty - $qty);
                            $sizeRow->update([
                                'qty' => $newQty,
                                'in_stock' => $newQty > 0
                            ]);
                        }

                        $hasAnyStock = \Modules\Product\Entities\ProductSizeInventory::where('product_id', $prodId)
                            ->where('qty', '>', 0)
                            ->exists();

                        $prod = \Modules\Product\Entities\Product::find($prodId);
                        if ($prod) {
                            if ($prod->manage_stock && $prod->qty > 0) {
                                $prod->decrement('qty', min($prod->qty, $qty));
                            }
                            if (!$hasAnyStock) {
                                $prod->update(['in_stock' => false]);
                            }
                        }
                    }
                }
            }
        }
    }

    private function restoreStock(Order $order)
    {
        self::restoreFootwearSizes($order);

        $order->products->each(function (OrderProduct $orderProduct) {
            if ($orderProduct->product_variant) {
                if ($orderProduct->product_variant->manage_stock) {
                    $orderProduct->product_variant->increment('qty', $orderProduct->qty);
                }

                if ($orderProduct->product_variant->qty === 1) {
                    $orderProduct->product_variant->markAsInStock();
                }
            } else {
                if ($orderProduct->product->manage_stock) {
                    $orderProduct->product->increment('qty', $orderProduct->qty);
                }

                if ($orderProduct->product->qty > 0) {
                    $orderProduct->product->markAsInStock();
                }
            }
        });
    }


    private function reduceStock(Order $order)
    {
        self::reduceFootwearSizes($order);

        $order->products->each(function (OrderProduct $orderProduct) {
            if ($orderProduct->product_variant) {
                if (
                    $orderProduct->product_variant->manage_stock
                    && $orderProduct->product_variant->qty !== 0
                ) {
                    $orderProduct->product_variant->decrement('qty', $orderProduct->qty);
                }
            } else {
                if (
                    $orderProduct->product->manage_stock
                    && $orderProduct->product->qty !== 0
                ) {
                    $orderProduct->product->decrement('qty', $orderProduct->qty);
                }
            }
        });
    }
}

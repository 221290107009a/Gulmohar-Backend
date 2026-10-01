<?php

namespace Modules\Checkout\Services;

use Modules\Cart\CartTax;
use Modules\Cart\CartItem;
use Modules\Cart\Facades\Cart;
use Modules\Order\Entities\Order;
use Modules\Address\Entities\Address;
use Modules\FlashSale\Entities\FlashSale;
use Modules\Currency\Entities\CurrencyRate;
use Modules\Account\Entities\DefaultAddress;
use Modules\Shipping\Facades\ShippingMethod;

class OrderService
{
    public function create($request)
    {
        $this->mergeShippingAddress($request);
        $this->saveAddress($request);        
        $this->addShippingMethodToCart($request);

        return tap($this->store($request), function ($order) {
            $this->storeOrderProducts($order);
            $this->storeOrderDownloads($order);
            $this->storeFlashSaleProductOrders($order);
            $this->incrementCouponUsage($order);
            $this->attachTaxes($order);
            $this->reduceStock();
        });
    }


    public function reduceStock()
    {
        Cart::reduceStock();

        try {
            $items = request()->input('items', []);
            if (is_array($items) && count($items) > 0) {
                foreach ($items as $item) {
                    $productId = $item['product_id'] ?? ($item['id'] ?? null);
                    $sizeStr = $item['size'] ?? ($item['selectedSize'] ?? null);
                    $qtyToDeduct = (int)($item['quantity'] ?? ($item['qty'] ?? 1));

                    if ($productId && $sizeStr && $qtyToDeduct > 0) {
                        $sizeRow = \Modules\Product\Entities\ProductSizeInventory::where('product_id', $productId)
                            ->where('size', $sizeStr)
                            ->first();

                        if ($sizeRow) {
                            $newQty = max(0, $sizeRow->qty - $qtyToDeduct);
                            $sizeRow->update([
                                'qty' => $newQty,
                                'in_stock' => $newQty > 0
                            ]);
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            \Log::error('Size stock reduction error: ' . $e->getMessage());
        }
    }


    public function delete(Order $order)
    {
        $order->delete();

        Cart::restoreStock();
    }


    private function mergeShippingAddress($request)
    {
        $request->merge([
            'shipping' => $request->ship_to_a_different_address ? $request->shipping : $request->billing,
        ]);
    }

    private function saveAddress($request)
    {   
        if ($request->is('api/*')) {
            if ($request->newBillingAddress) {
                $address = Address::create($this->extractAddress($request->billing));

                DefaultAddress::create([
                    'address_id' => $address->id,
                    'customer_id' => $request->customer_id,
                ]);
            }

            if ($request->ship_to_a_different_address && $request->newShippingAddress) {
                Address::create($this->extractAddress($request->shipping));
            }
        } else {
            if (auth()->guest()) {
                return;
            }

            if ($request->newBillingAddress) {
                $address = auth()
                    ->user()
                    ->addresses()
                    ->create($this->extractAddress($request->billing));

                $this->makeDefaultAddress($address);
            }

            if ($request->ship_to_a_different_address && $request->newShippingAddress) {
                auth()
                    ->user()
                    ->addresses()
                    ->create($this->extractAddress($request->shipping));
            }
        }

    }


    private function extractAddress($data)
    {
        return [
            'customer_id' => $data['customer_id'] ?? auth()->id(),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'address_1' => $data['address_1'],
            'address_2' => $data['address_2'] ?? null,
            'city' => $data['city'],
            'state' => $data['state'],
            'zip' => $data['zip'],
            'country' => $data['country'],
        ];
    }


    private function makeDefaultAddress(Address $address)
    {
        if (
            auth()
                ->user()
                ->addresses()
                ->count() > 1
        ) {
            return;
        }

        DefaultAddress::create([
            'address_id' => $address->id,
            'customer_id' => auth()->id(),
        ]);
    }


    private function addShippingMethodToCart($request)
    {
        if (!Cart::allItemsAreVirtual() && !Cart::hasShippingMethod()) {
            Cart::addShippingMethod(ShippingMethod::get($request->shipping_method));
        }
    }


    private function store($request)
    {
        return Order::create([
            'customer_id' => auth()->id() ?? $request->customer_id,
            'customer_email' => $request->customer_email,
            'customer_phone' => $request->customer_phone,
            'customer_first_name' => $request->billing['first_name'],
            'customer_last_name' => $request->billing['last_name'],
            'billing_first_name' => $request->billing['first_name'],
            'billing_last_name' => $request->billing['last_name'],
            'billing_address_1' => $request->billing['address_1'],
            'billing_address_2' => $request->billing['address_2'] ?? null,
            'billing_city' => $request->billing['city'],
            'billing_state' => $request->billing['state'],
            'billing_zip' => $request->billing['zip'],
            'billing_country' => $request->billing['country'],
            'shipping_first_name' => $request->shipping['first_name'],
            'shipping_last_name' => $request->shipping['last_name'],
            'shipping_address_1' => $request->shipping['address_1'],
            'shipping_address_2' => $request->shipping['address_2'] ?? null,
            'shipping_city' => $request->shipping['city'],
            'shipping_state' => $request->shipping['state'],
            'shipping_zip' => $request->shipping['zip'],
            'shipping_country' => $request->shipping['country'],
            'sub_total' => Cart::subTotal()->amount(),
            'shipping_method' => Cart::shippingMethod()->name(),
            'shipping_cost' => Cart::shippingCost()->amount(),
            'coupon_id' => Cart::coupon()->id(),
            'discount' => Cart::discount()->amount(),
            'total' => Cart::total()->amount(),
            'payment_method' => $request->payment_method,
            'currency' => currency(),
            'currency_rate' => CurrencyRate::for(currency()),
            'locale' => locale(),
            'status' => Order::PENDING_PAYMENT,
            'note' => $request->order_note,
        ]);
    }


    private function storeOrderProducts(Order $order)
    {
        Cart::items()->each(function (CartItem $cartItem) use ($order) {
            $order->storeProducts($cartItem);
        });
    }


    private function storeOrderDownloads(Order $order)
    {
        Cart::items()->each(function (CartItem $cartItem) use ($order) {
            $order->storeDownloads($cartItem);
        });
    }


    private function storeFlashSaleProductOrders(Order $order)
    {
        Cart::items()->each(function (CartItem $cartItem) use ($order) {
            if (!FlashSale::contains($cartItem->product)) {
                return;
            }

            FlashSale::pivot($cartItem->product)
                ->orders()
                ->attach([
                    $cartItem->product->id => [
                        'order_id' => $order->id,
                        'qty' => $cartItem->qty,
                    ],
                ]);
        });
    }


    private function incrementCouponUsage()
    {
        Cart::coupon()->usedOnce();
    }


    private function attachTaxes(Order $order)
    {
        Cart::taxes()->each(function (CartTax $cartTax) use ($order) {
            $order->attachTax($cartTax);
        });
    }
}

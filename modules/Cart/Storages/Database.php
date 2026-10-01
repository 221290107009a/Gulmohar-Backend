<?php

namespace Modules\Cart\Storages;

use Modules\Cart\Entities\Cart;
use Darryldecode\Cart\CartCollection;

class Database
{
    public function get($key)
    {
        $customerId = session()->get('customer_id') ?? request('customer_id');
        if ($customerId) {
            $isConditions = strpos($key, 'cart_conditions') !== false;
            $suffix = $isConditions ? '%_cart_conditions' : '%_cart_items';
            $row = Cart::where('customer_id', $customerId)
                ->where('id', 'like', $suffix)
                ->latest('updated_at')
                ->first();
            if ($row) {
                return new CartCollection($row->data);
            }
        }
        if ($this->has($key)) {
            return new CartCollection(Cart::find($key)->data);
        } else {
            return [];
        }
    }


    public function put($key, $value)
    {
        $customerId = session()->get('customer_id') ?? request('customer_id');
        $isEmpty = empty($value) || ($value instanceof CartCollection && $value->isEmpty());

        if ($customerId) {
            $isConditions = strpos($key, 'cart_conditions') !== false;
            $suffix = $isConditions ? '%_cart_conditions' : '%_cart_items';

            if ($isEmpty) {
                Cart::where('customer_id', $customerId)
                    ->where('id', 'like', $suffix)
                    ->delete();
                return;
            }

            $row = Cart::where('customer_id', $customerId)
                ->where('id', 'like', $suffix)
                ->latest('updated_at')
                ->first();
            if ($row) {
                $row->data = $value;
                $row->save();
                return;
            }
        }

        if ($isEmpty) {
            Cart::destroy($key);
            return;
        }

        if ($row = Cart::find($key)) {
            $row->data = $value;
            if ($customerId && !$row->customer_id) {
                $row->customer_id = $customerId;
            }
            $row->save();
        } else {
            Cart::create([
                'id' => $key,
                'customer_id' => $customerId ?? null,
                'data' => $value,
            ]);
        }
    }


    private function has($key)
    {
        $customerId = session()->get('customer_id') ?? request('customer_id');
        if ($customerId) {
            $isConditions = strpos($key, 'cart_conditions') !== false;
            $suffix = $isConditions ? '%_cart_conditions' : '%_cart_items';
            if (Cart::where('customer_id', $customerId)->where('id', 'like', $suffix)->exists()) {
                return true;
            }
        }
        return Cart::find($key);
    }
}

<?php

namespace Modules\Checkout\Http\Controllers;

use Exception;
use Modules\Support\Country;
use Modules\Cart\Facades\Cart;
use Modules\Page\Entities\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;
use Modules\Payment\Facades\Gateway;
use Illuminate\Contracts\View\Factory;
use Modules\Coupon\Checkers\ValidCoupon;
use Modules\Coupon\Checkers\CouponExists;
use Modules\Coupon\Checkers\MinimumSpend;
use Modules\Coupon\Checkers\MaximumSpend;
use Modules\User\Services\CustomerService;
use Modules\Checkout\Services\OrderService;
use Modules\Coupon\Checkers\AlreadyApplied;
use Modules\Account\Entities\DefaultAddress;
use Modules\Coupon\Checkers\ExcludedProducts;
use Modules\Coupon\Checkers\ApplicableProducts;
use Modules\Coupon\Checkers\ExcludedCategories;
use Illuminate\Contracts\Foundation\Application;
use Modules\Coupon\Checkers\UsageLimitPerCoupon;
use Modules\Coupon\Checkers\ApplicableCategories;
use Modules\Order\Http\Requests\StoreOrderRequest;
use Modules\Coupon\Checkers\UsageLimitPerCustomer;
use Modules\Cart\Http\Middleware\CheckCartItemsStock;
use Modules\Cart\Http\Middleware\RedirectIfCartIsEmpty;
use Illuminate\Http\Request;
use Modules\Order\Entities\Order;
use Modules\Checkout\Mail\NewOrderReceipt;
use Illuminate\Support\Facades\Mail;

class CheckoutController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware([
            RedirectIfCartIsEmpty::class,
        ]);

        $this->middleware([
            CheckCartItemsStock::class,
        ])->only('store');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param StoreOrderRequest $request
     * @param CustomerService $customerService
     * @param OrderService $orderService
     *
     * @return JsonResponse
     */
    public function store(StoreOrderRequest $request, CustomerService $customerService, OrderService $orderService)
    {
        if (auth()->guest() && $request->create_an_account) {
            $customerService->register($request)->login();
        }

        $order = $orderService->create($request);        
        
        $gateway = Gateway::get($request->payment_method);

        try {
            $response = $gateway->purchase($order, $request);
        } catch (Exception $e) {
            $orderService->delete($order);

            return response()->json([
                'message' => $e->getMessage(),
            ], 403);
        }

        return response()->json($response);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
     */
    public function create(): View|Factory|Application
    {
        Cart::clearCartConditions();

        return view('storefront::public.checkout.create', [
            'cart' => Cart::instance(),
            'countries' => Country::supported(),
            'gateways' => Gateway::all(),
            'defaultAddress' => auth()->user()->defaultAddress ?? new DefaultAddress,
            'addresses' => $this->getAddresses(),
            'termsPageURL' => Page::urlForPage(setting('storefront_terms_page')),
        ]);
    }


    /**
     * Get addresses for the logged in user.
     *
     * @return Collection
     */
    private function getAddresses()
    {
        if (auth()->guest()) {
            return collect();
        }

        return auth()->user()->addresses->keyBy('id');
    }


    public function apiStore(Request $request,OrderService $orderService)
    {
        $customerId = $request->user_id ?? $request->customer_id;
        if ($customerId) {
            if (is_numeric($customerId)) {
                $isBahujan = false;
                if ($request->order_note && strtoupper($request->order_note) === 'BAHUJAN_SAHITYA') {
                    $isBahujan = true;
                } elseif ($request->note && strtoupper($request->note) === 'BAHUJAN_SAHITYA') {
                    $isBahujan = true;
                }
                $suffix = $isBahujan ? '_bahujan' : '_store';
                $customerId = $customerId . $suffix;
            }

            session()->put('customer_id', $customerId);

            $cartRow = \Modules\Cart\Entities\Cart::where('customer_id', $customerId)->latest('updated_at')->first();
            if ($cartRow) {
                session()->setId($cartRow->id);
                session()->start();
            }
        }

        if ($request->has('items') && is_array($request->items)) {
            foreach ($request->items as $item) {
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

        if (Cart::instance()->isEmpty()) {
            return response()->json([
                'status' => true,
                'message' => 'Order placed successfully',
            ]);
        } else {            
            $order = $orderService->create($request);
            
            $gateway = Gateway::get($request->payment_method);

            try {
                $response = $gateway->purchase($order, $request);
                Mail::to($order->customer_email)
                ->send(new NewOrderReceipt($order));
            
                Cart::clear();
            } catch (Exception $e) {
                $orderService->delete($order);

                return response()->json([
                    'message' => $e->getMessage(),
                ], 403);
            }
        }
        return response()->json([
            'success' => true,
            'message' => 'Order placed successfully',
            'data' => $response            
        ]);
    }
}

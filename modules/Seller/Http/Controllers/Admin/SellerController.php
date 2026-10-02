<?php

namespace Modules\Seller\Http\Controllers\Admin;

use Modules\Seller\Entities\Seller;
use Modules\Admin\Traits\HasCrudActions;
use Modules\Seller\Http\Requests\SaveSellerRequest;
use Illuminate\Support\Facades\Mail;

class SellerController
{
    use HasCrudActions;

    /**
     * Model for the resource.
     *
     * @var string
     */
    protected $model = Seller::class;

    /**
     * Label of the resource.
     *
     * @var string
     */
    protected $label = 'seller::sellers.seller';

    /**
     * View path of the resource.
     *
     * @var string
     */
    protected $viewPath = 'seller::admin.sellers';

    /**
     * Form requests for the resource.
     *
     * @var array|string
     */
    protected $validation = SaveSellerRequest::class;

    public function store(SaveSellerRequest $request)
    {
        $seller = Seller::create($request->all());
        
        // Send email to admin
        $adminEmail = 'admin@example.com';
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

        return redirect()
            ->route('admin.sellers.index')
            ->withSuccess(trans('admin::messages.resource_saved', ['resource' => trans('seller::sellers.seller')]));
    }

    public function update(SaveSellerRequest $request, $id)
    {
        $seller = Seller::findOrFail($id);
        $oldStatus = $seller->current_status;
        
        $seller->update($request->all());

        // If status has changed, send notification email
        if ($oldStatus !== $seller->current_status) {
            // Send email to seller about status update
            Mail::send('seller::emails.status_update', [
                'seller' => $seller,
                'oldStatus' => $oldStatus
            ], function ($message) use ($seller) {
                $message->to($seller->email)
                        ->subject('Your Seller Status Has Been Updated');
            });

            // Send email to admin about status update
            $adminEmail = 'admin@example.com';
            Mail::send('seller::admin.sellers.emails.status_update', [
                'seller' => $seller,
                'oldStatus' => $oldStatus
            ], function ($message) use ($adminEmail, $seller) {
                $message->to($adminEmail)
                        ->subject('Seller Status Updated - ' . $seller->shop_name);
            });
        }

        return redirect()
            ->route('admin.sellers.index')
            ->withSuccess(trans('admin::messages.resource_saved', ['resource' => trans('seller::sellers.seller')]));
    }
}

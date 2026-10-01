<?php

namespace Modules\Checkout\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Modules\Media\Entities\File;
use Modules\Order\Entities\Order;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewOrderReceipt extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The instance of the order.
     *
     * @var Order
     */
    public $order;


    /**
     * Create a new message instance.
     *
     * @param Order $order
     *
     * @return void
     */
    public function __construct($order)
    {
        $this->order = $order;
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        app()->setLocale($this->order->locale);

        $this->order->load('products');

        return $this->subject(trans('checkout::mail.new_order_subject'))
            ->view("storefront::emails.new_order_receipt", [
                'logo' => File::findOrNew(setting('storefront_mail_logo'))->path,
            ]);
    }

}

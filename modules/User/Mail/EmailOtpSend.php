<?php

namespace Modules\User\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Modules\User\Entities\User;
use Modules\Media\Entities\File;
use Illuminate\Queue\SerializesModels;

class EmailOtpSend extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Reset complete form url.
     *
     * @var string
     */
    public $otp;


    /**
     * Create a new instance.
     *
     * @param User $user
     * @param string $url
     *
     * @return void
     */
    public function __construct($otp)
    {
        $this->otp = $otp;        
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject("Email Otp Varification")
            ->view("storefront::emails.{$this->getViewName()}", [
                'logo' => File::findOrNew(setting('storefront_mail_logo'))->path,
            ]);
    }


    private function getViewName()
    {
        return 'email_otp' . (is_rtl() ? '_rtl' : '');
    }
}

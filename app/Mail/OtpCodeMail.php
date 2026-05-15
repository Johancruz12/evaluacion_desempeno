<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $code, public int $ttlMinutes, public string $name)
    {
    }

    public function build()
    {
        return $this->subject('Código de verificación — JUnical')
            ->view('emails.otp-code')
            ->text('emails.otp-code-plain');
    }
}

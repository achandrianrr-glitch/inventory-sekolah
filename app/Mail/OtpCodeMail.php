<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otp,
        public string $email,
        public string $expiresText
    ) {}

    public function build()
    {
        return $this->subject('Kode OTP Verifikasi Email')
            ->view('emails.otp');
    }
}

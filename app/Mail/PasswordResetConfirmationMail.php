<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $email;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->name = $data->first_name;
        $this->email = $data->email;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        \Log::info('Send password reset confirmation mail:'.$this->email);

        return $this->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
                ->view('email.passwordresetconfirmation')
                ->subject('Loan App | Password Reset Confirmation')
                ->with([
                    'name' => $this->name,
                    'login_url' => env('APP_FE_URL').'/login'
        ]);
    }
}

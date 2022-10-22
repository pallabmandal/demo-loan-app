<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendPasswordResetTokenMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $link;
    public $email;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->name = $data['name'];
        $this->link = $data['url'];
        $this->email = $data['email'];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        \Log::info('Send password reset mail for email:'.$this->email);

        return $this->from(env('MAIL_FROM_ADDRESS'),  env('MAIL_FROM_NAME'))
                ->view('email.passwordreset')
                ->subject('Loan App | Password reset request')
                ->with([
                    'name' => $this->name,
                    'reset_link' => $this->link
        ]);
    }
}

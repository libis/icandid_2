<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class Mail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($body,$subject)
    {
        $this->body = $body;
        $this->subject = $subject;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $e = $this->view('mail')->with(["body"=>$this->body])->subject($this->subject);
        if (config('mail.from')) {
            $e->from(config('mail.from'));
        }
        if (config('mail.bcc')) {
            $e->bcc(config('mail.bcc'));
        }
        return $e;
    }
}

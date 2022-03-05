<?php

namespace Railroad\Crux\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Agnostic extends Mailable
{
    use Queueable, SerializesModels;

    public function build()
    {
        return $this;
    }
}

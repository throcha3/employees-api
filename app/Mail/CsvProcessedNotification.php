<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CsvProcessedNotification extends Mailable
{
    use Queueable;
    use SerializesModels;

    public string $recipient;
    public string $fileName;

    public function __construct(string $recipient, string $fileName)
    {
        $this->recipient = $recipient;
        $this->fileName = $fileName;
    }

    public function build(): self
    {
        return $this->to($this->recipient)
            ->subject('CSV Processed')
            ->text('mail.csv_processed_plain')
            ->with(['fileName' => $this->fileName]);
    }
}

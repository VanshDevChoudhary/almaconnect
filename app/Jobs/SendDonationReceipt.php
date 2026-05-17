<?php

namespace App\Jobs;

use App\Mail\DonationReceiptMail;
use App\Models\Donation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SendDonationReceipt implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [60, 300, 900];

    public function __construct(public Donation $donation)
    {
    }

    public function handle(): void
    {
        $this->donation->loadMissing('user', 'campaign');

        $pdf = Pdf::loadView('pdfs.donation-receipt', ['donation' => $this->donation]);
        $contents = $pdf->output();

        $path = "receipts/{$this->donation->id}.pdf";
        Storage::disk('public')->put($path, $contents);
        $this->donation->update(['receipt_path' => $path]);

        if ($this->donation->user) {
            Mail::to($this->donation->user->email)
                ->send(new DonationReceiptMail($this->donation, $contents));
        }
    }
}

<?php

namespace App\Services;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class InvoicePdfService
{
    /**
     * Genera e salva il PDF della fattura
     */
    public function generate(Invoice $invoice): string
    {
        $invoice->load([
            'invoiceItems.service',
            'doctor.user',
            'patient',
        ]);

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
        ]);

        $directory = 'invoices/'.$invoice->year;

        if (! Storage::exists($directory)) {
            Storage::makeDirectory($directory);
        }

        $filename = $invoice->uuid.'.pdf';

        $path = $directory.'/'.$filename;

        Storage::put(
            $path,
            $pdf->output()
        );

        return $path;
    }

    /**
     * Restituisce il PDF della fattura.
     * Se non esiste lo genera.
     */
    public function getPdfPath(Invoice $invoice): string
    {

        if ($invoice->pdf_path && Storage::exists($invoice->pdf_path)) {

            return $invoice->pdf_path;

        }

        $path = $this->generate($invoice);

        $invoice->update([
            'pdf_path' => $path,
        ]);

        return $path;
    }
}

<?php

namespace App\Livewire\Documents;

use App\Models\Document;
use App\Services\DocumentService;
use App\Services\PdfService;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class Show extends Component
{
    public Document $document;

    public bool $showEmailModal = false;

    public bool $showWhatsAppModal = false;

    public string $emailTo = '';

    public string $emailSubject = '';

    public string $emailMessage = '';

    public string $whatsappPhone = '';

    public function mount(Document $document): void
    {
        $this->document = $document->load(['items', 'customer', 'creator', 'referenceDocument', 'lead', 'childDocuments']);
        $this->emailTo = $document->customer_email ?? '';
        $this->emailSubject = $document->typeLabel().' '.$document->document_number;
        $this->emailMessage = "Dear {$document->customer_name},\n\nPlease find attached our {$document->typeLabel()} {$document->document_number} for ₹".number_format($document->grand_total, 2).".\n\nThank you.";
        $this->whatsappPhone = preg_replace('/\D/', '', $document->customer_phone ?? '');
    }

    public function markSent(): void
    {
        $this->document->update(['status' => 'sent', 'sent_at' => now()]);
        $this->document->refresh();
        $this->dispatch('notify', message: 'Marked as sent');
    }

    public function markAccepted(): void
    {
        $this->document->update(['status' => 'accepted']);
        $this->document->refresh();
        $this->dispatch('notify', message: 'Marked as accepted');
    }

    public function markPaid(): void
    {
        $this->document->update(['status' => 'paid', 'paid_at' => now()]);
        $this->document->refresh();
        $this->logActivity('marked_paid', 'Document marked as paid');
        $this->dispatch('notify', message: 'Marked as paid');
    }

    public function duplicate(DocumentService $service): void
    {
        if (! auth()->user()->hasPermission('documents.create')) {
            abort(403);
        }

        $newDoc = $service->duplicateDocument($this->document);
        session()->flash('success', 'Document duplicated successfully');
        $this->redirect(route('leads.documents.show', $newDoc), navigate: true);
    }

    public function deleteDocument(): void
    {
        if (! auth()->user()->hasPermission('documents.create')) {
            abort(403);
        }

        $this->logActivity('deleted', 'Document deleted: '.$this->document->document_number);
        $this->document->delete();
        session()->flash('success', 'Document deleted');
        $this->redirect(route('leads.documents'), navigate: true);
    }

    public function openEmailModal(): void
    {
        $this->showEmailModal = true;
    }

    public function closeEmailModal(): void
    {
        $this->showEmailModal = false;
    }

    public function sendEmail(): void
    {
        $this->validate([
            'emailTo' => 'required|email',
            'emailSubject' => 'required|string|max:255',
            'emailMessage' => 'required|string',
        ]);

        try {
            $pdfContent = app(PdfService::class)->generateDocumentPdf($this->document)->output();
            $filename = str_replace('/', '-', $this->document->document_number).'.pdf';

            Mail::raw($this->emailMessage, function ($mail) use ($pdfContent, $filename) {
                $mail->to($this->emailTo)
                    ->subject($this->emailSubject)
                    ->attachData($pdfContent, $filename, ['mime' => 'application/pdf']);
            });
            $sent = true;
        } catch (\Throwable) {
            $sent = false;
        }

        $this->document->update(['status' => $this->document->status === 'draft' ? 'sent' : $this->document->status, 'sent_at' => $this->document->sent_at ?? now()]);
        $this->document->refresh();

        $note = $sent ? 'Email sent to '.$this->emailTo : 'Email logged (mail not configured)';
        $this->logActivity('email', $note, ['email' => $this->emailTo, 'sent' => $sent]);

        $this->showEmailModal = false;
        $this->dispatch('notify', message: $sent ? 'Email sent successfully' : 'Email logged to activity (configure mail on server)');
    }

    public function openWhatsAppModal(): void
    {
        $this->showWhatsAppModal = true;
    }

    public function closeWhatsAppModal(): void
    {
        $this->showWhatsAppModal = false;
    }

    public function sendWhatsApp(): void
    {
        $phone = preg_replace('/\D/', '', $this->whatsappPhone);
        if (strlen($phone) < 10) {
            $this->addError('whatsappPhone', 'Enter a valid phone number');

            return;
        }

        $message = urlencode("Hi {$this->document->customer_name}, your {$this->document->typeLabel()} {$this->document->document_number} for ₹".number_format($this->document->grand_total, 2).' is ready. View: '.route('leads.documents.show', $this->document));
        $waLink = 'https://wa.me/'.$phone.'?text='.$message;

        $this->logActivity('whatsapp', 'WhatsApp link generated for +'.$phone, ['link' => $waLink]);
        $this->showWhatsAppModal = false;
        $this->dispatch('notify', message: 'WhatsApp link logged — opening chat');
        $this->dispatch('open-url', url: $waLink);
    }

    public function convertTo(string $type, DocumentService $service): void
    {
        if (! auth()->user()->hasPermission('documents.create')) {
            abort(403);
        }

        if (! $service->canConvert($this->document, $type)) {
            $this->dispatch('notify', message: 'Invalid conversion path', type: 'error');

            return;
        }

        $newDoc = $service->convertDocument($this->document, $type);
        $this->redirect(route('leads.documents.show', $newDoc), navigate: true);
    }

    protected function logActivity(string $type, string $title, ?array $meta = null): void
    {
        if (! $this->document->lead_id) {
            return;
        }

        $lead = $this->document->lead;
        if ($lead) {
            $lead->logActivity($type, $title, $meta ? json_encode($meta) : null);
        }
    }

    public function render()
    {
        $service = app(DocumentService::class);
        $canConvertProforma = $service->canConvert($this->document, 'proforma');
        $canConvertInvoice = $service->canConvert($this->document, 'invoice');
        $accent = $this->document->theme_color ?? '#7c3aed';
        $isUsd = $this->document->currency === 'USD';
        $docSymbol = $isUsd ? '$' : '₹';
        $hasRate = $this->document->exchange_rate && $this->document->exchange_rate > 0;
        $convertedTotal = $hasRate
            ? round($isUsd
                ? $this->document->grand_total * $this->document->exchange_rate
                : $this->document->grand_total / $this->document->exchange_rate, 2)
            : null;
        $convertedLabel = $isUsd ? 'INR Equivalent' : 'USD Equivalent';
        $convertedSymbol = $isUsd ? '₹' : '$';

        return view('livewire.documents.show', compact(
            'canConvertProforma', 'canConvertInvoice', 'accent',
            'docSymbol', 'convertedTotal', 'convertedLabel', 'convertedSymbol'
        ))->layout('layouts.app');
    }
}

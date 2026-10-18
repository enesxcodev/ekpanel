<?php

namespace App\Mail;

use App\Models\Forms\FormSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class FormSubmissionReceived extends Mailable
{
    use Queueable;

    public function __construct(public FormSubmission $submission) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Yeni form başvurusu: '.$this->submission->form_name);
    }

    public function content(): Content
    {
        return new Content(view: 'mail.form-submission');
    }
}

<?php

namespace App\Mail;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PersonSetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;

    public Clinic $clinic;

    public string $token;

    protected string $url;

    protected string $logoPath;

    public function __construct(User $user, Clinic $clinic, string $token)
    {
        $this->user = $user;
        $this->token = $token;
        $this->clinic = $clinic;
        $this->logoPath = public_path('images/logo_clinika.png');

        $appUrl = parse_url(config('app.url'));

        $scheme = $appUrl['scheme'] ?? 'http';
        $port = isset($appUrl['port']) ? ':'.$appUrl['port'] : '';

        $tenantUrl = "{$scheme}://{$clinic->slug}.".config('app.tenant_domain').$port;

        $path = route('password.reset', [
            'token' => $this->token,
        ], false);

        $this->url = $tenantUrl.$path;
    }

    public function build()
    {
        return $this->subject('Imposta la tua password - Clinika')
            ->view('emails.persons.set-password', [
                'user' => $this->user,
                'clinic' => $this->clinic,
                'url' => $this->url,
                'logoCid' => $this->logoPath,
            ])
            ->withSymfonyMessage(function ($message) {
                $logoCid = $message->embedFromPath(
                    $this->logoPath,
                    'logo_cid'
                );

                $this->with([
                    'logoCid' => $logoCid,
                ]);
            });
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Clinika - Imposta la tua password',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.persons.set-password',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

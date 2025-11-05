<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class DoctorSetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;


    public User $user;
    public string $token;
    protected string $url;
    protected string $logoPath;
    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $token)
    {
        $this->user = $user;
        $this->token = $token;
        $this->logoPath = public_path('images/logo_clinika.png');
        $this->url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $this->user->email,
        ], false));
    }

    public function build()
    {
        return $this->subject('Imposta la tua password - Clinika')
                    ->view('emails.doctors.set-password', ['user' => $this->user, 'url' => $this->url, 'logoCid' => $this->logoPath])->withSymfonyMessage(function ($message) {
                        $logoCid = $message->embedFromPath($this->logoPath, 'logo_cid');
                        // Passiamo il CID nel contesto della view
                        $this->with(['logoCid' => $logoCid]);
                    });
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Clinika - Cambio password richiesto',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.doctors.set-password',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

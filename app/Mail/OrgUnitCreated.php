<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrgUnitCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $password;
    public $orgUnit;
    /**
     * Create a new message instance.
     */
    public function __construct($user, $password, $orgUnit)
    {
        $this->user = $user;
        $this->password = $password;
        $this->orgUnit = $orgUnit;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Organizational Unit User Created',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emailtemplates.ou_created',
            with: [
                'firstname' => $this->user->fname,
                'lastname' => $this->user->lname,
                'org_unit' => $this->orgUnit->org_unit_name,
                'username' => $this->user->email,
                'password' => $this->password, // Sending plain text password (for example)
                'login_url' => url('/login'), // URL for login page
                'site_url' => url('/') // Dynamic URL for the site's homepage
            ]
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

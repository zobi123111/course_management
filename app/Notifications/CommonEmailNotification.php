<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommonEmailNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct($messages)
    {
        $this->messages = $messages;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mailMessage = new MailMessage;
        $mailMessage->greeting(isset($this->messages['greeting-text']) ? $this->messages['greeting-text'] : '');
        $mailMessage->subject(isset($this->messages['subject']) ? $this->messages['subject'] : 'Notification Email');
            $lineItems = $this->messages['lines_array'];

            foreach ($lineItems as $key => $value) { 
                if (strpos($key, 'special_') === 0) {
                    $specialLabel = ucwords(str_replace('_', ' ', str_replace('special_', '', $key)));
                    $mailMessage->line( $specialLabel . ': ' . $value);
                } else {
                    $mailMessage->line($value);
                }
            }
            $mailMessage->action(isset($this->messages['url-title']) ? $this->messages['url-title'] : 'Action Not Required', isset($this->messages['url']) ? url($this->messages['url']) : '#');
            $mailMessage->line(isset($this->messages['additional-info']) ? $this->messages['additional-info'] : '');
            $mailMessage->line(isset($this->messages['thanks-message']) ? $this->messages['thanks-message'] : '');
            
            return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}

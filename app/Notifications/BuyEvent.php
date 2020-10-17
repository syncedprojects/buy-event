<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Purchase;
use Illuminate\Notifications\Messages\NexmoMessage;

class BuyEvent extends Notification
{
    use Queueable;

    /**
     * The Purchase object.
     *
     * @var App\Models\Purchase
     */
    private $purchase;

    /**
     * A fake url for the purchase.
     *
     * @var string
     */
    private $purchaseUrl;

    /**
     * One of the predefined channel names.
     *
     * @var string
     */
    private $channel;

    /**
     * Create a new notification instance.
     *
     * @param  Purchase  $purchase
     * @param  string  $channel
     * @return void
     */
    public function __construct(Purchase $purchase, $channel)
    {
        $this->purchase = $purchase;
        $this->purchaseUrl = url('/purchases/' . $this->purchase->id);
        $this->channel = $channel;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $channel = ($this->channel == 'sms') ? 'nexmo' : $this->channel;
        return [$channel];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your purchase was successful.')
            ->line('Details:')
            ->line('Product title: ' . $this->purchase->product_title)
            ->line('Purchase price: ' . $this->purchase->purchase_price)
            ->action('Go to the website', $this->purchaseUrl)
            ->line('Thank you!');
    }

    /**
     * Get the array representation of the notification (database and broadcasting).
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'product_title' => $this->purchase->product_title,
            'purchase_price' => $this->purchase->purchase_price,
            'purchase_url' => $this->purchaseUrl,
        ];
    }

    /**
     * Get the Nexmo / SMS representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return NexmoMessage
     */
    public function toNexmo($notifiable)
    {
        return (new NexmoMessage)->content('Your purchase was successful. Product: ' . $this->purchase->product_title . ', Price: ' . $this->purchase->purchase_price . '. Thank you! (' . $this->purchaseUrl . ')');
    }
}
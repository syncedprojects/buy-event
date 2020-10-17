<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Exception;
use Log;
use App\Notifications\BuyEvent;
use App\Models\Customer;
use App\Models\Purchase;

class SendPurchaseNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'purchase-notification:send
                            {channel : database, mail, sms}
                            {--customer_id= : The ID of customer (for testing default customer is returned if not found instead of error)}
                            {--phone= : The phone of customer (for testing a customer is generated based on this value)}
                            {--email= : The email of customer (for testing a customer is generated based on this value)}
                            {--purchase_id= : The ID of the purchase (purchase gets generated randomly)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends purchase notifications to customers on various channels (database, mail, sms).';

    /**
     * Available sending channels.
     *
     * @var array
     */
    protected $channels = ['database', 'mail', 'sms'];

    /**
     * Operator selected channel name.
     *
     * @var string
     */
    protected $notificationChannel;

    /**
     * The Customer object.
     *
     * @var App\Models\Customer
     */
    protected $customer;

    /**
     * The Purchase object.
     *
     * @var App\Models\Purchase
     */
    protected $purchase;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show help message.
     *
     * @return void
     */
    private function showHelpMessage()
    {
        $this->info('For reference type `php artisan help purchase-notification:send`');
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            if (! $this->setNotificationChannel()) {
                return 1;
            }
            if (! $this->setCustomer()) {
                return 1;
            }
            if (! $this->setPurchase()) {
                return 1;
            }
            $this->customer->notify(new BuyEvent($this->purchase, $this->notificationChannel));
            $this->info('Notification sent.');
            return 0;
        }
        catch (Exception $e) {
            $exceptionMessage = $e->getMessage();
            /* write exception output */
            $this->error($exceptionMessage);
            /* log the errors on the dedicated channel for purchases */
            Log::channel('purchase')->error('Error sending purchase notification: ' . $exceptionMessage);
            return 1;
        }
    }

    /**
     * Set notification channel from user input.
     *
     * @return boolean
     */
    private function setNotificationChannel()
    {
        $this->notificationChannel = $this->argument('channel');
        if (! in_array($this->notificationChannel, $this->channels)) {
            $this->error('`channel` argument accepts only the following options: ' . implode(', ', $this->channels));
            $this->showHelpMessage();
            return false;
        }
        return true;
    }

    /**
     * Set Customer object from user input.
     *
     * @return boolean
     */
    private function setCustomer()
    {
        $customerId = (int)$this->option('customer_id');
        $phone = preg_replace('/[^0-9]/', '', $this->option('phone'));
        $phoneValid = filter_var($phone, FILTER_VALIDATE_INT);
        $email = $this->option('email');
        $emailValid = filter_var($email, FILTER_VALIDATE_EMAIL);
        if ( $customerId == 0 && ! $phoneValid && ! $emailValid ) {
            $this->error('Please, supply a valid `customer_id` or `phone` or `email`.');
            $this->showHelpMessage();
            return false;
        }
        if ($customerId) {
            $this->customer = Customer::find($customerId);
            /* if customer not found generate a default fake one */
            if (! $this->customer) {
                $someCustomer = new Customer;
                $someCustomer->id = $customerId;
                $someCustomer->phone = '998909760486';
                $someCustomer->email = 'wndrlst.projects@gmail.com';
                $this->customer = $someCustomer;
            }
        }
        else if ($phoneValid || $emailValid) {
            $this->customer = Customer::where('phone', $phone)->orWhere('email', $email)->first();
            /* if customer not found generate a fake one with the supplied phone and email */
            if (! $this->customer) {
                $someCustomer = new Customer;
                $someCustomer->id = 1;
                if ($phoneValid) {
                    $someCustomer->phone = $phone;
                }
                if ($emailValid) {
                    $someCustomer->email = $email;
                }
                $this->customer = $someCustomer;
            }
        }
        if ($this->notificationChannel == 'mail' && ! isset($this->customer->email)) {
            $this->error('Please, supply a valid `email`.');
            $this->showHelpMessage();
            return false;
        }
        if ($this->notificationChannel == 'sms' && ! isset($this->customer->phone)) {
            $this->error('Please, supply a valid `phone`.');
            $this->showHelpMessage();
            return false;
        }
        return true;
    }

    /**
     * Set Purchase object from user input.
     *
     * @return boolean
     */
    private function setPurchase()
    {
        $purchaseId = (int)$this->option('purchase_id');
        if (! isset($purchaseId) || $purchaseId == '' || ! $purchaseId) {
            $this->error('Please, supply a valid `purchase_id`.');
            $this->showHelpMessage();
            return false;
        }
        $this->purchase = Purchase::find($purchaseId);
        if (! $this->purchase) {
            $this->purchase = $this->generateRandomPurchase($purchaseId);
        }
        return true;
    }

    /**
     * Generate a random purchase object.
     *
     * @param  int  $purchaseId
     * @return App\Models\Purchase
     */
    private function generateRandomPurchase($purchaseId)
    {
        $randomProducts = [
            ['title' => 'Acer Laptop', 'price' => 300.00],
            ['title' => 'Apple iPad mini', 'price' => 349.99],
            ['title' => 'iPhone X', 'price' => 800],
            ['title' => 'Samsung SM-G950 Galaxy S8', 'price' => 499.99],
            ['title' => 'Monoblock MYPRO T22 LED 21.5', 'price' => 264],
        ];
        $randomProduct = $randomProducts[ array_rand( $randomProducts ) ];
        $somePurchase = new Purchase;
        $somePurchase->id = $purchaseId;
        $somePurchase->product_title = $randomProduct['title'];
        $somePurchase->purchase_price = $randomProduct['price'];
        return $somePurchase;
    }
}

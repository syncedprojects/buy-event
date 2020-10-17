<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Exception;
use Log;

class SendPurchaseNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'purchase-notification:send {channel : database, mail, nexmo (sms)} {customer_id} {purchase_id : The ID of the purchase (Purchase is generated randomly)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends purchase notifications to customers on various channels (database, mail, nexmo).';

    /**
     * Available sending channels.
     *
     * @var array
     */
    protected $channels = ['database', 'mail', 'sms'];

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
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $channel = $this->argument('channel');
        if (! in_array($channel, $this->channels)) {
            $this->error('`channel` argument accepts only the following options: ' . implode(', ', $this->channels));
            $this->info('For reference type `php artisan help purchase-notification:send`');
            return 1;
        }
        
        try {
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
}

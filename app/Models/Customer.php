<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Customer extends Model
{
    use Notifiable;
    
    protected $table = 'customers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'phone', 'email',
    ];

    /**
     * Get the notification routing information for the given driver.
     *
     * @param  string  $driver
     * @param  \Illuminate\Notifications\Notification|null  $notification
     * @return mixed
     */
    public function routeNotificationFor($driver, $notification = null)
    {
        if (method_exists($this, $method = 'routeNotificationFor'.Str::studly($driver))) {
            return $this->{$method}($notification);
        }

        switch ($driver) {
            case 'database':
                return $this->notifications();
            case 'mail':
                return $this->email;
            case 'nexmo':
                return $this->phone;
        }
    }
}

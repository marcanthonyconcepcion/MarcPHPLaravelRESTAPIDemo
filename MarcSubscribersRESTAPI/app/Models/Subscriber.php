<?php
/*
 * Copyright (c) 2021.
 * Marc Concepcion
 * marcanthonyconcepcion@gmail.com
 */

namespace App\Models;

use Database\Factories\SubscriberFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Subscriber extends Model
{
    use HasFactory;
    protected $fillable = ['email_address','last_name','first_name', 'activation_flag'];

    /** @return SubscriberFactory */
    protected static function newFactory()
    {
        return SubscriberFactory::new();
    }
}

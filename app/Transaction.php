<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'reference','amount','receiver_account', 'initiator_account'
    ];
}

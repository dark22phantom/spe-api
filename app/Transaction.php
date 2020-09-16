<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model{
    protected $table = 'transactions';
    
    protected $fillable = [
        'customer_id','transaction_amount','discount_bool','discount_rate','discount_amount','payment_amount','created_at','updated_at'
    ];

}
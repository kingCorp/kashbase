<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model 
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'payments';
    protected $fillable = [
        'amount',
        'reference',
        'via',
        'access_code',
        'authorization_url',
        'user_id',
        'transaction_date',
        'log',
        'bank',
        'status'
    ];

}

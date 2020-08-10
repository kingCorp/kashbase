<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class UserCards extends Model 
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'user_cards';
    
    protected $fillable = [
        'user_id',
        'card_number',
        'expiry_month',
        'authorization_code',
        'expiry_year',
        'card_type',
        'last4',
        'bin',
        'bank',
        'country_code',
    ];
    
}

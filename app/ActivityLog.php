<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class ActivityLog extends Model 
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'activities';

    protected $fillable = [
        'user_id',
        'action',
        'transaction_id'
    ];

    
}

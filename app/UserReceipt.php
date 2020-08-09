<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;


class UserReciept extends Model 
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'user_reciepts';
    
    protected $fillable = [
        'user_id',
        'bank',
        'account',
        'currency',
        'description',
        'reciept_code',
        'amount'
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
   
}

<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use App\UserBank;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'full_name', 'email', 'password', 'type', 'phone', 'sex', 'email_verified_at', 'avatar_url', 'authorization_code', 'id_user_card', 'wallet','api_token'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'authorization_code', 
        //'id_user_card', 
        //'wallet',
        'api_token'
    ];

    protected $with = [
        'bank'
    ];


    public function bank(){
        return $this->hasOne(UserBank::class, 'user_id');
    }

    public function reciept(){
        return $this->hasMany(UserReciept::class, 'user_id');
    }
}

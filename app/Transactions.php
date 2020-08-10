<?php
namespace App;
use Illuminate\Database\Eloquent\Model;

class Transactions extends Model {
   //
   protected $table = 'transactions';

   protected $fillable = ['user_id', 'reciepient_id', 'description','transaction_date', 'type', 'amount', 'status', 'prev_balance', 'current_balance'];
   

}

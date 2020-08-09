<?php

namespace App\Http\Controllers;

use App\User;
use Validator;
use App\Payment;
use App\UserCard;
use App\ActivityLog;
use App\Transactions;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\ApiController;

class TransactionController extends ApiController
{
    //
    public function transfer(Request $request)
    {
        try {
            //validate the post request
            $validator = Validator::make($request->all(), [
                'password' => 'required',
                'amount' => 'required|numeric',
                'reciepient_id' => 'required'
            ]);
            //if validator fails return json error response
            if ($validator->fails()) {
                $msg = "";
                foreach ($validator->errors()->toArray() as $error) {
                    foreach ($error as $errorMsg) {
                        $msg .= "" . $errorMsg . " ";
                    }
                }
                return $this->respondWithError(404, 'Email failed', $msg);
            }

            $user = Auth::user();
            $amount = $request->get('amount');

            if(!$user){
                return $this->respondWithError(404, 'User Login failed', 'User is not registered');
            }

            $reciepient = User::find($request->get('reciepient_id'));
            if(is_null($reciepient)){
                return $this->respondWithError(404, 'User Payment failed', 'User not found');
            }

            $transaction = Transactions::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'type' => 'CREDIT',
                'status' => 'PAID',
                'currency' => 'NGN',
                'description'=> 'WALLET TRANSFER',
                'reciepient_id' => $user->id,
                'prev_balance'   => $user->wallet,
                'current_balance' => ($user->wallet + $amount),
            ]);

            if(Hash::check($request->get('password'), $user->password)){
                $data = ['message'=> 'Transaction successfull'];
                return $this->respondWithoutError($data);
            } else {
                return $this->respondWithError(404, 'User Transaction failed', 'User Password incorrect');
            }


          
        } catch (\Exception $ex) {
            Log::error("TransactionController::transferOrganizer()  " . $ex->getMessage());
            return $this->respondWithError(404, 'Transaction failed failed', 'Something Went wrong');
        }
       
    }

    public function verifyUser(Request $request)
    {
        try {
             //validate the post request
             $validator = Validator::make($request->all(), [
                'email' => 'required|email',
            ]);
            //if validator fails return json error response
            if ($validator->fails()) {
                $msg = "";
                foreach ($validator->errors()->toArray() as $error) {
                    foreach ($error as $errorMsg) {
                        $msg .= "" . $errorMsg . " ";
                    }
                }
                return $this->respondWithError(404, 'Email failed', $msg);
            }

            $user = User::where('email', $request->get('email'))->first();

            if(!$user){
                return $this->respondWithError(404, 'User Verify failed', 'User is not found');
            }

            $data = ['message' => 'Verification successful', "email" => $user->email, "Full name"=> $user->full_name];
            return $this->respondWithoutError($data);

        } catch (\Exception $ex) {
            Log::error("TransactionController::rechagreOrganizer()  " . $ex->getMessage());
            return $this->respondWithError(404, 'Transaction failed failed', 'Something Went wrong');
        }
       
    }



    public function rechargeWallet(Request $request)
    {
        try {
            $user = Auth::user();

             //validate the post request
             $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric'
            ]);
            //if validator fails return json error response
            if ($validator->fails()) {
                $msg = "";
                foreach ($validator->errors()->toArray() as $error) {
                    foreach ($error as $errorMsg) {
                        $msg .= "" . $errorMsg . " ";
                    }
                }
                return $this->respondWithError(404, 'Payment failed', $msg);
            }

            if(is_null($user->id_user_card)){
                return $this->respondWithError(404, 'User Payment failed', 'User has no card');
            }

            $result = $this->chargeCard($user->authorization_code, $user->email, ($request->get('amount') * 100));

            if ($result->status) {
                $amount = $request->get('amount');
                $transaction = Transactions::create([
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'type' => 'CREDIT',
                    'status' => 'completed',
                    'currency' => 'NGN',
                    'description'=> 'RECHARGE WALLET ',
                    'reciepient_id' => $user->id,
                    'prev_balance'   => $user->wallet,
                    'current_balance' => ($user->wallet + $amount),
                ]);

                $user->wallet = intval($user->wallet) + $amount;
                $user->save();

                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'RECHARGE WALLET '.$user->full_name,
                    'transaction_id' => $transaction->id
                ]);

                $response = [
                    'message' => 'Recharge wallet successfull',
                    'transaction' => $transaction
                ];
                return $this->respondWithoutError($response);
            } else {
                return $this->respondWithError(400, 'Payment charge failed', $result->message);
            }

        } catch (\Exception $ex) {
            Log::error("TransactionController::rechagre()  " . $ex->getMessage());
            return $this->respondWithError(404, 'Transaction failed', 'Something Went wrong');
        }
       
    }

    //view total amount charged for delivery
    public function viewCards()
    {
        $user = Auth::user();
        try {
            $cards = UserCard::where('user_id', $user->id)
                        ->select('id', 'card_number', 'card_type', 'bank')
                        ->get();

            if (is_null($cards)) {
                return $this->respondWithError(401, 'Card not found', "User has no card");
            }

            $data = ["message" => "User cards", "cards" => $cards];

            return $this->respondWithoutError($data);
        } catch (\Exception  $ex) {
            Log::error("TransactionController::viewCard()  " . $ex->getMessage());
            return $this->respondWithError(500, 'Failed', "An error occurred.");
        }
    }


    //view total amount charged for delivery
    public function deleteCards(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $usercard = UserCard::where('id_user', $user->id)->where('id', $id)->first();
            if (is_null($usercard)) {
                return $this->respondWithError(401, 'Card not found', "User has no card");
            }
            $usercard->delete();

            if($user->id_user_card == $id ){
                $user->id_user_card = null;
                $user->authorization_code = null;
        
                $user->save();
            }

            $response = [
                'message' => 'Card deleted',
                'card' => $usercard
            ];
            return $this->respondWithoutError($response);
        } catch (\Exception  $ex) {
            Log::error("TransactionController::deleteCard()  " . $ex->getMessage());
            return $this->respondWithError(500, 'Failed', "An error occurred.");
        }
    }

    public function setCard(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $activeCard = UserCard::where('id', $id)->where('user_id', $user->id)->first();
            if (is_null($activeCard)) {
                return $this->respondWithError(404, 'Card operation failed', 'Card doesnt exist');
            }
            $user->authorization_code = $activeCard->authorization_code;
            $user->id_user_card = $activeCard->id;
            //$user->user_card = $activeCard->card_number;
            $user->save();
            $response = [
                'message' => 'Card selected',
                'cardCode' => $user->authorization_code,
                'cardId' => $user->id_user_card
            ];
            return $this->respondWithoutError($response);
        } catch (\Exception  $ex) {
            Log::error("PayController::viewAmount()  " . $ex->getMessage());
            return $this->respondWithError(500, 'Failed', "An error occurred.");
        }
    }

    function initializeTransaction(Request $request)
    {
        try {
          
            $user = Auth::user();
            $amount= 10000;
           $content = $this->initializeTrans($user->email, $amount);

            Payment::create([
                'amount' => $amount,
                'reference' => $content->data->reference,
                'via' => 0,
                'access_code' => $content->data->access_code,
                'authorization_url' => $content->data->authorization_url,
                'user_id' => Auth::user()->id
            ]);

            $response = [
                'amount' => ($amount / 100),
                'reference' => $content->data->reference,
                'message' => 'Payment initialization successful',
                'access_code' => $content->data->access_code,
                'authorization_url' => $content->data->authorization_url
            ];
            return $this->respondWithoutError($response);
        } catch (\Exception $ex) {
            Log::error("PaymentController::initializeTransaction() " . $ex->getMessage());
            return $this->respondWithError(401, 'Unable to initialize transaction', "An error occurred.");
        }
    }

    //not tested yet
    function handleRedirection(Request $request)
    {
        try {
             //validate the post request
             $validator = Validator::make($request->all(), [
                'trxref' => 'required',
            ]);
            //if validator fails return json error response
            if ($validator->fails()) {
                $msg = "";
                foreach ($validator->errors()->toArray() as $error) {
                    foreach ($error as $errorMsg) {
                        $msg .= "" . $errorMsg . " ";
                    }
                }
                return $this->respondWithError(404, 'Payment handle failed', $msg);
            }

            $content = $this->handleRedirect($request->get('trxref'));

            if (!$content->status) {
                return $this->respondWithError(400, 'Transaction verification failed', $content->message);
            }

            $payment = Payment::where('reference', '=', $content->data->reference)->first();
            if (empty($payment) || is_null($payment)) {
                //implies there's a big problem,
                //Fatal error somewhere
                $error = "Fatal Error! Payment transaction reference does not exist. Kindly contact Administrator to resolve.";
                return $this->respondWithError(400, $error, $content->message);
            }

            if ($payment->status == 1) {
                //check if card is reusable

                //payment is already verified
                return $this->respondWithoutError("Transaction successful, payment already verified");
            }
            //dd($content->data);

            if ($content->data->status == 'success') {
                //implies successfully paid without issues
                $payment->amount = $content->data->amount; //its in kobo
                $payment->transaction_date = $content->data->transaction_date;
                $payment->reference = $content->data->reference;
                $payment->domain = $content->data->domain;
                $payment->bank = $content->data->authorization->bank;
                $payment->status = 1; //successful
                $payment->log = json_encode($content->data);
                $payment->save();

                //create a user card details
                $user = Auth::user();
                $userCard = UserCard::create([
                    'user_id' => $user->id,
                    'card_number' => $content->data->authorization->last4,
                    'expiry_month'  => $content->data->authorization->exp_month,
                    'authorization_code'  => $content->data->authorization->authorization_code,
                    'expiry_year' => $content->data->authorization->exp_year,
                    'card_type' => $content->data->authorization->card_type,
                    'last4' => $content->data->authorization->last4,
                    'bin' => $content->data->authorization->bin,
                    'bank' => $content->data->authorization->bank,
                    'country_code'  => $content->data->authorization->country_code,
                ]);

                //set it has default card to deposit from
                //update user
                //$user->user_card = $userCard->card_number;
                $user->id_user_card = $userCard->id;
                $user->authorization_code = $userCard->authorization_code;
                $user->save();

                return $this->respondWithoutError(["message" => "Transaction successful", "user" => $user, "card_details" => $userCard]);
            } else {
                return $this->respondWithError(401, 'Unable to verify payment', $content->data->gateway_response);
            }
        } catch (\Exception $ex) {
            Log::error("PaymentController::handleRedirection() " . $ex->getMessage());
            return $this->respondWithError(401, 'Unable to handle redirection', "An error occurred.");
        } 
    }

    //view transaction history
    public function myTransaction(Request $request)
    {
        try {
            $user = Auth::user();
            
            $transactions = Transactions::where('user_id', $user->id)->orderBy('id', 'DESC')->get();

            $response = [
                'message' => 'Transaction history',
               'transactions' => $transactions
            ];
            return $this->respondWithoutError($response);
        } catch (\Exception  $ex) {
            Log::error("PaymentController::myTransaction()  " . $ex->getMessage());
            return $this->respondWithError(500, 'Failed', "An error occurred.");
        }
    }


}

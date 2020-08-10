<?php

namespace App\Http\Controllers;

use App\User;
use Validator;
use App\UserBank;
use App\UserReciept;
use App\Transactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\ApiController;

class UserController extends ApiController
{
    //
    public function register(Request $request)
    {
        try {
            //validate the post request
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8',
                'phone' => 'required',
                'full_name' => 'required',
            ]);
            //if validator fails return json error response
            if ($validator->fails()) {
                $msg = "";
                foreach ($validator->errors()->toArray() as $error) {
                    foreach ($error as $errorMsg) {
                        $msg .= "" . $errorMsg . " ";
                    }
                }
                return $this->respondWithError(404, 'Registration failed', $msg);
            }

            //validation successful. Register user
            $user = User::create([
                'full_name' => $request->get('full_name'),
                'email' => $request->get('email'),
                'password' => Hash::make($request->get('password')),
                'phone' => $request->get('phone'),
                'type' => 'user',
                'api_token' => base64_encode($request->get('email') . $request->get('password'))
            ]);

            $data = ['message' => 'Registration successful', 'token' => $user->api_token, "user" => $user];
            return $this->respondWithoutError($data);
        } catch (\Exception $ex) {
            Log::error("UserController::registerOrganizer()  " . $ex->getMessage());
            return $this->respondWithError(404, 'User Registration failed', 'Something Went wrong');
        }
    }



    public function authenticate(Request $request)
    {
        try {
            //validate the post request
            $validator = Validator::make($request->all(), [
                'email' => 'required',
                'password' => 'required|min:8',
            ]);
            //if validator fails return json error response
            if ($validator->fails()) {
                $msg = "";
                foreach ($validator->errors()->toArray() as $error) {
                    foreach ($error as $errorMsg) {
                        $msg .= "" . $errorMsg . " ";
                    }
                }
                return $this->respondWithError(404, 'Login failed', $msg);
            }

            $user = User::where('email', $request->get('email'))->first();

            if (!$user) {
                return $this->respondWithError(404, 'User Login failed', 'User is not registered');
            }

            if (Hash::check($request->get('password'), $user->password)) {
                $token = base64_encode($request->get('email') . $request->get('password'));

                User::where('email', $request->get('email'))->update(['api_token' => $token]);

                $data = ['message' => 'Authentication successful', 'token' => $token, "user" => $user];
                return $this->respondWithoutError($data);
            } else {
                return $this->respondWithError(404, 'User Login failed', 'Invalid Email or Password');
            }
        } catch (\Exception $ex) {
            Log::error("UserController::authenticate()  " . $ex->getMessage());
            return $this->respondWithError(404, 'User Registration failed', 'Something Went wrong');
        }
    }

    public function profile(Request $request)
    {
        try {
            $user = Auth::user();
            $data = ['message' => 'Profile successful', "user" => $user];
            return $this->respondWithoutError($data);
        } catch (\Exception $ex) {
            Log::error("UserController::profile()  " . $ex->getMessage());
            return $this->respondWithError(404, 'User Profile failed', 'Something Went wrong');
        }
    }

    public function verifyUserAccount(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'account' => 'required|numeric',
                'bank' => 'required',
            ]);
            //if validator fails return json error response
            if ($validator->fails()) {
                $msg = "";
                foreach ($validator->errors()->toArray() as $error) {
                    foreach ($error as $errorMsg) {
                        $msg .= "" . $errorMsg . " ";
                    }
                }
                return $this->respondWithError(404, 'User Account failed', $msg);
            }

            $result = $this->verifyAccount($request->get('account'), $request->get('bank'));
            return $this->respondWithoutError($result);
        } catch (\Exception $ex) {
            Log::error("UserController::profile()  " . $ex->getMessage());
            return $this->respondWithError(404, 'User Profile failed', 'Something Went wrong');
        }
    }

    public function setBankAccount(Request $request)
    {
        try {
            //validate the post request
            $validator = Validator::make($request->all(), [
                'bank' => 'required',
                'account' => 'required|min:8',
            ]);
            //if validator fails return json error response
            if ($validator->fails()) {
                $msg = "";
                foreach ($validator->errors()->toArray() as $error) {
                    foreach ($error as $errorMsg) {
                        $msg .= "" . $errorMsg . " ";
                    }
                }
                return $this->respondWithError(404, 'Login failed', $msg);
            }

            $user = Auth::user();
            $bank = $request->get('bank');
            $account = $request->get('account');


            $user->bank()->updateOrCreate(['user_id' => $user->id], ['bank' => $bank, 'account' => $account]);

            $data = ['message' => 'Bank update successful', "user" => $user];
            return $this->respondWithoutError($data);
        } catch (\Exception $ex) {
            Log::error("UserController::profile()  " . $ex->getMessage());
            return $this->respondWithError(404, 'User Profile failed', 'Something Went wrong');
        }
    }

    public function transferAccount(Request $request)
    {
        try {

            //validate the post request
            $validator = Validator::make($request->all(), [
                'amount' => 'required',
            ]);
            //if validator fails return json error response
            if ($validator->fails()) {
                $msg = "";
                foreach ($validator->errors()->toArray() as $error) {
                    foreach ($error as $errorMsg) {
                        $msg .= "" . $errorMsg . " ";
                    }
                }
                return $this->respondWithError(404, 'Transfer failed', $msg);
            }

            $amount = $request->get('amount');

            $user = Auth::user();

            if ($user->wallet < $amount) {
                return $this->respondWithError(404, 'User Transfer failed', 'Insufficient funds');
            }

            if (!$user->bank) {
                return $this->respondWithError(401, 'Unable to continue transfer', "User has no Active Bank Account");
            }
            
            $content = $this->generateReciept($user->bank->account, $user->bank->bank);

            if ($content->status) {

                $user->reciept()->create([
                    'name' => 'cashout',
                    'reciept_code' => 'REF'.$user->id.'-'.time(),
                    'amount' => $amount,
                    'account' => $user->bank->account,
                    'bank' => $user->bank->bank,
                    'currency' => 'NGN',
                    'description' => 'transfer reciept'
                ]);

                $result = $this->makeTransfer($content->data->recipient_code, ($amount * 100));

                if ($result->status) {

                    $transaction = Transactions::create([
                        'user_id' => $user->id,
                        'amount' => $amount,
                        'type' => 'debit',
                        'status' => 'completed',
                        'currency' => 'NGN',
                        'description' => 'WALLET CASHOUT',
                        'log' => $result->data,
                        'reciepient_id' => $user->id,
                        'prev_balance'   => $user->wallet,
                        'current_balance' => ($user->wallet - $amount),
                    ]);

                    $user->wallet = ($user->wallet - $amount);
                    $user->save();

                    $data = ['message' => 'Transaction successful', "transaction" => $transaction];
                    return $this->respondWithoutError($data);
                } else {
                    return $this->respondWithError(401, 'Unable to verify payment', $result->data->gateway_response);
                }
            } else {
                return $this->respondWithError(401, 'Unable to verify payment', $content->data->gateway_response);
            }
        } catch (\Exception $ex) {
            Log::error("UserController::profile()  " . $ex->getMessage());
            return $this->respondWithError(404, 'User Profile failed', 'Something Went wrong');
        }
    }
}

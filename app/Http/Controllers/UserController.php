<?php

namespace App\Http\Controllers;

use App\User;
use Validator;
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
                'api_token' => base64_encode($request->get('email').$request->get('password'))
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

            if(!$user){
                return $this->respondWithError(404, 'User Login failed', 'User is not registered');
            }

            if(Hash::check($request->get('password'), $user->password)){
                $token = base64_encode($request->get('email').$request->get('password'));

                User::where('email', $request->get('email'))->update(['api_token' => $token]);

            $data = ['message' => 'Authentication successful', 'token' => $token, "user" => $user];
            return $this->respondWithoutError($data);
            }else{
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
           
            $user =Auth::user();

            if(!$user){
                return $this->respondWithError(404, 'User Login failed', 'User is not registered');
            }

            $data = ['message' => 'Profile successful', "user" => $user];
            return $this->respondWithoutError($data);

        } catch (\Exception $ex) {
            Log::error("UserController::profile()  " . $ex->getMessage());
            return $this->respondWithError(404, 'User Profile failed', 'Something Went wrong');
        }
       
    }

}

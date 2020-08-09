<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use App\Http\Controllers\Controller;

class ApiController extends Controller
{
    //
    protected $statusCode = 200;

    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    protected function respondWithoutError($data)
    {

        $response = [
            'hasError' => false,
            'data' => $data,
        ];

        return response()->json($response);
    }

    protected function respondWithError($errorCode, $title, $errorMessage)
    {
        return response()->json([
            'hasError' => true,
            'errors' => [
                'code' => $errorCode,
                'title' => $title,
                'message' => $errorMessage
            ]
        ]);
    }

    public  function initializeTrans($email, $amount)
    {
        $client = new Client([
            'curl' => [CURLOPT_SSL_VERIFYPEER => env('VERIFY_SSL')]
        ]); 
        
        $paymentData = ['amount' => $amount, 'email' => $email, 'reference' => 'CARDAUTH' . time()];
        $result = $client->request('post', 'https://api.paystack.co/transaction/initialize', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('PAY_STACK_TEST_SECRET_KEY')
            ],
            'form_params' => $paymentData
        ]);
        return json_decode($result->getBody());
    }

    function handleRedirect($reference)
    {
        $client = new Client([
            'curl' => [CURLOPT_SSL_VERIFYPEER => env('VERIFY_SSL')]
        ]);

        $url = 'https://api.paystack.co/transaction/verify/' . $reference;
        $result = $client->request('get', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . env('PAY_STACK_TEST_SECRET_KEY')
            ]
        ]);

        return json_decode($result->getBody());
    }

    public function chargeCard($authRef, $email, $amount)
    {
        $client = new Client([
            'curl' => [CURLOPT_SSL_VERIFYPEER => env('VERIFY_SSL')]
        ]);

        $paymentData = ['amount' => $amount, 'email' => $email, 'authorization_code' => $authRef];
        $result = $client->request('post', 'https://api.paystack.co/transaction/charge_authorization', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('PAY_STACK_TEST_SECRET_KEY')
            ],
            'form_params' => $paymentData
        ]);
        return json_decode($result->getBody());
    }

    public function makeTransfer($recipient, $amount)
    {
        $client = new Client([
            'curl' => [CURLOPT_SSL_VERIFYPEER => env('VERIFY_SSL')]
        ]);

        $paymentData = [
            'source' => 'balance',
            'recipient' => $recipient,
            'amount' => $amount,
            'currency' => 'NGN',

        ];
        $result = $client->request('post', 'https://api.paystack.co/transfer', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('PAY_STACK_TEST_SECRET_KEY')
            ],
            'form_params' => $paymentData
        ]);
        return json_decode($result->getBody());
    }


    public function checkApi(){
        return  response()->json(['status' => 'success','result' => 'new lumen hello this is Api']);
    }
}

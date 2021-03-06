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

    public function generateReciept($account_no, $bank_code)
    {
        $client = new Client([
            'curl' => [CURLOPT_SSL_VERIFYPEER => env('VERIFY_SSL')]
        ]);

        $codes = $this->getBankCodes();
        
        $code = $codes[$bank_code];

        $paymentData = [
            'type' => 'nuban',
            'name' => 'cashout',
            'account_number' => $account_no,
            'bank_code' => $code,
            'currency' => 'NGN',
            'description' => 'transfer reciept'
        ];
        $result = $client->request('post', 'https://api.paystack.co/transferrecipient', [
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
            'reason' => 'cashout'
        ];
        $result = $client->request('post', 'https://api.paystack.co/transfer', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('PAY_STACK_TEST_SECRET_KEY')
            ],
            'form_params' => $paymentData
        ]);
        return json_decode($result->getBody());
    }

    function verifyAccount($account, $bank)
    {
        $codes = $this->getBankCodes();
        
        $code = $codes[$bank];
       
        $client = new Client([
            'curl' => [CURLOPT_SSL_VERIFYPEER => env('VERIFY_SSL')]
        ]);

        $result = $client->request('get', 'https://api.paystack.co/bank/resolve?account_number=' . $account . '&bank_code=' . $code . '', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('PAY_STACK_TEST_SECRET_KEY')
            ]
        ]);

        return json_decode($result->getBody());
    }

    public function getBankCodes()
    {
        return [
            "Access Bank (Diamond)" => "063",
            "Access Bank" => "044",
            "ALAT by WEMA" => "035A",
            "ASO Savings and Loans" => "401",
            "Citibank Nigeria" => "023",
            "Ecobank Nigeria" => "050",
            "Ekondo Microfinance Bank" => "562",
            "Fidelity Bank" => "070",
            "First Bank of Nigeria" => "011",
            "First City Monument Bank" => "214",
            "Guaranty Trust Bank" => "058",
            "Heritage Bank" => "030",
            "Jaiz Bank" => "301",
            "Keystone Bank" => "082",
            "Kuda Bank" => "50211",
            "Parallex Bank" => "526",
            "Polaris Bank" => "076",
            "Providus Bank" => "101",
            "Stanbic IBTC Bank" => "221",
            "Standard Chartered Bank" => "068",
            "Sterling Bank" => "232",
            "Suntrust Bank" => "100",
            "Union Bank of Nigeria" => "032",
            "United Bank For Africa" => "033",
            "Unity Bank" => "215",
            "Wema Bank" => "035",
            "Zenith Bank" => "057",
        ];
    }

    public function checkApi()
    {
        return  response()->json(['status' => 'success', 'result' => 'new lumen hello this is Api']);
    }
}

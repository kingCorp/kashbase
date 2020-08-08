<?php

namespace App\Http\Controllers;

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

    
    public function checkApi(){
        return  response()->json(['status' => 'success','result' => 'new lumen hello this is Api']);
    }
}

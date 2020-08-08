<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ApiController;

class UserController extends ApiController
{
    //
    public function check(){
        return $this->checkApi();
    }
}

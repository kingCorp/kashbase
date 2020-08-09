<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix'=>'api/v1'], function() use($router){

    $router->post('login/','UserController@authenticate');
    $router->post('register/','UserController@register');

    $router->group(['middleware' => 'auth'], function () use ($router) {

        $router->group(['prefix'=>'user'], function() use($router){
            $router->get('profile/','UserController@profile');

            $router->post('transfer/','TransactionController@transfer');
            $router->post('verify/','TransactionController@verifyUser');
            $router->post('recharge/','TransactionController@rechargeWallet');
            $router->get('card/','TransactionController@viewCards');
            $router->post('card/{id}','TransactionController@setCard');
            $router->delete('card/{id}','TransactionController@deleteCards');
            $router->post('initialize/','TransactionController@initializeTransaction');
            $router->post('handle/','TransactionController@handleRedirection');
            $router->get('transactions/','TransactionController@myTransaction');
        });
 
    });

    
});




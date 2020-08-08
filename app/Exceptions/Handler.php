<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {

        if($exception instanceof MethodNotAllowedHttpException){
            return response()->json([
                'hasError' => true,
                'errors' => [
                    'code' => $exception->getStatusCode(),
                    'title' => "Invalid endpoint",
                    'message' => "Confirm the endpoint you are trying to access exists"
                ]
            ]);
        }

        if ($exception instanceof AuthenticationException) {
            return response()->json([
                'hasError' => true,
                'errors' => [
                    'code' => 666,
                    'title' => "Invalid Token",
                    'message' => "Session Expired. Kindly, Log out and Sign in."
                ]
            ]);
        }

        //we dont know whats going on
        Log::error($exception->getMessage()."Handler@render() :  ".$exception->getTraceAsString());

        return response()->json([
            'hasError' => true,
            'errors' => [
                'code' => 401,
                'title' => "An error occurred",
                'message' => $exception->getMessage()
            ]
        ]);

        //return parent::render($request, $exception);
    }
}

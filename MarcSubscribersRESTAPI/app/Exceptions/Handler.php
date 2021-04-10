<?php
/*
 * Copyright (c) 2021.
 * Marc Concepcion
 * marcanthonyconcepcion@gmail.com
 */

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        });
        $this->renderable(function (HttpException $e) {
            if ($e->getStatusCode() == 404)
            {
                return response()->json(['error' => 'The records or resources that you requested are not available.'],
                    404);
            }
            return response()->json(['error' => $e->getMessage()], $e->getStatusCode());
        });
        $this->renderable(function (Throwable $e) {
            return response()->json(
                ['error' => 'Error caused by server or client. Please provide acceptable API Command'],500);
        });
    }
}

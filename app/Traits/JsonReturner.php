<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Response;

trait JsonReturner
{
    protected function successResponse($data, $message = null, $code = 200)
    {
        if (auth()->check()) {
            $cookie = Cookie::make('userId', auth()->id(), 60 * 24 * 30);
            $response = Response::json([
                'status' => 'success',
                'message' => $message,
                'data' => $data,
            ], $code);
            $response->headers->setCookie($cookie);
            return $response;
        } else {
            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => $data,
            ], $code);
        }
    }

    protected function errorResponse($message = null, $code = 200)
    {
        if (auth()->check()) {
            $cookie = Cookie::make('userId', auth()->id(), 60 * 24 * 30);
            $response = Response::json([
                'status' => 'error',
                'message' => $message,
            ], $code);
            $response->headers->setCookie($cookie);
            return $response;
        } else {
            return response()->json([
                'status' => 'error',
                'message' => $message,
            ], $code);
        }
    }

    protected function unauthorizedResponse($message = null, $code = 200)
    {
        if (auth()->check()) {
            $cookie = Cookie::make('userId', auth()->id(), 60 * 24 * 30);
            $response = Response::json([
                'status' => 'unauthorized',
                'message' => $message,
            ], $code);
            $response->headers->setCookie($cookie);
            return $response;
        } else {
            return response()->json([
                'status' => 'unauthorized',
                'message' => $message,
            ], $code);
        }
    }

    protected function validationResponse($message = null, $code = 200)
    {
        if (auth()->check()) {
            $cookie = Cookie::make('userId', auth()->id(), 60 * 24 * 30);
            $response = Response::json([
                'status' => 'error validation',
                'message' => $message,
            ], $code);
            $response->headers->setCookie($cookie);
            return $response;
        } else {
            return response()->json([
                'status' => 'error validation',
                'message' => $message,
            ], $code);
        }
    }
}

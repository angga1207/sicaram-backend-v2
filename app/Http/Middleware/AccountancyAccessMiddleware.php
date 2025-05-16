<?php

namespace App\Http\Middleware;

use App\Traits\JsonReturner;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AccountancyAccessMiddleware
{
    use JsonReturner;

    public function handle(Request $request, Closure $next): Response
    {
        if (in_array(auth()->user()->role_id, [1, 2, 4, 7, 12])) {
            return $next($request);
        } else {
            // return abort(401);
            // return redirect()->route('dashboard');

            return $this->unauthorizedResponse('Anda tidak memiliki akses ke halaman ini');
        }
    }
}

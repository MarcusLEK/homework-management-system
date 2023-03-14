<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Api\ApiController;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class ApiAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiController = new ApiController();

        if (empty($request->server('PHP_AUTH_USER')) && empty($request->server('PHP_AUTH_PW'))) {
            return $apiController->respondMethodNotAllowed();
        }

        $user = User::where('username', $request->server('PHP_AUTH_USER'))->first();
        if (!$user) {
            return $apiController->respondNotFound('User not found');
        }
        if (!Hash::check($request->server('PHP_AUTH_PW'), $user->password)) {
            return $apiController->respondMethodNotAllowed('Username and password does not match');
        }


        return $next($request);
    }
}

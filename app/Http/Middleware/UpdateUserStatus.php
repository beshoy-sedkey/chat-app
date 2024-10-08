<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Events\UserStatusChanged;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UpdateUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user->is_online = true;
            $user->save();
            broadcast(new UserStatusChanged($user->id, true));
        }
        return $next($request);
    }

    // public function terminate($request, $response)
    // {
    //     if (Auth::check()) {
    //         $user = Auth::user();
    //         $user->is_online = false;
    //         $user->last_seen = now();
    //         $user->save();

    //         broadcast(new UserStatusChanged($user->id, false));
    //     }
    // }
}

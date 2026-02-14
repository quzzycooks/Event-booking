<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OnlyAttendee
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('api')->user();

        if (! $user || $user->role !== 'attendee') {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this resource',
            ], 403);
        }

        return $next($request);
    }
}

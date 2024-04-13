<?php

namespace App\Http\Middleware;

use App\Models\Admins;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userId = $request->userId;
        $admin = Admins::where('userId', '=', $userId);

        if($admin->get()->count() < 1){
            return response()->json(
                [
                    'status' => 0,
                    'message' => "Нет доступа"
                ]
            );
        }

        return $next($request);
    }
}

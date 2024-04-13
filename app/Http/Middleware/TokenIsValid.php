<?php

namespace App\Http\Middleware;

use App\Models\Tokens;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $auth = $request->header('Authorization');

        $auth = str_replace('Bearer ', '', $auth);
        if(is_null($auth)){
            return response()->json(
                [
                    'status'=>'0',
                    'message'=>'Авторизуйтесь'
                ],
                401
            );
        }

        $token = Tokens::where('value', '=', $auth)->get();
        if($token->count() < 1){
            return response()->json(
                [
                    'status' => 0,
                    'message' => 'Нет доступа'
                ],
                404
            );
        }
        $user = User::find($token[0]->userId);

        if(is_null($user)){
            return response()->json(
                [
                    'status' => 0,
                    'message' => 'Авторизуйтесь'
                ],
                404
            );
        }
        $request['userId'] = $user->id;
        return $next($request);
    }
}

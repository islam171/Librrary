<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tokens;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function register(Request $request){
        $name = $request->name;
        $email = $request->name;
        $password = $request->password;

        foreach (User::all() as $item){
            if($item -> name == $name){
                return response("Такой пользователь уже существует");
            }
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        Auth::login($user);
        return $user;
    }

    public function login(Request $request){
        $name = $request->name;
        $password = $request->password;

        $user = null;
        foreach (User::all() as $item){
            if($item -> name == $name){
                $user = $item;
            }
        }

        if($user || Hash::check($user->password, $password)){

            $oldToken = Tokens::where('userId', '=', $user->id);
            if(!is_null($oldToken->get())){
                $oldToken->delete();
            }


            $today = date("Y-m-d", strtotime("+7 days"));
            $today = $today . " " . '00:00:00';

            $token = $user->createToken($name)->plainTextToken;

            $doc = DB::table('Tokens')->insert([
                'value' => $token,
                'userId' => $user->id,
                'validUntil' => $today
            ]);

            return [
                'token' => $token,
                'user' => $doc
            ];
        }else{
            return "Неверный логин или пароль";
        }



    }
}

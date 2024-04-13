<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tokens;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request){
        $name = $request->name;
        $password = $request->password;

        foreach (Users::all() as $item){
            if($item -> name == $name){
                return response("Такой пользователь уже существует");
            }
        }

        $password = Hash::make($password);

        $user = Users::create([
            'name' => $name,
            'password' => $password,
        ]);

        return $user;
    }

    public function login(Request $request){
        $name = $request->name;
        $password = $request->password;

        $user = null;
        foreach (Users::all() as $item){
            if($item -> name == $name){
                $user = $item;
            }
        }

        return Hash::check($user->password, $password);

        if($user && Hash::check($user->password, $password)){

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

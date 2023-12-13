<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlockingsResource;
use App\Models\Admins;
use App\Models\Blockings;
use App\Models\BlockUser;
use App\Models\Tokens;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function getAdmin(Request $request){

        $auth = $request->headers;
        return $auth['Authorization'];

        $token= Tokens::where('value', '=', $auth)->get();

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

        $admin = Admins::where('userId', '=', $user->id);

        if($admin->get()->count() > 0){
            return response()->json(
                [
                    'status' => 1,
                    'message' => true
                ]
            );
        }else{
            return response()->json(
                [
                    'status' => 0,
                    'message' => false
                ]
            );
        }

    }


    public function blockUser(Request $request){

        // Проверка на авторизацию открыта))

        $id = $request->id;

        $auth = $request->header('Authorization');
        $auth = str_replace('Bearer ', "", $auth);

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
        // Проверка на авторизацию закрыто))

        // Проверка на админа
        $admin = Admins::where('userId', '=', $user->id);

        if(is_null($admin->get())){
            return response()->json(
                [
                    'status' => 0,
                    'message' => "Нет прав"
                ], 400
            );
        }


        $userBlock =  Blockings::where('userId', '=', $id)->get();
        if($userBlock->count() > 0){
            return response()->json(
                [
                    'status' => 0,
                    'message' => "Пользовотель заблокирован"
                ], 403
            );
        }


        $doc = Blockings::create(['userId' => $id]);
        return new BlockingsResource($doc);

    }
    public function unlockUser(Request $request){

        $id = $request->id;

        $auth = $request->header('Authorization');
        $auth = str_replace('Bearer ', "", $auth);

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
        // Проверка на авторизацию закрыто))

        // Проверка на админа
        $admin = Admins::where('userId', '=', $user->id);

        if(is_null($admin->get())){
            return response()->json(
                [
                    'status' => 0,
                    'message' => "Нет прав"
                ], 400
            );
        }


        $userBlock =  Blockings::where('userId', '=', $id);
        if($userBlock->get()->count() < 1){
            return response()->json(
                [
                    'status' => 0,
                    'message' => "Пользовотель не был заблокирован"
                ], 403
            );
        }

        $res = $userBlock->delete();
        if ($res){
            return response()->json(
                [
                    'status' => 1,
                    'message' => "Пользовотель был разблокирован"
                ]
            );
        }else{
            return response()->json(
                [
                    'status' => 0,
                    'message' => "Не удалось раблокировать пользователя"
                ], 500
            );
        }
    }



    public function getUsers(){
        $auth = getallheaders()['Authorization'];
        $token= Tokens::where('value', '=', $auth)->get();

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
        // Проверка на авторизацию закрыто))

        // Проверка на админа
        $admin = Admins::where('userId', '=', $user->id);

        if(is_null($admin->get())){
            return response()->json(
                [
                    'status' => 0,
                    'message' => "Нет прав"
                ], 400
            );
        }

        $res = [];
        $users = User::all();
        foreach ($users as $item){
            if($item->id == $user->id){
                continue;
            }
            $bloking = Blockings::where('userId', '=', $item->id)->get();
            if($bloking->count() > 0){
                $res[] = [
                    'user' => $item,
                    'block' => true
                ];
            }else{
                $res[] = [
                    'user' => $item,
                    'block' => false
                ];
            }

        }
        return $res;
    }


}

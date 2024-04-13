<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlockingsResource;
use App\Models\Admins;
use App\Models\Blockings;
use App\Models\BlockUser;
use App\Models\Tokens;
use App\Models\User;
use App\Models\userbooks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function getAdmin(Request $request){

        return response()->json(
            [
                'status' => 1,
                'message' => true
            ]
        );

    }

    public function blockUser(Request $request){

        // Проверка на авторизацию открыта))

        $id = $request->id;

        $userBlock =  Blockings::where('userId', '=', $id)->get();
        if($userBlock->count() > 0){
            return response()->json(
                [
                    'status' => 0,
                    'message' => "Пользовотель уже заблокирован"
                ], 403
            );
        }


        $doc = Blockings::create(['userId' => $id]);
        if($doc->get()->count() > 0){
            return response()->json(
                [
                    'status' => 1,
                    'message' => "Пользовотель заблокирован"
                ]
            );
        }

    }
    public function unlockUser(Request $request){

        $id = $request->id;

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

    public function getUsers(Request $request){

        $userId = $request->userId;
        $admin = Admins::where('userId', '=', $userId);

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
            if($item->id == $userId){
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

    public function updateUserBook(Request $request, string $id)
    {
        $userId = $request->userId;
        $bookId = $request->bookId;

        $taking = userbooks::find($id);
        if(is_null($taking)){
            return response()->json(
                [
                    'status' => 0,
                    'message' => 'Не найдено'
                ],
                404
            );
        }

        DB::beginTransaction();
        try{
            $taking->userId = $userId;
            $taking->bookId = $bookId;
            return $taking;
            $taking->save();
            DB::commit();
        }catch (\Exception $error){
            DB::rollBack();
            $taking = null;
        }

        if(is_null($taking)){
            return response()->json(
                [
                    'status' => 0,
                    'message' => 'Server error'
                ],
                500
            );
        }else{
            return response()->json(
                [
                    'status' => 0,
                    'message' => 'Изменение прошло успешно'
                ],
                200
            );
        }

    }


}

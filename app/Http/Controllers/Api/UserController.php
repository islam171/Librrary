<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BooksResource;
use App\Http\Resources\TakingResource;
use App\Models\Books;
use App\Models\Taking;
use App\Models\Tokens;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

    //Вывод всех добавленных книг пользовотелям
    public function getBooksUser(Request $request){
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

        $takings = Taking::where('userId', '=', $token[0]->userId);
        if($takings->get()->count() < 1){
            return response()->json(
                [
                    'data' => []
                ]
            );
        }

        $books = [];
        $i = 0;


        while($takings->get()->count() > $i){
            $book = Books::where('id', '=', $takings->get()[$i]->bookId)->get();
            if($book->count()>0){
                $books[] = $book[0];
            }
            $i = $i + 1;
        }


        return BooksResource::collection($books);

    }

    public function destroy(Request $request, string $id)
    {
        $auth = getallheaders()['Authorization'];
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
                    'status'=>'0',
                    'message'=>'Время истекло'
                ],
                404
            );
        }
        $user = User::find($token[0]->userId);
        if(is_null($user)){
            return response()->json(
                [
                    'status'=>'0',
                    'message'=>'Авторизуйтесь'
                ],
                403
            );
        }

        $taking = Taking::where('bookId', '=', $id)->get();
        if(is_null($taking[0])){
            return response()->json(
                [
                    'status' => 0,
                    'message' => 'Не найдено'
                ],
                404
            );
        }

        //Проверка пользовотеля
        if($taking[0]->userId != $user->id){
            return response()->json(
                [
                    'status' => 0,
                    'message' => 'Нет доступа'
                ],
                404
            );
        }

        DB::beginTransaction();
        try{
            $taking[0]->delete();
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
                    'status' => 1,
                    'message' => 'Успешно удален'
                ],
                200
            );
        }
    }
}

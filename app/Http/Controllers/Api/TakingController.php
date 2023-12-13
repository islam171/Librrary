<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlockingsResource;
use App\Http\Resources\TakingResource;
use App\Models\Blockings;
use App\Models\Books;
use App\Models\Taking;
use App\Models\Tokens;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class TakingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    //Admin
    public function index(Request $request)
    {
        $auth = getallheaders()['Authorization'];
        $token= Tokens::where('value', '=', $auth)->get();


        //Проверка на авторизованность
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
                    'message' => 'Нет доступа'
                ],
                404
            );
        }
        //Проверка на админа
        if($user->name != 'user1'){
            return response()->json(
                [
                    'status' => 0,
                    'message' => 'Нет доступа'
                ],
                404
            );
        }

        return Taking::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    //User
    public function store(Request $request)
    {
        $auth = getallheaders()['Authorization'];
        $bookId = $request->bookId;
        $token= Tokens::where('value', '=', $auth)->get();

        if($token->count() < 1){
            return response()->json(
                [
                    'status' => 0,
                    'message' => 'Нет доступа'
                ],
                401
            );
        }

        $user = User::find($token[0]->userId);
        if(is_null($user)){
            return response()->json(
                [
                    'status' => 0,
                    'message' => 'Нет доступа'
                ],
                401
            );
        }

        $blocks = Blockings::where('userId', '=', $user->id)->get();
        if($blocks->count() > 0){
            return response()->json(
                [
                    'status' => 3,
                    'message' => 'Пользователь заблокирован'
                ],
                401
            );
        }


        $book = Books::find($bookId);
        if(is_null($book)){
            return response()->json(
                [
                    'status' => 0,
                    'message' => 'Книга не найдена'
                ],
                404
            );
        }

        if(is_null($user)){
            return response()->json(
                [
                    'status' => 0,
                    'message' => 'Нет доступа'
                ],
                404
            );
        }

        $today = date('Y-m-d');
        $today = $today . " 00:00:00";
        $returnDate = date("Y-m-d", strtotime("+7 days"));
        $returnDate = $returnDate . ' 00:00:00';

        $oldTaking = Taking::where('bookId', '=', $bookId)->get();

        if($oldTaking->count() > 0){
            return response()->json(
                [
                    'status' => 0,
                    'message' => 'Книга уже добавлено'
                ],
                403
            );
        }

        DB::table('Takings')->insert([
            'userId' => $user->id,
            'bookId'=> $bookId,
            'issueDate' => $today,
            'returnDate' => $returnDate
        ]);
        $takings = Taking::where('bookId', '=', $bookId);
        return TakingResource::collection($takings->get())[0];
    }

    /**
     * Display the specified resource.
     */
    //User
    public function show(string $id)
    {
        $taking = Taking::find($id);
        return $taking;
    }

    /**
     * Update the specified resource in storage.
     */
    //Admin
    public function update(Request $request, $id)
    {
        $userId = $request->userId;
        $bookId = $request->bookId;

        $taking = Taking::find($id);
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

    /**
     * Remove the specified resource from storage.
     */
    //User
    public function destroy(Request $request, string $id)
    {
        $bearer = $request->bearerToken();
        if(is_null($bearer)){
            return response()->json(
                [
                    'status'=>'0',
                    'message'=>'Авторизуйтесь'
                ],
                404
            );
        }
        $token = Tokens::where('value', '=', $bearer)->get();
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



        $taking = Taking::find($id);
        if(is_null($taking)){
            return response()->json(
                [
                    'status' => 0,
                    'message' => 'Не найдено'
                ],
                404
            );
        }

        //Проверка пользовотеля
        if($taking->userId != $user->id){
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
            $taking->delete();
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
                    'message' => 'Успешно удален'
                ],
                200
            );
        }
    }
}

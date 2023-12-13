<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admins;
use App\Models\Books;
use App\Models\Tokens;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use App\Http\Resources\BooksResource;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $books = BooksResource::collection(Books::all());
        return $books;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
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
                404
            );
        }

        $admin = Admins::where('userId', '=', $user->id);
        if($admin->get()->count() < 1){
            return response()->json(
                [
                    'status'=>'0',
                    'message'=>'У вас не достаточно прав'
                ],
                404
            );
        }


        $doc = Books::create($request -> all());
        return new BooksResource($doc);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return $id;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Books $book)
    {
        $book ->update($request->all());
        return new BooksResource($book);
    }

    /**
     * Remove the specified resource from storage.
     * @throws AuthorizationException
     */
    public function destroy(Request $request, string $id)
    {
        $auth = getallheaders()['Authorization'];

        $token = Tokens::where('value', '=', $auth)->firstOrFail();
        if(is_null($token)){
            return response()->json(
                [
                    'status'=>'0',
                    'message'=>'Время истекло'
                ],
                404
            );
        }
        $user = User::find($token->userId);
        if(is_null($user)){
            return response()->json(
                [
                    'status'=>'0',
                    'message'=>'Авторизуйтесь'
                ],
                403
            );
        }

        $book = Books::find($id);
        if(is_null($book)){
            return response()->json(
                [
                    'status'=>'0',
                    'message'=>'Книга не найдена'
                ],
                404
            );
        }

        $book->delete();
        return response(null, 204);
    }
}

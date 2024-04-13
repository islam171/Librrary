<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BooksResource;
use App\Models\Books;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function getAll(){
        $books = BooksResource::collection(Books::all());
        return $books;
    }

    public function create(Request $request){
        $doc = Books::create($request -> all());
        return new BooksResource($doc);
    }

    public function update(Request $request, string $id)
    {
        $book = Books::find($id);
        $book->update($request->all());
        return new BooksResource($book);
    }

    public function destroy(Request $request, string $id)
    {
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

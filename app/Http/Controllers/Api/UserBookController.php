<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TakingResource;
use App\Models\Blockings;
use App\Models\Books;
use App\Models\userbooks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserBookController extends Controller
{
    public function create(Request $request)
    {
        $userId = $request->userId;
        $bookId = $request->bookId;

        $blocks = Blockings::where('userId', '=', $userId)->get();
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

        $today = date('Y-m-d');
        $today = $today . " 00:00:00";
        $returnDate = date("Y-m-d", strtotime("+7 days"));
        $returnDate = $returnDate . ' 00:00:00';

        $oldTaking = userbooks::where('bookId', '=', $bookId)->get();

        if($oldTaking->count() > 0){
            return response()->json(
                [
                    'status' => 0,
                    'message' => 'Книга уже добавлено'
                ],
                403
            );
        }

        DB::table('userbooks')->insert([
            'userId' => $userId,
            'bookId'=> $bookId,
            'IssueDate' => $today,
            'returnDate' => $returnDate
        ]);
        $takings = userbooks::where('bookId', '=', $bookId);
        return TakingResource::collection($takings->get())[0];
    }

    public function get(Request $request)
    {
        $userId = $request->userId;
        $userBooks = userbooks::where('userId', '=', $userId)->get();

        $books = [];
        foreach($userBooks as $item){
            $books[] = Books::find($item->bookId);
        }
        return $books;
    }

    public function delete(Request $request, string $id){

        $userId = $request->userId;

        $userBook = userbooks::where('bookId', '=', $id);

        if($userBook->get()->count() < 1){
            return response()->json(
                [
                    'status' => 0,
                    'message' => 'Не найдено'
                ],
                404
            );
        }

        //Проверка пользовотеля
        if($userBook->get()[0]->userId != $userId){
            return response()->json(
                [
                    'status' => 0,
                    'message' => 'Нет доступа'
                ],
                404
            );
        }

        $userBook->delete();
        return response(null, 204);
    }

}

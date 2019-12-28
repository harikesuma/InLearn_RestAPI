<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use LikeJawaban;
use DB;
class TopOfUserController extends Controller
{   
    public $successStatus = 200;

    public function getTopOfUser(){
        
        $likeJawaban = DB::select(
            DB::raw("SELECT jawaban_id, tb_user.user_name,
            COUNT(*) AS like_as FROM tb_like_jawaban 
            INNER JOIN tb_jawaban ON tb_like_jawaban.jawaban_id = tb_jawaban.`id` 
            INNER JOIN tb_user ON tb_jawaban.`user_id` = tb_user.`id`
            GROUP BY user_name ORDER BY like_as DESC"));

        $likeJawaban2['topOfUserList'] =$likeJawaban;
        return response()->json($likeJawaban2, $this->successStatus);
    }
}

<?php

namespace App\Http\Controllers\API;


use App\Pertanyaan; 
use App\Jawaban; 
use App\LikeJawaban;
use App\User;
use App\FCMToken;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use DB;
use Carbon\Carbon;
use Exception;
use Intervention\Image\ImageManagerStatic as Image;
use App\Notification;

class PertanyaanController extends Controller
{
    public $successStatus = 200;

    /**
     * API menampilkan seluruh pertanyaan di home awal app
     */

    public function showPertanyaan(Request $request)
    {
        // $request->validate([
        //     'kategori' => 'string',
        // ]);

        // $token = $request->header('Authorization');

        // $pertanyaan = DB::table('tb_pertanyaan')
        //                 ->join('tb_user','tb_pertanyaan.user_id', '=', 'tb_user.id')
        //                 ->join('tb_kategori','tb_pertanyaan.kategori_id', '=', 'tb_kategori.id')
        //                 ->select('tb_pertanyaan.id AS pertanyaanID', 'tb_pertanyaan.pertanyaan', 'tb_kategori.kategori', 'tb_pertanyaan.pict AS picture', 'tb_user.name AS nama_penanya', 'tb_user.user_name AS username_penanya', 'tb_user.pict AS user_pict', 'tb_pertanyaan.created_at AS tgl_post')
        //                 ->where('tb_kategori.kategori', '=', $request->kategori)
        //                 ->orderBy('tb_pertanyaan.id','DESC')->get();
        
        // $pertanyaan = $pertanyaan->toArray();
        
        // $data = [];

        // $i = 0;
        // foreach ($pertanyaan as $pertanya) {          

        //     $jawaban = DB::table('tb_jawaban')
        //                 ->join('tb_pertanyaan','tb_jawaban.pertanyaan_id', '=', 'tb_pertanyaan.id')
        //                 ->select(DB::raw('COUNT(tb_jawaban.id) AS jumlahJawaban'))
        //                 ->groupBy('tb_jawaban.pertanyaan_id')
        //                 ->where('tb_jawaban.pertanyaan_id', $pertanya->pertanyaanID)
        //                 ->first();

        //     $jwb = [
        //             'pertanyaanID' => $pertanya->pertanyaanID,
        //             'pertanyaan' => $pertanya->pertanyaan,
        //             'kategori' => $pertanya->kategori,
        //             'picture' => $pertanya->picture,
        //             'username_penanya' => $pertanya->username_penanya,
        //             'user_pict' => $pertanya->user_pict,
        //             'tgl_post' => $pertanya->tgl_post,
        //             'total_jawaban' => $jawaban->jumlahJawaban,
        //             ];

        //     array_push($data,$jwb);

        // }


        $pertanyaans = Pertanyaan::orderBy('id','DESC')->get();
        
        $pertanyaan['msg'] = "succes";

        foreach($pertanyaans as $pertanya){
            $pertanyaan['pertanyaanList'][] = array(
                'id' => $pertanya['id'],
                'user_id' => $pertanya['user_id'],
                'user_pict' => $pertanya->user->pict,
                'user_name' => $pertanya->user->user_name,
                'kategori' => $pertanya->kategori->kategori,
                'kategori_id' => $pertanya->kategori_id,
                'pertanyaan' => $pertanya['pertanyaan'],
                'pict' => $pertanya['pict'],
                'edited' => $pertanya['edited'],
                'total_jawaban' => $pertanya->countJawaban($pertanya['id']),
                'created_at' => $pertanya['created_at']
                );
        }
        
        return response()->json($pertanyaan,$this->successStatus);
    }



    /**
     * API untuk menampilkan history user, pertanyaan dan jawaban yang sudah di post oleh user
     */

    public function historyQuestion(Request $request)
    {
        // dd($request->userID);
        
        $pertanyaan =  DB::table('tb_pertanyaan')
                        ->join('tb_user','tb_pertanyaan.user_id','=','tb_user.id')
                        ->join('tb_kategori','tb_pertanyaan.kategori_id', '=', 'tb_kategori.id')
                        ->select('tb_pertanyaan.id AS pertanyaanID','tb_user.name AS nameUser','tb_user.id AS userID','tb_pertanyaan.pertanyaan AS pertanyaan','tb_kategori.kategori AS kategori','tb_pertanyaan.pict AS picture','tb_pertanyaan.created_at AS tgl_post')
                        ->where('tb_pertanyaan.user_id',$request->userID)
                        ->get();

        // dd($pertanyaan);

        $dataPertanyaan = [];

        foreach($pertanyaan as $per){

            $jawaban = DB::table('tb_jawaban')
                            ->join('tb_pertanyaan','tb_jawaban.pertanyaan_id', '=', 'tb_pertanyaan.id')
                            ->select(DB::raw('COUNT(tb_jawaban.id) AS jumlahJawaban'))
                            ->groupBy('tb_jawaban.pertanyaan_id')
                            ->where('tb_jawaban.pertanyaan_id', $per->pertanyaanID)
                            ->first();
            
            $pertanya = [
                    'pertanyaanID' => $per->pertanyaanID,
                    'userID' => $per->userID,
                    'namaUser' => $per->nameUser,
                    'pertanyaan' => $per->pertanyaan,
                    'kategori' => $per->kategori,
                    'picture' => $per->picture,
                    'tgl_post' => $per->tgl_post,
                    'total_jawaban' => $jawaban->jumlahJawaban,
                    ];
            
            array_push($dataPertanyaan,$pertanya);

        }

        // dd($dataPertanyaan);

        $answers = DB::table('tb_jawaban')
                        ->join('tb_user','tb_jawaban.user_id','=','tb_user.id')
                        ->join('tb_pertanyaan','tb_jawaban.pertanyaan_id','=','tb_pertanyaan.id')
                        ->select('tb_jawaban.id AS jawabanID','tb_jawaban.jawaban AS jawaban','tb_user.name AS namaUser')
                        ->where('tb_jawaban.user_id', $request->userID)
                        ->get();

        // $answers = DB::table('tb_jawaban')->get();

        $dataJawaban = [];
        
        foreach($answers as $ans){

            $like = DB::table('tb_like_jawaban')
                            ->join('tb_jawaban','tb_like_jawaban.jawaban_id','=','tb_jawaban.id')
                            ->select(DB::raw('COUNT(tb_like_jawaban.id) AS jmlLike'))
                            ->where('tb_like_jawaban.jawaban_id',$ans->jawabanID)
                            ->first();

            $likes = [
                'jawabanID' => $ans->jawabanID,
                'namaUser' => $ans->namaUser,
                'jawaban' => $ans->jawaban,
                'jmlLike' => $like->jmlLike
            ];

            array_push($dataJawaban,$likes);
            
        }

        // dd($dataJawaban);
        return response()->json([
            'pertanyaan' => $dataPertanyaan,
            'jawaban' => $dataJawaban,
        ], $this->successStatus);


    }

    /**
     * Upload Pertanyaan user beserta gambar
     */
    public function postPertanyaan(Request $request)
    {
        $request->validate([
           'user_id' => 'required',
           'kategori_id' => 'required',
           'question' => 'required|string',
           'imageUpload' => 'image|nullable|max:5000',
        ]);
        $files = $request->file('imageUpload');

        try {
    
            if(!empty($files)) {
                $folderName = 'pertanyaan';
                $fileName = $folderName.'_image';
                $fileExtension = $files->getClientOriginalExtension();
                $fileNameToStorage = $fileName.'_'.time().'.'.$fileExtension;

                $image_resize = Image::make($files->getRealPath())->resize(500,500);
                
                $filePath = $image_resize->save('storage/pertanyaan/'.$fileNameToStorage); 
                
            } else {
                $filePath = NULL;
            }

            // dd($path);
 
            
            DB::table('tb_pertanyaan')->insert([
                'user_id' => $request->user_id,
                'kategori_id' => $request->kategori_id,
                'pertanyaan' => $request->question,
                'pict' => $fileNameToStorage,
                'edited' => "0",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);


        } catch (Exception $e) {
            
            return response()->json([
                'msg' => $e->getMessage(),
            ], 401);

        }

            return response()->json([
                'msg' => 'Pertanyaan berhasil di Upload'
            ], $this->successStatus);


    }

    public function showUpdatePertanyaan(Request $request)
    {
        $request->validate([
            'pertanyaanID' => 'required',
        ]);
        
        $data = DB::table('tb_pertanyaan')
                    ->join('tb_kategori','tb_pertanyaan.kategori_id','=','tb_kategori.id')
                    ->select('tb_pertanyaan.id AS pertanyaanID','tb_pertanyaan.pertanyaan AS pertanyaan','tb_kategori.kategori AS kategori','tb_kategori.id AS kategoriID','tb_pertanyaan.pict AS picture')
                    ->where('tb_pertanyaan.id',$request->pertanyaanID)
                    ->first();

        return response()->json([
            'pertanyaanID' => $data->pertanyaanID,
            'kategori' => $data->kategori,
            'pertanyaan' => $data->pertanyaan,
            'picture' => $data->picture,
        ],$this->successStatus);
        
    }

    public function storeUpdatePertanyaan(Request $request)
    {

        $request->validate([
            'pertanyaanID' => 'required',
            'kategoriID' => 'required',
            'pertanyaan' => 'required|string',
            'picture' => 'image|nullable|max:5000',
        ]);

        try {

            $kategori = DB::table('tb_kategori')->where('tb_kategori.kategori',$request->kategori)->first();

            DB::table('tb_pertanyaan')
                    ->where('tb_pertanyaan.id',$request->pertanyaanID)
                    ->updates([
                        'pertanyaan' => $request->pertanyaan,
                        'kategori' => $kategori->id,
                        'pict' => $request->picture,
                        'edited' => 2,
                        'updated_at' => Carbon::now(),
                    ]);

        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ],401);
        }

        return response()->json([
            'status' => 'Pertanyaan berhasil di Update'
        ], $this->successStatus);

    }

    public function showDetailPertanyaan($id){
        $pertanyaan = Pertanyaan::findOrFail($id);
        return response()->json([
            'msg' => 'success',
            'id' => $pertanyaan['id'],
            'user_pict' => $pertanyaan->user->pict,
            'user_name' => $pertanyaan->user->user_name,
            'kategori' => $pertanyaan->kategori->kategori,
            'pertanyaan' => $pertanyaan['pertanyaan'],
            'pict' => $pertanyaan['pict'],
            'edited' => $pertanyaan['edited'],
            'created_at' => $pertanyaan['created_at'],
        ], $this->successStatus);
    
    }

    public function showComment($id){
        $jawabans = Jawaban::where('pertanyaan_id',$id)->get();
          
        $jawaban['msg'] = "succes";

        foreach($jawabans as $jawab){
            $jawaban['jawabanList'][] = array(
                'id' => $jawab['id'],
                'user_name' => $jawab->user->user_name,
                'user_pict' => $jawab->user->pict,
                'comment' => $jawab->jawaban,
                'like'=> $jawab->countLike($jawab['id']),
                'created_at' => $jawab['created_at'],
                'user_id' => $jawab->user_id
                );
        }
        
        return response()->json($jawaban,$this->successStatus);
    }

    public function commentLike($id, Request $request){
        $user = User::where('id','=',$request->user_id)->first();
        $userComment = $user->name;

        $likejawaban = new LikeJawaban();
        $likejawaban->jawaban_id = $id;
        $likejawaban->user_id = $request->user_id;
        $likejawaban->save();

        $toUser = Jawaban::where('id','=', $id)->first();
        $toUser = $toUser->user_id;

        $toUserFCM = FCMToken::where('user_id','=',$toUser)->value('fcm_token');
         /*FCM*/  

         $key = 'AAAAunCvjOc:APA91bE8yDf4y9_EjRMjNVBjyekP01pMWhf0kWuXxlSH9THRfHpGa3kB92_9XZVx9RCu4UYKWV_knZvhxa6sQiWSMEmszo2ryJwxnKOcxpw5qoB7cTF5Sr4tKB7bt-NdahxOFTLlQhxQ';
         $fcmUrl = 'https://fcm.googleapis.com/fcm/send';

         $headers = array(
            'Authorization: key='.$key,
            'Content-Type: application/json'
        );

        $fields = array(
            'to'    =>  $toUserFCM,
            // 'registration_ids'=>$request->user_id,
            'notification' => array(
                'title' => "InLearn",
                'body' => $userComment." liked on you're answer!",
                'sound'=>'default',
                'click_action' => "NOTIFICATION_ACTIVITY"
            )
        );


        $notification = new Notification;
        $notification->user_id = $toUser;
        $notification->notification = $userComment." liked on you're answer!";
        $notification->save();

        $curl_session = curl_init();
        curl_setopt($curl_session, CURLOPT_URL,$fcmUrl);
        curl_setopt($curl_session, CURLOPT_POST, true);
        curl_setopt($curl_session, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_session, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl_session, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($curl_session);
        curl_close($curl_session);
         /*FCM*/    

        $status["msg"] = "liked!";
        $status["id"] = $id;

        return response()->json($status,$this->successStatus);

    }

    public function postComment(Request $request){
        $user = User::where('id','=',$request->user_id)->first();
        $userComment = $user->name;
        

        $jawaban = new Jawaban();
        $jawaban->pertanyaan_id = $request->pertanyaan_id;
        $jawaban->user_id = $request->user_id;
        $jawaban->jawaban = $request->jawaban;
        $jawaban->save();

        $toUser = Pertanyaan::where('id','=', $request->pertanyaan_id)->first();
        $toUser = $toUser->user_id;

        $toUserFCM = FCMToken::where('user_id','=',$toUser)->value('fcm_token');


         /*FCM*/  

         $key = 'AAAAunCvjOc:APA91bE8yDf4y9_EjRMjNVBjyekP01pMWhf0kWuXxlSH9THRfHpGa3kB92_9XZVx9RCu4UYKWV_knZvhxa6sQiWSMEmszo2ryJwxnKOcxpw5qoB7cTF5Sr4tKB7bt-NdahxOFTLlQhxQ';
         $fcmUrl = 'https://fcm.googleapis.com/fcm/send';

         $headers = array(
            'Authorization: key='.$key,
            'Content-Type: application/json'
        );

        $fields = array(
            'to'    =>  $toUserFCM,
            // 'registration_ids'=>$request->user_id,
            'notification' => array(
                'title' => "InLearn",
                'body' => $userComment." comment on you're post!",
                'sound'=>'default',
                'click_action' => "NOTIFICATION_ACTIVITY"
            )
        );


        $notification = new Notification;
        $notification->user_id = $toUser;
        $notification->notification = $userComment." comment on you're post!";
        $notification->save();

        $curl_session = curl_init();
        curl_setopt($curl_session, CURLOPT_URL,$fcmUrl);
        curl_setopt($curl_session, CURLOPT_POST, true);
        curl_setopt($curl_session, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_session, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl_session, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($curl_session);
        curl_close($curl_session);
         /*FCM*/    

        $status["msg"] = "comment posted!";

        return response()->json($status,$this->successStatus);
    }
    
    public function getUserQuestionHistory($id){
        $pertanyaans = Pertanyaan::where('user_id', $id)->orderBy('id','DESC')->get();
        
        $pertanyaan['msg'] = "succes";

        foreach($pertanyaans as $pertanya){
            $pertanyaan['pertanyaanList'][] = array(
                'id' => $pertanya['id'],
                'user_id' => $pertanya->user_id,
                'user_pict' => $pertanya->user->pict,
                'user_name' => $pertanya->user->user_name,
                'kategori' => $pertanya->kategori->kategori,
                'pertanyaan' => $pertanya['pertanyaan'],
                'pict' => $pertanya['pict'],
                'edited' => $pertanya['edited'],
                'total_jawaban' => $pertanya->countJawaban($pertanya['id']),
                'created_at' => $pertanya['created_at']
                );
        }
        
        return response()->json($pertanyaan,$this->successStatus);


    }

    public function getUserAnswerHistory($id){
        $jawabans = Jawaban::where('user_id',$id)->orderBy('id','DESC')->get();
          
        $jawaban['msg'] = "succes";

        foreach($jawabans as $jawab){
            $jawaban['jawabanList'][] = array(
                'id' => $jawab['id'],
                'user_name' => $jawab->user->user_name,
                'user_pict' => $jawab->user->pict,
                'comment' => $jawab->jawaban,
                'like'=> $jawab->countLike($jawab['id']),
                'created_at' => $jawab['created_at'],
                'user_id' => $jawab->user_id
                );
        }
        
        return response()->json($jawaban,$this->successStatus);
    }

    public function deleteAnswer($id){
        $jawaban = Jawaban::find($id);
        $jawaban->delete();

        return response()->json(["msg"=>"comment deleted"],$this->successStatus);
    }

    public function deleteQuestion($id){
        $pertanyaan = Pertanyaan::find($id);
        $pertanyaan->delete();

        return response()->json(["msg"=>"question deleted"],$this->successStatus);
    }

    public function showEditQuestion($id){
        $pertanyaan = Pertanyaan::findOrFail($id);
        
        $pertanyaan['msg'] = "success";
        return response()->json($pertanyaan, $this->successStatus);
    }

    public function editQuestion($id, Request $request){
        $pertanyaan = Pertanyaan::findOrFail($id);

        $pertanyaan->kategori_id = $request->kategori_id;
        $pertanyaan->pertanyaan = $request->pertanyaan;
        $pertanyaan->edited = "1";
        $pertanyaan->save();

        return response()->json(['msg' => "Update Success"], $this->successStatus);


    }

}

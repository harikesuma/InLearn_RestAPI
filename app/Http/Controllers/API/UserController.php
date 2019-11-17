<?php

namespace App\Http\Controllers\API;

use App\User; 
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Http\Request;
use Validator;
use DB;


class UserController extends Controller
{
    public $successStatus = 200;

    public function login(){
        if(Auth::attempt(['user_name' => request('user_name'), 'password' => request('password')])){
            $user = Auth::user();
            
            return response()->json([
                'success' => 'success',
                'status' => 'true',
                'user_name' => $user->user_name,
                'name' => $user->name,
                'email' => $user->email,
                'pict' => $user->pict,
                'token' =>  $user->createToken('InLearnApp')->accessToken,
        ], $this->successStatus);
        }
        else{
            return response()->json([
                'error'=>'Unauthorised',
                'status'=> 'false',
            ], 401);
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'user_name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'jenis_kelamin' => 'required'
        
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);            
        }

        $input = $request->all();

        $count = User::where(['user_name' => $input['user_name']])->count();
        if($count) {

            // $response['status'] = 401;
            // $response['message'] = "Username already exist";

            return response()->json([
                'status'=> 401,
                'message' => 'Username already exist'   
                ], 401);      
        }

        $count = User::where(['email' => $input['email']])->count();
        if($count) {

            // $response['status'] = 401;
            // $response['message'] = "Email already used";

            return response()->json([
                'status' => 401,
                'message' => "Email already used",
            ], 401);      
        }
        
        
        $input['password'] = bcrypt($input['password']);
        $input['status'] = '1';
        $input['pict'] = 'not selected yet';
        
        $user = User::create($input);

        // $success['token'] =  $user->createToken('InLearnApp')->accessToken;
        // $success['name'] =  $user->name;
        // $success['user_name'] =  $user->user_name;
        // $success['email'] =  $user->email;

        return response()->json([
            'status'=>'true',
           'success'=>'success',
           'token' =>  $user->createToken('InLearnApp')->accessToken,
           'name' =>  $user->name,
           'user_name' =>  $user->user_name,
           'email' =>  $user->email,
    ], $this->successStatus);
    }

    public function details()
    {
        $user = Auth::user();
        return response()->json(['success' => $user], $this->successStatus);
    }


    public function showPertanyaan(Request $request)
    {
        // $request->validate([
        //     'kategori' => 'string',
        // ]);

        // $token = $request->header('Authorization');

        $pertanyaan = DB::table('tb_pertanyaan')
                        ->join('tb_user','tb_pertanyaan.user_id', '=', 'tb_user.id')
                        ->join('tb_kategori','tb_pertanyaan.kategori_id', '=', 'tb_kategori.id')
                        ->select('tb_pertanyaan.id AS pertanyaanID', 'tb_pertanyaan.pertanyaan', 'tb_kategori.kategori', 'tb_pertanyaan.pict AS picture', 'tb_user.name AS nama_penanya', 'tb_user.user_name AS username_penanya', 'tb_user.pict AS user_pict', 'tb_pertanyaan.created_at AS tgl_post')
                        ->where('tb_kategori.kategori', '=', $request->kategori)
                        ->orderBy('tb_pertanyaan.id','DESC')->get();
        
        $pertanyaan = $pertanyaan->toArray();
        
        $data = [];

        $i = 0;
        foreach ($pertanyaan as $pertanya) {          

            $jawaban = DB::table('tb_jawaban')
                        ->join('tb_pertanyaan','tb_jawaban.pertanyaan_id', '=', 'tb_pertanyaan.id')
                        ->select(DB::raw('COUNT(tb_jawaban.id) AS jumlahJawaban'))
                        ->groupBy('tb_jawaban.pertanyaan_id')
                        ->where('tb_jawaban.pertanyaan_id', $pertanya->pertanyaanID)
                        ->first();

            $jwb = [
                    'pertanyaanID' => $pertanya->pertanyaanID,
                    'pertanyaan' => $pertanya->pertanyaan,
                    'kategori' => $pertanya->kategori,
                    'picture' => $pertanya->picture,
                    'username_penanya' => $pertanya->username_penanya,
                    'user_pict' => $pertanya->user_pict,
                    'tgl_post' => $pertanya->tgl_post,
                    'total_jawaban' => $jawaban->jumlahJawaban,
                    ];

            array_push($data,$jwb);

        }
        
        return response()->json([
            'pertanyaan' => $data,
        ],$this->successStatus);
    }

    public function historyQuestion(Request $request)
    {
        // dd($request->userID);
        $dataPertanyaan = [];
        
        $pertanyaan =  DB::table('tb_pertanyaan')
                        ->join('tb_user','tb_pertanyaan.user_id','=','tb_user.id')
                        ->join('tb_kategori','tb_pertanyaan.kategori_id', '=', 'tb_kategori.id')
                        ->select('tb_pertanyaan.id AS pertanyaanID','tb_user.id AS userID','tb_pertanyaan.pertanyaan AS pertanyaan','tb_kategori.kategori AS kategori','tb_pertanyaan.pict AS picture','tb_pertanyaan.created_at AS tgl_post')
                        ->where('tb_pertanyaan.id',$request->userID)
                        ->get();

        // dd($pertanyaan);

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
                    'pertanyaan' => $per->pertanyaan,
                    'kategori' => $per->kategori,
                    'picture' => $per->picture,
                    'tgl_post' => $per->tgl_post,
                    'total_jawaban' => $jawaban->jumlahJawaban,
                    ];
            
            array_push($dataPertanyaan,$pertanya);

        }

        dd($dataPertanyaan);

        $answers = DB::table('tb_jawaban')
                        ->join('tb_user','tb_jawaban.user_id','=','tb_user.id')
                        ->join('tb_like_pertanyaan','tb_like_jawaban.jawaban_id','=','tb_jawaban.id')
                        ->join('tb_pertanyaan','tb_jawaban.pertanyaan_id','=','tb_pertanyaan.id')
                        ->select('tb_jawaban.id AS jawabanID','tb_jawaban.jawaban AS jawaban','tb_user.name AS namaUser')
                        ->get();

    }

}

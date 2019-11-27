<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use DB;
use Carbon\Carbon;
use Exception;

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
           'userID' => 'required',
           'kategoriID' => 'required',
           'pertanyaan' => 'required|string',
           'pic' => 'image|nullable|max:5000',
        ]);

        try {

            if($request->hasFile('pic')){
                $path = $request->file('pic')->store('public/pic_Pertanyaan'); //upload file
            } else {
                $path = NULL;
            }

            // dd($path);
 
            
            DB::table('tb_pertanyaan')->insert([
                'user_id' => $request->userID,
                'kategori_id' => $request->kategoriID,
                'pertanyaan' => $request->pertanyaan,
                'pict' => $path,
                'edited' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => NULL,
            ]);


        } catch (Exception $e) {
            
            return response()->json([
                'messages' => $e->getMessage(),
            ], 401);

        }

            return response()->json([
                'messages' => 'Pertanyaan berhasil di Upload'
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



}

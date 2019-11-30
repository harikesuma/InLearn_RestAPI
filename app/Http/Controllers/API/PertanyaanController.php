<?php

namespace App\Http\Controllers\API;


use App\Pertanyaan; 
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use DB;
use Carbon\Carbon;
use Exception;
use Intervention\Image\ImageManagerStatic as Image;

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
                
                $filePath = $image_resize->save(public_path('storage/pertanyaan/'.$fileNameToStorage)); 
                
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



}

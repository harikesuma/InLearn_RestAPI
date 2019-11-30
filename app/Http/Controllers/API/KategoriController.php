<?php

namespace App\Http\Controllers\API;


use App\Kategori; 
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    public $successStatus = 200;

    public function getKategori(){
        

        // $kategori = Kategori::get();
        // return response()->json(['success' => $kategori], $this-> successStatus); 


        $kategoris = Kategori::get();

        $kategori['msg'] = 'success';
        foreach($kategoris as $kat){
            $kategori['kategoriList'][] = array(
                'id' => $kat['id'],
                'kategori' => $kat['kategori'],
                );
        }
        
        return response()->json($kategori); ;


    }

}

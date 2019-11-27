<?php

namespace App\Http\Controllers\API;


use App\Kategori; 
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class KategoriController extends Controller
{

    public function getKategori(){
        $kategori = Kategori::all();
        foreach($kategori as $kat){
            $kat['kategoriList'][] = array(
                'id' => $data['id'],
                'kategori' => $data['kategori'],
                );
        }
    }

}

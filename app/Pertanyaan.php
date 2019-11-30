<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pertanyaan extends Model
{
     
    protected $table = 'tb_pertanyaan';
    protected $primaryKey = 'id';

       /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id','pertanyaan','kategori_id','pict','edited'
    ];

    public function kategori()
    {
        return $this->belongsTo('App\Kategori');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function countJawaban($id)
    {
        $jawaban = Jawaban::where('pertanyaan_id','=',$id)->get();
        $totalJawaban = $jawaban->count();
        return $totalJawaban;
    }

    public function jawaban()
    {
        return $this->hasMany('App\Jawaban');
    }
}

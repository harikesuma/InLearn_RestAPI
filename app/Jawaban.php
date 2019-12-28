<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Jawaban extends Model
{
    protected $table = 'tb_jawaban';
    protected $primaryKey = 'id';

       /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'pertanyaan_id', 'user_id', 'jawaban'
    ];

    public function pertanyaan()
    {
        return $this->belongsTo('App\Pertanyaan');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function likeJawaban(){
        return $this->hasMany('App\LikeJawaban');
    }

    public function countLike($id){
        $like = LikeJawaban::where('jawaban_id','=',$id)->get();
        $totallike = $like->count();
        return $totallike;
    }
}

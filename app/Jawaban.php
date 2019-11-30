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
}

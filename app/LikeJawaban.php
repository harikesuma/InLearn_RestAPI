<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LikeJawaban extends Model
{
    protected $table = 'tb_like_jawaban';
    protected $primaryKey = 'id';

       /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'jawaban_id', 'user_id'
    ];
}

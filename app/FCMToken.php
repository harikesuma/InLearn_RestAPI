<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FCMToken extends Model
{
    protected $table = 'tb_fcm';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id', 'fcm_token'
   ];

   public function user()
   {
       return $this->belongsTo('App\User');
   }
}

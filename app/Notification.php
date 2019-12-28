<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'tb_notification';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id', 'notification'
   ];

   public function user()
   {
       return $this->belongsTo('App\User');
   }
}

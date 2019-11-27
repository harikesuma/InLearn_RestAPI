<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
  
    protected $table = 'tb_kategori';
    protected $primaryKey = 'id';

       /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'kategori'
    ];
}

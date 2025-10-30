<?php

namespace App\Models\memo;

use Illuminate\Database\Eloquent\Model;

class Cipherkey extends Model
{
    // protected $table = 'Fin.cipherkeys';
    // protected $connection = "Fin";
    protected $fillable = ['usr_id', 'date', 'tag', 'status', 'details'];
}

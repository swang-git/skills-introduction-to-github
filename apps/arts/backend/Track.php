<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Track extends Model
{
    protected $table = 'Logs.tracks';
    protected $connection = "Logs";
}


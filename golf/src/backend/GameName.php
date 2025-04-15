<?php

namespace App\Models\golf;

use Illuminate\Database\Eloquent\Model;

class GameName extends Model
{
    protected $table = "game_names";
    protected $connection = "golf_dev";
    // protected $connection = "princeton_golf";
    // protected $fillable = ['usr_id', 'recursive', 'due date', 'type', 'message', 'status', 'details'];
}

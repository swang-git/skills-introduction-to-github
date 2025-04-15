<?php

namespace App\Models\golf;

use Illuminate\Database\Eloquent\Model;

class Tplayer extends Model
{
    // protected $table = "golf_dev.tplayers";
    // protected $connection = "golf_dev";
    protected $fillable = ['tournament_id', 'game_id', 'year', 'tnum', 'name', 'grp', 'captain', 'player_id', 'hole19'];
    // public function players()
    // {
    //     return $this->belongsTo('App\Models\golf\Player');
    // }
}

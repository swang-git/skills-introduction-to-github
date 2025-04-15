<?php
namespace App\Models\golf;

use Illuminate\Database\Eloquent\Model;

class MatchGroup extends Model {
    protected $fillable = ['tournament_id', 'group_scenario'];
}

<?php
namespace App\Models\golf;

use Illuminate\Database\Eloquent\Model;

class Membership extends Model
{
    protected $table = 'memberships';
    protected $connection = 'golf_dev';
    // protected $connection = 'princeton_golf';
    // protected $connection = config('database.connections.Golf.database');
    // protected $connection = config('constants.dirs.webdata');
    // protected $connection = env('DB_DATABASE');
    // protected $fillable = ['player_id', 'year', 'fees'];
}

<?php
namespace App\Models\arts;

use Illuminate\Database\Eloquent\Model;

class DailyArt extends Model
{
    /**
    * The table associated with the model.
    *
    * @var string
    */
    protected $table = 'MyWeb.DailyArt'; //default table name will be DailyArt

    /**
    * Indicates if the model should be timestamped if no created_at and updated_at columns
    *
    * @var bool
    */
    public $timestamps = false;

    /**
    * The connection name for the model.
    *
    * @var string
    */
    // protected $connection = 'MyWeb';
}

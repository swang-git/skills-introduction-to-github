<?php
namespace App\Models\Art;

use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    /**
    * The table associated with the model.
    *
    * @var string
    */
    protected $table = 'Art.photos'; //default table name will be DailyArt

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

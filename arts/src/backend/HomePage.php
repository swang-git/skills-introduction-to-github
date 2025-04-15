<?php
namespace App\Models\arts;

use Illuminate\Database\Eloquent\Model;

class HomePage extends Model
{
    /**
    * The table associated with the model.
    *
    * @var string
    */
    protected $table = 'MyWeb.homepage'; //default table name will be homepage

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

<?php
namespace App\Models\arts;

use Illuminate\Database\Eloquent\Model;

class ArtClass extends Model
{
    /**
    * The table associated with the model.
    *
    * @var string
    */
    protected $table = 'art_class';

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
    protected $connection = 'MyWeb';
}

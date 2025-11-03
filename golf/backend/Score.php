<?php
namespace App\Models\golf;

use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    /**
    * The table associated with the model.
    *
    * @var string
    */
    protected $table = 'scores'; //default table name
    // protected $table = 'princeton_golf.scores'; // this is for accessing from expense app

    /**
    * The connection name for the model.
    *
    * @var string
    */
    protected $connection = 'golf_dev';
    protected $fillable = ['id','player_id','tournament_id','course_id','teetime','teebox_id','h1','h2','h3','h4','h5','h6','h7','h8','h9','h10','h11','h12','h13','h14','h15','h16','h17','h18','front9','back9','totalscore','hcapdiff'];
    // protected $connection = 'princeton_golf';

    /**
    * Indicates if the model should be timestamped if no created_at and updated_at columns
    *
    * @var bool
    */
    // public $timestamps = false;
}

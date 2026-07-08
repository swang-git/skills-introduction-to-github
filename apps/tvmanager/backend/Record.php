<?php
namespace App\Models\tv;

use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    protected $connection = 'mythconverg';
    protected $table = 'record';
    protected $primaryKey = 'recordid';
    // const UPDATED_AT = 'lastmodified'; 
    // protected $fillable = ['watched'];
    //protected $fillable = ['user_id', 'recursive', 'due_date', 'tag', 'message', 'status', 'details', 'link'];
}

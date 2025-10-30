<?php
namespace App\Models\tv;

use Illuminate\Database\Eloquent\Model;

class Recorded extends Model
{
    protected $connection = 'mythconverg';
    protected $table = 'recorded';
    protected $primaryKey = 'recordedid';
    const UPDATED_AT = 'lastmodified'; 
    protected $fillable = ['watched'];
    //protected $fillable = ['user_id', 'recursive', 'due_date', 'tag', 'message', 'status', 'details', 'link'];
}

<?php
namespace App\Models\tv;

use Illuminate\Database\Eloquent\Model;

class Oldrecorded extends Model
{
    protected $connection = 'mythconverg';
    protected $table = 'oldrecorded';
    const UPDATED_AT = 'lastmodified'; 
}

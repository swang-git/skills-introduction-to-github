<?php
namespace App\Models\dictionary;
use Illuminate\Database\Eloquent\Model;
class Dictionary extends Model
{
    protected $fillable = ['datetime', 'english', 'chinese', 'note', 'lnks'];
    // protected $fillable = ['usr_id', 'date', 'tag', 'status', 'details','link'];
}

<?php
namespace App\Models\memo;
use Illuminate\Database\Eloquent\Model;
class Memo extends Model
{
    protected $fillable = ['usr_id', 'date', 'tag', 'reminder', 'status', 'details','link'];
}

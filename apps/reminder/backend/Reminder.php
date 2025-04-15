<?php
namespace App\Models\reminder;

use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    protected $fillable = ['user_id', 'recursive', 'due_date', 'type', 'message', 'status', 'details', 'link'];
}

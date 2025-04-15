<?php
namespace App\Models\golf;

use Illuminate\Database\Eloquent\Model;

class CourseYard extends Model
{
    protected $table = "course_yards";
    protected $connection = "golf_dev";
    // protected $connection = "princeton_golf";
    // protected $fillable = ['usr_id', 'recursive', 'due date', 'type', 'message', 'status', 'details'];
}

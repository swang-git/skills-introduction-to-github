<?php
namespace App\Models\golf;

use Illuminate\Database\Eloquent\Model;

class CourseHandicap extends Model
{
    protected $table = "course_handicaps";
    protected $connection = "golf_dev";
    // protected $connection = "princeton_golf";
    // protected $fillable = ['usr_id', 'recursive', 'due date', 'type', 'message', 'status', 'details'];
}

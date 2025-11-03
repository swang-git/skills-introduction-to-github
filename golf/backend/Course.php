<?php
namespace App\Models\golf;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $table = 'golf_dev.courses';
    // protected $table = 'princeton_golf.courses';
    // protected $connection = "princeton_golf";
    protected $connection = "golf_dev";
    protected $fillable = ['name'];
}

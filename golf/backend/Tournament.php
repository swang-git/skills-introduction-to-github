<?php
namespace App\Models\golf;
use Illuminate\Database\Eloquent\Model;
class Tournament extends Model {
    //protected $table = "golf.tournaments";
    //protected $connection = "golf";

    protected $fillable = ['start_at'];
    public function tplayers() { return $this->hasMany('\App\Models\golf\Tplayer'); }
    public function course() { return $this->belongsTo(Course::class); }
    public function mteebox() { return $this->belongsTo(CourseTee::class, 'mens_tee_id'); }
    public function lteebox() { return $this->belongsTo(CourseTee::class, 'lady_tee_id'); }
}

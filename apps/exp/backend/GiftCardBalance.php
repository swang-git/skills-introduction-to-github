<?php

namespace App\Models\expense;

use Illuminate\Database\Eloquent\Model;

class GiftCardBalance extends Model {
    // protected $connection = "Fin";
     /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    //protected $guarded = [];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['spend_id'];
    // public function spend () {
    //     return $this->belongsTo('App\Models\Spend');
    // }
}

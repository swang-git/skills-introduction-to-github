<?php

namespace App\Models\Art;

use Illuminate\Database\Eloquent\Model;

class ArtMenu extends Model
{
    protected $table = 'Art.art_menus'; //default table name will be DailyDat
    protected $connection = "Art";
}

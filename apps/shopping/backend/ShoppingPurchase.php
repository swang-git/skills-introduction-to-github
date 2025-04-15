<?php

namespace App\Models\shopping;

use Illuminate\Database\Eloquent\Model;

class ShoppingPurchase extends Model {
    protected $fillable = [ 'id', 'date', 'name', 'item_id', 'price', 'units', 'costs', 'status' ];
}

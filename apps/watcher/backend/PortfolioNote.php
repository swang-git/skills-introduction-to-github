<?php
namespace App\Models\watcher;
use Illuminate\Database\Eloquent\Model;
class PortfolioNote extends Model {
    protected $fillable = ['date', 'user_id', 'faccount', 'symbol', 'action', 'price', 'share', 'taccount', 'note'];
}


<?php
namespace App\Models\glucosecheck;
use Illuminate\Database\Eloquent\Model;
class GlucoseCheck extends Model {
  protected $fillable = ['user_id', 'datetime', 'fasting', 'af2hour', 'food', 'drink', 'fruit', 'note'];
}

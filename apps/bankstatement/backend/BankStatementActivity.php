<?php
namespace App\Models\bankstatement;
use Illuminate\Database\Eloquent\Model;
class BankStatementActivity extends Model {
  // protected $fillable = ['user_id', 'year', 'month', 'bank', 'security', 'price', 'quantity', 'account_num', 'account_name', 'amount', 'sett_date', 'idx', 'description', 'cost'];
  protected $fillable = ['user_id', 'year', 'month', 'bank', 'security', 'account_num', 'account_name', 'amount', 'sett_date', 'idx', 'description', 'cost'];
}

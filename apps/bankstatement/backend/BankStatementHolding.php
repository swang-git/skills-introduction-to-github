<?php
namespace App\Models\bankstatement;
use Illuminate\Database\Eloquent\Model;
class BankStatementHolding extends Model {
    protected $fillable = ['user_id', 'year', 'month', 'bank', 'symbol', 'price', 'quantity', 'eai', 'account_num', 
    'account_name', 'start_balance', 'end_balance', 'cost', 'eai', 'ey'];
}

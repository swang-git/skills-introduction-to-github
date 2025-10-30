<?php
namespace App\Models\bankstatement;
use Illuminate\Database\Eloquent\Model;
class BankAccountActivity extends Model {
    protected $fillable = ['bank', 'user_id', 'account_num', 'year', 'month', 'acct_type', 'tran_num', 'tran_date', 'description',  'begin_balance', 'amount', 'end_balance'];
}

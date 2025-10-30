<?php

namespace App\Models\bankstatement;
use Illuminate\Database\Eloquent\Model;
class BankStatementAsset extends Model {
    protected $fillable = ['user_id', 'year', 'month', 'begin_date', 'end_date', 'bank', 'tran_cnt', 'primary_account', 'begin_balance', 'end_balance'];
}

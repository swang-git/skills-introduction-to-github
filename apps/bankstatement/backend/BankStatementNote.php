<?php
namespace App\Models\bankstatement;
use Illuminate\Database\Eloquent\Model;
class BankStatementNote extends Model {
protected $fillable = ['bank', 'user_id', 'year', 'month', 'note_id', 'notes', 'amount'];
}

<?php
namespace App\Models\pfcheck;

use Illuminate\Database\Eloquent\Model;

class PfCheck extends Model
{
    protected $fillable = ['usr_id', 'datetime', 'vol', 'note', 'status'];
    // protected $fillable = ['usr_id', 'datetime', 'vol', 'status', 'created_at', 'updated_at'];
}

<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use App\Models\expense\GCB_view;

class CheckGCBalance extends Command {
  // use AsAction;
  // protected $signature = 'user:update-password {user_id} {password}';
  protected $signature = 'giftcard:check_gift_card_balance {paymId} {limit}';
  protected $description = 'checking golf gift card balance';

  public function handle() {
    $gcbs = GCB_view::where('paymId', $this->argument('paymId'))->select('spendId', 'spendDate', 'cost', 'balance')->limit($this->argument('limit'))->get();
    $idx = 0;
    $pcost = $gcbs[0]->cost;
    $pbals = $gcbs[0]->balance;
    $this->line(sprintf('spendDate:%s spendId:%s cost:%s balance:%s check:%s', $gcbs[0]->spendDate, $gcbs[0]->spendId, $gcbs[0]->cost, $gcbs[0]->balance, $gcbs[1]->balance - $pcost - $pbals));
    foreach ($gcbs as $b) {
      // Log::info($b->spendId, [$b->spendDate, $b->cost, $b->balance]);
      if ($idx >= 1) {
        // Log::info($b->spendId, ['spendDate' => $b->spendDate, 'check' => $b->balance - $pcost - $pbals]);
        $this->line(sprintf('spendDate:%s spendId:%s cost:%s balance:%s check:%s', $b->spendDate, $b->spendId, $b->cost, $b->balance, $b->balance - $pcost - $pbals));
        $pcost = $b->cost;
        $pbals = $b->balance;
      }
      $idx++;
    }
  }
}

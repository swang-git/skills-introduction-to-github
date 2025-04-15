<?php
namespace App\Traits;
use Illuminate\Support\Facades\Log;

trait IraHoldingsTrait {
  private function getIndIraHoldings($data, $lines, $start, $end) { Log::info("-CK-getIndIraHoldings Start_line=$start End_line=$end");
    $holdings = [];
    $acctInd = 'Z71-367818';
    for ($i=$start; $i < $end; $i++) {
      $line = $lines[$i];
      $this->setHoldFlag($line);
      // Log::debug("-CK-getIndIraHoldings holdFlag", [self::$holdFlag]);
      if (self::$holdFlag == 'Core Account' and preg_match('/\(SPAXX\)/', $line) > 0) {
        Log::debug("-CK-getIndIraHoldings Core Account and SPAXX $line");
        // [$EAI, $EY] = $this->getEaiEy($lines[$i + 8]);
        $x = [
          'SPAXX',
          $this->cleanMoney($lines[$i + 5]), // begin value
          $this->cleanMoney($lines[$i + 6]), // quantity
          $this->cleanMoney($lines[$i + 7], 'SPAXX price'), // price
          $this->cleanMoney($lines[$i + 8]), // end value
          null, // cost
          null, // unrealized gain/lose
          // $this->cleanMoney($lines[$i + 8]) // EAI
          $lines[$i + 9], // EAI,
          $lines[$i + 10]  // EY
        ];
        $holdings['SPAXX'] = $this->procHoldings($x);
      } else if (self::$holdFlag == 'Core Account' and preg_match('/\(FNJXX\)/', $line) > 0) {
        Log::debug("-CK-getIndIraHoldings Core Account and FNJXX $line");
        [$EAI, $EY] = $this->getEaiEy($lines[$i + 8]);
        $x = [
          'FNJXX',
          $lines[$i + 1],
          $lines[$i + 2],
          $lines[$i + 3],
          $lines[$i + 4],
          $lines[$i + 5],
          $lines[$i + 6],
          // $this->cleanMoney($lines[$i + 7]),
          $EAI,
          $EY
        ];
        $holdings['FNJXX'] = $this->procHoldings($x);
      } else if (self::$holdFlag == 'Mutual Funds' and preg_match('/\(FSJXX\)/', $line) > 0) {
        Log::debug("-CK-getIndIraHoldings Mutual Funds and FSJXX $line");
        // [$EAI, $EY] = $this->getEaiEy($lines[$i + 8]);
        $x = [
          'FSJXX',
          $this->cleanMoney($lines[$i + 5]), // begin value
          $this->cleanMoney($lines[$i + 6]), // quantity
          $this->cleanMoney($lines[$i + 7]), // price
          $this->cleanMoney($lines[$i + 8]), // end value
          // $lines[$i + 6], // cost
          // $lines[$i + 7], // unrealized gain/lose
          // // $this->cleanMoney($lines[$i + 8]) // EAI
          // $EAI,
          // $EY
          null, // $lines[$i + 9]  .' '. $lines[$i + 10], // cost
          null, // $lines[$i + 11] .' '. $lines[$i + 12], // unrealized gain/lose
          // $this->cleanMoney($lines[$i + 8]) // EAI
          $lines[$i + 9], // EAI,
          $lines[$i + 10]  // EY
        ];
        $holdings['FSJXX'] = $this->procHoldings($x);
      } else if (self::$holdFlag == 'Stocks' and preg_match('/\(T\)/', $line) > 0) {
        // Log::debug("-CK-getIndIraHoldings holdFlag", [self::$holdFlag]);
        Log::debug("-CK-getIndIraHoldings Stocks and (T) $line");
        // [$EAI, $EY] = $this->getEaiEy($lines[$i + 7]);
        $x = [
          'T',
          $lines[$i + 1], // begin value
          $lines[$i + 2], // quantity
          $lines[$i + 3], // price
          $lines[$i + 4], // end value
          $lines[$i + 5], // cost
          $lines[$i + 6], // unrealized gain/lose
          $lines[$i + 7], //$EAI,
          $lines[$i + 8] //$EY,
        ];
        $holdings['ATT'] = $this->procHoldings($x);
      } else if (self::$holdFlag == 'Stocks' and preg_match('/\(CHTR\)/', $line) > 0) {
        // [$EAI, $EY] = $this->getEaiEy($lines[$i + 7], 'CHTR');
        $x = [
          'CHTR',
          $lines[$i + 1], // begin value
          $lines[$i + 2], // quantity
          $lines[$i + 3], // price
          $lines[$i + 4], // end value
          $lines[$i + 5], // cost
          $lines[$i + 6], // unrealized gain/loss
          // $EAI,
          // $EY,
        ];
        $holdings['CHTR'] = $this->procHoldings($x);
      } else if (self::$holdFlag == 'Stocks' and preg_match('/\(CSCO\)/', $line) > 0) {
        // [$EAI, $EY] = $this->getEaiEy($lines[$i + 7]);
        $x = [
          'CSCO',
          $lines[$i + 1],
          $lines[$i + 2],
          $lines[$i + 3],
          $lines[$i + 4],
          $lines[$i + 5],
          $lines[$i + 6],
          $lines[$i + 7], // EAI,
          $lines[$i + 8] // EY,
        ];
        $holdings['CSCO'] = $this->procHoldings($x);
      } else if (self::$holdFlag == 'Stocks' and preg_match('/\(DELL\)/', $line)) {
        // [$EAI, $EY] = $this->getEaiEy($lines[$i + 7]);
        $x = [
          'DELL',
          $lines[$i + 1],
          $lines[$i + 2],
          $lines[$i + 3],
          $lines[$i + 4],
          $lines[$i + 5],
          $lines[$i + 6],
          $lines[$i + 7], //EAI,
          $lines[$i + 8] //EY,
        ];
        $holdings['DELL'] = $this->procHoldings($x);
      } else if (self::$holdFlag == 'Stocks' and preg_match('/\(MSFT\)/', $line)) {
        // [$EAI, $EY] = $this->getEaiEy($lines[$i + 7], 'MSFT');
        $x = [
          'MSFT',
          $lines[$i + 1],
          $lines[$i + 2],
          $lines[$i + 3],
          $lines[$i + 4],
          $lines[$i + 5],
          $lines[$i + 6],
          $lines[$i + 7], // EAI
          $lines[$i + 8], // EY
        ];
        $holdings['MSFT'] = $this->procHoldings($x);
      } else if (self::$holdFlag == 'Stocks' and preg_match('/\(VMW\)/', $line) > 0) {
        [$EAI, $EY] = $this->getEaiEy($lines[$i + 7], 'VMW');
        $x = [
          'VMW',
          $lines[$i + 1],
          $lines[$i + 2],
          $lines[$i + 3],
          $lines[$i + 4],
          $lines[$i + 5],
          $lines[$i + 6],
          $lines[$i + 7],
        ];
        $holdings['VMW'] = $this->procHoldings($x);
      } else if (self::$holdFlag == 'Stocks' and preg_match('/\(WBD\)/', $line) > 0) {
        $start_balance = $lines[$i + 1];
        if ($start_balance == 'unavailable') $start_balance = 0.0;
        [$EAI, $EY] = $this->getEaiEy($lines[$i + 7], 'WBD');
        $x = [
          'WBD',
          $start_balance, // begin value
          $lines[$i + 2],
          $lines[$i + 3],
          $lines[$i + 4],
          $lines[$i + 5],
          $lines[$i + 6],
          $lines[$i + 7],
        ];
        $holdings['WBD'] = $this->procHoldings($x);
      }
    }
    $data['holdingsInd'] = $holdings;
    return $data;
  }
  private function getIraHoldings($data, $lines, $start, $end) {
    $holdings = [];
    for ($i=$start; $i < $end; $i++) {
      $line = $lines[$i];
      $this->setHoldFlag($line);
      if (self::$holdFlag == 'Core Account' and preg_match('/\(FDRXX\)/', $line)) {
        // [$EAI, $EY] = $this->getEaiEy($lines[$i + 8], 'FDRXX');
        $x = [
          'FDRXX',
          $lines[$i + 5], // start balance
          $lines[$i + 6], // quantity
          $lines[$i + 7], // price
          $lines[$i + 8], // end balance
          $lines[$i + 9]  .' applicable', // cost
          $lines[$i + 11] .' applicable', // unrealized gail/loss
          $lines[$i + 13], //$EAI,
          $lines[$i + 14] //$EY,
        ];
        $holdings['FDRXX'] =  $this->procHoldings($x);
      } else if (self::$holdFlag == 'Mutual Funds' and preg_match('/\(FFTWX\)/', $line)) {
        $a = explode(' ', $line);
        $x = [
          'FFTWX',
          $a[4],
          $a[5],
          $a[6],
          $a[7],
          $a[8],
          $a[9],
          $a[10],
          $lines[$i + 1] // EY
        ];
        $holdings['FFTWX'] = $this->procHoldings($x);
      } else if (self::$holdFlag == 'Mutual Funds' and preg_match('/\(FSKAX\)/', $line)) {
        // [$EAI, $EY] = $this->getEaiEy($lines[$i + 7], 'FSKAX');
        $x = [
          'FSKAX',
          $lines[$i + 1],
          $lines[$i + 2],
          $lines[$i + 3],
          $lines[$i + 4],
          $lines[$i + 5],
          $lines[$i + 6],
          $lines[$i + 7], // EAI
          $lines[$i + 8], // EY
        ];
        $holdings['FSKAX'] = $this->procHoldings($x);
      } else if (self::$holdFlag == 'Mutual Funds' and preg_match('/\(FXAIX\)/', $line)) {
        // [$EAI, $EY] = $this->getEaiEy($lines[$i + 7], 'FXAIX');
        $x = [
          'FXAIX',
          $lines[$i + 1],
          $lines[$i + 2],
          $lines[$i + 3],
          $lines[$i + 4],
          $lines[$i + 5],
          $lines[$i + 6], // EAI
          $lines[$i + 7], //$EY,
          // $lines[$i + 8] //$EY,
        ];
        $holdings['FXAIX'] = $this->procHoldings($x);
      } else if (self::$holdFlag == 'Mutual Funds' and preg_match('/\(SPRXX\)/', $line)) {
        [$EAI, $EY] = $this->getEaiEy($lines[$i + 8], 'SPRXX');
        $x = [
          'SPRXX',
          $lines[$i + 2],
          $lines[$i + 3],
          $lines[$i + 4],
          $lines[$i + 5],
          $lines[$i + 6],
          $lines[$i + 7],
          $EAI,
          $EY,
        ];
        $holdings['SPRXX'] = $this->procHoldings($x);
      }
    }
    $data['holdings'] = $holdings;
    return $data;
  }
}
<?php
namespace App\Traits;
use Illuminate\Support\Facades\Log;

trait RothHoldingsTrait {
  private function getIndRothHoldings($data, $lines, $start, $end) { Log::info("-fn-getIndRothHoldings (X85-275143) start=$start end=$end");
    $holdings = [];
    for ($i=$start; $i < $end; $i++) {
      $line = $lines[$i];
      $this->setHoldFlag($line); // static variable (global variable)
      // if (self::$holdFlag == "Core Account" and preg_match('/\(QBNYQ\)/', $line)) {
      //   // $hFlag = self::$holdFlag; Log:info("-CK-holdFlat=$hFlag");
      //   [$EAI, $EY] = $this->getEaiEy($lines[$i + 6], 'CORE');
      //   $x = [
      //     'CORE',
      //     $lines[$i + 2], // beginning market value
      //     $lines[$i + 3], // quantity
      //     1, // price
      //     $lines[$i + 5], // ending market value
      //     'blank',
      //     'blank',
      //     $EAI,
      //     $EY,
      //   ];
      if (self::$holdFlag == "Core Account" and preg_match('/\(QBNYQ\)/', $line)) {
        // $hFlag = self::$holdFlag; Log:info("-CK-holdFlat=$hFlag");
        // [$EAI, $EY] = $this->getEaiEy($lines[$i + 6], 'CORE');
        $secs = 'CORE';
        // $x = explode(' ', $lines[$i + 6]);
        // $begval = null;
        // $quan = $x[1];
        // $price = $x[2];
        // $endval = $x[3];
        // $x = explode(' ', $lines[$i + 6]);
        $begval = $lines[$i + 6];
        $quan = $lines[$i + 7];
        $price = $lines[$i + 8];
        $endval = $lines[$i + 9];
        $EAI = '-';
        $EY = '-';
        $x = [
          $secs,
          $begval,
          $quan,
          $price,
          $endval,
          $EAI,
          $EY
        ];
        $holdings['CORE'] = $this->procHoldings($x);
      } else if (self::$holdFlag == "Mutual Funds" and preg_match('/\(FZDXX\)/', $line) > 0) {
        Log::debug("-CK-FZDXX", [self::$holdFlag, __file__, __line__]);
        // [$EAI, $EY] = $this->getEaiEy($lines[$i + 5], 'FZDXX');
        $bval = $lines[$i + 5];
        $quan = $lines[$i + 6];
        $pric = $lines[$i + 7];
        $eval = $lines[$i + 8];
        $EAI = $lines[$i + 9];
        $EY = $lines[$i + 10];
        $x = [
          'FZDXX',
          $bval,
          $quan,
          $pric,
          $eval,
          '-',
          '-',
          $EAI,
          $EY,
        ];
        $holdings['FZDXX'] = $this->procHoldings($x);
        // $holdings['FZDXX'] = $x;
      }
    }
    $data['holdingsInd'] = $holdings;
    return $data;
  }
  private function getRothHoldings($data, $lines, $start, $end) { Log::info("-fn-getRothHoldings (226-227936) start=$start end=$end");
    $holdings = [];
    for ($i=$start; $i < $end; $i++) {
      $line = $lines[$i];
      $this->setHoldFlag($line);
      if (self::$holdFlag == 'Core Account' and preg_match('/\(QPCBQ\)/', $line)) {
        // $hFlag = self::$holdFlag; Log::info("-CK-holdFlag=$hFlag");
        // [$EAI, $EY] = $this->getEaiEy($lines[$i + ]);
        $secs = 'QPCBQ';
        $bval = $lines[$i + 6];
        $quan = $lines[$i + 7];
        $pric = $lines[$i + 8];
        $eval = $lines[$i + 9];
        $cost = 'not applicable';
        $nrgl = 'not applicable';
        $EAI = '-';
        $EY = '-';
        $x = [
          $secs,
          $bval,
          $quan,
          $pric,
          $eval,
          $cost,
          $nrgl,
          $EAI,
          $EY,
        ];
        $holdings['CORE'] = $this->procHoldings($x);
      } else if (self::$holdFlag == 'Core Account' and preg_match('/\(QBYIQ\)/', $line)) {
        // [$EAI, $EY] = $this->getEaiEy($lines[$i + 8]);
        $secs = 'QBYIQ';
        $bval = $lines[$i + 6];
        $quan = $lines[$i + 7];
        $pric = $lines[$i + 8];
        $eval = $lines[$i + 9];
        $cost = 'not applicable';
        $nrgl = 'not applicable';
        $EAI = '-';
        $EY = '-';
        $x = [
          $secs,
          $bval,
          $quan,
          $pric,
          $eval,
          $cost,
          $nrgl,
          $EAI,
          $EY,
        ];
        $holdings['QBYIQ'] = $this->procHoldings($x);
      } else if (self::$holdFlag == "Mutual Funds" and preg_match('/\(FSKAX\)/', $line)) {
        // [$EAI, $EY] = $this->getEaiEy($lines[$i + 7]);
        // $hFlag = self::$holdFlag; Log::info("-CK-holdFlag=$hFlag");
        $secs = 'FSKAX';
        $bval = $lines[$i + 1];
        $quan = $lines[$i + 2];
        $pric = $lines[$i + 3];
        $eval = $lines[$i + 4];
        $cost = $lines[$i + 5];
        $nrgl = $lines[$i + 6];
        $EAI = $lines[$i + 7];
        $EY = $lines[$i + 8];
        $x = [
          $secs,
          $bval,
          $quan,
          $pric,
          $eval,
          $cost,
          $nrgl,
          $EAI,
          $EY,
        ];
        $holdings['FSKAX'] = $this->procHoldings($x);
      } else if (self::$holdFlag == "Stocks" and preg_match('/\(BEKE\)/', $line)) {
        // $hFlag = self::$holdFlag; Log::info("-CK-holdFlag=$hFlag");
        // [$EAI, $EY] = $this->getEaiEy($lines[$i + 7]);
        $secs = 'BEKE';
        $bval = $lines[$i + 1];
        $quan = $lines[$i + 2];
        $pric = $lines[$i + 3];
        $eval = $lines[$i + 4];
        $cost = $lines[$i + 5];
        $nrgl = $lines[$i + 6];
        $EAI = '-';
        $EY = '-';
        $x = [
          $secs,
          $bval,
          $quan,
          $pric,
          $eval,
          $cost,
          $nrgl,
          $EAI,
          $EY,
        ];
        $holdings['BEKE'] = $this->procHoldings($x);
      }
    }
    $data['holdings'] = $holdings;
    return $data;
  }
}
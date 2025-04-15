<?php
namespace App\Traits;
use Illuminate\Support\Facades\Log;

trait IraActivityTrait {
  private function getIndIraActivity($data, $lines, $start, $end) { Log::info("-fn-getIndIraActivity Start_line=$start END_line=$end");
    $activity = [];
    for ($i=$start; $i<$end; $i++) {
      $line = $lines[$i];
      $this->setActvFlag($line);
      if (self::$actvFlag == 'Withdrawals' and preg_match('/^\d\d\/\d\d$/', $line)) {
        $date = $line;
        $secs = 'ToBeD';
        $desc = $lines[$i + 1];
        if (preg_match('/DEBIT\s+CARDMEMBER\s+SER\s+WEB\s+PYMT/i', $desc)) $secs = 'CCardP';  // used to Withdrw
        $quan = '-';
        $pric = '-';
        $amnt = $this->cleanMoney($lines[$i + 2]);
        Log::info("-CK-Withdrawals date=$date secs=$secs desc=$desc amnt=$amnt", [self::$actvFlag, __line__, __file__]);
        $x = [
          $date,
          $secs,
          $desc,
          $quan,
          $pric,
          $amnt
        ];
        $activity[] = $x;
      } else if (self::$actvFlag == 'Dividends Int' and preg_match('/^\d\d\/\d\d$/', $line)) {
        Log::debug("getIndIraActivity dd/dd line start=$start end=$end getIndIraActivity()", [self::$actvFlag]);
        // $security = $this->shortName($lines[$i + 1]);
        [$ni, $security] = $this->getSecurity($lines, $i);
        // $security = $lines[$i + 1];
        Log::debug("==== XXXXX getIndIraActivity ni=$ni security=$security", [self::$actvFlag]);
        if ($security == 'CISCO') {
          $x = [
            $line, // date
            $security, // security
            $lines[$i + 5] .' '. $lines[$i + 6],   // description
            $lines[$i + 7],   // quantity
            $lines[$i + 8],   // price
            $this->cleanMoney($lines[$i + 9]),  // amount
          ];
       
        } else if ($security == 'MSFT') {
          $x = [
            $line, // date
            $security, // security
            $lines[$ni + 1] .' '. $lines[$ni + 2],   // description
            $lines[$ni + 3],   // quantity
            $lines[$ni + 4],   // price
            $this->cleanMoney($lines[$ni + 5]),  // amount
          ];
        } else if ($security == 'SPAXX') { // "FIDELITY GOVERNMENT MONEY MARKET"
          $x = [
            $line, // date
            $security, // security
            $lines[$ni + 1] .' '. $lines[$ni + 2],   // description
            $lines[$ni + 3],   // quantity
            $lines[$ni + 4],   // price
            $this->cleanMoney($lines[$ni + 5]),  // amount
          ];
        } else if ($security == 'FSJXX') { // FIDELITY NJ MUNI MONEY MKT PREMIUM CLASS
          if ($lines[$ni + 1] == 'Dividend') {
            $x = [
              $line, // date
              $security, // security
              $lines[$ni + 1] .' '. $lines[$ni + 2],   // description
              $lines[$ni + 3],   // quantity
              $lines[$ni + 4],   // price
              $this->cleanMoney($lines[$ni + 5]),  // amount
            ];
          } else {
            $x = [
              $line, // date
              $security, // security
              $lines[$ni + 1],   // description
              $lines[$ni + 2],   // quantity
              $lines[$ni + 3],   // price
              $this->cleanMoney($lines[$ni + 4]),  // amount
            ];
          }
        } else if ($security == 'WBD') {
          $x = [
            $line, // date
            $security, // security
            $lines[$i + 4],   // description
            $lines[$i + 5],   // quantity
            $lines[$i + 6],   // price
            $this->cleanMoney($lines[$i + 7]),  // amount
          ];
        } else if ($security == 'AT&T') {
          $x = [
            $line, // date
            $security, // security
            $lines[$i + 6] . ' ' . $lines[$i + 7],   // description
            $lines[$i + 8],   // quantity
            $lines[$i + 9],   // price
            $this->cleanMoney($lines[$i + 10]),  // amount
          ];
        } else if ($security == 'DELL') {
          $x = [
            $line, // date
            $security, // security
            $lines[$i + 7] . ' ' . $lines[$i + 8],   // description
            $lines[$i + 9],   // quantity
            $lines[$i + 10],   // price
            $this->cleanMoney($lines[$i + 11]),  // amount
          ];
        } else {
          $x = [
            $line, // date
            $this->shortName($lines[$i + 1]), // security
            $lines[$i + 3],   // description
            $this->cleanMoney($lines[$i + 4]),   // quantity
            $this->cleanMoney($lines[$i + 5]),   // price
            $this->cleanMoney($lines[$i + 6]),  // amount
          ];
        }
        $activity[] = $x;
      } else if (self::$actvFlag == 'OtherActivityIn' and preg_match('/^\d\d\/\d\d$/', $line)) {
        // [$ni, $security] = $this->getSecurity($lines, $i);
        $security = $lines[$i + 1];
        if ($security == 'DELL') {
          $x = [
            $line, // date
            $security, // security
            $lines[$i + 11],   // description
            $lines[$i + 12],   // quantity
            $lines[$i + 13],   // price
            // $lines[$i + 14],   // cost
            $this->cleanMoney($lines[$i + 15]),  // amount
          ];
          Log::debug("======= Other Activity In ============= $i $ni $line $security", $x);
        }
        $activity[] = $x;  
      } else if (self::$actvFlag == 'OtherActivityOut' and preg_match('/^\d\d\/\d\d$/', $line)) {
        Log::debug("======= Other Activity Out ============= $i $ni $line $security");
        [$ni, $security] = $this->getSecurity($lines, $i);
        if ($security == 'DELL') {
          $x = [
            $line, // date
            $security, // security
            $lines[$i + 10],   // description
            $lines[$i + 11],   // quantity
            $lines[$i + 12],   // price
            // $lines[$i + 13],   // Cost
            $this->cleanMoney($lines[$i + 14]),  // amount
          ];
        }
        $activity[] = $x;  
      } else if (self::$actvFlag == 'Exchanges In' and preg_match('/^\d\d\/\d\d$/', $line)) {
        Log::info("Exchanges In dd/dd line $line", [$lines[$i+1]]);
        $desc = $lines[$i + 2];
        if ($desc == 'Transferred') {
          $desc = 'Transferred From ' . $lines[$i + 1];
          // $security = substr($lines[$i + 1], 0, 6);
          $security = 'TranFr';
        }
        $x = [
          $line,
          $security,
          // $this->shortName($lines[$i + 1]),
          $desc,
          $lines[$i + 4],
          $lines[$i + 5],
          $this->cleanMoney($lines[$i + 6]),
        ];
        $activity[] = $x;
      } else if (self::$actvFlag == 'Bill Payments' and preg_match('/^\d\d\/\d\d$/', $line)) {
        $symbol = $lines[$i + 1] .' '. $lines[$i + 2] .' '. $lines[$i + 3];
        Log::info("dd/dd Bill Payments symbol=[$symbol] line=[$line]");
        if (preg_match('/WINDSOR MEADOWS CA/', $symbol)) {
          $symbol = 'BillPay';
          $desc = 'WINDSOR MEADOWS CA ' . 'Shelley Cir';
          $amnt = $this->cleanMoney($lines[$i + 6]);
          $ytop = $this->cleanMoney($lines[$i + 7]);
        } else if (preg_match('/EAST WINDSOR M.U.A./', $symbol)) { //__tobe revised
          $symbol = 'BillPay';
          $desc = 'EAST WINDSOR Sewer & Water Bill';
          if ($lines[$i + 4] == 'P') {
            $amnt = $this->cleanMoney($lines[$i + 6]);
            $ytop = $this->cleanMoney($lines[$i + 7]);
          } else {
            $amnt = $this->cleanMoney($lines[$i + 3]);
            $ytop = $this->cleanMoney($lines[$i + 4]);
          }
        // } else if (preg_match('/TAX COLLECTOR EAST WIND/', $symbol)) { //__tobe revised
        } else if (preg_match('/TAX COLLECTOR EAST/', $symbol)) { //__tobe revised
          $symbol = 'PTaxPay';
          $desc = 'East Windsor Property Tax';
          $amnt = $this->cleanMoney($lines[$i + 6]);
          $ytop = $this->cleanMoney($lines[$i + 7]);
        }
        $x = [
          $line,
          $symbol,
          $desc,
          null,  // quatity
          null,  // price
          $amnt,
          $ytop
        ];
        $activity[] = $x;
      } else if (self::$actvFlag == 'Core Fund Activity' and preg_match('/^\d\d\/\d\d$/', $line)) {
        // Log::info("===C===actvFlat=Ind Ira Core Fund Activity dd/dd line $line", [$lines[$i+1], $lines[$i+2]]);
        if (preg_match('/^(Sold|Bought)/', $lines[$i + 3])) {
          $symbol = $this->shortName($lines[$i + 4]);
          $desc = $lines[$i + 1] .' '.$lines[$i + 2] .' '.$lines[$i + 3];
          $quantity = $this->cleanMoney($lines[$i + 6]);
          $price = $this->cleanMoney($lines[$i + 7]);
          $amount = $this->cleanMoney($lines[$i + 8]);
          $balance = $this->cleanMoney($lines[$i + 9]);
        } else {
          $symbol = $this->shortName($lines[$i + 3]);
          $desc = $lines[$i + 1] .' '.$lines[$i + 2];
          $quantity = $this->cleanMoney($lines[$i + 5]);
          $price = $this->cleanMoney($lines[$i + 6]);
          $amount = $this->cleanMoney($lines[$i + 7]);
          $balance = $this->cleanMoney($lines[$i + 8]);
        }
        $x = [
          $line,
          $symbol,
          $desc,
          $quantity,
          $price,
          $amount,
          $balance,
        ];
        $activity[] = $x;
      }
    }
    $data['activityInd'] = $activity;
    return $data;
  }
  private function getIraActivity($data, $lines, $start, $end) { Log::info("getIraActivity(data, lines, $start, $end)");
    $activity = [];
    for ($i=$start; $i<$end; $i++) {
      $line = $lines[$i];
      $this->setActvFlag($line);
      Log::debug("-CK-melformat date -> c e03/31 $line", [__line__, __file__]);
      if (self::$actvFlag == 'Securities BS' and preg_match('/(.*)\d\d\/\d\d(.*)/', $line)) {
        // $this->shortName($lines[$i + 1]),
        $date = preg_replace('/(.*)(\d\d\/\d\d)(.*)/', "$2", $line);
        [$ni, $security] = $this->getSecurity($lines, $i);
        Log::info("-CKxxxx Securities BS dd/dd line ni=$ni $line", [self::$actvFlag]);
        $desc = $lines[$ni + 1] .' '. $lines[$ni + 2]; // description
        // $quan = explode('$', $lines[$ni + 5])[0];
        // $pric = explode('$', $lines[$ni + 5])[1];
        // $amnt = $this->cleanMoney($lines[$ni + 7]);
        $quan = $this->cleanMoney($lines[$ni + 5]);
        $pric = $this->cleanMoney($lines[$ni + 6]);
        $cost = $this->cleanMoney($lines[$ni + 7]);
        $transaction_cost = $lines[$ni + 8];
        $amnt = $this->cleanMoney($lines[$ni + 9]);
        $x = [
          $date,
          $security,
          $desc,
          $quan,
          $pric,
          // $cost,
          $amnt,
        ];
        $activity[] = $x;
      } else if (self::$actvFlag == 'Dividends Int' and preg_match('/^\d\d\/\d\d$/', $line)) { //__TODO start here
        // what has been DONE: 
        // 1. headers for ira and roth
        // 2. ira Ind holdings and activities
        // 3. ira holdings and part of activities
        $date = $line;
        [$ni, $security] = $this->getSecurity($lines, $i);
        $desc = $lines[$ni + 1];
        if ($desc == 'Dividend') {
          $desc = $lines[$ni + 1] . ' ' . $lines[$ni + 2];
          $quan = $lines[$ni + 3];
          $pric = $lines[$ni + 4];
          $amnt = $this->cleanMoney($lines[$ni + 5]);
        } else if ($desc == 'Reinvestment') {
          $a = explode('$', $lines[$ni + 2]);
          if (count($a) == 2) {
            $quan = $a[0];
            $pric = array_pop($a);
            $amnt = $this->cleanMoney($lines[$ni + 3]);
          } else if (count($a) == 1) {
            // Log::info("-CK-Dividends Int dd/dd line $line ni=$ni $desc", [self::$actvFlag]); why quan = pric ????
            $quan = $lines[$ni + 2];
            $pric = $lines[$ni + 3];
            // $amnt = $this->cleanMoney($lines[$ni + 4]);
            $amnt = $this->cleanMoney($lines[$ni + 4]);
            Log::info("-CK-Dividends Int dd/dd line $line ni=$ni $date, $desc, $security, $quan, $pric, $amnt", [self::$actvFlag]);
          }
        } else if ($desc == 'Short-Term') {
          $desc.= $lines[$ni + 2] .' ' . $lines[$ni + 3];
          $quan = $lines[$ni + 4];
          $pric = $lines[$ni + 5];
          $amnt = $this->cleanMoney($lines[$ni + 6]);
        }
        $x = [
          $date,
          $security,
          $desc,
          $quan,
          $pric,
          $amnt,
        ];
        $activity[] = $x;
      } else if (self::$actvFlag == 'Contributions' and preg_match('/^\d\d\/\d\d$/', $line)) {
        // Log::info("ZZZ Contributions CCard dd/dd line $line");
        $desc = $lines[$i + 1];
        $security = $this->shortName($lines[$i + 1]);
        if (preg_match('/Deposit Elan Cardsvc Current Year Contrib/', $desc)) $security = 'CardRv';
        $x = [
          $line,    // sett_date
          $security,
          $desc,
          null,      // quantity
          null,      // price
          $this->cleanMoney($lines[$i + 2])  // amount
        ];
        $activity[] = $x;
      } else if (self::$actvFlag == 'Distributions' and preg_match('/^\d\d\/\d\d$/', $line)) {
        // Log::info("Distributions dd/dd line $line");
        $desc =  $lines[$i + 1];
        // $security = $lines[$i + 1];
        // if (preg_match('/PARTIAL DISTR NORMAL/', $desc)) $security = 'DISTR'; // used to be Withdrw
        if (preg_match('/^Normal/', $desc)) {
          $security = 'DISTR'; // used to be Withdrw
          $desc = 'Normal Distri Patial';
          $amnt = $this->cleanMoney($lines[$i + 5]);
        }
        $x = [
          $line,
          $security,
          $desc,
          null,
          null,
          $amnt
        ];
        $activity[] = $x;
      } else if (self::$actvFlag == 'Taxes Withheld' and preg_match('/^\d\d\/\d\d$/', $line)) {
        // Log::info("Taxes Withheld dd/dd line $line");
        // $desc =  $lines[$i + 1] .' '. $lines[$i + 2] .' '. $lines[$i + 3];
        $desc =  $lines[$i + 4];
        $security = $lines[$i + 1];
        if (preg_match('/Fed\s+Tax\s+W\/H/i', $desc)) $security = 'FTXWH';
        if (preg_match('/State\s+Tax\s+W\/H/i', $desc)) $security = 'STXWH';
        // Log::info("===== Activity desc $desc security $security");
        $x = [
          $line,
          $security,
          // $security = $this->shortName[$security] == $security ? $security : $this->shortName[$security],
          $desc,
          null,
          null,
          $this->cleanMoney($lines[$i + 5]),
        ];
        $activity[] = $x;
      } else if (self::$actvFlag == 'Core Fund Activity' and preg_match('/^\d\d\/\d\d$/', $line)) {
        // Log::info("XXXX in getAcctActivityIra() Core Fund dd/dd line $line");
        $security = $this->shortName($lines[$i + 4]);
        $desc =  $lines[$i + 1] .' '. $lines[$i + 2].' '. $lines[$i + 3];
        if (preg_match('/^CASH\s+You\s+(Sold|Bought)/', $desc)) {
          $date = $line;  // date
          $secs = $security;
          $quan = $this->cleanMoney($lines[$i + 6]); // quantity
          $pric = $this->cleanMoney($lines[$i + 7]); // price
          $amnt = $this->cleanMoney($lines[$i + 8]); // amount
          $balc = $this->cleanUplineJunk($this->cleanMoney($lines[$i + 9])); // cost(balance)
        } else if (preg_match('/^CASH\s+Reinvestment/', $desc)) {
          $date = $line;  // date
          $desc = $lines[$i + 1] .' '. $lines[$i + 2];
          $secs = $security = $this->shortName($lines[$i + 3]);
          $quan = $this->cleanMoney($lines[$i + 5]); // quantity
          $pric = $this->cleanMoney($lines[$i + 6]); // price
          $amnt = $this->cleanMoney($lines[$i + 7]); // amount
          $balc = $this->cleanUplineJunk($this->cleanMoney($lines[$i + 8])); // cost(balance)
        }
        $x = [
          $date,  // date
          $secs,
          $desc,
          $quan,
          $pric,
          $amnt,
          $balc,
        ];
        // log::info('activity x=', $x);
        $activity[] = $x;
      }
    }
    $data['activity'] = $activity;
    return $data;
  }
}
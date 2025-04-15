<?php
namespace App\Traits;
use Illuminate\Support\Facades\Log;

trait RothActivityTrait {
  private function getIndRothActivity($data, $lines, $start, $end) { Log::info("-fn-getIndRothActivity (X85-275143) start=$start end=$end");
    $activity = [];
    // Log::debug("-X- getAcctActivityRothInd Int dd/dd line $start $end");
    for ($i=$start; $i<$end; $i++) {
      $line = $lines[$i];
      $this->setActvFlag($line);
      if (self::$actvFlag == 'Securities BS' and preg_match('/^\d\d\/\d\d$/', $line)) {
        // Log::info("Roth Sec BS  dd/dd line $line");
        $date = $line;
        // $security = $this->shortName($lines[$i + 1]);
        [$ni, $secs] = $this->getSecurity($lines, $i);
        $desc = $lines[$ni + 1] .' '. $lines[$ni + 2];
        // if (preg_match('/You Sold/i', $desc)) $desc = $lines[$i + 2] .' '. $desc;
        $quan = $this->cleanMoney($lines[$ni + 3]);
        $pric = $lines[$ni + 4];
        // $cost = $lines[$ni + 5];
        $amnt = $this->cleanMoney($lines[$ni + 6]);

        $x = [
          $date,
          $secs,
          $desc,
          $quan,
          $pric,
          // $cost,
          $amnt,
        ];
        $activity[] = $x;
      } else if (self::$actvFlag == 'Dividends Int' and preg_match('/^\d\d\/\d\d$/', $line)) {
        $date = $line;
        [$ni, $secs] = $this->getSecurity($lines, $i);
        $desc = $lines[$ni + 1];
        Log::debug("-CK-actvFlag=Dividends Int date=$date desc=$desc i=$i ni=$ni secs=$secs", [self::$actvFlag, __line__, __file__]);
        if ($desc == 'Dividend') {
          $desc .= ' ' . $lines[$ni + 2];
          $quan = $this->cleanMoney($lines[$ni + 3]);
          $pric = $this->cleanMoney($lines[$ni + 4]);
          $amnt = $this->cleanMoney($lines[$ni + 5]);
        } else if ($desc == 'Reinvestment') {
          $desc = $lines[$ni + 1];
          $quan = $this->cleanMoney($lines[$ni + 2]);
          $pric = $this->cleanMoney($lines[$ni + 3]);
          $amnt = $this->cleanMoney($lines[$ni + 4]);
        } else if ($secs == 'Ints') {
          $desc = 'Interest Earned';
          $quan = '-';
          $pric = '-';
          $amnt = $this->cleanMoney($lines[$ni + 3]);
        }
        // if ($lines[$i + 8] == 'Deposits Date') continue;
        $x = [
          $date,
          $secs,
          $desc,
          $quan,
          $pric,
          $amnt,
        ];
        $activity[] = $x;
      } else if (self::$actvFlag == 'Deposits' and preg_match('/^\d\d\/\d\d$/', $line)) { //__ToBe_revised
        Log::debug("=x=actvFlag Deposits dd/dd line $line");
        $security = '-';
        $desc = $lines[$i + 1];
        $quantity = '-';
        $price = '-';
        $amount = $this->cleanMoney($lines[$i + 2]);
        // if ($lines[$i + 8] == 'Deposits Date') continue;
        $x = [
          $line,
          $security,
          $desc,
          $quantity,
          $price,
          $amount,
        ];
        $activity[] = $x;
      } else if (self::$actvFlag == 'Withdrawals' and preg_match('/^\d\d\/\d\d$/', $line)) {
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
      } else if (self::$actvFlag == 'Core Fund Activity' and preg_match('/^\d\d\/\d\d$/', $line)) { //__ToBe_revised
        // Log::info("======CORE FUND ACTI======= Roth Actv 'Core Fund Activity' dd/dd line $line", [ $lines[$i+1], $lines[$i+2] ]);
        // $security = 'Unkown';
        // $security = $this->shortName($lines[$i + 3]);
        $security = "CORE";
        $desc = $lines[$i + 1] .' '.$lines[$i + 2] .' '.$lines[$i + 3] .' '. $this->shortName($lines[$i + 4]);
        // if (preg_match('/DEBIT CARDMEMBER SER WEB PYMT/', $desc)) $security = 'CCardP';  // used to Withdraw
        $quantity = $this->cleanMoney($lines[$i + 6]);
        $price = $this->cleanMoney($lines[$i + 7]);
        $amount = $this->cleanMoney($lines[$i + 8]);
        $balance = $this->cleanMoney($lines[$i + 9]);
        $x = [
          $line,
          $security,
          $desc,
          $quantity,
          $price,
          $amount,
          $balance,
        ];
        $activity[] = $x;
      } else if (self::$actvFlag == 'Exchanges In' and preg_match('/^\d\d\/\d\d$/', $line)) { //__ToBe_revised
        // Log::info("=======EXCHANGES IN======= Roth Actv 'Exchanges In' dd/dd line $line");
        $security = 'Unkown';
        $desc = $lines[$i + 1];
        if (preg_match('/DEBIT CARDMEMBER SER WEB PYMT/', $desc)) {
          $security = 'CCardP';  // used to Withdrw
          $quantity = '-';
          $price = '-';
          $amount = $this->cleanMoney($lines[$i + 2]);
        } else if (preg_match('/Z71-/', $desc)) {
          $security = 'FromTo'; 
          $desc = 'Transferred From ' . $desc;
          $quan = '-';
          $price = '-';
          $amount = $this->cleanMoney($lines[$i + 6]);
        }
        $x = [
          $line,
          $security,
          $desc,
          $quan,
          $price,
          $amount
        ];
        $activity[] = $x;
      } else if (self::$actvFlag == 'Exchanges Out' and preg_match('/^\d\d\/\d\d$/', $line)) { //__ToBe_revised
        // Log::info("=======EXCHANGES OUT======= Roth Actv 'Exchanges Out' dd/dd line $line");
        $security = 'ToFrom';
        // $security = $lines[$i + 1];
        $desc = $lines[$i + 2];
        if (preg_match('/Transferred To/', $desc)) {
          $desc .= ' ' . $lines[$i + 1];
          // $security = substr($security, 0, 6);
          $security = 'TranTo';
        }
        $quantity = '-';
        $price = '-';
        $amount = $this->cleanMoney($lines[$i + 5]);
        $x = [
          $line,
          $security,
          $desc,
          $quantity,
          $price,
          $amount
        ];
        $activity[] = $x;
      } else if (self::$actvFlag == 'Fees and Charges' and preg_match('/^\d\d\/\d\d$/', $line)) { //__ToBe_revised
        Log::debug("Ind Roth Actv Fees and Charges dd/dd line:[$line]");
        $securityName = $lines[$i + 1];
        $security = 'AtmFR';
        if ($securityName == 'Atm Fee Rebate') {
          $desc = $securityName;
          $quantity = null;
          $price = null;
          $amount = $this->cleanMoney($lines[$i + 2]);
        } else if ($securityName == 'Ke' or $securityName == 'KE') {
          $security = 'BEKE';
          $desc = 'Fees and Charges';
          $quantity = null;
          $price = null;
          $amount = $this->cleanMoney($lines[$i + 10]);
        }
        $x = [
          $line,
          $security,
          $desc,
          $quantity,
          $price,
          $amount,
        ];
        $activity[] = $x;
      } else if (self::$actvFlag == 'Debit Card Activity' and preg_match('/^\d\d\/\d\d$/', $line)) { //__ToBe_revised
        Log::debug("Ind Roth Actv Activity Debit Card Summary dd/dd line:[$line]");
        $desc = 'Cash withdraw at ' . $lines[$i + 1];
        $security = 'CashAdv';
        $quantity = null;
        $price = null;
        $amount = $this->cleanMoney($lines[$i + 2]);
        $x = [
          $line,
          $security,
          $desc,
          $quantity,
          $price,
          $amount,
        ];
        $activity[] = $x;
      }
    }
    $data['activityInd'] = $activity;
    return $data;
  }
  private function getRothActivity($data, $lines, $start, $end) { Log::info("-fn-getRothActivity (226-227936) start=$start end=$end");
    $activity = [];
    for ($i=$start; $i<$end; $i++) {
      $line = $lines[$i];
      $this->setActvFlag($line);
      // if (preg_match('/Fees/', self::$actvFlag)) Log::debug("=CK=actvFlag=" . self::$actvFlag);
      // if (self::$actvFlag == "Fees and Charges") Log::debug("=CK=actvFlag=" . self::$actvFlag . $line);
      if (self::$actvFlag == 'Dividends Int' and preg_match('/^\d\d\/\d\d$/', $line)) {
        // Log::info("Roth Actv Dividends Int dd/dd line $line");
        [$ni, $secs] = $this->getSecurity($lines, $i);
        // $security = $this->shortName($securityName);
        $date = $line;
        $desc = $lines[$ni + 1];
        $quan = $lines[$ni + 2];
        if ($desc == 'Reinvestment') {
          if (preg_match('/$/', $quan)) {
            // if (preg_match('/^[+-]?\d+(\.\d+)?$/', $quan) !== 1) { // check if contains something other than numbers
              Log::debug("-CK-str_contains $ start=$start quan=$quan", [__line__, __file__]);
            $q = explode('$', $quan);
            if (count($q) == 2) {
              $quan = $q[0];
              $pric = array_pop($q);
              $amnt = $this->cleanMoney($lines[$ni + 3]);
            } else {
              $pric = $lines[$ni + 3];
              $amnt = $this->cleanMoney($lines[$ni + 4]);
            }
          }
        } else if ($desc == 'Dividend' && $lines[$ni + 2] == 'Received') {
          $desc .= ' Received';
          $quan = '-';
          $pric = '-';
          $amnt = $this->cleanMoney($lines[$ni + 5]);
        } else {
          $desc = substr($lines[$ni], 9);  // 'somecusip Interest Earned'
          $quan = $lines[$ni + 1];
          $pric = $lines[$ni + 2];
          $amnt = $this->cleanMoney($lines[$ni + 3]);
        }
        $x = [
          $date,
          $secs,
          $desc,
          $quan,
          $pric,
          $amnt,
        ];
        $activity[] = $x;
        // if (preg_match('/FDIC INSURED DEPOSIT/', $securityName)) $desc .= ' ' . $securityName;
        // $quantity = $lines[$i + 4];
        // $price = $lines[$i + 5];
        // $amount = $this->cleanMoney($lines[$i + 6]);
        // $x = [
        //   $line,
        //   $security,
        //   $desc,
        //   $quantity,
        //   $price,
        //   $amount,
        // ];
        // $activity[] = $x;
      } else if (self::$actvFlag == 'Fees and Charges' and preg_match('/^\d\d\/\d\d$/', $line)) { //__ToBe_revised
        Log::debug("=X=Roth Ind Actv Fees and Charges Int dd/dd line $line");
        $securityName = $lines[$i + 1];
        $security = $this->shortName($securityName);
        if ($security == 'BEKE') {
          $desc = 'Ke Hold Ads Fees and Charges';
          $quantity = null;
          $price = null;
          // $amount = $this->cleanMoney(str_replace('Total', '', $lines[$i + 2]));
          $amount = $this->cleanMoney($lines[$i + 10]);
        } else {
          $desc = $lines[$i + 3];
          $quantity = $lines[$i + 4];
          $price = $lines[$i + 5];
          $amount = $this->cleanMoney($lines[$i + 6]);
        }
        if (preg_match('/FDIC INSURED DEPOSIT/', $securityName)) $desc .= ' ' . $securityName; //__ToBe_revised
        $x = [
          $line,
          $security,
          $desc,
          $quantity,
          $price,
          $amount,
        ];
        $activity[] = $x;
      } else if (self::$actvFlag == 'Core Fund Activity' and preg_match('/^\d\d\/\d\d$/', $line)) {
        // Log::info("Roth Actv Core Fund Activity dd/dd line $line");
        // $secs = "CORE";
        $date = $line;
        $secs = $this->shortName($lines[$i + 4]);
        $desc = $lines[$i + 1] .' '. $lines[$i + 2] .' '. $lines[$i + 3];
        $quan = $this->cleanMoney($lines[$i + 6]);
        $pric = $this->cleanMoney($lines[$i + 7]);
        $amnt = $this->cleanMoney($lines[$i + 8]);
        $balc = $this->cleanMoney($lines[$i + 9]);
        $x = [
          $date,
          $secs,
          $desc,
          $quan,
          $pric,
          $amnt,
          $balc,
        ];
        $activity[] = $x;
      }
    }
    $data['activity'] = $activity;
    return $data;
  }
}
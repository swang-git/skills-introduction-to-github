<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use Spatie\PdfToText\Pdf;

use App\Models\expense\Spend;
use App\Models\bankstatement\BankStatementAsset;
// use App\Models\bankstatement\BankStatementHolding;
use App\Models\bankstatement\BankStatementNote;
// use App\Models\bankstatement\BankStatementActivity;
use App\Models\bankstatement\BankAccountActivity;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

use App\Traits\PDFTrait;
use App\Traits\MyTraits;

class StatementsBOAController extends Controller {
	public function __construct() {	}

  use PDFTrait;
  use MyTraits;

  public function addNotes(Request $da) { Log::info('BOA addNotes', $da->toArray());
    $userId = Auth::user()->id;
    foreach($da->toArray() as $d) {
      $dm = new BankStatementNote($d);
      $dm->upsert(
        ['user_id'=>$userId, 'bank'=>$dm->bank, 'year'=>$dm->year, 'month'=>$dm->month, 'note_id'=>$dm->note_id, 'notes'=>$dm->notes, 'amount'=>$dm->amount],
        ['user_id', 'bank', 'year', 'month', 'note_id'],
        ['notes', 'amount']
      );
    }
    return [ 'status' => "OK" ];
  }
  // public function addNotes(Request $da) { Log::info('BOA addNotes', $da->toArray());
  //   $userId = Auth::user()->id;
  //   foreach($da->toArray() as $d) {
  //     $dm = new BankStatementNote($d);
  //     $dm->user_id = $userId;
  //     $dm->save();
  //   }
  //   return [ 'status' => "OK" ];
  // }
  // public function addActivity(Request $da) { Log::info('addActivity', [__line__, __file__, $da->toArray()]);
  //   $userId = Auth::user()->id;
  //   foreach($da->toArray() as $d) {
  //     $dm = new BankAccountActivity($d);
  //     $dm->user_id = $userId;
  //     $dm->save();

  //   }
  //   return [ 'status' => "OK" ];
  // }
  public function addActivity(Request $da) { //Log::info('addActivity', [__line__, __file__, $da->toArray()]);
    $userId = Auth::user()->id;
    foreach($da->toArray() as $d) {
      // $dm = new BankAccountActivity($d);
      Log::info('addActivity', [__line__, __file__, $d]);
      $vals = array_merge(['user_id' => $userId], $d);
      BankAccountActivity::upsert(
        $vals,
        uniqueBy: ['bank', 'year', 'month', 'account_num', 'tran_num'],
        update:['acct_type', 'tran_date', 'description', 'begin_balance', 'amount', 'end_balance'],
      );
    }
    return [ 'status' => "OK" ];
  }
  private function getAssets($lines) { // Log::info("clines", $clines);
    $assets = [];
    for ($i=0; $i<count($lines); $i++) {
      $line = $lines[$i];
      if ($line == 'Your combined statement') {
        $x = preg_split('/ to /', $lines[$i + 1]);
        $begin_date = preg_replace('/for /', '', $x[0]);
        Log::debug("begin_date=$begin_date", $x);
        // exit(0);
        $assets['begin_date'] = $begin_date;
        $assets['end_date'] = $x[1];
      } else if (preg_match('/^Adv\s+Tiered\s+Interest\s+Chkg/', $line)) {
        // $assets['primary_account'] = preg_replace('/(.*)\s+(\d+)\s+(\d+)\s+(\d+)\s+(.*)/', "$2 $3 $4", $line);
        $assets['primary_account'] = trim($lines[$i + 1]);
      }
      // Log::debug("-AA-CK-getAssets $i", $assets);
    }
    return $assets;
  }
  // private function getSection($line, $section) {
  //   $line = trim($line);
  //   $sec_map = [
  //     // 'Preferred Rewards Gold' => 'Gold',
  //     'Adv Tiered Interest Chkg' => 'Interest Chkg',
  //     'Your Money Market Savings' => 'Money Market Savings',
  //     'This page intentionally left blank' => 'END'
  //   ];
  //   if (isset($sec_map[$line])) return $sec_map[$line];
  //   return $section;
  // }
  private function getSection($line, $section) {
    $line = trim($line);
    $sec_map = [
      // 'Preferred Rewards Gold' => 'Gold',
      'Your Adv Tiered Interest Chkg' => 'Interest Chkg',
      'Your Money Market Savings' => 'Money Market Savings',
      'This page intentionally left blank' => 'END'
    ];
    if (isset($sec_map[$line])) return $sec_map[$line];
    return $section;
  }
  private function getCheckingData($lines) { Log::info("-fn-getCheckingData");
    $chk = $this->getData1($lines);
    $sav = $this->getData2($lines);
    $da = ['chk' => $chk, 'sav' => $sav];
    // Log::info("dachksav", $da['chk']);
    return $da;
  }
  private function getData1($lines) {
    $act = [];
    $nos = [];
    $chk =['act' => $act, 'nos' => $nos];
    $section = false;
    for ($i=0; $i<count($lines); $i++) {
      $line = $lines[$i];
      $section = $this->getSection($line, $section);
      if ($section == 'Interest Chkg' and preg_match('/^Beginning balance on\s+/i', $line)) {
        // $chk['begin_balance'] = $this->cleanMoney(preg_replace('/(.*),\s+\d{4}\s+(.*)/', "$2", $line));
        $chk['begin_balance'] = $this->cleanMoney($lines[$i + 1]);
      } else if ($section == 'Interest Chkg' and preg_match('/^Ending balance on\s+/i', $line)) {
        // $chk['end_balance'] = $this->cleanMoney(preg_replace('/(.*),\s+\d{4}\s+(.*)/', "$2", $line));
        $chk['end_balance'] = $this->cleanMoney($lines[$i + 1]);
      } else if ($section == 'Interest Chkg' and preg_match('/^Ending balance on\s+/i', $line)) {
        $chk['ebal'] = $this->cleanMoney($lines[$i + 1]);
      } else if ($section == 'Interest Chkg' and preg_match('/^\d\d\/\d\d\/\d\d/', $line)) {
        // $patt = '/(^\d\d\/\d\d\/\d\d)\s+(.*)\s+([-]?\d{1,}.\d\d)/';
        $patt = '/(^\d\d\/\d\d\/\d\d)\s+(.*)/';
        $date = preg_replace($patt, "$1", $line);
        // $date = substr($date, 0, 8);
        // $desc = preg_replace($patt, "$2", $line);
        $desc = preg_replace($patt, "$2", $line);
        $amnt = $lines[$i + 1];
        if (!is_numeric($amnt)) $amnt = $lines[$i + 2];
        Log::debug("date=$date, desc=$desc, amnt=$amnt", [__line__, __file__]);
        if (!is_numeric($amnt)) {
          Log::debug("=C-CK amnt=$amnt at line $i is not a number, exit...", [__line__, __file__]);
          // exit(-100);
        }
        $act[] = [$date, $desc, $amnt];
        Log::debug("-CK-date=$date desc=$desc amnt=$amnt at line $i", [__line__, __file__]);
      } else if ($section == 'Interest Chkg' and preg_match('/^Annual\s+Percentage\s+(.*):\s+(\d{1,}.\d\d%)(.*)/', $line)) {
        $note = preg_replace('/(.*):\s+(\d{1,}.\d\d)(.*)/', "$1", $line);
        $amnt = preg_replace('/(.*):\s+(\d{1,}.\d\d)(.*)/', "$2", $line);
        if (!is_numeric($amnt)) {
          Log::debug("-D-CK-amnt=$amnt at line $i is not a number, exit...", [__line__, __file__]);
          // exit(-100);
        }
        $nos[] = [$note, $amnt];
      } else if ($section == 'Interest Chkg' and preg_match('/^Interest Paid\s+(.*):\s+[\$]?(\d{1,}.\d\d)(.*)/', $line)) {
        $note = preg_replace('/^(.*):\s+[\$]?(\d{1,}.\d\d)(.*)/', "$1", $line);
        $amnt = preg_replace('/^(.*):\s+[\$]?(\d{1,}.\d\d)(.*)/', "$2", $line);
        $nos[] = [$note, $amnt];
      } else if ($section == 'Money Market Savings') {
        $chk['nos'] = $nos;
        $chk['act'] = $act;
        // Log::debug("-CK-amnt=$amnt at line $i ", [__line__, $chk]);
        // Log::debug("-DG-amnt=$amnt at line $i is not a number, exit...", [__line__, __file__]);
        return $chk;
      }
    }
  }
  private function getData2($lines) { // no activities in the account
    // $sav =['begin_balance' => 0, 'end_balance' => 0];
    $section = false;
    for ($i=0; $i<count($lines); $i++) {
      $line = $lines[$i];
      $section = $this->getSection($line, $section);
      // Log::info("getData2 section: $section");
      if ($section == 'Money Market Savings' and preg_match('/^Your\s+Money\s+Market\s+Savings$/', $line)) {
        $sav['account'] = preg_replace('/Account\s+number:\s+(\d+)\s+(\d+)\s+(\d+)$/', "$1 $2 $3", $lines[$i - 2]);
      } else if ($section == 'Money Market Savings' and preg_match('/^Beginning balance on\s+/i', $line)) {
        $sav['begin_balance'] = $this->cleanMoney($lines[$i + 1]);
      } else if ($section == 'Money Market Savings' and preg_match('/^Ending balance on\s+/i', $line)) {
        // $sav['end_balance'] = $this->cleanMoney(preg_replace('/(.*),\s+\d{4}\s+(.*)/', "$2", $line));
        $sav['end_balance'] = $this->cleanMoney($lines[$i + 1]);
        // Log::info("getData2 section: $section", $sav);
        if (isset($sav['account']) and $sav['begin_balance'] > 0 and $sav['end_balance'] > 0) {
          Log::info("-ck-getData2 section: $section, sav data=", $sav);
          return $sav;
        }
      } else if ($section == 'END') {
        // $sav['act'] = $act;
        return $sav;
      }
    }
  }
  // private function getData2($lines) { // no activities in the account
  //   $sav =[];
  //   $section = false;
  //   for ($i=0; $i<count($lines); $i++) {
  //     $line = $lines[$i];
  //     $section = $this->getSection($line, $section);
  //     // Log::info("getData2 section: $section");
  //     if (preg_match('/^Money\s+Market\s+Savings\s+/', $line)) {
  //       $sav['account'] = preg_replace('/(.*)\s+(\d+)\s+(\d+)\s+(\d+)\s+(.*)/', "$2 $3 $4", $line);
  //     } else if ($section == 'Money Market Savings' and preg_match('/^Beginning balance on\s+/i', $line)) {
  //       $sav['begin_balance'] = $this->cleanMoney(preg_replace('/(.*),\s+\d{4}\s+(.*)/', "$2", $line));
  //     } else if ($section == 'Money Market Savings' and preg_match('/^Ending balance on\s+/i', $line)) {
  //       $sav['end_balance'] = $this->cleanMoney(preg_replace('/(.*),\s+\d{4}\s+(.*)/', "$2", $line));
  //       // Log::info("getData2 section: $section", $sav);
  //       if (isset($sav['account']) and $sav['begin_balance'] > 0 and $sav['end_balance'] > 0) {
  //         Log::info("getData2 section: $section, get data, return", $sav);
  //         return $sav;
  //       }
  //     // } else if ($section == 'Money Market Savings' and preg_match('/^\d\d\/\d\d\/\d\d/', $line)) {
  //     //   $patt = '/(^\d\d\/\d\d\/\d\d)\s+(.*)\s+([-]?\d{1,}.\d\d)/';
  //     //   $date = preg_replace($patt, "$1", $line);
  //     //   $desc = preg_replace($patt, "$2", $line);
  //     //   $amnt = preg_replace($patt, "$3", $line);
  //     //   if (!is_numeric($amnt)) {
  //     //     Log::debug("$amnt at line $i is not a number, exit...", [__line__, __file__]);
  //     //     exit(-100);
  //     //   }
  //     //   $act[] = [$date, $desc, $amnt];
  //     } else if ($section == 'END') {
  //       // $sav['act'] = $act;
  //       return $sav;
  //     }
  //   }
  // }
  private function getSavingsData($lines) {
    $act = [];
    $nos = [];
    $savinfo =['act' => $act, 'nos' => $nos];
    $section = false;
    for ($i=0; $i<count($lines); $i++) {
      $line = $lines[$i];
      $section = $this->getSection($line, $section);
      if ($section == 'Money Market Savings' and preg_match('/Account Number:/i', $line)) {
        $savinfo['account'] = preg_replace('/^Account number:\s+(.*)/i', "$1", $line);
      } else if ($section == 'Money Market Savings' and preg_match('/^Beginning balance on\s+/i', $line)) {
        // $savinfo['begin_balance'] = $this->cleanMoney(preg_replace('/(.*),\s+\d{4}\s+(.*)/', "$2", $line));
        $savinfo['begin_balance'] = $this->cleanMoney($lines[$i + 1]);
      } else if ($section == 'Money Market Savings' and preg_match('/^Ending balance on\s+/i', $line)) {
        // $savinfo['end_balance'] = $this->cleanMoney(preg_replace('/(.*),\s+\d{4}\s+(.*)/', "$2", $line));
        $savinfo['end_balance'] = $this->cleanMoney($lines[$i + 1]);
      // } else if ($section == 'Money Market Savings' and preg_match('/^Ending balance on\s+/i', $line)) {
      //   $savinfo['ebal'] = $lines[$i + 1];
        // $act[] = [$date, $desc, $amnt];
      } else if ($section == 'Money Market Savings' and preg_match('/^\d\d\/\d\d\/\d\d\s+(.*)/', $line)) {
        // // $patt = '/(^\d\d\/\d\d\/\d\d)\s+(.*)\s+([-]?\d{1,}.\d\d)/';
        // $patt = '/(^\d\d\/\d\d\/\d\d)\s+(.*)/';
        // $date = preg_replace($patt, "$1", $line);
        // // $date = substr($date, 0, 8);
        // // $desc = preg_replace($patt, "$2", $line);
        // $desc = preg_replace($patt, "$2", $line);
        // $amnt = $lines[$i + 1];
        // Log::debug("=A=CK-savings act:date=$date desc=$desc amnt=$amnt at line $i", [__line__, __file__]);
        // if (!is_numeric($amnt)) {
        //   Log::debug("=D=CK amnt=$amnt at line $i is not a number, exit...", [__line__, __file__]);
        //   exit(-100);
        // }
        $x = $this->get_date_desc_amnt_from_line($line);
        if (is_null($x)) {
          Log::debug("=A=CK-savings not date=$date at line $i exit....", [__line__, __file__]);
          // exit(-100);
        }
        [$date, $desc, $amnt] = $x;
        if (is_null($amnt)) $amnt = $lines[$i + 1];
        if (is_null($amnt) or !$this->check_money($amnt)) $amnt = $lines[$i + 2];
        $act[] = [$date, $desc, $amnt];
      } else if ($section == 'Money Market Savings' and preg_match('/^Annual Percentage\s+(.*):\s+(\d{1,}.\d\d%)(.*)/', $line)) {
        $note = preg_replace('/(.*):\s+(\d{1,}.\d\d)(.*)/', "$1", $line);
        $amnt = preg_replace('/(.*):\s+(\d{1,}.\d\d)(.*)/', "$2", $line);
        if (!is_numeric($amnt)) {
          Log::debug("=B=CK amnt=$amnt at line $i is not a number, exit...", [__line__, __file__]);
          // exit(-100);
        }
        $nos[] = [$note, $amnt];
      } else if ($section == 'Money Market Savings' and preg_match('/^Interest Paid\s+(.*):\s+[\$]?(\d{1,}.\d\d)(.*)/', $line)) {
        $note = preg_replace('/^(.*):\s+[\$]?(\d{1,}.\d\d)(.*)/', "$1", $line);
        $amnt = preg_replace('/^(.*):\s+[\$]?(\d{1,}.\d\d)(.*)/', "$2", $line);
        $nos[] = [$note, $amnt];
        // } else if ($section == 'END') {
          Log::info("-CK-savinfo:", [__line__, __file__]); Log::info("-CK-savinfo:", $savinfo);
        // }
      }
    }
    $savinfo['act'] = $act;
    $savinfo['nos'] = $nos;
    return $savinfo;
  }
  public function loadMonthlyStatements($ymon) { Log:info("loadMonthlyStatements $ymon");
    $docRoot = "/sites/webdata/docs/BOA/";

    $clines = $this->parsePDF($docRoot . "${ymon}_checking.pdf");
    if (is_string($clines)) return ['info' => $clines, 'status' => 'NO_FILE'];

    $filename = "BOA_monthly_statement_$ymon" . '_checking';
    $this->writeToTempFile($filename, $clines);
    $assets = $this->getAssets($clines);   // partially
    $chkinfo = $this->getCheckingData($clines);
    
    $slines = $this->parsePDF($docRoot . "${ymon}_savings.pdf");
    $filename = "BOA_monthly_statement_$ymon" . '_savings';
    $this->writeToTempFile($filename, $slines);
    $savinfo = $this->getSavingsData($slines);
    // $savinfo = ['begin_balance' => 0, 'end_balance' => 0];
    $retval = ['assets' => $assets, 'chkinfo' => $chkinfo, 'savinfo' => $savinfo, 'status' => "OK"];
    Log::debug("===XXXX=== return:", $retval);
    // return ['assets' => $assets, 'chkinfo' => $chkinfo, 'savinfo' => $savinfo, 'status' => "OK"];
    return $retval;
  }
  private function XXgetSectionLineNums($lines)  {
    $hline = [];
    $aline = [];
    for ($i=0; $i<count($lines); $i++) {
      $line = $lines[$i];
      if (preg_match('/^Holdings/', $line)) $hline[] = $i;
      else if (preg_match('/^Activity/', $line)) $aline[] = $i;
    }
    return [$hline, $aline];
  }
  private function procHoldings($holdings) {
    $x = []; $i = 0;
    foreach($holdings as $e) {
      if ($i++ < 2 ) {
        $x[] = preg_replace('/[$|,]/', '', $e);
        continue;
      }
      if ($e == 'unknown' or $e == 'not applicable' or $e == 'blank') $e = null;
      else $e = floatval(preg_replace('/[$|,]/', '', $e));
      $x[] = $e;
    }
    return $x;
  }
  // private function parsePDF($statementFile) { // Log::info("parseStatements File: $statementFile");
  //   $text = (new Pdf())->setPdf($statementFile)->setOptions(['layout', 'r 96'])->text();
  //   $x = preg_split('/\n/', $text);
  //   $lines = [];
  //   foreach ($x as $line) {
  //     if (ctype_print($line)) $lines[] = preg_replace('/\s+/', ' ', $line);
  //   }
  //   return $lines;
  // }
  // private function writeToTempFile($filename, $lines) {
  //   $ccfile = "/sites/tmp/$filename";
  //   $fp = fopen($ccfile, 'w');
  //   $i = 0;
  //   foreach($lines as $line) {
  //       $i++;
  //       fwrite($fp, "$i, [$line]\n"); // only print printable line
  //   }
  //   fclose($fp);
  // }
}

<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
// use Spatie\PdfToText\Pdf;
use App\Models\bankstatement\BankStatementAsset;
use App\Models\bankstatement\BankStatementNote;
use App\Models\bankstatement\BankAccountActivity;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use App\Traits\PDFTrait;

class StatementChaseController extends Controller {
	public function __construct() {	}

  use PDFTrait;

  // public function addNotes(Request $da) { Log::info('addNotes', $da->toArray()); // use addNotes in StatementBOAController
  //   $userId = Auth::user()->id;
  //   foreach($da->toArray() as $d) {
  //     $dm = new BankStatementNote($d);
  //     $dm->user_id = $userId;
  //     $dm->save();
  //   }
  //   return [ 'status' => "OK" ];
  // }
  public function addActivity(Request $da) { Log::info('addActivity', $da->toArray());
    $userId = Auth::user()->id;
    foreach($da->toArray() as $d) {
      $dm = new BankAccountActivity($d);
      $dm->upsert(
        ['user_id'=>$userId, 'bank'=>$dm->bank, 'year'=>$dm->year, 'month'=>$dm->month, 'account_num'=>$dm->account_num,
          'acct_type'=>$dm->acct_type, 'tran_num'=>$dm->tran_num, 'tran_date'=>$dm->tran_date,
          'description'=>$dm->description, 'begin_balance'=>$dm->begin_balance, 'amount'=>$dm->amount,
          'end_balance'=>$dm->end_balance
        ],
        ['bank', 'user_id', 'year', 'month', 'account_num', 'tran_num'],
        ['acct_type', 'tran_date', 'description', 'begin_balance', 'amount', 'end_balance']
      );
    }
    return [ 'status' => "OK" ];
  }
  private function getAssets($lines) { // Log::info("-fn-getAssets for Chase Monthly Statement", $clines);
    $assets = [];
    $section = 'AST';
    for ($i=0; $i<count($lines); $i++) {
      $line = $lines[$i];
      $section = $this->getSection($line, $section);
      if (preg_match('/(.*)\d\d,\s+\d{4}\s+through/', $line)) {
        $assets['begin_date'] = preg_replace('/(.*)(\d\d,\s+\d{4})\s+through\s+(.*)/', "$1$2", $line);
        $assets['end_date'] = preg_replace('/(.*)(\d\d,\s+\d{4})\s+through\s+(.*)/', "$3", $line);
      } else if (preg_match('/(.*)Primary\s+Account:\s+/', $line)) {
        $assets['primary_account'] = preg_replace('/(.*)Primary\s+Account:\s+(.*)/', "$2", $line);
        // $line_num = $i + 1; Log::info("pacct $line_num", $assets);
      } else if (preg_match('/^TOTAL ASSETS\s+/', $line)) {
        $x = preg_split('/\s+/', $line);
        $assets['begin_balance'] = $this->cleanMoney($x[2]);
        $assets['end_balance'] = $this->cleanMoney($x[3]);
        // $line_num = $i + 1; Log::info("pacct $line_num", $assets);
      } else if ($section === 'chase_checking') {
        break;
      }
    }
    return $assets;
  }
  private function getSection($line, $section) { //Log::info("getSection $line $section");
    $line = trim($line);
    $sec_map = [
      // 'CHECKING SUMMARY' => 'checking_summary',
      // 'TRANSACTION DETAIL' => 'tran_detail',
      // 'TRANSACTION DETAIL' => 'tran_detail',
      // 'This page intentionally left blank' => 'END'
      // 'CHASE TOTAL CHECKING' => 'chase_checking',
      // 'CHASE SAVINGS' => 'chase_savings',
      'CHASE PRIVATE CLIENT CHECKING' => 'chase_checking',
      'CHASE PRIVATE CLIENT SAVINGS' => 'chase_savings',
    ];
    if (isset($sec_map[$line])) return $sec_map[$line];
    return $section;
  }
  // private function cleanMoney($str) {
  //   return preg_replace('/[$|,]/', '', $str);
  // }
  private function getSavingsData($lines) { Log::info("getSavingsData");
    $nos = [];
    $act = [];
    $sav = ['act' => $act, 'nos' => $nos];
    $section = 'SAV';
    for ($i=0; $i<count($lines); $i++) {
      $line = $lines[$i];
      $section = $this->getSection($line, $section);
      if ($section == 'chase_savings' and preg_match('/(.*)Account\s+Number:\s+\d{5,}/', $line)) {
        $sav['account'] = preg_replace('/(.*)Account\s+Number:\s+(\d{5,})$/', "$2", $line);
      } else if ($section == 'chase_savings' and preg_match('/^Beginning\s+Balance\s*/', $line)) {
        // $x = preg_split('/\s+/', $line);
        $sav['begin_balance'] = $this->cleanMoney($lines[$i + 1]);
      } else if ($section == 'chase_savings' and preg_match('/^Deposits\s+and\s+Additions\s*/', $line)) {
        // $x = preg_split('/\s+/', $line);
        $sav['additions'] = $this->cleanMoney($lines[$i + 1]);
      } else if ($section == 'chase_savings' and preg_match('/^Ending\s+Balance\s*/', $line)) {
        // $x = preg_split('/\s+/', $line);
        $sav['end_balance'] = $this->cleanMoney($lines[$i + 1]);
      } else if ($section == 'chase_savings' and preg_match('/\d\d\/\d\d(.*)/', $line)) {
        $patt = '/(\d\d\/\d\d)\s+(.*)/';
        $date = preg_replace($patt, "$1", $line);
        $desc = preg_replace($patt, "$2", $line);
        $desc = str_replace('Transaction#', '', $desc);
        if (preg_match('/\d+\s+Reference/', $lines[$i + 1])) $x = explode(" ", $lines[$i + 2]);
        else $x = explode(" ", $lines[$i + 1]);
        $amnt = $this->cleanMoney($x[0]);
        $balc = $this->cleanMoney($x[1]);
        $patt = '/Online Transfer to Chk/i';
        if (preg_match($patt, $desc)) {
          $x = explode(' ', $line);
          Log::info("get SavingsData Online Transfer section $section", [__line__]);
          $date = $x[0];
          $desc = $x[2] . ' ' . $x[3] . ' ' . $x[4] . ' to Chk ' . $x[6] . ' Tran#: ' . $x[8];
          $amnt = $this->cleanMoney($x[9]);
          $balc = $this->cleanMoney($x[10]);
        }
        $act[] = [$date, $desc, $amnt, $balc];
      } else if ($section == 'chase_savings' and preg_match('/Annual\s+Percentage\s+Yield(.*)/', $line)) {
        $note = $line;
        $amnt = preg_replace('/%/', "", $lines[$i + 1]);
        // $amnt = $this->cleanMoney($lines[$i + 1]);
        $nos[] = [$note, $amnt];
      } else if ($section == 'chase_savings' and preg_match('/Interest\s+Paid\s+This\s+Period\s+/', $line)) {
        $note = $line;
        $amnt = $this->cleanMoney($lines[$i + 1]);
        $nos[] = [$note, $amnt];
      } else if ($section == 'chase_savings' and preg_match('/Interest\s+Paid\s+This Period\s*/', $line)) {
        $note = $line;
        $amnt = $this->cleanMoney($lines[$i + 1]);
        $nos[] = [$note, $amnt];
        Log::info("Savings nos $line", $nos);
      } else if ($section == 'chase_savings' and preg_match('/Interest\s+Paid\s+Year-to-Date\s*/', $line)) {
        $note = $line;
        $amnt = $this->cleanMoney($lines[$i + 1]);
        $nos[] = [$note, $amnt];
      } else if (preg_match('/IN CASE OF ERRORS OR QUESTIONS ABOUT YOUR ELECTRONIC FUNDS TRANSFERS:/', $line)) {
        $sav['act'] = $act;
        $sav['nos'] = $nos;
        return $sav;
      }
    }
    return $sav;
  }
  public function getCheckingData($lines) { Log:info("getCheckingData XX_XX");
    $act = [];
    $chk = ['act' => $act];
    $section = 'CHK';
    for ($i=0; $i<count($lines); $i++) {
      $line = $lines[$i];
      $section = $this->getSection($line, $section);
      // if (preg_match('/^Beginning\s+Balance/', $line)) Log::info("==XX chk section act [$section][$line]");
      // if ($section == 'chase_checking' and preg_match('/^Beginning\s+Balance/', $line)) Log::info("==XX chk section act [$section][$line]");
      if ($section == 'chase_checking' and preg_match('/^Beginning\s+Balance\s*/', $line)) {
        // $x = preg_split('/\s+/', $line);
        // Log::info("checking_lines XXXX_XXXX $lines[$i + 1]");
        $chk['begin_balance'] = $this->cleanMoney($lines[$i + 1]);
      } else if ($section == 'chase_checking' and preg_match('/^Electronic\s+Withdrawals\s*/', $line)) {
        // $x = preg_split('/\s+/', $line);
        $chk['withdrawals'] = $this->cleanMoney($lines[$i + 1]);
      } else if ($section == 'chase_checking' and preg_match('/^Ending\s+Balance\s*/', $line)) {
        // $x = preg_split('/\s+/', $line);
        // $chk['end_balance'] = $this->cleanMoney($x[2]);
        $chk['end_balance'] = $this->cleanMoney($lines[$i + 1]);
      } else if ($section == 'chase_checking' and preg_match('/\d\d\/\d\d(.*)/', $line)) {
        // $patt = '/(\d\d\/\d\d)\s+(.*)\s+([-]?\d{1,}.\d\d)\s+(.*)/';
        $patt = '/(\d\d\/\d\d)\s+(.*)/';
        $date = preg_replace($patt, "$1", $line);
        $desc = preg_replace($patt, "$2", $line);
        $desc = str_replace('Transaction#', 'Trans#', $desc);
        // $amnt = preg_replace($patt, "$3", $line);
        // $balc = $this->cleanMoney(preg_replace($patt, "$4", $line));
        $x = preg_split('/\s+/', $lines[$i + 1]);
        $amnt = $this->cleanMoney($x[0]);
        $balc = $this->cleanMoney($x[1]);
        $patt = '/Online International Wire/';
        if (preg_match($patt, $desc)) {
          $x = preg_split('/\s+/', $lines[$i + 4]);
          $amnt = $this->cleanMoney($x[0]);
          $balc = $this->cleanMoney($x[1]);
        }
        $act[] = [$date, $desc, $amnt, $balc];
        Log::info("chk act $section", $act);
      } else if ($section == 'chase_savings') {
        $chk['act'] = $act;
        break;
      }
    }
    return $chk;
  }
  // public function getCheckingData($lines) { Log:info("getCheckingData");
  //   $act = [];
  //   $chk = ['act' => $act];
  //   $section = 'CHK';
  //   for ($i=0; $i<count($lines); $i++) {
  //     $line = $lines[$i];
  //     $section = $this->getSection($line, $section);
  //     // Log::info("chk section act $section");
  //     if ($section == 'chase_checking' and preg_match('/^Beginning\s+Balance\s+/', $line)) {
  //       $x = preg_split('/\s+/', $line);
  //       $chk['begin_balance'] = $this->cleanMoney($x[2]);
  //     } else if ($section == 'chase_checking' and preg_match('/^Electronic\s+Withdrawals\s+/', $line)) {
  //       $x = preg_split('/\s+/', $line);
  //       $chk['withdrawals'] = $this->cleanMoney($x[2]);
  //     } else if ($section == 'chase_checking' and preg_match('/^Ending\s+Balance\s+/', $line)) {
  //       $x = preg_split('/\s+/', $line);
  //       $chk['end_balance'] = $this->cleanMoney($x[2]);
  //     } else if ($section == 'chase_checking' and preg_match('/\d\d\/\d\d(.*)/', $line)) {
  //       $patt = '/(\d\d\/\d\d)\s+(.*)\s+([-]?\d{1,}.\d\d)\s+(.*)/';
  //       $date = preg_replace($patt, "$1", $line);
  //       $desc = preg_replace($patt, "$2", $line);
  //       $amnt = preg_replace($patt, "$3", $line);
  //       $balc = $this->cleanMoney(preg_replace($patt, "$4", $line));
  //       $act[] = [$date, $desc, $amnt, $balc];
  //       Log::info("chk act $section", $act);
  //     } else if ($section == 'chase_savings') {
  //       $chk['act'] = $act;
  //       break;
  //     }
  //   }
  //   return $chk;
  // }
  public function loadMonthlyStatement($ymon) { Log:info("loadMonthlyStatements $ymon");
    $docRoot = "/Users/swang/sites/webdata/docs/Chase/";
    $lines = $this->parsePDF($docRoot . "{$ymon}.pdf");
    if (is_string($lines)) return ['info' => $lines, 'status' => 'NO_FILE'];

    $filename = "Chase_monthly_statement_$ymon";
    $this->writeToTempFile($filename, $lines);
    $assets = $this->getAssets($lines);
    $chk = $this->getCheckingData($lines);
    $sav = $this->getSavingsData($lines);
    Log::info('chk return', $chk);
    Log::info('SAV return', $sav);
    return ['assets' => $assets, 'chk' => $chk, 'sav' => $sav, 'status' => "OK"];
  }
  // private function parsePDF($statementFile) { // Log::info("parseStatements File: $statementFile");
  //   $text = (new Pdf())->setPdf($statementFile)->setOptions(['layout', 'r 96'])->text();
  //   $x = preg_split('/\n/', $text);
  //   $lines = [];
  //   foreach ($x as $line) {
  //     if (ctype_print($line)) {
  //       $line = preg_replace('/\s+/', ' ', $line);
  //       $line = trim($line);
  //       $lines[] = $line;
  //     }
  //   }
  //   return $lines;
  // }
  // private function writeToTempFile($filename, $lines) {
  //   $ccfile = "/sites/tmp/$filename";
  //   $fp = fopen($ccfile, 'w');
  //   $i = 0;
  //   foreach($lines as $line) {
  //       $i++;
  //       fwrite($fp, "$i, [$line]\n");
  //   }
  //   fclose($fp);
  // }
}

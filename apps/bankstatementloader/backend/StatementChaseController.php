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
  private $nos = [];

  use PDFTrait;
  // use addNotes in StatementBOAController
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
  private function getAssets($lines, $ymon) { Log::info("-fn-getAssets for Chase Monthly Statement");
    $assets = ['bank' => 'Chase', 'year' => substr($ymon, 0, 4), 'month' => substr($ymon, 4)];
    $section = 'AST';
    for ($i=0; $i<count($lines); $i++) {
      $line = $lines[$i];
      $section = $this->getSection($line, $section);
      if (preg_match('/(.*)\d\d,\s+\d{4}\s+through/', $line)) {
        $assets['begin_date'] = date('Y-m-d', strtotime(preg_replace('/(.*)(\d\d,\s+\d{4})\s+through\s+(.*)/', "$1$2", $line)));
        $assets['end_date'] = date('Y-m-d', strtotime(preg_replace('/(.*)(\d\d,\s+\d{4})\s+through\s+(.*)/', "$3", $line)));
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
  private function getSavingsData($lines) { Log::info("getSavingsData");
    $nos = $this->nos;
    $act = [];
    $sav = ['act' => $act, 'nos' => $nos];
    $section = 'SAV';
    for ($i=0; $i<count($lines); $i++) {
      $line = $lines[$i];
      $section = $this->getSection($line, $section);
      $bpatt = '/^Beginning\s+Balance\s+\$(\d{1,3}(?:,\d{3})*\.\d\d)$/';
      $dpatt = '/^Deposits\s+and\s+Additions\s+(\d{1,3}(?:,\d{3})*\.\d\d)$/';
      $wpatt = '/^Electronic\s+Withdrawals\s+([+-]?\d{1,3}(?:,\d{3})*\.\d\d)$/';
      $epatt = '/^Ending\s+Balance\s+\$(\d{1,3}(?:,\d{3})*\.\d\d)$/';
      $note1 = '/^(Annual\s+Percentage\s+Yield\s+Earned\s+This\s+Period)\s+(\d{1,3}\.\d\d)%$/';
      $note2 = '/^(Interest\s+Paid\s+This\s+Period)\s+\$(\d{1,3}\.\d\d)$/';
      $note3 = '/^(Interest\s+Paid\s+Year-to-Date)\s+\$(\d{1,3}\.\d\d)$/';
      $apatt = '/(.*)Account\s+Number:\s+(\d{5,})/';
      if ($section == 'chase_savings' and preg_match($apatt, $line)) {
        $sav['account'] = preg_replace($apatt, "$2", $line);
      } else if ($section == 'chase_savings' and preg_match($bpatt, $line)) {
        preg_match($bpatt, $line, $m);
        $sav['begin_balance'] = $this->cleanMoney($m[1]);
      } else if ($section == 'chase_savings' and preg_match($dpatt, $line)) {
        preg_match($dpatt, $line, $m);
        $sav['deposits'] = $this->cleanMoney($m[1]);
      } else if ($section == 'chase_savings' and preg_match($wpatt, $line)) {
        preg_match($wpatt, $line, $m);
        $sav['withdrawals'] = trim($this->cleanMoney($m[1]), '-');
      } else if ($section == 'chase_savings' and preg_match($epatt, $line)) {
        preg_match($epatt, $line, $m);
        $sav['end_balance'] = $this->cleanMoney($m[1]);
      } else if ($section == 'chase_savings' and preg_match($note1, $line)) {
        preg_match($note1, $line, $m);
        $note = 'sav: '.$m[1];
        $amnt = $m[2];
        $nos[] = [$note, $amnt];
      } else if ($section == 'chase_savings' and preg_match($note2, $line)) {
        preg_match($note2, $line, $m);
        $note = 'sav: '.$m[1];
        $amnt = $m[2];
        $nos[] = [$note, $amnt];
      } else if ($section == 'chase_savings' and preg_match($note3, $line)) {
        preg_match($note3, $line, $m);
        $note = 'sav: '.$m[1];
        $amnt = $m[2];
        $nos[] = [$note, $amnt];
      } else if ($section == 'chase_savings' and preg_match('/\d\d\/\d\d(.*)/', $line)) {
        $patt = '#^(\d{2}/\d{2})\s+(.*?)\s+([+-]?\d{1,3}(?:,\d{3})*\.\d\d)\s+([+-]?\d{1,3}(?:,\d{3})*\.\d\d)$#x'; // no change for # x
        preg_match($patt, $line, $m);
        $date = $m[1];
        $desc = $m[2];                
        $amnt = $this->cleanMoney($m[3]);                
        $balc = $this->cleanMoney($m[4]);                
        $act[] = [$date, $desc, $amnt, $balc];
      } else if (preg_match('/IN CASE OF ERRORS OR QUESTIONS ABOUT YOUR ELECTRONIC FUNDS TRANSFERS:/', $line)) {
        $sav['act'] = $act;
        $sav['nos'] = $nos;
        return $sav;
      }
    }
    return $sav;
  }
  public function getCheckingData($lines) { Log:info("-fn-getCheckingData");
    $act = [];
    $chk = ['act' => $act];
    $section = 'CHK';
    for ($i=0; $i<count($lines); $i++) {
      $line = $lines[$i];
      $section = $this->getSection($line, $section);
      $bpatt = '/^Beginning\s+Balance\s+\$(\d{1,3}(?:,\d{3})*\.\d\d)$/';
      $dpatt = '/^Deposits\s+and\s+Additions\s+(\d{1,3}(?:,\d{3})*\.\d\d)$/';
      $wpatt = '/^Electronic\s+Withdrawals\s+([+-]?\d{1,3}(?:,\d{3})*\.\d\d)$/';
      $epatt = '/^Ending\s+Balance\s+\$(\d{1,3}(?:,\d{3})*\.\d\d)$/';
      $note1 = '/^(Annual\s+Percentage\s+Yield\s+Earned\s+This\s+Period)\s+(\d{1,3}\.\d\d)%$/';
      $note2 = '/^(Interest\s+Paid\s+This\s+Period)\s+\$(\d{1,3}\.\d\d)$/';
      $note3 = '/^(Interest\s+Paid\s+Year-to-Date)\s+\$(\d{1,3}\.\d\d)$/';
      if ($section == 'chase_checking' and preg_match($bpatt, $line)) {
        preg_match($bpatt, $line, $m);
        $chk['begin_balance'] = $this->cleanMoney($m[1]);
      } else if ($section == 'chase_checking' and preg_match($dpatt, $line)) {
        preg_match($dpatt, $line, $m);
        $chk['deposits'] = $this->cleanMoney($m[1]);
      } else if ($section == 'chase_checking' and preg_match($wpatt, $line)) {
        preg_match($wpatt, $line, $m);
        $chk['withdrawals'] = trim($this->cleanMoney($m[1]), '-');
      } else if ($section == 'chase_checking' and preg_match($epatt, $line)) {
        preg_match($epatt, $line, $m);
        $chk['end_balance'] = $this->cleanMoney($m[1]);
       } else if ($section == 'chase_checking' and preg_match($note1, $line)) {
        preg_match($note1, $line, $m);
        $note = 'chk: '.$m[1];
        $amnt = $m[2];
        $this->nos[] = [$note, $amnt];
      } else if ($section == 'chase_checking' and preg_match($note2, $line)) {
        preg_match($note2, $line, $m);
        $note = 'chk: '.$m[1];
        $amnt = $m[2];
        $this->nos[] = [$note, $amnt];
      } else if ($section == 'chase_checking' and preg_match($note3, $line)) {
        preg_match($note3, $line, $m);
        $note = 'chk: '.$m[1];
        $amnt = $m[2];
        $this->nos[] = [$note, $amnt];
      } else if ($section == 'chase_checking' and preg_match('/\d\d\/\d\d(.*)/', $line)) {
        $patt = '#^(\d{2}/\d{2})\s+(.*?)\s+([+-]?\d{1,3}(?:,\d{3})*\.\d\d)\s+([+-]?\d{1,3}(?:,\d{3})*\.\d\d)$#x'; // no change for # x
        preg_match($patt, $line, $m);
        $date = $m[1];
        $desc = $m[2];          
        $amnt = $this->cleanMoney($m[3]);
        $balc = $this->cleanMoney($m[4]);       
        $act[] = [$date, $desc, $amnt, $balc];
        // Log::info("===== chk nos $section", $this->nos);
      } else if ($section == 'chase_savings') {
        $chk['act'] = $act;
        break;
      }
    }
    return $chk;
  }
  public function loadMonthlyStatement($ymon) { Log:info("loadMonthlyStatements $ymon");
    $docRoot = "/sites/webdata/docs/Chase/";
    $fullpath = "$docRoot/{$ymon}.pdf";
    if (!file_exists($fullpath)) {
      Log::info("$fullpath not exist");
      return ['info' => '', 'status' => 'NO_FILE'];
    }
    try {
      $lines = $this->parsePDF_Spatie($fullpath);
      // $lines = $this->parsePDF($fullpath);
      // $lines = $this->parseFidelityStatememt($fullpath);
      if (is_string($lines)) return ['info' => $lines, 'status' => 'NO_FILE'];
    } catch(Excption $e) {
      Log::info("ParsePDF Failed $e.error()");
    }

    $filename = "Chase_monthly_statement_$ymon";
    $this->writeToTempFile($filename, $lines);
    $assets = $this->getAssets($lines, $ymon);
    $chk = $this->getCheckingData($lines);
    $sav = $this->getSavingsData($lines);
    Log::info('chk return', $chk);
    Log::info('SAV return', $sav);
    return ['assets' => $assets, 'chk' => $chk, 'sav' => $sav, 'status' => "OK"];
  }
}

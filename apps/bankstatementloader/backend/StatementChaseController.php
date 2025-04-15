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
  private $chk_nos = [];
  use PDFTrait;

  public function addActivity(Request $da) { //Log::info('addActivity', $da->toArray());
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

  private function getLines($filename) { // Log::info("getLines->filename=$filename");
    // Open the file for reading
    // Log::info("file_exists[" . file_exists($filename) ? $filename : 'NOT exist' . "]"); //exit(0);
    if (!file_exists($filename)) return null; //exit(0);
    $file = fopen($filename, 'r');

    // Check if the file was opened successfully
    $lines = [];
    if ($file) {
        // Read the file line by line
        while (($line = fgets($file)) !== false) {
            // Process the line (e.g., print it)
            // array_push($lines, $line);
            $line = trim($line);
            if ($line == '') continue;
            array_push($lines, $line);
        }
        // Close the file
        fclose($file);
        return $lines;
    } else {
        // Handle the error if the file could not be opened
        echo "Error: Unable to open the file.";
    }
  }
  public function loadMonthlyStatement($ymon) { Log:info("loadMonthlyStatements $ymon"); //exit(0);
    $filename = config('constants.DOC_DIR') . "/Chase/${ymon}.txt";
    $lines = $this->getLines($filename);
    // Log::info('lines', [$lines]);
    foreach($lines as $line) Log::info("[$line]");
    Log::info("============ the above is the Chase $ymon Statement ==============");
    if (is_null($lines)) return ['info' => $filename, 'status' => 'NO_FILE'];

    [$start, $assets] = $this->getAssets($lines);
    $lines = array_slice($lines, $start + 3);
    [$start, $chk] = $this->getCheckingData($lines);
    $lines = array_slice($lines, $start + 2);
    // Log::info("savings part start=$start", $lines);
    $sav = $this->getSavingsData($lines);
    // Log::info('chk return', $chk);
    return ['assets' => $assets, 'chk' => $chk, 'sav' => $sav, 'status' => "OK"];
  }

  private function getAssets($lines) { // Log::info("-fn-getAssets for Chase Monthly Statement");
    $assets = [];
    $keys = [
      'bank',
      'year',
      'month',
      'begin_date',
      'end_date',
      'begin_balance',
      'end_balance',
      'primary_account',
      'tran_cnt',
    ];
    array_shift($lines);
    for ($i=0; $i<count($lines); $i++) {
      // if ($i == 9) break;
      $line = $lines[$i];
      // if ($line == '[Assets]') continue;
      $key = $keys[$i];

      $x = preg_replace("/{$key}:(.*)/", "$1", $line);
      if ($x == $line or is_null($x)) {
        Log::warn("somethig is wrong key=[$key] line=[$line] exiting...");
        exit(-1);
      } else {
        $assets[$key] = $x;
        // Log::info("-CK- key=[$key] line=[$line]", $assets);
        if ($key == 'tran_cnt') return [$i, $assets];
        continue;
      }
    }
  }

  private function getCheckingData($lines) { // Log:info("getCheckingData lines=");
    $act = [];
    $chk = ['act' => $act];
    $keys = [
      'account',
      'begin_balance',
      'end_balance',
      'tran',
      'nos1',
      'nos2',
      'nos3',
    ];
    for ($i=0; $i<count($lines); $i++) {
      $line = $lines[$i];
      if ($line == '[Checking]') continue;
      $key = $keys[$i];

      $x = preg_replace("/{$key}:(.*)/", "$1", $line);
      if ($x == $line or is_null($x)) {
        Log::warn("somethig is wrong key=[$key] line=[$line] exiting...", [__line__]);
        exit(-1);
      } else if ($key == 'tran') {
        $trans = explode('__', $x);
        $start_val = $chk['begin_balance'];
        foreach ($trans as $tr) {
          $t = explode(' ~ ', $tr);
          $date = $t[0];
          $desc = $t[1];
          $amnt = $t[2];
          $balc = $amnt + $start_val;
          $act[] = [$date, $desc, $amnt, $balc];
          $start_val = $balc;
        }
        $chk['act'] = $act;
        continue;
      } else if ($key == 'nos1') {
        $t = explode(' ~ ', $x);
        $note = 'chk:' . $t[0];
        $amnt = str_replace('%', '', $t[1]);
        $this->chk_nos[] = [$note, $amnt];
        continue;
      } else if ($key == 'nos2') {
        $t = explode(' ~ ', $x);
        $note = 'chk:' . $t[0];
        $amnt = $t[1];
        $this->chk_nos[] = [$note, $amnt];
        continue;
      } else if ($key == 'nos3') {
        $t = explode(' ~ ', $x);
        $note = 'chk:' . $t[0];
        $amnt = $t[1];
        $this->chk_nos[] = [$note, $amnt];
        // Log::info("i=$i chk", [$chk, __line__]);
        return [$i, $chk];
      } else {
        $chk[$key] = $x;
        // Log::info("-CK- key=[$key] line=[$line]", $assets);
        // if ($key == 'nos3') break;
        continue;
      }
    }
  }

  private function getSavingsData($lines) { //Log:info("getSavingsData liine=", $lines);
    $act = [];
    $sav = ['act' => $act];
    $keys = [
      'account',
      'begin_balance',
      'end_balance',
      'tran',
      'nos1',
      'nos2',
      'nos3',
    ];
    for ($i=0; $i<count($lines); $i++) {
      $line = $lines[$i];
      if ($line == '[Checking]') continue;
      $key = $keys[$i];

      $x = preg_replace("/{$key}:(.*)/", "$1", $line);
      if ($x == $line or is_null($x)) {
        Log::warn("somethig is wrong key=[$key] line=[$line] exiting...", [__line__]);
        exit(-1);
      } else if ($key == 'tran') {
        $trans = explode('__', $x);
        $start_val = $sav['begin_balance'];
        foreach ($trans as $tr) {
          $t = explode(' ~ ', $tr);
          $date = $t[0];
          $desc = $t[1];
          $amnt = $t[2];
          // Log::info("saving amnt=[$amnt] begin_balance=[$start_val]", [__line__]);
          $balc = $amnt + $start_val;
          $act[] = [$date, $desc, $amnt, $balc];
          $start_val = $balc;
        }
        $sav['act'] = $act;
        continue;
      } else if ($key == 'nos1') {
        $t = explode(' ~ ', $x);
        $note = 'sav:' . $t[0];
        $amnt = str_replace('%', '', $t[1]);
        $nos[] = [$note, $amnt];
        continue;
      } else if ($key == 'nos2') {
        $t = explode(' ~ ', $x);
        $note = 'sav:' . $t[0];
        $amnt = $t[1];
        $nos[] = [$note, $amnt];
        continue;
      } else if ($key == 'nos3') {
        $t = explode(' ~ ', $x);
        $note = 'sav:' . $t[0];
        $amnt = $t[1];
        $nos[] = [$note, $amnt];
        // Log::info("i=$i sav", [$sav, __line__]);
        $sav['nos'] = array_merge($this->chk_nos, $nos);
        return $sav;
      } else {
        $sav[$key] = $x;
        continue;
      }
    }
  }
}
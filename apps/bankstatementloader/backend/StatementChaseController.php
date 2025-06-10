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
  private $notes = [];

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
    foreach ($lines as $i => $line) Log::info("$i:[$line]");
    Log::info("============ the above is the Chase $ymon Statement ==============");
    if (is_null($lines)) return ['info' => $filename, 'status' => 'NO_FILE'];

    [$start, $assets] = $this->getAssets($lines);
    // Log::info("start=$start, assets:", $assets);
    $lines = array_slice($lines, $start + 3);
    [$start, $chk] = $this->getAccountData($lines, 'chk');
    // Log::info("start=$start, chk:", $chk);
    $lines = array_slice($lines, $start + 1);
    // Log::info("savings part start=$start", $lines);
    [$start, $sav] = $this->getAccountData($lines, 'sav');
    // Log::info("start=$start, sav:", $sav);
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
    foreach ($lines as $i => $line) {
      $key = $keys[$i];

      $x = preg_replace("/{$key}:(.*)/", "$1", $line);
      if ($x == $line or is_null($x)) {
        Log::error("somethig is wrong key=[$key] line=[$line] exiting...");
        exit(-1);
      } else {
        $assets[$key] = $x;
        // Log::info("-CK- key=[$key] line=[$line]", $assets);
        if ($key == 'tran_cnt') return [$i, $assets];
        continue;
      }
    }
  }

  private function getAccountData($lines, $note_type) { //Log:info("getAccountData lines=", $lines);
    $act = [];
    $nos = [];
    $acct = ['act' => $act];
    $keys = [
      'account',
      'begin_balance',
      'end_balance',
    ];
    // for ($i=0; $i<count($lines); $i++) {
    foreach ($lines as $i => $line) {
      // $line = trim($lines[$i]);
      if (!preg_match('/(.*):(.*)/', $line)) break;
      if ($line == '[Savings]') {
        $acct['act'] = $act;
        $acct['nos'] = $nos;
        return [$i, $acct]; //checking data
      }
      if ($line == '[Checking]') continue;
      if ($i<=2) {
        $key = $keys[$i];
        $x = preg_replace("/{$key}:(.*)/", "$1", $line);
        if ($x == $line or is_null($x)) {
          Log::error("somethig is wrong key=[$key] line=[$line] exiting...", [__line__]);
          exit(-1);
        } else {
          $acct[$key] = $x;
        }
      } else if ($i > 2) {
        [$key, $x] = explode(':', $line, 2); // get the key
        Log::info("key=$key x=$x");
        if ($key == 'tran') {
          if (!isset($acct['tran'])) $acct['tran'] = $acct['begin_balance'];
          $t = explode(' ~ ', $x);
          $date = $t[0];
          $desc = $t[1];
          $amnt = $t[2];
          $balc = $amnt + $acct['tran'];
          $act[] = [$date, $desc, $amnt, $balc];
          $acct['tran'] = $balc;
          // $acct['begin_balance'] = $balc;
          continue;
        } else if ($key == 'note') {
          $t = explode(' ~ ', $x);
          $note = $note_type == 'chk' ? 'chk:' . $t[0] : 'sav:' . $t[0];
          $amnt = $t[1];
          $this->notes[] = [$note, $amnt];
          continue;
        }
      }
    }
    $acct['act'] = $act;
    $acct['nos'] = $this->notes;
    return [$i, $acct];
  }
}

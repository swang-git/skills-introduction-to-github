<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\bankstatementloader\BankSecurityHolding;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use App\Traits\PDFTrait;

class ChaseBrockerageIntradayController extends Controller {
  public function __construct() {	}
  use PDFTrait;
  private $positions = [];
  private $bank = 'Chase';
  private $account = '985-10278';
  private $date = null;
  private $cash = null;
  private $today_total_gl = null;
  private $today_total_val = null;

  public function saveData(Request $da) { Log::info("saveData");
    $positions = $da['positions'];
    // $totalVal = $da['totalVal'];
    // $totalGL = $da['totalGL'];
    BankSecurityHolding::upsert(
      $positions,
      ['price', 'pchg', 'pchg_p', 'value', 'today_global', 'share', 'cost', 'total_gl_p', 'total_gl'],
      ['date', 'bank', 'account', 'symbol']
    );
    return ['status' => 'OK'];
  }

  private function alreadyInDB() {
    $pos = BankSecurityHolding::where('date', $this->date)->select("*")->get();
    if (count($pos) > 0) {
      $pos = BankSecurityHolding::where('status', 'A')->select("*")->orderBy('date', 'desc')->get();
      return ['status' => 'OK', 'pos' => $pos, 'dataIn' => count($pos)];
    } else {
      return null;
    }
  }
  public function getData() { Log::info("getData for Chase Brockerage Account");
    $docRoot = "/sites/webdata/docs/Chase/Brokerage";
    $filename = '';
    $files = scandir($docRoot);
    rsort($files);
    $filename = $files[0];
    Log::info("latest file: $docRoot/$filename", $files);
    $this->date = substr($filename, 0, 10);
    Log::info("date=[$this->date]");
    $ret = $this->alreadyInDB();
    // Log::info("date=[$this->date]", $ret);
    if (!is_null($ret)) return $ret;
    
    $lines = file("$docRoot/$filename",FILE_SKIP_EMPTY_LINES|FILE_IGNORE_NEW_LINES);
    Log::info('lines:', $lines);
    $pos = [];
    $stock = [];
    $cashx = trim(array_pop($lines));
    while($cashx == '' || $cashx == null) $cashx = trim(array_pop($lines));
    $this->cash = $this->cleanMoney($cashx);
    Log::info("cashx=[$cashx]");
    $today_total_glx = trim(array_pop($lines));
    Log::info("today_total_glx=[$today_total_glx]");
    if (preg_match('/^Loss of\s+/', $today_total_glx)) $this->today_total_gl = - explode('-', $today_total_glx)[2];
    else if (preg_match('/^Gain of\s+/', $today_total_glx)) $this->today_total_gl = explode('+', $today_total_glx)[2];
    else if (!preg_match('/[1-9]/', $today_total_glx)) $this->today_total_gl = 0;
    $today_totalx = preg_split("/\t{1,}/", array_pop($lines));
    $this->today_total_val = $this->cleanMoney(array_pop($today_totalx));
    foreach ($lines as $line) {
      $line = trim($line);
      // Log::info("line:[$line]");
      
      if ($line == 'Trade') {
        // Log::info("stock", $stock);
        array_push($pos, $stock);
        $stock = [];
        continue;
      }
      array_push($stock, $line);
    }
    Log::info("cash=$this->cash total_gl=$this->today_total_gl total=$this->today_total_val");
    $this->process_data($pos);
    $this->showPositions();
    return ['pos' => $this->positions, 'today_total_val' => $this->today_total_val,
      'today_total_gl' => $this->today_total_gl, 'dataIn' => false, 'status' => 'OK'];
  }
  private function showPositions() {
    foreach ($this->positions as $pos) {
      Log::info("====================");
      foreach ($pos as $key => $val) {
        Log::info("$key = $val");
      }
    }
    Log::info("cash=$this->cash today_total_gl=$this->today_total_gl today_total_val=$this->today_total_val");
  }
  private function get_base_cost($symbol) {
    if ($symbol == 'DELL') return 900.00;
    else if ($symbol == 'WBD') return 690;
    else if ($symbol == 'T') return  2870;
    else if ($symbol == 'CHTR') return  4000;
    else if ($symbol == 'CSCO') return  12800;
    else if ($symbol == 'MSFT') return  18000;
  }
  private function getKeyVal ($stock) {
    $x = ['account' => '985-10278', 'date' => $this->date, 'bank' => 'Chase'];
    $p = $stock;
      $symbol = $p[0];
      $x['symbol'] = $symbol;
      // $x['company'] = $p[1];
      $base_cost = $this->get_base_cost($symbol);
      $x['price'] = $p[2];
      if (preg_match('/^(Loss|Gain)\s+of/', $p[3])) {
        $x['prchg'] = $this->process_price_change($p[3], 'val');
        $x['prchg_p'] = $this->process_price_change($p[3], 'pct');
        $x['value'] = $this->cleanMoney($p[6]);
      } else if (!preg_match('/[1-9]/', $p[3])) {
        $x['prchg'] = 0;
        $x['prchg_p'] = 0;
        $x['value'] = $this->cleanMoney($p[5]);
      }
      if (preg_match('/(Loss|Gain)\s+of/', $p[7])) {
        $x['today_gl'] = $this->cleanMoney($this->get_today_gl($p[7]));
        $x['share'] = $p[8];
      } else {
        $x['today_gl'] = 0;
        $x['share'] = $p[6];
      }
      $x['cost'] = $base_cost;
      $value = $x['value'];
      Log::info("base cost:[$base_cost] value=[$value]");
      $x['total_gl'] = sprintf('%01.2f', $x['value'] - $base_cost);
      $total_gl = $x['total_gl'];
      $x['total_gl_p'] = sprintf('%01.2f', $total_gl / $base_cost * 100);
    return $x;
  }
  function process_data($pos) {
    foreach ($pos as $stock) {
      $x = $this->getKeyVal($stock);
      array_push($this->positions, $x);
    }
    array_push($this->positions, // add cash position
      ['date' => $this->date, 'bank' => $this->bank, 'account' => $this->account, 'symbol' => 'CASH', 
        'price' => 1.00, 'prchg' => 0, 'prchg_p' => 0, 'value' => $this->cash, 'today_gl' => 0,
        'share' => $this->cash, 'cost' => $this->cash, 'total_gl' => 0, 'total_gl_p' => 0
      ]);
  }
  function get_today_gl($line) {
    // line like: Gain of +304.00+304.00
    // or
    // line like: Loss of +304.00+304.00
    // or
    // 0.000.00
    if (!preg_match('/[1-9]/', $line)) return 0;
    $glx = preg_split('/(Loss|Gain)\s+of\s+/', $line);
    // Log::info('glx', $glx);
    $glv = $glx[1];
    if (preg_match('/^\+/', $glv)) return explode('+', $glv)[1];
    else if (preg_match('/-/', $glv)) { 
      $ret = explode('-', $glv)[1];
      // Log::info("ret=$ret");
      return -$ret;
    }
  }
  function process_price_change($line, $flag) {
    // line like: Gain of +0.76+0.76Gain of 0.18%0.18%
    // or
    // line like: Loss of +0.76+0.76Loss of 0.18%0.18%
    // or
    // line like: 0.00%0.00%
    if (!preg_match('/[1-9]/', $line)) {
      $value = $this->cleanMoney($line);
      $share = $this->cleanMoney($line);
      return 0;
    }
    $px = preg_split('/(Loss|Gain)\s+of\s+/', $line);
    Log::info('px', $px);
    if ($flag == 'val') {
      $pv = $px[1];
      if (preg_match('/^\+/', $pv)) return explode('+', $pv)[1];
      else if (preg_match('/-/', $pv)) { 
        $ret = explode('-', $pv)[1];
        // Log::info("ret=$ret");
        return -$ret;
      }
    }
    if ($flag == 'pct') {
      $pv = $px[2];
      if (preg_match('/-/', $pv)) return -trim(explode('-', $pv)[1], '%');
      else return explode('%', $pv)[1];
    }
  }
}

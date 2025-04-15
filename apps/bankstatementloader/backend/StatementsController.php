<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Models\expense\Spend;
use App\Models\bankstatement\BankStatementAsset;
use App\Models\bankstatement\BankStatementHolding;
use App\Models\bankstatement\BankStatementActivity;
use App\Models\bankstatementloader\SpendsView;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use App\Traits\PDFTrait;
use App\Traits\ParserFlagPDFTrait;
use App\Traits\RothActivityTrait;
use App\Traits\IraActivityTrait;
use App\Traits\RothHoldingsTrait;
use App\Traits\IraHoldingsTrait;

class StatementsController extends Controller {
	public function __construct() {	}
  use PDFTrait;
  use ParserFlagPDFTrait;
  use RothActivityTrait;
  use IraActivityTrait;
  use RothHoldingsTrait;
  use IraHoldingsTrait;

  public function getCreditCardSpendings($userId, $openDate, $closeDate, $dueDay) { Log::info("getCreditCardSpending openDate=$openDate, closeDate=$closeDate, dueDay=$dueDay");
		$ccdata = DB::select('CALL get_credit_card_spendings(?, ?, ?, ?)', [$userId, $openDate, $closeDate, $dueDay]);
		return ['ccdata' => $ccdata, 'status' => "OK"];
	}
  private function getAnnuityFileName($ym, $docRoot) {
    $year = (int)substr($ym, 0, 4);
    $month = (int)substr($ym, 4);
    $quarter = $this->getQuarter($ym);
    Log::info("year=$year month=$month Q$quarter");
    $annuityFileName = "/" . $year . "Q" . $quarter . "_annuity.pdf";
    if (!file_exists($docRoot . $annuityFileName)) {
      Log::info("Annuity file for $docRoot$annuityFileName not exists, using last Quarter file");
      $quarter--;
      if ($quarter == 0) {
        $year--;
        $quarter = 4;
      }
      $annuityFileName = $year . "Q" . $quarter . "_annuity.pdf";
    }
    if (!file_exists($docRoot . $annuityFileName)) {
      Log::info("Annuity file for $docRoot$annuityFileName not exists, exit ...");
      exit(0);
    }
    return $docRoot . $annuityFileName;
  }
  private function getQuarter($ym) {
    $year = (int)substr($ym, 0, 4);
    $month = (int)substr($ym, 4);
    $quarter = 1;
    if ($month>=4 and $month < 7) $quarter = 2;
    else if ($month>=7 and $month < 10) $quarter = 3;
    else if ($month>=10 and $month <= 12) $quarter = 4;
    return $quarter;
  }
  private function getAnnuityData($lines) {
    $data = [];
    for ($i=0; $i<count($lines); $i++) {
      $line = $lines[$i];
      if (preg_match('/Fidelity\s+VIP\s+FundsManager/', $line)) {
        $data['bunit'] = $this->cleanMoney($lines[$i + 1]);
        $data['bpric'] = $lines[$i + 2];
        $data['sbal']  = $this->cleanMoney($lines[$i + 3]);
        $data['eunit'] = $this->cleanMoney($lines[$i + 4]);
        $data['epric'] = $lines[$i + 5];
        $data['ebal'] =  $this->cleanMoney($lines[$i + 6]);
      // } else if (preg_match('/Personal\s+Retirement\s+Annuity\s+Contract\s+Number:/', $line)) {
      } else if (preg_match('/Personal\s+Retirement\s+Annuity\s+Contract\s+Number/', $line)) {
        $data['cnum'] = $lines[$i + 1];
      }
    }
    return $data;
  }
  private function getIraStatementHeaders($lines)  {
    $data = [];
    for ($i=0; $i<count($lines); $i++) {
      $line = $lines[$i];
      if ($i == 0 and $line == 'INVESTMENT REPORT') {
        $dates = preg_split('/ - /', $lines[1]);
        $data['bdate'] = $dates[0];
        $data['edate'] = $dates[1];
      } else if ($line == 'Your Portfolio Value:') {
        $data['cpval'] = $this->cleanMoney($lines[$i + 1]);
      } else if ($line == 'Beginning Portfolio Value') {
        $data['bpval'] = $this->cleanMoney($lines[$i + 1]);
        $data['ypval'] = $this->cleanMoney($lines[$i + 2]);
      } else if ($i < 28 and $line == 'Additions') {
        $data['cadd'] = $lines[$i + 1];
        $data['yadd'] = $lines[$i + 2];
      } else if ($i < 30 and $line == 'Subtractions') {
        $data['csub'] = $lines[$i + 1];
        $data['ysub'] = $lines[$i + 2];
      } else if ($i < 35 and $line == 'Change in Investment Value *') {
        $data['cchg'] = $lines[$i + 1];
        $data['ychg'] = $lines[$i + 2];
      } else if ($i < 40 and preg_match('/Ending Portfolio Value\*\*/', $line)) {
        $data['cend'] = $this->cleanMoney($lines[$i + 1]);
        $data['yend'] = $this->cleanMoney($lines[$i + 2]);
      } else if (strpos($line, 'INDIVIDUAL TOD') > 0) {
        $data['indAcct'] = $lines[$i + 1];
        $data['indSval'] = $this->cleanMoney($lines[$i + 2]);
        $data['indEval'] = $this->cleanMoney($lines[$i + 3]);
      } else if ($i < 77 and strpos($line, 'TRADITIONAL IRA') > 0) {
        $data['acct'] = $lines[$i + 2];
        $data['sval'] = $this->cleanMoney($lines[$i + 3]);
        $data['eval'] = $this->cleanMoney($lines[$i + 4]);
      // } else if ($i < 400 and strpos($line, '\(SPAXX\)') > 0) {
      }
    }
    return $data;
  }
  private function getRothStatementHeaders($lines)  { Log::debug("-CK-getRothStatementHeaders");
    $data = [];
    for ($i=0; $i<count($lines); $i++) {
      $line = $lines[$i];
      if ($i == 0 and $line == 'INVESTMENT REPORT') {
        $dates = preg_split('/ - /', $lines[1]);
        $data['bdate'] = $dates[0];
        $data['edate'] = $dates[1];
      } else if ($line == 'Your Portfolio Value:') {
        $data['cpval'] = $this->cleanMoney($lines[$i + 1]);
      } else if (preg_match('/^Beginning Portfolio Value/', $line)) {
        $data['bpval'] = $this->cleanMoney($lines[$i + 1]);
        $data['ypval'] = $this->cleanMoney($lines[$i + 2]);
      } else if ($i < 22 and $line == 'Additions') {
        $data['cadd'] = $lines[$i + 1];
        $data['yadd'] = $lines[$i + 2];
      } else if ($i < 26 and $line == 'Subtractions') {
        $data['csub'] = $lines[$i + 1];
        $data['ysub'] = $lines[$i + 2];
      } else if ($i < 27 and $line == 'Transaction Costs, Fees & Charges') {
        $data['tranFee'] = $lines[$i + 2];
      } else if ($i < 28 and $line == 'Change in Investment Value *') {
        $data['cchg'] = $lines[$i + 1];
        $data['ychg'] = $lines[$i + 2];
      } else if ($i < 30 and preg_match('/Ending\s+Portfolio\s+Value\*\*(.*)/', $line)) {
        $data['cend'] = $this->cleanMoney($lines[$i + 1]);
        $data['yend'] = $this->cleanMoney($lines[$i + 2]);
      } else if ($i <= 70 and $line == 'X85-275143') {
        $data['indAcct'] = $line;
        $data['indSval'] = $this->cleanMoney($lines[$i + 1]);
        $data['indEval'] = $this->cleanMoney($lines[$i + 2]);
      } else if ($i <= 84 and strpos($line, 'ROTH IRA') > 0) {
        $data['acct'] = $lines[$i + 2];
        $data['sval'] = $this->cleanMoney($lines[$i + 3]);
        $data['eval'] = $this->cleanMoney($lines[$i + 4]);
      }
    }
    return $data;
  }
  public function loadFidelityMonthlyStatements($ymon) { Log::debug("loadFidelityMonthlyStatements $ymon");
    $docRoot = config('constants.DOC_DIR') . "/Fidelity/";
    $annuityFileName = $this->getAnnuityFileName($ymon, $docRoot);
    Log::debug("parsing file $annuityFileName");
    $lines = $this->parseFidelityStatememt($annuityFileName);
    // $lines = $this->parsePDF($annuityFileName);
    Log::debug("parsing file $annuityFileName DONE");
    if (is_string($lines)) {
      Log::debug("loading file $annuityFileName FAILED");
      return ['info' => $lines, 'status' => 'NO_FILE'];
    }
    $year = substr($ymon, 0, 4);
    $filename = "fidelity_monthly_statement_${year}Q" . $this->getQuarter($ymon) . '_annuity';
    $this->writeToTempFile($filename, $lines);
    $dataAnn = $this->getAnnuityData($lines);
    Log::debug("writeToTempFile $filename");

    //== IRA accounts (one individual account)
    $iraFileName = $docRoot . "${ymon}_ira.pdf";
    Log::debug("parsing file $iraFileName");
    $lines = $this->parseFidelityStatememt($iraFileName);
    Log::debug("parsing file $iraFileName DONE");
    if (is_string($lines)) {
      Log::debug("loading file $iraFileName FAILED");
      return ['info' => $lines, 'status' => 'NO_FILE'];
    }
    $filename = "fidelity_monthly_statement_$ymon" . '_ira';
    $this->writeToTempFile($filename, $lines);
    $dataIra = $this->getIraStatementHeaders($lines);
    $accountSepLine = $this->getAccountSepLine($lines);
    // Log::info("-CK-IRA Account Separation Line=$accountSepLine");
    $dataIra = $this->getIndIraHoldings($dataIra, $lines, 0, $accountSepLine);
    $dataIra = $this->getIraHoldings($dataIra, $lines, $accountSepLine, count($lines));
    $dataIra = $this->getIndIraActivity($dataIra, $lines, 0, $accountSepLine);
    $dataIra = $this->getIraActivity($dataIra, $lines, $accountSepLine, count($lines));

    //== Roth accounts (one individual account)
    $lines = $this->parseFidelityStatememt($docRoot . "${ymon}_roth.pdf");
    if (is_string($lines)) return ['info' => $lines, 'status' => 'NO_FILE'];
    $filename = "fidelity_monthly_statement_$ymon" . '_roth';
    $this->writeToTempFile($filename, $lines);
    $accountSepLine = $this->getAccountSepLine($lines);
    // Log::info("-CK-Roth Account Separation Line=$accountSepLine");
    $dataRoth = $this->getRothStatementHeaders($lines);
    $dataRoth = $this->getIndRothHoldings($dataRoth, $lines, 0, $accountSepLine);
    $dataRoth = $this->getRothHoldings($dataRoth, $lines, $accountSepLine, count($lines));
    $dataRoth = $this->getIndRothActivity($dataRoth, $lines, 0, $accountSepLine);
    $dataRoth = $this->getRothActivity($dataRoth, $lines, $accountSepLine, count($lines));

    return ['dataIra' => $dataIra, 'dataRoth' => $dataRoth, 'dataAnn' => $dataAnn, 'status' => 'OK'];
  }
  private function getAccountSepLine($lines)  {
    $accountSepLine = 0;
    $idx = 0;
    foreach ($lines as $line) {
      $idx++;
      // if (preg_match('/^Account Summary/', $line) and $accountSepLine == 0) {
      if (preg_match('/Account Summary/', $line) and $accountSepLine == 0) {
        $accountSepLine = $idx;
        continue; // skip first
      }
      // if (preg_match('/^Account Summary/', $line)) {
      if (preg_match('/Account Summary/', $line)) {
        $accountSepLine = $idx;
        break;
      }
    }
    return $accountSepLine;
  }
  private function getPTdate($openDate, $mmdd) { Log::info("-fn-getPTdate $openDate $mmdd");
    $year = explode('/', $openDate)[2];
    $odate = date_create_from_format('m/d/Y', $openDate);
    $mmddyy = $mmdd . "/$year";
    $idate = date_create_from_format('m/d/Y', $mmddyy);
    $retdate = "$year/" . $mmdd;
    $diff = (array)date_diff($odate, $idate);
    // Log::info('-CK-diff days ' . $diff['days'] . ' ' . $mmddyy);
    if ($diff['days'] > 31) { // year end change year
      $year += 1;
      $retdate = "$year/" . $mmdd;
    }
    return preg_replace('/\//', '-', $retdate);
  }
  private function chkDBforMatch($data) { Log::info('-fn-chkDBforMatch', $data);
    $purchases = $data['purchases'];
    $openDate = $data['open'];
    // $closDate = $data['clos'];
    $noDBmatch = array_fill(0, count($purchases), 0);
    $dup_items = array_fill(0, count($purchases), 0);
    $idx = 0;
    foreach($purchases as $p) {
      if ($p[2] == 'MTC') {
        $idx++;
        continue;
      }
      $pdate = $this->getPTdate($openDate, $p[0]);
      $tdate = $this->getPTdate($openDate, $p[1]);

      $pcost = preg_replace('/[,|$| ]/', '', $p[4]);
      if (preg_match('/RETURN/', $p[3])) $pcost = -$pcost;
      Log::info("date compare: $pdate > $tdate, [$pcost] postDate=[$pdate]");
      // $match = Spend::where([ ['status', 'A'], ['totalpaid', $pcost], ['paymethod_id', 10] ])
      $match = Spend::where([ ['status', 'A'], ['totalpaid', $pcost]])->whereIn('paymethod_id', [10, 18])
          ->where(function($q)  use ($tdate, $pdate, $pcost) {
          $q->whereBetween('purchasedon', [$tdate, $pdate])->orWhere('post_date', $pdate);
        })->pluck('totalpaid');
        // Log::info('sql', [$match, $pdate, $tdate, $pcost]); exit(0);
      if (count($match) == 0) {
        Log::info('count(match) == 0', [$idx, $match, $pdate, $tdate, $pcost]);
        $noDBmatch[$idx] = 1;
      } else if (count($match) == 1) {
        Log::info('count(match) == 1', [$idx, $match, $pdate, $tdate, $pcost]);
        $noDBmatch[$idx] = 0;
      } else if (count($match) > 1) {
        $noDBmatch[$idx] = count($match);
        $dup_items[$idx] = count($match);
        // Log::info('dup_items ' . count($match), $dup_items);
      }
      $idx++;
    }
    // this only works for dupd purchases which match records in DB
    Log::info('noDBmatch', $noDBmatch);
    Log::info('dup_items', $dup_items);
    return array_map(function($a, $b) { return $a - $b; }, $noDBmatch, $dup_items);
  }
  function setPostDate($id, $postDate) { Log::info("-fn-setPostDate: $postDate for $id");
    $dm = Spend::find($id);
    $dm->post_date = $postDate;
    $dm->save();
    return ['status' => "OK"];
  }
  function getMatchedSpends(Request $da) { Log::info('-fn-getMatchedSpends', $da->toArray());
    $userId = Auth::user()->id;
    $cost = $da->cost;
    $openDate = $da->openDate;
    $closeDate = $da->closeDate;
    $postDate = $da->postDate;
    $matchedSpends = DB::select('CALL get_db_matched_spending(?, ?, ?, ?, ?)', [$userId, $cost, $openDate, $closeDate, $postDate]);
    return ['matchedSpends' => $matchedSpends, 'status' => "OK"];
  }
  // function getMatchedSpends(Request $da) { Log::info('-fn-getMatchedSpends', $da->toArray());
  //   $cost = $da->cost;
  //   $podate = $da->podate;
  //   $bedate = $da->bedate;
  //   $afdate = $da->afdate;
  //   $FC10or18 = SpendsView::where([['paymethod_id', 10], ['cost', $cost], ['purchasedate', '>=', $bedate], ['purchasedate', '<=', $afdate]])
  //                       ->orWhere([['paymethod_id', 18], ['cost', $cost], ['purchasedate', '>=', $bedate], ['purchasedate', '<=', $afdate]]);
  //   $matchedSpends = $FC10or18->select('id', 'purchasedate', 'postdate', 'payee', 'cost', 'paymethod_id')->get();
  //   return ['matchedSpends' => $matchedSpends, 'status' => "OK"];
  // }
  function subtract($a, $b) { return $a - $b; }
  private function LogInfo($lines) {
    $i = 0;
    foreach($lines as $line) {
      $i++;
      Log::info("LINE $i [" . $line . "]");
    }
  }
  public function getCreditCardData($date, $bank) { Log::info("-fn-getCreditCardData $bank $date", [__file__, __line__]);
    $pdfFile = config('constants.DOC_DIR') . "/fidelity_credit_card/$date.pdf";
    Log::info("pdfFile=$pdfFile");
    if (!file_exists($pdfFile)) return ['status' => "Fidelity Credit Card Statement of $date not exists"];
    $lines = $this->parseFidelityStatememt($pdfFile);
    if (count($lines) == 0) {
      // Log::info("getCreditCardData $bank [$pdfFile] parsing error, no lines");
      return ['status' => 'no data lines -- PDF parsing error?'];
    }
    // $lines = $this->parsePDF($pdfFile);
    if (is_string($lines) or count($lines) <= 0) return ['info' => $lines, 'status' => 'NO_FILE'];
    // Log:info("getStatementData $bank $date", $lines);
    $this->writeToTempFile($date, $lines);
    [$data, $year, $mont] = $this->getStatementData($lines);
    // Log:info("getStatementData $bank $date, $year, $mont", $lines);
    // $noDBmatch = $this->chkDBforMatch($data['purchases'], $year, $mont);
    $noDBmatch = $this->chkDBforMatch($data);
    $data['noDBmatch'] = $noDBmatch;
    return ['data' => $data, 'status' => "OK"];
  }
  private function getStatementData($lines) { Log::info('-fn-getStatementData');
    $data = [ 'purchases' => []];
    $year = 0;
    $mont = 0;
    $credits = [];
    $crDates = [];
    $prevYear = null;
    for ($i = 0; $i < count($lines); $i++) {
      $line = $lines[$i];
      if (preg_match('/^Open Date/', $line)) {
        // $x = ['open' => $lines[$i + 1] . $lines[$i + 2] . $lines[$i + 3] . $lines[$i + 4] ];
        $ol = explode('Closing Date', $lines[$i + 1]);
        $x = [ 'open' => $ol[0]];
        // $x = ['open' => '2024-01-01'];
        $data = array_merge($data, $x);
        $x = [ 'clos' => $lines[$i + 2]];
        $data = array_merge($data, $x);
        $year = explode('/', $data['clos'])[2];
        $mont = explode('/', $data['clos'])[0];
        $data = array_merge($data, $x);
      // } else if ($line == 'Account Number') {
      } else if (preg_match('/^Account Number/', $line)) {
        $x = [ 'acct' => $lines[$i + 1] ];
        $data = array_merge($data, $x);
        Log::debug("==CK Account Number[$line] == YEAR: $year, MONTH: $mont ", $data);
      } else if ($line == 'New Balance') {
        $x = [ 'balc' => $lines[$i + 1] ];
        $data = array_merge($data, $x);
      } else if (preg_match('/^Previous Balance\s+/', $line)) {
        $x = [ 'preb' => explode(' + ', $line)[1] ];
        $data = array_merge($data, $x);
      } else if ($line == 'Payments' and !isset($data['paym'])) {
        $x = [ 'paym' => $lines[$i + 2] ];
        $data = array_merge($data, $x);
      } else if ($line == 'Other Credits') {
        // $cr = $lines[$i + 1];
        // $cred = null;
        // if ($cr == '+' or $cr == '-') $cred = $this->cleanMoney($lines[$i + 2]);
        // Log::info("Other Credits cr=[" . $cr . "] linei+2=[" . $lines[$i + 2] . "] cred=[" . $cred ."] line=".__LINE__);
        // $x = ['cred' => $cr . is_numeric($cred) ? $cred : 0];
        // $x = ['cred' => $cr . $cred ];
        // $x = ['cred' => $cr];
        $cred = $this->cleanMoney($lines[$i + 1]);
        $x = ['cred' => $cred ];
        $data = array_merge($data, $x);
      } else if ($line == 'Other Debits') {
        $x = [ 'debt' => $this->cleanMoney($lines[$i + 1]) ];
        $data = array_merge($data, $x);
      } else if ($line == 'Other Credits') {
        $x = [ 'cred' => $this->cleanMoney($lines[$i + 1]) ];
        $data = array_merge($data, $x);
        // } else if ($line == 'Transaction Description') {
          //   $de = $this->cleanMoney($lines[$i + 1]);
          //   $x = ['debt' => is_numeric($de) ? $de : '0'];
          //   $data = array_merge($data, $x);
          // } else if (preg_match('/RETURN/', $line)) {
            //   $credit = $this->cleanMoney($lines[$i + 1]);
            //   $credit_date = $lines[$i - 3];
            //   // Log::debug("cred=$cred cred_date=$cred_date LINE=".__LINE__);
            //   // $x = ['credits' => $credits[] = $credit, 'cred_date' => $cred_date . "/$year"];
            //   // $x = ['credits' => array_push($credits, $credit), 'credit_dates' => $crDates[] = $credit_date];
            //   $credits[] = (float)$credit;
            //   $crDates[] = $credit_date . "$prevYear";
            //   // Log::debug("credits data LINE=".__LINE__, $x);
            //   // $data = array_merge($data, $x);
          } else if ($line == 'Fees Charged') {
            // $fe = $this->cleanMoney($lines[$i + 1]);
            // if ($fe == '+' or $fe == '-') $x = [ 'fees' => $fe . $this->cleanMoney($lines[$i + 2]) ];
            // else $x = [ 'fees' => $this->cleanMoney($lines[$i + 1]) ];
            $x = [ 'fees' => $this->cleanMoney($lines[$i + 1]) ];
            $data = array_merge($data, $x);
          } else if ($line == 'RETURNED PAYMENT FEE') {
            // $fe = $this->cleanMoney($lines[$i + 1]);
            // if ($fe == '+' or $fe == '-') $x = [ 'fees' => $fe . $this->cleanMoney($lines[$i + 2]) ];
            // else $x = [ 'fees' => $this->cleanMoney($lines[$i + 1]) ];
            $x = [ 'fees' => $lines[$i - 1] .' RETURNED PAYMENT FEE '. $this->cleanMoney($lines[$i + 1]) ];
            $data = array_merge($data, $x);
          } else if ($line == 'Interest Charged') {
            // $x = $this->cleanMoney($lines[$i + 1]);
            // if ($x == '-')
            $x = $this->cleanMoney($lines[$i + 1]);
            $data = array_merge($data, ['inst' => $x]);
          // } else if (preg_match('/\d\d\/\d\d/', $line) and preg_match('/\d\d\/\d\d/', $lines[$i + 1])) { // purchase data
          } else if (preg_match('/\d\d\/\d\d\d\d\/\d{2}MTC/', $line)) { // purchase data
            $postdate = substr($line, 0, 5);
            $trandate = substr($line, 5, 5);
            $ref      = 'MTC';
            $desc = 'PAYMENT THANK YOU';
            $paym = $this->cleanMoney($lines[$i + 4]);
            array_push($data['purchases'], Array($postdate, $trandate, $ref, $desc, $paym));
            Log::debug("==== LINE[$line]=== DEBUGing", $data);
          } else if (preg_match('/\d\d\/\d\d\d\d\/\d{6}/', $line)) { // purchase data
            $postdate = substr($line, 0, 5);
            $trandate = substr($line, 5, 5);
            $ref      = substr($line, 10, 4);
            // $desc     = preg_replace('/MERCHANDISE\/SERVICE/', '', $lines[$i + 3]);
            // if (preg_match('/REVERSAL/', $ref) == 1) {
            //   $data = array_merge($data, ['revs' => $lines[$i + 3]]);
            //   break;
            // }
            $desc = '';
            $cost = null;
            for ($j=1; $j<10; $j++) {
              if (preg_match('/^\$\d{1,}/', $lines[$i + $j])) {
                $cost = $this->cleanMoney($lines[$i + $j]);
                break;
              } else {
                $desc .= ' ' .  $lines[$i + $j];
              }
            }
            array_push($data['purchases'], Array($postdate, $trandate, $ref, $desc, $cost));


        // } else if (preg_match('/\d\d\/\d\d/', $line) and preg_match('/\d{1,}/', $lines[$i + 1]) and preg_match('/CREDIT\s+ADJUSTMENT/', $lines[$i + 2])) { // credit adjust line
        //   $postdate = $line;
        //   $trandate = $line;
        //   $ref      = $lines[$i + 1];
        //   $desc     = $lines[$i + 2] . ' RETURN';
        //   $cost     = $lines[$i + 3];
        //   array_push($data['purchases'], Array($postdate, $trandate, $ref, $desc, $cost));
        // } else if (preg_match('/\d\d\/\d\d/', $line) and preg_match('/\d{1,}/', $lines[$i + 1]) and preg_match('/DEBIT\s+ADJUSTMENT/', $lines[$i + 2])) { // debit adjust line
        //   $postdate = $line;
        //   $trandate = $line;
        //   $ref      = $lines[$i + 1];
        //   $desc     = $lines[$i + 2];
        //   $cost     = $lines[$i + 3];
        //   // Log::info("cost=[$cost]");
        //   array_push($data['purchases'], Array($postdate, $trandate, $ref, $desc, $cost));
      }
    }
    if (count($credits) > 0) {
      Log::debug("credits:", $credits);
      Log::debug("crDates:", $crDates);
      $data = array_merge($data, ['credits' => $credits]);
      $data = array_merge($data, ['crDates' => $crDates]);
    }
    return [$data, $year, $mont];
  }
  public function pythonVersiongetCreditCardData($date, $bank) { Log:info("getData $bank $date");
    $process = new Process(['/usr/bin/python', config('constants.USER_SITE') . '/devx/public/recon/pcc', '-d', $date, '-s', 'showDa']);
    $process->start();
    foreach ($process as $type => $data) {
      if ($process::OUT === $type) {
        $da = $data;
      } else { // $process::ERR === $type
        Log::info("\nRead from stderr: ".$data);
        exit(0);
      }
    }
    $stda = [];
    $lines = explode(']', $da)[2];
    foreach($lines as $line) {
      $x = explode('[', $line);
      if (count($x) > 1) array_push($stda, explode(',', $x[1]));
    }
    return ['data' => $stda, 'status' => "OK"];
  }
  public function addAssets(Request $da) { //Log::info('addAssets', $da->toArray());
    $userId = Auth::user()->id;
    $dm = new BankStatementAsset($da->toArray());
    $dm->user_id = $userId;
    $dm->upsert(
      ['user_id'=>$dm->user_id, 'bank'=>$dm->bank, 'year'=>$dm->year, 'month'=>$dm->month,
        'begin_date'=>$dm->begin_date, 'end_date'=>$dm->end_date, 'primary_account'=>$dm->primary_account,
        'tran_cnt'=>$dm->tran_cnt, 'begin_balance'=>$dm->begin_balance, 'end_balance'=>$dm->end_balance],
      ['user_id', 'bank'. 'year', 'month'],
      ['begin_date', 'end_date', 'primary_account', 'tran_cnt', 'begin_balance', 'end_balance']
    );

    return [ 'status' => "OK" ];
  }
  // public function addAssets(Request $da) { //Log::info('addAssets', $da->toArray());
  //   $userId = Auth::user()->id;
  //   $dm = new BankStatementAsset($da->toArray());
  //   $dm->user_id = $userId;
  //   $dm->save();
  //   return [ 'status' => "OK" ];
  // }
  public function addHoldings(Request $da) { //Log::info('addHoldings', $da->toArray());
    $userId = Auth::user()->id;
    foreach($da->toArray() as $d) {
      $dm = new BankStatementHolding($d);
      $dm->upsert(
        [
          ['bank'=>$dm->bank, 'year'=>$dm->year, 'month'=>$dm->month, 'account_num'=>$dm->account_num, 'account_name'=>$dm->account_name,
           'symbol'=>$dm->symbol, 'start_balance'=>$dm->start_balance, 'quantity'=>$dm->quantity, 'price'=>$dm->price,
           'end_balance'=>$dm->end_balance, 'cost'=>$dm->cost, 'eai'=>$dm->eai, 'ey'=>$dm->ey, 'user_id'=>$userId]
        ],
        ['user_id', 'bank'. 'year', 'month', 'account_num', 'symbol'], // UBYMAS_idx
        ['start_balance', 'quantity', 'price', 'end_balance', 'cost', 'eai', 'ey']
      );
    }
    return [ 'status' => "OK" ];
  }
  // public function addHoldings(Request $da) { Log::info('addHoldings', $da->toArray());
  //   $num_deleted_records = BankStatementHolding::where([['year', $da['year']], ['month', $da['month']], ['account_num', $da['account_num']]])->delete();
  //   Log::info("addHoldings $num_deleted_records records deleted first to cleanup");
  //   $userId = Auth::user()->id;
  //   foreach($da->toArray() as $d) {
  //     $dm = new BankStatementHolding($d);
  //     $dm->user_id = $userId;
  //     $dm->save();
  //   return [ 'status' => "OK" ];
  // }
  // public function addActivity(Request $da) { // Log::info('addActivity', $da->toArray());
  //   $userId = Auth::user()->id;
  //   foreach($da->toArray() as $d) {
  //     $dm = new BankStatementActivity($d);
  //     $dm->user_id = $userId;
  //     if ($dm->amount == '-') $dm->amount = null;
  //     if ($dm->price == '-') $dm->price = null;
  //     $dm->save();
  //   }
  //   return [ 'status' => "OK" ];
  // }
  public function addActivity(Request $da) { // Log::info('addActivity for All Banks(Fidelity/Chase/BOA)', $da->toArray());
    $userId = Auth::user()->id;
    foreach($da->toArray() as $d) {
      $dm = new BankStatementActivity($d);
      $dm->user_id = $userId;
      if ($dm->amount == '-') $dm->amount = null;
      if ($dm->price == '-') $dm->price = null;
      $dm->upsert([
                    ['idx'=>$dm->idx, 'bank'=>$dm->bank, 'year'=>$dm->year, 'month'=>$dm->month, 'account_num'=>$dm->account_num,
                    'account_name'=>$dm->account_name, 'sett_date'=>$dm->sett_date, 'security'=>$dm->security,
                    'description'=>$dm->description, 'amount'=>$dm->amount,
                    'price'=>$dm->price, 'cost'=>$dm->cost, 'user_id'=>$userId]
        ],
        ['bank', 'user_id', 'year', 'month', 'account_num', 'idx', 'security'],
        ['sett_date', 'description', 'amount', 'price', 'cost']
      );
    }
    return [ 'status' => "OK" ];
  }
}

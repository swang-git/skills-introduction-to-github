<?php
namespace App\Http\Controllers;

use App\Models\bankstatement\SecurityMeta;
use App\Models\bankstatement\StockQuote;
use App\Models\bankstatement\BankStatementHolding;
use App\Models\bankstatement\BankStatementAsset;
use App\Models\bankstatement\BankStatementNote;
use App\Models\bankstatement\BankAccountActivity;
use App\Models\bankstatement\BankStatementActivity;
use App\Models\bankstatementloader\BankSecurityHolding;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class BankStatementController extends Controller
{
	public function __construct() {
		$this->middleware('auth');
	}
	/**
	 * [index description]
	 * @method index
	 * author: swang
	 * created at 2017-05-10T16:31:40-040
	 * revised at 2017-05-10T16:31:40-040
	 * version [version]
	 * @return [type] [description]
	 */
	public function index() {
		// $this->middleware('auth');
	}
	public function getList() {
		$userId = Auth::user()->id;
		$hideSql = 'select true';
		$diffSql = 'end_balance - begin_balance';
		$mnthSql = 'CASE when month < 10 then concat("0", month) else month END';
		$dats = BankStatementAsset::where([ ['status', 'A'], ['user_id', $userId], ['bank', '!=', 'NAC'] ])
				->select('id', 'user_id', 'year', 'begin_date', 'end_date', 'tran_cnt', 'bank', 'primary_account', 'begin_balance', 'end_balance')
				->selectSub($hideSql, 'hideIt')
				->selectSub($diffSql, 'diff')
				->selectSub($mnthSql, 'month')
				->orderBy('year', 'desc')
				->orderBy('month', 'desc')
				->orderBy('bank', 'desc')->get();
		// Log::info('getList', $dats->toArray());
		$latestmon = BankStatementHolding::where([['status', 'A'], ['user_id', 1]])
				->select('year', 'month')
				->orderBy('year', 'desc')
				->orderBy('month', 'desc')->limit(1)->get()[0];
		// Log::info('Last Month', [$latestmon->year, $latestmon->month]);
		$year = $latestmon->year;
		$mnth = $latestmon->month;
		// Log::info('Latest Month for BankStatementHoldings(Fidelity Only)', [$year, $mnth]);
		$stocks = 0.0;
		$indvStocks = BankStatementHolding::select('symbol', 'end_balance')
				->where([['status', 'A'], ['user_id', 1], ['year', $year], ['month', $mnth], ['account_num', 'Z71-367818']])
				->whereNotIn('symbol', ['FNJXX', 'SPAXX'])->get();

		foreach($indvStocks as $h) {
			$stocks += $h->end_balance;
			// Log::info($h->symbol, [$h->end_balance]);
		}

		$fidel_cash = 0.0;
		$holds = BankStatementHolding::select('symbol', 'end_balance')
				->where([['status', 'A'], ['user_id', 1], ['year', $year], ['month', $mnth]])
				->whereIn('account_num', ['X85-275143', 'Z71-367818'])
				->get();

		foreach($holds as $h) {
			// if (in_array($h->symbol, ['SPAXX', 'SPRXX', 'FZDXX', 'FDRXX', 'QBNYQ', 'FNJXX', 'QPCBQ']))
			$fidel_cash += $h->end_balance;
		}

		// $year = date('Y'); // get Annuity in North America Company (NAC)
		// $nacd = BankStatementAsset::where([ ['status', 'A'], ['user_id', $userId], ['bank', 'NAC'], ['year', $year] ])
		$nacd = BankStatementAsset::where([ ['status', 'A'], ['user_id', $userId], ['bank', 'NAC'] ])
		->select('id', 'user_id', 'year', 'begin_date', 'end_date', 'tran_cnt', 'bank', 'primary_account', 'begin_balance', 'end_balance')
		->selectSub($hideSql, 'hideIt')
		->selectSub($diffSql, 'diff')
		->selectSub($mnthSql, 'month')
		->orderBy('year', 'desc')
		->orderBy('month', 'desc')
		->orderBy('bank', 'desc')
    ->limit(1)->get();

		// Log::info("year=$year money market(cash in Fidelity Personal Accounts Z71 and X85)=$fidel_cash", $nacd->toArray());
		Log::info("year=$year nacd=", $nacd->toArray());
		// $datsAr = $dats->toArray()[0];
    if (count($nacd) > 0) {
      $month = $dats[0]->month; // Log::info("-CK-XXX month=$month");
      $month = $dats[0]->month; // Log::info("-CK-XXX month=$month");
      // $nacd[0]->month = date('m') + 1;
      $dats = $nacd->merge($dats);
    }
		[$bkgd, $cash, $intraday] = $this->getChaseBkgAssets();
		$dats = $bkgd->merge($dats); Log::info("-CK-XXX cash=$cash", $bkgd->toArray());
		$stock_val = $bkgd[0]->end_balance - $cash;
		return ['stocks_val' => $stock_val, 'fidel_cash' => $fidel_cash, 'dats' => $dats, 'chase_cash' => $cash, 'intraday' => $intraday, 'status' => "OK" ];
	}
	private function getChaseBkgAssets() { Log::info("getChaseBkgAssets");
		$loadTime = substr(StockQuote::where('status', 'A')->max('load_time'), 0, 10);
		$da = DB::table('stock_quotes as s')
			->where('load_time', 'like', "%$loadTime%")
			->where('m.symbol', '<>', 'BEKE')
			->join('security_metas as m', 'm.symbol', 's.symbol')
			->select('s.symbol', 's.price', 's.price_change', 'm.quantity')
      ->orderBy('load_time', 'desc')
      ->limit(6)
			->get();
		$cnt = count($da);
		Log::info("stocks: loadTime=$loadTime cnt=$cnt");
		$begin_balance = 0;
		$end_balance = 0;
		foreach($da as $d) {
			Log::info("$d->symbol $d->price $d->price_change $d->quantity");
			$begin_balance += ($d->price - $d->price_change) * $d->quantity;
			$end_balance += $d->price * $d->quantity;
		}
		$cash = 40465.51;
		$bkgd = StockQuote::select(DB::raw("1 as user_id") , DB::raw("'BKG' as bank"),
				DB::raw("DATE_FORMAT(load_time, '%Y') as year"),
				DB::raw("DATE_FORMAT(load_time, '%m') as month"),
				DB::raw("'985 10278' as primary_account"),
				DB::raw("0 as tran_cnt"),
				DB::raw("DATE_FORMAT(load_time, '%Y-%m-%d') as begin_date"),
				DB::raw("'$loadTime' as end_date"),
				// DB::raw("DATE_FORMAT(load_time, '%Y-%m-%d') as end_date"),
				DB::raw("true as hideIt"),
				DB::raw("$end_balance - $begin_balance as diff"),
				DB::raw("$begin_balance + $cash as begin_balance"),
				DB::raw("$end_balance + $cash as end_balance"))
			->orderByDesc('load_time')->limit(1)->get();
		Log::info("bkgd=", $bkgd->toArray());
		return [$bkgd, $cash, str($loadTime, 0, 10)];
	}
	public function getDetails($bank, $year, $month) { Log::info('BankStatement - getDetails', [$bank, $year, $month]);
		$userId = Auth::user()->id;
		$notes = BankStatementNote::where([ ['status', 'A'], ['user_id', $userId], ['bank', $bank], ['year', $year], ['month', $month] ])
				->select('id', 'user_id', 'year', 'month', 'note_id', 'notes', 'amount', 'bank')->get();

		if ($bank == 'Chase') {
			$chkactvs = BankAccountActivity::where([ ['status', 'A'], ['user_id', $userId], ['bank', $bank], ['year', $year], ['month', $month], ['acct_type', 'Checking'] ])
					->select('id', 'user_id', 'year', 'month', 'bank', 'account_num', 'acct_type', 'tran_num', 'tran_date', 'description', 'begin_balance', 'amount', 'end_balance')
					->orderBy('tran_date')->get();

					$savactvs = BankAccountActivity::where([ ['status', 'A'], ['user_id', $userId], ['bank', $bank], ['year', $year], ['month', $month], ['acct_type', 'Savings'] ])
					->select('id', 'user_id', 'year', 'month', 'bank', 'account_num', 'acct_type', 'tran_num', 'tran_date', 'description', 'begin_balance', 'amount', 'end_balance')
					->orderBy('tran_date')->get();
		} else if ($bank == 'BOA') {
			$chkactvs = BankAccountActivity::where([ ['status', 'A'], ['user_id', $userId], ['bank', $bank], ['year', $year], ['month', $month], ['acct_type', 'Checking'] ])
					->select('id', 'user_id', 'year', 'month', 'bank', 'account_num', 'acct_type', 'tran_num', 'tran_date', 'description', 'begin_balance', 'amount', 'end_balance')
					->orderBy('tran_num', 'asc')->get();
			$savactvs = BankAccountActivity::where([ ['status', 'A'], ['user_id', $userId], ['bank', $bank], ['year', $year], ['month', $month], ['acct_type', 'Savings'] ])
				->select('id', 'user_id', 'year', 'month', 'bank', 'account_num', 'acct_type', 'tran_num', 'tran_date', 'description', 'begin_balance', 'amount', 'end_balance')
				->orderBy('tran_num', 'asc')->get();
		}

		return ['notes' => $notes, 'chkactvs' => $chkactvs, 'savactvs' => Collect($savactvs), 'status' => "OK"  ];
	}

	public function getDICLists($bank, $year) { //Log::info('BankStatement - getHoldings', [$bank, $acNum, $year, $month]);
		$userId = Auth::user()->id;
		if ($bank == 'Fidelity') {
			// $DsubSql = "select 'dvd'";
			$dividends = BankStatementActivity::select('amount', 'year', 'month', 'account_name', 'account_num', 'security')
				->selectSub($DsubSql, 'DorI')
				->where([['status', 'A'], ['user_id', $userId], ['year', $year], ['description', 'like', '%DIVIDEND RECEIVED%']])
				->get();
			// $IsubSql = "select 'int'";
			$interests = BankStatementActivity::select('amount', 'year', 'month', 'account_name', 'account_num', 'security')
			->selectSub($IsubSql, 'DorI')
			->where([['status', 'A'], ['user_id', $userId], ['year', $year], ['description', 'like', '%INTEREST EARNED%']])
			->get();
			// $CsubSql = "select 'ccd'";
			$cardrvs = BankStatementActivity::select('amount', 'year', 'month', 'account_name', 'account_num', 'security')
				->selectSub($CsubSql, 'DorI')
				->where([['status', 'A'], ['user_id', $userId], ['year', $year], ['security', 'CardRv']])
				->get();
		}
		// Log::info('dividend', $dividends->toArray());
		// $dividend = 0;
		// foreach($dividends as $o) $dividend += $o->amount;
		// $interest = 0;
		// foreach($interests as $o) $interest += $o->amount;
		return ['dvd' => $dividends, 'int' => $interests, 'ccd' => $cardrvs, 'status' => "OK"];
	}

	public function getHoldings($bank, $year, $month) { Log::info('BankStatement - getHoldings', [$bank, $year, $month]);
		$userId = Auth::user()->id;
		if ($bank == 'Fidelity') {
			$diffSql = 'end_balance - start_balance';
			$costSql = 'CASE when isNull(cost) then "-" else cost END';
			$holdings = BankStatementHolding::where([['status', 'A'], ['user_id', $userId], ['bank', $bank], ['year', $year], ['month', $month]])
					->select('symbol', 'account_name', 'account_num', 'quantity', 'price', 'start_balance', 'end_balance')
					->selectSub($diffSql, 'diff')
					->selectSub($costSql, 'cost')
					->orderBy('account_num')
					->get();
			$actv = BankStatementActivity::where([ ['user_id', $userId], ['bank', $bank], ['year', $year], ['month', $month], ['status', 'A'] ])
			->select('bank', 'year', 'month', 'account_name', 'account_num', 'description', 'sett_date', 'security', 'amount')
			->orderBy('account_name')
			->orderBy('security')
			->orderBy('sett_date')
			// ->orderBy('idx')
			->get();
			// if (count($actv) <= 0) {
			// 	return $this->getHoldings($bank, $year, --$month)
			// }
		}
		return ['holdings' => Collect($holdings), 'actv' => $actv, 'status' => "OK" ];
	}

	//////////////////////////////////////////////////////////////
	/**
	 * This is a very simple function to calculate the difference between two datetime values,
	 * returning the result in seconds. To convert to minutes, just divide the result by 60.
	 * In hours, by 3600 and so on.
	 * @method time_diff
	 * author: swang
	 * created at 2017-05-17T01:15:50-040
	 * revised at 2017-05-17T01:15:50-040
	 * version [version]
	 * @param  [datetime] $dt1 [ in format YYYY-mm-dd 13:23:14]
	 * @param  [datetime] $dt2 [ in format YYYY-mm-dd 13:23:14]
	 * @return [diff in number of seconds]   [$dt1 - $dt2]
	 */
	private function XXXtime_diff($dt1, $dt2){
		$y1 = substr($dt1,0,4);
		$m1 = substr($dt1,5,2);
		$d1 = substr($dt1,8,2);
		$h1 = substr($dt1,11,2);
		$i1 = substr($dt1,14,2);
		$s1 = substr($dt1,17,2);

		$y2 = substr($dt2,0,4);
		$m2 = substr($dt2,5,2);
		$d2 = substr($dt2,8,2);
		$h2 = substr($dt2,11,2);
		$i2 = substr($dt2,14,2);
		$s2 = substr($dt2,17,2);

		$r1=date('U',mktime($h1,$i1,$s1,$m1,$d1,$y1));
		$r2=date('U',mktime($h2,$i2,$s2,$m2,$d2,$y2));
		return ($r1-$r2);
	}
	/**
	 * [index description]
	 * @method index
	 * author: swang
	 * created at 2017-05-10T16:31:40-040
	 * revised at 2017-05-10T16:31:40-040
	 * version [version]
	 * @return [type] [description]
	 */
	private function XXXsetDueIn($d) {
		$dueDate = substr($d['due date'], 0, 10);
		$today = Date('Y-m-d', time());
		$dueInDays = $this->time_diff($dueDate, $today) / (60*60*24);
		if ($dueInDays > 365) $d['due in'] = sprintf('%2.1f', $dueInDays / 365) . "年";
		else if ($dueInDays > 30) $d['due in'] = sprintf('%2.1f', $dueInDays / 30) . "月";
		else if ($dueInDays == 1) $d['due in'] = "Due TOMORROW";
		else if ($dueInDays == 0) $d['due in'] = "Due TODAY !!!";
		else if ($dueInDays < 0) $d['due in'] = "过  期";
		else $d['due in'] = "$dueInDays 天";
	}
}

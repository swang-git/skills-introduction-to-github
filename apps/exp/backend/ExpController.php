<?php
namespace App\Http\Controllers;
use App\Models\expense\Spend;
use App\Models\expense\Category;
use App\Models\expense\Subcategory;
use App\Models\expense\Payee;
use App\Models\expense\PayMethod;
use App\Models\expense\ShoppingPurchase;
use App\Models\expense\GiftCard;
use App\Models\expense\GiftCardBalance;
use App\Models\expense\GiftCardBalancesView;
use App\Models\expense\GCB_view;
use App\Models\golf\Score;
use App\Models\golf\Course;
use App\Models\golf\CourseHandicap;
use App\Models\golf\CoursePar;
use App\Models\golf\CourseYard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent;
use DateTime;
use DateTimeZone;

// \Config::set('database.default', 'prod');

// header("Access-Control-Allow-Origin: http://devx");

class ExpController extends Controller {
	// private $origCost = null;
	private $giftCardIds = [9, 15];  // 9 = Mercer County Golf Gift Card, 15 = Somerset County Golf Gift Card
	// private $lowerBound = null;
	// private $origPaymId = null;
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
		// return view('Fin.spense');
	}
  public function getGiftCards() { Log::info("getGiftCards");
    // $giftcards = GiftCard::where('status', 'A')->select('dtm as start_time', 'paym_id', 'name', 'value as start_value', 'card_num')->orderBy('dtm', 'desc')->get();
    // $giftcards = GiftCard::where('status', 'A')->select('paym_id', 'name as slbl', 'card_num')->orderBy('dtm', 'desc')->get();
    $giftcards = GiftCard::where('status', 'A')->select('paym_id', 'name as slbl')->distinct()->get();
    $usedcards = [];
    $idx = 0;
    foreach ($giftcards as $card) {
      // $paymId = GiftCardBalance::where([['status', 'A'], ['pay_method_id', $card->paym_id], ['card_num', $card->card_num]])->value('pay_method_id');
      // if ($paymId > 0) {
        $card->sval = $idx++;
        $usedcards[] = $card;
        // }
      }
      $cardNums09 = GiftCard::where([['status', 'A'], ['paym_id', 9]])->select('card_num as slbl')->orderBy('dtm', 'desc')->get();
      $cardNums15 = GiftCard::where([['status', 'A'], ['paym_id', 15]])->select('card_num as slbl')->orderBy('dtm', 'desc')->get();
      $idx = 0; foreach ($cardNums09 as $num) $num->sval = $idx++;
      $idx = 0; foreach ($cardNums15 as $num) $num->sval = $idx++;

    return ['giftCards' => $usedcards, 'cardNums09' => $cardNums09, 'cardNums15' => $cardNums15, 'status' => "OK"];
    // return ['giftcards' => $giftcards, 'status' => "OK"];
  }
  public function getGiftCardBalances($paymId, $giftCardNum, $prevCardNum) { Log::info("getGiftCardBalances($paymId, $giftCardNum, $prevCardNum)");
    $balances = DB::select("CALL get_golf_gift_card_balances(?, ?)", [$paymId, $giftCardNum]);
    $prevBalance = GiftCardBalance::where([['pay_method_id', $paymId], ['card_num', $prevCardNum]])->orderBy('spend_datetime', 'desc')->limit(1)->value('balance');
    $cardValue = GiftCard::where([['paym_id', $paymId], ['card_num', $giftCardNum]])->value('value');

    // $balances = GiftCardBalancesView::where([['paymId', $paymId], ['card_num', $giftCardNum]])->select('spendId', 'spendDate', 'cost', 'balance', 'card_num')->get();
    // $prevBalance = GiftCardBalancesView::where([['paymId', $paymId], ['card_num', $prevCardNum]])->orderBy('spendDate', 'desc')->limit(1)->value('balance');
    return ['balances' => $balances, 'prevBalance'=> $prevBalance, 'cardValue'=>$cardValue, 'status' => "OK"];
  }
  // public function getGiftCardBalances($paymId, $giftCardNum, $prevCardNum) { Log::info("getGiftCardBalances($paymId, $giftCardNum, $prevCardNum)");
  //   $balances = GiftCardBalancesView::where([['paymId', $paymId], ['card_num', $giftCardNum]])->select('spendId', 'spendDate', 'cost', 'balance', 'card_num')->get();
  //   $prevBalance = GiftCardBalancesView::where([['paymId', $paymId], ['card_num', $prevCardNum]])->orderBy('spendDate', 'desc')->limit(1)->value('balance');
  //   return ['balances' => $balances, 'prevBalance'=> $prevBalance, 'status' => "OK"];
  // }
  public function checkBalance($paymId) { Log::info("checkBalance($paymId)");
    $gcbs = GCB_view::where('paymId', $paymId)->select('spendId', 'spendDate', 'cost', 'balance')->limit(9)->get();
    $idx = 0;
    $pcost = $gcbs[0]->cost;
    $pbals = $gcbs[0]->balance;
    foreach ($gcbs as $b) {
      // Log::info($b->spendId, [$b->spendDate, $b->cost, $b->balance]);
      if ($idx >= 1) {
        Log::info($b->spendId, ['spendDate' => $b->spendDate, 'check' => $b->balance - $pcost - $pbals]);
        $pcost = $b->cost;
        $pbals = $b->balance;
      }
      $idx++;
    }
  }
  public function getSpending($spendId) { Log::info("getSpending $spendId");
    $userId = Auth::user()->id;
    $spending = DB::select('CALL get_spending(?, ?)', [$userId, $spendId]); 
    return [ 'spending' => $spending[0], 'status' => "OK"];
  }
  public function addSpend(Request $d) { Log::info('addSpend', $d->toArray());
		$d->id = null;
		$dx = $this->getModel($d, 'add');
    $dx->save();
    if (in_array($d->paymId, $this->giftCardIds, true)) {
      $d->id = $dx->id;
      Log::debug("add new GCB Record for [spend_id=$d->id, purchasedon=$d->purchasedon, cost=$d->cost, gcardNum=$d->gcardNum]",[__line__]);
      $gc = new GiftCardBalance;
      $gc->spend_datetime = $d->purchasedon;
      $gc->spend_id = $d->id;
      $gc->pay_method_id = $d->paymId;
      $gc->balance = 666.66; // to be updated in updGCBalances
      $gc->card_num = $d->gcardNum;
      $gc->save();
      $this->updGCBalances($d->purchasedon, $d->paymId, $d->gcardNum, $d->cost);
    }
    $da = $this->getList($dx->id);
    $da['lst'][0]->add_upd = true; // for old expense
    $da['lst'][0]->add = true; // for new exp/exlist
    return ['row' => $da['lst'][0], 'status' => "OK"];
	}
}

<?php
namespace App\Http\Controllers;
// header("Access-Control-Allow-Origin: http://devx:8080");
// header("Access-Control-Allow-Origin: *");
// headers(
// 	"Access-Control-Allow-Origin": "*",fv
// 	"Access-Control-Allow-Credentials": "true",
// 	"Access-Control-Allow-Methods": "GET,HEAD,OPTIONS,POST,PUT",
// 	"Access-Control-Allow-Headers": "Origin, X-Requested-With, Content-Type, Accept, Authorization"
// )
use App\Models\expense\Spend;
use App\Models\expense\Category;
use App\Models\expense\Subcategory;
use App\Models\expense\Payee;
use App\Models\expense\PayMethod;
use App\Models\expense\ShoppingPurchase;
use App\Models\expense\GiftCard;
use App\Models\expense\GiftCardBalance;
use App\Models\golf\Score as Score;
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

class ExpenseController extends Controller {
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
	public function getSpending($spendId) {
		$userId = Auth::user()->id;
		$spending = DB::select("CALL get_spending(?, ?)", [$userId, $spendId]);
		Log::info("getSpending userId=$userId spendId=$spendId", Collect($spending[0])->toArray());
		return ['spending' => Collect($spending), 'status' => "OK"];
	}
	public function getScoreId(Request $da) { Log::info($da);
		// header("Access-Control-Allow-Origin: http://devx:8080");
		$playerId = $da['playerId'];
		$courseId = $da['courseId'];
		$teetime = $da['teetime'];
		$scoreId = null;
		$scoreId = Score::where([ ['player_id', $playerId], ['teetime', $teetime], ['course_id', $courseId] ])->value('id');
		// $x = Score::where([ ['player_id', $playerId], ['teetime', $teetime], ['course_id', $courseId] ])->select('id')->get();
		// if (count($x) == 1) $scoreId = $x[0]->id;
		Log::info("playerId=$da->playerId, scoreId=$scoreId", [__line__, __file__]);
		return [ 'scoreId' => $scoreId, 'status' => "OK" ];
	}
	public function getScore(Request $da) { Log::info("playerId=$da->playerId, scoreId=$da->scoreId");
		$score = DB::select("CALL get_scores(?, ?)", [ $da->playerId, $da->scoreId ])[0]; //Log::info($score);
		$score->pars = CoursePar::where('course_id', $score->courseId)->select('*')->get()[0];
		$score->hcaps = CourseHandicap::where('course_id', $score->courseId)->select('*')->get()[0];
		$score->yards = $this->getCourseYardages($score->courseId, $score->teebox);
		// Log::info(Collect($score));
		return ['status' => "OK", 'playData' => $score];
	}
	public function getCourseYardages($courseId, $mtee) { //Log::info("getCourseYardages($courseId, $mtee)");
		$ltee = null;
		$myards = CourseYard::where([['status', 'A'], ['course_id', $courseId], ['teebox', $mtee]])->select('*')->get();
		if (!is_null($ltee)) $lyards = CourseYard::where([['status', 'A'], ['course_id', $courseId], ['teebox', $ltee]])->select('*')->get();
		// Log::info('courseYardages', [$myards->toArray(), $lyards->toArray()]);
		return [ 'myards' => $myards->toArray()[0], 'lyards' => is_null($ltee) ? null : $lyards->toArray()[0], 'status' => "OK" ];
	}
	public function getCreditCardSpendings($startDay, $endDay, $dueDay) { Log::info("-CK-fn-getCreditCardSpendings($startDay, $endDay, $dueDay)", [__LINE__]);
		$userId = Auth::user()->id;
		$ccStatementId = Spend::where([['status', 'A'], ['purchasedon', $dueDay]])->value('id');
		Log::info("getCreditCardSpending startDay=$startDay endDay=$endDay dueDay=$dueDay ccStatementId=$ccStatementId LINE=".__LINE__);
		$dm = Spend::find($ccStatementId);
		$dm->notes = $startDay . ' ~ ' . $endDay;
		$dm->link = "fidelity_credit_card/$dueDay.pdf";
		$dm->save();
		Log::info("getCreditCardSpending Id=$ccStatementId");
		$ccdata = DB::select('CALL get_credit_card_spendings(?, ?, ?, ?)', [$userId, $startDay, $endDay, $dueDay]);
		return ['ccdata' => $ccdata, 'status' => "OK"];
	}
	public function setReconcile(Request $da) {
		$dm = Spend::find($da->Id);
		$dm->reconciled_at = $da->reconciledAt;
		try {
			$dm->save();
			return ['status' => "OK"];
		} catch(\Exception $ex) {
			return $ex->getMessage();
		}
	}
	public function getPurchasedList($date, $payeeId) { // Log::info("get_purchased_list:, $date, $payeeId");
		$plist = DB::select('CALL get_purchased_list(?, ?)', [$date, $payeeId]);
		return ['lst' => $plist, 'status' => "OK" ];
	}
	public function setStore($pid, $payeeId) { Log::info('setStore', [$pid, $payeeId]);
		$ps = ShoppingPurchase::find($pid);
		$ps->payee_id = $payeeId > 0 ? $payeeId : null;
		$ps->update();
		return ['status' => "OK"];
  }
	private function resetGolfPlayPayeeId($spendId) { Log::info('running resetGolfPlayPayeeId');
		$date30 = mktime(0, 0, 0, date("m"), date("d") + 30, date("Y")); // keep this for referencing
		$mdate = Date('Y-m-d', $date30); // not used - just for referencing
		$userId = Auth::user()->id;
		// Log::info('getList for userId:' . $userId .', email: '. Auth::user()->email .' date30(referencing):'.$mdate);
		$dats = DB::select('CALL get_spendings(?, ?)', [$userId, $spendId]);
		// Log::info("get_spendings for userId $userId:", [$dats[0]->date]);
		foreach($dats as $d) {
			$date = $d->date;
			$catsId = $d->catsId;
			$subcId = $d->subcId;
			$subc = $d->subc;
			// if ($date >= '2022-09-10' or $catsId != 2 or $subcId != 4) continue;
			// if (!($date<'2022-09-10' and $catsId==2 and ($subcId==4 or str_contains($subc, 'Tournament') or str_contains($subc, 'Playoff')))) continue;
			$course_related = ($catsId==2 and (str_contains($subc,'Play') or str_contains($subc,'Tournament') or str_contains($subc,'Playoff')));
			// $course_related = ($catsId==2 and (str_contains($subc,'Play') or str_contains($subc,'Tournament') or str_contains($subc,'Playoff') or str_contains($subc,'Outing')));
			if ($date >= '2022-09-10') continue;
			if (!$course_related) continue;
      $payeId = $d->payeId;
			$id = $d->id;
			$paye = trim($d->paye);
			$cats = $d->cats;
			$subc = $d->subc;
			// Log::info("id=$id date=$date payeId=$payeId catId=$catsId cats=$cats subcId=$subcId subc=$subc paye=$paye");
			$courseName = match($paye) {
				'Mountain View' => 'Mountain View Golf Club',
				'Royce Brook Golf Club' => 'Royce Brook East',
				'Mercer Oaks West' => 'Mercer Oaks GC West',
				'Royce Brook GC West' => 'Royce Brook West',
				'Mattwang Golf Club' => 'Mattawang Golf Club',
				'Scottsdale Stadium, Phoenix, Arizona' => 'Scottsdale Stadium Phoenix, AZ',
				'Howell Park Golf' => 'Howell Park Golf Course',
				'High Bridge Hill Golf Club' => 'High Bridge Hill GC',
				'Charleston Springs Golf Club' => 'Charleston Springs South Course',
				'Make Field GC' => 'Makefield Highlands',
				'Eagle Ridge Golf Club Lakewoord' => 'Eagle Ridge GC Ridge and Pine',
				'Fox Hollow GC' => 'Fox Hollow Golf Club',
				'Neshanic Golf Club' => 'Neshanic Valley Ridge and Lake',
				'Neshanic Valley Golf Club' => 'Neshanic Valley Ridge and Lake',
				'Jasna Polana' => 'TPC Jasna Polana Golf Club',
				'TPC Jasna Polana' => 'TPC Jasna Polana Golf Club',
				'HOMINY HILL GOLF COURS COLTS NECK NJ' => 'Hominy Hill Golf Course',
				'Good Fortune Supermarket' => 'Quail Brook Golf Club',
				'Warren Brook Golf' => 'Warrenbrook Golf Course',
				'Seaview Resort & Spa' => 'Seaview GC - Bay Course',
				'Bethpage SP Golf Course Black' => 'Bethpage Black',
				'Bottle King East Windsor' => 'Green Knoll Golf Course',
				'Saving Account' => 'Knob Hill Golf Club',
				'Club at Morgan Hill' => 'Morgan Hill Golf Course',
				'US Airways' => 'Mountain View Golf Club',
				'HERON GLEN GOLF COURSE RINGOES NJ' => 'Heron Glen',
				//'川妹子' => 'Cranbury Golf Club',
				// 'Starbucks' => 'Talamore Country Club',
				'Bulle Rock Golf' => 'Bulle Rock',
				default => 'NoMatchedPaye',
			};

			if ($courseName === 'NoMatchedPaye') {
			// if (strcasecmp($courseName, 'NoMatchedPaye') === 0) {
				Log::info("no matched courseName for paye=[$paye] find course by paye and update payeId id=$id payeId=$payeId catsId=$catsId subc=$subc date=$date");
				$cx = Course::where([['status', 'A'], ['name', $paye]])->select('id', 'name')->get(); //Retrieving A Single Row / Column From A Table -- pluck(name) for a single column
				if (count($cx) == 1) {
					$sp = Spend::find($id);
					$coId = $cx[0]->id;
					$sp->payee_id = $coId;
					$sp->save(); Log::info("=UPD=spend.payee_id updated from $payeId to $coId for course[$paye]");
					continue;
				}
				$dc = Course::find($payeId);
				if ($dc == null) {
					Log::info("=NO=paye=[$paye] Course::find($payeId)=null do update payeId id=$d->id payeId=$payeId catsId=$catsId subc=$subc date=$date");
					$courseName = $paye;
					$this->updPayeId(null, $paye, $id, $subc, $date, $payeId); // could a new course -- need to add it to courses table and update payeId
					continue;
				} else if ($dc->name === $paye) { // both paye/course.name and payeId/course.id matched -- no need to update
					Log::info("=OK=paye=[$paye] - no need to upd dcourseName=[$dc->name] is from CourseDB move to next id=$d->id payeId=$payeId catsId=$catsId subc=$subc date=$date");
					continue;
					// return;
				}
			} else {
				// $courseName = $paye;
				$this->updPayeId($courseName, $paye, $id, $subc, $date, $payeId);  // course name matched, need update payeId to course.id
			}
		}
	}
	private function updPayeId($courseName, $paye, $id, $subc, $date, $payeId) {
		if ($courseName == null) {
			$cx = Course::where([['status', 'A'], ['name', $paye]])->select('id', 'name')->get(); //Retrieving A Single Row / Column From A Table -- pluck(name) for a single column
			if ($cx == null or count($cx) == 0) {
				Log::info("=N=$paye could be a new course - need to add new course and run this again for paye=[$paye] matchedCourse=[$courseName] id=$id subc=$subc date=$date payeId=$payeId $cx");
				return;
			} else if (count($cx) > 1) {
				Log::info("=X=course id and name returned incorrectly for paye=[$paye] matchedCourse=[$courseName] id=$id subc=$subc date=$date payeId=$payeId $cx");
				return;
			}
		}

		$cx = Course::where([['status', 'A'], ['name', $courseName]])->select('id', 'name')->get();
		if ($cx == null or count($cx) == 0) {
			Log::info("=NC=Course=[$courseName] NOT exists in golf.courses -- spid=$id date=$date");
		}
		$cname = $cx[0]->name;
		$coId = $cx[0]->id;
		if (strcasecmp($courseName, $cname) != 0) {
			Log::info("=CK=nCourseName=[$courseName]=/=cname=[$cname] NOT exists in golf.courses -- spid=$id date=$date");
			return;
		} else {
			$dx = Spend::find($id);
			if ($dx->payee_id != $payeId) {
				Log::info("=CK=something weard, check it dx->payee_id=/=payeId $id==$dx->id\t payeId=$payeId payee_id=$dx->payee_id\t ==> $coId \t $courseName");
				return;
			}
			if ($payeId != $coId) {
				Log::info("=upd=$dx->purchasedon $id==$dx->id\t payeId=$payeId payee_id=$dx->payee_id\t ==> $coId \t $courseName");
				$dx->payee_id = $coId; // updating
				$dx->save();
			}
		}
	}
	public function getList($spendId=0) { Log::info('exp->getList()');
		// $this->resetGolfPlayPayeeId($spendId);
		$date30 = mktime(0, 0, 0, date("m"), date("d") + 30, date("Y")); // keep this for referencing
		$mdate = Date('Y-m-d', $date30); // not used - just for referencing
		$userId = Auth::user()->id;
		// Log::info('getList for userId:' . $userId .', email: '. Auth::user()->email .' date30(referencing):'.$mdate);
		$dats = DB::select('CALL get_spendings(?, ?)', [$userId, $spendId]);
		// Log::info("get_spendings for userId $userId:", [$dats[0]->date]);
		foreach($dats as $d) {
			// if ($d->date > $mdate) $d->future = true;
			$d->height= 6;
			if ($d->link != null) {
				$doc_dir = config('global.doc_dir');
				$docs = explode('@', $d->link); //Log::info([$d->link, $docs]);
				if (count($docs) == 1) {
					$doc = $docs[0];
					$dname = preg_replace('/_|-/', ' ', $doc);
					if (strlen($dname) > 80) $dname = substr($dname, -77);
					if(filter_var($doc, FILTER_VALIDATE_URL)) $d->lnk = "<a href='$doc' target='_blank'>$dname</a>";
					else $d->lnk = "<a href='$doc_dir/$doc' target='_blank'>$dname</a>";
					$d->height++;
				} else {
					$d->lnk = "<ul>";
					foreach($docs as $doc) {
						$d->height++;
						$dname = preg_replace('/_|-/', ' ', $doc);
						if (strlen($dname) > 80) $dname = substr($dname, -77);
						// Log::info('dname', [$dname]);
						if(filter_var($doc, FILTER_VALIDATE_URL)) $d->lnk .= "<li><a href='$doc' target='_blank'>$dname</a></li>";
						else $d->lnk .= "<li><a href='$doc_dir/$doc' target='_blank'>$dname</a></li>";
					}
					$d->lnk .= "</ul>";
				}
			}
			if ($d->note != null) $d->height++;
		}
		// Log::info("get_spendings for userId $userId:", [$dats[0]->id]);
		return ['lst' => $dats, 'status' => "OK"];
	}
	public function getCatsCombo($catId, $subId) {
		$userId = Auth::user()->id;
		$catOptions = Category::where([['user_id', $userId],['status','A']])->select('id as value', 'name as label')->get();
		$obj = ['value' => -1, 'label' => 'Add New Catetory'];
		$catOptions[] = Collect($obj);

		$subOptions = subCategory::where([['user_id', $userId],['status','A'], ['cat_id', $catId]])->select('id as value', 'name as label')->get();
		$obj = ['value' => -1, 'label' => 'Add New Subcatetory'];
		$subOptions[] = Collect($obj);

		$pyeOptions = payee::where([['user_id', $userId],['status','A'], ['subcat_id', $subId]])->select('id as value', 'name as label')->get();
		$obj = ['value' => -1, 'label' => 'Add New Payee'];	//dd($pyeOptions);
		$pyeOptions[] = $obj;

		$pymOptions = paymethod::where([['user_id', $userId],['status','A']])->select('id as value', 'name as label')->get();
		$obj = ['value' => -1, 'label' => 'Add New Payment Type'];
		$pymOptions[] = $obj;
		// $courseList = $this->getCourseList();
		// return [ 'catOptions' => $catOptions, 'subOptions' => $subOptions, 'pyeOptions' => $pyeOptions, 'pymOptions' => $pymOptions, 'courseList' => $courseList ];
		return [ 'catOptions' => $catOptions, 'subOptions' => $subOptions, 'pyeOptions' => $pyeOptions, 'pymOptions' => $pymOptions, 'status' => "OK" ];
	}
	public function getCourseList() {
		// $da = Course::where('status', 'A')->select('id as value', 'name as label')->orderBy('name')->get();
		$da = DB::select("CALL golf.get_course_list_order_by_frequency()");
		$da[] = ['value' => -1, 'label' => "Add New Course"];
		// return Collect($da);
		return ['lst' => $da, 'status' => "OK"];
	}
	public function getSubcat($catId) {
		$userId = Auth::user()->id;
		$subOptions = subCategory::where([['user_id', $userId],['status','A'], ['cat_id', $catId]])->select('id as value', 'name as label')->get();
		$obj = ['value' => -1, 'label' => 'Add New Subcatetory'];
		$subOptions[] = $obj;
		return ['subcOpt' => $subOptions, 'status' => "OK"];
	}
	public function getPayee($subId) {
		$userId = Auth::user()->id;
		$pyeOptions = payee::where([['user_id', $userId],['status','A'], ['subcat_id', $subId]])->select('id as value', 'name as label')->get();
		$obj = ['value' => -1, 'label' => 'Add New Payee'];
		$pyeOptions[] = $obj;
		return ['lst' => $pyeOptions, 'status' => "OK"];
	}
	private function col_map ($nd, $d) { //Log::info('add input', $d->toArray());
		$subc = $d->subc;
		$nd['user_id'] = Auth::user()->id;
		$nd['purchasedon'] = $d['purchasedon'];
		$nd['post_date'] = $d['post_date'];
		$nd['cat_id'] = $d['catsId'];
		$nd['subcat_id'] = $d['subcId'];
		$nd['payee_id'] = $d['payeId'];
		$nd['paymethod_id'] = $d['paymId'];
		$nd['totalpaid'] = $subc == 'Refund' ? -$d->cost : $d->cost;
		$nd['unitprice'] = $d['unip'];
		$nd['quantity'] = $d['quan'];
		$nd['miles'] = $d['mile'];
		$nd['notes'] = $d['note'];
		$nd['link'] = $d['link'];
		return $nd;
	}
	private function getModel($d, $action) {
		$nd = null;
		if ($action == 'upd') $nd = Spend::find($d['id']);
		else if ($action == 'add') $nd = new Spend;
		// $oldCost = $action == 'upd' ? $nd['totalpaid'] : 0;
		return $this->col_map($nd, $d);
	}
	function getGiftCardBalance($paymId, $cost) { Log::info("getGiftCardBalance for $paymId");
		$cardInfo = GiftCard::where([['status', 'A'], ['paym_id', $paymId]])->select('value', 'card_num')->orderBy('dtm', 'desc')->limit(1)->get();
		if (count($cardInfo) != 1) {
			Log::info("County Golf Gift Card count($cardInfo) for $paymId");
			return ['status' => "Need to Add New Golf Gift Card", 'paymId' => $paymId, 'currCardBal' => 'None', 'currCardNum' => 'None'];
		}

		$balx = GiftCardBalance::where([['status', 'A'], ['pay_method_id', $paymId]])->select('balance', 'card_num')->orderBy('spend_datetime', 'desc')->limit(1)->get();
		if (count($balx) == 0 and $cardInfo[0]->value > 0) { Log::info("return new card value $cardInfo[0]->value, no balance entry yet");
			$gcardbal = number_format($cardInfo[0]->value, 2);
			$gcardnum = $cardInfo[0]->card_num;
			return ['gcardBal' => $gcardbal, 'gcardNum' => $gcardnum, 'status' => 'OK'];
		} else if (count($balx) == 1 and $cardInfo[0]->value < 0) {
			Log:info("can not getGiftCardBalance. Problem: balx=$balx <-- empty array paymId=$paymId");
			return ['status' => "FAILED", 'errmsg' => 'can not get GiftCardBalance'];
		} else if (count($balx) == 1 and $balx[0]->balance < $cost) {
			// $newBalance = number_format($cardInfo[0]->value + $balx[0]->balance, 2);
			$newBalance = number_format($balx[0]->balance, 2);
			$newCardNum = $cardInfo[0]->card_num;
			// $curBalance = number_format($balx[0]->balance, 2);
			// $curCardNum = $cardInfo[0]->card_num;
			Log::info("getGiftCardBalance - Low balance: $balx[0]->balance < $cost for paymId=$paymId return new card_num=$newCardNum with new balance=$newBalance");
			// Log::info("getGiftCardBalance - Low balance: $balx[0]->balance < $cost for paymId=$paymId return curr card_num=$curCardNum with curr balance=$curBalance");
			// return ['gcardBal' => $newBalance, 'gcardNum' => $newCardNum, 'status' => "OK"];
			return ['gcardBal' => $newBalance, 'gcardNum' => $newCardNum, 'status' => "Need to Add New Golf Gift Card"];
			// return ['curBalance' => $curBalance, 'curCardNum' => $curCardNum, 'status' => "Need to Add New Golf Gift Card"];
		}
		// Log::info("Somerset County Golf Gift Card count($balx)");
		$gcardbal = number_format($balx[0]->balance, 2);
		$gcardnum = $balx[0]->card_num;
		return ['gcardBal' => $gcardbal, 'gcardNum' => $gcardnum, 'status' => 'OK'];
	}
	function addNewGiftCard(Request $da) {
		// $dx = $da->toArray()
		$da['dtm'] = date('Y-m-d H:i:s');
		Log::info('add new gift card', $da->toArray());
		$newCard = new GiftCard($da->toArray());
		$newCard->save();
		return ['status' => "OK"];
	}
	// public function getNewGiftCardRecord($paymId, $gcardNum) { Log:info("getNewGiftCardRecord for paymId=$paymId, gcardNum=$gcardNum");
	// 	$newGiftCard = GiftCard::where([['card_num', '>', $gcardNum], ['paym_id', $paymId], ['status', 'A']])
	// 		->select('value', 'card_num')->orderBy('dtm', 'asc')->first();
	// 	if (is_null($newGiftCard)) {
	// 		Log::info('No new gift card, please add new gift card');
	// 		return array('value' => -1, 'card_num' => -1, 'status' => 'Plase add new gilf card');
	// 	} else {
	// 		Log::info('New Gift Card Record', $newGiftCard->toArray());
	// 		return ['cardValue' => $newGiftCard->value, 'cardNum' => $newGiftCard->card_num, 'status' => "OK" ];
	// 	}
	// }
	private function get_new_gcard_record($paymId, $gcardNum) { Log:info('paymId, gcardNum', [$gcardNum, $paymId]);
		$newGiftCard = GiftCard::where([['card_num', '>', $gcardNum], ['paym_id', $paymId], ['status', 'A']])
			->select('value', 'card_num')->orderBy('dtm', 'asc')->first();
		if (is_null($newGiftCard)) {
			Log::info('gift card balance is low, please add new gift card');
			return array('value' => 600, 'card_num' => 99999);
		} else {
			Log::info('get_new_gcard_record', $newGiftCard->toArray());
			return $newGiftCard;
		}
	}
	/**
	 * [updGCBalances description]
	 * @method updGCBalances(purchasedon, paymId, gcardNum) - privated function used by add/upd/delSpend
	 * after add/upd/del spend and the entry then call this function
	 * 1. getLastBalance(purchasedon, paymId, gcardNum) which is the balance of the 1st unaffected balance
	 *    in GiftCardBalance table
	 * 2. get all affected records in GiftCardBalance in asc order by spend_datetime
	 * 3. update the balance of each record by new balance = lastBalance - totalpaid
	 * author: swang
	 * created at 2022-02-19 11:31
	 * revised at 2022-02-19 11:31
	 * version [version 1.0]
	 * @return [void]
	 */
	private function get_last_balance($purchasedon, $paymId, $gcardNum, $cost) {
		Log::info("get_last_balance for purchasedon=$purchasedon, paymId=$paymId gcardNum=$gcardNum, cost=$cost", [__line__]);
	// public function testDB($purchasedon, $paymId, $gcardNum) {
		// $lastB = GiftCardBalance::where([['spend_datetime', '<', $purchasedon], ['pay_method_id', $paymId], ['card_num', $gcardNum]])
		$lastCardB = GiftCardBalance::where([['spend_datetime', '<', $purchasedon], ['pay_method_id', $paymId]])
				->orderBy('spend_datetime', 'desc')->select('balance')->first()->balance;
				Log::debug("-CK-get_last_balance(from GiftCardBalance) lastCardBalance=$lastCardB or cost<0($cost) pursdon=$purchasedon,gcardNum=$gcardNum,paymId=$paymId", [__line__]);
		if (is_null($lastCardB) or $lastCardB == 0 or $lastCardB < $cost) {
			Log::info("CANNOT get_last_balance(from GiftCardBalance) lastCardBalance=$lastCardB or cost<0($cost) for $purchasedon, $paymId, getting new Card", [__line__]);
			$newCardB = GiftCard::where([['paym_id', $paymId], ['card_num', $gcardNum]])->select('value')->orderby('dtm', 'desc')->first()->value;
			Log::debug("-CK-get_last_balance(from GiftCardBalance) newCardBalance=$newCardB or cost<0($cost) pursdon=$purchasedon,gcardNum=$gcardNum,paymId=$paymId", [__line__]);
			if (is_null($newCardB) or $newCardB == 0) {
				Log::info("can NOT get new card get_last_balance(from GiftCard) for $paymId, $gcardNum exit...", [__line__]);
				exit(-10);
			}
			Log::info("get_last_balance(from GiftCard) new card value=$newCardB, cost=$cost, lastCardB=$lastCardB", ['line' => __LINE__]);
			// if (is_null($lastCardB) or $lastCardB == 0 or $lastCardB < $cost) return $newCardB;
			if (is_null($lastCardB) or $lastCardB == 0) return $newCardB;
			else return $newCardB + $lastCardB;
		} else {
			Log::debug("get_last_balance(from GiftCardBalance)=$lastCardB for $purchasedon, $paymId, getting new Card line at ", [__LINE__]);
			return $lastCardB;
		}
	}
	private function get_affected_gcb($purchasedon, $paymId, $gcardNum) {
		$allAffected = GiftCardBalance::where([['spend_datetime', '>=', $purchasedon], ['pay_method_id', $paymId], ['card_num', $gcardNum]])
				->orderBy('spend_datetime', 'asc')->select('id', 'spend_id', 'spend_datetime as spdt', 'balance', 'card_num')->get();
		// Log::info('get_affected_gcardb_records', $allAffected->toArray());
		// foreach($allAffected as $af) Log::info("$af->balance");
		return $allAffected;
	}
	private function updGCBalances($lowBound, $paymId, $gcardNum, $cost=0) {
		Log::debug("UpdGCBalances lower_spend_datetime affected gc lowBound=$lowBound,paymId=$paymId,gcardNum=$gcardNum,cost=$cost line at ", [__line__]);
	// public function testDB($lowBound, $paymId, $gcardNum) {
		$lastB = $this->get_last_balance($lowBound, $paymId, $gcardNum, $cost);
		Log::info("get_last_balance=$lastB", [__line__]);
		$afGCB = $this->get_affected_gcb($lowBound, $paymId, $gcardNum);
		foreach($afGCB as $af) {
			$gc = GiftCardBalance::find($af->id);
			$pd = Spend::find($af->spend_id);
			// $newCost = $af->spend_datetime == $lowBound ? $cost : $pd->totalpaid;
			// $nb = $lastB - $newCost;
			$nb = $lastB - $pd->totalpaid;
			Log::info("$gc->spend_datetime, new balance=$lastB - $pd->totalpaid=$nb", [__line__]);
			// Log::info("$gc->spend_datetime, new balance=$lastB - $newCost=$nb", [__line__]);
			$gc->balance = $lastB - $pd->totalpaid;
			// $gc->balance = $lastB - $newCost;
			$gc->update();
			$lastB = $lastB - $pd->totalpaid;
		}
	}
	public function addSpend(Request $d) { Log::info('addSpend d', $d->toArray());
		$d->id = null;
		$dx = $this->getModel($d, 'add');
		try {
			$dx->save();
			if (in_array($d->paymId, $this->giftCardIds, true)) {
				$d->id = $dx->id;
				Log::info("add new GCB Record for [spend_id=$d->id, purchasedon=$d->purchasedon, cost=$d->cost, gcardNum=$d->gcardNum]");
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
		} catch(\Exception $e) {
			Log::info($e->getMessage());
			return $e->getMessage();
		}
	}
	private function upd_ins_gcb_record($gcardId, $spendDt, $paymId, $gcardNum, $spendId=0) {
		if (is_null($gcardId)) {
			$dx = new GiftCardBalance;
			$dx->spend_id = $spendId;
			$dx->spend_datetime = $spendDt;
			$dx->card_num = $gcardNum;
			$dx->pay_method_id = $paymId;
			$dx->balance = 88.88;   // will be updated later by updGCBalance
			$dx->save();
			Log::info("NEW GCbalance inserted spendDt=$spendDt, spendId=$spendId, paymId=$paymId, gcardNum=$gcardNum, gcardId=$gcardId");
		} else {
			$dx =	GiftCardBalance::find($gcardId);
			$dx->spend_datetime = $spendDt;
			$dx->card_num = $gcardNum;
			$dx->pay_method_id = $paymId;
			$dx->balance = 99.99; // will be updated later by updGCBalance
			$dx->update();
		}
	}
	private function get_lower_bound($newTime, $orgTime) {
		$lastm = (new DateTime($newTime, new DateTimeZone('America/New_York')))->modify('-1 day');
		$purtm = (new DateTime($orgTime, new DateTimeZone('America/New_York')))->modify('-1 day');
		$lowerBound = min($lastm, $purtm)->format('Y-m-d G:i:s');
		Log:info("lower bound = min(newTime and origTime) lowerBound=$lowerBound, newTime=$newTime, orgTim=$orgTime");
		return $lowerBound;
	}
	private function isPaidByGiftCard($paymId) {
		return $paymId == 9 or $paymId == 15;
	}
	private function updateAndReturn(Request $d, String $msg) {
		Log::info($msg, [__line__]);
		$nd = $this->getModel($d, 'upd');
		$nd->update();
		$da = $this->getList($d->id); //Log::info("getList of for spendId $d->id", $da['lst']);
		$da['lst'][0]->add_upd = true; // for old expense
		$da['lst'][0]->upd = true; // for new exp/exlist
		return ['row' => $da['lst'][0], 'status' => "OK"];
	}
	public function updSpend(Request $d) { Log::info('-fn-updSpend', $d->toArray());
		$origSpend = Spend::find($d->id);
		$origPaymId = $origSpend->paymethod_id;
		$updPaymId = $d->paymId;
		// case 0: paid by non-gift cards for both old and new spendings
		if (!$this->isPaidByGiftCard($origPaymId) and !$this->isPaidByGiftCard($updPaymId)) {
			$msg = "case 0: paid by non-gift cards for both old and new spendings oldPaymId=[$origPaymId] newPaymId=[$updPaymId]";
			return $this->updateAndReturn($d, $msg);
			// Log::info("a regular update for old PaymId=$origPaymId and new PaymId=$updPaymId", [__line__]);
		}
		Log::info("update for old PaymId=$origPaymId and new PaymId=$updPaymId", [__line__]);
		/***
		 * case 1: gift card to non-gift card -- update from paid by gift card to non-gift card
		 * case 2: same gift card -- update from paid by gift card to same gift card
		 * case 3: diff gift card -- update from paid by gift card to differebt gift card
		 * case 4: non-gift card to gift card -- update from paid by non-gift card to gift card  -- rarely happens
		 */
		// case 1: gift card to non-gift card
		if ($this->isPaidByGiftCard($origPaymId) and !$this->isPaidByGiftCard($updPaymId)) {
			$deleted = GiftCardBalance::where('spend_id', $d->id)->delete(); // remove the balance record
			$msg = "case 1: gift card to non-gift card -- update from paid by gift card to non-gift card";
			return $this->updateAndReturn($d, $msg);
		}
		// case 2: same gift card -- update from paid by gift card to same gift card
		$origTime = $origSpend->purchasedon;
		$newTime = $d->purchasedon;
		if ($origPaymId == $updPaymId) {
			$origGcBalance = GiftCardBalance::where('spend_id', $d->id)->get();
			if (count($origGcBalance) != 1) {
				$errmsg = "can't get original gift card balance for spending id=[$d->id], check it out";
				Log::debug($errmsg);
			}
			$origGcBalance[0]->spend_datetime = $newTime;
			$origGcBalance[0]->update();
			$lowerBound = $this->get_lower_bound($newTime, $origTime);
			$this->updateAndReturn($d, 'update first so that to get new cost'); // update first so that balance will be updated correctly
			$this->updGCBalances($lowerBound, $updPaymId, $d->gcardNum, $d->cost);
			$msg = "case 2: same gift card -- update from paid by gift card to same gift card oldTime=[$origTime] newTime=[$newTime]";
			return $this->updateAndReturn($d, $msg);
		}
		// case 3: diff gift card -- update from paid by gift card to differebt gift card
		if ($origPaymId != $updPaymId and $this->isPaidByGiftCard($origPaymId) and $this->isPaidByGiftCard($updPaymId)) {
			$origGcBalance = GiftCardBalance::where('spend_id', $d->id)->get();
			if (count($origGcBalance) != 1) {
				$errmsg = "case 3:can't get original gift card balance for spending id=[$d->id], check it out";
				Log::debug($errmsg);
			}
			$gcbalance = $origGcBalance[0];
			$gcbalance->spend_datetime = $d->purchasedon;
			// $gcbalance->spend_id = $d->id;
			$gcbalance->pay_method_id = $updPaymId;
			$gcbalance->card_num = $d->gcardNum;
			$gcbalance->balance = 777;
			$gcbalance->save();
			$this->updateAndReturn($d, 'update spend first for case 3.');
			$lowerBound = $this->get_lower_bound($newTime, $origTime);
			$this->updGCBalances($lowerBound, $updPaymId, $d->gcardNum, $d->cost);
			$msg = "case 3: diff gift card -- update from paid by gift card to differebt gift card";
			return $this->updateAndReturn($d, $msg);
		}
		// case 4: non-gift card to gift card -- update from paid by non-gift card to gift card -- rarely happens
		if (!$this->isPaidByGiftCard($origPaymId) and $this->isPaidByGiftCard($updPaymId)) {
			$gcbalance = new GiftCardBalance();
			$gcbalance->spend_datetime = $d->purchasedon;
			$gcbalance->spend_id = $d->id;
			$gcbalance->pay_method_id = $updPaymId;
			$gcbalance->card_num = $d->gcardNum;
			$gcbalance->balance = 777;
			$gcbalance->save();
			$this->updateAndReturn($d, 'update spend first for case 4');
			$this->updGCBalances($d->purchasedon, $updPaymId, $d->gcardNum, $d->cost);
			$msg = "case 4: non-gift card to gift card -- update from paid by non-gift card to gift card";
			return $this->updateAndReturn($d, $msg);
		}
	}
	/////////////////////////////////////////
	// 	if ($d->paymId == 9 or $d->paymId == 15) {
	// 		$this->updGiftCardBalance($d);  // mercer or somerset gift cards
	// 	} else { // changed pay method -- paying with means other than mercer or somerset gift cards
	// 		$spend_id = $d->spend_id;
	// 		Log::info("remove the gift card balance record for spend_id: $spend_id");
	// 		$deleted = GiftCardBalance::where('spend_id', $d->id)->delete(); // remove the balance record
	// 	}

	// 	$da = $this->getList($d->id); Log::info("getList of for spendId $d->id", $da['lst']);
	// 	$da['lst'][0]->add_upd = true; // for old expense
	// 	$da['lst'][0]->upd = true; // for new exp/exlist
	// 	return ['row' => $da['lst'][0], 'status' => "OK"];
	// }
	// public function updSpend(Request $d) { Log::info('updSpend', $d->toArray());
	// 	$nd = $this->getModel($d, 'upd');
	// 	$nd->update();
	// 	if ($d->paymId == 9 or $d->paymId == 15) {
	// 		$this->updGiftCardBalance($d);  // mercer or somerset gift cards
	// 	} else { // changed pay method -- paying with means other than mercer or somerset gift cards
	// 		$spend_id = $d->spend_id;
	// 		Log::info("remove the gift card balance record for spend_id: $spend_id");
	// 		$deleted = GiftCardBalance::where('spend_id', $d->id)->delete(); // remove the balance record
	// 	}

	// 	$da = $this->getList($d->id); Log::info("getList of for spendId $d->id", $da['lst']);
	// 	$da['lst'][0]->add_upd = true; // for old expense
	// 	$da['lst'][0]->upd = true; // for new exp/exlist
	// 	return ['row' => $da['lst'][0], 'status' => "OK"];
	// }
	// public function XXupdGiftCardBalance(Request $d) { Log::info('updGiftCardBalance', $d->toArray());
	// 	$spendId = $d->id;
	// 	$origSpend = Spend::find($spendId);
	// 	$origTime = $origSpend->purchasedon;
	// 	$origPaymId = $origSpend->paymethod_id;
	// 	$origCardNum = GiftCardBalance::where('spend_id', $spendId)->value('card_num');
	// 	Log::info("spendId=$spendId origCardNum=$origCardNum");
	// 	if (is_null($origCardNum)) {
	// 		Log::info("select * from gift_card_balances where spend_id=$d->id, return null -- check it manually");
	// 		$msg = "select * from gift_card_balances where spend_id=$d->id, return null check it manually";
	// 		return ['msg' => $msg, 'status' => "FAILED" ];
	// 	}
	// 	Log::info("oTime=$origTime, oPaymId=$origPaymId, oCardNum=$origCardNum");
	// 	// try {
	// 	// 	$nd = $this->getModel($d, 'upd');
	// 	// 	$nd->update();
	// 	// } catch(\Exception $e) {
	// 	// 	return $e->getMessage();
	// 	// }
	// 	if (in_array($d->paymId, $this->giftCardIds, true) and in_array($origPaymId, $this->giftCardIds, true)) {
	// 		if ($d->paymId == $origPaymId) {
	// 			Log::info("SAME GCard upd affected gift card records, spend_id=$d->id paymId=$d->paymId gcardNum=$d->gcardNum cost=$d->cost", [__line__]);
	// 			$this->upd_ins_gcb_record($d->gcardId, $d->purchasedon, $d->paymId, $d->gcardNum);
	// 			$lowerBound = $this->get_lower_bound($d->purchasedon, $origTime);
	// 			$this->updGCBalances($lowerBound, $d->paymId, $d->gcardNum, $d->cost);
	// 		} else {
	// 			Log::info("DIFF paymIds upd affected gift_card_balances for both gc records, spend_id=$d->id, nPaymId=$d->paymId, nGcardNum=$d->gcardNum]", [__line__]);
	// 			if (in_array($d->paymId, $this->giftCardIds, true)) {
	// 				$this->upd_ins_gcb_record($d->gcardId, $d->purchasedon, $d->paymId, $d->gcardNum, $d->id);
	// 				Log::info("newInfo spend_id=$d->id, nDtim=$d->purchasedon, nPaymId=$d->paymId, nGcardNum=$d->gcardNum]", [__line__]);
	// 				$this->updGCBalances($d->purchasedon, $d->paymId, $d->gcardNum);
	// 			}
	// 			if (in_array($origPaymId, $this->giftCardIds, true) and !is_null($origCardNum)) {
	// 				Log::info("origInfo oTime=$origTime, oPaymId=$origPaymId, oCardNum=$origCardNum", [__line__]);
	// 				$this->updGCBalances($origTime, $origPaymId, $origCardNum);
	// 			}
	// 		}
	// 	} else if (in_array($d->paymId, $this->giftCardIds, true) and !in_array($origPaymId, $this->giftCardIds, true)) {
	// 		Log::info("paymIds in GiftCard need to update for spend_id=$d->id, nPaymId=$d->paymId, nGcardNum=$d->gcardNum]", [__line__]);
	// 		$this->upd_ins_gcb_record(null, $d->purchasedon, $d->paymId, $d->gcardNum, $d->id);
	// 		$this->updGCBalances($d->purchasedon, $d->paymId, $d->gcardNum);
	// 	} else if (!in_array($d->paymId, $this->giftCardIds, true) and in_array($origPaymId, $this->giftCardIds, true)) {
	// 		Log::info("origPaymId in GiftCard need to delete it from gift_card_balances table for gcardId=$d->gcardId, oPaymId=$origPaymId, oGcardNum=$origCardNum]", [__line__]);
	// 		GiftCardBalance::destroy($d->gcardId);
	// 		$this->updGCBalances($origTime, $origPaymId, $origCardNum);
	// 	}
	// 	// $da = $this->getList($d->id); Log::info("getList of for spendId $d->id", $da['lst']);
	// 	// $da['lst'][0]->add_upd = true; // for old expense
	// 	// $da['lst'][0]->upd = true; // for new exp/exlist
	// 	// return ['row' => $da['lst'][0], 'status' => "OK"];
	// }
	// private function get_lower_bound($newTime, $orgTime) {
	// // public function testDB($newTime, $origTime) {
	// 	$lastm = (new DateTime($newTime, new DateTimeZone('America/New_York')))->modify('-1 day');
	// 	$purtm = (new DateTime($orgTime, new DateTimeZone('America/New_York')))->modify('-1 day');
	// 	$lowerBound = min($lastm, $purtm)->format('Y-m-d G:i:s');
	// 	Log:info('lower bound = min(newTime and origTime)', [$lowerBound, $lastm, $purtm]);
	// 	return $lowerBound;
	// }
	public function delSpend(Request $d) { Log::info('-fn-delSpend', $d->toArray());
		if (in_array($d->paymId, $this->giftCardIds, true)) {
			Log::info("delete gift_card_balances records based on records in spends[spend_id=$d->id, paymId=$d->paymId dt=$d->date, gcardNum=$d->gcardId]", [__line__]);
			// $dx = GiftCardBalance::find($d->gcardId);
			// if (!is_null($dx)) $dx->delete();
			GiftCardBalance::destroy($d->gcardId);
			Spend::destroy($d->id);
			$this->updGCBalances($d->date, $d->paymId, $d->gcardNum);
		}
		Spend::destroy($d->id);
		Log::info("spend_id=$d->id deleted");
		return ['row' => $d->toArray(), 'status' => "OK"];
	}
	public function addNewCSP(Request $indata) { Log::info("addNewCSP", $indata->toArray());
		$user = Auth::user();
		$name = $indata['name'];   // name for Category, Subcategory or Payee
		$parentId = $indata['parentId'];
		$model = $indata['model'];
		$mo = new Category;
		if ($model == "Category") {
			$mo->user_id = $user->id;
			$mo->name = $name;
		} else if ($model == "Subcategory") {
			$mo = new Subcategory;
			$mo->user_id = $user->id;
			$mo->name = $name;
			$mo->cat_id = $parentId;
		} else if ($model == "Payee") {
			$mo = new Payee;
			$mo->user_id = $user->id;
			$mo->name = $name;
			$mo->subcat_id = $parentId;
		} else if ($model == "PayMethod") {
			$mo = new PayMethod;
			$mo->user_id = $user->id;
			$mo->name = $name;
		}
		Log::info("Add NewCSP $model=$model, name=$name, parentId=$parentId");
		$mo->save();
		// return $mo;
		return ['csp' => ['model' => $model, 'name' => $name, 'id' => $mo->id], 'status' => "OK" ];
	}
	///////////////////////////////////////////////////////////////
	/**
	 * [get options for Category, Subcatetory, payee]
	 * @method getOptions
	 * author: swang
	 * created at 2017-05-14T12:38:23-040
	 * revised at 2017-05-14T12:38:23-040
	 * version [version]
	 * @param  [type]     $model       [description]
	 * @param  [type]     $parentOptId [description]
	 * @return [type]                  [description]
	 */
	public function getPayMethodOptions() {
		$user = Auth::user();
		$payOptions = PayMethod::where([['user_id', $user->id],['status','A']])->select('id', 'name')->get();
		$obj = ['id' => -1, 'name' => 'Add New Payment Method'];
		$payOptions[] = $obj;
		return [ 'payOptions' => $payOptions ];
	}
	public function getCategoryOptions() {
		$user = Auth::user();
		$catOptions = Category::where([['user_id', $user->id],['status','A']])->select('id', 'name')->get();
		$obj = ['id' => -1, 'name' => 'Add New Catetory'];
		$catOptions[] = $obj;
		return [ 'catOptions' => $catOptions ];
	}
	public function getInitialOptions($catId, $subcatId) {
		$user = Auth::user();
		$catOptions = null;
		$subcatOptions = null;
		// $catOptions = Category::where([['user_id', $user->id],['status','A']])->select('id', 'name')->get();
		$subcatOptions = Subcategory::where([['user_id', $user->id],['status','A'], ['cat_id', $catId]])->select('id', 'name')->get();
		$payeeOptions = Payee::where([['user_id', $user->id],['status','A'], ['subcat_id', $subcatId]])->select('id', 'name')->get();

		// $obj = ['id' => -1, 'name' => 'Add New Category'];
		// $catOptions[] = $obj;
		$obj = ['id' => -1, 'name' => 'Add New Subcategory'];
		$subcatOptions[] = $obj;
		$obj = ['id' => -1, 'name' => 'Add New Payee'];
		$payeeOptions[] = $obj;
		return [ 'subcatOptions' => $subcatOptions, 'payeeOptions' => $payeeOptions ];
		// return [ 'catOptions' => $catOptions, 'subcatOptions' => $subcatOptions, 'payeeOptions' => $payeeOptions ];
	}
	public function getOptions($model, $parentOptId) {
		$user = Auth::user();
		$da = null;
		if ($model == "Category") {
			$da = Category::where([['user_id', $user->id],['status','A']])->select('id', 'name')->get();
		} else if ($model == "Subcategory") {
			$da = Subcategory::where([['user_id', $user->id],['status','A'], ['cat_id', $parentOptId]])->select('id', 'name')->get();
		} else if ($model == "Payee") {
			$da = Payee::where([['user_id', $user->id],['status','A'], ['subcat_id', $parentOptId]])->select('id', 'name')->orderBy('name')->get();
		} else if ($model == "PayMethod") {
			$da = PayMethod::where([ ['user_id', $user->id],['status','A'] ])->select('id', 'name')->get();
		}
		return [ 'options' => $da ];
	}
	/**
	* Display a listing of the resource.App\Models\HomePag
	*
	* @return Response
	*/
	private function date_interval($days) {
		return date_interval_create_from_date_string("$days days");
	}
	public function add(Request $d) {
		$d['id'] = 0;
		$nd = $this->getModel($d, 'add');
		$nd->save();
		$user = Auth::user();
		$dats = DB::select('CALL get_spenseById(?,?)', [$user->id, $nd['id']])[0];
		$da = Collect($dats);
		return $da;
	}
	public function del(Request $d) {
		$nd = Spense::find($d['id']);
		$nd->status = 'D';
		$nd->save();
		return $nd;
	}
	// private function getModelX($d, $action) {
	// 	$nd = null;
	// 	if ($action == 'upd') $nd = Spense::find($d['id']);
	// 	else if ($action == 'add') $nd = new Spense;
	// 	return $this->col_map($nd, $d);
	// }
	// private function col_mapX($nd, $d) {
	// 	$nd['user_id'] = $user = Auth::user()->id;
	// 	$nd['purchasedon'] = $d['Purchased on'];
	// 	$nd['cat_id'] = $d['Category'];
	// 	$nd['subcat_id'] = $d['Subcategory'];
	// 	$nd['payee_id'] = $d['Payee'];
	// 	$nd['paymethod_id'] = $d['PayMethod'];
	// 	$nd['totalpaid'] = $d['Cost'];
	// 	$nd['unitprice'] = $d['Unit Price'];
	// 	$nd['quantity'] = $d['Quantity'];
	// 	$nd['miles'] = $d['Miles Run'];
	// 	$nd['notes'] = $d['Notes'];
	// 	return $nd;
	// }
	public function upd(Request $d) {
		$user = Auth::user();
		$nd = $this->getModel($d, 'upd');		//return $nd;
		$nd->save();
		$dats = DB::select('CALL get_spenseById(?,?)', [$user->id, $d['id']])[0];
		$da = Collect($dats);
 		return $da;
	}
	public function addNewCSPX(Request $indata) {
		$user = Auth::user();
		$name = $indata['newCSP'];   // name for Category, Subcategory or Payee
		$prevId = $indata['prevOptId'];
		$model = $indata['model'];
		// $model = preg_replace('@ @', '', $model);
		$mo = new Category;
		if ($model == "Category") {
			$mo->user_id = $user->id;
			$mo->name = $name;
		} else if ($model == "Subcategory") {
			$mo = new Subcategory;
			$mo->user_id = $user->id;
			$mo->name = $name;
			$mo->cat_id = $prevId;
		} else if ($model == "Payee") {
			$mo = new Payee;
			$mo->user_id = $user->id;
			$mo->name = $name;
			$mo->subcat_id = $prevId;
		} else if ($model == "PayMethod") {
			$mo = new PayMethod;
			$mo->user_id = $user->id;
			$mo->name = $name;
		}
		$mo->save();
		return $mo;
	}
}

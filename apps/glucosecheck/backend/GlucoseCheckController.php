<?php
namespace App\Http\Controllers;
use App\Models\glucosecheck\GlucoseCheck;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GlucoseCheckController extends Controller {
	public function __construct() {
		$this->middleware('auth');
	}
	/**
	 * [getList description]
	 * @method getList
	 * author: swang
	 * created at 2021-08-02 19:00
	 * revised at 2021-08-02 19:00
	 * version [version]
	 * @return [type] [description]
	 */
	public function getList() { Log::info('-fn-GlucoseCheck->getList()');
		$user = Auth::user();
		$dats = GlucoseCheck::where([ ['status', 'A'], ['user_id', $user->id] ])->orderBy('datetime', 'desc')
      		->select('datetime', 'glucose', 'type', 'weight', 'blood_pressure as bloodPressure', 'food', 'exercise', 'breakfast', 'lunch', 'dinner', 'fruit', 'drink', 'note', 'id')
      		->get();
		foreach($dats as $d) {
			$d->datetime = substr($d->datetime, 0, 16);
      		$d->BMI = $d->weight * 0.4536 / 1.73 / 1.73;
      		$x = explode(' / ', $d->bloodPressure);
      		$d->hiBP = isset($x[0]) ? $x[0] : null;
      		$d->loBP = isset($x[1]) ? $x[1] : null;
      		$d->htBT = isset($x[2]) ? $x[1] : null;
			if (is_null($d->food)) $d->glucoseSearch = 'glucose';
		}

    	$ex = GlucoseCheck::where([ ['status', 'A'], ['user_id', $user->id], ['exercise', '<>', null] ])->distinct()->pluck('exercise');
    	foreach($ex as $i => $e) $exOpt[] = ['value' => $i + 1, 'label' => $e];
    	// Log::info("exercise", $exOpt);
    	$ex = GlucoseCheck::where([ ['status', 'A'], ['user_id', $user->id], ['breakfast', '<>', null] ])->distinct()->pluck('breakfast');
    	foreach($ex as $i => $e) $brOpt[] = ['value' => $i + 1, 'label' => $e];
    	// Log::info("brOpt", $brOpt);
    	$ex = GlucoseCheck::where([ ['status', 'A'], ['user_id', $user->id], ['lunch', '<>', null] ])->distinct()->pluck('lunch');
    	foreach($ex as $i => $e) $luOpt[] = ['value' => $i + 1, 'label' => $e];
    	// Log::info("luOpt", $luOpt);
    	$ex = GlucoseCheck::where([ ['status', 'A'], ['user_id', $user->id], ['dinner', '<>', null] ])->distinct()->pluck('dinner');
    	foreach($ex as $i => $e) $diOpt[] = ['value' => $i + 1, 'label' => $e];
    	$ex = GlucoseCheck::where([ ['status', 'A'], ['user_id', $user->id], ['drink', '<>', null] ])->distinct()->pluck('drink');
    	foreach($ex as $i => $e) $drOpt[] = ['value' => $i + 1, 'label' => $e];
    	// Log::info("drOpt", $drOpt);
		$ex = GlucoseCheck::where([ ['status', 'A'], ['user_id', $user->id], ['fruit', '<>', null] ])->distinct()->pluck('fruit');
		foreach($ex as $i => $e) $frOpt[] = ['value' => $i + 1, 'label' => $e];
		// Log::info("frOpt", $frOpt);
		$ex = GlucoseCheck::where([ ['status', 'A'], ['user_id', $user->id], ['fruit', '<>', null] ])->distinct()->pluck('food');
		foreach($ex as $i => $e) $foOpt[] = ['value' => $i + 1, 'label' => $e];
		// Log::info("foOpt", $foOpt);

		return ['lst' => $dats, 'exOpt' => $exOpt, 'brOpt' => $brOpt, 'luOpt' => $luOpt, 'diOpt' => $diOpt, 'drOpt' => $drOpt, 'frOpt' => $frOpt, 'foOpt' => $foOpt, 'status' => "OK"];
	}
	public function add(Request $da) { // Log::info('adding da', $da->toArray());
		$user = Auth::user();
		$dm = new GlucoseCheck($da->toArray()); Log::info('-fn-adding dm', $dm->toArray());
		// $dm->af2hour = $da->food == null ? null : $da->fasting;
		$dm->user_id = $user->id;
		$dm->datetime = $da->datetime;
    $dm->glucose = $da->glucose;
    $dm->type = $da->type;
		$dm->blood_pressure = $da->bloodPressure;
		$dm->weight = $da->weight;
    $dm->food = $da->food;
    $dm->exercise = $da->exercise;
    $dm->breakfast = $da->breakfast;
    $dm->lunch = $da->lunch;
    $dm->dinner = $da->dinner;
		$dm->drink = $da->drink;
		$dm->fruit = $da->fruit;
		$dm->note = $da->note;
		$dm->save();
		// Log::info('added dm', $dm->toArray());
		return $this->getList();
		// $da->id = $dm->id;
		// return ['row' => $da, 'status' => "OK"];
	}
	public function upd(Request $da) { // Log::info('da', $da->toArray());
		$id = $da['id'];
		if (is_null($id)) {
			Log::info('no id for update, exit ...');
			exit;
		}
		$user = Auth::user();
		$dm = GlucoseCheck::find($da['id']);
    $dm->datetime = $da->datetime;
    $dm->glucose = $da->glucose;
    $dm->type = $da->type;
		$dm->blood_pressure = $da->bloodPressure;
		$dm->weight = $da->weight;
    $dm->food = $da->food;
    $dm->exercise = $da->exercise;
    $dm->breakfast = $da->breakfast;
    $dm->lunch = $da->lunch;
    $dm->dinner = $da->dinner;
		$dm->drink = $da->drink;
		$dm->fruit = $da->fruit;
		$dm->note = $da->note;
		$dm->update();
		return $this->getList();
		// return ['status' => "OK"];
	}
	// public function upd(Request $da) { // Log::info('da', $da->toArray());
	// 	$id = $da['id'];
	// 	if (is_null($id)) {
	// 		Log::info('no id for update, exit ...');
	// 		exit;
	// 	}
	// 	$user = Auth::user();
	// 	$dm = GlucoseCheck::find($da['id']);
	// 	$dm->fasting = $da->fasting;
	// 	$dm->datetime = $da->datetime;
	// 	$dm->af2hour = $da->food == null ? null : $da->fasting;
	// 	$dm->blood_pressure = $da->bloodPressure;
	// 	$dm->food = $da->food;
	// 	$dm->drink = $da->drink;
	// 	$dm->fruit = $da->fruit;
	// 	$dm->weight = $da->weight;
	// 	$dm->note = $da->note; Log::info('updating dm', $dm->toArray());
	// 	$dm->user_id = $user->id;
	// 	$dm->update();
	// 	return $this->getList();
	// 	// return ['status' => "OK"];
	// }
	public function del(Request $da) { Log::info('delete id', $da->toArray());
		$dm = GlucoseCheck::find($da['id']);
		$dm->delete();
		return $this->getList();
		// return ['status' => "OK"];
	}
}

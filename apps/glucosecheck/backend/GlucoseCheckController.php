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
		// $dats = GlucoseCheck::select('id', 'user_id',  DATE_FORMAT(now(), '%Y-%d-%m %H:%i'), 'fasting', 'twoh_after', 'food', 'fruit', 'drink', 'note')
		// $dats = GlucoseCheck::select('id', 'user_id',  'datetime', 'fasting', 'af2hour', 'food', 'fruit', 'drink', 'note')
		// $dats = GlucoseCheck::select('id', 'user_id',  'datetime', 'fasting', 'food', 'fruit', 'drink', 'note')
		$dats = GlucoseCheck::select('datetime', 'fasting', 'weight', 'blood_pressure as bloodPressure', 'food', 'fruit', 'drink', 'note', 'id')
		->where([ ['status', 'A'], ['user_id', $user->id] ])->orderBy('datetime', 'desc')->get();

		foreach($dats as $d) {
			$d->datetime = substr($d->datetime, 0, 16);
			if (is_null($d->food)) $d->fastingSearch = 'fasting';
		}

		return ['lst' => $dats, 'status' => "OK"];
	}
	public function add(Request $da) { // Log::info('adding da', $da->toArray());
		$user = Auth::user();
		$dm = new GlucoseCheck($da->toArray()); Log::info('-fn-adding dm', $dm->toArray());
		$dm->af2hour = $da->food == null ? null : $da->fasting;
		$dm->user_id = $user->id;
		$dm->datetime = $da->datetime;
		$dm->weight = $da->weight;
		$dm->blood_pressure = $da->bloodPressure;
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
		$dm->fasting = $da->fasting;
		$dm->datetime = $da->datetime;
		$dm->af2hour = $da->food == null ? null : $da->fasting;
		$dm->blood_pressure = $da->bloodPressure;
		$dm->food = $da->food;
		$dm->drink = $da->drink;
		$dm->fruit = $da->fruit;
		$dm->weight = $da->weight;
		$dm->note = $da->note; Log::info('updating dm', $dm->toArray());
		$dm->user_id = $user->id;
		$dm->update();
		return $this->getList();
		// return ['status' => "OK"];
	}
	public function del(Request $da) { Log::info('delete id', $da->toArray());
		$dm = GlucoseCheck::find($da['id']);
		$dm->delete();
		return $this->getList();
		// return ['status' => "OK"];
	}
}

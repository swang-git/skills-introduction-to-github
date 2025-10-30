<?php
namespace App\Http\Controllers;
use App\Models\reminder\Reminder;
use App\Models\memo\Memo;
use App\Models\memo\Cipherkey;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReminderController extends Controller
{
	public function __construct() {
		// if ( env('APP_ENV') !== 'prod' ) log_client_info();
		// $this->fin_db = config('database.connections.Fin.database');
		$this->middleware('auth');
	}
	// public function checkReminder($date) { Log::info("checkReminder $date");
	// 	$remd = Reminder::where('due_date', $date)->select('tag', 'message')->get();
	// 	$ret = $remd->toArray();
	// 	Log::info("checkReminder for $date " . count($ret), $ret);
	// 	$retval = count($ret) > 0 ? $ret[0] : 'not_scheduled';
	// 	return ['reminder' => $retval, 'status' => "OK"];
	// }	
	private function fmtLink($link) {
		$lnk = '';
		$doc_dir = config('global.doc_dir');
		$docs = explode('@', $link); //Log::info([$d->filename, $docs]);
		if (count($docs) == 1) {
			$doc = $docs[0];
			$dname = preg_replace('/_|-/', ' ', $doc);
			if (filter_var($doc, FILTER_VALIDATE_URL)) {
				$x = explode('__A_TAG_NAME__', $doc); Log::info("x=", [$x, __line__]);
				if (count($x) == 2) {
					$doc = $x[0];
					$dname = $x[1];
				}
				$lnk .= "<li><a href='$doc' target='_blank'>$dname</a></li>";
			} else {
				$lnk = "<a href='$doc_dir/$doc' target='_blank'>$dname</a>";
			}
		} else {
			$lnk = "<ol>";
			foreach($docs as $doc) {
				$lnkname = $doc;
				$dname = preg_replace('/_|-/', ' ', $lnkname); Log::info("doc=$doc", [__line__]);
				if(filter_var($doc, FILTER_VALIDATE_URL)) {
					$x = explode('__A_TAG_NAME__', $doc); Log::info("x=", [$x, __line__]);
					if (count($x) == 2) {
						$doc = $x[0];
						$dname = $x[1];
					}
					$lnk .= "<li><a href='$doc' target='_blank'>$dname</a></li>";
				} else {
					$lnk .= "<li><a href='$doc_dir/$doc' target='_blank'>$dname</a></li>";
				}
			}
			$lnk .= "</ol>";
		}
		return $lnk;
	}
	private function en_de_cryptTxt($txt, $decrypt=false) {
		$cm = Cipherkey::find(1);
		$enkey = $cm->encrypt_key;
		$enmod = $cm->encrypt_mode;
		$iv = $cm->iv;
		if ($decrypt) return openssl_decrypt($txt, $enmod, $enkey, 0, $iv);
		return openssl_encrypt($txt, $enmod, $enkey, 0, $iv);
	}
	/**
	 * [getList description]
	 * @method getList
	 * author: swang
	 * created at 2017-05-10T16:31:40-040
	 * revised at 2018-07-09T21:43:40-040
	 * version [version]
	 * @return [type] [description]
	 */
	public function getList() { Log::info("-- Reminder-getList");
		$user = Auth::user();
		$golfReminders = DB::select('Call get_golf_play_from_spends_for_reminders()');
		$todoReminders = DB::select('Call get_todo_list_from_spends_for_reminders()');
		$pxloadReminders = DB::select('Call check_daily_pxload()');
		$today = Date('Y-m-d', time());
		$memoReminders = Memo::where([['status', 'A'], ['reminder', 1], ['user_id', $user->id], ['date', '>', $today]])
			->select('id', 'user_id', 'date as due_date', 'tag', 'details', 'reminder as memo')
			->orderBy('date', 'desc')->get();
		Log::debug("memoReminders", $memoReminders->toArray());
		foreach($memoReminders as $d) {
			$details = $this->en_de_cryptTxt($d->details, true);
			// $details = preg_replace('/<strike>/', '<div style="color:darkgreen">', $details);
			// $details = preg_replace('/<\/strike>/', '</div>', $details);
			$d->message = 'at '. substr($d->due_date, 11, 5) . ' ' . strip_tags($details);
			$d->details = $details;
			// $d->date = substr($d->date, 0, 16);
			$d->due_date = substr($d->due_date, 0, 10);
      // if ($d->link != null) $d->lnk = $this->fmtLink($d->link); 
		}
		Log::debug("memoReminders", $memoReminders->toArray());

		$dats = Reminder::select('id', 'user_id', 'due_date', 'recursive', 'tag', 'message', 'details', 'link')
				 ->where([ ['status', 'A'], ['user_id', $user->id] ])->orderBy('due_date', 'asc')->get();
		$todoList = [];
		$justPastList = [];
		$allTodoList = [];
		$allPastList = [];
		// $today = Date('Y-m-d', time()); //Log::info(['from reminder', $today]);
		$todoCutDays = 8;
		foreach($dats as $d) {
			if ($d->link != null) $d->lnk = $this->fmtLink($d->link); 
			$dueDate = substr($d->due_date, 0, 10);
			$this->setDueIn($d);
			$dueInDays = $this->time_diff($dueDate, $today) / (60*60*24);
			$d->dueInDays = $dueInDays;
			// if ($dueInDays >= 0 and $dueInDays <= 20) { //Log::info([$duedt, $today, $dueInDays]);
			if ($dueInDays >= 0 and $dueInDays <= $todoCutDays) { //Log::info([$duedt, $today, $dueInDays]);
				$todoList[] = $d;
				if ($dueInDays == 0 and $d->recursive > 0) $d = $this->setNextReminder($d);
			} else if ($dueInDays > $todoCutDays) {
				$allTodoList[] = $d;
			} else if ($dueInDays >= -4 and $dueInDays < 0) {
				$justPastList[] = $d;
			} else {
				$allPastList[] = $d;
			}
    }
		foreach($pxloadReminders as $d) {
			$d->recursive = 0;
			// $d->tag = '<b style="font-family:fixed">' . $d->tag . "</b> (" . $d->count . ")";
			$d->tag .= " (" . $d->count . ")";
			$this->setDueIn($d);
			$dueInDays = (int)$this->time_diff($d->due_date, $today) / (60*60*24);
			$d->dueInDays = $dueInDays;
			// $d->tag = 'Play at ' . $d->tag;
		}
		foreach($golfReminders as $d) {
			$this->setDueIn($d);
			$dueInDays = (int)$this->time_diff($d->due_date, $today) / (60*60*24);
			$d->dueInDays = $dueInDays;
			$d->recursive = null;
			$d->tag = 'Play at ' . $d->tag;
		}
		foreach($todoReminders as $d) {
			$this->setDueIn($d);
			$dueInDays = (int)$this->time_diff($d->due_date, $today) / (60*60*24);
			$d->dueInDays = $dueInDays;
			$d->type = 'Go to ' . $d->type;
		}
		foreach($memoReminders as $d) {
			$this->setDueIn($d);
			$dueInDays = (int)$this->time_diff($d->due_date, $today) / (60*60*24);
			$d->dueInDays = $dueInDays;
			$d->type = 'To Do ' . $d->tag;
		}
		foreach($dats as $d) {
			$d->details = preg_replace('/<strike>/', '<div style="color:darkgreen">', $d->details);
			$d->details = preg_replace('/<\/strike>/', '</div>', $d->details);
		}
		arsort($allPastList);
		$da = array_merge($memoReminders->toArray(), $golfReminders, $todoReminders, $todoList, $pxloadReminders, $justPastList, $allTodoList, $allPastList);

		return ['lst' => $da, 'status' => "OK"];
	}
	private function setNextReminder($d) { Log::info("-fn-setNextReminder due_date=$d->due_date");
		$today = date_create(); //Log::info($today->format('Y-m-d'));
		$newDueDate = date_add($today, $this->date_interval($d->recursive))->format('Y-m-d');
		// Log::info([$newDueDate, $d->due_date, $d->user_id, $d->type, $d->recursive, $d->message, $d->details]);
		// Reminder::firstOrCreate(['due_date'=>$newDueDate, 'recursive'=>$d->recursive, 'user_id'=>$d->user_id, 'type'=>$d->type, 'message'=>$d->message, 'details'=>$d->details, 'status'=>'A']);
		Reminder::firstOrCreate(['due_date'=>$newDueDate, 'user_id'=>$d->user_id, 'tag'=>$d->tag], ['recursive'=>$d->recursive, 'message'=>$d->message, 'details'=>$d->details, 'filename'=>$d->filename]);
	}
	/**
	 * Display a listing of the resource.App\Models\HomePag
	 *
	 * @return Response
	*/
	private function date_interval($days) {
		return date_interval_create_from_date_string("$days days");
	}
	private function setDueIn($d) {
		$dueDate = substr($d->due_date, 0, 10);
		$today = Date('Y-m-d', time());
		$dueInDays = sprintf('%2d', $this->time_diff($dueDate, $today) / (60*60*24));
		if ($dueInDays > 365) $d->due_in = sprintf('%2.1f', $dueInDays / 365) . "年";
		else if ($dueInDays > 30) $d->due_in= sprintf('%2.1f', $dueInDays / 30) . "月";
		else if ($dueInDays == 2) $d->due_in= "后 天";
		else if ($dueInDays == 1) $d->due_in= "明 天";
		else if ($dueInDays == 0) $d->due_in = "今 天";
		else if ($dueInDays < 0) $d->due_in= "过  期";
		else if ($dueInDays > 2 and $dueInDays < 10) $d->due_in = "0" . $dueInDays ." 天";
		else $d->due_in = $dueInDays . "  天";
		if ($d->recursive === 0 or $d->recursive == null) $d->recursive = null;
		else if ($d->recursive < 10) $d->recursive = "00" . $d->recursive;
		else if ($d->recursive < 100) $d->recursive = "0" . $d->recursive;
	}
	public function upd(Request $d) { Log::info("reminder-upd", $d->toArray());
		$nd = Reminder::find($d->id);
		$dx = $d->toArray();
		foreach($dx as $k => $val) {
			if ($k == 'id') continue;
			$nd->$k = $val;
		}
		try {
			$nd->save();
			Log::info('reminder/upd', $nd->toArray());
      if ($nd->link != null) $nd->lnk = $this->fmtLink($nd->link);
		} catch (\Exception $e) {
			Log::info($e->getMessage());
			return [ 'dbstatus' => "failed", 'error' => $e->getMessage(), 'status' => 'reminder/upd FAILED' ];
		}
		$this->setDueIn($nd);
		// return Collect($nd);
		return [ 'row' => Collect($nd), 'status' => "OK" ];
	}
	public function add(Request $da) { Log::info("reminder/add da", $da->toArray());
		$da['id'] = null;
		$nd = new Reminder($da->toArray());
		try {
			$nd->save();
      if ($nd->link != null) $nd->lnk = $this->fmtLink($nd->link);
		} catch (\Exception $e) {
			Log::info('db-error', [$e->getMessage()]);
			return [ 'dbstatus' => "failed", 'error' => $e->getMessage(), 'status' => "Reminder/add FAILED" ];
		}
		$this->setDueIn($nd);
		return [ 'row' => Collect($nd), 'status' => "OK" ];
	}
	public function del(Request $d) {
		Reminder::destroy($d['id']);
		return [ 'row' => $d, 'status' => "OK" ];
		// return $d;
	}

	///////////////////////////////////////////
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
	private function time_diff($dt1, $dt2) { // $dt = '2019-11-19 22:33:59'
	    return strtotime($dt1) - strtotime($dt2);
	}
	// private function time_diff($dt1, $dt2) { // $dt = '2019-11-19 22:33:59'
	//     $y1 = (int)substr($dt1,0,4);
	//     $m1 = (int)substr($dt1,5,2);
	//     $d1 = (int)substr($dt1,8,2);
	//     $h1 = (int)substr($dt1,11,2);
	//     $i1 = (int)substr($dt1,14,2);
	//     $s1 = (int)substr($dt1,17,2);

	//     $y2 = (int)substr($dt2,0,4);
	//     $m2 = (int)substr($dt2,5,2);
	//     $d2 = (int)substr($dt2,8,2);
	//     $h2 = (int)substr($dt2,11,2);
	//     $i2 = (int)substr($dt2,14,2);
	//     $s2 = (int)substr($dt2,17,2);

	//     $r1=date('U',mktime($h1,$i1,$s1,$m1,$d1,$y1));
	//     $r2=date('U',mktime($h2,$i2,$s2,$m2,$d2,$y2));
	//     // return ($r1-$r2)/(1000 * 60 * 60 * 24);
	//     return ($r1-$r2);
	// }
	/**
	 * [index description]
	 * @method index
	 * author: swang
	 * created at 2017-05-10T16:31:40-040
	 * revised at 2017-05-10T16:31:40-040
	 * version [version]
	 * @return [type] [description]
	 */
	public function XXXindex() {
		$user = Auth::user();
		$dats = Reminder::select('id', 'user_id', 'due_date', 'recursive', 'tag', 'message', 'details', 'filename')
				 ->where([ ['status', 'A'], ['user_id', $user->id] ])->orderBy('due date', 'asc')->get();

	 	$tododata = [];
		$latedata = [];
		foreach($dats as $d) {
			if ($d->filename != null) {
				$doc_dir = config('global.doc_dir');
				if(filter_var($d->filename, FILTER_VALIDATE_URL)) $d->lnk = "<a href='$d->filename' target='_blank'>Related Document URL</a>";
				else $d->lnk = "<a href='$doc_dir/$d->filename' target='_blank'>Related Document File</a>";
			}
			$this->setDueIn($d);
			$dueDate = $d['due date'];
			$today = Date('Y-m-d', time());	 					//$d['due date'] = $dueDate; //for testing
			if ($dueDate >= $today) {		//$d['type'] = $this->time_diff($dueDate, $today);
				$tododata[] = $d;
				if ($dueDate == $today) { $d = $this->setNextReminder($d); }//  $tododata[] = $d; }
			} else {
				$latedata[] = $d;
			}
		}
		$da = array_merge($tododata, $latedata);

		$gridData = Collect($da);
		return view('Fin.reminder', compact('gridData'));
	}
	private function XXXencryptTxt($txt, $decrypt='not_sch') {
		$cm = Cipherkey::find(1);
		$enkey = $cm->encrypt_key;
		$enmod = $cm->encrypt_mode;
		$iv = $cm->iv;
		if ($decrypt) return openssl_decrypt($txt, $enmod, $enkey, 0, $iv);
		return openssl_encrypt($txt, $enmod, $enkey, 0, $iv);
	}
	private function XXXcol_map($nd, $d) {
		foreach($d as $key => $val) { $nd[$key] = $val; }
		// $nd['due date'] = $d['due date'];
		// $nd->type = $d['type'];
		// $nd->message = $d['message'];
		// $nd->recursive = $d['recursive'];
		// $nd->details = $d['details'];
		return $nd;
	}
}

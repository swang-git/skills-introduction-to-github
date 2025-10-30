<?php
namespace App\Http\Controllers;

use App\Models\tv\Recorded;
use App\Models\tv\Oldrecorded;
// use App\Models\tv\Channel;
// use App\Models\tv\ChannelOldId;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

// \Config::set('database.default', 'mythconverg');
class TvController extends Controller
{
	public function __construct() {
		// $this->middleware('auth');
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
		// return view('fin');
	}
	private function showData($tag, $d) {
		Log::info("$tag starttime: $d->starttime");
		Log::info("$tag   endtime: $d->endtime");
		Log::info("$tag  duration: $d->duration");
		Log::info("$tag  filesize: $d->filesize");
		Log::info("$tag     title: $d->title");
		Log::info("$tag  filename: $d->filename");
	}
	private function checkFileExist($d) { //Log::info("tv.checkAndFormat()", $d->toArray());
		$d->filename = null;
		// $this->showData("BEF", $d);
		if (file_exists("/f36/bak/xtv/$d->basename")) { $d->filename = "/f36/bak/xtv/$d->basename"; $d->dsk="atv"; }
		else if (file_exists("/btv/$d->basename")) { $d->filename = "/btv/$d->basename"; $d->dsk="btv"; }
		else if (file_exists("/ctv/$d->basename")) { $d->filename = "/ctv/$d->basename"; $d->dsk="ctv"; }
		else if (file_exists("/dtv/rec/$d->basename")) { $d->filename = "/dtv/rec/$d->basename"; $d->dsk="dtv"; }
		else if (file_exists("/home/swang/htv/$d->basename")) { $d->filename = "/home/swang/htv/$d->basename"; $d->dsk="htv"; }
		if (is_null($d->filesize)) return $d;
		$d->filesize = round($d->filesize / 1024 / 1024 / 1024, 1);
		return $d;
	}
	private function convertToLocalTime($d) {
		$localTimeZone = (new \DateTime())->getTimezone();
		// Log::info("Local time zone:". $localTimeZone->getName());
		$d->gmt_starttime = $d->starttime;
		$d->starttime = (new \DateTime($d->starttime, new \DateTimeZone('UTC')))->setTimezone($localTimeZone)->format('Y-m-d H:i');
		$d->endtime = (new \DateTime($d->endtime, new \DateTimeZone('UTC')))->setTimezone($localTimeZone)->format('Y-m-d H:i');
		$d->channum = str_replace('_', '-', $d->channum);
		if (is_numeric($d->filesize) and !preg_match('/\./', $d->filesize))	$d->filesize = $d->filesize . ".0";
		$d->duration = round((strtotime($d->endtime) - strtotime($d->starttime))/60, 0);
		if ($d->dsk == 'Default')	$d->dsk = 'abc';
		else if ($d->dsk == 'HomeTV')	$d->dsk = 'htv';
		else if ($d->dsk == 'USBdisk')	$d->dsk = 'dtv';
		return $d;
	}
	private function UTCnow() {  // or call GMT (Greenwich Time)
		return gmdate("Y-m-d H:i:s");
	}
	private function UTCplus($addHours) {  // or call GMT (Greenwich Time)
		return date('Y-m-d H:i:s', strtotime("+$addHours hour", strtotime($this->UTCnow())));
	}
	public function getList($hours=4) {  Log::info("TvController.getlist()", [$this->UTCnow(), $this->UTCplus($hours)]);
		// $upcoming = Oldrecorded::where('watched', 0)
		$gmtnow = $this->UTCnow();
		$upcoming = Oldrecorded::where([['oldrecorded.starttime', '>', $gmtnow], ['oldrecorded.endtime', '<', $this->UTCplus($hours)]])
			->select('record.recordid as recordedid', 'channum', 'oldrecorded.starttime', 'oldrecorded.endtime',
				DB::raw('null as basename'), 'oldrecorded.title', 'oldrecorded.subtitle', 'oldrecorded.description',
				DB::raw('null as filesize'), DB::raw('storagegroup as dsk'))
			->join('channel', 'channel.chanid', 'oldrecorded.chanid')
			->join('record', 'record.recordid', 'oldrecorded.recordid')
			->orderBy('starttime', 'desc')->get();

		$datx = Recorded::where('watched', 0)
			->select('recordedid', 'channel.channum', 'starttime', 'endtime', 'basename', 'title', 'subtitle', 'description', 'filesize')
			->join('channel', 'channel.chanid', 'recorded.chanid')
			// ->select('recordedid', 'channel_old_id_ALL.channum', 'starttime', 'endtime', 'basename', 'title', 'subtitle', 'description', 'filesize')
			// ->join('channel_old_id_ALL', 'channel_old_id_ALL.chanid', 'recorded.chanid')
			->orderBy('starttime', 'desc')->get();
		$dats = [];
		// Log::info('tv-getList', $datx->toArray());
		$basebaneIdx = 0;
		foreach($upcoming as $d) {
			$d->basename = --$basebaneIdx;
			$dx = $this->convertToLocalTime($d);
			array_push($dats, $dx);
		}
		foreach($datx as $d) {
			$d = $this->convertToLocalTime($d);
			$dx = $this->checkFileExist($d);
			if (!is_null($dx->filename)) {
				array_push($dats, $dx);
				// $this->showData("AFT", $dx);
			}
		}
		return ['lst' => $dats, 'status' => "OK"];
	}
	public function del(Request $d) { //Log::info("tvmanager-del", $d->toArray());
		Log::info("tvmanager-del file: $d->filename");
		$recd = Recorded::find($d->recordedid);
		$recd->watched = 2;
		$recd->deletepending = true;
		$recd->recgroup = 'Deleted';
		$recd->bookmarkupdate = date('Y-m-d h:i:s', time());
		$recd->update();
		// Log::info("lastLine:[$lastLine], status=[$retval]");
		return $this->getList();
	}
  // private function fmtLink($link) {
  //   $lnk = '';
  //   $doc_dir = config('global.doc_dir');
  //   $docs = explode('@', $link); //Log::info([$d->filename, $docs]);
  //   if (count($docs) == 1) {
  //     $doc = $docs[0];
  //     $dname = preg_replace('/_|-/', ' ', $doc);
  //     if (filter_var($doc, FILTER_VALIDATE_URL)) $lnk = "<a href='$doc' target='_blank'>$dname</a>";
  //     else $lnk = "<a href='$doc_dir/$doc' target='_blank'>$dname</a>";
  //   } else {
  //     $lnk = "<ol>";
  //     foreach($docs as $doc) {
  //       $dname = preg_replace('/_|-/', ' ', $doc); //Log::info(['dname', $dname]);
  //       if(filter_var($doc, FILTER_VALIDATE_URL)) $lnk .= "<li><a href='$doc' target='_blank'>$dname</a></li>";
  //       else $lnk .= "<li><a href='$doc_dir/$doc' target='_blank'>$dname</a></li>";
  //     }
  //     $lnk .= "</ol>";
  //   }
  //   return $lnk;
  // }
	// // public function getList() {
	// // 	$userId = Auth::user()->id;
	// // 	// Log::info('userId', [$userId, Auth::user()]);
	// // 	$dats = Memo::select('id', 'user_id', 'date', 'tag', 'reminder', 'details', 'link')->where([ ['status', 'A'], ['user_id', $userId] ])->orderBy('date', 'desc')->get();
	// // 	// Log::info('memo-getList', $dats->toArray());
	// // 	foreach($dats as $d) {
	// // 		$details = $this->en_de_cryptTxt($d->details, true);
	// // 		$details = preg_replace('/<strike>/', '<div style="color:darkgreen">', $details);
	// // 		$details = preg_replace('/<\/strike>/', '</div>', $details);
	// // 		$d->details = $details;
	// // 		$d->date = substr($d->date, 0, 16);
  // //     if ($d->link != null) $d->lnk = $this->fmtLink($d->link);
	// // 	}
	// // 	Log::info('memo-field', $dats[0]->toArray());
	// // 	// return Collect($dats);
	// // 	return ['lst' => $dats, 'status' => "OK"];
	// // }
	// private function en_de_cryptTxt($txt, $decrypt=false) {
	// 	$cm = Cipherkey::find(1);
	// 	$enkey = $cm->encrypt_key;
	// 	$enmod = $cm->encrypt_mode;
	// 	$iv = $cm->iv;
	// 	if ($decrypt) return openssl_decrypt($txt, $enmod, $enkey, 0, $iv);
	// 	return openssl_encrypt($txt, $enmod, $enkey, 0, $iv);
	// }
	// public function upd(Request $da) { Log::info("upd-memo id=$da->id", $da->toArray());
	// 	$nd = Memo::find($da->id);
	// 	$dx = $da->toArray();
	// 	foreach($dx as $k => $val) {
	// 		if ($k == 'id') continue;
	// 		$nd->$k = $val;
	// 	}
	// 	// Log::info("upd memo link $nd->link");
	// 	$nd->details = $this->en_de_cryptTxt($da->details);
	// 	try {
	// 		$nd->save();
	// 		$nd->details = $da->details;
  //     if ($nd->link != null) $nd->lnk = $this->fmtLink($da->link);
	// 		return ['row' => $nd, 'status' => "OK" ];
	// 	} catch(Exception $ex) {
  //     return $ex->getMessage();
	// 	}
	// }
	// public function add(Request $d) { Log::info($d->toArray());
	// 	$nd = new Memo($d->toArray()); Log::info('new Memo to DB', [$nd]);
	// 	$nd->details = $this->en_de_cryptTxt($nd->details);
	// 	try {
  //     $nd->save();
	// 		$nd->details = $this->en_de_cryptTxt($nd->details, true);
  //     if ($nd->link != null) $nd->lnk = $this->fmtLink($nd->link);
  //     // Log::info('memo/add', $nd->toArray());
	// 		return ['row' => $nd, 'status' => "OK" ];
	// 	} catch(Exception $ex) {
	// 		return $ex->getMessage();
	// 	}
	// }
	// public function del(Request $d) { // Log::info("delete Memo", [$d->id, $d['id']]);
	// 	// $appId = $d['id'];
	// 	$appId = $d->id;
	// 	Log::debug("delete appId=[$appId]");
	// 	try {
	// 		Memo::destroy($appId);
	// 		return ['status' => "OK"];
	// 	} catch(Exception $ex) {
	// 		return $ex->getMessage();
	// 	}
	// }
	// private function XXXXgetModel($d, $action) {
	// 	$nd = null;
	// 	if ($action == 'upd') $nd = Memo::find($d['id']);
	// 	else if ($action == 'add') $nd = new Memo;
	// 	$nd->user_id = Auth::user()->id;
	// 	foreach($d as $key => $val) { $nd[$key] = $val; }
	// 	$nd->details = $this->en_de_cryptTxt($nd->details);  // this is necessary to encrypt otherwise it will not encrypt
	// 	return $nd;
	// }
	// //////////////////////////////////////////////////////////////
	// /**
	//  * This is a very simple function to calculate the difference between two datetime values,
	//  * returning the result in seconds. To convert to minutes, just divide the result by 60.
	//  * In hours, by 3600 and so on.
	//  * @method time_diff
	//  * author: swang
	//  * created at 2017-05-17T01:15:50-040
	//  * revised at 2017-05-17T01:15:50-040
	//  * version [version]
	//  * @param  [datetime] $dt1 [ in format YYYY-mm-dd 13:23:14]
	//  * @param  [datetime] $dt2 [ in format YYYY-mm-dd 13:23:14]
	//  * @return [diff in number of seconds]   [$dt1 - $dt2]
	//  */
	// private function XXXtime_diff($dt1, $dt2){
	//     $y1 = substr($dt1,0,4);
	//     $m1 = substr($dt1,5,2);
	//     $d1 = substr($dt1,8,2);
	//     $h1 = substr($dt1,11,2);
	//     $i1 = substr($dt1,14,2);
	//     $s1 = substr($dt1,17,2);

	//     $y2 = substr($dt2,0,4);
	//     $m2 = substr($dt2,5,2);
	//     $d2 = substr($dt2,8,2);
	//     $h2 = substr($dt2,11,2);
	//     $i2 = substr($dt2,14,2);
	//     $s2 = substr($dt2,17,2);

	//     $r1=date('U',mktime($h1,$i1,$s1,$m1,$d1,$y1));
	//     $r2=date('U',mktime($h2,$i2,$s2,$m2,$d2,$y2));
	//     return ($r1-$r2);
	// }
	// /**
	//  * [index description]
	//  * @method index
	//  * author: swang
	//  * created at 2017-05-10T16:31:40-040
	//  * revised at 2017-05-10T16:31:40-040
	//  * version [version]
	//  * @return [type] [description]
	//  */
	// public function XXX_index() {
	// 	$user = Auth::user();
	// 	$dats = Memo::select('id', 'user_id', 'date', 'tag', 'details')
	// 	 		->where([ ['status', 'A'], ['user_id', $user->id] ])->orderBy('date', 'desc')->get();

	// 	foreach($dats as $d) {
	// 		$d->details = $this->en_de_cryptTxt($d->details, true);
	// 		$d->date = substr($d->date, 0, 16);
	// 	}

	// 	$gridData = Collect($dats);
	// 	return view('Fin.memo', compact('gridData'));
	// }
	// private function XXXX_en_de_cryptTxt($txt, $decrypt=false) {
	// 	$cm = Cipherkey::find(1);
	// 	$enkey = $cm->encrypt_key;
	// 	$enmod = $cm->encrypt_mode;
	// 	$iv = $cm->iv;
	// 	if ($decrypt) return openssl_decrypt($txt, $enmod, $enkey, 0, $iv);
	// 	return openssl_encrypt($txt, $enmod, $enkey, 0, $iv);
	// }
	// private function XXXsetNextReminder($d) {
	// 	$today = date_create();
	// 	$newDueDate = date_add($today, $this->date_interval($d->recursive))->format('Y-m-d'); //dd($newDueDate, $d->date, $today);
	// 	$nd = [ 'user_id' => $d->user_id,
	// 				'due date' => $newDueDate,
	// 				'type' => $d->type,
	// 				'message' => $d->message,
	// 				'details' => $d->details,
	// 				'recursive' => $d->recursive,
	// 				'status' => 'A' ];

	// 	$nrd = Reminder::firstOrCreate($nd);
	// }
	// /**
	//  * Display a listing of the resource.App\Models\HomePag
	//  *
	//  * @return Response
	// */
	// private function XXXdate_interval($days) {
  //   	return date_interval_create_from_date_string("$days days");
  //   }
	// private function XXXsetDueIn($d) {
	// 	$dueDate = substr($d['due date'], 0, 10);
	// 	$today = Date('Y-m-d', time());
	// 	$dueInDays = $this->time_diff($dueDate, $today) / (60*60*24);
	// 	if ($dueInDays > 365) $d['due in'] = sprintf('%2.1f', $dueInDays / 365) . "年";
	// 	else if ($dueInDays > 30) $d['due in'] = sprintf('%2.1f', $dueInDays / 30) . "月";
	// 	else if ($dueInDays == 1) $d['due in'] = "Due TOMORROW";
	// 	else if ($dueInDays == 0) $d['due in'] = "Due TODAY !!!";
	// 	else if ($dueInDays < 0) $d['due in'] = "过  期";
	// 	else $d['due in'] = "$dueInDays 天";
	// }
}

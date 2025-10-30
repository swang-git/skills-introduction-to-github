<?php
namespace App\Http\Controllers;

use App\Models\dictionary\Dictionary;

use App\Http\Controllers\Controller;
// use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DictionaryController extends Controller {
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
	}
	public function getList() {
		$dats = Dictionary::select('id', 'datetime', 'english', 'chinese', 'note', 'lnks')->where('status', 'A')->orderBy('datetime', 'desc')->get();
		// Log::info('Dictionary-getList', $dats->toArray());
		foreach($dats as $d) {
			if ($d->lnks != null) $d->lnk = $this->fmtLink($d->lnks);
		}
		return ['list' => Collect($dats), 'status' => "OK"];
	}
	private function fmtLink($link) {
    $lnk = '';
    $doc_dir = config('global.doc_dir');
    $docs = explode('@', $link); //Log::info([$d->filename, $docs]);
    if (count($docs) == 1) {
      $doc = $docs[0];
      $dname = preg_replace('/_|-/', ' ', $doc);
      if (filter_var($doc, FILTER_VALIDATE_URL)) $lnk = "<a href='$doc' target='_blank'>$dname</a>";
      else $lnk = "<a href='$doc_dir/$doc' target='_blank'>$dname</a>";
    } else {
      $lnk = "<ol>";
      foreach($docs as $doc) {
        $dname = preg_replace('/_|-/', ' ', $doc); //Log::info(['dname', $dname]);
        if(filter_var($doc, FILTER_VALIDATE_URL)) $lnk .= "<li><a href='$doc' target='_blank'>$dname</a></li>";
        else $lnk .= "<li><a href='$doc_dir/$doc' target='_blank'>$dname</a></li>";
      }
      $lnk .= "</ol>";
    }
    return $lnk;
  }
	public function upd(Request $d) { Log::info("Dictionary-upd", $d->toArray()); 
		$nd = Dictionary::find($d->id);
		$dx = $d->toArray();
		foreach($dx as $k => $val) {
			if ($k == 'id') continue;
			$nd->$k = $val;
		}
		try {
			$nd->save();
			if ($nd->lnks != null) $nd->lnk = $this->fmtLink($d->lnks);
			return ['wd' => $nd, 'status' => "OK"];
		} catch(Exception $ex) {
			return $ex->getMessage();
		}
	}
	public function add(Request $d) { Log::info("dictionary-add", $d->toArray());
		$nd = new Dictionary($d->toArray());
		try {
			$nd->save();
			if ($nd->lnks != null) {
				$doc_dir = config('global.doc_dir');
				$nd->lnk = "<a href='$doc_dir/$nd->lnks' target='_blank'>Related Document</a>";
			}
			return ['wd' => $nd, 'status' => "OK"];
		} catch(Exception $ex) {
			return $ex->getMessage();
		}
	}
	public function del(Request $d) {
		try {
			Dictionary::destroy($d['id']);
			// return ['wd' => $nd, 'status' => "OK"];
			return ['wd' => $d, 'status' => "OK"];
		} catch(Exception $ex) {
			return $ex->getMessage();
		}
	}
}

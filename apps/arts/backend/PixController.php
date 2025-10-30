<?php
namespace App\Http\Controllers\Art;

use App\Models\Art\Photo;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class PixController extends Controller
{
	/**
	 * Display a listing of dictories of photos.
	 *
	 * @return Response
	 */
	public function index()
	{
		// $px = DB::table('MyWeb.Photo')->select('loc', 'tit', 'tip')->where([['status', '<>', 'U'], ['type', '<>', 'XXX']])->get();
		$px = Photo::select('loc', 'tit', 'tip')->where([['status', '<>', 'U'], ['type', '<>', 'XXX']])->get();
		$ptx = [];
		foreach($px as $p) {
			$p->pix = "/$p->loc/icon";
			$pxt[] = $p;
		}													//dd(is_array($pxt));
		return view('Art.pictures.index', compact('pxt'));
	}
	function showpix($photodir, $pdir, $idx, $tit) {
		$fullpath = config('constants.dirs.webdata') . "/$photodir/$pdir";  //dd($fullpath);
// 		$px = scandir($fullpath); dd($px);
		$px = glob("$fullpath/*.{jpg,jpeg,gif,png,JPG}", GLOB_BRACE); //dd($px);
		$pix = basename($px[$idx - 1]);
		$imgsrc = "/photos/$pdir/$pix";
		$nxt = $idx+1;
		$prv = $idx-1;
		$cnt = count($px);
		if ($nxt > $cnt) $nxt = 1;
		if ($prv < 1) $prv = $cnt;
// 		$line = "<img style='max-height:580px' src='$sline'>";
// 		$line.= "<br><p><span style='display:inline;margin-left:5px;float:right;' class='otxt'>$tit ";
// 		$line.= "<span class='ntxt'>第 $nxt 张 (共 " . count($px) . " 张)</span></span>";
		$prev_idx = $prv;
		$next_idx = $nxt;
		return view('Art.pictures.show', compact('imgsrc', 'prev_idx', 'idx', 'next_idx', 'tit', 'pdir', 'cnt'));
	}
}

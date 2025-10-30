<?php
namespace App\Http\Controllers\Art;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\DB;


class LegacyController extends Controller
{
	public function __construct()
	{
		// if ( env('APP_ENV') !== "dev") log_client_info();
	}

	function index()
	{
		$orders = array();
		$reading = DB::table('MyWeb.reading')->where('active', 1)->orderBy('id', 'desc')->get();
		$r20 = clone($reading[0]);
		$r20->id = 20;
		$r20->gbname = '高点文章';
		$r20->last_ptime = Date('Y-m-d', time());
		foreach($reading as $read) {
			$id = $read->id;
			$arts = DB::table('MyWeb.txtfiles')->select('title', 'id')->where('tid', $id)->orderBy('ptime', 'desc')->take(6)->get();
			$len = $this->art_len($arts);
			$read->arts = $arts;
			$read->len = $len;
			$read->marked = false;
			$orders[] = $len;
		}
		$arts = DB::table('MyWeb.txtfiles')->select('title', 'id')->orderBy('num_click', 'desc')->take(6)->get();
		$r20->arts = $arts;						//dd($r20);
		$len = $this->art_len($arts);
		$orders[] = $len;
		$r20->len = $len;
		$r20->marked = false;
		$reading[] = $r20;
		$reading = $this->reorder($reading, $orders);

		return view('Art.legacy.index', compact('reading'));
	}
	function reorder($reading, $orders) {
		$r = [];
		sort($orders);
		for ($i=0; $i<10; $i++) {
			$max = array_pop($orders);
			$min = array_shift($orders);
			$r[] = $this->get_reading($reading, $max);
			$r[] = $this->get_reading($reading, $min);
		}
		return $r;
	}
	private function get_reading($reading, $len) {
		foreach($reading as $read) {
			if ($read->len == $len and !$read->marked) {
				$read->marked = true;
				return $read;
			}
		}
	}
	private function art_len($arts) {
		$max = 0;
		foreach($arts as $a) $max = max($max, mb_strlen($a->title, 'UTF-8'));
		return $max;
	}
	function showchpart($id, $start, $end) {   //dd("$id, $start, $end");
		$artinfo = DB::table('MyWeb.txtfiles')->where('id', $id)->get(); 	//dd($artinfo);
		$fname = $artinfo[0]->fname;
		$fnm = READING_DIR . "/$fname";			//dd($fnm);
		$lines = file($fnm);
		$contents = [];
		for ($i=$start; $i<$end; $i++) {
			$line = $lines[$i];
			$contents[] = nl2br(iconv('GB18030', 'UTF-8//TRANSLIT//IGNORE', $line));
		}
		return view('Art.legacy.showchpart', compact('contents'));
	}
	private function debug_show_lines($lines) {
		$idx = 0;
		foreach($lines as $line) {
			echo $idx++ . ") " . mb_convert_encoding($line, 'UTF-8', 'GB18030') . "<p><p>";
		} exit;
	}
	private function XXget_art($id, $tit, $startline) {
		$artinfo = DB::table('MyWeb.txtfiles')->where('id', $id)->get();
		$fullpath = config('constants.dirs.reading') . '/' . $artinfo[0]->fname;
		$lines = file($fullpath);		//echo "$startline, $tit<p>"; $this->debug_show_lines($lines);

		$tit = trim($tit);
		$ptit = mb_substr($tit, 10, strlen($tit) - 10);
		while (count($lines) > 0) {
			$line = array_shift($lines);
			$line = mb_convert_encoding($line, 'UTF-8', 'GB18030');
			if (substr_count($line, $ptit) > 0) break;
		}													echo "$ptit, $line<br>";

		$ret = [];
		$pat = '@^·【@';
		foreach ($lines as $line) {
			$line = trim($line);
			if ($line == '') continue;
			$line = mb_convert_encoding($line, 'UTF-8', 'GB18030');
			if (preg_match($pat, $line)) { break; }
			$ret[] = $line;
		}
		$str =  implode("<br /><br />", $ret);				//dd($str);
		return $str;
	}
	private function showchp($artinfo) { 								//dd($artinfo);
		$fname = $artinfo->fname;
		$fnm = config('constants.dirs.reading') . "/$fname";			//dd($fnm);
		$lines = file($fnm); 											//$this->debug_show_lines($lines);
		$cnt = count($lines);
		$chps = [];
		$start = 0;
		$end = 0;
		$id = $artinfo->id;
		$tit = $artinfo->title;
		array_shift($lines);
		array_shift($lines);
		array_shift($lines);
		$aut = $artinfo->author;
		$tim = $artinfo->ptime;
		$chps[] = "<div class='atit' style='text-align:center'>$tit</div>";
		$chps[] = "<div class='lnkx'>$aut $tim</div>";
		$idx = 1;
		while (count($lines) > 0) {
			$line = array_shift($lines);
			$line = trim($line);
			if ($line == '' or preg_match('@<font size=@i', $line) or preg_match('@ajectToRect@i', $line)) continue;
			$line = trim(mb_convert_encoding($line, 'UTF-8', 'GB18030'));
			$line = "$line\n";

			$xpat = '(^)((第\W{2,6}章)|(·【(.*)】)|(·〖(.*)〗)|(·序)|(序)|(·)|(\d+．))'; //dd($xpat);
			if (preg_match("@$xpat@", $line)) {
				$chps[] = '</div>';
				$tit = trim($line, '·');
				$id = null;
				$startline = null;
				if (substr_count($tit, '&') > 0) {	// handle case like 【马前】37225&252&百年老狼──经济危机的脉络(9)
					$pat = '@(\d+&)@';
					$tit = preg_replace($pat, '', $tit, -1, $rpcount);
					$tit = preg_replace('@&@', '', $tit);
					if ($rpcount === 2) {
						$pat = '@(.*)(·|】)(\d+)&[-]*(\d+)&(.*)@';
						$id = trim(preg_replace($pat, '$3', $line)); 			//dd("$line,$tit, $id");
						$startline = trim(preg_replace($pat, '$4', $line)); 	//echo "$line, $tit, $id, $startline<br>";
					}
					else if ($rpcount === 1) {
						$pat = '@(.*)(·|】)(\d+)&(.*)@';
						$id = trim(preg_replace($pat, '$3', $line)); 					//dd("$line, $tit, $id, $rpcount");
					}

					if (!is_numeric($id)) {
						$pat = '@(.*)(·|〗)(\d+)&(.*)@';
						$id = trim(preg_replace($pat, '$3', $line)); 					//dd("$line, $tit, $id, $rpcount");
					}

					$chps[] = "<a class='lnk1' href='$id' target=_blank>$tit</a><br />";
					// $chps[] = "<a class='lnk1' href='/art/legacy/showart/$id' target=_blank>$tit</a><br />";
				} else {
					$chps[] = "<div style='margin-left:50px' class='lnk1' onclick=read($idx)>$tit</div><p>";
				}

				$chps[] = "<div id='$idx' class='atxt' style='display:none'>";
				//if (isset($id)) $chps[] = "<div class='otxt'>" . $this->get_art($id, $tit, null) . '</div>';
				$idx++;
			} else {									//echo("$line,$tit, $id");
				if (preg_match('@∞⊙∞@i', $line)) {
					$line = '<p style="height:20px"><img style="display:absolute;transform:translateX(30%);" src="/img/hr-grey.gif"></p>';
				}
				else if (preg_match('@-wwimgs@i', $line)) {
					$line = preg_replace('@-@', '', $line);
					$line = trim($line);
					$sline = "/$line";
					$line = "<img src='$sline'>";
				}
				else if (preg_match('@src=imgs@i', $line)) {				//dd($line);
					$line = preg_replace('@src=imgs@i', 'src=/imgs', $line);
				}
				$chps[] = "<div class='otxt'>" . nl2br($line) . "</div>";
			}
		}
		return view('Art.legacy.showchp', compact('tit', 'chps'));
	}
	function showart($id) {	 //echo $id;
		//$xxx = DB::select('select * from reading where active=1');
		$artinfo = DB::table('MyWeb.txtfiles')->where('id', $id)->get(); 							//dd($artinfo);
		$tid = $artinfo[0]->tid;
		$fname = $artinfo[0]->fname;
		$chps = [1, 2, 3, 5, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 19];
		if ( in_array($tid, $chps )) {
			return $this->showchp($artinfo[0]);
		}
		$fnm = config('constants.dirs.reading') . "/$fname";		/* dd(env('APP_ENV')); dd(config('constants.dirs.reading')); dd($fnm); */
		$lines = file($fnm);					//dd($lines);
		$contents = [];
		foreach($lines as $line) {
			$line = trim($line);
			$line = trim($line, '-');
			if ($line == '') continue;
			$line = iconv('GB18030', 'UTF-8//TRANSLIT//IGNORE', $line);
			if (preg_match('@∞⊙∞@i', $line)) {
				$line = '<p style="height:20px"><img style="display:absolute;transform:translateX(60%);" src="/img/hr-grey.gif"></p>';
				$contents[] = "$line";
				continue;
			} else if (preg_match('@<img\s+src="imgs@i', $line)) {
				$line = preg_replace('@<img\s+src="imgs@i', '<img src="/imgs', $line);
				$contents[] = $line;
			} else if (!preg_match('@<img@i', $line) and preg_match('@^-(.*).(jpg|gif|png|jpeg)@i', $line)) {
				$line = trim($line, '-');
				$sline = "/imgs/$line";
				$sline = str_replace('imgs\/imgs', '/imgs', $sline);
				$line = "<img style='max-width:600px' src='$sline'>";
				$contents[] = $line;
			} else if (preg_match('@^<a href=/pslide.php@i', $line)) {
				$line = preg_replace('@&@i', '/', $line);
				$line = preg_replace('@/pslide.php\?@i', '/art/legacy/showpixs', $line);
				$contents[] = $line;
			} else if (!preg_match('@<(img|marquee)@i', $line) and preg_match('@.(jpg|jpeg|png|gif)@i', $line)) {
				$x = explode('|', $line);
				$line = $x[0];
				if (preg_match('@imgs/@i', $line)) {
					$sline = $line;
				} else if (preg_match('@/@', $line)) {
					$sline = "/imgs/$line";
				} else {
					$sline = "/imgs_2006/$line";
				}
				$line = "<img src='$sline'>";
				$contents[] = $line;
			}

			$contents[] = nl2br($line);
		}													//dd($contents);

		if ($artinfo[0]->tid == 7) {
			$line = trim($contents[3]);
			$tit = trim($contents[0]);
			$pdir = $line;
			$fullpath = config('constants.dirs.webdata') . "/$pdir";						//dd($pdir);
			if (is_dir($fullpath) and (preg_match('@连环画@', $tit) or preg_match('@^imgs/@i', $pdir))) {
				$dix = explode('/', $pdir);
				$idir = $dix[0];
				$pdir = $dix[1];
				return $this->showpixs($idir, $pdir, 0, $tit);
			} else if (is_dir($fullpath) and preg_match('@^(music|pingshu)/@i', $pdir)) {
				$dix = explode('/', $pdir);
				$idir = $dix[0];
				$pdir = $dix[1];
				return $this->playmusic($tit, $idir, $pdir, 0);
			} else if (preg_match('@中国塔吉克族──婚俗写真@', $tit)) {
				$idir = "imgs";
				$pdir = "TaJiKeHunSu";
				return $this->showpixs($idir, $pdir, 0, $tit);
			}

			if (preg_match('@\|@i', $line)) {
				$x = preg_split('@\|@', $line);
				$line = $x[0];
				//dd($line);
			}

			if (!preg_match('@<img\s+@i', $line) and preg_match('@.(jpg|jpeg|gif|png)@i', $line)) {
				$sline = "/imgs/$line";
				$sline = str_replace('/imgs/imgs', '/imgs', $sline);
				$line = "<img src='$sline' style='max-width:610px'>";
				$contents[3] = $line;
			}
			else if (preg_match('@.(wma|au|swf|asf|wmv|rm)@i', $line)) {
				if (preg_match('@movies@i', $line)) {
					$sline = $line;
				} else {
					$sline = "/music/$line";
					$sline = str_replace('/music/music', '/music', $sline);
				}
				//$sline = "<object width='800' data='$sline'></object>";
				return redirect($sline);
			}
			else if (preg_match('@.(wma|mp3|au)@i', $line)) {
				$sline = "/music/$line";
				$sline = str_replace('/music/music', '/music', $sline);
				$line = "<audio id='avdio' controls>";
				$line.= "<source src='$sline' type='audio/au'>";
				$line.= "<source src='$sline' type='audio/mpeg'>";
				$line.= "<source src='$sline' type='audio/mp3'>";
				$line.= "<source src='$sline' type='audio/wma'>";
				$line.="</audio>";
				$contents[3] = $line;
			} //dd($contents[3]);
			else if (preg_match('@.(avi|mp4|rm|ram|swf|asf|wmv)@i', $line)) {
				$sline = "/movies/$line";
				$sline = str_replace('/movies/movies', '/movies', $sline);
				$line = "<video id='avdio' width='600' height='400' controls>";
				$line.= "<source src='$sline' type='video/mp4'>";
// 				$line.= "<source src='$sline' type='video/avi'>";
// 				$line.= "<source src='$sline' type='video/rm'>";
// 				$line.= "<source src='$sline' type='video/ram'>";
// 				$line.= "<source src='$sline' type='video/asf'>";
// 				$line.= "<source src='$sline' type='video/wmv'>";
				$line.="</vedio>";
				$contents[3] = $line;
			} //dd($contents[3]);
		}
		return view('Art.legacy.showart', compact('contents'));
	}
	function showlst($id) {
		$tit = DB::table('reading')->select('gbname')->where('id', $id)->get()[0]->gbname;
		$cat = trim($tit);	//dd($tit);
		$arts = DB::table('txtfiles')->select('title', 'id')->where('tid', $id)->orderBy('ptime', 'desc')->get(); //dd($arts);
		return view('Art.legacy.showlst', compact('cat', 'arts'));
	}
	function showpixs($idir, $pdir, $pagen, $tit) {	//dd($pdir);
		$fullpath = config('constants.dirs.webdata') . "/$idir/$pdir";
		$px = glob("$fullpath/*.{gif,jpeg,jpg,png,jfif}", GLOB_BRACE);						//dd($px);

		if ($pagen >= count($px)) $pagen = 0;
		$fname = basename($px[$pagen]);
		$sline = "/$idir/$pdir/$fname";
		$nxtpix = $pagen + 1;
		$line = "<a href='/art/legacy/showpixs/$idir/$pdir/$nxtpix/$tit'><img src='$sline' style='max-height:590px'><br>";
		$line.= $tit . "<span style='font-size:16pt;font-family:normal'> ( 共" . count($px) . "页 )</span>";
		if (file_exists("$fullpath/photos.txt")) {
			$lines = file("$fullpath/photos.txt");
			if (isset($lines[$pagen])) {
				$txt = $lines[$pagen];
				$line.= "<div class='atxt'>" . iconv('GB18030', 'UTF-8', $txt) . "</div>";
			}
			$line.= '</a>';
		} else if (file_exists("$fullpath/$nxtpix.txt")) {
			$txt = file("$fullpath/$nxtpix.txt")[0];
			$line.= "<div class='atxt'>" . iconv('GB18030', 'UTF-8', $txt) . "</div>";
		} else {
			$line.= "<p style='font-size:18pt;font-family:normal'>第". $nxtpix."页";
		}
		return view('Art.legacy.showpixs', compact('line'));
	}
	function playmusic($tit, $idir, $pdir, $pagen) {	//dd($pdir);
		$fullpath = config('constants.dirs.webdata') . "/$idir/$pdir";
		$px = glob("$fullpath/*.{mp3,wma}", GLOB_BRACE);						//dd($px);

		if ($pagen >= count($px)) $pagen = 0;
		$fname = basename($px[$pagen]);
		$sline = "/$idir/$pdir/$fname";
		$nxtpix = $pagen + 1;
		$line = "<div style='font-size:24px;font-fmaily:normal'>$tit ( 共" . count($px) . "首 )</div><p>";
		$line.= "<audio id='avdio' controls>";
		$line.= "<source src='$sline' type='audio/mpeg'>";
		$line.= "<source src='$sline' type='audio/mp3'>";
		$line.= "</audio>";
		if (file_exists("$fullpath/content.txt")) {
			$lines = file("$fullpath/content.txt");
			if (isset($lines[$pagen])) {
				$txt = $lines[$pagen];
				$line.= "<p><span class='atxt'>" . iconv('GB18030', 'UTF-8', $txt) . "</span>";
			}
		} else {
			$line.= "<p class='atxt'>第". $nxtpix."篇";
		}
		$line.= "<a class='lnkx' href='/art/legacy/playmusic/$tit/$idir/$pdir/$nxtpix'> ( 点击听下一首 )</a>";
		return view('Art.legacy.showpixs', compact('line'));
	}
	// public function collections()
	// {
	// 	$pxar = ['PXQG', 'PXHY', 'PXWW', 'PXZJ'];
	// 	$titlst = DB::table('MyWeb.art_class')->select('tag', 'name')->where('status', 'A')->
	// 	whereNotIn('tag', $pxar)->orderBy('name')->get();		//dd(is_array($titlst));
	// 	$titlist = [];
	// 	foreach($titlst as $a) {
	// 		$tag = $a->tag;
	// 		$tcnt = DB::select("select count(*) as cnt from MyWeb.daily_data where tag =:tag", ['tag'=>$tag]); //dd($cnt);
	// 		$a->cnt = $tcnt[0]->cnt;
	// 		$titlist[] = $a;	  //convert objects to array
	// 	}																	//dd($titlist);
	// 	return view('legacy.collectionlist', compact('titlist'));
	// }
}

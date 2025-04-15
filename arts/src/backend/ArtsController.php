<?php
namespace App\Http\Controllers;
use App\Models\arts\HomePage;
use App\Models\arts\DailyDat;
use App\Models\arts\DailyArt;
// old articles
use App\Models\arts\ArtClass;
use App\Models\arts\DailyData;

use App\Http\Controllers\Controller;
// use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;

// require_once 'vendor/autoload.php';
// use GeoIp2\Database\Reader;
// use geoip2\Database\Reader;
// require_once '/home/swang/sites/devx/app/Libs/myLibs.php';
use App\Traits\MyTraits;

class ArtsController extends Controller
{
	use MyTraits;
	public $pxar = ['PXQG', 'PXHY', 'PXWW', 'PXWX', 'PXZJ', 'PXJL'];
	public function __construct() {
		// moved to app/MyTrait -- a shared libs
		// Log::info(['arts__construct $_SERVER["HTTP_USER_AGENT"]:', $_SERVER['HTTP_USER_AGENT']]);
		// $this->saveLog('artsHome');
	}
	public function index() { }

	public function logClientPlatform(Request $request) {
		Log::info($request);
	}
	public function getList() {
		$artWW = collect(HomePage::where('tag', 'PXWW')->orderBy('ymd', 'desc')->take(2)->get()); //dd($arts);
		$artQG = collect(HomePage::where('tag', 'PXQG')->orderBy('ymd', 'desc')->take(2)->get());
		$artWX = collect(HomePage::where('tag', 'PXWX')->orderBy('ymd', 'desc')->take(2)->get()); //dd($arts);
		$artJL = collect(HomePage::where('tag', 'PXJL')->orderBy('ymd', 'desc')->take(2)->get());
		$artHY = collect(HomePage::where('tag', 'PXHY')->orderBy('ymd', 'desc')->take(2)->get()); //dd($arts);
		// $artZJ = collect(HomePage::where('tag', 'PXZJ')->orderBy('ymd', 'desc')->take(2)->get());
		// $arts = $artHY->merge($artWX)->merge($artWW)->merge($artQG)->merge($artZJ);
		$arts = $artWW->merge($artQG)->merge($artWX)->merge($artJL)->merge($artHY);
		$links = [];
		$titles = [];
		$subtits = [];
		$updtime = [];
		foreach($arts as $a) {
			$links[] = ['tag'=>$a->tag, 'ymd'=>$a->ymd];
			$titles[] = $a->tit;
			$cnt = $a->cnt."篇";
			$updtime[] = $a->addtm . " 更新 (" . $cnt . ")" ;
			// $flw = $a->flw>0 ? $a->flw . "条回复(共{$a->fsz}字)" : null;
			// $img = $a->img>0 ? $a->img . "图片" : null;
			// $vdo = $a->vdo>0 ? $a->vdo . "视频" : null;
			// $afz = $a->afz>0 ? "文章总字数:" . $a->afz . "字" : null;
			// $subtits[] = "$cnt $flw $img $vdo $afz";
		}
		$links = Collect($links);
		$titles = Collect($titles);
		$subtits = Collect($subtits);
		$updtime = Collect($updtime);
		//___ToDo_later $this->saveLog('artsList');
		return ['links'=>$links, 'titles'=>$titles, 'updtime'=>$updtime, 'status' => "OK"];
		// return ['links'=>$links, 'titles'=>$titles, 'subtits'=>$subtits, 'updtime'=>$updtime];
	}
	public function getCont($tag, $ymd)
    {
        $d1 = date_create($ymd);
		$d2 = date_create('2016-08-15');
		if ($d1 > $d2) {
			$arts = DailyDat::where([['tag', $tag], ['ymd', $ymd]])->orderBy('idx')->get(); 			//dd($artlist);
			$pagetit = HomePage::where([['tag', $tag], ['ymd', $ymd]])->select('tit')->first()->tit; 	//dd($pagetit);
		} else {
			$arts = DB::table('daily_data')->where([ ['tag', $tag], ['ymd', $ymd] ])->get();
			$pagetit = $this->get_cn_tit($tag) . " (" . $this->get_cn_ymd($ymd) . ")";
		}
		// foreach($arts as $a) { unset($a->tag); }
		$xymd = HomePage::where('tag', $tag)->select('ymd')->orderBy('ymd', 'desc')->take(100)->get();          //dd(Collect($ymds));
		$ymds = [];
      	foreach($xymd as $x) $ymds[] = $x->ymd;
		$pagetit = $this->get_cn_tit($tag) . " (" .$this->get_cn_ymd($ymd) . ")";
		// if ($this->userAgent == 'smartphone') $pagetit = "<span class='top-title'>$pagetit</span>";
		// else $pagetit = "<span class='top-title'>$pagetit</span> <span class='top-info'>(". count($arts) ."篇)</span>";
		// else $pagetit = "<span class='top-title'>$pagetit</span> <span class='art-info'>(". count($arts) ."篇)</span>";
      	$dats = $this->get_art_list($arts, $pagetit);
		$dats['ymds'] = $ymds;
		$dats['key'] = "/" . $tag . "/" . $ymd;   // use as url path as well
		//__ ToDo_later $this->saveLog("$tag/$ymd");
		return ['cont' => $dats, 'status' => "OK"];

		// $pagetit = $pagetit . " (". count($arts) . "篇)";
		// return $this->get_art_list($arts, $pagetit, 'List');
	}
	public function getText($tag, $ymd, $qid) { Log::info("arts-getText()");
		$d1 = date_create($ymd);
		$d2 = date_create('2016-08-15');
		$art = [];
		$art_flw = [];
		$artcont = null;
		if (in_array($tag, $this->pxar) and $d1 > $d2) {
			$arts = DailyArt::where([ ['tag', $tag], ['qid', $qid] ])->orderBy('idx')->get(); //dd($artcont);
			$artinfo = DailyDat::where([ ['tag', $tag], ['ymd', $ymd], ['qid', $qid] ])->first(); //dd($artinfo);
			// $artinfo->tit = proc_tit($artinfo->tit);
			// $artinfo->tit = proc_line($artinfo>tit);
		} else {
			list($arts, $artinfo) = $this->getCollArtCont($tag, $ymd, $qid);
		}

		$qids = DailyDat::where([ ['tag', $tag], ['ymd', $ymd] ])->orderBy('idx')->pluck('qid');
		// Log::info('qids', $qids->toArray());

		// $art = [];
		$art['id'] = $artinfo->id;
		$art['artId'] = $arts[0]->id;
		// $art['tag'] = $tag;
		// $art['ymd'] = $ymd;
		// $art['qid'] = $qid;
		$art['tit'] = $artinfo->tit;
		// $art['tit'] = proc_line(proc_tit($artinfo->tit));
		$art['sub'] = $this->get_sub($artinfo);
		$art['lnk'] = $artinfo->lnk;
		
		$txt = trim($arts[0]->txt); //Log::info($txt);
		
		// $txt = $this->process_text($txt);

		$txt = nl2br($txt); //Log::info($txt);
		$txt = str_replace("</tr><br />", "</tr>", $txt); //Log::info($txt);
		$txt = str_replace("</tbody><br />", "</tbody>", $txt); //Log::info($txt);
		
		// $txt = preg_replace('@<br /><br />@', '<br />', $txt);
		$art['txt'] = $txt;
		// $artinfo->afz = (int)(strlen($txt) / 2 + 0.5);   // more accurate calc afz
		// $art['sub'] = $this->get_sub($artinfo);
		// $art['txt'] = proc_line($txt) . "<br />";
		// $art['txt'] = proc_line($arts[0]->txt);
		$art['bak'] = "/art/$tag/$ymd";
		$art['qids'] = $qids->toArray();

		$flw = [];
		for ($i=1; $i<count($arts); $i++) {
			$a = $arts[$i];
			$a->txt = preg_replace('@[※]{9,}@', '※ ※ ※ ※ ※ ※ ※', $a->txt); //dd($a->txt);
			if (!empty($a->lvl)) $a->txt = $this->get_flw_prex($a->lvl) . $a->txt;
			$x = [];
			$x['txt'] = nl2br(trim($arts[$i]->txt));
			// $x['txt'] = trim($arts[$i]->txt);
			// $x['txt'] = proc_line($arts[$i]->txt) . "<br />";
			$x['sub'] = "回复($i) " . $this->get_sub($a);
			$x['fid'] = $a->fid;
			$x['id'] = $a->id;
			$flw[] = $x;
		}											//dd($flw);

		$pageType = "Cont";
		$pageTitle = $art['tit'];
		$artInfo = Collect(['art'=>$art, 'flw'=>$flw]);
		$userAgent = $this->userAgent;
		$page = "$tag/$ymd/$qid";
		//__ ToDo_later $this->saveLog($page);
		return ['text' => $artInfo, 'status' => "OK"];
		// return view('Art.art', compact('pageType', 'pageTitle', 'artInfo', 'userAgent'));
	}
	private function process_text($txt) {
		// $txt = preg_replace('@https://(.*)\s+@', '<br><a href="https://$1" target="_blank">链接</a>pic', $txt);
		// $txt = preg_replace('@https://(.*)\s+@', '<br><a href="https://$1" target="_blank">链接</a>pic', $txt);
		// $txt = preg_replace('@pic.twitter.com/(\w+)(.*)@', '<br><a href="https://pic.twitter.com/$1" target="_blank">$2</a><br>', $txt);
		$txt = preg_replace('@https://t.co/(.*)@', 'https://t.co/ $1', $txt);
		$txt = preg_replace('@pic.twitter.com/(\w+)(.*)@', 'pic.twitter.com/ $1$2<br>', $txt);
		
		return $txt;
	}
	private function get_flw_prex($lvl) {
		$ret = "";
        $blk = "☻";
        $wht = "☺";
        for ($i=0; $i<$lvl; $i++) {
            if ($i % 2 == 0) $ret .= $blk;
            else $ret .= $wht;
        }
        return $ret;
	}
	public function updText(Request $d) { Log::info("-fn-updText", [$d->toArray(), __line__, __file__]);
		// $d = Input::All(); // return $d;
		$flwIdx = isset($d['flwIdx']) ? $d['flwIdx'] : -1;
		if ($flwIdx >= 0) {
			$artId = $d['artId'];
			if ($artId >= 0) {
				$txt = $d['txt'];
				$dm = DailyArt::find($artId);
				$dm->txt = $txt;
				$dm->update();
				return ['status' => "OK"];
			}
		}
		$datId = $d['datId'];
		if ($datId > 0) {
			$tit = $d['tit'];
			$da = DailyDat::find($datId);
			$da->tit = $tit;
			$da->update();
		}
		$artId = $d['artId'];
		Log::info("artId=$artId");
		if ($artId >= 0) {
			$txt = $d['txt'];
			$dm = DailyArt::find($artId);
			// $txt = preg_replace('@\r@', '', $txt);
			$dm->txt = $txt;
			$dm->update();
		}
		return ['status' => "OK"];
	}
 	public function search($cat, $txt) {
        $arts = DailyDat::fromQuery('CALL MyWeb.art_search(?,?)', [$cat, "%$txt%"]);   // dd($arts);
		$pagetit = "搜索作者含有“${txt}”的文章";
		if ($cat == 'tit') $pagetit = "搜索题目含有“${txt}”的文章";
		else if ($cat == 'txt') $pagetit = "搜索文章内容含有“${txt}”的文章";
		$dats = $this->get_art_list($arts, $pagetit);
		$dats['key'] = "/" . $cat . "/" . $txt;       // use as url as well
		// $dats['key'] = "/search/" . $cat . "/" . $txt;       // use as url as well
		//__ToDo_later $this->saveLog("arts/$cat/$txt");
		return $dats;
    }
	private function get_art_list($arts, $topTitle, $page_type="XXXX") {
		$titles = [];
		$subtits = [];
		$links = [];
		$idx = 0;
		$cons = [];
		foreach($arts as $a) {
			$idx++;
			// $titles[] = $idx . ") " . proc_tit($a->tit);
			// $titles[] = $idx . ") " . $a->tit;
			$titles[] = $a->tit;
			// $titles[] = $a->idx . ") " . proc_line(proc_tit($a->tit));
			// $links[] = ['tag' => $a->tag, 'ymd' => $a->ymd, 'qid' => $a->qid];
			$links[] = ['tag' => $a->tag, 'ymd' => $a->ymd, 'qid' => $a->qid];
			$subtits[] = $this->get_sub($a);
			if ($a->img > 0) $cons[] = "image";
      		else if ($a->vdo > 0) $cons[] = "video_camera_back";
      		else $cons[] = "";
		}
		$titles = Collect($titles);
		$links = Collect($links);
		$subtits = Collect($subtits);
		$cnt = count($links);
		// $pageType = $page_type;					//dd($pageType);
		// $artInfo = Collect(['links'=>$links, 'titles'=>$titles, 'subtits'=>$subtits, 'renutms'=>[] ]);
		return Collect(['links'=>$links, 'titles'=>$titles, 'subtits'=>$subtits, 'cons'=>$cons, 'topTitle'=>$topTitle ]);
		// $userAgent = $this->userAgent;
		// return view('Art.art', compact('pageType', 'pageTitle', 'artInfo', 'userAgent'));
	}
/////////////////////////////////////////////////////////////////////////////
	private $userAgent;
	public function XXX__construct() {			 //log_client_info();
		if (env('APP_ENV') != 'local')  log_client_info();
		// if ( config('app.env') !== 'dev') log_client_info()log_client_info();;
		$this->userAgent = $this->get_user_agent();					//dd($this->agent);
	}
	public function XXX_index() { }
    private function is_OK_ip() {												//dd($_SERVER['REMOTE_ADDR']);
        return preg_match('@192.168.1.|127.0.0.1|106.120.64.26@', $_SERVER['REMOTE_ADDR']);
    }
    private function get_user_agent() {
        $agent = "desktop";
        if (preg_match('@iPhone|Android|Windows@i', $_SERVER['HTTP_USER_AGENT'])) $agent = "smartphone";
        return $agent;
    }
	public function artHome()
    {
        $arts = HomePage::orderBy('ymd', 'desc')->take(8)->orderBy('tag')->get(); //dd($arts);
        $links = [];
		$titles = [];
		$subtits = [];
		$renutms = [];
        foreach($arts as $a) {
			$links[] = "art/$a->tag/$a->ymd";
			$titles[] = $a->tit;
 			$renutms[] = $a->addtm . " 更新";
			$cnt = $a->cnt."篇";
			$flw = $a->flw>0 ? $a->flw . "条回复(共{$a->ffz}字)" : null;
			$img = $a->img>0 ? $a->img . "图片" : null;
			$vdo = $a->vdo>0 ? $a->vdo . "视频" : null;
			$afz = $a->afz>0 ? "文章总字数:" . $a->afz . "字" : null;
			$subtits[] = "$cnt $flw $img $vdo $afz";//
		}
        $links = Collect($links);
        $titles = Collect($titles);
        $subtits = Collect($subtits);
        $renutms = Collect($renutms);
		$pageTitle = null;
		$pageType = "Home";
		$artInfo = Collect(['links'=>$links, 'titles'=>$titles, 'subtits'=>$subtits, 'renutms'=>$renutms]);
		$userAgent = $this->userAgent;
        return view('Art.art', compact('pageType', 'pageTitle', 'artInfo', 'userAgent'));
	}
    private function get_cn_tit($tag) {
		$atit = '强国文摘';
		if ($tag === "PXWW") $atit = '即时新闻';
		else if ($tag === "PXWX") $atit = '焦点新闻';
		else if ($tag === "PXQG") $atit = '强国论坛';
		else if ($tag === "PXHY") $atit = '华山论剑';
		else if ($tag === "PXZJ") $atit = '史海钩沉';
		else if ($tag === "PXJL") $atit = '军事历史';
		return $atit;
	}
	private function get_cn_ymd($ymd) {
		$ymd = preg_replace('/-/', '', $ymd);
		$dt = date_create($ymd);
		$cdate = $dt->format("Y年m月d日星期w");
		$cdate = str_replace("星期0", "星期日", $cdate);
		$cdate = str_replace("星期1", "星期一", $cdate);
		$cdate = str_replace("星期2", "星期二", $cdate);
		$cdate = str_replace("星期3", "星期三", $cdate);
		$cdate = str_replace("星期4", "星期四", $cdate);
		$cdate = str_replace("星期5", "星期五", $cdate);
		$cdate = str_replace("星期6", "星期六", $cdate);
		return "$cdate";
	}
	private function get_sub($a) {
		$aut = "作者:".$a->aut;
		$tim = $a->tim; if ($this->userAgent == "smartphone") $tim = substr($tim, 5, 11);
		$flw = $a->flw>0 ? $a->flw . "条回复(共{$a->fsz}字)" : null;
		$img = $a->img>0 ? $a->img . "图片" : null;
		$vdo = $a->vdo>0 ? $a->vdo . "视频" : null;
		$afz = $a->afz>0 ? "文章字数:" . $a->afz . "字" : null;
		return "$tim $aut $flw $vdo $img $afz";
	}
	private function get_subs($arts) {
		$subs = [];http://devx/arts/getCont/PXHY/2018-07-10
		foreach($arts as $a) {
			$titles[] = $a->idx . "." . proc_line(proc_tit($a->tit));//
			$tim = $a->tim;
			$flw = $a->flw>0 ? $a->flw . "条回复(共{$a->ffz}字)" : null;
			$img = $a->img>0 ? $a->img . "图片" : null;
			$vdo = $a->vdo>0 ? $a->vdo . "视频" : null;
			$afz = $a->afz>0 ? "文章总字数:" . $a->afz . "字" : null;
			$subs[] = "$tim $aut $flw $img $vdo $afz";
		}
		return subs;
	}
	private function get_art_listXXX($arts, $topTitle, $page_type) {
		$titles = [];
		$subtits = [];
		$links = [];
		$idx = 0;
		$cons = [];
		foreach($arts as $a) {
			$idx++;
			// $titles[] = $idx . ") " . proc_tit($a->tit);
			// $titles[] = $idx . ") " . $a->tit;
			$titles[] = $a->tit;
			// $titles[] = $a->idx . ") " . proc_line(proc_tit($a->tit));
			// $links[] = ['tag' => $a->tag, 'ymd' => $a->ymd, 'qid' => $a->qid];
			$links[] = ['tag' => $a->tag, 'ymd' => $a->ymd, 'qid' => $a->qid];
			$subtits[] = $this->get_sub($a);
			if ($a->img > 0) $cons[] = "photo";
      		else if ($a->vdo > 0) $cons[] = "videocam";
      		else $cons[] = "";
		}
		$titles = Collect($titles);
		$links = Collect($links);
		$subtits = Collect($subtits);
		$cnt = count($links);
		$pageType = $page_type;					//dd($pageType);
		// $artInfo = Collect(['links'=>$links, 'titles'=>$titles, 'subtits'=>$subtits, 'renutms'=>[] ]);
		return Collect(['links'=>$links, 'titles'=>$titles, 'subtits'=>$subtits, 'cons'=>$cons, 'topTitle'=>$topTitle ]);
		$userAgent = $this->userAgent;
		// return view('Art.art', compact('pageType', 'pageTitle', 'artInfo', 'userAgent'));
	}
	public function artList($tag, $ymd)
    {
        $d1 = date_create($ymd);
		$d2 = date_create('2016-08-15');
        if ($d1 > $d2) {
            $arts = DailyDat::where([['tag', $tag], ['ymd', $ymd]])->orderBy('idx')->get(); 			//dd($artlist);
            $pagetit = HomePage::where([['tag', $tag], ['ymd', $ymd]])->select('tit')->first()->tit; 	//dd($pagetit);
        } else {
            $arts = DB::table('daily_data')->where([ ['tag', $tag], ['ymd', $ymd] ])->get();
            $pagetit = $this->get_cn_tit($tag) . "(" . $this->get_cn_ymd($ymd) . ")";
        }
		$pagetit = $this->get_cn_tit($tag) . "(" .$this->get_cn_ymd($ymd) . ")";
		if ($this->userAgent == 'smartphone') $pagetit = "<span class='art-page-tit'>$pagetit</span>";
		else $pagetit = "<span class='art-page-tit'>$pagetit</span> <span class='art-info'>(". count($arts) ."篇)</span>";
		return $this->get_art_list($arts, $pagetit, 'List');
	}
    public function artSearch($cat, $txt)
    {
        $arts = DailyDat::fromQuery('CALL MyWeb.art_search(?,?)', [$cat, "%$txt%"]);   // dd($arts);
		$pagetit = "搜索作者含有“${txt}”的文章";
		if ($cat == 'tit') $pageTitle = "搜索题目含有“${txt}”的文章";
		else if ($cat == 'txt') $pageTitle = "搜索文章内容含有“${txt}”的文章";
		$pagetit = "<span class='art-page-tit'>$pagetit</span> <span class='art-infox'>(". count($arts) ."篇)</span>";
		return $this->get_art_list($arts, $pagetit, 'Search');
    }
    public function pxnList($tag, $ymd, $pxn)
    {
        $theday = ""; $dx = null;
		$d1 = date_create($ymd);
		$d2 = date_create('2016-08-15');
		if ($d1 > $d2) {
        	if ($pxn == "prev") {
            	$dx = DailyDat::select('ymd')->where([ ['ymd', '<', $ymd], ['tag', $tag] ])->orderBy('ymd', 'desc')->take(1)->first();
        	} else if ($pxn == "next") {
            	$dx = DailyDat::select('ymd')->where([ ['ymd', '>', $ymd], ['tag', $tag] ])->orderBy('ymd', 'asc')->take(1)->firstOrFail(); //dd($dx);
        	}
		} else {
			if ($pxn == "prev") {
				$dx = DailyData::select('ymd')->where([ ['ymd', '<', $ymd], ['tag', $tag] ])->orderBy('ymd', 'desc')->take(1)->first();
			} else if ($pxn == "next") {
				$dx = DailyData::select('ymd')->where([ ['ymd', '>', $ymd], ['tag', $tag] ])->orderBy('ymd', 'asc')->take(1)->firstOrFail(); //dd($dx);
			}
		}
        if ($dx) $theday = $dx->ymd;
        return [ 'theday' => $theday ];
    }
    /**
     * [getCollArtCont -- get old articles from files rather than from database table DailyArt]
     * @method getCollArtCont
     * author: swang
     * created at 2017-04-21T19:18:26-040
     * revised at 2017-04-21T19:18:26-040
     * version [version]
     * @param  [string]       $tag [not in [ PXHY, PXQG, PXWW, PXZJ， PXJL]]
     * @param  [date]         $ymd [yyyy-mm-dd]
     * @param  [integer]      $qid [article ID]
     * @return [json string]  [to fit in the art return ]
     */
    private function getCollArtCont($tag, $ymd, $qid) {
        $rootdir = config('constants.dirs.daily_data');
        $x = preg_split('@-@', $ymd);	//dd($x);
        $year = $x[0];
        $yxd = "$x[0]$x[1]$x[2]";
        $fullpath = "$rootdir/$tag/$year/$yxd/$qid.txt";	//dd($fullpath);
        $lines = file($fullpath);							//dd($lines);

        // $art = DB::table('daily_data')->where([ ['tag', $tag], ['ymd', $ymd], ['qid', $qid],])->first(); //dd($art);
        $art = DailyData::where([ ['tag', $tag], ['ymd', $ymd], ['qid', $qid],])->first(); //dd($art);

        $tit = trim(array_shift($lines));
        $art->tit = $tit;
        $atfi = trim(array_shift($lines));

        $flw_start = false;
        $txt = '';
        while(count($lines) > 0) {
            $line = trim(array_shift($lines));
            if (preg_match('@(followups---x|∞⊙∞跟∵x-)@', $line) and $art->flw > 0) {
                $line = "<img style='display:absolute;transform:translateX(50%);' src='/img/hr-grey.gif'>";
                $txt = trim($txt) . "\n";	// more multple \n before flw_mark
                $flw_start = true;
            } else if (preg_match('@followups---x@', $line) and empty($art->flw)) {
                break;
// 				} else  if (!$flw_start) {
// 					$line.= "\n";
            }

            if (preg_match('@.(jpg|jpeg|gif|png)@i', $line)) {
                $line = preg_replace('@/MyWeb@i', '', $line);
                $line = trim($line); //dd($line);
            }
            // $txt.= "$line\n";
            // $txt .= nl2br($line) . "<br>";
            $txt .= "$line<br />";
            // $txt .= proc_line($line) . "<br>";
        }
        $art_flw[0] = $art;
        $art_flw[0]->txt = $txt;

        $flwfile = "$rootdir/$tag/$year/$yxd/${qid}_flw.txt";
        if (file_exists($flwfile)) {
            $lines = file($flwfile);
            while (count($lines) > 0) {
                $line = trim(array_shift($lines));
                $art_flw[0]->txt.= "$line\n";
            }
        }
        $artcont = $art_flw;
        $artinfo = $art;
        return [ $artcont, $artinfo ];
    }
    // public function artCont($tag, $ymd, $qid, $searchCat=null, $searchTxt=null)
    public function artCont($tag, $ymd, $qid)
    {
        // $pxtags = ['PXQG', 'PXHY', 'PXWW', 'PXZJ', 'PXJL'];
        $d1 = date_create($ymd);
		$d2 = date_create('2016-08-15');
		$art = null;
		$art_flw = [];
		$artcont = null;
        if (in_array($tag, $this->pxar) and $d1 > $d2) {
            $arts = DailyArt::where([ ['tag', $tag], ['qid', $qid] ])->orderBy('idx')->get(); //dd($artcont);
            $artinfo = DailyDat::where([ ['tag', $tag], ['ymd', $ymd], ['qid', $qid] ])->first(); //dd($artinfo);
            $artinfo->tit = proc_tit($artinfo->tit);
            // $artinfo->tit = proc_line($artinfo>tit);
        } else {
            list($arts, $artinfo) = $this->getCollArtCont($tag, $ymd, $qid);
        }

		$art = [];
		$art['tit'] = proc_line(proc_tit($artinfo->tit));
		$art['sub'] = $this->get_sub($artinfo);
		$art['lnk'] = $artinfo->lnk;

		$txt = trim($arts[0]->txt);

		// $txt = preg_replace('@\n\n@', '<br /><br />', $txt);

		// $txt = nl2br($txt);

		// $txt = preg_replace('@<br /><br />@', '<br />', $txt);
		$art['txt'] = proc_line($txt);
		// $art['txt'] = proc_line($txt) . "<br />";
		// $art['txt'] = proc_line($arts[0]->txt);
		$art['bak'] = "/art/$tag/$ymd";

		$flw = [];
		for ($i=1; $i<count($arts); $i++) {
			$a = $arts[$i];
			$a->txt = preg_replace('@[※]{9,}@', '※ ※ ※ ※ ※ ※ ※', $a->txt); //dd($a->txt);
			if (!empty($a->lvl)) $a->txt = get_flw_prex($a->lvl) . $a->txt;
			$x = [];
			$x['txt'] = proc_line(trim($arts[$i]->txt));
			// $x['txt'] = proc_line($arts[$i]->txt) . "<br />";
			$x['sub'] = "回复($i) " . $this->get_sub($a);
			$x['fid'] = $a->fid;
			$flw[] = $x;
		}											//dd($flw);

		$pageType = "Cont";
		$pageTitle = $art['tit'];
		$artInfo = Collect(['art'=>$art, 'flw'=>$flw]);
		$userAgent = $this->userAgent;
		return view('Art.art', compact('pageType', 'pageTitle', 'artInfo', 'userAgent'));
    }
    public function XXXartcont($tag, $ymd, $qid, $searchCat=null, $searchTxt=null)
    {
        // $pxtags = ['PXQG', 'PXHY', 'PXWW', 'PXZJ'];
        $d1 = date_create($ymd);
		$d2 = date_create('2016-08-15');
		$art = null;
		$art_flw = [];
		$artcont = null;
        if (in_array($tag, $this->pxar) and $d1 > $d2) {
            $agent = $this->getUserAgent();
            $arts = DailyArt::where([ ['tag', $tag], ['qid', $qid] ])->orderBy('idx')->get(); //dd($artcont);
            $artinfo = DailyDat::where([ ['tag', $tag], ['ymd', $ymd], ['qid', $qid] ])->first(); //dd($artinfo);
            $artinfo->tit = proc_tit($artinfo->tit);
            $artinfo->tit = proc_line($artinfo->tit);
            if ($agent == "smartphone") $artinfo->tim = substr($artinfo->tim, 5, 11);
        } else {
            list($arts, $artinfo) = $this->getCollArtCont($tag, $ymd, $qid);
        }
        foreach($arts as $a) {
            // $line = trim($a->txt);
			// $line = str_replace(array("\r\n", "\r", "\n"), "<br />", $line);
            // $line = proc_line($line);
            // $line = nl2br($line);
            // $line = preg_replace('@\\n@', '<br>', $line);

			$a->txt = proc_line($a->txt);
			$a->txt = preg_replace('@[※]{9,}@', '※ ※ ※ ※ ※ ※ ※', $a->txt); //dd($a->txt);

            if ($agent == "smartphone") $a->tim = substr($a->tim, 5, 11);
            if (!empty($a->lvl)) $a->txt = get_flw_prex($a->lvl) . $a->txt;
			$artcont[] = $a;
        }

        $show = [ 'home' => 0, 'list' => 0, 'cont' => 1, 'agent' => $agent ];
        $dx = $titlst = DB::table('art_class')->select('name')->where([ ['status', 'A'], ['tag', $tag] ])->first();
        if ($dx) $lastTitle = $dx->name; else $lastTitle = "";

		// if (empty($artcont)) $artcont = $arts->toArray();
								//dd($artcont);
        $artdata = collect([ 'artcont' => $artcont, 'artinfo' => $artinfo, "okip" => $this->isOKip(), 'lastTitle' => $lastTitle ]);
        // $userdata = json_encode($data);
        return view('Art.art', compact('artdata', 'show'));
    }
    /**
     * get colleciton list.
     *
     * @return the list
     */
    public function collections()
    {
        // $pxar = ['PXQG', 'PXHY', 'PXWW', 'PXZJ'];
        // $titlst = DB::table('art_class')->select('tag', 'name')->where('status', 'A')
            // ->whereNotIn('tag', $pxar)->orderBy('name')->get();		//dd(is_array($titlst));
        $titlst = ArtClass::select('tag', 'name')->where('status', 'A')->whereNotIn('tag', $this->pxar)->orderBy('name')->get(); //dd($titlst);
        $titlist = [];
        foreach($titlst as $a) {			//dd($a);
            $tag = $a->tag;
			$cnt = null;
            // $tcnt = DB::select("select count(*) as cnt from daily_data where tag =:tag", ['tag'=>$tag]); //dd($cnt);
            $cnt = DailyData::where('tag', $tag)->count(); //dd($cnt);
            $a->cnt = $cnt;
            $titlist[] = $a;	  //convert objects to array
        }																	//dd($titlist);
        return view('Art.collections', compact('titlist'));
    }
    public function artCollList($tag, $pagetit, $cnt)
    {
        if ($tag == 'CNSW') {
			$id = 5679;
			return (new LegacyController())->showart($id);
		}
		// $pxar = ['PXQG', 'PXHY', 'PXWW', 'PXZJ'];
		$arts = null;
		if (!in_array($tag, $this->pxar)) {
			// $arts = DB::table('daily_data')->where('tag', $tag)->orderBy('idx')->get();		//dd($artlist);
			$arts = DailyData::where('tag', $tag)->orderBy('idx')->get();		//dd($artlist);
		}
		// $pagetit = "<span class='art-page-tit'>$pagetit </span><span class='art-infox'>(${cnt}篇)</span>";
		$pagetit = $pagetit . "(${cnt}篇)";
		return $this->get_art_list($arts, $pagetit, 'Collections');
		//
        // foreach($arts as $a) {
        //     $a->lnk = "/art/$a->tag/$a->ymd/$a->qid";
        //     $a->tit = proc_tit($a->tit);
        //     $a->tit = proc_line($a->tit);
        //     if ($this->agent == 'smartphone') $a->tim = substr($a->tim, 5, 11);
        // }
		// $artlist = $arts->toArray();
        // $show = [ 'home' => 0, 'list' => 1, 'cont' => 0, 'agent' => $this->agent ];
        // $artdata = collect([ 'artlist' => $artlist, 'pagetit' => $pagetit ]);
        // return view('art', compact('artdata', 'show'));

        // //////////////////////////////////////////

		$tags = DB::table('art_class')->select('tag')->where('status','A')->whereNotIn('tag',$pxar)->orderBy('name')->get();//dd($tags);
		$cnt = count($tags);

		$first = $tags[0]->tag;
		$last = $tags[$cnt - 1]->tag;
		$prev_tag = null;
		$next_tag = null;
		for ($i=0; $i<$cnt; $i++) {
			$tagx = $tags[$i]->tag;						//dd("$tag, $curr_tag, $next_tag");
			if ($tagx == $tag) {
				if ($i == 0) $prev_tag = $last;
				else if ($i == $cnt - 1) $next_tag = $first;
				else {
					$prev_tag = $tags[$i - 1]->tag;
					$next_tag = $tags[$i + 1]->tag;
				}
			}
		}  								//dd("$prev_tag, $curr_tag, $next_tag");
		$artlist[0]->has_art = true;
		$tit = DB::table('art_class')->select('name')->where('tag', $tag)->first()->name;
		$prev_idx = $artlist[0]->idx; $i = 0;
		if ($prev_idx !== 1) $this->upd_idx($tag, $artlist);
		foreach($artlist as $art) {
			if ($i++ === 0) continue;
			if ($art->idx == $prev_idx) {
				$this->upd_idx($tag, $artlist);
				break;
			}
			$prev_idx = $art->idx;
		}
		$tobe_stored = $this->store_artlist_info($artlist);
		return view('articles.list', compact('artlist', 'tit', 'tobe_stored', 'prev_tag', 'next_tag'));
    }
	public function getTitForEditing($tag, $ymd, $qid) {
		$d = DailyDat::select('tit')->where([ ['qid', $qid], ['tag', $tag], ['ymd', $ymd] ])->get();
		$dx = $d[0];
		return ['tit' => $dx->tit];
	}
	public function getTxtForEditing($qid, $fid) {
		$d = DailyArt::select('id', 'txt')->where([ ['qid', $qid], ['fid', $fid] ])->get();
		$dx = $d[0];
		// return ['id' => $fid, 'txt' => $qid];
		return ['id' => $dx->id, 'txt' => $dx->txt];
	}
	public function updateTxt(Request $d) {
		// $d = Input::All();
		$id = $d['id'];
		// $qid = $d['qid'];
		// $fid = $d['fid'];
		$txt = $d['txt'];
		$dm = DailyArt::find($id);
		$txt = preg_replace('@\r@', '', $txt);
		$dm->txt = $txt;
		$dm->update();
		return 'OK';
	}
	public function updateTit(Request $d) {
		// $d = Input::All();
		$tag = $d['tag'];
		$ymd = $d['ymd'];
		$qid = $d['qid'];
		$tit = $d['tit'];
		$dm = DailyDat::where('tag', $tag)->where('qid', $qid)->where('ymd', $ymd)->update(['tit' => $tit]);
		return 'OK';
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
        //
	}
	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		//
	}
}

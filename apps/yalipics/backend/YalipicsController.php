<?php
namespace App\Http\Controllers;
// use App\Models\healthtest\HealthTest;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent;
use DateTime;
use DateTimeZone;

class YalipicsController extends Controller {
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
	public function index() { }
  public function getList($isIM=0) { Log::info("YalipicsController->getList isIM=$isIM");
    $picdir = "/Users/swang/sites/webdata/pics/yali";
    //if ($isIM) $picdir = "/sites/webdata/pics/yaliIM";
		$thumbnails = [];
		$thumbnaildir = "$picdir/thumbnails";
		// Open a known directory, and proceed to scandir its contents
    // $thumbnails = array_diff(scandir($thumbnaildir), ['.', '..']);
    $thumbnails = glob("$thumbnaildir/*.{jpg,jpeg,png,gif,JPG,JPEG,PNG,GIG}", GLOB_BRACE);

    // Sort by modification time
    usort($thumbnails, function($a, $b) use ($thumbnaildir) {
      // return filemtime("$thumbnaildir/$b") - filemtime("$thumbnaildir/$a");
      return filemtime($b) - filemtime($a);
    });

    $dates = [];
    $ratios = [];
    // $rat1 = [];
    // $rat2 = [];
    $filelist = [];
    foreach($thumbnails as $fnm) {
      // $dates[] = date('Y.n.j', filemtime($thumbnaildir . '/' . $fnm));
      $dates[] = date('Y.n.j', filemtime($fnm));
      // list($width, $height) = getimagesize($picdir . '/' . preg_replace('/_thumbnail/', '', $fnm));
      // list($width, $height) = getimagesize("$picdir/$fnm");
      list($width, $height) = getimagesize($fnm);
      $ratio = $width / $height;
      $ratios[] = $ratio;
      // Log:info("ratio=$ratio width=$width height=$height");
      // if ($ratio < 1) $rat1[] = $fnm;
      // else $rat2[] = $fnm; 
      $filelist[] = basename($fnm); // $fnm is fullpath
    }
    // Log::info("ratios", $ratios);

    // $tlst = array_merge($rat1, $rat2);
    // $tlst = $rat1 + $rat2;

    // return ['lst' => $thumbnails, 'dates' => $dates, 'ratios' => $ratios, 'status' => "OK"];
    // return ['lst' => $tlst, 'dates' => $dates, 'ratios' => $ratios, 'status' => "OK"];
    return ['lst' => $filelist, 'dates' => $dates, 'ratios' => $ratios, 'status' => "OK"];
  }
}

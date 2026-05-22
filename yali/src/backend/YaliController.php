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

class YaliController extends Controller {
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
  public function getList($isIM=0) { Log::info("YaliController->getList isIM=$isIM");
    $picdir = "/Users/swang/sites/webdata/pics/yali";
    //if ($isIM) $picdir = "/sites/webdata/pics/yaliIM";
		$thumbnails = [];
		$thumbnaildir = "$picdir/thumbnails";
		// Open a known directory, and proceed to scandir its contents
    // $thumbnails = array_diff(scandir($thumbnaildir), ['.', '..']);
    $thumbnails = glob("$thumbnaildir/*.{jpg,webp,jpeg,png,gif,JPG,JPEG,PNG,GIG}", GLOB_BRACE);
    // Sort by modification time
    usort($thumbnails, function($a, $b) use ($thumbnaildir) {
      // return filemtime("$thumbnaildir/$b") - filemtime("$thumbnaildir/$a");
      return filemtime($b) - filemtime($a);
    });
    // Log::info($thumbnails);
    $icons = [];
    $iconURLroot = "/pics/yali/thumbnails/";
    foreach($thumbnails as $thm) {
      $icon = [];
      $icon['name'] = $thm;
      $icon['URL'] = $thm;
      $icons[] = $icon;
    }


    $datetms = [];
    $ratios = [];
    $filelist = [];
    $fileSizes = [];
    foreach($thumbnails as $fnm) {
      // $datetms[] = date('Y.n.j', filemtime($thumbnaildir . '/' . $fnm));
      $datetms[] = date('Y.n.j H:i', filemtime($fnm));
      // list($width, $height) = getimagesize($picdir . '/' . preg_replace('/_thumbnail/', '', $fnm));
      // list($width, $height) = getimagesize("$picdir/$fnm");
      list($width, $height) = getimagesize($fnm);
      $ratio = $width / $height;
      $ratios[] = $ratio;
      // Log:info("ratio=$ratio width=$width height=$height");
      // if ($ratio < 1) $rat1[] = $fnm;
      // else $rat2[] = $fnm;
      $filename = basename($fnm); // $fnm is fullpath
      $filelist[] = $filename;
      $fileFullpath = dirname(dirname($fnm)) ."/". $filename; // get rid of /thumbnails
      $sizeInByte = filesize($fileFullpath); // in K
      // $sizeInKiloByte = $sizeInByte / 2014;  // in KB
      // Log::info("fileFullpath=[$fileFullpath] fileSizeInKiloByte=[$sizeInKiloByte]");
      // Log::info("fileFullpath=[$fileFullpath] fileSizeInByte=[$sizeInByte]");
      $fileSizes[] = $sizeInByte;
    }
    // Log::info("ratios", $ratios);

    return ['lst' => $filelist, 'fsz' => $fileSizes, 'datetms' => $datetms, 'ratios' => $ratios, 'status' => "OK"];
    // return ['lst' => $icons, 'fsz' => $fileSizes, 'datetms' => $datetms, 'ratios' => $ratios, 'status' => "OK"];
  }
}

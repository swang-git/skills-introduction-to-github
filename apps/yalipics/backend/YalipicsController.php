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
  public function getList() { Log::info("YalipicsController->getList");
		$thumbnails = [];
		$thumbnaildir = "/sites/webdata/pics/yali/thumbnails";
		// Open a known directory, and proceed to scandir its contents
    $thumbnails = array_diff(scandir($thumbnaildir), ['.', '..']);


    // Sort by modification time
    usort($thumbnails, function($a, $b) use ($thumbnaildir) {
      return filemtime($thumbnaildir . '/' . $b) - filemtime($thumbnaildir . '/' . $a);
    });

    $picdir = "/sites/webdata/pics/yali";
    $dates = [];
    $ratios = [];
    foreach($thumbnails as $fnm) {
      $dates[] = date('Y.n.j', filemtime($thumbnaildir . '/' . $fnm));
      list($width, $height) = getimagesize($picdir . '/' . preg_replace('/_thumbnail/', '', $fnm));
      $ratios[] = $width / $height;
    }
    // Log::info("ratios", $ratios);

    return ['lst' => $thumbnails, 'dates' => $dates, 'ratios' => $ratios, 'status' => "OK"];
  }
}

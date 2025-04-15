<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use App\Models\golf\MatchPlayer;
use App\Models\golf\MatchGroup;
use App\Models\golf\Player;
use App\Models\golf\PlayerAlias;
use App\Models\golf\Tplayer;
use App\Models\golf\KjGameData;
use App\Models\golf\KjGameDataView;
use App\Models\golf\UserGuide;
use App\Models\golf\Score;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use App\Traits\MyTraits;
use App\Traits\ExcelTrait;


\Config::set('database.default', 'golf_dev');
class LoadKJGameDataController extends Controller {
	use MyTraits;
	use ExcelTrait;
	public function __construct() {
		// $nologs = ['/golf/getUserType', '/golf/getPlayerCount', '/golf/updGScore', '/golf/insGScore', '/golf/getPStrokes'];
		// // Log::info('$_SERVER["REMOTE_ADDR"]=' . $_SERVER['REMOTE_ADDR'] . ' $_SERVER["REQUEST_URI"]=' . $_SERVER['REQUEST_URI'], $nologs);
		// if (in_array($_SERVER['REQUEST_URI'], $nologs)) return;
		// $this->logPage('from __construct'); // this will be called for every click
	}
  public function loadKJExcelFile() {
		$xlsxfile = null;
		// $kjsfile = 'http://jianonline.com/Golf.xlsx';
		// $kjsfile = 'http://mmsgolf.com/';
		// $kjsfile = '/sites/tmp/kjsfiles/kjsfile_Sun_17_06.xlsx';
		$kjsfile = 'http://mmsgolf.com/kj/Golf.xlsx';
		Log::info("loadKJExcelFile from $kjsfile", [__file__, __line__]);
		$response = null;
		try {
			$response = Http::get($kjsfile);
		} catch (\Exception $ex) {
			// Log::info("open $kjsfile failed:", $ex->Error());
			// $kjsfile = '/sites/tmp/kjsfiles/kjsfile_Sun_17_06.xlsx';
			Log::debug("ONLINE file $kjsfile is not available");
			return "ONLINE excel file is not available";
		}
		$rhandle = fopen($kjsfile, "rb");
		$contents = '';
		while (!feof($rhandle)) {
			$contents .= fread($rhandle, 8192);
		}
		fclose($rhandle);
		$xlsxfile = "/sites/tmp/kjsfiles/kjsfile_".date('Y-m-d-H-i').".xlsx";
		$whandle = fopen($xlsxfile, 'wb');
		fwrite($whandle, $contents);
    fclose($whandle);
		return $xlsxfile;
	}
  public function processKJExcel($xlsxfile) {
    Log::info("-fn-processKjExcel [$xlsxfile]");
    // $this->processKjExcelFile("/sites/devx/$xlsxfile");
    $this->processKjExcelFile($xlsxfile);
    return ['status' => 'OK'];
  }
}

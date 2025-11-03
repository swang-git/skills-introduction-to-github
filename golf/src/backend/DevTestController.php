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
// use App\Models\golf\AliasHandicapKJView;
// use App\Models\golf\MatchPlayerHandicap;
use App\Models\golf\KjGameData;
use App\Models\golf\KjGameDataView;
use App\Models\golf\UserGuide;
use App\Models\golf\Score;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use App\Traits\ExcelTrait;


\Config::set('database.default', 'golf_dev');
class DevTestController extends Controller {
	use ExcelTrait;
	public function __construct() {
		// $nologs = ['/golf/getUserType', '/golf/getPlayerCount', '/golf/updGScore', '/golf/insGScore', '/golf/getPStrokes'];
		// // Log::info('$_SERVER["REMOTE_ADDR"]=' . $_SERVER['REMOTE_ADDR'] . ' $_SERVER["REQUEST_URI"]=' . $_SERVER['REQUEST_URI'], $nologs);
		// if (in_array($_SERVER['REQUEST_URI'], $nologs)) return;
		// $this->logPage('from __construct'); // this will be called for every click
	}
  public function processKjExcel($xlsxfile) {
    Log::info("-fn-processKjExcel $xlsxfile");
    $this->processKjExcelFile("/sites/devx/$xlsxfile");
    return ['status' => 'OK'];
  }
}

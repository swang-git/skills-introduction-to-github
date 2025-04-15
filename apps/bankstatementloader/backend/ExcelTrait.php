<?php
namespace App\Traits;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

use App\Models\golf\Player;
use App\Models\golf\MatchPlayer;
use App\Models\golf\KjGameData;

trait ExcelTrait {
  private $pcount = 0;
  public function processKjExcelFile($xlsxfile) { Log::info("processKjExcelFile for $xlsxfile");
    // ini_set('memory_limit', '512M');
    ini_set('memory_limit', '1028M');
    $sheet = $this->getSheet($xlsxfile); //Log::info("updKjGameData $xlsxfile", [$sheet]);
    
    $latestGameDateOnSheet = $this->getLatestDate($sheet);
    $latestGameDateInDB = $this->getLatestDateInDB();
    Log::info("-fn-processKjExcelFile $xlsxfile for latestGameDateInDB=$latestGameDateInDB latestGameDateOnSheet=$latestGameDateOnSheet", [__line__]);
    if ($latestGameDateInDB == $latestGameDateOnSheet) {
      Log::info("All game data loaded -- DONE for latestGameDateInDB=$latestGameDateInDB");
      return ['latestHCapDate' => $latestGameDateOnSheet, 'status' => 'DONE for loading game data'];
    }
    list($startIdx, $nextGameDate) = $this->getColIdxForGameDate($sheet, $latestGameDateInDB, $latestGameDateOnSheet);
    
    $lastDataRow = $sheet->getHighestRow();  // get last row number
    // Log::debug("lastDataRow=$lastDataRow", [__line__]);
    $range = $sheet->rangeToArray("A1:G$lastDataRow"); // note this G
    list($playerData, $lastGameDataRow) = $this->getPlayerData($range);
    $colTag = $this->convertToExcelColTag($startIdx);
    $range = $sheet->rangeToArray("{$colTag}1:AZ$lastGameDataRow"); // note this AZ
    Log::debug('range for game data $colTag1:AZ$lastDataRow -- may need to adjust AZ and check colTag', [__line__]);
    Log::debug("lastestGameDateInDB=$latestGameDateInDB startIdx=$startIdx colTag=$colTag nextGameDate=$nextGameDate", [__line__]);
    $names = $sheet->rangeToArray("A5:B$lastDataRow");
    $gameData = $this->getGameDataForDate($playerData, $range, $nextGameDate);
    foreach ($gameData as $drow) {
      $this->saveGameData($drow);
      // if ($drow['full_name'] == 'Jian, Kevin') { Log::debug("dataRow", $drow); }
    }
    if ($startIdx == 6) return ['lastHcapDate' => $latestGameDateOnSheet, 'status' => 'Done for loading game data'];  // last game date case
    $this->processKjExcelFile($xlsxfile);
    Log::debug("latestGameDateOnSheet=$latestGameDateOnSheet nextGameDate=$nextGameDate", [__line__]);
    return ['lastHcapDate' => $latestGameDateOnSheet, 'status' => 'not done yet for loading game data'];
  }
  private function saveGameData($drow) {
    KjGameData::upsert(
      [$drow],
      ['mp_id', 'game_date']
    );
    // Log::info("-CK-B fullname=$name alias=$alias last_game_date=$gdate match_player_id=$match_player_id avg10=$avg avg_score=$avg_score", [__line__]); 
  }
  private function convertToExcelColTag($colIdx) {
    $charStr = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charArray = str_split($charStr);
    $colTag = $charArray[$colIdx % 26];
    if ($colIdx >= 26) {
      $intPart = (int)$colIdx / 26 - 1;
      $residule = $colIdx % 26;
      $staChar = $charArray[$intPart];
      $endChar = $charArray[$residule];
      $colTag = $staChar . $endChar;
    }
    // Log::debug("-CK-colIdx=$colIdx colTag=$colTag", $charArray);
    return $colTag;
  }
  private function getGameDataForDate($playerData, $range, $nextGameDate) {
    // Log::debug("-fn-getGameDataForDate nextGameDate=$nextGameDate", [__line__]);
    $rw = array_shift($range); // game date
    $rw = array_shift($range);
    $gameInfo = $rw[0];
    $rw = array_shift($range);
    $course_rating = $rw[0];
    $course_slope = $rw[1];
    $rw = array_shift($range);
    Log::debug("nextGameDate=$nextGameDate gameInfo=$gameInfo course_rating=$course_rating course_slope=$course_slope", [__line__]);

    $i = 0;
    $gameData = [];
    $pcnt = count($playerData);
    foreach ($range as $row) {
      // Log::debug("pcnt=$pcnt i=$i");
      if ($i >= $pcnt) break;
      $pda = $playerData[$i++];
      $name = $pda[0];
      if (str_contains($name,'Guest')) break;
      // $games_played = $pda[1] == '' ? null : $pda[1];

      $gross_score = $row[0] == '' ? null : $row[0];
      $idx_diff = $row[1] == '' ? null : $row[1];
      $pair = $row[2] == '' ? null : $row[2];
      $pair_win_pt = $row[3] == '' ? null : $row[3];
      $team_win_pt = $row[4] == '' ? null : $row[4];
      // Log::info("mpId=$pda[1] name=$name gross_score=[$gross_score], idx_diff=[$idx_diff], pair=[$pair], pair_win_pt=[$pair_win_pt], team_win_pt=[$team_win_pt]", [__line__]);
      $dataRow = [
        'game_date' => $nextGameDate,
        'full_name' => $pda[0],
        'mp_id' => $pda[1],
        'games_played' => $pda[2], // current year
        'avg_idx' => $pda[3],
        'all_games_played' => $pda[4],
        'avg_idx_all_games' => $pda[5],
        'avg_score' => $pda[6],
        'gross_score' => $gross_score, 
        'idx_diff' => $idx_diff, 
        'pair' => $pair,
        'pair_win_pt' => $pair_win_pt,
        'team_win_pt' => $team_win_pt,
        'notes' => $gameInfo,
        'course_rating' => $course_rating,
        'course_slope' => $course_slope,
      ];
      array_push($gameData, $dataRow);
      // Log::info("lastn=$lastn firstn=$firstn fullname=$fullname match_player_id=$match_player_id", [__line__]);
    }
    return $gameData;
  }
  private function getColIdxForGameDate($sheet, $latestGameDateInDB, $latestGameDateOnSheet) {
    $range = $sheet->rangeToArray("A1:BE1"); // AZ need to Adjust to cover all game data not yet loaded
    $row1 = $range[0];
    $startIdx = 0; // for the next game data to save -- the first date on which game data not yet saved in DB
    $nextGameDate = null;
    foreach ($row1 as $col) {
      $startIdx++; //
      if (preg_match('/\d{1,}\/\d{1,}\/\d{4}/', $col)) {
        $date = date('Y-m-d', strtotime($col));
        if ($date == $latestGameDateInDB) {
          $startIdx -= 6;
          $startIdx = $startIdx == 1 ? 6 : $startIdx;  
          // $startIdx = 6; // last game date case
          $nextGameDate = $row1[$startIdx]; 
        }
        Log::debug("colDate=$date lastestGameDateInDB=$latestGameDateInDB startIdx=$startIdx nextGameDate=$nextGameDate", [__line__]);
        if ($nextGameDate != null) return [$startIdx, date('Y-m-d', strtotime($nextGameDate))];
      }
    }
    return null;
  }

  private function getLatestDate($sheet) {
		// $cell = $sheet->getCellByColumnAndRow(13, 1);
		// Log::info(print_r($cell, true));
		// Log::error("cell", ['cell' => $cell->cell]);
		// $cell = $sheet->getCell("H1");
		$str = "FGHIJKLMNOPQRSTU";
		$date = null;
		forEach(str_split($str) as $char) {
			$cell = $sheet->getCell("{$char}1");
			$date = $cell->getFormattedValue();
			// Log::info("==fn==[${char}1][$date]");
			if (preg_match('@\d{1,}/\d{1,}/\d{4}@', $date)) {
        return date('Y-m-d', strtotime($date));
      }
		}
	}
  private function getMpIdAndCheckNewPlayer($name) {
    list($lastn, $firstn) = explode(', ', $name);
      // Log::info("$lastn", ['firstname' => $firstn, 'lastname' => $lastn, 'line' => __line__]); 
      $x = Player::where([['players.lastname', $lastn], ['firstname', $firstn], ['players.status','A'], ['match_players.status', 'A']])
      ->leftJoin('match_players', function($leftJoin) {
        $leftJoin->on('players.id', 'match_players.player_id');
      })->select('players.id as pid', 'match_players.id as mid', 'match_players.alias', 'match_players.match_id')->get();
			if (count($x) != 1) {
				$aliases = MatchPlayer::where('status', 'A')->select('alias')->get();
				Log::info("$lastn", [ 'firstname' => $firstn, 'lastname' => $lastn, 'aliases'=> $aliases, 'status' => "ADD_NEW_SIM_PLAYER"]);
				return [ 'firstname' => $firstn, 'lastname' => $lastn, 'aliases'=> $aliases, 'status' => "ADD_NEW_SIM_PLAYER"];
			}
      // $xx = $x->toArray()[0];
			// $fullname = $lastn .', '.$firstn;
			// $alias = $xx['alias'];
			// $match_player_id = DB::select('select id from match_players_view where name = "$fullname" and match_id = 14');
			$match_player_id = Player::where([['lastname', $lastn], ['firstname', $firstn], ['players.status', 'A'], ['match_players.status', 'A']])
			  ->leftJoin('match_players', function($leftJoin) {
          $leftJoin->on('match_players.player_id','players.id');
				})->select('match_players.id')->value('id');
      return ['match_player_id' => $match_player_id, 'status' => 'mpId'];
  }
  private function getPlayerData($range) { //Log::debug("getPlayerData");
    $playerData = [];
    $i = 0;
    foreach ($range as $row) {
      $i++;
      $mpId = 0;
      $name = $row[0];
      if ($name == null or $name == 'Player Name') continue;
      else if (str_contains($name, 'Guest')) {
        Log::debug("-CK-lastGameDataRow=$i", [__line__]);
        break;
      }
      $retval = $this->getMpIdAndCheckNewPlayer($name);
      if ($retval['status'] == 'ADD_NEW_SIM_PLAYER') {
        Log::debug("need to add new player for firstname=$firstn lastname=$lastn status=>'ADD_NEW_SIM_PLAYER'");
        return [ 'firstname' => $firstn, 'lastname' => $lastn, 'aliases'=> $aliases, 'status' => "ADD_NEW_SIM_PLAYER"];
      } else if ($retval['status'] == 'mpId') {
        $mpId = $retval['match_player_id'];
      }
      $num_game_played = $row[1] == '' ? null : $row[1];
      $weighted_avg_idx = $row[2] == '' ? null : $row[2];
      $all_game_played = $row[3] == '' ? null : $row[3];
      $avg_idx_all_games = $row[4] == '' ? null : $row[4];
      $avg_score_all_games = $row[5] == '' ? null : $row[5];
      $rdata = [$name, $mpId, $num_game_played, $weighted_avg_idx, $all_game_played, $avg_idx_all_games, $avg_score_all_games];
      array_push($playerData, $rdata);
    }
    $this->pcount = count($playerData);
    // Log::info("playerData pcount=$this->pcount", $playerData[$this->pcount - 1]);
    return [$playerData, $i];
  }
  private function getLatestDateInDB() {
    $gdate = KjGameData::where('status', 'A')->orderBy('game_date', 'desc')->limit(1)->value('game_date');
    return date('Y-m-d', strtotime($gdate));
    // Log::info("lastGameDate=[$lastGameDate]");
  }

  private function getSheet($xlsxfile) { //Log::info("getSheet( xlsxflle=[$xlsxfile])");
		$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
		// $reader->setReadFilter( new MyReadFilter() );
		$reader->setReadDataOnly(false);
		// $reader->setReadDataOnly(true);
		$spreadsheet = $reader->load($xlsxfile);
		$sheet = $spreadsheet->getActiveSheet();
		// Log::info("getSheet($xlsxfile)");
		return $sheet;
	}
}


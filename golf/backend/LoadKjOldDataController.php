<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use App\Models\golf\MatchPlayer;
use App\Models\golf\MatchGroup;
use App\Models\golf\Player;
use App\Models\golf\Tplayer;
use App\Models\golf\KjGameData;
use App\Models\golf\KjGameDataView;
use App\Models\golf\UserGuide;
use App\Models\golf\Score;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use App\Traits\MyTraits;

\Config::set('database.default', 'golf_dev');
class LoadKjOldDataController extends Controller {
	private function getDateByCol($sheet, $col) {  // col like 'ZO' 'G' etc.
		$rowIdx = 1;
		$cell = $sheet->getCell("$col$rowIdx");
		$date = $cell->getFormattedValue();
		print("getDateByCol(sheet, $col$rowIdx) [$date] [$col$rowIdx]\n");
		$x = explode('/', $date);
		$date = $x[2].'-'.$x[0].'-'.$x[1];
		return $date;
	}
	private function getNotesByCol($sheet, $col) {  // col like 'ZO' 'G' etc.
		$rowIdx = 2;
		$cell = $sheet->getCell("{$col}$rowIdx");
		$notes = $cell->getFormattedValue();
		$notes = preg_replace("/\n/", ", ", $notes);
		Log::info("getNotes(sheet) [$notes]");
		return $notes;
	}
	private function getRatingByCol($sheet, $col) {  // col like 'ZO' 'G' etc.
		$rowIdx = 3;
		$cell = $sheet->getCell("{$col}$rowIdx");
		$Ratng = $cell->getFormattedValue();
		Log::info("getRatng(sheet) [$Ratng]");
		return $Ratng;
	}
	private function getNextCol($col) {
		$x = str_split($col);
		Log::info("-CK-getNexCol()-x", $x);
	}
	private function getSlopeByCol($sheet, $col) {  // col like 'ZO' 'G' etc.
		$rowIdx = 3;
		// $col = $this->getNextCol($col);
		$col++;
		$cell = $sheet->getCell("{$col}$rowIdx");
		$slope = $cell->getFormattedValue();
		Log::info("getSlope(sheet) [$slope]");
		return $slope;
	}
	private function getMatchPlayerId($name) {
		[$lname, $fname] = explode(', ', $name);
		// $mpId = MatchPlayer::where([['lastname', $lname], ['firstname', $fname], ['status', 'A']])->value('id');
		$mpId = Player::where([['players.lastname', $lname], ['firstname', $fname], ['players.status','A'], ['match_players.status', 'A']])
				->leftJoin('match_players', function($leftJoin) {
				$leftJoin->on('players.id', 'match_players.player_id');
				})->value('match_players.id');
				// })->select('players.id as pid', 'match_players.id as mid', 'match_players.alias', 'match_players.match_id')->get();
		// print("$mpId, [$lname], [$fname]\n");
		return $mpId;
	}
	private function getGamesPlayed($exlDate, $mpId) { //current year
		$year = substr($exlDate, 0, 4);
		// print("year=[$year] exlDate=[$exlDate]");
		$gamesPlayed = KjGameData::where([['game_date', 'like', $year .'%'], ['game_date', '<=', $exlDate], ['mp_id', $mpId]])->count();
		return $gamesPlayed;
	}
	private function getAvgIdx($exlDate, $mpId) { // last 10 games played
		$avgIdx = KjGameData::where([['game_date', '<=', $exlDate], ['mp_id', $mpId]])->orderBy('game_date', 'desc')->limit(10)->avg('idx_diff');
		return $avgIdx;
	}
	private function getAllGamesPlayed($exlDate, $mpId) { // last 10 games played
		$allGamesPlayed = KjGameData::where([['game_date', '<=', $exlDate], ['mp_id', $mpId]])->count();
		return $allGamesPlayed;
	}
	private function getAvgIdxAllGames($exlDate, $mpId) { // last 10 games played
		$avgIdxAllGames = KjGameData::where([['game_date', '<=', $exlDate], ['mp_id', $mpId]])->avg('idx_diff');
		return $avgIdxAllGames;
	}
	private function getAvgScoreAllGames($exlDate, $mpId) { // last 10 games played
		$avgScoreAllGames = KjGameData::where([['game_date', '<=', $exlDate], ['mp_id', $mpId]])->avg('gross_score');
		return $avgScoreAllGames;
	}
	private function upsertMetaData($darray) {
		print_r($darray);
		KjGameData::upsert(
			[$darray],
			['mp_id', 'game_date'],
			[
				'games_played', // current year
				'avg_idx', // last 10
				'all_games_played',
				'avg_idx_all_games',
				'avg_score', // all games
			], 
		);
	}
	private function upsertDataForTheDate($exlDate, $mpId, $name, $grossScore, $idxDiff, $pair, $pairWinPoint, $teamWinPoint, $notes) {
		KjGameData::upsert([[
			'game_date' => $exlDate,
			'mp_id' => $mpId,
			'full_name'=> $name,
			'gross_score' => $grossScore,
			'idx_diff' => $idxDiff,
			'pair' => $pair,
			'pair_win_pt' => $pairWinPoint,
			'team_win_pt' => $teamWinPoint,
			'notes' => $notes,
			]], 
			['mp_id', 'game_date']
		);
	}
	private function getPlayData($sheet, $col, $exlDate, $notes) {
		$playData = [];
		$start = 5;
		$pIdx = 0;
		for ($rowIdx=$start; $rowIdx<300; ++$rowIdx) {
			$colx = $col;
			// $cell = $sheet->getCell("A${rowIdx}");
			// $name = $cell->getFormattedValue();
			$name = $sheet->getCell("A$rowIdx")->getValue();
			if (preg_match("/^Guest/", $name)) break;
			Log::info($rowIdx-$start . " name=[$name]");
			$grossScore = $sheet->getCell("$colx$rowIdx")->getValue();
			// print("$name $colx $rowIdx $grossScore\n");
			if ($grossScore == '') {
				continue;
			}
			// $matchPlayerId = $this->getMatchPlayerId($name);
			$mpId = $this->getMatchPlayerId($name);
			$idxDiff = $sheet->getCell(++$colx . $rowIdx)->getFormattedValue();
			$pair  = $sheet->getCell(++$colx . $rowIdx)->getValue();
			$pairWinPoint = $sheet->getCell(++$colx . $rowIdx)->getValue();
			$teamWinPoint = $sheet->getCell(++$colx . $rowIdx)->getFormattedValue();
			if ($teamWinPoint == '') $teamWinPoint = null;
			$this->upsertDataForTheDate($exlDate, $mpId, $name, $grossScore, $idxDiff, $pair, $pairWinPoint, $teamWinPoint, $notes);

			$gamesPlayed = $this->getGamesPlayed($exlDate, $mpId);
			$avgIdx = $this->getAvgIdx($exlDate, $mpId);
			// if ($avgIdx == '')	$avgIdx = $idxDiff;
			$allGamesPlayed = $this->getAllGamesPlayed($exlDate, $mpId);
			$avgIdxAllGames = $this->getAvgIdxAllGames($exlDate, $mpId);
			// if ($avgIdxAllGames == '') $avgIdxAllGames = $idxDiff;
			$avgScoreAllGames = $this->getAvgScoreAllGames($exlDate, $mpId);
			// if ($avgScoreAllGames == '') $avgScoreAllGames = $grossScore;
			array_push($playData, [
				'game_date' => $exlDate,
				'mp_id' => $mpId,
				'full_name'=> $name,
				'games_played' => $gamesPlayed, // current year
				'avg_idx' => $avgIdx, // last 10
				'all_games_played' => $allGamesPlayed,
				'avg_idx_all_games' => $avgIdxAllGames,
				'avg_score' => $avgScoreAllGames, // all games
				'gross_score' => $grossScore,
				'idx_diff' => $idxDiff,
				'pair' => $pair,
				'pair_win_pt' => $pairWinPoint,
				'team_win_pt' => $teamWinPoint,
				'notes' => $notes,
			]);
			$this->upsertMetaData($playData[$pIdx++]);
		}
		return $playData;
	}
	public function loadGameDataByCol($xlsxfile, $col) { // col is execl col like 'ZO' 'G' etc.
		Log::info("-CK-fn-loadGameDataByCol($xlsxfile, $col)", [__line__]);
		$sheet = $this->getSheet($xlsxfile); //Log::info("updKjGameData $xlsxfile", [$sheet]);
		$date = $this->getDateByCol($sheet, $col);
		Log::info("-CK-fn-getDateByCol=$date, col=$col", [__line__]);
		$notes = $this->getNotesByCol($sheet, $col);
		Log::info("-CK-notes=$notes, col=$col", [__line__]);
		$rating = $this->getRatingByCol($sheet, $col);
		Log::info("-CK-rating=$rating, col=$col", [__line__]);
		$slope = $this->getSlopeByCol($sheet, $col);
		Log::info("-CK-slope=$slope, col=$col", [__line__]);
		$playData = $this->getPlayData($sheet, $col, $date, $notes);
		// print_r($playData);
		// print_r($playData[5]);
		print("\n\n==== DONE: data loaded to DB for col=$col ==== \n\n");
		exit(0);
	}









private function XXXXXXXXBlocked() {
		$notes = $this->getNotesByCol($sheet);
		$highestRow = $sheet->getHighestRow();
		$range = $sheet->rangeToArray("A1:O$highestRow");
		$colIdx = $this->getColIndexOfAvg_Index($range);
		Log::info("-CK-latestDate=$date data range=[A1:N$highestRow] Avg Index(last 10) column=[$colIdx]", [__line__]);
		$rowIdx = 0;
		foreach($range as $row) {
			// Log::info("==row==[$colIdx]". $row[0], $range);
			$rowIdx++;
			Log::info("-CK-loop-rowIdx=$rowIdx [$colIdx] highestRow=$highestRow name=$row[0]", [__line__]);
			$name=$row[0];
			// if (preg_match('/Guest/', $name)) break;
			if (str_contains($name, 'Guest')) {
				Log::info("-DONE-loadLatestGameData finished at rowIdx=$rowIdx [$colIdx] highestRow=$highestRow name=$row[0]", [__line__]);
				break;
			}
			// $games_played=$row[$colIdx-2] == "" ? null : $row[$colIdx-2];
			$games_played=$row[$colIdx-1] == "" ? null : $row[$colIdx-1];
			// Log::info("-CK-loop-rowIdx=$rowIdx colIdx=$colIdx games_played=$games_played");
			// $net_win_loss=$row[$colIdx-1] == "" ? null : $row[$colIdx-1];
			$net_win_loss=$row[$colIdx] == "" ? null : $row[$colIdx];
			$avg=$row[$colIdx];
			$all_games_played=$row[$colIdx+1];
			$avg_idx_all_games=$row[$colIdx+2];
			$avg_score=$row[$colIdx+3];
			$gross_score=$row[$colIdx+4];
			$idx_diff=$row[$colIdx+5];
			$pair=$row[$colIdx+6];
			$pair_win_pct=$row[$colIdx+7] == "" ? null : $row[$colIdx+7];
			$team_win_pct=$row[$colIdx+8] == "" ? null : $row[$colIdx+8];
			// $pair_win_pct=rtrim($row[$colIdx+7], "%");
			// $team_win_pct=rtrim($row[$colIdx+8], '%');
			// $pair_win_pt=$row[$colIdx+7];
			// $team_win_pt=$row[$colIdx+8];
			$pair_win_pt=$row[$colIdx+9];
			$team_win_pt=$row[$colIdx+10];
			if ($name == null || $avg == null || $avg_score == null) continue;
			else if (str_contains($name, 'Guest')) break;
			Log::info("name=$name, avg=$avg avg_score=$avg_score");
			list($lastn, $firstn) = preg_split("/,\s*/", $name);
			$x = Player::where([['players.lastname', $lastn], ['firstname', $firstn], ['players.status','A'], ['match_players.status', 'A']])
				->leftJoin('match_players', function($leftJoin) {
						$leftJoin->on('players.id', 'match_players.player_id');
				})->select('players.id as pid', 'match_players.id as mid', 'match_players.alias', 'match_players.match_id')->get();
			if (count($x) != 1) {
				$aliases = MatchPlayer::where('status', 'A')->select('alias')->get();
				Log::info("$lastn", [ 'firstname' => $firstn, 'lastname' => $lastn, 'aliases'=> $aliases, 'status' => "ADD_NEW_SIM_PLAYER"]);
				return [ 'firstname' => $firstn, 'lastname' => $lastn, 'aliases'=> $aliases, 'status' => "ADD_NEW_SIM_PLAYER"];
			}
			$xx = $x->toArray()[0];
			$fullname = $lastn .', '.$firstn;
			$alias = $xx['alias'];
			// $match_player_id = DB::select('select id from match_players_view where name = "$fullname" and match_id = 14');
			$match_player_id = Player::where([['lastname', $lastn], ['firstname', $firstn], ['players.status', 'A'], ['match_players.status', 'A']])
			  ->leftJoin('match_players', function($leftJoin) {
					$leftJoin->on('match_players.player_id','players.id');
				})->select('match_players.id')->value('id');
			Log::info("fullname=$fullname alias=$alias game_date=$date match_player_id=$match_player_id avg10=$avg outfile=$xlsxfile", [__line__]); 
			KjGameData::upsert(
				[[
					'game_date' => $date,
					'mp_id' => $match_player_id, 
					'full_name' => $fullname,
					'games_played' => $games_played,
					'net_win_loss' => $net_win_loss,
					'avg_idx' => $avg,
					'all_games_played' => $all_games_played,
					'avg_idx_all_games' => $avg_idx_all_games,
					'avg_score' => $avg_score,
					'pair_win_pct' => $pair_win_pct,
					'team_win_pct' => $team_win_pct,
					'gross_score' => $gross_score,
					'idx_diff' => $idx_diff,
					'pair' => $pair,
					'pair_win_pt' => $pair_win_pt,
					'team_win_pt' => $team_win_pt,
					'notes' => $notes,
				]],
				['mp_id', 'game_date']
			);
			Log::info("-CK-B fullname=$fullname alias=$alias last_game_date=$date match_player_id=$match_player_id avg10=$avg avg_score=$avg_score", [__line__]); 
		}
		$matchPlayers = DB::select("select * from kj_game_data_views where game_date = '$date' order by handicap");
		return ['matchPlayers' => $matchPlayers, 'lastHcapDate' => $date, 'status' => "OK"];
	}


	
	//==== loadLatestGameData starts here ====
	private function loadURL($url) {
		// $kjsfile = 'http://mmsgolf.com/kj/Golf.xlsx';
		Log::info("loadURL from $url");
		$rhandle = fopen($url, "rb");
		if (false === $rhandle) {
			exit("Failed to open stream url=$url");
		}
		$contents = '';
		while (!feof($rhandle)) {
			$contents .= fread($rhandle, 8192);
		}
		fclose($rhandle);
		$xlsxfile = "/sites/tmp/kjsfiles/kjsfile_".date('D_H').".xlsx";
		$whandle = fopen($xlsxfile, 'wb');
		fwrite($whandle, $contents);
    fclose($whandle);
		return $xlsxfile;
	}
	// public function loadLatestGameData($xlsxfile) { 
	public function loadLatestGameData($url) {
		$xlsxfile = $this->loadURL($url);
		Log::info("-fn-updKjGameData $xlsxfile", [__line__]);
		$sheet = $this->getSheet($xlsxfile); //Log::info("updKjGameData $xlsxfile", [$sheet]);
		$notes = $this->getNotes($sheet); //exit(0);
		$date = $this->getLatestDate($sheet);
		$highestRow = $sheet->getHighestRow();
		// $range = $sheet->rangeToArray("A1:C$highestRow");
		// $range = $sheet->rangeToArray("A1:G$highestRow");
		$range = $sheet->rangeToArray("A1:O$highestRow");
		$colIdx = $this->getColIndexOfAvg_Index($range);
		Log::info("-CK-latestDate=$date data range=[A1:N$highestRow] Avg Index(last 10) column=[$colIdx]", [__line__]);
		$rowIdx = 0;
		foreach($range as $row) {
			// Log::info("==row==[$colIdx]". $row[0], $range);
			$rowIdx++;
			Log::info("-CK-loop-rowIdx=$rowIdx [$colIdx] highestRow=$highestRow name=$row[0]", [__line__]);
			$name=$row[0];
			// if (preg_match('/Guest/', $name)) break;
			if (str_contains($name, 'Guest')) {
				Log::info("-DONE-loadLatestGameData finished at rowIdx=$rowIdx [$colIdx] highestRow=$highestRow name=$row[0]", [__line__]);
				break;
			}
			// $games_played=$row[$colIdx-2] == "" ? null : $row[$colIdx-2];
			$games_played=$row[$colIdx-1] == "" ? null : $row[$colIdx-1];
			// Log::info("-CK-loop-rowIdx=$rowIdx colIdx=$colIdx games_played=$games_played");
			// $net_win_loss=$row[$colIdx-1] == "" ? null : $row[$colIdx-1];
			$net_win_loss=$row[$colIdx] == "" ? null : $row[$colIdx];
			$avg=$row[$colIdx];
			$all_games_played=$row[$colIdx+1];
			$avg_idx_all_games=$row[$colIdx+2];
			$avg_score=$row[$colIdx+3];
			$gross_score=$row[$colIdx+4];
			$idx_diff=$row[$colIdx+5];
			$pair=$row[$colIdx+6];
			$pair_win_pct=$row[$colIdx+7] == "" ? null : $row[$colIdx+7];
			$team_win_pct=$row[$colIdx+8] == "" ? null : $row[$colIdx+8];
			// $pair_win_pct=rtrim($row[$colIdx+7], "%");
			// $team_win_pct=rtrim($row[$colIdx+8], '%');
			// $pair_win_pt=$row[$colIdx+7];
			// $team_win_pt=$row[$colIdx+8];
			$pair_win_pt=$row[$colIdx+9];
			$team_win_pt=$row[$colIdx+10];
			if ($name == null || $avg == null || $avg_score == null) continue;
			else if (str_contains($name, 'Guest')) break;
			Log::info("name=$name, avg=$avg avg_score=$avg_score");
			list($lastn, $firstn) = preg_split("/,\s*/", $name);
			$x = Player::where([['players.lastname', $lastn], ['firstname', $firstn], ['players.status','A'], ['match_players.status', 'A']])
				->leftJoin('match_players', function($leftJoin) {
						$leftJoin->on('players.id', 'match_players.player_id');
				})->select('players.id as pid', 'match_players.id as mid', 'match_players.alias', 'match_players.match_id')->get();
			if (count($x) != 1) {
				$aliases = MatchPlayer::where('status', 'A')->select('alias')->get();
				Log::info("$lastn", [ 'firstname' => $firstn, 'lastname' => $lastn, 'aliases'=> $aliases, 'status' => "ADD_NEW_SIM_PLAYER"]);
				return [ 'firstname' => $firstn, 'lastname' => $lastn, 'aliases'=> $aliases, 'status' => "ADD_NEW_SIM_PLAYER"];
			}
			$xx = $x->toArray()[0];
			$fullname = $lastn .', '.$firstn;
			$alias = $xx['alias'];
			// $match_player_id = DB::select('select id from match_players_view where name = "$fullname" and match_id = 14');
			$match_player_id = Player::where([['lastname', $lastn], ['firstname', $firstn], ['players.status', 'A'], ['match_players.status', 'A']])
			  ->leftJoin('match_players', function($leftJoin) {
					$leftJoin->on('match_players.player_id','players.id');
				})->select('match_players.id')->value('id');
			Log::info("fullname=$fullname alias=$alias game_date=$date match_player_id=$match_player_id avg10=$avg outfile=$xlsxfile", [__line__]); 
			KjGameData::upsert(
				[[
					'game_date' => $date,
					'mp_id' => $match_player_id, 
					'full_name' => $fullname,
					'games_played' => $games_played,
					'net_win_loss' => $net_win_loss,
					'avg_idx' => $avg,
					'all_games_played' => $all_games_played,
					'avg_idx_all_games' => $avg_idx_all_games,
					'avg_score' => $avg_score,
					'pair_win_pct' => $pair_win_pct,
					'team_win_pct' => $team_win_pct,
					'gross_score' => $gross_score,
					'idx_diff' => $idx_diff,
					'pair' => $pair,
					'pair_win_pt' => $pair_win_pt,
					'team_win_pt' => $team_win_pt,
					'notes' => $notes,
				]],
				['mp_id', 'game_date']
			);
			Log::info("-CK-B fullname=$fullname alias=$alias last_game_date=$date match_player_id=$match_player_id avg10=$avg avg_score=$avg_score", [__line__]); 
		}
		$matchPlayers = DB::select("select * from kj_game_data_views where game_date = '$date' order by handicap");
		return ['matchPlayers' => $matchPlayers, 'lastHcapDate' => $date, 'status' => "OK"];
	}
	private function getSheet($xlsxfile) {
		$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
		// $reader->setReadFilter( new MyReadFilter() );
		$reader->setReadDataOnly(false);
		// $reader->setReadDataOnly(true);
		$spreadsheet = $reader->load($xlsxfile);
		$sheet = $spreadsheet->getActiveSheet();
		// Log::info("getSheet($xlsxfile)");
		return $sheet;
	}
	private function getLatestDate($sheet) {
		$cell = $sheet->getCellByColumnAndRow(13, 1);
		// Log::info(print_r($cell, true));
		// Log::error("cell", ['cell' => $cell->cell]);
		// $cell = $sheet->getCell("H1");
		$str = "FGHIJKLMNOPQRSTU";
		$date = null;
		forEach(str_split($str) as $char) {
			$cell = $sheet->getCell("{$char}1");
			$date = $cell->getFormattedValue();
			// Log::info("==fn==[{$char}1][$date]");
			if (preg_match('@\d{1,}/\d{1,}/\d{4}@', $date)) break;
		}
		// $xx = $cell->getFormattedValue();
		Log::info("getLatestDate(sheet) [$date]");
		$x = explode('/', $date);
		$date = $x[2].'-'.$x[0].'-'.$x[1];
		return $date;
	}
	private function getNotes($sheet) {
		$cell = $sheet->getCellByColumnAndRow(13, 2);
		$str = "FGHIJKLMNOPQRSTU";
		$notes = null;
		forEach(str_split($str) as $char) {
			$cell = $sheet->getCell("{$char}2");
			$notes = $cell->getFormattedValue();
			if ($notes != '') {
				$notes = preg_replace("/\t|\n/", ", ", $notes);
				Log::info("=CK=fn [{$char}2][$notes]");
				return $notes;
			}
		}
		Log::info("getNotes(sheet) [$course]");
		// $x = explode('/', $date);
		// $date = $x[2].'-'.$x[0].'-'.$x[1];
		return $course;
	}
	private function getColIndexOfAvg_Index($range) {
		foreach ($range as $row) {
			$idx = 0;
			foreach ($row as $col) {
				if ($col == 'Ave_Index (Last 10)') return $idx;
				$idx++;
			}
		}
	}
}

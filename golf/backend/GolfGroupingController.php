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
use App\Models\golf\KjNewPlayer;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use App\Traits\MyTraits;
use App\Traits\ExcelTrait;

// use App\Models\golf\LogPage;
// class MyReadFilter implements IReadFilter {
// 	public function readCell($columnAddress, $row, $worksheetName = '') {
// 		// Read title row and rows 20 - 30
// 		if ($row == 1 || ($row >= 5 && $row <= 30)) {
// 			return true;
// 		}
// 		return false;
// 	}
// }

\Config::set('database.default', 'golf_dev');
class GolfGroupingController extends Controller {
	use MyTraits;
	use ExcelTrait;
	public function __construct() {
		// $nologs = ['/golf/getUserType', '/golf/getPlayerCount', '/golf/updGScore', '/golf/insGScore', '/golf/getPStrokes'];
		// // Log::info('$_SERVER["REMOTE_ADDR"]=' . $_SERVER['REMOTE_ADDR'] . ' $_SERVER["REQUEST_URI"]=' . $_SERVER['REQUEST_URI'], $nologs);
		// if (in_array($_SERVER['REQUEST_URI'], $nologs)) return;
		// $this->logPage('from __construct'); // this will be called for every click
	}
	public function getHandicaps($gameId) { Log::info("getHandicaps $gameId");
		$playerTable = $gameId == 13 ? 'players' : 'match_players';
		$qry = null;
		if ($gameId == 14) $qry = "select player_id, player_handicap(player_id, $gameId) as handicap from match_players WHERE status = 'A'";
		else $qry = "select id as player_id, player_handicap(id, $gameId) as handicap from players WHERE status = 'A'";
		// Log::info("getHandicaps qry=[$qry]");
		$handicaps = DB::select($qry);
		return ['handicaps' => $handicaps, 'gameId' => $gameId, 'status' => 'OK'];
	}
	// public function getHandicaps($mdate, $gameId) { Log::info("getHandicaps $mdate, $gameId");
	// 	$qry = "select player_id, handicap_idx(player_id, $gameId, '$mdate') as handicap from tplayers p, tournaments t ";
	// 	$qry.= "where p.tournament_id = t.id and t.start_at like '" . $mdate . "%' and p.status = 'A' and t.status='A'";
	// 	Log::info("getHandicaps qry=[$qry]");
	// 	$handicaps = DB::select($qry);
	// 	return ['handicaps' => $handicaps, 'status' => 'OK'];
	// }
	public function getMatchGroupingPlayers() { Log::info("getMatchGroupingPlayers");
		$qry = 'select gender, id as player_id, null as handicap, concat(lastname,", ", firstname) as name from players where status = "A" order by name';
		// $qry = 'select gender, id as player_id, concat(lastname,", ", firstname) as name from players where status = "A" order by name';
		$lst = DB::select($qry);
		return ['lst' => $lst, 'status' => 'OK'];
	}
	public function saveGrouping(Request $da) { Log::info("saveGrouping $da->gsx (all players should be tplayers, i.e. they have id in tplayers)", $da->tmntIds); 
		// $ids = Tplayer::whereIn('tournament_id', $da->tmntIds)->pluck('id');
		// Log::info("all ids=$ids");
		// foreach ($ids as $id) Tplayer::destroy($id);
		$gsx = $da->gsx;
		foreach ($da->tmntIds as $tmntId) {
			MatchGroup::updateOrCreate(
				['tournament_id' => $tmntId],
				['group_scenario' => $gsx]
			);
		}

		$pTmntIds = [];
		$tmntIds = [];
		$tplayers = $da->tplayers; //Log::info("groupingFinalized $da->gsx", $players);
		foreach($tplayers as $tp) { // Log::info("groupingFinalized-loopX", $tp);
			$dm = Tplayer::find($tp['id']);
			$name=$tp['name'];
			$dm->name = $name;
			$dm->tournament_id = $tp['tmntId'];
			$dm->player_id = $tp['player_id'];
			$dm->game_id = isset($tp['game_id']) ? $tp['game_id'] : $tp['gameId'];
			$dm->year = $tp['year'];
			$dm->grp = $tp['grp'];
			$dm->tnum = isset($tp['team']) ? $tp['team'] : $tp['tnum'];
			$dm->save();
			// $this->cleanupScores($tp['player_id'], $tp['tmntId']);
			$this->cleanupScores($tp['player_id'], $da['tmntIds']);
		}
		
		return ['status' => 'OK'];
	}
	public function addupdMatchGroups(Request $da) { Log::info("addupdMatchGroups gsx=$da->gsx", $da->tmntIds); 
		$gsx = $da->gsx;
		foreach ($da->tmntIds as $tmntId) {
			MatchGroup::updateOrCreate(
				['tournament_id' => $tmntId],
				['group_scenario' => $gsx]
			);
		}
		return ['status' => 'OK'];
	}
	private function cleanupScores($playerId, $tmntIds) {
		$sids = Score::whereIn('tournament_id', $tmntIds)->where('player_id', $playerId)->pluck('id');
		foreach($sids as $sid) {
			Score::destroy($sid);
		}
	}
	public function moveOutGrouped(Request $tp) { Log::info("moveOutGrouped", $tp->toArray());
		$tpid = $tp['id'];
		$dm = Tplayer::find($tpid);
		$dm->tnum = $tp['team'];
		$dm->grp = $tp['grp'];
		$dm->save();
		// $this->cleanupScores($tp['player_id'], $tp['tmntIds']);
		$da = new Request([ 'gsx' => 0, 'tmntIds' => [$tp['tmntId']] ]);
		$this->addupdMatchGroups($da);
		return ['status' => 'OK'];
	}
	public function moveOutGrouping(Request $tp) { Log::info("moveOutGrouping", $tp->toArray());
		$tpid = $tp['id'];
		Tplayer::destroy($tpid);
		$this->cleanupScores($tp['player_id'], [$tp['tmntId']]);
		return ['status' => 'OK'];
	}
	public function moveToGrouped(Request $tp) { Log::info("moveToGrouped", $tp->toArray());
		$dm = Tplayer::find($tp['id']);
		if ($dm == null) {
			return $this->moveToGrouping($tp);
		} else {
			$dm->grp = $tp['grp'];
			// $dm->tnum = ($tp['team'] == null ? $tp['startAt'] == null ? $tp['tnum'] == null ? null : $tp['tnum']);
			if ($tp['team'] != null) $dm->tnum = $tp['team'];
			else if ($tp['startAt'] != null) $dm->tnum = $tp['startAt'];
			else if ($tp['tnum'] != null) $dm->tnum = $tp['tnum'];
			$dm->tournament_id = $tp['tmntId'];
			// if ($tp['captain'] > 0) 
			$dm->captain = $tp['captain'];
			$dm->save();
		}
		$scoreId = $tp['scoreId'];
		if ($scoreId > 0) {
			$ds = Score::find($scoreId);
			$ds->course_id = $tp['courseId'];
			$ds->teebox_id = $tp['teeboxId'];
			$ds->teetime = $tp['teetime'];
			$ds->tournament_id = $tp['tmntId'];
			$ds->update();
		}
		return ['id' => $dm->id, 'status' => 'OK'];
	}
	public function moveToGrouping(Request $tp) { Log::info("moveToGrouping", $tp->toArray());
		$dm = new Tplayer;
		$dm->name = $tp['name'];
		$dm->tournament_id = $tp['tmntId'];
		$dm->player_id = $tp['player_id'];
		$dm->game_id = isset($tp['game_id']) ? $tp['game_id'] : $tp['gameId'];
		$dm->year = $tp['year'];
		$dm->grp = $tp['grp'];
		$dm->captain = $tp['captain'];
		$dm->tnum = isset($tp['team']) ? $tp['team'] : $tp['tnum'];
		$dm->save();
		return ['status' => 'OK', 'id' => $dm->id, 'playerId' => $dm->player_id];
	}
	public function addToMatchPlayer($pid, $alias, $matchId, $matchPlayerId) { Log::info("addToMatchPlayer pid=$pid gameId=$matchId alias=$alias$matchPlayerId=$matchPlayerId");
		$dm = MatchPlayer::find($matchPlayerId);
		$dm->player_id = $pid;
		$dm->alias = $alias;
		$dm->match_id = $matchId;
		$dm->update();
		$dx = KjNewPlayer::find($matchPlayerId);
		$dx->player_id = $pid;
		$dx->alias = $alias;
		$dx->game_id = $matchId;
		$dx->update();
    $this->getAliases($matchId);
		return ['status' => 'OK'];
	}
	// public function addToMatchPlayer($pid, $alias, $matchId, $kjNewPlayerId) { Log::info("addToMatchPlayer pid=$pid gameId=$matchId alias=$alias kjNewPlayerId=$kjNewPlayerId");
	// 	$dm = new MatchPlayer;
	// 	$dm->player_id = $pid;
	// 	$dm->alias = $alias;
	// 	$dm->match_id = $matchId;
	// 	$dm->save();
	// 	$dx = KjNewPlayer::find($kjNewPlayerId);
	// 	$dx->player_id = $pid;
	// 	$dx->alias = $alias;
	// 	$dx->game_id = $matchId;
	// 	$dx->save();
  //   $this->getAliases($matchId);
	// 	return ['status' => 'OK'];
	// }
	public function addNewSimPlayer(Request $da) { Log::info("addNewSimPlayer", $da->toArray());
		// $kjNewPlayerId = $da['id'];
		$matchId = $da['gameId'];
		$matchPlayerId = $da['id'];
		$alias = $da['alias'];
		$fnm = $da['firstname'];
		$lnm = $da['lastname'];
		$pid = Player::where([['firstname', $fnm], ['lastname', $lnm], ['status', 'A']])->select('id')->value('id');
		Log::info("$lnm, $fnm checking in players with id=$pid, will add to match_players");
		if ($pid > 0) {
			Log::info("$lnm, $fnm exists in players with id=$pid, will update match_players for id=$pid");
      return $this->addToMatchPlayer($pid, $alias, $matchId, $matchPlayerId);
			// $dm = MatchPlayer::find($matchPlayerId);
			// $dm->player_id = $pid;
			// $dm->alias = $alias;
			// $dm->update();
			// $dx = KjNewPlayer::find($matchPlayerId);
			// $dx->player_id = $pid;
			// $dx->alias = $alias;
			// $dx->update();
			// return;
		}
		$dm = new Player;
		$dm->firstname = $da['firstname'];
		$dm->lastname = $da['lastname'];
		$dm->gender = $da['gender'];
		$dm->nkname = $da['nkname'];
		$dm->email = $da['email'];
		$dm->chname = $da['chname'];
		$dm->save();
		$pid = $dm->id;
		$alias = $da['alias'];
		return $this->addToMatchPlayer($pid, $alias, $matchId, $matchPlayerId);
	}
	// public function addNewSimPlayer(Request $da) { Log::info("addNewSimPlayer", $da->toArray());
	// 	// $kjNewPlayerId = $da['id'];
	// 	$kjNewPlayerId = $da['id'];
	// 	$fnm = $da['firstname'];
	// 	$lnm = $da['lastname'];
	// 	$pid = Player::where([['firstname', $fnm], ['lastname', $lnm], ['status', 'A']])->select('id')->value('id');
	// 	Log::info("$lnm, $fnm checking in players with id=$pid, will add to match_players");
	// 	if ($pid > 0) {
	// 		Log::info("$lnm, $fnm exists in players with id=$pid, will add to match_players");
  //     return $this->addToMatchPlayer($pid, $da['alias'], $da['gameId'], $kjNewPlayerId);
	// 	}
	// 	$dm = new Player;
	// 	$dm->firstname = $da['firstname'];
	// 	$dm->lastname = $da['lastname'];
	// 	$dm->gender = $da['gender'];
	// 	$dm->nkname = $da['nkname'];
	// 	$dm->email = $da['email'];
	// 	$dm->chname = $da['chname'];
	// 	$dm->save();
	// 	$pid = $dm->id;
	// 	$alias = $da['alias'];
	// 	$matchId = $da['gameId'];
	// 	return $this->addToMatchPlayer($pid, $alias, $matchId, $kjNewPlayerId);
	// }
	// public function getKJAliases($gameId) { Log::info("getKJAliases gameId=$gameId");
	// 	// $this->logPage('from __construct');
	// }
	/***
	 * If you just need to find out if two files are identical, 
	 * comparing file hashes can be inefficient, especially on large files. 
	 * There's no reason to read two whole files and do all the math 
	 * if the second byte of each file is different.
	 * If you don't need to store the hash value for later use,
	 * there may not be a need to calculate the hash value just to compare files.  
	 * This can be much faster:
	*/
	private	function files_identical($fn1, $fn2) {
		define('READ_LEN', 4096);
		if(filetype($fn1) !== filetype($fn2))
			return FALSE; // same as false

		if(filesize($fn1) !== filesize($fn2))
			return FALSE;

		if(!$fp1 = fopen($fn1, 'rb'))
			return FALSE;

		if(!$fp2 = fopen($fn2, 'rb')) {
			fclose($fp1);
			return FALSE;
		}

		$same = TRUE; //same as true
		while (!feof($fp1) and !feof($fp2))
			if(fread($fp1, READ_LEN) !== fread($fp2, READ_LEN)) {
					$same = FALSE;
					break;
			}
	
		if(feof($fp1) !== feof($fp2))
				$same = FALSE;

		fclose($fp1);
		fclose($fp2);

		return $same;
	}
	public function execution_time($fn1, $fn2) {
		$start_time = microtime(true);
		md5_file($fn1);
		$end_time = microtime(true);
		Log::info("md5             time:".($end_time - $start_time));
		$start_time = microtime(true);
		sha1_file($fn1);
		$end_time = microtime(true);
		Log::info("sha1            time:".($end_time - $start_time));
		$start_time = microtime(true);
		$this->files_identical($fn1, $fn2);
		$end_time = microtime(true);
		Log::info("files_identical time:".($end_time - $start_time));
		$this->getLatestDate($this->getSheet($fn1));
		$end_time = microtime(true);
		Log::info("getLatestDate   time:".($end_time - $start_time));
	}
	public function XXgetKJAliases($lasthdate) {
		Log::info("lasthdate=$lasthdate, latest KJ data loaded from swang/crontab/loadLatestkjdata", [__file__, __line__]);
		$ah = KjGameData::where([['game_date', '=', $lasthdate], ['match_players.status', 'A'], ['kj_game_data.status', 'A']])
			->join('match_players', function($join) { $join->on('match_players.id', '=', 'kj_game_data.mp_id'); })
			->select('match_players.player_id as player_id', 'kj_game_data.full_name as name', 'match_players.alias', 
							 'kj_game_data.avg_idx as handicap', 'match_players.match_id as gameId')->orderBy('alias')->get();

		// $xh = DB::table('match_players')->where(DB::raw('id not in (SELECT distinct mp_id from kj_game_data)'))
		$xh = DB::table('match_players')
			->join('players', function($join) { $join->on('players.id', '=', 'match_players.player_id'); })
			->select(DB::raw('player_id, concat(players.lastname, ", ", players.firstname) as name, alias, 28.2 AS handicap, match_id AS gameId'))
			->whereNotIn('match_players.id', KjGameData::where([['game_date', '=', $lasthdate], ['match_players.status', 'A'], ['kj_game_data.status', 'A']])->select('mp_id'))
			->where('match_players.status', "A")
			->orderBy('alias')->get();
		$ah = array_merge($ah->toArray(), $xh->toArray());

		// Log:info("xh=", $xh->toArray());

		// $ah = KjGameData::where([['game_date', '=', $lasthdate], ['match_players.status', 'A'], ['kj_game_data.status', 'A']])
		// 	->leftJoin('match_players', function($leftJoin) { $leftJoin->on('match_players.id', 'kj_game_data.mp_id'); })
		// 	->select('match_players.player_id as player_id', 'kj_game_data.full_name as name', 'match_players.alias', 
		// 					 'kj_game_data.avg_idx as handicap', 'match_players.match_id as gameId')->orderBy('alias')->get();
		return ['aliases' => $ah, 'lastHandicapDate' => $lasthdate, 'gameId' => 14, 'status' => 'OK'];
	}
	public function getAliases($gameId, $forceLoad=FALSE) { Log::info("getAliases gameId=$gameId forceLoad=$forceLoad", [__line__]); //$this->testPrint();
		// $this->logPage('from __construct');
		$ah = null;
		if ($gameId == 13 || $gameId == 16) {
			$qry = "SELECT pa.player_id, pa.alias, pa.game_id, CONCAT(p.lastname, ', ', p.firstname) as name FROM ";
			$qry.= "player_aliases pa, players p WHERE pa.player_id = p.id AND pa.status = 'A' AND p.status ='A' order by pa.alias";
			$ah = DB::select($qry);
			// Log::info("getAliases gameId=$gameId forceLoad=$forceLoad", $ah); 
			// return ['aliases' => $ah, 'gameId' => $gameId, 'status' => 'OK'];
		} else if ($gameId == 14) {
			// $lasthdate = KjGameData::orderBy('game_date', 'desc')->limit(1)->value('game_date');
			// Log::info("-fn-getAliases lasthdate=$lasthdate(lasthdate is from table KjGameData)", [__file__, __line__]);
			// return $this->getKJAliases($lasthdate);
			$qry = "SELECT player_id, alias, match_id as game_id, CONCAT(lastname, ', ', firstname) as name FROM match_players WHERE status = 'A' order by alias";
			$ah = DB::select($qry);
			// Log::info("getAliases gameId=$gameId", $ah); 
		}
		return ['aliases' => $ah, 'gameId' => $gameId, 'status' => 'OK'];
	}
	// public function getAliases($gameId, $forceLoad=FALSE) { Log::info("getAliases gameId=$gameId forceLoad=$forceLoad", [__line__]); //$this->testPrint();
	// 	// $this->logPage('from __construct');
	// 	$ah = null;
	// 	if ($gameId == 13 || $gameId == 16) {
	// 		$ah = DB::select("CALL get_handicap_and_aliases(?)", [$gameId]);
	// 		// Log::info("getAliases gameId=$gameId forceLoad=$forceLoad", $ah); 
	// 		return ['aliases' => $ah, 'gameId' => $gameId, 'status' => 'OK'];
	// 	} else if ($gameId == 14) {
	// 		$lasthdate = KjGameData::orderBy('game_date', 'desc')->limit(1)->value('game_date');
	// 		Log::info("-fn-getAliases lasthdate=$lasthdate(lasthdate is from table KjGameData)", [__file__, __line__]);
	// 		return $this->getKJAliases($lasthdate);
	// 	}
	// }
}

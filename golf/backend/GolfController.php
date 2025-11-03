<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Models\golf\MatchPlayerHandicap;
use App\Models\golf\MatchPlayer;
use App\Models\golf\MatchGroup;
use App\Models\golf\Score;
use App\Models\golf\Player;
use App\Models\golf\Tplayer;
use App\Models\golf\Course;
use App\Models\golf\CourseTee;
use App\Models\golf\CoursePar;
use App\Models\golf\CourseYard;
use App\Models\golf\CourseHandicap;
use App\Models\golf\GameName;
use App\Models\golf\Tournament;
use App\Models\golf\Membership;
use App\Models\golf\UserGuide;
use App\Models\golf\LogPage;
use App\Models\golf\KjNewPlayer;
use App\Models\golf\KjGameData;

use App\Models\golf\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

\Config::set('database.default', 'golf_dev');
class GolfController extends Controller {
	private $gross_ranks = [];

	public function __construct() {
		$nologs = ['/golf/getUserType', '/golf/getPlayerCount', '/golf/updGScore', '/golf/insGScore', '/golf/getPStrokes'];
		// Log::info($_SERVER["REQUEST_URI"], $_SERVER);
		// Log::info('$_SERVER["REMOTE_ADDR"]=' . $_SERVER['REMOTE_ADDR'] . ' $_SERVER["REQUEST_URI"]=' . $_SERVER['REQUEST_URI'], $nologs);
		if (isset($_SERVER["REQUEST_URI"]) and in_array($_SERVER["REQUEST_URI"], $nologs)) return;
		$this->logPage('from __construct'); // this will be called for every click
	}
	public function getKjGamePlayers() {
		$kjGameLatestDate = KjGameData::where('status', 'A')->max('game_date');
		$kjGamePlayers = KjGameData::where('status', 'A')->select('full_name as label', 'mp_id as value')->distinct()->orderBy('full_name')->get();//Log::info("game_dates", $gameDates->toArray());
		return ['kjGamePlayers' => $kjGamePlayers, 'kjGameLatestDate' => $kjGameLatestDate, 'status' => 'OK']; 
	}
	public function getKjGameDates() {
		$kjGameDates = KjGameData::where('status', 'A')->select('game_date')->distinct()->orderByDesc('game_date')->get();//Log::info("game_dates", $gameDates->toArray());
		return ['kjGameDates' => $kjGameDates, 'status' => 'OK']; 
	}
	public function getKjGameDataByMpId($mpId) {
		// _iog::info("gameData=[$gameDate]");
		$mpIdClause = [['status','A'], ['mp_id',$mpId]];
		$kjGameData = KjGameData::where($mpIdClause)->orderByDesc('game_date')
		  ->select('game_date', 'games_played','avg_idx','net_team_win_loss','net_pair_win_loss','all_games_played','avg_idx_all_games','avg_score','gross_score')->get();
		return ['kjGameData' => $kjGameData, 'status' => 'OK']; 
	}
	public function getKjGameDataByDate($date) {
		$dateClause = [['status','A'], ['game_date', $date], ['full_name', '<>', 'Ren, Jianlin']];
			$q = KjGameData::where($dateClause)->orderBy('avg_idx')->select('full_name as game_date','games_played','avg_idx','net_team_win_loss');
			$kjGameData = $q->addSelect('net_pair_win_loss','all_games_played','avg_idx_all_games','avg_score','gross_score')->get();
		return ['kjGameData' => $kjGameData, 'status' => 'OK']; 
	}
	public function getKjNewPlayer() { Log::info("getKjNewPlayer()", [__file__, __line__]);
		$kjNewPlayer = KjNewPlayer::where([['player_id', null], ['alias', null], ['status', 'A']])
			->select('match_player_id', 'firstname', 'lastname')->limit(1)->get();
		return ['kjNewPlayer' => $kjNewPlayer, 'status' => "OK"];
	}
	public function checkReminder($date) { Log::info("checkReminder($date)");
		$todo = DB::select('CALL check_reminder(?)', [$date]);
		Log::info("checkReminder($date)", $todo);
		if (count($todo) == 0) $todo = 'NOTHING_TODO';
		return ['status' => 'OK', 'todo' => $todo];
	}
	private function now() {
		return Date('Y-m-d H:i:s', time());
	}
	private function logPage($params=null) {
		$request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'http://localhost';
		// Log::info($_SERVER['REMOTE_ADDR'] . ' ' . $_SERVER['REQUEST_URI'] . ' ' . $this->now());
		$dx = new LogPage();
		$dx->ip_address = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
		// $dx->page_name = preg_replace('/\/golf\//', '', $_SERVER['REQUEST_URI']);
		$dx->page_name = preg_replace('/\/golf\//', '', $request_uri);
		$dx->params = $params;
		$dx->save();
	}	
	// public function savePoyg($poyg, $playerId, $tmntId, $gameId, $year) { Log:info("savePoyg $poyg, $playerId, $tmntId, $year");
	public function savePoyg($poyg, $tid) { Log:info("savePoyg $poyg, $tid");
		$dm = Tplayer::find($tid);
		$dm->poyg = $poyg;
		$dm->save();
		return ['status' => "OK"];
	}
	public function doubleBack9($playerId, $tmntId) { Log:info("doubleBack9 $playerId, $tmntId");
		Tplayer::where('tournament_id', $tmntId)->update(['activity' => 'dxbl']);
		Tplayer::where('tournament_id', $tmntId)->where('player_id', $playerId)->update(['activity' => 'dobl']);
		return $this->getTeamMatchPlayers($tmntId, 13, null);
	}
	public function getPStrokes(Request $da) { Log:info("getPStrokes", $da->toArray());
		$this->logPage($da['name']);
		$playerId = $da['playerId'];
		$courseId = $da['courseId'];
		$teeboxId = $da['teeboxId'];
		$tmntId = $da['tmntId'];
		$teetime = $da['teetime'];
		$strokes = Score::where([['player_id', $playerId], ['course_id', $courseId], ['teetime', $teetime], ['teebox_id', $teeboxId], ['tournament_id', $tmntId]])
			->select('id','player_id','course_id','teetime','h1','h2','h3','h4','h5','h6','h7','h8','h9','h10','h11','h12','h13','h14','h15','h16','h17','h18','front9','back9','totalscore','note')
			->get();
		if (count($strokes) === 1) return ['strokes' => $strokes[0], 'status' => "OK"];
		else if (count($strokes) > 1) return ['status' => "more_than_one_score"];
		else return ['status' => "no_score"];
	}
	private function getHandicapDiff($gscore, $rating, $slope) {
		return ($gscore - $rating) * 113 / $slope;
	}
	public function insGScore(Request $da) { Log:info("insGScore", $da->toArray());
		$this->logPage($da['front9'].' '.$da['back9'].' '.$da['totalscore'].' '.$da['name']);
		$dm = new Score($da->toArray());
		$dm->hdcpdiff = $this->getHandicapDiff($da['totalscore'], $da['rating'], $da['slope']);
		$dm->save();
		$scoreId = $dm->id;
		if ($da['tplayerId'] > 0) $this->updTPlayerScore($da, $dm->totalscore, $dm->hdcpdiff, $scoreId, $da['tplayerId']);
		return ['scoreId' => $dm->id, 'status' => "OK"];
	}
	public function updGScore(Request $da) { Log:info("updGScore", $da->toArray());
		$this->logPage($da['front9'].' '.$da['back9'].' '.$da['totalscore'].' '.$da['name']);
    $dm = Score::find($da['id']);
		$dm->player_id = $da['player_id'];
		// $dm->tournamentId = $da['tmntId'];
		$dm->tournament_id = $da['tournament_id'];
		$dm->course_id = $da['course_id'];
		$dm->teebox_id = $da['teebox_id'];
		$dm->teetime = $da['teetime'];
		$dm->front9 = $da['front9'];
		$dm->back9 = $da['back9'];
		$dm->totalscore = $da['totalscore'];
		$dm->hdcpdiff = $this->getHandicapDiff($da['totalscore'], $da['rating'], $da['slope']);
		$dm->note = $da['note'];
		$dm->h1 = $da['h1'];
		$dm->h2 = $da['h2'];
		$dm->h3 = $da['h3'];
		$dm->h4 = $da['h4'];
		$dm->h5 = $da['h5'];
		$dm->h6 = $da['h6'];
		$dm->h7 = $da['h7'];
		$dm->h8 = $da['h8'];
		$dm->h9 = $da['h9'];
		$dm->h10 = $da['h10'];
		$dm->h11 = $da['h11'];
		$dm->h12 = $da['h12'];
		$dm->h13 = $da['h13'];
		$dm->h14 = $da['h14'];
		$dm->h15 = $da['h15'];
		$dm->h16 = $da['h16'];
		$dm->h17 = $da['h17'];
		$dm->h18 = $da['h18'];
    $dm->update();
		if ($da['tplayerId'] > 0) $this->updTPlayerScore($da, $dm->totalscore, $dm->hdcpdiff, 0, $da['tplayerId']);
		return ['status' => "OK"];
	}
	public function getGroupScores($tmntId, $ttim, $courseId, $teeboxId) { Log:info("getGroupScores $tmntId, $ttim, $courseId, $teeboxId");
		$scores = DB::select('CALL get_group_scores(?, ?)', [$tmntId, $ttim]);
		// $courseId = Arr::get($scores, 0)->courseId;
		// $teeboxId = Arr::get($scores, 0)->teeboxId;
		// $teebox = Arr::get(CourseTee::where([['id', $teeboxId], ['status', 'A']])->select('teebox')->get(), 0)->teebox;
		// Log::info("courseInfo", [$courseId, $teeboxId, $teebox]);
		// $holes = CoursePar::where([['status', 'A'], ['courseId', $courseId]])->select('h1','h2','h3','h4','h5','h6','h7','h8','h9','h10','h11','h12','h13','h14','h15','h16','h17','h18')->get();
		// $hcaps = CourseHandicap::where([['status', 'A'], ['courseId', $courseId]])->select('p1','p2','p3','p4','p5','p6','p7','p8','p9','p10','p11','p12','p13','p14','p15','p16','p17','p18')->get();
		// $yards = CourseYard::where([['status','A'],['courseId',$courseId],['teebox',$teebox]])->select('y1','y2','y3','y4','y5','y6','y7','y8','y9','y10','y11','y12','y13','y14','y15','y16','y17','y18')->get();
		// return ['scores' => $scores, 'holes' => $holes, 'hcaps' => $hcaps, 'yards' => $yards, 'status' => "OK"];
		return ['scores' => $scores, 'status' => "OK"];
	}
	public function delPGCTplayer($id) { Log:info("delPGCTplayer $id");
		Tplayer::destroy($id);
		return ['status' => "OK"];
	}
	public function delTplayer($Id, $tmntId) { Log:info("delTplayer $Id $tmntId");
		Tplayer::destroy($Id);
		return $this->getTplayers($tmntId);
	}
	public function updUserGuide(Request $da) { Log:info("updUserGuide", $da->toArray());
    $dm = Userguide::find($da['id']);
    $dm->page_name = $da['page_name'];
    $dm->user_guide = $da['user_guide'];
    // Log::info('user_guide for $pagename', $uguide->toArray());
    $dm->save();
		// return $this->getUserGuide($da['page_name']);
		return ['status' => "OK"];
	}
	public function saveUserGuide(Request $da) { Log:info("saveUserGuide", $da->toArray());
    $dm = new UserGuide();
    $dm->page_name = $da['page_name'];
    $dm->user_guide = $da['user_guide'];
    // Log::info('user_guide for $pagename', $uguide->toArray());
    $dm->save();
		return ['status' => "OK"];
	}
	// public function getUserGuideId($pagename) { Log:info("getUserGuideId for $pagename");
	// 	$id = UserGuide::where([['status', 'A'], ['page_name', $pagename]])->select('id')->value('id');
	// 	// $id = UserGuide::where([['status', 'A'], ['page_name', $pagename]])->value('id');
	// 	return ['userGuideId' => $id, 'status' => 'OK'];
	// }
	public function getUserGuide($pagename) { // Log:info("getUserGuide for $pagename");
    $dx = UserGuide::where([['status', 'A'], ['page_name', $pagename]])->select('id', 'page_name', 'user_guide')->get();
    Log::info("user_guide for $pagename", $dx->toArray());
    $id = 0;
    $pagename = '';
    $userguide = '';
    if (count($dx) == 1) {
      $id = $dx[0]->id;
      $pagename = $dx[0]->page_name;
      $userguide = $dx[0]->user_guide;
    } else if (count($dx) === 0) {
      $userguide = '<div class="text-h6 text-green-9">User Guide TO BE ADDED</div>';
    } else {
      $userguide = '<div class="text-h6 text-red-9">Something WRONG(There could be more than 1 rows), Please contact Admin</div>';
    }
		return ['id' => $id, 'pagename' => $pagename, 'userguide' => $userguide, 'status' => "OK"];
	}
	public function updTeamMatchTplayer(Request $da) { Log:info('updTeamMatchTplayer', $da->toArray());
		$this->logPage($da['name']);
		$id = $da['id'];
		$tscore = $da['tscore']; //== 0 ? null : $da['tscore'];
		$tplayer = Tplayer::find($id);
		$tplayer->tournament_id = $da['tmntId'];
		$tplayer->gross_score = $da['pscore'];
		$tplayer->game_id = $da['game_id'];
		$tplayer->tnum = $da['team'];
		$tplayer->captain = $tscore == 0 ? null : $tscore;
		$tplayer->grp = $da['grp'];
		$tplayer->player_id = $da['player_id'];
		$tplayer->update();
		return ['status' => "OK"];
	}
	public function addPGCTplayer(Request $da) { Log:info('addPGCTplayers', $da->toArray());
		$tid = 0;
		if (!array_key_exists('id', $da->toArray())) {
			$dm = new Tplayer($da->toArray());
			$dm->save();
			$tid = $dm['id'];
			Log::info("addPGCTplayer add", $dm->toArray());
			// Log::info("addPGCTplayer $dm[id]");
		} else {
			$dx = Tplayer::find($da['id']);
			Log::info("addPGCTplayer upd", $dx->toArray());
			$dx->grp = $da['grp'];
			$dx->pos = $da['pos'];
			$dx->note = $da['note'];
			$dx->tnum = $da['tnum'];
			$dx->year = $da['year'];
			$dx->save();
			$tid = $da['id'];
			if ($da['pos'] == 'G5' || $da['pos'] == 'P5' || $da['pos'] == 'CP') {
				$tmntId = $da['tournament_id'];
				$dt = Tournament::find($tmntId);
				$dt->score_done = $da['player_id'];
				$dt->update();
			}
		}
		return ['tid' => $tid, 'status' => "OK"];
	}
	public function upsertTplayers(Request $da) { Log:info('upsertTplayers', $da->toArray());
		$tmntId = -1;
		$vals = [];
		foreach($da->toArray() as $d) {
			$tmntId = $d['tmntId'];
			$captain = $d['captain'] == 1 ? 1 : null;
			$grp = $d['grp'] > 0 ? $d['grp'] : null;
			$x = [ 'id' => $d['id'], 'tournament_id' => $tmntId, 'year' => $d['year'], 'game_id' => $d['gameId'], 'player_id' => $d['playerId'], 'player' => $d['fullname'], 'grp' => $grp, 'captain' => $captain ];
			array_push($vals, $x);
		}
		Log::info('upsert vals', $vals);
		Tplayer::upsert(
			$vals,
			['id'],
			['grp', 'captain', 'year'],
		);
		return $this->getTplayers($tmntId);
	}
	public function XXXupsertTplayers(Request $da) { Log:info('upsertTplayers');
		$vals = [];
		foreach($da->toArray() as $d) {
			$x = [
				'tournament_id' => $d['tmntId'],
				'player_id' => $d['player_id'],
				'game_id' => $d['gameId'],
				// 'year' => $d['year'],
				'grp' => $d['grp'],
				'captain' => $d['captain']
			];
			array_push($vals, $x);
		}
		Tplayer::upsert(
			$vals,
			['grp', 'captain', 'game_id'],
			['grp', 'captain']
		);
		return $this->getTplayers($d['tmntId']);
	}
	public function XX_upsertTplayers(Request $da) { Log:info('upsertTplayers');
		$vals = [];
		foreach($da->toArray() as $d) {
			$x = [
				'tournament_id' => $d['tmntId'],
				'player_id' => $d['player_id'],
				'game_id' => $d['gameId'],
				'year' => $d['year'],
				'grp' => $d['grp'],
				'captain' => $d['captain']
			];
			array_push($vals, $x);
		}
		Tplayer::upsert(
			$vals,
			['tournament_id', 'player_id'],
			['grp', 'captain', 'year', 'game_id']
		);
		return $this->getTplayers($d['tmntId']);
	}
	// public function getPlayers(Request $da) { Log:info('getPlayers');
	public function getPlayers() { Log:info('getPlayers');
		// $p = Player::where('status', 'A')->select('id as player_id', 'gender', CONCAT('lastname', ', ', 'firstname') as fullname)->get();
		// $lst = DB::select("select id as player_id, gender, null as captain, null as grp, CONCAT(lastname, ', ', firstname) as name from players where status='A'");
		$lst = DB::select("select id as player_id, gender, null as captain, null as grp, lastname, firstname, CONCAT(lastname, ', ', firstname) as name from players where status='A'");
		return ['players' => $lst, 'status' => "OK"];
	}
	public function addTplayers(Request $da) { Log:info('addTplayers', $da->toArray());
		$tmntId = $da->tmntId;
		foreach($da->players as $d) {
		// foreach($da->tplayers as $d) {
			$x = new Tplayer($d);
			$x->save();
		}
		return $this->getTplayers($tmntId);
	}
	public function setTmntNumGroups(Request $da) { Log:info('setTmntNumGroups', $da->toArray());
		$dm = Tournament::find($da['tmntId']);
		$dm->num_groups = $da['numGroups'];
		$dm->update();
		return ['numGroups' => $da['numGroups'], 'status' => "OK"];
	}
	public function addUnsignedTeamMatchPlayers(Request $da) { Log::info("addUnsignedTeamMatchPlayer", $da->toArray());
		$this->logPage('add ' . count($da->toArray()) . ' players');
		$tmntId = 0;
		$gameId = 0;
		foreach($da->toArray() as $d) {
			$tmntId = $d['tournament_id'];
			$gameId = $d['game_id'];
			$matchDate = $d['matchDate'];
			// Log::info('addUnsignedTeamMatchPlayer', [$tmntId, $gameId, $matchDate]);
			$dm = new Tplayer($d); 
			// Log::info('-addUnsignedTeamMatchPlayer-', $dm->toArray());
			$dm->save();
			if ($gameId > 10) {
				$dx = Player::find($dm->player_id);
				if (stripos($dx->team, strval($gameId)) !== false) {
					// Log::info("stripos", [stripos($dx->team, strval($gameId)), stripos($dx->team, $gameId)]);
					continue;
				}
				if ($dx->team == null) {
					$dx->team = $gameId;
				} else {
					$dx->team .= ",$gameId";
				}
				$dx->save();
			}
		}
		if ($gameId < 10) return $this->getPGCGamePlayers($tmntId, $gameId, substr($matchDate, 0, 4));
		else return $this->getTeamMatchPlayers($tmntId, $gameId, $matchDate);
	}
	public function getPlayerGameScores($playerId, $gameId) {
		$lst = DB::select('CALL get_player_game_scores(?, ?)', [$playerId, $gameId]);
		$back_n_days = DB::select('select double_value from constant where id = "back_n_days"');
		return ['lst' => $lst, 'back_n_days' => $back_n_days[0], 'status' => 'OK'];
	}
	public function getCandidatePlayers($tmntId, $gameId) {
		$lst = DB::select('CALL get_candidate_players(?, ?)', [$tmntId, $gameId]);
		// $allList = DB::select('select gender, id as player_id, concat(lastname, ", ", firstname) as player from players where status = "A" order by player');
		// return ['canList' => $canList, 'allList' => $allList, 'status' => 'OK'];
		return ['lst' => $lst, 'status' => 'OK'];
	}
	public function delTeamMatchPlayer($id, $tmntId, $gameId, $matchDate) {
		$dm = Tplayer::find($id);
		$this->logPage($dm->name);
		$dm->delete();
		// if ($gameId == 13) return $this->getTeamMatchPlayers($tmntId, $gameId, $matchDate);
		if (in_array($gameId, [13,14,16])) return $this->getTeamMatchPlayers($tmntId, $gameId, $matchDate); // for TeamMatch
		else if ($gameId < 10) return $this->getPGCGamePlayers($tmntId, $gameId, substr($matchDate, 0, 4));
		else return ['status' => "OK"];
	}
	public function addTeamMatchPlayer(Request $da) { //Log::info('addTeamMatchPlayer', $da->toArray());
		$dm = new Tplayer($da->toArray());
		$dm->save();
	}
	public function getTournaments($gameId, $tmntId=0) { Log::info("by gameId -fn-getTournaments/$gameId", [__file__, 'line='.__line__]);
		// $this->logPage("for game $gameId");
		$tmgames = Tournament::where([['game_id', $gameId], ['status', 'A']])
		->select('id', 'game_id', 'start_at', 'teetime_gap', 'course_id', 'note', 'fees', 'mens_tee_id', 'lady_tee_id')
		->with('course:id,name')
		->with('lteebox:id,par,teebox,rating,slope,yardage')
		->with('mteebox:id,par,teebox,rating,slope,yardage')
		->orderBy('start_at', 'desc')->get();

		$kjNewPlayer = KjNewPlayer::where([['player_id', null], ['status', 'A']])->select('match_player_id as id', 'game_date', 'firstname', 'lastname')->get();
		// Log::info("match list for gameId $gameId", [$tmgames]);
		// if ($gameId == 14) {
			// 	[$lastHcapDate, $lastn, $firstn] = $this->loadKJsFile($gameId);
			// 	Log::info("lastHcapDate=$lastHcapDate");
			// 	if ($lastHcapDate == 'ADD_NEW_SIM_PLAYER') {
			// 		$aliases = MatchPlayer::where('status', 'A')->select('alias')->orderBy('alias')->get('alias');
			// 		// Log::info("XXX$lastn", ['firstname' => $firstn, 'lastname' => $lastn, 'aliases'=> $aliases, 'status' => "ADD_NEW_SIM_PLAYER"]);
			// 		// return [ 'firstname' => $firstn, 'lastname' => $lastn, 'aliases'=> $aliases, 'status1' => "ADD_NEW_SIM_PLAYER", 'status' => "OK"];
			// 		return [ 'firstname' => $firstn, 'lastname' => $lastn, 'aliases'=> $aliases, 'status' => "ADD_NEW_SIM_PLAYER"];
			// 	}
			// 	// $lastHcapDate = '2022-11-24';
			// 	$matchPlayers = DB::select("select * from match_player_handicaps_view where last_handicap_date = '$lastHcapDate' order by handicap");
			// 	return ['matchPlayers' => $matchPlayers, 'matches' => $tmgames, 'status' => "OK"];
			// }
			// if ($gameId == 13) {
			// 	// $gameCount = 3;
			// 	// $aliases = DB::select('CALL get_active_players(?, ?)', [$gameId, $gameCount]);
			// 	$aliases = DB::select("select * from alias_handicap_JZ_view order by handicap");
			// 	// Log::info("gameId=$gameId, gameCount=$gameCount", $aliases);
			// 	return ['matches' => $tmgames, 'aliases' => $aliases, 'status' => "OK"];
			// } else if ($gameId == 14) {
			// 	$lasthdate = '2022-12-04';
			// 	// $aliases = DB::select("select 'player_id', 'alias', 'handicap', 'name' from alias_handicap_KJ_view where last_handicap_date = '$lasthdate' order by handicap");
			// 	$aliases = DB::select("select * from alias_handicap_KJ_view where last_handicap_date = '$lasthdate' order by handicap");
			// 	// Log::info("gameId=$gameId aliases=", $aliases);
			// 	return ['matches' => $tmgames, 'aliases' => $aliases, 'status' => "OK"];
			// }
			return ['matches' => $tmgames, 'tmntId' => $tmntId, 'kjNewPlayer' => $kjNewPlayer, 'status' => "OK"];
		}
		public function getTournamentByTmntId($tmntId, $gameId) { Log::info("by tmntId -fn-getTournamentByTmntId/$tmntId", [__file__, 'line='.__line__]);
			$this->logPage("for match game $tmntId");
			$matchGame = Tournament::where([['id', $tmntId], ['status', 'A']])
				->select('id', 'game_id', 'start_at', 'teetime_gap', 'course_id', 'note', 'fees', 'mens_tee_id', 'lady_tee_id')
				->with('course:id,name')
				->with('lteebox:id,par,teebox,rating,slope,yardage')
				->with('mteebox:id,par,teebox,rating,slope,yardage')->get();
				// ->orderBy('start_at', 'desc')->get();

				if ($gameId == 14) {
					$kjNewPlayer = KjNewPlayer::where([['player_id', null], ['status', 'A']])->select('id', 'game_date', 'firstname', 'lastname')->get();
					return ['matchGame' => $matchGame[0], 'kjNewPlayer' => $kjNewPlayer, 'status' => "OK"];
				} else return ['matchGame' => $matchGame[0], 'status' => "OK"];
			}
			public function delRound($scoreId) {
				try {
					Score::destroy($scoreId);
					return "OK";
				} catch(MyException $e) {
					return "FAILED " + $e;
				}
			}
			public function getPlayerCount() { // Log::info('getPlayerCount', [Auth::user()]);
				$mcnt = Player::where([['status', 'A'], ['gender', 'M']])->count();
				$fcnt = Player::where([['status', 'A'], ['gender', 'F']])->count();
				return ['status' => "OK", 'mcnt' => $mcnt, 'fcnt' => $fcnt];
			}
			// public function getPlayerCount() { // Log::info('getPlayerCount', [Auth::user()]);
			// 	$players = Player::where('status', 'A')->select('gender')->get();
			// 	// Log::info('players', $players->toArray());
			// 	return ['status' => "OK", 'tplayers' => $players->toArray()];
			// }
			public function XXXsaveTeamMatch(Request $da) { //Log::info('saveTeamMatch', $da->toArray());
				foreach($da->toArray() as $d) {
					$dm = Tplayer::find($d['id']);
					$dm->tournament_id = $d['tmntId'];
					$dm->player = $d['player'];
			$dm->grp = $d['grp'];
			$dm->tnum = $d['team'];
			$dm->captain = $d['gscore'];
			$dm->gross_score = $d['score'];
			try {
				$dm->save();
			} catch(MyException $e) {
				return "FAILED " + $e;
			}
		}
		return ['status' => "OK"];
	}
	public function saveGrouping(Request $da) { Log::info('saveGrouping', $da->toArray());
		foreach($da->toArray() as $d) {
			$tmntId = $d['tmntId'];
			$playerId = $d['player_id'];
			$dm = Tplayer::firstOrNew(  // update or create new record
				[ 'tournament_id' => $tmntId, 'player_id' => $playerId ],
				[ 'player' => $d['fullname'], 'grp' => $d['grp'], 'captain' => $d['captain'], 'year' => $d['year'], 'game_id' => $d['gameId'] ],
			);

			// $dm = Tplayer::where([ ['tournament_id', $tmntId], ['player_id', $playerId] ])->get();
			// if ($dm)
			// $dm->player = $d['fullname'];
			// $dm->grp = $d['grp'];
			// $dm->captain = $d['captain'];
			// $dm->year = $d['year'];
			// $dm->game_id = $d['gameId'];
			// $dm->tournament_id = $d['tmntId'];
			// $dm->player_id = $d['player_id'];
			try {
				$dm->save();
			} catch(MyException $e) {
				return "FAILED " + $e;
			}
		}
		return "OK";
	}
	public function saveGrouping_XXX(Request $da) { // Log::info('saveGrouping', $da->toArray());
		foreach($da->toArray() as $d) {
			$dm = Tplayer::find($d['id']);
			$dm->player = $d['fullname'];
			$dm->grp = $d['grp'];
			$dm->captain = $d['captain'];
			try {
				$dm->save();
			} catch(MyException $e) {
				return "FAILED " + $e;
			}
		}
		return "OK";
	}
	public function getScoreId(Request $da) { Log::info('getScoreId', [$da->playerId, $da->courseId, $da->teetime]);
		$playerId = $da->playerId;
		$courseId = $da->courseId;
		$teetime = $da->teetime;
		$dx = Score::where([ ['player_id', $playerId], ['teetime', $teetime], ['course_id', $courseId] ])->select('id as scoreId')->get(); //Log::info($dx[0]['scoreId']);
		if (count($dx) == 1) {
			return [ 'scoreId' => $dx[0]->scoreId, 'status' => "OK" ];
		} else {
			return [ 'scoreId' => null, 'status' => "NoId" ];
			// return [ 'scoreid' => -1, 'status' => "can't find scoreId for " . $teetime ];
		}
	}
	public function getScore(Request $da) { Log::info("getScore playerId=$da->playerId, scoreId=$da->scoreId");
		$score = DB::select("CALL get_scores(?, ?)", [ $da['playerId'], $da['scoreId'] ])[0]; //Log::info($scores);
		// $score = DB::select("CALL get_scores(?, ?)", [ $da['playerId'], $da['scoreId'] ]); Log::info($scorex);
		// $score->pars = CoursePar::where('courseId', $score->courseId)->select('*')->get()[0];
		// $score->hcaps = CourseHandicap::where('courseId', $score->courseId)->select('*')->get()[0];
		// $score->yards = $this->getCourseYardage($score->courseId, $score->teebox);
		// Log::info(Collect($score));
		return ['strokes' => $score, 'status' => "OK"];
	}
	public function getCourseInfo($tmntId, $courseId, $teeboxId) { //Log::info("getCourseInfo($courseId, $teeboxId)");
		$courseName = Course::where([['id', $courseId], ['status', 'A']])->select('name')->value('name');
		$parsx = CoursePar::where('course_id', $courseId)->select('h1','h2','h3','h4','h5','h6','h7','h8','h9','h10','h11','h12','h13','h14','h15','h16','h17','h18')->get();
		if (count($parsx) !==1) {
			$status = "FAILED pars count !=1";
			return ['status' => $status];
		} else $pars = $parsx[0];
		$hcapsx = CourseHandicap::where('course_id', $courseId)->select('p1','p2','p3','p4','p5','p6','p7','p8','p9','p10','p11','p12','p13','p14','p15','p16','p17','p18')->get();
		if (count($hcapsx) !==1) {
			$status = "FAILED hcaps count !=1";
			return ['status' => $status];
		} else $hcaps = $hcapsx[0];
		$yardsx = CourseYard::where([['course_id',$courseId],['teebox_id',$teeboxId],['status','A']])->select('y1','y2','y3','y4','y5','y6','y7','y8','y9','y10','y11','y12','y13','y14','y15','y16','y17','y18')->get();
		// $yardsx = CourseYard::where([['courseId',$courseId],['teeboxId',269],['status','A']])->select('y1','y2','y3','y4','y5','y6','y7','y8','y9','y10','y11','y12','y13','y14','y15','y16','y17','y18')->get();
		Log::info("get GolfController/CourseYard::where(cource_id=$courseId, teebox_id=$teeboxId", [__line__]);
		if (count($yardsx) !==1) {
			$status = "FAILED yards count !=1";
			Log::info("get CourseYard::where(cource_id=$courseId, teebox_id=$teeboxId, status=$status", [__line__]);
			return ['status' => $status];
		} else $yards = $yardsx[0];
		$rsyx = CourseTee::where([['course_id', $courseId], ['id', $teeboxId], ['status', 'A']])->select('rating', 'slope', 'yardage', 'par')->get();
		// $rsyx = CourseTee::where([['course_id', $courseId], ['id', 269], ['status', 'A']])->select('rating', 'slope', 'yardage')->get();
		if (count($rsyx) !==1) {
			$status = "FAILED rsy count !=1";
			return ['status' => $status];
		} else $rsy = $rsyx[0];
		$rating  = $rsy->rating;
		$slope   = $rsy->slope;
		$yardage = $rsy->yardage;
		$par = $rsy->par;
		// Log::info("rating=$rating slope=$slope yardage=$yardage", $xsr->toArray());
		return ['tmntId' => $tmntId, 'name' => $courseName, 'courseId' => $courseId, 'teeboxId' => $teeboxId, 'pars' => $pars, 'yards'=> $yards, 'hcaps' => $hcaps, 'par' => $par, 'rating' => $rating, 'slope' => $slope, 'yardage' => $yardage, 'status' => "OK" ];
	}
	public function getScores($playerId) {
		// date_default_timezone_set("America/New_York");
		// $now = Date("Y-m-d H:i:s", time());
		// $scores = Score::where([ ['PlayerId', $playerId], ['teetime', '<', $now] ])->orderBy('teetime', 'desc')->select('*')->get();
		$scores = DB::select("CALL get_scores(?, 0)", [$playerId]); //Log::info($scores);
		foreach($scores as $s) {
			$s->pars = CoursePar::where('course_id', $s->courseId)->select('*')->get()[0];
			$s->hcaps = CourseHandicap::where('course_id', $s->courseId)->select('*')->get()[0];
			$s->yards = $this->getCourseYardage($s->courseId, $s->teeboxId, $s->teebox);
		}
		// return Collect($scores);
		return [ 'scores' => $scores, 'playerId' => $playerId, 'status' => "OK" ];
	}
	public function getPlayedRoundList($playerId) {
		date_default_timezone_set("America/New_York");
		$now = Date("Y-m-d H:i:s", time());
		return Score::where([ ['playerId', $playerId], ['teetime', '<=', $now] ])->select('id', 'course_id', 'teebox_id', 'teetime', 'note')->orderBy('teetime', 'asc')->get();
	}
	public function getRoundList($playerId) {
		// date_default_timezone_set("America/New_York");
		// $now = Date("Y-m-d H:i:s", time());
		// return Score::where([ ['playerId', $playerId], ['teetime', '>', $now] ])->select('id', 'courseId', 'teeboxId', 'teetime', 'note')->orderBy('teetime', 'asc')->get();
		$roundList = DB::select("CALL get_open_rounds(?)", [$playerId]);
		// Log::info("RoundList for $playerId", $roundList);
		return ['roundList' => $roundList, 'status' => "OK"];
	}
	private function setF9scores($game) {
		$score = [];
		$score[] = $game['h1'];
		$score[] = $game['h2'];
		$score[] = $game['h3'];
		$score[] = $game['h4'];
		$score[] = $game['h5'];
		$score[] = $game['h6'];
		$score[] = $game['h7'];
		$score[] = $game['h8'];
		$score[] = $game['h9'];
		return $score;
	}
	private function setB9scores($game) {
		$score = [];
		$score[] = $game['h10'];
		$score[] = $game['h11'];
		$score[] = $game['h12'];
		$score[] = $game['h13'];
		$score[] = $game['h14'];
		$score[] = $game['h15'];
		$score[] = $game['h16'];
		$score[] = $game['h17'];
		$score[] = $game['h18'];
		return $score;
	}
	public function updTotalScoreAndNote(Request $da) {
		$scoreId = $da['scoreId'];
		$dm = Score::find($scoreId);
		try {
			$dm->id = $scoreId;
			$dm->note = $da['note'];
			$dm->totalscore = $da['totalScore'];
			$dm->save();
			return "OK";
		} catch(MyException $e) {
			return "FAILED " . $e;
		}
	}
	public function XXaddScoreRecord(Request $da) {
		$player = $da;                     Log::info('addScoreRecors', $player->toArray());
		$scoreId = $player['scoreId'];
		$courseId = $player['courseId'];
		$teebox = $player['teebox'];
		$teeboxId = $player['teeboxId'];
		$playerId = $player['id'];
		$teetime = $player['teetime'];
		$note = $player['note'];
		$tournamentId = isset($player['tournamentId']) ? $player['tournamentId'] : null;
		if ($scoreId > 0) $dm = Score::find($scoreId);
		else $dm = new Score;

		$dm->courseId = $courseId;
		$dm->teeboxId = $teeboxId;
		$dm->playerId = $playerId;
		$dm->teetime = $teetime;
		$dm->tournamentId = $tournamentId;
		$dm->note = $note;
		$dm->status = 'O';
		$dm->totalScore = 0;
		try {
			$dm->save();
			$scoreId = $dm->id;
			$strokes = DB::select("CALL get_scores(?, ?)", [$playerId, $scoreId])[0];
		} catch(MyException $e) {
			return "FAILED " . $e;
		}
		// Log::info('addScoreRecors XXX', $player->toArray());
		return [ 'strokes' => Collect($strokes), 'status' => 'OK' ];
	}
	// public function addScoreRecord(Request $da) {
	// 	$player = $da;                    // Log::info('addScoreRecors', $player->toArray());
	// 	$scoreId = $player['scoreId'];
	// 	$courseId = $player['courseId'];
	// 	$teebox = $player['teebox'];
	// 	$teeboxId = $player['teeboxId'];
	// 	$playerId = $player['id'];
	// 	$teetime = $player['teetime'];
	// 	$note = $player['note'];
	// 	$tournamentId = isset($player['tournamentId']) ? $player['tournamentId'] : null;
	// 	if ($scoreId > 0) $dm = Score::find($scoreId);
	// 	else $dm = new Score;

	// 	$dm->courseId = $courseId;
	// 	$dm->teeboxId = $teeboxId;
	// 	$dm->playerId = $playerId;
	// 	$dm->teetime = $teetime;
	// 	$dm->tournamentId = $tournamentId;
	// 	$dm->note = $note;
	// 	$dm->status = 'O';
	// 	$dm->totalScore = 0;
	// 	try {
	// 		$dm->save();
	// 		$player['scoreId'] = $dm->id;
	// 		$player['holes'] = $this->getHoleInfo($courseId);
	// 		$player['hcaps'] = $this->getHandicaps($courseId);
	// 		$player['yards'] = $this->getYards($courseId, $teebox);
	// 		if ($player['yards'] == null) { // no yardage for this teebox of the course
	// 			$player['yards'] = ['y1'=>401, 'y2'=>402, 'y3'=>403, 'y4'=>404, 'y5'=>405, 'y6'=>406, 'y7'=>407, 'y8'=>408, 'y9'=>409,
	// 				'y10'=>410, 'y11'=>411, 'y12'=>412, 'y13'=>413, 'y14'=>414, 'y15'=>415, 'y16'=>416, 'y17'=>417, 'y18'=>418];
	// 		}
	// 		$player['f9scores'] = $this->setF9scores($dm);
	// 		$player['f9total'] = array_sum($player['f9scores']);;
	// 		$player['b9scores'] = $this->setB9scores($dm);
	// 		$player['b9total'] = array_sum($player['b9scores']);;
	// 	} catch(MyException $e) {
	// 		return "FAILED " . $e;
	// 	}
	// 	// Log::info('addScoreRecors XXX', $player->toArray());
	// 	return [ 'player' => Collect($player), 'status' => 'OK' ];
	// }
	public function getCourseHandicaps($courseId) {
		return CourseHandicap::where([['status', 'A'], ['course_id', $courseId]])->select('*')->get()[0];
	}
	private function getHandicaps($courseId) {
		$cp = CourseHandicap::where([['status', 'A'], ['course_id', $courseId]])->select('*')->get()[0];
		return $cp;
		$player['hcaps'][] = $cp['p1'];
		$player['hcaps'][] = $cp['p2'];
		$player['hcaps'][] = $cp['p3'];
		$player['hcaps'][] = $cp['p4'];
		$player['hcaps'][] = $cp['p5'];
		$player['hcaps'][] = $cp['p6'];
		$player['hcaps'][] = $cp['p7'];
		$player['hcaps'][] = $cp['p8'];
		$player['hcaps'][] = $cp['p9'];
		$player['hcaps'][] = $cp['p10'];
		$player['hcaps'][] = $cp['p11'];
		$player['hcaps'][] = $cp['p12'];
		$player['hcaps'][] = $cp['p13'];
		$player['hcaps'][] = $cp['p14'];
		$player['hcaps'][] = $cp['p15'];
		$player['hcaps'][] = $cp['p16'];
		$player['hcaps'][] = $cp['p17'];
		$player['hcaps'][] = $cp['p18'];
		return $player;
	}
	public function updCourseYardage(Request $da) { Log::info("updCourseYardage", $da->toArray());
		$id = $da['id'];
		$yard = $da['yard'];
		$yx = $da['yx'];
		// $teeboxId = $da['teeboxId'];
		$dm = CourseYard::find($id);
		$dm->$yx = $yard;
		// $dm->teeboxId = $teeboxId;
		$dm->update();
		return ['status' => "OK"];
	}
	public function getCourseYardages(Request $d) { Log::info('getCourseYard() in', $d->toArray());
		$courseId = $d->courseId;
		$mtee = $d->mtee;
		$ltee = $d->ltee;
		$myards = CourseYard::where([['status', 'A'], ['course_id', $courseId], ['teebox', $mtee]])->select('*')->get();
		if (!is_null($ltee)) $lyards = CourseYard::where([['status', 'A'], ['course_id', $courseId], ['teebox', $ltee]])->select('*')->get();
		// Log::info('courseYardages', [$myards->toArray(), $lyards->toArray()]);
		return [ 'myards' => $myards->toArray()[0], 'lyards' => is_null($ltee) ? null : $lyards->toArray()[0], 'status' => "OK" ];
		// return [ 'myards' => $myards->toArray()[0], 'lyards' => $lyards->toArray()[0], 'status' => "OK" ];
	}
	private function getYards($courseId, $teebox) {
		$cpx = CourseYard::where([['status', 'A'], ['course_id', $courseId], ['teebox', $teebox]])->select('*')->get();
		if (count($cpx) == 1) {
			return $cpx[0];
		} else {
			return null;
		}
	}
	private function getHoleInfo($courseId) {
		$cp = CoursePar::where([['status', 'A'], ['course_id', $courseId]])->select('*')->get()[0];
		return $cp;
		$player['holes'][] = $cp['h1'];
		$player['holes'][] = $cp['h2'];
		$player['holes'][] = $cp['h3'];
		$player['holes'][] = $cp['h4'];
		$player['holes'][] = $cp['h5'];
		$player['holes'][] = $cp['h6'];
		$player['holes'][] = $cp['h7'];
		$player['holes'][] = $cp['h8'];
		$player['holes'][] = $cp['h9'];
		$player['holes'][] = $cp['h10'];
		$player['holes'][] = $cp['h11'];
		$player['holes'][] = $cp['h12'];
		$player['holes'][] = $cp['h13'];
		$player['holes'][] = $cp['h14'];
		$player['holes'][] = $cp['h15'];
		$player['holes'][] = $cp['h16'];
		$player['holes'][] = $cp['h17'];
		$player['holes'][] = $cp['h18'];
		return $player;
	}
	public function getMatchedCourses($search) {
		$da = Course::where([ ['status', 'A'], [ 'name', 'like', "%$search%" ] ])->select('id', 'name')->get();
		return $da;
	}
	public function getCourseDetails($courseId) { Log::info('getCourseDetails courseId=$courseId');
		if ($courseId == -1) $courseId = 1;
		$da = Course::where([ ['status', 'A'], [ 'id', $courseId ] ])->select('id', 'name', 'address', 'phone', 'website')->get();
		if (count($da) == 0) return ['course' => null, 'status' => "OK"];
		$da = $da[0];
		$course = ['id' => $da->id, 'name' => $da->name, 'address' => $da->address, 'phone' => $da->phone, 'website' => $da->website ];
		$trys = CourseTee::where([['status', 'A'], [ 'course_id', $courseId ]])->select('id','course_id','teebox','par','rating','yardage','slope')->orderBy('yardage','desc')->get();
		if (count($trys) == 0) $trys[0] = ['id' => 0, 'courseId' => $courseId, 'teebox' => 'color', 'par' => '72', 'rating' =>'77.7', 'yardage' => '7000', 'slope' => '177'];
		$course['trys'] = $trys;
		$teeboxes = CourseTee::distinct()->select('teebox')->orderBy('teebox','asc')->get(); //Log::info($teeboxes);
		$teenames = []; foreach($teeboxes as $t) $teenames[] = $t->teebox;
		$holes = CoursePar::where([ ['status', 'A'], [ 'course_id', $courseId ] ])->select('*')->get();
		if (count($holes) == 0) {
			$par = new CoursePar;
			$par->courseId = $courseId;
			$par->h1 = 4;
			$par->h2 = 4;
			$par->h3 = 4;
			$par->h4 = 4;
			$par->h5 = 4;
			$par->h6 = 4;
			$par->h7 = 4;
			$par->h8 = 4;
			$par->h9 = 4;
			$par->h10 = 4;
			$par->h11 = 4;
			$par->h12 = 4;
			$par->h13 = 4;
			$par->h14 = 4;
			$par->h15 = 4;
			$par->h16 = 4;
			$par->h17 = 4;
			$par->h18 = 4;
			$par->save();
			$holes = CoursePar::where([ ['status', 'A'], [ 'course_od', $courseId ] ])->select('*')->get()[0];
		} else $holes = $holes[0];
		$course['holes'] = $holes;
		$hcaps = CourseHandicap::where([ ['status', 'A'], [ 'course_id', $courseId ] ])->select('*')->get();
		if (count($hcaps) == 0) {
			$cap = new CourseHandicap;
			$cap->courseId = $courseId;
			$cap->p1 = 1;
			$cap->p2 = 2;
			$cap->p3 = 3;
			$cap->p4 = 4;
			$cap->p5 = 5;
			$cap->p6 = 6;
			$cap->p7 = 7;
			$cap->p8 = 8;
			$cap->p9 = 9;
			$cap->p10 = 10;
			$cap->p11 = 11;
			$cap->p12 = 12;
			$cap->p13 = 13;
			$cap->p14 = 14;
			$cap->p15 = 15;
			$cap->p16 = 16;
			$cap->p17 = 17;
			$cap->p18 = 18;
			$cap->save();
			$hcaps = CourseHandicap::where([ ['status', 'A'], [ 'course_id', $courseId ] ])->select('*')->get()[0];
		} else $hcaps = $hcaps[0];
		$course['hcaps'] = $hcaps; Log::info("getCourseYardage($courseId)");
		$course['yards'] = $this->getCourseYardage($courseId, $trys[0]['id'], $trys[0]['teebox']);
		$course['tees'] = $teenames; //Log::info($course);
		return ['course' => $course, 'status' => "OK"];
	}
	function getCourseYardage($courseId, $teeboxId, $teebox) {
		$yards = CourseYard::where([ ['status', 'A'], ['course_id', $courseId], ['teebox_id', $teeboxId] ])->select('*')->get();
		if (count($yards) == 1 and $yards[0]['y1'] > 50) return $yards[0];
		else if (count($yards) == 1 and $yards[0]['y1'] <= 50) return $this->getFakeYardageForUpdate($teebox, $yards[0]);
		$yards = new CourseYard;
		$yards->course_id = $courseId;
		$yards->teebox = $teebox;
		$yards->teebox_id = $teeboxId;
		$yards->y1 = '1';
		$yards->y2 = '2';
		$yards->y3 = '3';
		$yards->y4 = '4';
		$yards->y5 = '5';
		$yards->y6 = '6';
		$yards->y7 = '7';
		$yards->y8 = '8';
		$yards->y9 = '9';
		$yards->y10 = '10';
		$yards->y11 = '11';
		$yards->y12 = '12';
		$yards->y13 = '13';
		$yards->y14 = '14';
		$yards->y15 = '15';
		$yards->y16 = '16';
		$yards->y17 = '17';
		$yards->y18 = '18';
		$yards->save(); // update later by user from frontend
		$yards = CourseYard::where([ ['status', 'A'], [ 'course_id', $courseId ], [ 'teebox_id', $teeboxId] ])->select('*')->get();
		return $this->getFakeYardageForUpdate($teebox, $yards[0]);
	}
	private function XXXgetFakeYards() {
		$yards['y1'] = 'y1';
		$yards['y2'] = 'y2';
		$yards['y3'] = 'y3';
		$yards['y4'] = 'y4';
		$yards['y5'] = 'y5';
		$yards['y6'] = 'y6';
		$yards['y7'] = 'y7';
		$yards['y8'] = 'y8';
		$yards['y9'] = 'y9';
		$yards['y10'] = 'y10';
		$yards['y11'] = 'y11';
		$yards['y12'] = 'y12';
		$yards['y13'] = 'y13';
		$yards['y14'] = 'y14';
		$yards['y15'] = 'y15';
		$yards['y16'] = 'y16';
		$yards['y17'] = 'y17';
		$yards['y18'] = 'y18';
		return $yards;
	}
	private function getFakeYardageForUpdate($teebox, $yards) {
		$yards['y1'] = $teebox[0] . $yards['y1'];
		$yards['y2'] = $teebox[0] . $yards['y2'];
		$yards['y3'] = $teebox[0] . $yards['y3'];
		$yards['y4'] = $teebox[0] . $yards['y4'];
		$yards['y5'] = $teebox[0] . $yards['y5'];
		$yards['y6'] = $teebox[0] . $yards['y6'];
		$yards['y7'] = $teebox[0] . $yards['y7'];
		$yards['y8'] = $teebox[0] . $yards['y8'];
		$yards['y9'] = $teebox[0] . $yards['y9'];
		$yards['y10'] = $teebox[0] . $yards['y10'];
		$yards['y11'] = $teebox[0] . $yards['y11'];
		$yards['y12'] = $teebox[0] . $yards['y12'];
		$yards['y13'] = $teebox[0] . $yards['y13'];
		$yards['y14'] = $teebox[0] . $yards['y14'];
		$yards['y15'] = $teebox[0] . $yards['y15'];
		$yards['y16'] = $teebox[0] . $yards['y16'];
		$yards['y17'] = $teebox[0] . $yards['y17'];
		$yards['y18'] = $teebox[0] . $yards['y18'];
		return $yards;
	}
	private function getCourseTee($t) {
		$courseTee = new CourseTee;
		$courseTee->teebox = $t['teebox'];
		$courseTee->yardage = $t['yardage'];
		$courseTee->rating = $t['rating'];
		$courseTee->slope = $t['slope'];
		return $courseTee;
	}
	private function getHolePars($h) {
		$hp = new CoursePar;
		$hp->h1 = $h['h1'];
		$hp->h2 = $h['h2'];
		$hp->h3 = $h['h3'];
		$hp->h4 = $h['h4'];
		$hp->h5 = $h['h5'];
		$hp->h6 = $h['h6'];
		$hp->h7 = $h['h7'];
		$hp->h8 = $h['h8'];
		$hp->h9 = $h['h9'];
		$hp->h10 = $h['h10'];
		$hp->h11 = $h['h11'];
		$hp->h12 = $h['h12'];
		$hp->h13 = $h['h13'];
		$hp->h14 = $h['h14'];
		$hp->h15 = $h['h15'];
		$hp->h16 = $h['h16'];
		$hp->h17 = $h['h17'];
		$hp->h18 = $h['h18'];
		return $hp;
	}
	private function setHolePars($hp, $h) {
		$hp->h1 = $h['h1'];
		$hp->h2 = $h['h2'];
		$hp->h3 = $h['h3'];
		$hp->h4 = $h['h4'];
		$hp->h5 = $h['h5'];
		$hp->h6 = $h['h6'];
		$hp->h7 = $h['h7'];
		$hp->h8 = $h['h8'];
		$hp->h9 = $h['h9'];
		$hp->h10 = $h['h10'];
		$hp->h11 = $h['h11'];
		$hp->h12 = $h['h12'];
		$hp->h13 = $h['h13'];
		$hp->h14 = $h['h14'];
		$hp->h15 = $h['h15'];
		$hp->h16 = $h['h16'];
		$hp->h17 = $h['h17'];
		$hp->h18 = $h['h18'];
		return $hp;
	}
	private function getHoleHcaps($h) {
		$hp = new CourseHandicap;
		$hp->p1 = $h['p1'];
		$hp->p2 = $h['p2'];
		$hp->p3 = $h['p3'];
		$hp->p4 = $h['p4'];
		$hp->p5 = $h['p5'];
		$hp->p6 = $h['p6'];
		$hp->p7 = $h['p7'];
		$hp->p8 = $h['p8'];
		$hp->p9 = $h['p9'];
		$hp->p10 = $h['p10'];
		$hp->p11 = $h['p11'];
		$hp->p12 = $h['p12'];
		$hp->p13 = $h['p13'];
		$hp->p14 = $h['p14'];
		$hp->p15 = $h['p15'];
		$hp->p16 = $h['p16'];
		$hp->p17 = $h['p17'];
		$hp->p18 = $h['p18'];
		return $hp;
	}
	private function setHoleHcaps($hp, $h) {
		$hp->p1 = $h['p1'];
		$hp->p2 = $h['p2'];
		$hp->p3 = $h['p3'];
		$hp->p4 = $h['p4'];
		$hp->p5 = $h['p5'];
		$hp->p6 = $h['p6'];
		$hp->p7 = $h['p7'];
		$hp->p8 = $h['p8'];
		$hp->p9 = $h['p9'];
		$hp->p10 = $h['p10'];
		$hp->p11 = $h['p11'];
		$hp->p12 = $h['p12'];
		$hp->p13 = $h['p13'];
		$hp->p14 = $h['p14'];
		$hp->p15 = $h['p15'];
		$hp->p16 = $h['p16'];
		$hp->p17 = $h['p17'];
		$hp->p18 = $h['p18'];
		return $hp;
	}
	public function updCourse(Request $da) { Log::info("course id=" . $da['id'] .", course name=". $da['name'], $da->toArray());
		$courseId = $da['id'];
		$course = Course::find($courseId);
		$course->name = $da['name'];

		$holes = $da['holes'];
		$dmPar = CoursePar::find($holes['id']);
		$dmPars = $this->setHolePars($dmPar, $holes);

		$hcaps = $da['hcaps'];
		$dmCap = CourseHandicap::find($hcaps['id']);
		$dmCaps = $this->setHoleHcaps($dmCap, $hcaps);

		$trys = $da['trys'];
		$dmTrys = [];
		foreach($trys as $t) {
			if (isset($t['id']) && $t['id'] > 0) $dm = CourseTee::find($t['id']);
			else $dm = new CourseTee;
			$dm->course_id = $courseId;
			$dm->teebox = $t['teebox'];
			$dm->yardage = $t['yardage'];
			$dm->rating = $t['rating'];
			$dm->slope = $t['slope'];
			$dm->par = $t['par'];
			// $dm->par = $holes->h1+$holes->h2+$holes->h3+$holes->h4+$holes->h5+$holes->h6+$holes->h7+$holes->h8+$holes->h9+$holes->h10+$holes->h11+$holes->h12+$holes->h13+$holes->h14+$holes->h15+$holes->h16+$holes->h17+$holes->h18;
			// $dm->par = $holes['h1']+$holes['h2']+$holes['h3']+$holes['h4']+$holes['h5']+$holes['h6']+$holes['h7']+$holes['h8']$holes['h9']+$holes['h10']+$holes['h11']+$holes['h12']+$holes['h13']+$holes['h14']+$holes['h15']+$holes['h16']+$holes['h17']+$holes['h18'];
			$dmTrys[] = $dm;
		}
		try {
			$course->save();
			foreach($dmTrys as $dt) $dt->save();
			$dmPars->save();
			$dmCaps->save();
		} catch(MyException $e) {
			return "FAILED " . $e;
		}
		return ['status' => "OK"];
	}
	public function delTeebox(Request $da) {
		// Log::info(['delTeebox', $da->teeboxId, $da['courseId'], $da]);
		// Log::info(['delTeebox', $da->teeboxId]);
		// Course::destroy($da['courseId']);
		CourseTee::destroy($da->teeboxId);
		return "OK";
	}
	public function delCourse(Request $da) {
		$courseId = $da['id'];
		course::destroy($courseId);
		// $course = Course::find($courseId);
		// $course->status = 'D';

		// $holes = $da['holes'];
		// $dmPar = CoursePar::find($holes['id']);
		// $dmPar->status = 'D';

		// $hcaps = $da['hcaps'];
		// $dmCap = CourseHandicap::find($hcaps['id']);
		// $dmCap->status = 'D';

		// $trys = $da['trys'];
		// $dmTrys = [];
		// foreach($trys as $t) {
		// 	$dm = CourseTee::find($t['id']);
		// 	$dm->status = 'D';
		// 	$dmTrys[] = $dm;
		// }
		// try {
		// 	$course->save();
		// 	foreach($dmTrys as $dt) $dt->save();
		// 	$dmPar->save();
		// 	$dmCap->save();
		// } catch(MyException $e) {
		// 	return "FAILED " . $e;
		// }
		return "OK";
	}
	public function addCourse(Request $da) {
		$name = $da['name'];
		$course = new Course;
		$course->name = $name;
		$courseId = -1;
		$hparId = -1;
		$hcapId = -1;
		$trysIds = [];
		try {
			$course->save();
			$courseId = $course->id;

			$trys = $da['trys'];
			foreach($trys as $t) {
				$ct = $this->getCourseTee($t);
				$ct->course_id = $courseId;
				$ct->save();
				// $trys.array_push($ct->id);
				$trys[] = $ct->id;
			}

			// $hp = $this->getHolePars($da['holes']);
			$hp = new CoursePar;
			$hp->course_id = $courseId;
			$hp->save();
			$hparId = $hp->id;

			// $hd = $this->getHoleHcaps($da['hcaps']);
			$hd = new CourseHandicap;
			$hd->course_id = $courseId;
			$hd->save();
			$hcapId = $hd->id;
		} catch(MyException $e) {
			return "FAILED " . $e;
		}
		return ['courseId' => $courseId, 'trysIds' => $trysIds, 'hparId' => $hparId, 'hcapId' => $hcapId, 'status' => "OK" ];
	}
	public function XBAK___addCourse(Request $da) {
		$name = $da['name'];
		$course = new Course;
		$course->name = $name;
		$courseId = -1;
		try {
			$course->save();
			$courseId = $course->id;
		} catch(MyException $e) {
			return "FAILED " . $e;
		}

		$trys = $da['trys'];
		foreach($trys as $t) {
			$ct = $this->getCourseTee($t);
			$ct->course_id = $courseId;
			try {
				$ct->save();
			} catch(MyException $e) {
				return "FAILED " . $e;
			}
		}

		$hp = $this->getHolePars($da['holes']);
		$hp->courseId = $courseId;
		try {
			$hp->save();
		} catch(MyException $e) {
			return "FAILED " . $e;
		}

		$hd = $this->getHoleHcaps($da['hcaps']);
		$hd->courseId = $courseId;
		try {
			$hd->save();
		} catch(MyException $e) {
			return "FAILED " . $e;
		}
	}
	public function XXXXaddCourse(Request $da) {
		$name = $da['name'];
		$course = new Course;
		$course->name = $name;
		$courseId = -1;
		try {
			$course->save();
			$courseId = $course->id;
		} catch(MyException $e) {
			return "FAILED " . $e;
		}

		$trys = $da['trys'];
		foreach($trys as $t) {
			$courseTee = new CourseTee;
			$courseTee->course_id = $courseId;
			$courseTee->teebox = $t['teebox'];
			$courseTee->yardage = $t['yardage'];
			$courseTee->rating = $t['rating'];
			$courseTee->slope = $t['slope'];
			try {
				$courseTee->save();
			} catch(MyException $e) {
				return "FAILED " . $e;
			}
		}

		$h = $da['holes'];
		$hp = new CoursePar;
		$hp->h1 = $h['h1'];
		$hp->h2 = $h['h2'];
		$hp->h3 = $h['h3'];
		$hp->h4 = $h['h4'];
		$hp->h5 = $h['h5'];
		$hp->h6 = $h['h6'];
		$hp->h7 = $h['h7'];
		$hp->h8 = $h['h8'];
		$hp->h9 = $h['h9'];
		$hp->h10 = $h['h10'];
		$hp->h11 = $h['h11'];
		$hp->h12 = $h['h12'];
		$hp->h13 = $h['h13'];
		$hp->h14 = $h['h14'];
		$hp->h15 = $h['h15'];
		$hp->h16 = $h['h16'];
		$hp->h17 = $h['h17'];
		$hp->h18 = $h['h18'];
		try {
			$hp->save();
		} catch(MyException $e) {
			return "FAILED " . $e;
		}

		$h = $da['hcaps'];
		$hp = new CourseHandicap;
		$hp->p1 = $h['p1'];
		$hp->p2 = $h['p2'];
		$hp->p3 = $h['p3'];
		$hp->p4 = $h['p4'];
		$hp->p5 = $h['p5'];
		$hp->p6 = $h['p6'];
		$hp->p7 = $h['p7'];
		$hp->p8 = $h['p8'];
		$hp->p9 = $h['p9'];
		$hp->p10 = $h['p10'];
		$hp->p11 = $h['p11'];
		$hp->p12 = $h['p12'];
		$hp->p13 = $h['p13'];
		$hp->p14 = $h['p14'];
		$hp->p15 = $h['p15'];
		$hp->p16 = $h['p16'];
		$hp->p17 = $h['p17'];
		$hp->p18 = $h['p18'];
		try {
			$hp->save();
		} catch(MyException $e) {
			return "FAILED " . $e;
		}
		return $courseId;
	}
	public function XXgetUserType() {
		$user = Auth::guard('gadmin')->user(); // Log::info("getUserType", [$user]);
		return [ 'usertype' => is_null($user) ? null : $user->usertype, 'status' => "OK" ];
	}
	public function getUserType() {
		$user = Auth::guard('gadmin')->user(); Log::info("getUserType", [$user]);
		return ['status' => "OK", 'usertype' => is_null($user) ? null : $user->usertype];
	}
	private function XXXgetLoginUser() {
		$noLogin = ['id'=>-1, 'usertype'=>'none'];
		$user = Auth::guard('gadmin')->user(); Log::info('getLoginUser', [$user]);
		if (empty($user)) $user = $noLogin;
		return $user;
	}
	/**
	 * Log the user out of the application.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function logout(Request $request) {
		// Log::info('before logout', [$request, Auth::user()]);
		// Log::info('before logout', [Auth::guard('gadmin')->user(), $request->session()]);
		Auth::guard('gadmin')->logout();
		// $currentPassword = 'Ybsjll11'; Auth::guard('gadmin')->logoutOtherDevices($currentPassword);
		// $request->session('gadmin')->invalidate();
		$request->session()->regenerateToken();
		// $request->gaurd('gadmin')->session()->invalidate();
		// $request->gaurd('gamdin')->session()->regenerateToken();
		// Log::info('after logout', [Auth::user()]);
		// return redirect('apps/golf');
		// return redirect()->intended('apps/golf');
    return ['usertype' => $this->getUserType(), 'status' => "OK" ];
	}
  /**
   * Get a validator for an incoming registration request.
   *
   * @param  array  $data
   * @return \Illuminate\Contracts\Validation\Validator
   */
  protected function validator(array $data) {
      return Validator::make($data, [
          'name' => ['required', 'string', 'max:255'],
          'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
          'password' => ['required', 'string', 'min:8', 'confirmed'],
      ]);
  }
	public function createAccount(Request $da) { Log::info('login', $da->toArray());
    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    return User::create([
        'name' => $da['name'],
        'username' => $da['username'],
        'usertype' => $da['usertype'],
        'name' => $da['name'],
        'email' => $da['email'],
        'password' => Hash::make($da['password']),
    ]);
  }
	public function login(Request $request) { Log::info('login', $request->toArray());
		// $credentials = $request->only('email', 'password');
		// $credentials = $request->validate([ 'email' => ['required', 'email'], 'password' => ['required'] ]);
		// $credentials = $request->only('username', 'password');
		$credentials = $request->validate([ 'username' => ['required'], 'password' => ['required'] ]);
    if (Auth::guard('gadmin')->attempt($credentials)) {
			// Authentication passed...
			// return redirect()->intended('golf');
			// $user = $request->session()->regenerate(); Log::info('user info', $user);getTournamentPlayersWithScores
			// $user = $this->getLoginUser();
			// return redirect()->intended('apps/golf');
			$user = Auth::guard('gadmin')->user();
			// Log::info('getLoginUser', [$user]);
			return ['usertype'=> $user->usertype, 'status' =>"OK"];
		} else {
			return ['status' => "FAILED"];
			// return back()->withErrors([ 'email' => 'The provided credentials do not match our records.' ]);
		}
	}
	public function getMemberList() {
		// $list = DB::select("CALL get_member_list(0)");
		$list = DB::select("CALL get_member_list(0)");
		$now = date('Y-m-d');
		foreach($list as $d) {
			// $cx = DB::select("CALL get_player_club_index_and_progress(?, ?)", [0, $d->id]);
			$cx = DB::select("CALL get_player_club_index_and_progress(?, ?)", [0, $d->id]);
			$d->cidx = $cx[0]->club_index;
			$d->nidx = $cx[0]->year_progress;
		}
		return ['lst' => $list, 'status' => 'OK'];
	}
	public function index() {
		// $agent = getUserAgent();							//dd($agent);
		// return view('Golf.golf', compact('agent'));
		return view('pgc');
	}
	public function getUnexpiredTournaments($gameName='') {
		$da = DB::select("CALL get_unexpired_tournaments(?)", [$gameName]);
		return ['games' => $da, 'status' => "OK"];
	}
	public function getPlayerInfoList() {
		$da = Player::where('status', 'A')->select('id', 'lastname', 'firstname', 'chname as 姓名', 'gender', 'phone', 'email')->orderBy('id', 'desc')->get();
		$playerInfoList = Collect($da);
		return $playerInfoList;
	}
	public function buyMembership(Request $d) {
		$playerId = $d['id'];
		$memberId = $d['mid'];
		$year = $d['year'];
		$member = $d['member'];
		if ($memberId > 0) {
			$dm = Membership::find($memberId);
			$dm->year = $year;
			$dm->player_id = $playerId;
			$dm->status = $member == 'Yes' ? 'A' : 'D';
			$dm->save();
			return $memberId;
		} else {
			$dm = new Membership;
			$dm->year = $year;
			$dm->player_id = $playerId;
			$dm->status = $member == 'Yes' ? 'A' : 'D';
			$dm->save();
			return $dm->id;
		}
	}
	public function getMembership($year) {
		$da = DB::select("CALL get_membership(?)", [$year]);
		// $da = Player::where('players.status', 'A')->select('players.id', 'players.lastname', 'players.firstname', 'memberships.fees')
		// 	->leftJoin('memberships', function($leftJoin) {
		// 		$leftJoin->on('players.id', 'memberships.player_id')
		// 		->where([ ['memberships.status', 'A'], ['memberships.year', 2017] ]);
		// 	})->orderBy('id', 'asc')->get();

		$members = Collect($da);
		return $members;
	}
	public function getTournamentList() {
		$da = DB::select("CALL get_tournament_list(NULL)");
		// return [ 'lst' => Collect($da), 'status' => "OK" ];
		// return [ 'lst' => $da, 'status' => "OK" ];
		return [ 'lst' => $da, 'status' => "OK" ];
	}
	public function getCourseList() {
		// $da = Course::where('status', 'A')->select('id as value', 'name as label')->orderBy('name')->get();
		$da = DB::select("CALL get_course_list_order_by_frequency()");
		$da[] = ['value' => -1, 'label' => "Add New Course"];
		return ['lst' => $da, 'status' => "OK" ];
	}
	public function getGameNameList() {
		$da = GameName::where('status', 'A')->select('id as value', 'name as label')->orderBy('id')->get();
		$opts = [];
		foreach($da as $d) {
			if (is_numeric(substr($d->label, 0, 1))) $opts[] = [ 'value' => $d->value, 'label' => $d->label . " " . 'Tournament' ];
			else $opts[] = [ 'value' => $d->value, 'label' => $d->label ];
		}
		$opts[] = ['value' => -1, 'label' => "Add New Game Name"];
		return [ 'lst' => $opts, 'status' => "OK" ];
	}
	public function updTplayerActivity(Request $da) { Log::info('act', $da->toArray());
		$pid = $da->id;
		$dm = Tplayer::find($pid);
		$dm->activity = $da->activity;
		$dm->save();
		$tplayers = Collect(DB::select("CALL get_tournament_players(?,-1)", [$da->tmntId]));
		return $tplayers;
	}
	public function getTournamentPlayers($tid) {
		// $status = 'notDone';
		$tplayers = [];
		$tournament = Collect(DB::select("CALL get_tournaments(?)", [$tid]))[0]; //dd($tournament);
		$tplayers = Collect(DB::select("CALL get_tournament_players(?,-1)", [$tid]));
		foreach($tplayers as $tp) {
			$da = Collect(DB::select("CALL get_player_club_index_and_progress(?, ?)", [$tid, $tp->playerId]))[0];
			$tp->cdx = $da->club_index;
			$tp->npy = $da->year_progress;
		}
		return ['status' => "OK", 'tmnt' => $tournament, 'tplayers' => $tplayers ];
	}
	public function getPGCGames($gameName) { Log::info("getPGCGames for $gameName");
		$games = DB::select("CALL get_all_pgc_games(?)", [$gameName]);
		// Log::info('getPGCGames', $games);
		return [ 'lst' => $games, 'status' => "OK" ];
	}
	public function getPGCNotGroupedPlayers($tmntId, $gameId, $year) {
		$players = DB::select("CALL get_pgc_not_grouped_players(?, ?, ?)", [$tmntId, $gameId, $year]);
		// Log::info('getPGCNotGroupedPlayers', $players);
		return [ 'players' => $players, 'status' => "OK" ];
	}
	public function getPGCGamePlayers($tmntId, $gameId, $year) {
		$PGCPlayers = DB::select("CALL get_pgc_game_players(?, ?, ?)", [$tmntId, $gameId, $year]);
		// if ($gameId == 6) $poyPlayers = Tplayer::where([['poy', '>', 0], ['game_id', 4]])->select('player_id', 'poy')->get();
		if ($gameId == 6) {
			$regPlayers = DB::select("CALL get_reg_tournaments_players(?)", [$year]);  // players not played in playoff with poy > 0
			foreach($regPlayers as $p) {
				$dx = new Tplayer;
				$dx->player_id = $p->player_id;
				$dx->game_id = $gameId;
				$dx->tournament_id = $tmntId;
				$dx->year = $year;
				$dx->name = $p->name;
				Log::info("save player = $p->player_id, $p->name");
				$dx->save();
			}
			$PGCPlayers = array_merge(Collect($PGCPlayers)->toArray(), Collect($regPlayers)->toArray());
		}
		// Log::info('getPGCGamePlayers', json_decode(json_encode($PGCPlayers[0], true), true));
		return [ 'PGCPlayers' => $PGCPlayers, 'status' => "OK" ];
	}
	public function getTeamMatchPlayers($tmntId, $gameId, $gameDate) { // Log::info("getTeamMatchPlayers tmntId=$tmntId gameId=$gameId gameDate=$gameDate");
		$tplayers = DB::select("CALL get_team_match_players(?, ?, ?)", [$tmntId, $gameId, $gameDate]);
		$group_scenario = null;
		if (count($tplayers)>0) {
			$tmntId = $tplayers[0]->tmntId;
			Log::info("getTeamMatchPlayers tmntId=$tmntId gameId=$gameId gameDate=$gameDate", $tplayers);
			$group_scenario = MatchGroup::where([['status', 'A'], ['tournament_id', $tmntId]])->select('group_scenario')->value('group_scenario');
			// Log::info("getTeamMatchPlayers tmntId=$tmntId group_scenario=$group_scenario", $tplayers);
		}
		// if ($gameId == 14) {
		// $x = (new GolfGroupingController)->getAliases($gameId);
		// $aliases = $x['aliases'];
		// 	return [ 'aliases' => $aliases, 'tplayers' => $tplayers, 'group_scenario' => $group_scenario, 'status' => "OK" ];
		// }
		return [ 'tplayers' => $tplayers, 'group_scenario' => $group_scenario, 'status' => "OK" ];
	}
	public function getTplayers($tid) {
		// $tournament = Collect(DB::select("CALL get_tournaments(?)", [$tid]))[0]; //dd($tournament);
		$tplayers = DB::select("CALL get_tplayers(?)", [$tid]);
		return [ 'tplayers' => $tplayers, 'status' => "OK" ];
	}
	public function getTournamentPlayersWithScores($tid) {
		// $status = 'notDone';
		$da = $this->getTournamentPlayers($tid);
		$tournament = $da['tmnt'];
		// return $tournament;
		$tplayers = $da['tplayers'];

		$holes = CoursePar::where([ ['status', 'A'], ['course_id', $tournament->course_id] ])->select("*")->get()[0];
		$tournament->holes = $holes;

		foreach($tplayers as $p) {
			$pid = $p->playerId;
			// $ttm = $tournament->start_at;
			$tid = $tournament->id;
			$ret = $this->getPlayerGameScoresByTid($pid, $tid);
			if ($ret == 'FAILED' or $ret == 'NO_SCORES') { // there are more than one record
				$p->f9scores = null;
				$p->b9scores = null;
			} else {
				$p->scoreId = $ret->id;
				$f9 = [];
				$f9[] = $ret->h1;
				$f9[] = $ret->h2;
				$f9[] = $ret->h3;
				$f9[] = $ret->h4;
				$f9[] = $ret->h5;
				$f9[] = $ret->h6;
				$f9[] = $ret->h7;
				$f9[] = $ret->h8;
				$f9[] = $ret->h9;
				$p->f9scores = $f9;
				$b9 = [];
				$b9[] = $ret->h10;
				$b9[] = $ret->h11;
				$b9[] = $ret->h12;
				$b9[] = $ret->h13;
				$b9[] = $ret->h14;
				$b9[] = $ret->h15;
				$b9[] = $ret->h16;
				$b9[] = $ret->h17;
				$b9[] = $ret->h18;
				$p->b9scores = $b9;
			}
		}

		// return ['status' => $status, 'tmnt' => $tournament, 'tplayers' => $tplayers ];
		return ['status' => "OK", 'tmnt' => $tournament, 'tplayers' => $tplayers ];
	}
	public function getPlayersForTournament($tournamentId) {
		// $tplayers = DB::select("CALL get_players_for_tournament(?, -1)", [$tournamentId]);
		$players = DB::select("CALL get_players_for_tournament(?)", [$tournamentId]);
		$playersTobeAdded = [];
		foreach ($players as $p) $playersTobeAdded[] = [ 'value' => $p->playerId, 'label' => $p->player];
		return ['lst' => $playersTobeAdded, 'status' => "OK" ];
	}
	private function memberExist($da) {
		$dx = Player::where([ ['firstname', $da['firstname']], ['lastname', $da['lastname']] ])->select('id', 'firstname', 'lastname', 'gender', 'status')->get();
		if (count($dx) <= 0 or $da['gender'] != $dx[0]['gender']) return false;
		else if ($dx[0]['status'] == 'A') return true;
		return false;
	}
	// private function memberExist($lastnm, $firstnm, $gender) {
	// 	$dx = Player::where([ ['firstname', $firstnm], ['lastname', $lastnm]])->select('id', 'firstname', 'lastname', 'status')->get();
	// 	if (count($dx) <= 0 or $dx['gender'] != $gender) return false;
	// 	else if ($dx[0]['status'] == 'A') return true;
	// 	return false;
	// }
	public function getPGCRules($gameId) { Log::info("getPGCRules for gameId=$gameId");
		// $path = '/sites/webdata/docs/golf/pgc_rules.txt';  // for gameId = 0
		$path = '/sites/webdata/docs/golf/pgc_rules.txt';  // for gameId = 0
		$rules = file_get_contents($path);
		Log::info("$path: $rules");
		return ['rules' => $rules, 'status' => "OK"];
	}
	public function getPGCMemberList($year) { Log::info("getPGCMemberList for year $year");
		$lst = Membership::where('year', $year)->select('memberships.id', 'lastname', 'firstname', 'gender', 'player_id', 'type', 'fees')
				->leftJoin('players', function($leftJoin) {
						$leftJoin->on('players.id', 'memberships.player_id');
				})->orderBy('id', 'asc')->get();
		return ['lst' => $lst, 'status' => "OK"];
	}
	public function addPGCMembership(Request $da) { Log::info('addPGCMembership', $da->toArray());
		$dm = new Membership();
		$dm->player_id = is_null($da['player_id']) ? $da['id'] : $da['player_id'];
		$dm->type = $da['type'];
		$dm->year = $da['year'];
		$dm->fees = $da['fees'];
		try {
			Membership::upsert(
				['player_id' => $dm->player_id, 'year' => $dm->year, 'type' => $dm->type, 'fees' => $dm->fees],
				['player_id', 'year', 'type'], ['fees']
			);
			return ['status' => "OK"];
		} catch (MyException $ex) {
			return ['status' => "addPGCMembership FAILED " . $ex->message()];
		}
	}
	public function addMember(Request $da) { Log::info("addMember", $da->toArray());
		$member = null;
		$ret_status = "OK";
		$da['mid'] = -1;
		$fnm = $da['firstname'];
		$lnm = $da['lastname'];
		// $dx = Player::firstOrNew(['firstname' => $fnm], ['lastname' => $lnm]); return [$dx, 'Exist'];
		$dx = Player::where([ ['firstname', $fnm], ['lastname', $lnm]])->select('id', 'firstname', 'lastname', 'status')->get();
		// $dx = Collect($dx); 
		Log::info("a new player", $dx->toArray());
		if (count($dx) <= 0) {	Log::info("a new player $fnm $lnm");
			$dm = new Player;
			$dm->firstname = $fnm;
			$dm->lastname = $lnm;
			$dm->gender = $da['gender'];
			$dm->phone = $da['phone'];
			$dm->email = $da['email'];
			$dm->chname = $da['chname'];
			$dm->nkname = $da['nkname'];
			$dm->team = $da['team'];
			try {
				$dm->save();
				$this->saveMembership($dm->id, $da);
				$member = $dm;
				// return [ 'newPlayer' => $member, 'status' => 'OK' ];
				if (isset($da['type'])) { // new member for membership
					$member = ['player_id' => $dm->id, 'gender' => $da['gender'], 'type' => $da['type'], 'name' => $da['lastname'] . ', ' . $da['firstname']];
					return [ 'member' => $member, 'status' => 'OK' ];
				}
				return $this->getMemberList();
			} catch(MyException $e) {
				return [$e->getMessage(), 'DBerror'];
			}
		} else if ($this->memberExist($da)) {
			// $member = $dx[0];
			// $ret_status = 'Exists';
			$member = ['player_id' => $dx->id, 'gender' => $da['gender'], 'type' => $da['type'], 'name' => $da['lastname'] . ', ' . $da['firstname']];
		} else if ($dx[0]->status == 'D') {
			$pid = $dx[0]->id;
			$d = Player::find($dx[0]->id);
			$d->chname = $da['chname'];
			$d->nkname = $da['nkname'];
			$d->gender = $da['gender'];
			$d->phone = $da['phone'];
			$d->email = $da['email'];
			$d->status = 'A';
			try {
				$d->update();
				$this->saveMembership($pid, $da);
				$member = $d;
			} catch(MyException $e) {
				return [$e->getMessage(), 'DBerror'];
			}
		}
		return ['player' => $member, 'status' => $ret_status ];
	}
	public function delMember($pid, $mid) {
		Player::destroy($pid);
		if ($mid > 0) Membership::destroy($mid);
		// return [ 'status' => 'OK' ];
		return $this->getMemberList();
	}
	public function updMember(Request $da) { //Log::info($da);
		$pid = $da['id'];
		$dm = Player::find($pid);
		$dm->firstname = $da['firstname'];
		$dm->lastname = $da['lastname'];
		if ($this->is_duplicated($dm)) return [$da, 'Duplicated'];
		$dm->gender = $da['gender'];
		$dm->phone = $da['phone'];
		$dm->email = $da['email'];
		$dm->chname = $da['chname'];
		$dm->nkname = $da['nkname'];
		$dm->update();

		$this->saveMembership($pid, $da);
		return $this->getMemberList();
	}
	private function saveMembership($pid, $da) {
		$type = $da['type'];
		if (is_null($type)) return;
		$mid = $da['mid'];
		$dm = Membership::find($mid);
		if (is_null($dm)) $dm = new Membership;
		$fees = $da['fees'];
		$dm->year = $da['year'];
		$dm->type = $type;
		if ($type == 'T') $dm->fees += $fees;
		else $dm->fees = $fees;
		$dm->player_id = $pid;
		try {
			$dm->save();
			return [ 'updatedMember' => $dm, 'status' => "OK" ];
		} catch(Exception $e) {
			return "FAILED " . $e->getMessage();
		}
		return "OK";
	}
	public function addNewCourse(Request $da) {
		$dm = new Course;
		$courseId = -1;
		$dm->name = $da['courseName'];
		try {
			// $dm->updateOrCreate([ 'name' => $da['courseName'] ]);
			$dm->save();
			$courseId = $dm->id;
			// return "OK";
		} catch(MyException $e) {
			return "FAILED";
		}
		try {
			$courseDa = $da['courseData'];
			foreach($courseDa as $d) {
				$dx = new CourseTee;
				$dx->course_id = $courseId;
				$dx->teebox = $d['teebox'];
				$dx->yardage = $d['yardage'];
				$dx->rating = $d['rating'];
				$dx->slope = $d['slope'];
				$dx->save();
			}
			return "OK";
		} catch(MyException $e) {
			return "FAILED";
		}
	}
	public function getExCourse() {
		$courseId = 3;
		$da = Course::where('id', $courseId)->select('name')->get();
		$dx = CourseTee::where('course_id', $courseId)->select('teebox', 'rating', 'slope', 'yardage')->get();
		return [ 'courseName' => $da[0]->name, 'courseData' => $dx ];
	}
	public function getTeeboxList($courseId) {
		$da = CourseTee::where([ ['status', 'A'], ['course_id', $courseId]])
			->select('id', 'teebox', 'rating', 'slope', 'yardage')->orderBy('teebox')->get();			//dd(Collect($da));
		if (count($da) <= 0) return [ 'options' => [], 'optId' => -1 ];
		$opts = [];
		foreach($da as $d) {
			// $opts[] = ['value'=>$d->id, 'label'=>$d->teebox . ' ~ Rating:' . $d->rating . ', Slope:' . $d->slope . ', Yardage:' . $d->yardage];
			$opts[] = ['value'=>$d->id, 'label'=>$d->teebox . ' ~ ' . $d->rating . ' ' . $d->slope . ' '. $d->yardage];
		}
		return [ 'lst' => $opts, 'optId' => $da[0]->id, 'status' => 'OK' ];
	}
	public function updTournament(Request $da) { Log:info('upding Tournament', $da->toArray());
		// $this->updScoreOnTmnt($da);
		$theId = (int)$da->id;
		// Log::info("theId", [$theId, $da->game_id, $da->start_at]);
		$dm = Tournament::find($theId); // return 404 to client if failed
		// $dx = $d->toArray();
		// foreach($dx as $key => $val) {
			// 	if ($key == 'year') $dm->$key = substr($val, 0, 4);
			// 	else $dm->$key = $val;
			// }
		// Log::info('dm', [$theId, $dm]);
		// Log::info('dm', [$dm->start_at, $dm->year, $dm->id]);
		// Log::info($id, [$dm, $dm->start_at]);
		// Log::info('dm', $dm->toArray());
		// Log::info('dm', $dm->toArray(), $da->toArray());
		// $dm->start_at = 'start_at';
		$dm->start_at = $da['start_at'];
		$dm->year = (int)substr($da['start_at'], 0, 4);
		$dm->fees = $da['fees'];
		$dm->game_id = $da->game_id;
		$dm->course_id = $da['course_id'];
		$dm->mens_tee_id = $da['mtee_id'];
		$dm->lady_tee_id = empty($da['ltee_id']) ? null : $da['ltee_id'];
		$dm->note = empty($da['note']) ? null : $da['note'];
		$dm->teetime_gap = $da['teetime_gap'];
		$dm->links = $da['links'];
		// $dm->teetime_gap = empty($da['teetime_gap']) ? null : $da['ttgap'];
		try {
			$dm->save();
			$this->updScoreOnTmnt($da);
			return $this->getTournaments($da->game_id, $da->id);
			// return $this->getTournamentByTmntId($da->id, $da->game_id);
		} catch(Exception $ex) {
			return $ex->getMessage();
		}
	}
	private function updScoreOnTmnt($da) {
		$theId = (int)$da->id;
		$sids = Score::where([['status', 'A'], ['tournament_id', $theId]])->pluck('id');
		foreach($sids as $sid) {
			Log::info("getting score db obj for score_id=$sid");
			$ds = Score::find($sid);
			if ($ds == null) {
				Log::info("can not find score db obj for score id=$id");
			}
			$ds->course_id = $da['course_id'];
			$ds->teebox_id = $da['mtee_id'];
			$ds->teetime = $da['start_at'];
			$ds->save();
		}
	}
	public function addTournament(Request $da) { Log::info('adding Tournament', $da->toArray());
		$ng = isSet($da['numGroup']) ? $da['numGroup'] : 1 ;
		$st = $da['start_at'];
		$tg = $ng > 1 ? $da['teetime_gap'] : null;
		$dm = null;
		for ($i=0; $i<$ng; $i++) {
			$tm = $i * $tg;
			$dm = new Tournament;
			$dm->start_at = date("Y-m-d H:i", strtotime("+$tm minutes", strtotime($st)));
			$dm->game_id = $da['game_id'];
			$dm->course_id = $da['course_id'];
			$dm->year = substr($da['start_at'], 0, 4);
			$dm->fees = empty($da['fees']) ? null : $da['fees'];
			$dm->mens_tee_id = $da['mtee_id'];
			$ttgap = $da->teetime_gap;
			$dm->teetime_gap = $da->teetime_gap;
			$dm->lady_tee_id = empty($da['ltee_id']) ? null : $da['ltee_id'];
			$dm->note = empty($da['note']) ? null : $da['note'];
			$dm->save();
		}
		// $nd = DB::select("CALL get_tournaments_for_date_and_game(?, ?)", [$dm->game_id, substr($dm->start_at, 0, 10)]);
		// return [ 'matches' => Collect($nd), 'status' => 'OK'];
		// if ($da->game_id == 13) return $this->getTournaments($da->game_id); // JZsMatch
		Log::info("added New Tournament for $da->game_id from addTournament");
		// if ($da->game_id > 0) return $this->getTournaments($da->game_id); // TeamMatch
		if ($da->game_id > 0) return ['status' => "OK"]; // TeamMatch
		else return $this->getTournamentList(); 
	}
	// public function getTournament($gameId, $tid) { Log::info("getTournament($gameId, $tid)");
	// 	$tournament = Tournament::where([['game_id', $gameId], ['status', 'A'], ['id', $tid]])
	// 		->select('id', 'game_id', 'start_at', 'teetime_gap', 'course_id', 'note', 'fees', 'mens_tee_id', 'lady_tee_id')
	// 		->with('course:id,name')
	// 		->with('lteebox:id,teebox,rating,slope,yardage')
	// 		->with('mteebox:id,teebox,rating,slope,yardage')
	// 		->orderBy('start_at', 'desc')->get();
	// 		Log::info("get Tournament for gameId=$gameId, tid=$tid", [$tournament]);
	// 	return ['tournament' => $tournament[0], 'status' => "OK"];
	// }
	public function XXXXaddTournament(Request $da) { Log::info('addTournament', $da);
		$start_at = $da['start_at'];
		$game_id = $da['game_id'];
		$course_id = $da['course_id'];

		$dx = Tournament::firstOrNew( ['start_at' => $start_at ], ['game_id' => $game_id ], ['course_id' => $course_id ] );
		if (!empty($dx['id']) and $dx['status'] == 'A') {
			$d = Tournament::find($dx['id']);
			$nd = DB::select("CALL get_tournaments(?)", [$d->id])[0];
			return [Collect($nd), "Exist"];
		} else if (!empty($dx['id']) and $dx['status'] == 'D') {
			$d = Tournament::find($dx['id']);
			$d->status = 'A';
			$d->start_at = $da['start_at'];
			$d->game_id = $da['game_id'];
			$d->course_id = $da['course_id'];
			$d->year = substr($da['start_at'], 0, 4);
			$d->teetime_gap = $da->ttgap;
			// $d->fees = array_key_exists('fees', $da) or empty($da['fees']) ? null : $da['fees'];
			$d->fees = empty($da['fees']) ? null : $da['fees'];
			$d->mens_tee_id = empty($da['mtee_id']) ? null : $da['mtee_id'];
			$d->lady_tee_id = empty($da['ltee_id']) ? null : $da['ltee_id'];
			$d->note = empty($da['note']) ? null : $da['note'];
			$d->update();
			$nd = DB::select("CALL get_tournaments(?)", [$d->id])[0];
			return [Collect($nd), "Success"];
		}

		$dm = new Tournament;
		$dm->start_at = $da['start_at'];
		$dm->game_id = $da['game_id'];
		$dm->course_id = $da['course_id'];
		$dm->year = substr($da['start_at'], 0, 4);
		$dm->fees = empty($da['fees']) ? null : $da['fees'];
		$dm->mens_tee_id = $da['mtee_id'];
		$dm->teetime_gap = $da->ttgap;
		// $dm->mens_tee_id = empty($da['mteeId']) ? null : $da['mteeId'];
		$dm->lady_tee_id = empty($da['ltee_id']) ? null : $da['ltee_id'];
		$dm->note = empty($da['note']) ? null : $da['note'];
		$dm->save();
		$nd = DB::select("CALL get_tournaments(?)", [$dm->id])[0];
		return [Collect($nd), 'Success'];
	}
	public function delTournament(Request $da) { Log:info('delTournament', $da->toArray());
		$dm = Tournament::find($da['id']);
		try {
			$dm->delete();
			if ($da['cleanupTplayers']) {
				Log::info('cleanup tplayers');
				Tplayer::where('tournament_id', $da['id'])->delete();
			}
			// return "Success";
			// return ['status' => "OK"];
			return ['gameId' => $da['gameId'], 'status' => "OK"];
			// return $this->getTournaments($da->gameId);
		} catch(Exception $ex) {
			return $ex->getMessage();
		}
	}
	public function XXXdelTournament(Request $da) {
		$dm = Tournament::find($da['id']);
		$dm->status = 'D';
		$dm->update();
		return "Success";
	}
	public function updDinnerTournamentPlayer($dinner, $tpid) {
		$dm = Tplayer::find($tpid);
		$dm->hole19 = $dinner;
		$dm->update();
		return "Success";
	}
	public function delTournamentPlayer($tplayerId) {
		$dm = Tplayer::find($tplayerId);
		$dm->status = 'D';
		$dm->update();
		return [ 'status' => "OK" ];
	}
	private function getModelForScoreIns($da) {
		$playerId = $da->player_id;
		$teetime = $da['teetime'];
		$tmntId = $da['tmntId'];
		$dm = Score::firstOrNew(['playerId' => $playerId], ['teetime' => $teetime], ['tournamentId'=> $tmntId]);
		$dm->playerId = $playerId;
		$dm->courseId = $da['courseId'];
		$dm->teetime = $da['teetime'];
		$dm->tournamentId = $da['tmntId'];
		$dm->note = $da['note'];
		$dm->front9 = $da['f9total'];
		$dm->back9 = $da['b9total'];
		$f9s = $da['f9scores'];
		$dm->h1 = $f9s[0];
		$dm->h2 = $f9s[1];
		$dm->h3 = $f9s[2];
		$dm->h4 = $f9s[3];
		$dm->h5 = $f9s[4];
		$dm->h6 = $f9s[5];
		$dm->h7 = $f9s[6];
		$dm->h8 = $f9s[7];
		$dm->h9 = $f9s[8];
		$b9s = $da['b9scores'];
		$dm->h10 = $b9s[0];
		$dm->h11 = $b9s[1];
		$dm->h12 = $b9s[2];
		$dm->h13 = $b9s[3];
		$dm->h14 = $b9s[4];
		$dm->h15 = $b9s[5];
		$dm->h16 = $b9s[6];
		$dm->h17 = $b9s[7];
		$dm->h18 = $b9s[8];
		$dm->totalscore = $dm->front9 + $dm->back9;
		$dm->teeboxId = $da['teeboxId'];
		$dm->hdcpdiff = ($dm->totalscore - $da['rating']) * 113 / $da['slope'];
		return $dm;
	}
	public function updPosition($tpid, $pos) {
		$dm = TPlayer::find($tpid);
		$pos = $pos == "X" ? null : $pos;
		$dm->pos = $pos;
		try {
			$dm->update();
			return "OK";
		} catch(MyException $e) {
			return "FAILED";
		}
	}
	private function setModelForUpdScores($dm, $da) {
		if ( isset($da['note']) ) $dm->note = $da['note'];
		$dm->front9 = $da['f9total'];
		$dm->back9 = isset($da['b9total']) ? $da['b9total'] : null;
		$f9s = $da['f9scores'];
		$dm->h1 = $f9s[0];
		$dm->h2 = $f9s[1];
		$dm->h3 = $f9s[2];
		$dm->h4 = $f9s[3];
		$dm->h5 = $f9s[4];
		$dm->h6 = $f9s[5];
		$dm->h7 = $f9s[6];
		$dm->h8 = $f9s[7];
		$dm->h9 = $f9s[8];
		$b9s = $da['b9scores'];
		$dm->h10 = $b9s[0];
		$dm->h11 = $b9s[1];
		$dm->h12 = $b9s[2];
		$dm->h13 = $b9s[3];
		$dm->h14 = $b9s[4];
		$dm->h15 = $b9s[5];
		$dm->h16 = $b9s[6];
		$dm->h17 = $b9s[7];
		$dm->h18 = $b9s[8];
		$dm->totalscore = $dm->front9 + $dm->back9;
		$dm->hdcpdiff = ($dm->totalscore - $da['rating']) * 113 / $da['slope'];
		return $dm;
	}
	private function XXXsetDdModel($dm, $da) {
		$dm->playerId = $da['member']['playerId'];
		// $dm->player = $da['member']['player'];
		$dm->courseId = $da['tmnt']['course_id'];
		// $dm->course = $da['tmnt']['courseName'];
		$dm->teetime = $da['tmnt']['start_at'];
		$dm->tournamentId = $da['tmnt']['id'];
		$dm->note = $da['note'];
		$dm->front9 = $da['f9total'];
		$dm->back9 = $da['b9total'];
		$f9s = $da['f9scores'];
		$dm->hole1par = $f9s[0];
		$dm->hole2par = $f9s[1];
		$dm->hole3par = $f9s[2];
		$dm->hole4par = $f9s[3];
		$dm->hole5par = $f9s[4];
		$dm->hole6par = $f9s[5];
		$dm->hole7par = $f9s[6];
		$dm->hole8par = $f9s[7];
		$dm->hole9par = $f9s[8];
		$b9s = $da['b9scores'];
		$dm->hole10par = $b9s[0];
		$dm->hole11par = $b9s[1];
		$dm->hole12par = $b9s[2];
		$dm->hole13par = $b9s[3];
		$dm->hole14par = $b9s[4];
		$dm->hole15par = $b9s[5];
		$dm->hole16par = $b9s[6];
		$dm->hole17par = $b9s[7];
		$dm->hole18par = $b9s[8];
		$dm->totalscore = $dm->front9 + $dm->back9;
		$gender = $da['member']['gender'];
		if ($gender == 'M') {
			$dm->teebox = $da['tmnt']['mtee'];
			$dm->rating = $da['tmnt']['mrating'];
			$dm->slope = $da['tmnt']['mslope'];
		} else {
			$dm->teebox = $da['tmnt']['ltee'];
			$dm->rating = $da['tmnt']['lrating'];
			$dm->slope = $da['tmnt']['lslope'];
		}
		$dm->hdcpdiff = ($dm->totalscore - $dm->rating) * 113 / $dm->slope;
		return $dm;
	}
	private function updTPlayerScore($da, $gross_score, $hdcpdiff, $scoreId=0, $datpid=0) { Log::info("updTPlayerScore, tpid=$datpid, scoreId=$scoreId, gross_score=$gross_score, hdcpdiff=$hdcpdiff", $da->toArray());
		$tpid = $da['id'];
		if ($datpid > 0) $tpid = $datpid;
		$dm = TPlayer::find($tpid);
		// $dm->year = $da['year'];
		$dm->name = $da['name'];
		if ($scoreId>0) $dm->score_id = $scoreId;
		$dm->gross_score = $gross_score;
		$dm->idx_diff = $hdcpdiff;
		try {
			$dm->save();
			return "OK";
		} catch(MyException $e) {
			// return [$e->getMessage(), 'DBerror'];
			return "FAILED";
		}
	}
	public function setTournamentScore(Request $da) {
		$tpid = $da['tpid'];
		$score = $da['score'];
		$isDone = $da['isDone'];
		$tournamentId = $da['tournamentId'];
		if (empty($score)) {
			$isDone = false;
			$tm = Tournament::find($tournamentId);
			$tm->is_score_input_done = false;
			$tm->update();
		}
		$dt = Tplayer::find($tpid);
		$dt->gross_score = $da['score'];
		$dt->save();			//if ($isDone) return $tournamentId; //return $isDone;
		if ($isDone) {
			$this->upd_tournament_ranks($tournamentId);
			$tm = Tournament::find($tournamentId);
			$tm->is_score_input_done = true;
			$tm->update();
		}
		return ['status' => 'scoreInputDone', 'scores' => DB::select("CALL get_tournament_scores(?, -1)", [$tournamentId])];
	}
	private function upd_tournament_ranks($tournamentId) {
		$da = Tplayer::where([ ['tournament_id', $tournamentId], ['status', 'A'] ])->select('id', 'gross_score')->orderBy('gross_score')->get();
		foreach($da as $d) {
			$dt = Tplayer::find($d->id);
			$dt->pos = null;
			$dt->gross_rank = null;
			$dt->update();
		}
		$orders = [];
		$order = 0;
		$prev_score = 0; //return $prev_score;
		foreach($da as $d) {
			// $dt->pos = null;
			if ($prev_score < $d->gross_score)  { $order++; }
			$orders[] = $order;
			$prev_score = $d->gross_score;
		}
		$this->part_avg($orders);   //populate the $this->gross_ranking array
		foreach($da as $d) {
			$order = array_shift($orders);
			$rank = array_shift($this->gross_ranks);
			$dt = Tplayer::find($d->id);
			if ($order == 1) $dt->pos = 'G1';
			else if ($order == 2) $dt->pos = 'G2';
			else if ($order == 3) $dt->pos = 'G3';
			$dt->gross_rank = $rank;
			$dt->update();
		}
	}
	private function part_avg($orders) {
		$this->gross_ranks = [];
		$comp = array_count_values($orders); //print_r($comp);
		$sum = 0;
		$idx = 1;
			foreach(array_values($comp) as $val) {
			for ($k=0; $k<$val; $k++) {
				$sum += $idx;
				$idx++;
			}
			$avg = $sum / $val;
			for ($k=0; $k<$val; $k++) $this->gross_ranks[] = $avg;
			$sum = 0;
	  	}
	}
	private function XXXXpart_avg($orders) {
		$this->gross_ranks = [];
	  $comp = array_count_values($orders); //print_r($comp);
	  $last = max(array_keys($comp)); //echo max( $last ); exit;
	  $sum = 0;
		$avg = 0;
	  $idx = 1;
	  for ($i=1; $i<=$last; $i++) {
	      $cntk = $comp[$i];
	      for ($k=0; $k<$cntk; $k++) {
	        $sum += $idx;
	        $idx++;
	      }
	      $avg = $sum / $cntk;
	      for ($j=0; $j<$cntk; $j++) $this->gross_ranks[] = $avg;
	      $sum = 0;
	  }
	}
	public function getPlayersRanking($tid) {
		$da = DB::select("CALL get_players_ranking(?)", [$tid]);
		foreach($da as $d) {
			$dx = Collect(DB::select("CALL get_player_club_index_and_progress(?, ?)", [$tid, $d->player_id]))[0];
            $d->CDX = $dx->club_index;
			if (isset($d->GSC) and isset($dx->club_index)) $d->NSC = $d->GSC - $dx->club_index;
			else $d->NSC = null;
		}
		$tmnt = Tournament::find($tid);
		$gameName = GameName::find($tmnt->game_id)->name;
		if (preg_match('@^\d@', $gameName)) $gameName .= ' Tournament';
		$tmnt_start = $tmnt->start_at;
		// $today = Date('Y-m-d', time());
		// if ($today > $tmnt_start) return ['ranks' => $da, 'game' =>substr($tmnt_start, 0, 16) .' the '. $gameName];
		return ['ranks' => $da, 'game' =>substr($tmnt_start, 0, 16) .' the '. $gameName];

		/* calc positions -- can and should be done manually
		$rank = 1;
		$nn = 1;
		$prev_score = $da[0]->NSC; //return $prev_score;
		foreach($da as $d) {
			if (empty($d->GSC)) {    // score input not done yet
				$d->pos = null;
				$d->GRK = null;
				$d->NSC = null;
				$d->NRK = null;
				continue;
			}

			// if (empty($d->pos) or $d->pos == '' or $d->pos == null) {
			// 	if ($d->GRK == 1.0) $d->pos = 'G1';
			// 	else if ($d->GRK >= 2.0 and $d->GRK < 3 ) $d->pos = 'G2';
			// 	else if ($d->GRK >= 3.0 and $d->GRK <= 4) $d->pos = 'G3';
			// }

			if (!preg_match('@^(G|L|C|N)@', $d->pos)) $d->pos = null;
			if (empty($d->NSC) or $d->NSC == '') { continue; }
			if (!empty($prev_score) and $prev_score != $d->NSC) $rank++;
			$d->NRK = $rank;
			if (!empty($d->tpid)) {
				$dt = Tplayer::find($d->tpid);
				if ($nn <= 4 and !preg_match("@^(G)@", $dt->pos)) {
					$d->pos = 'N' . $nn;
					$dt->pos = 'N' . $nn++;
				}
				$dt->update();
			}
			$prev_score = $d->NSC;
		}
		return ['ranks' => $da, 'game' => substr($tmnt_start, 0, 16) .' the '. $gameName];
		*/
	}
	public function getTournamentRanks($tournamentId) {
		// $da = DB::select("CALL get_tournament_ranks(4, -2, ?)", [$tournamentId]);
		$da = DB::select("CALL get_tournament_ranks(4, -2, ?)", [$tournamentId]);
		$tmnt = Tournament::find($tournamentId);
		$gameName = GameName::find($tmnt->game_id)->name;
		if (preg_match('@^\d@', $gameName)) $gameName .= ' Tournament';
		$tmnt_start = $tmnt->start_at;
		$today = Date('Y-m-d', time());
		if ($today > $tmnt_start) return ['ranks' => $da, 'game' =>substr($tmnt_start, 0, 16) .' the '. $gameName];

		$rank = 1;
		$nn = 1;
		$prev_score = $da[0]->NSC; //return $prev_score;
		foreach($da as $d) {
			if (empty($d->GSC)) {    // score input not done yet
				$d->pos = null;
				$d->GRK = null;
				$d->NSC = null;
				$d->NRK = null;
				continue;
			}

			// if (empty($d->pos) or $d->pos == '' or $d->pos == null) {
			// 	if ($d->GRK == 1.0) $d->pos = 'G1';
			// 	else if ($d->GRK >= 2.0 and $d->GRK < 3 ) $d->pos = 'G2';
			// 	else if ($d->GRK >= 3.0 and $d->GRK <= 4) $d->pos = 'G3';
			// }

			if (!preg_match('@^(G|L|C|N)@', $d->pos)) $d->pos = null;
			if (empty($d->NSC) or $d->NSC == '') { continue; }
			if (!empty($prev_score) and $prev_score != $d->NSC) $rank++;
			$d->NRK = $rank;
			if (!empty($d->id)) {
				$dt = Tplayer::find($d->id);
				if ($nn <= 4 and !preg_match("@^(G)@", $dt->pos)) {
					$d->pos = 'N' . $nn;
					$dt->pos = 'N' . $nn++;
				}
				$dt->update();
			}
			$prev_score = $d->NSC;
		}
		return ['ranks' => $da, 'game' => substr($tmnt_start, 0, 16) .' the '. $gameName];
	}
	public function XXXXsetTournamentPos(Request $da) {
		$id = $da['id'];
		$pos = $da['pos'];
		$dt = Tplayer::find($id);
		$dt->pos = $pos;
		$dt->update();
		return [ 'status' => 'OK' ];
	}
	public function addGameName(Request $di) {
		$dm = new GameName;
		$dm->name = trim($di['game']);
		if (strlen($dm->name) <= 0) return [ 'status' => 'FAILED', 'error' => "game name missing!" ];
		else if (strlen($dm->name) > 16) return [ 'status' => 'FAILED', 'error' => "game name is too long must be < 16 letters! yours is " . strlen($dm->name)];

		$dm->save();
		$da = GameName::find($dm->id);
		return [ 'status' => 'SUCCESS', 'game' => [ 'label' => $da->name, 'value' => $da->id ] ];
	}
	public function XXX__addCourse(Request $di) {
		$dm = new Course;
		$dm->name = $di['newCSP'];
		$dm->save();
		$da = Course::find($dm->id);
		return [ 'name' => $da->name, 'optId' => $da->id ];
	}
	public function addTeebox(Request $di) { Log::info("addTeebox", $di->toArray());
		$dm = new CourseTee;
		$dm->course = trim($di['course']);
		$dm->par = trim($di['par']);
		$dm->course_id = trim($di['courseId']);
		$dm->teebox = trim($di['teebox']);
		$dm->rating = trim($di['rating']);
		$dm->slope = trim($di['slope']);
		$dm->yardage = trim($di['yardage']);
		$errmsg = '';
		if (strlen($dm->teebox) < 0) $errmsg.= "teebox name missing\n";
		if (!is_numeric($dm->course_id)) $errmsg.= "courseId missing and must be an integer\n";
		if (!is_numeric($dm->yardage)) $errmsg.= "yardage missing and must be an integer\n";
		if (!is_numeric($dm->slope)) $errmsg.= "slope missing and must be an integer\n";
		if (!is_numeric($dm->rating)) $errmsg.= "rating missing and must be a floating\n";
		$errmsg = trim($errmsg);
		if (strlen($errmsg) > 0) return [ 'status' => "FAILED", 'error' => $errmsg ];
		$dm->save();
		$d = CourseTee::find($dm->id);
		$tee = ['value'=>$d->id, 'label'=>$d->teebox . ' ~ Rating:' . $d->rating . ', Slope:' . $d->slope . ', Yardage:' . $d->yardage];
		return [ 'status' => "OK", 'tee' => $tee ];
	}
	public function addCourseInfo(Request $di) {
		$course = trim($di['course']);
		$mtee = trim($di['mtee']);
		$ltee = trim($di['ltee']);
		$myard = trim($di['myard']);
		$lyard = trim($di['lyard']);
		$mrating = trim($di['mrating']);
		$lrating = trim($di['lrating']);
		$mslope = trim($di['mslope']);
		$lslope = trim($di['lslope']);
		$errmsg = "";
		if (strlen($course) <= 0) $errmsg.="Course Name missing\n";
		if (strlen($mtee) <= 0) $errmsg.="men's teee missing\n";
		if (strlen($ltee) <= 0) $errmsg.="ladies' teee missing\n";
		if (!is_numeric($myard)) $errmsg.="men's yardage must be an integer\n";
		if (!is_numeric($lyard)) $errmsg.="ladies' yardage must be an integer\n";
		if (!is_numeric($mslope)) $errmsg.="men's slope: must be a float\n";
		if (!is_numeric($lslope)) $errmsg.="ladies' slope: must be a float\n";
		if (!is_numeric($mrating)) $errmsg.="men's rating: must be a float\n";
		if (!is_numeric($lrating)) $errmsg.="ladies' rating: must be a float\n";
		if (strlen($errmsg) > 0) {
			return ['status' => 'error', 'error' => $errmsg];
		}
		$dc = new Course;
		$dc->name = $course;
		$dc->save();
		$courseId = $dc->id;

		$dm = new CourseTee;
		$dm->course_id = $courseId;
		$dm->teebox = $mtee;
		$dm->rating = $mrating;
		$dm->slope = $mslope;
		$dm->yardage = $myard;
		$dm->save();
		$mteeId = $dm->id;

		$dl = new CourseTee;
		$dl->course_id = $courseId;
		$dl->teebox = $ltee;
		$dl->rating = $lrating;
		$dl->slope = $lslope;
		$dl->yardage = $lyard;
		$dl->save();
		$lteeId = $dl->id;
		$courseInfo = [ 'value' => $courseId, 'label' => $course ];
		$mteeInfo   = [ 'value' => $mteeId, 'label' => $mtee . '~ Rating:' . $mrating . ', Slope:' . $mslope . ', Yardage:' . $myard ];
		$lteeInfo   = [ 'value' => $lteeId, 'label' => $ltee . '~ Rating:' . $lrating . ', Slope:' . $lslope . ', Yardage:' . $lyard ];
		return [ 'status' => 'OK', 'course' => $courseInfo, 'mtee' => $mteeInfo, 'ltee' => $lteeInfo ];
	}
	public function XXXgetTournamentScores($tournamentId) {
		$user = Auth::user();
		$da = DB::select("CALL get_tournament_scores(?, -1)", [$tournamentId]); //dd($da);
		$tournament = Collect($da);
		$info['tid'] =  $tournamentId;
		$info['usertype'] = empty($user) ? 'none' : $user->usertype;
		$info = Collect($info);
		return view('Golf.signup', compact('tournament', 'info'));
		// return view('Golf.tournamentscores');
	}
	public function getPlayerGameScoresByTeetime($playerId, $teetime) {
		$da = Score::where([ ['playerId', $playerId], ['teetime', $teetime] ])->select('*')->get();
		if (count($da) == 1) return $da[0];
		else if (count($da) <= 0) return "NO_SCORES";
		else return "FAILED";
	}
	public function getPlayerGameScoresByTid($playerId, $tid) {
		$da = Score::where([ ['playerId', $playerId], ['tournamentId', $tid] ])->select('*')->get();
		if (count($da) == 1) return $da[0];
		else if (count($da) <= 0) return "NO_SCORES";
		else return "FAILED";
	}
	public function getPlayerScores($playerId) {
		$da = DB::select("CALL get_tplayer_scores(?)", [$playerId]);
		$scores = Collect($da);
		return $scores;
	}
	public function updTournamentGroup(Request $da) {
		$tid = $da['tournamentId'];
		$gid = $da['groupId'];
		// DB::update("update Golf.tplayers set group_num=NULL, captain=NULL where tournament_id=? and group_num=?", [$tid, $gid]);
		DB::update("update tplayers set group_num=NULL, captain=NULL where tournament_id=? and group_num=?", [$tid, $gid]);
		$players = explode(',', $da['players']);
		$pid = array_shift($players);
		$dm = Tplayer::find($pid);
		$dm->group_num = $gid;
		$dm->captain = 'Y';
		$dm->update();
		foreach($players as $pid) {
			$dm = Tplayer::find($pid);
			$dm->group_num = $gid;
			$dm->captain = NULL;
			$dm->update();
		}
		return $dm;
	}
	public function getTournamentPlayerListForGrouping($tournamentId) {
		$tplayers = Collect(DB::select("CALL get_tournament_players_for_grouping(?,-1)", [$tournamentId]));
		$tournament = $this->getTournamentInfo($tournamentId);
		return ['tplayers' => $tplayers, 'tournamentInfo' => $tournament ];
	}

	// ==================
	public function tournament() {
		// $da = DB::select('CALL get_tournaments');
		// $gridData = Collect($da);							//dd($gridData);
		return view('Golf.tournament'); //, compact('gridData'));
	}
	public function createTournament() {
		return view('Golf/addtournament');
	}
	public function getTournamentDetails(Request $da) {
		$courseName = $da['course Name'];
		$courses = Collect(Course::where('status', 'A')->select('id', 'name')->get());
		$courseId = -1;
		foreach($courses as $c) {
			if ($c->name == $courseName) {
				$courseId = $c->id;
				break;
			}
		}
		$mtee = $da["men's Tee"];
		$ltee = $da["ladies' Tee"];
		$mteeId = -1;
		$lteeId = -1;
		$tbx = $this->getTeeboxList($courseId);		//dd($tbx);
		$teeboxes = $tbx['options'];
		// $teeboxes = Collect(CourseTee::where('course_id', $courseId)->select('id', 'teebox as name')->get());
		foreach($teeboxes as $t) {
			if (preg_match("@$mtee@", $t['name'])) $mteeId = $t['id'];
			else if (preg_match("@$ltee@", $t['name'])) $lteeId = $t['id'];
			if ($mteeId > 0 and (empty($ltee) or $lteeId > 0)) break;
		}
		if ($lteeId < 0) $lteeId = $mteeId;

		$game = $da['game'];
		$games = Collect(GameName::where('status', 'A')->select('id', 'name')->get());
		$gameId = -1;
		foreach($games as $g) {
			if ($g->name == $game) {
				$gameId = $g->id;
				break;
			}
		}
		return [$courses, $courseId, $teeboxes, $mteeId, $lteeId, $games, $gameId, $tbx['options']];
	}
	public function signupTournament($tournamentId) {
		$user = Auth::user();
		$da = DB::select("CALL get_tournaments(?)", [$tournamentId])[0]; //dd($da);
		$tournament = Collect($da);
		$info['tid'] =  $tournamentId;
		$info['usertype'] = empty($user) ? 'none' : $user->usertype;
		$info = Collect($info);
		return view('Golf.signup', compact('tournament', 'info'));
	}
	public function getPlayerList() {
		$da = Player::where('status', 'A')->select('id', 'lastname', 'firstname')->orderBy('lastname')->get();
		$players = [];
		foreach($da as $d) {
			$p = [];
			$p['id'] = $d['id'];
			$p['name'] = $d->lastname . ", " . $d->firstname;
			$players[] = $p;
		}			 //dd($players);
		return $players;
	}
	public function addTournamentPlayer(Request $da) { Log::info('addTournamentPlayer - signup game', $da->toArray());
		$tmntId = -1;
		$vals = [];
		foreach($da->toArray() as $d) {
			$tmntId = $d['tmntId'];
			$status = $d['status'];
			// $captain = $d['captain'] == 1 ? 1 : null;
			// $grp = $d['grp'] > 0 ? $d['grp'] : null;
			$actv = $d['activity'];
			// $x = [ 'id' => $d['id'], 'tournament_id' => $tmntId, 'year' => $d['year'], 'game_id' => $d['gameId'], 'player_id' => $d['playerId'], 'player' => $d['fullname'], 'grp' => $grp, 'captain' => $captain ];
			$x = [ 'tournament_id' => $tmntId, 'year' => $d['year'], 'game_id' => $d['gameId'], 'player_id' => $d['playerId'], 'player' => $d['fullname'], 'activity' => $actv, 'status' => $status ];
			array_push($vals, $x);
		}
		Log::info('upsert vals', $vals);
		Tplayer::upsert(
			$vals,
			['tournament_id', 'player_id'],
			['activity', 'status'],
		);
		return $this->getTournamentPlayers($tmntId);
	}
	public function XXXXaddTournamentPlayer(Request $da) { Log::info('addTournamentPlayer', $da->toArray());
		$tmntId = $da['tmntId'];
		$playerId = $da['playerId'];
		$player = $da['player'];
		$gameId = $da['gameId'];
		$year = $da['year'];
		$activity = $da->activity;
		// $opt = $da['option'];
		// if ($opt == 'play_dinner') $hole19 = "Yes";
		// else if ($opt == 'play_only') $hole19 = 'No';
		// else if ($opt == 'dinner_only') $hole19 = 'Don';
		$dx = Tplayer::where([ ['tournament_id', $tmntId], ['player_id', $playerId] ])->select('id', 'status')->get(); //return $dx;
		if (count($dx) == 1 and $dx[0]->status == 'A') {
			// $existedPlayer = Collect(DB::select("CALL get_tournament_players(?, ?)", [$tmntId, $playerId])[0]);
			// $existedPlayer = Collect(DB::select("CALL get_tournament_players(?, ?)", [$tmntId, $playerId]));
			// $existedPlayer['count'] = -1;
			// return $existedPlayer;
			return "exist";
		}

	 	if (count($dx) == 1 and ($dx[0]->status == 'D' or $dx[0]->status == 'Y')) {
			$dm = Tplayer::find($dx[0]->id);
			$dm->status = 'A';
			$dm->game_id = $gameId;
			$dm->activity = $activity;
			$dm->update();
		} else if (count($dx) <= 0) {
			$dm = new Tplayer;
			$dm->tournament_id = $tmntId;
			$dm->player_id = $playerId;
			$dm->player = $player;
			$dm->activity = $activity;
			$dm->game_id = $gameId;
			$dm->year = $year;
			$dm->save();
			// Log::info('addTournamentPlayer new player', $dm->toArray());
		}
		// return $this->getTournamentPlayersWithScores($tmntId);
		return $this->getTournamentPlayers($tmntId);
		// $addedPlayer = DB::select("CALL get_tournament_players(?, ?)", [$tmntId, $playerId])[0];
		// $addedPlayer = DB::select("CALL get_tournament_players(?, ?)", [$tmntId, $playerId]);
		// $addedPlayer['count'] = 0;
		// return ['addedPlayer' => $addedPlayer, 'status' => "OK" ];
		// return [ 'status' => "OK" ];
	}
	public function getPlayerInfo() {
		return view('Golf.players');
	}
	private function is_duplicated($dm) {
		$dx = Player::where([ ['firstname', $dm->firstname], ['lastname', $dm->lastname], ['id', '<>', $dm->id] ])->select('id', 'status')->get();
		return count($dx) > 0;
	}
	// public function getTournament($tid) {
	// 	$tournament = Collect(DB::select("Call get_tournaments(?)", [$tid]));	//dd($tournament);
	// 	return ['tournament' => $tournament, 'status' => "OK" ];
	// }
	// public function getTournamentInfo($tournamentId) {
	// 	// $tournament = DB::select('Call get_tournaments(?)', [$tournamentId]);	dd($tournament);
	// 	$tournament = Collect(DB::select("Call get_tournaments(?)", [$tournamentId]));	//dd($tournament);
	// 	return $tournament;
	// }
	public function getTournamentForGrouping($tid) {
		return view('Golf.grouping', compact('tournament'));
	}
	////// new functions /////////////////
	public function getTournamentByDate($dt) {
		$tournament = Tournament::where('start_at', 'like', "$dt%")->get(); //dd(tournament); // dd(DB::getQueryLog());
		return $tournament;
	}
	// public function setPlayerScore (Request $da) { Log:info('setPlayerScore', $da->toArray());
	// 	$id = $da['id'];
	// 	$score = $da['pscore'];
	// 	$dm = Tplayer::find($id);
	// 	$dm->gross_score = $score;
	// 	$dm->update();
	// 	return [ 'ret' => $da, 'status' => "OK"];
	// }
	public function saveTplayerScore (Request $da) { Log:info('saveTplayerScore', $da->toArray());
		$id = $da['id'];
		$score = $da['pscore'];
		$tmntId = $da['tmntId'];
		$x = $this->getRatingSlope($tmntId);
		// Log::info("rating, slope", [$x->rating, $x->slope]);
		$rating = $x->rating;
		$slope = $x->slope;
		$dm = Tplayer::find($id);
		$dm->gross_score = $score;
		$dm->idx_diff = ($score - $rating) * 113 / $slope;
		$dm->update();
		return [ 'ret' => $da, 'status' => "OK"];
	}
	private function getRatingSlope($tmntId) {
		$x = DB::table('course_tees')
			->join('tournaments', 'tournaments.mens_tee_id', 'course_tees.id')
			->where('tournaments.id', $tmntId)
			->select('rating', 'slope')
			->first();
		// Log::info("rating, slope", [$x->rating, $x->slope]);
		return $x; // this is a collection
	}
// 	private function getRatingSlope($tmntId) {
// 		$x = DB::table('course_tees')
// 			->join('tournaments', 'tournaments.mens_tee_id', 'course_tees.id')
// 			->where('tournaments.id', $tmntId)
// 			->get(['rating', 'slope']);
// 			// ->select('rating', 'slope')
// 			// ->first();
// 		Log::info("rating, slope", [$x[0]->rating, $x[0]->slope]);
// 	}
// }
	public function updCourseTee($id, $tag, $val) { Log::info("updCourseTee:id=$id, tag=$tag, val=$val");
		$dm = CourseTee::find($id);
		$dm->$tag = $val;
		$dm->update();
	}
}

?>

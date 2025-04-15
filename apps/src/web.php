<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// Route::view('/apps/watcher', 'apps/watcher')->middleware('auth');

Route::get('/', function () { return view('welcome'); });
// Route::get('/apps/getAttached/{dir}', 'AppsController@getAttached');
// // Route::get('/apps/getAttached/{dir}', function () { Log::info('AppsController@getAttached'); });
Route::group (
  array('prefix' => 'arts'), function () {
    // Route::view('/', 'arts');
    Route::view('/{tag}/{ymd}',       'arts')->where(['tag'=>'(PX)(HY|WX|WW|QG|ZJ|JL)', 'ymd'=>'\d{4}-\d\d-\d\d']);
    Route::view('/{tag}/{ymd}/{qid}', 'arts')->where(['tag'=>'(PX)(HY|WX|WW|QG|ZJ|JL)', 'ymd'=>'\d{4}-\d\d-\d\d', 'qid'=>'\d+']);
    Route::view('/{cat}/{txt}',       'arts')->where(['cat'=>'(aut|tit|txt)']);
    Route::get('/getList',                   'ArtsController@getList');
    Route::get('/getCont/{tag}/{ymd}',       'ArtsController@getCont')->where(['tag'=>'(PX)(HY|WX|WW|QG|ZJ|JL)', 'ymd'=>'\d{4}-\d\d-\d\d']);
    Route::get('/getText/{tag}/{ymd}/{qid}', 'ArtsController@getText')->where(['tag'=>'(PX)(HY|WX|WW|QG|ZJ|JL)', 'ymd'=>'\d{4}-\d\d-\d\d', 'qid'=>'\d+']);
    Route::post('/updText', 'ArtsController@updText');
    Route::post('/logClientPlatform', 'ArtsController@logClientPlatform');
    Route::get('/search/{cat}/{txt}', 'ArtsController@search')->where(['cat'=>'(aut|tit|txt)']);
  });
// Route::view('/arts', function () { return redirect('/apps/arts'); });
// Route::permanentRedirect('/arts', '/apps/arts');
Route::group (
    array('prefix' => 'watcher'), function() {
      Route::view('', 'watcher')->middleware('auth');
      Route::view('gain-percent-chart', 'watcher')->middleware('auth');
      Route::view('gain-loss-chart', 'watcher')->middleware('auth');
      Route::view('weight-chart', 'watcher')->middleware('auth');
      Route::view('stock-chart', 'watcher')->middleware('auth');
      Route::view('list',    'watcher')->middleware('auth');
      Route::get ('getList', 'WatcherController@getList')->middleware('auth');
      Route::get ('getStockPriceList', 'WatcherController@getStockPriceList');
      Route::get ('getPortfolio/{date}', 'WatcherController@getPortfolio');
      Route::post('upd', 'WatcherController@upd');
      Route::post('add', 'WatcherController@add');
      Route::post('del', 'WatcherController@del');
      Route::post('addPNote', 'WatcherController@addPNote');
      Route::post('updPNote', 'WatcherController@updPNote');
      Route::post('delPNote', 'WatcherController@delPNote');
      // Route::get ('loadPositions/{date}', 'WatcherController@loadPositions');
      Route::get ('getPositions/{date}', 'WatcherController@getPositions');
  }
);
// Route::group (
//   array('prefix' => 'reminder'), function() {
//     Route::view('list',    'reminder')->middleware('auth');
//     // Route::view('list', 'reminder');
//     Route::get ('getList', 'ReminderController@getList')->middleware('auth');
//     Route::post('add', 'ReminderController@add')->middleware('auth');
//     Route::post('upd', 'ReminderController@upd')->middleware('auth');
//     Route::post('del', 'ReminderController@del')->middleware('auth');
//     // Route::get ('checkReminder/{date}', 'ReminderController@checkReminder');
//   }
// );
Route::prefix('reminder')
  ->missing(fn() => ['success' => false, 'message' => 'The requested location does not exist'])
  ->controller(ReminderController::class)
  ->group(function() {
    Route::get('getList', 'getList')->middleware('auth');
    Route::post('add', 'add')->middleware('auth');
    Route::post('upd', 'upd')->middleware('auth');
    Route::post('del', 'del')->middleware('auth');
  }
);

Route::group (
  array('prefix' => 'memo'), function() {
    Route::view('list',    'memo')->middleware('auth');
    Route::get ('getList/{screenwidthD13}', 'MemoController@getList'); //->middleware('auth');
    Route::post('add', 'MemoController@add');
    Route::post('upd', 'MemoController@upd');
    Route:: get('del/{id}', 'MemoController@del');
  }
);
Route::group (
  array('prefix' => 'dictionary'), function() {
    Route::view('list', 'dictionary');
    Route::get ('getList', 'DictionaryController@getList')->middleware('auth');
    Route::post('add', 'DictionaryController@add');
    Route::post('upd', 'DictionaryController@upd');
    Route::post('del', 'DictionaryController@del');
  }
);
Route::group (
  array('prefix' => 'expense'), function() {
    Route::view('', 'expense')->middleware('auth');
    Route::view('list', 'expense')->middleware('auth');
    Route::get ('getList', 'ExpenseController@getList'); // controlled in __constructor with middleware('auth');
    Route::get ('getCatsCombo/{catsId}/{subcId}', 'ExpenseController@getCatsCombo');
    Route::get ('getSubcat/{catsId}', 'ExpenseController@getSubcat');
    Route::get ('getPayee/{subcId}',  'ExpenseController@getPayee');
    Route::post('delSpend', 'ExpenseController@delSpend');
    Route::post('addSpend',  'ExpenseController@addSpend');
    Route::post('updSpend',  'ExpenseController@updSpend');
    Route::post('addNewCSP', 'ExpenseController@addNewCSP');
    Route::get ('getPurchasedList/{date}/{payeeId}', 'ExpenseController@getPurchasedList');
    // Route::get ('getPurchasedList/{date}', 'ExpenseController@getPurchasedList');
    // Route::get ('getGCardInfo/{spendId}/{purchasedon}/{cost}', 'ExpenseController@getGCardInfo');
    Route::get ('setStore/{pid}/{payeeId}', 'ExpenseController@setStore');
    Route::get ('getCreditCardSpendings/{startDay}/{endDay}/{dueDay}', 'ExpenseController@getCreditCardSpendings');
    Route::post('setReconcile', 'ExpenseController@setReconcile');
    Route::get ('getGiftCardBalance/{paymId}/{cost}', 'ExpenseController@getGiftCardBalance');
    Route::post('addNewGiftCard', 'ExpenseController@addNewGiftCard');
    Route::post('getScoreId', 'ExpenseController@getScoreId');
    Route::post('getScore', 'ExpenseController@getScore');
    Route::get ('getCourseList', 'ExpenseController@getCourseList');
    // Route::post('login', 'ExpenseController@getScore');
    Route::get ('getSpending/{sid}', 'ExpenseController@getSpending');
  }
);
Route::group (
  array('prefix' => 'exp'), function() {
    // Route::view('', 'exp')->middleware('auth');
    Route::view('', 'exp');
    Route::view('list', 'exp')->middleware('auth');
    Route::get ('getList', 'ExpenseController@getList'); // controlled in __constructor with middleware('auth');
    Route::get ('getCatsCombo/{catsId}/{subcId}', 'ExpenseController@getCatsCombo');
    Route::get ('getSubcat/{catsId}', 'ExpenseController@getSubcat');
    Route::get ('getPayee/{subcId}',  'ExpenseController@getPayee');
    Route::post('delSpend', 'ExpenseController@delSpend');
    Route::post('addSpend',  'ExpenseController@addSpend');
    Route::post('updSpend',  'ExpenseController@updSpend');
    Route::post('addNewCSP', 'ExpenseController@addNewCSP');
    Route::get ('getPurchasedList/{date}/{payeeId}', 'ExpenseController@getPurchasedList');
    Route::get ('getPurchasedList4Exdar/{date}/{payeeId}', 'ExpenseController@getPurchasedList');
    Route::get ('setStore/{pid}/{payeeId}', 'ExpenseController@setStore');
    Route::get ('getCreditCardSpendings/{startDay}/{endDay}/{dueDay}', 'ExpenseController@getCreditCardSpendings');
    Route::post('setReconcile', 'ExpenseController@setReconcile');
    Route::get ('getGiftCardBalance/{paymId}/{cost}', 'ExpenseController@getGiftCardBalance');
    Route::post('addNewGiftCard', 'ExpenseController@addNewGiftCard');
    Route::post('getScoreId', 'ExpenseController@getScoreId');
    Route::post('getScore', 'ExpenseController@getScore');
    Route::get ('getCourseList', 'ExpenseController@getCourseList');
    // Route::get ('testDB/{d1}/{d2}', 'ExpenseController@testDB');
    // Route::get ('testDB/{spendId}', 'ExpenseController@testDB');
    Route::post('login', 'ExpenseController@loginAdmin');
    Route::get ('checkBalance/{pymId}', 'ExpController@checkBalance');
    Route::get ('getGiftCards', 'ExpController@getGiftCards');
    Route::get ('getGiftCardBalances/{paymId}/{giftCardNum}/{prevCardNum}', 'ExpController@getGiftCardBalances');
    Route::get ('getSpending/{spendId}', 'ExpController@getSpending');
  }
);
Route::group (
  array('prefix' => 'shopping'), function() {
    Route::view('list', 'shopping')->middleware('auth');
    Route::get ('getShoppingList', 'ShoppingController@getShoppingList');
    Route::get ('getPurchasedItemsForTheDate/{date}', 'ShoppingController@getPurchasedItemsForTheDate');
    Route::get ('getShoppingDates', 'ShoppingController@getShoppingDates');
    Route::get ('getPurchasedDate/{item}', 'ShoppingController@getPurchasedDate');
    // Route::get ('getLastPurchaseDate', 'ShoppingController@getLastPurchaseDate');
    Route::get ('getThisDatePurchases/{date}', 'ShoppingController@getThisDatePurchases');
    Route::get ('getAllItems', 'ShoppingController@getAllItems');
    Route::post('addPurchase', 'ShoppingController@store');
    Route::post('delPurchase', 'ShoppingController@store');
    Route::post('addClass', 'ShoppingController@store');
    Route::post('addItem',  'ShoppingController@store');
    Route::post('addShoppingItem', 'ShoppingController@addShoppingItem');
    Route::post('delShoppingItem', 'ShoppingController@delShoppingItem');
    Route::post('addPurchasedItem','ShoppingController@addPurchasedItem');
    Route::get ('delPurchasedItem/{pid}','ShoppingController@delPurchasedItem');
    Route::post('addNewItem','ShoppingController@addNewItem');
    Route::post('addNewClass','ShoppingController@addNewClass');
  }
);
Route::group (
  array('prefix' => 'bankstatement'), function() {
    Route::view('list',    'bankstatement')->middleware('auth');
    Route::get('getList', 'BankStatementController@getList'); //->middleware('auth');
    Route::get('getDetails/{bank}/{year}/{month}', 'BankStatementController@getDetails'); //->middleware('auth');
    Route::get('getHoldings/{bank}/{year}/{month}', 'BankStatementController@getHoldings');
  }
);
Route::group (
  array('prefix' => 'holdings'), function() {
    Route::view('list', 'holdings')->middleware('auth');
    Route::get('getDICLists/{bank}/{year}', 'HoldingsController@getDICLists')->middleware('auth');
    Route::get('getHoldings/{bank}/{year}/{month}', 'HoldingsController@getHoldings');
    // Route::get('getDICLists/{bank}/{year}', 'BankStatementController@getDICLists')->middleware('auth');
    // Route::get('getHoldings/{bank}/{year}/{month}', 'BankStatementController@getHoldings');
  }
);
Route::group (
  array('prefix' => 'glucosecheck'), function() {
    // Route::view('',    'glucosecheck')->middleware('auth');
    Route::get ('getList', 'GlucoseCheckController@getList'); //->middleware('auth');
    Route::post('add', 'GlucoseCheckController@add');
    Route::post('upd', 'GlucoseCheckController@upd');
    Route::post('del', 'GlucoseCheckController@del');
  }
);
Route::group (
  array('prefix' => 'bank'), function() {
    Route::view('list',    'bank')->middleware('auth');
    Route::get ('getList', 'BankController@getList'); //->middleware('auth');
    Route::get ('getAcctDetails/{date}/{account}', 'BankController@getAcctDetails');
    Route::post('add', 'BankController@add');
    Route::post('upd', 'BankController@upd');
    Route::post('del', 'BankController@del');
    Route::post('getPrice', 'BankController@getPrice');
  }
);
// Route::group (
  //   array('prefix' => 'calendar'), function() {
    //     Route::get('getAllData/{ystIdx}/{yIdx}', 'CalendarController@getAllData');
    //     Route::get('getC2Gdata/{idx}', 'CalendarController@getC2Gdata');
    //     Route::get('getC2Gdata1/{idx}', 'CalendarController@getC2Gdata');
    //     Route::get('getCalSolarTerm/{idx}', 'CalendarController@getCalSolarTerm');
    //     Route::get('getSolarTerm/{idx}', 'CalendarController@getSolarTerm');
    //     Route::get('getSolar1Term/{idx}', 'CalendarController@getSolarTerm');
    //     Route::get('getNewMoon/{idx}', 'CalendarController@getNewMoon');
    //     Route::get('get1stQuarter/{idx}', 'CalendarController@get1stQuarter');
    //     Route::get('getFullMoon/{idx}', 'CalendarController@getFullMoon');
    //     Route::get('get3rdQuarter/{idx}', 'CalendarController@get3rdQuarter');
    //   }
    // );
    // Route::view('apps/expense', 'apps/expanse');
    // Route::view('apps/watcher', 'watcher');
    
Route::view('/', 'golf');
Route::view('golf/PlayerList', 'golf');
Route::view('golf/EnterScores', 'golf');
Route::view('golf/PGCGroupList', 'golf');
Route::view('golf/TournamentList', 'golf');
Route::view('golf/TournamentScores', 'golf');
Route::view('golf/CourseDetails', 'golf');
Route::view('golf/Signup/{tid}', 'golf');
Route::view('golf/Signup', 'golf');
Route::view('golf/EnterTournamentScores/{tid}', 'golf');
Route::view('golf/TeamCompetition', 'golf');
Route::view('golf/JZsMatch', 'golf');
Route::view('golf/KJsMatch', 'golf');
Route::view('golf/ALsMatch', 'golf');
Route::view('golf/PGCGameList', 'golf');
Route::view('golf/LoadLogPage', 'golf');

Route::group (
  array('prefix' => 'golf'), function() {
    // Route::get('/getUser', function() { return Auth::guard('auth:')->user(); });
    Route::get('getHandicaps/{gameId}', 'GolfGroupingController@getHandicaps');
    Route::get('getMemberList', 'GolfController@getMemberList');
    Route::post('/updMember',    'GolfController@updMember'); //->middleware('auth:auth:');
    Route::post('/addMember',    'GolfController@addMember'); //->middleware('auth:auth:');
    Route::get('/delMember/{pid}/{mid}', 'GolfController@delMember'); //->middleware('auth:auth:');
    Route::get('/getTournament/{tid}', 'GolfController@getTournament');
    Route::get('/getTournamentList',  'GolfController@getTournamentList');
    Route::get('Teebox/{courseId}',   'GolfController@getTeeboxList');
    Route::post('addTournament', 'GolfController@addTournament'); //->middleware('auth:auth:');
    Route::post('updTournament', 'GolfController@updTournament'); //->middleware('auth:auth:');
    Route::post('delTournament', 'GolfController@delTournament'); //->middleware('auth:auth:');
    Route::get('/CourseList',     'GolfController@getCourseList');
    Route::get('TeeboxList/{cid}',  'GolfController@getTeeboxList');
    Route::get('GameNameList',      'GolfController@getGameNameList');
    Route::get('PlayersRanking/{tid}',      'GolfController@getPlayersRanking');
    Route::get('getPlayersForTournament/{tid}', 'GolfController@getPlayersForTournament');
    Route::post('addTournamentPlayer',       'GolfController@addTournamentPlayer');
    Route::get('updTournamentPlayer/{dinner}/{tpId}', 'GolfController@updDinnerTournamentPlayer');
    Route::get('delTournamentPlayer/{tpId}', 'GolfController@delTournamentPlayer');
    Route::get('getTournamentByDate/{dt}', 'GolfController@getTournamentByDate');
    Route::get('getTournamentPlayers/{tid}', 'GolfController@getTournamentPlayers');
    Route::get('getTournamentPlayersWithScores/{tid}', 'GolfController@getTournamentPlayersWithScores');
    Route::post('insGameScore', 'GolfController@insGameScore');
    Route::post('updGameScore', 'GolfController@updGameScore');
    Route::get('updPosition/{tpid}/{pos}', 'GolfController@updPosition')->middleware('auth:auth:');
    Route::get('calcPosition', 'GolfController@calcPosition')->middleware('auth:auth:');
    Route::get('getExCourse',  'GolfController@getExCourse');
    Route::get('UnexpiredTournaments/{gameName}', 'GolfController@getUnexpiredTournaments');
    Route::get('CourseDetails/{courseId}', 'GolfController@getCourseDetails');
    Route::get('CourseYardage/{courseId}/{teeboxId}/{teebox}', 'GolfController@getCourseYardage');
    Route::post('updCourseYardage', 'GolfController@updCourseYardage');
    Route::post('addCourse', 'GolfController@addCourse');
    Route::post('updCourse', 'GolfController@updCourse');
    Route::post('delCourse', 'GolfController@delCourse');
    Route::post('delTeebox', 'GolfController@delTeebox');
    Route::post('addScoreRecord', 'GolfController@addScoreRecord');
    Route::post('updTotalScoreAndNote', 'GolfController@updTotalScoreAndNote');
    Route::get('RoundList/{playerId}', 'GolfController@getRoundList');
    Route::get('delRound/{scoreId}',   'GolfController@delRound');
    Route::get('/getTplayers/{tid}',   'GolfController@getTplayers');
    Route::get('/getTeamMatchPlayers/{tid}/{gameId}/{gameDate}', 'GolfController@getTeamMatchPlayers');
    Route::post('/saveGrouping',  'GolfController@saveGrouping');
    Route::post('/saveTeamMatch', 'GolfController@saveTeamMatch');
    Route::get('PlayedRoundList/{playerId}', 'GolfController@getPlayedRoundList');
    Route::get('Scores/{playerId}', 'GolfController@getScores');
    Route::post('getScoreId', 'GolfController@getScoreId');
    Route::post('getScore', 'GolfController@getScore');
    Route::post('updTplayerActivity', 'GolfController@updTplayerActivity');
    Route::get ('getCourseHandicaps/{courseId}', 'GolfController@getCourseHandicaps');
    Route::post('getCourseYardages', 'GolfController@getCourseYardages');
    Route::get ('getPlayerCount', 'GolfController@getPlayerCount');
    Route::post('login', 'GolfController@login');
    Route::post('createAccount', 'GolfController@createAccount');
    Route::get ('logout', 'GolfController@logout');
    // Route::get('/logout',  function() { return Auth::logout(); });
    Route::get ('getUserType', 'GolfController@getUserType');
    Route::get ('getTournaments/{gameId}', 'GolfController@getTournaments');
    Route::get ('getAllPlayers', 'GolfController@getAllPlayers');
    Route::get ('getCandidatePlayers/{tmntId}/{gameId}', 'GolfController@getCandidatePlayers');
    Route::post('addTeamMatchPlayer', 'GolfController@addTeamMatchPlayer');
    Route::get ('delTeamMatchPlayer/{id}/{tmntId}/{gameId}/{gameDate}', 'GolfController@delTeamMatchPlayer');
    Route::post('addUnsignedTeamMatchPlayers', 'GolfController@addUnsignedTeamMatchPlayers');
    // Route::post('setPlayerScore', 'GolfController@setPlayerScore');
    Route::post('saveTplayerScore', 'GolfController@saveTplayerScore');
    Route::post('setTmntNumGroups', 'GolfController@setTmntNumGroups');
    Route::post('addTplayers', 'GolfController@addTplayers');
    Route::get ('getPlayers', 'GolfController@getPlayers');
    Route::post('upsertTplayers', 'GolfController@upsertTplayers');
    Route::post('updTeamMatchTplayer', 'GolfController@updTeamMatchTplayer');
    Route::get ('delTplayer/{Id}/{tmntId}', 'GolfController@delTplayer');
    Route::get ('getPlayerGameScores/{playerId}/{gameId}', 'GolfController@getPlayerGameScores');
    Route::get ('getUserGuide/{pagename}', 'GolfController@getUserGuide');
    // Route::get ('getUserGuideId/{pagename}', 'GolfController@getUserGuideId');
    Route::post('saveUserGuide', 'GolfController@saveUserGuide');
    Route::post('updUserGuide', 'GolfController@updUserGuide');
    Route::post('addPGCMembership', 'GolfController@addPGCMembership');
    Route::get ('getPGCMemberList/{year}', 'GolfController@getPGCMemberList');
    Route::get ('getPGCGamePlayers/{tmntId}/{gameId}/{year}', 'GolfController@getPGCGamePlayers');
    Route::get ('getPGCGames/{gameName}', 'GolfController@getPGCGames');
    Route::get ('getPGCRules/{gameId}', 'GolfController@getPGCRules');
    Route::get ('delPGCTplayer/{id}', 'GolfController@delPGCTplayer');
    Route::get ('getPGCNotGroupedPlayers/{tmntId}/{gameId}/{year}', 'GolfController@getPGCNotGroupedPlayers');
    Route::post('addPGCTplayer', 'GolfController@addPGCTplayer');
    Route::get ('getGroupScores/{tmntId}/{ttim}/{curseId}/{teebox}', 'GolfController@getGroupScores');
    Route::post('updGScore', 'GolfController@updGScore');
    Route::post('insGScore', 'GolfController@insGScore');
    Route::get ('getCourseInfo/{tmntId}/{courseId}/{teename}', 'GolfController@getCourseInfo');
    Route::post('getPStrokes', 'GolfController@getPStrokes');
    Route::get ('doubleBack9/{playerId}/{tmntId}', 'GolfController@doubleBack9');
    Route::get ('savePoyg/{poyg}/{tid}', 'GolfController@savePoyg');
    Route::get ('loadLogPage', 'LoadLogPageController@index');
    Route::get ('checkReminder/{date}', 'GolfController@checkReminder');
    Route::get ('updCourseTee/{id}/{tag}/{val}', 'GolfController@updCourseTee');
    Route::post('addTeebox', 'GolfController@addTeebox');
    Route::get ('loadKJsFile/{matchId}', 'GolfGroupingController@loadKJsFile');
    // Route::post('groupingFinalized', 'GolfGroupingController@groupingFinalized');
    Route::get ('getAliases/{gameId}', 'GolfGroupingController@getAliases');
    Route::get ('getKJAliases/{gameId}', 'GolfGroupingController@getKJAliases');
    Route::get ('getMatchGroupingPlayers', 'GolfGroupingController@getMatchGroupingPlayers');
    Route::post('addNewSimPlayer', 'GolfGroupingController@addNewSimPlayer');
    Route::post('moveToGrouping', 'GolfGroupingController@moveToGrouping');
    Route::post('moveOutGrouping', 'GolfGroupingController@moveOutGrouping');
    Route::post('moveToGrouped', 'GolfGroupingController@moveToGrouped');
    Route::post('moveOutGrouped', 'GolfGroupingController@moveOutGrouped');
    Route::post('addupdMatchGroups', 'GolfGroupingController@addupdMatchGroups');
    Route::post('saveGrouping', 'GolfGroupingController@saveGrouping');

    //Route::get ('getKjAliases', 'LoadKJGolfxlsxController@getKJAliases');
    //Route::get ('loadGameData', 'LoadKJGolfxlsxController@loadGameData');
    Route::get ('getKjAliases/{gameDate}', 'GolfGroupingController@getKJAliases');
    //Route::get ('getKjNewPlayer', 'GolfController@getKjNewPlayer');
    // Route::get ('getKjGameDates', 'GolfController@getKjGameDates');
    Route::get ('getKjGameDataByDate/{date}', 'GolfController@getKjGameDataByDate');
    Route::get ('getKjGamePlayers', 'GolfController@getKjGamePlayers');
    Route::get ('getKjGameDataByMpId/{mpId}', 'GolfController@getKjGameDataByMpId');
  }
);
Route::group (
  array('prefix' => 'bankstatementloader'), function() {
    Route::get ('getCreditCardData/{date}/{bank}', 'StatementsController@getCreditCardData')->middleware('auth');
    Route::get ('loadFidelityMonthlyStatements/{ymon}', 'StatementsController@loadFidelityMonthlyStatements')->middleware('auth');
    Route::post('addAssets', 'StatementsController@addAssets')->middleware('auth');
    Route::post('addHoldings', 'StatementsController@addHoldings')->middleware('auth');
    Route::post('addActivity', 'StatementsController@addActivity')->middleware('auth');
    Route::get ('loadMonthlyStatementsBOA/{ymon}', 'StatementsBOAController@loadMonthlyStatements')->middleware('auth');
    Route::post('addActivityBOA', 'StatementsBOAController@addActivity')->middleware('auth');
    Route::post('addNotes', 'StatementsBOAController@addNotes')->middleware('auth');
    Route::get ('loadMonthlyStatementChase/{ymon}', 'StatementChaseController@loadMonthlyStatement')->middleware('auth');
    Route::get ('loadChaseBkg/{ymon}', 'ChaseBrockerageIntradayController@getData')->middleware('auth');
    // Route::get ('loadYearlyStatementNAC/{ymon}', 'StatementNACController@loadYearlyStatement')->middleware('auth');
    Route::post('saveData', 'ChaseBrockerageIntradayController@saveData')->middleware('auth');
    Route::post('addChecking', 'StatementChaseController@addActivity')->middleware('auth');
    Route::post('addSavings',  'StatementChaseController@addActivity')->middleware('auth');
    Route::post('getMatchedSpends', 'StatementsController@getMatchedSpends')->middleware('auth');
    Route::get ('setPostDate/{id}/{postDate}', 'StatementsController@setPostDate')->middleware('auth');
    // Route::get ('getCreditCardSpendings/{userId}/{startDay}/{endDay}/{dueDay}', 'StatementsController@getCreditCardSpendings');
    // Route::get ('getSpending/{sid}', 'ExpController@getSpending');
  }
);
Route::group (
  array('prefix' => 'healthtest'), function() {
    Route::view('', 'healthtest');
    Route::view('list', 'healthtest')->middleware('auth');
    Route::get ('getList', 'HealthTestController@getList'); //->middleware('auth');
  }
);
Route::group (
  array('prefix' => 'totext'), function() {
    Route::view('', 'totext');
    Route::view('totext', 'totext')->middleware('auth');
    Route::get ('getText/{filename}', 'ToTextController@getText'); //->middleware('auth');
  }
);
Route::group (
  array('prefix' => 'yalipics'), function() {
    Route::view('list', 'yalipics'); 
    Route::get ('getList', 'YalipicsController@getList'); //->middleware('auth');
  }
);
Route::group (
  array('prefix' => 'tvmanager'), function() {
    // Route::view('list', 'tvmanager'); 
    Route::get ('getList/{hours}', 'TvController@getList');
    Route::post('del', 'TvController@del');
  }
);
Route::group (
  array('prefix' => 'todo'), function() {
    Route::view('list', 'todo');
  }
);
Route::group (  // set up here to show login page
  array('prefix' => 'apps'), function() {
    // Route::view('todo', 'apps')->middleware('auth');
    Route::view('apps', 'apps')->middleware('auth');
    Route::view('glucosecheck', 'apps')->middleware('auth');
    // Route::view('expense', 'apps')->middleware('auth');
    Route::view('shopping', 'apps')->middleware('auth');
    Route::view('reminder', 'apps')->middleware('auth');
    Route::view('dictionary', 'apps');
    Route::view('memo', 'apps')->middleware('auth');
    Route::view('watcher', 'apps')->middleware('auth');
    Route::view('bankstatement', 'apps')->middleware('auth');
    Route::view('holdings', 'apps')->middleware('auth');
    Route::view('bank', 'apps')->middleware('auth');
    Route::view('holidays', 'apps');
    Route::view('arts', 'apps');
    // Route::view('golf', 'apps')->middleware('auth:auth:');
    // Route::view('golf', 'apps');
    Route::view('bankstatementloader', 'apps');
    Route::view('exlist', 'apps')->middleware('auth');
    Route::view('healthtest', 'apps')->middleware('auth');
    Route::view('yalipics', 'apps');
    Route::view('tvmanager', 'apps');
  }
);

// Route::view('/apps/expense', 'apps');
// Route::view('/apps/shopping', 'apps');
// Route::view('/apps/arts', 'apps/arts');

Auth::routes();

// Route::get('/home', 'HomeController@index')->name('home');   //use to redirect to registration
// Route::get('/home', 'HomeController@index')->name('');   //use to redirect to registration
Route::get('/home', 'HomeController@index');   //use to redirect to registration
Route::get('/apps', 'HomeController@index')->name('apps');
// Route::get('/getUsertype', 'HomeController@getUsertype');
Route::get('/auth/getUsertype', function() {
  return ['user' => auth()->user(), 'status' => "OK" ];
});
// Route::get('/apps/golf', 'HomeController@golf')->name('apps/golf');

Route::get('/logout', function() {
  // $usertype = auth()->user()->usertype;
  // Log::info("logout usertype=$usertype");
  // return Auth::logout(); 
  Auth::logout(); 
  $user = auth()->user();
  Log::info("logout user=$user");
  return ['status' => "OK"];
  // 'HomeController@index';
});
// Route::get('/login',  function() { return Auth::login(); });
// Route::get('/login', 'HomeController@loginAdmin');
// Route::get('/auth/login',  function() { return 'auth/LoginController@loginAdmin' }); 
// Route::post('/apps/loginAdmin',  'Auth/LoginController@login'); 
Route::post('/apps/loginAdmin', 'AppsController@loginAdmin'); 
// Route::post('/login', 'Auth/LoginController@loginAdmin'); 
// Route::post('/apps/loginAdmin', function() { return route('login'); }); 

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// Route::get('/admin', [HomeController::class, 'golfAdminHome'])->name('golf.admin.home')->middleware('usertype');
// Route::get('/admin', 'HomeController@golfAdminHome')->name('golf.admin.home')->middleware('auth:');
// Route::get('/admin', 'HomeController@golfAdminHome')->name('golf.admin.home')->middleware('gadmin');
// Route::get('/admin', 'HomeController@golfAdminHome')->name('golf.admin.home')->middleware('auth');

// use Illuminate\Http\Request;
 
// Route::get('/token', function (Request $request) {
//     $token = $request->session()->token();
 
//     $token = csrf_token();
 
//     // ...
// });
// Route::get('LoadLogPage', ['LoadLogPageController@index']);
// Route::post('/users', [UserController::class, 'store'])->name('users.store');
// Route::get('/users', [UserController::class, 'getUserList'])->name('users.getUserList');

Route::group (
  array('prefix' => 'users'), function() {
    Route::get ('getUserList', 'UserController@getUserList'); //->middleware('auth');
    Route::get ('del/{id}', 'UserController@deleteById'); //->middleware('auth');
    Route::post('upd', 'UserController@updateUser'); //->middleware('auth');
    Route::post('add', 'UserController@store')->middleware('auth');
  }
);
Route::group (
  array('prefix' => 'DevTest'), function() {
    Route::get('processKjExcel/{xlsxfile}', 'DevTestController@ProcessKjExcel');
  }
);

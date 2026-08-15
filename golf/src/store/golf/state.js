export default {
  // tournament: { game: 'unknown', disptm: '00-00 00:00' },
  tournament: null,
  user: null,
  // userId: -1,
  usertype: null,
  // isAdmin: false,
  // doGroup: false,
  pageTitle: '普林斯顿高尔夫俱乐部',
  tmntScores: {},
  holes: [],
  homeNotes: [
    'Club Index Algorithm',
    'Handicap Differential: (gross_score - course_rating) * 113 / course_slope * 0.96',
    'Gross Ranking(Tournament Based): The average of the tied positions, e.g. 77, 77, 78, 78, 78, 80, ... => 1.5, 1.5, 4, 4, 4, ... ' +
    'are the gross ranking for the first 5 players. This will be used for the points of playoff ~ points = 17 - gross ranking.',
    'Club Index: The average of the best handicap differentials of the half number of the tournaments scores within ' +
    'the last 2 year e.g. 3 of 5 best will be counted if you played 5 tournaments in the last 2 years time period ' +
    'from the time you check). The handicap differential of the current tournament is exclusive.',
    'Net Score: Gross Score - Club Index',
    'Player of Year(PoY) Points: The sum of the PoY points of the 3 best tournaments of the year played (points = 17 - Gross Ranking)'
  ],
  memberListNotes: [
    'Page Notes',
    'Regular user: click on the name will show the tournament Performance',
    'If you want to play a round and keep the record click on your name. You can check the play records as well by clicking the green button to the right of your name',
    'To revise a round, e.g. change course, tee time, note etc. click on play button after your revise - this will update the round info and show number pad to record the stroks for each hole',
    'Click on the right yellow icon to your name. You can check the your scores for your previous rounds',
    '<b>H:</b> Honorary Members ($1000)',
    '<b>R:</b> Regular Member ($100)',
    '<b>C:</b> Couple Member ($80 each)',
    '<b>D:</b> Developer Member ($50 introduces at least 3 new members)',
    '<b>N:</b> Non-member - plays tournament only ($30)',
    '<b>G:</b> Guest - plays tournament only ($30)',
    'Admin(need to login): click on the name to edit, on X to delete the member'
  ],
  tmntListNotes: [
    'Regular user: Click on the expired tournament -- it will show the scores/ranking/index for tournaments; on active tournament will show the signup page for the tournament',
    'Admin(need to login): can edit/delete the unexpired(active) tournaments or remove players from the acitve tournaments'
  ],
  tmntScoreNotes: [
    'Click on the top to select for Gross Score, Gross Ranking, Net Score, Points of the Player of Year, Club Index so up to this tournament',
    'Click on the icon on the right for the score/ranking details of the player'
  ],
  signup_notes: [
    'This is the signup list. click on the icon to the left of avatar to change your go-to-dinner status',
    'The 1st circle on the left to the name is this year net progress(current club index - year begin club index), the 2nd is the playoff point, the 3rd circle is the current club index',
    'For admin only -- To remove the player from the tournament, you have to login first and click on the cross "X" icon'
  ],
  enterTmntScoresNotes: [
    'Click on your name to enter your tournament scores',
    'You may enter stroks for 18 holes or just strokes for front and back 9 holes'
  ],
  enterScoresNotes: [
    'Enter your game scores',
    'The first circle is the front 9 score, 2nd back 9 score, the 3rd total stroks and the 4th is the tournament award',
    'click on the circles to enter your games scores. you can always re-enter the scores if there are mistakes',
    'click on the player names to set the rewords -- G1=Gross 1st, G2=Gross 2nd, N3=Net 3rd, LD=Longest Drive, KP=Closet to Pin, etc'
  ],
  course_details_notes: [
    'Revise on the course name with the real golf course name at the top',
    'Click the button at the middle labeled with "YARDAGE FOR ..." to switch the teebox',
    'Click on the cell which you want to revise teebox, rating, slope, yardage, with the targeted golf course information',
    'Click on the cell which you want to revise hole pars',
    'Click on the cell which you want to revise hole yards',
    'Click on the cell which you want to revise hole handicaps'
  ],
  course_manager_notes: [
    'Click on "SELECT COURSE" button to select golf course',
    'then clcick on the interested course to edit or delete or add the similar one by revise the related items'
  ],
  grouping_notes: [
    '<b>This page is for game orgnizer to group players</b>',
    'assuming:',
    'each group is consist of 4 player including one captain',
    'first group teeing off at tournament start time and 10 minutes apart for the following groups in order',
    'simply click "+" to add player to the first available group - that is the first group which has less than 4 players -- you can adjust the groups by doing the following:',
    'click the right side to the grouped player - the dropdown - sign and click on G^x to move the player to group x',
    'on the same dropdown, click on red "C" to assign the captain'
  ],
  team_match_top_notes: [
    '<b class="text-bold text-h6">Team Match Notes</b>',
    '<b>NOTES:</b> Matches are daily basis, meaning that there is at most one match in the day; a match can have several games which could take place at different courses.',
    '<b>Create New Match: </b> clicking on it will popup matche creatioln dialog and do as following:',
    '<b>A.</b> if matches take place on one course: do: <br /> \
      <b class="q-pl-md">1.</b> setup number of groups; <br /> \
      <b class="q-pl-md">2.</b> setup tee time gap in minutes; <br />\
      <b class="q-pl-md">3.</b> setup first teetime, green fee, course, man\'s tee, lady\'s tee, note, etc.',
    '<b>B.</b> matches take place on different courses: <br /> \
      <b class="q-pl-md">do A.</b> for each course.',
    '<b>C.</b> To delete or revise the games in a match click on the "删除/修改" button to the match date the click on the game you to delete/revise and go from there.',
    '<b>D.</b> Click on the Match Date which you are interestd, it will show the match details like player list, groups/teams, courses, tees and tee times, etc. on the next page'
  ],
  team_match_groups_notes: [
    '<b class="text-bold text-h6">Team Match Group Notes</b>',
    '<b>Groups of the Match:</b>',
    '<b>A. Add Players to the Match</b>:',
    'Click on the right button(labeled "# players") at bottom to add players to the match (you can select multiple players from a name list), the added players are non-grouped players showing at the bottom',
    '<b>B. Grouping and Teaming up</b>:',
    '<b class="q-pl-xs">1.</b> Click on the button(labeled "+") to the right of the non-grouped player name(at bottom) and go from there straight forward: to add to group and teamA/B player 1 or 2',
    '<b class="q-pl-xs">2.</b> Click on every left icon(tree) to the left of the grouped player name witch will remove the player from the group and she/he will be the non-grouped player showing at the bottom',
    '<b class="q-pl-xs">3.</b> Click on every right button(labeld "x") to the left of the non-grouped player name(at the botton) witch will drop the player from the match',
    // '<b class="q-pl-xs">4.</b> Click on every right button(labeld "+") to the right of the non-grouped player (at the bottom) will show adding player dialog to the group/team and following as the app goes',
    '<b>C. Input team points</b>: Click on the blue or red button to add team score for the match',
    '<b>D. Input individual scores</b>: Click on the circle to add the player\'s score for the match',
    '<b>E. Average Score</b><br />The very right round button showing the player\'s average score, click on it will show scores of all matches used for average:',
  ],
  page: 'home',
  playerCount: 0,
  dinnerCount: 0,
  sideNote: '',
  // golfUserType: null,
}

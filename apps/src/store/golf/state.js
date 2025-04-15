export default {
  // tournament: { game: 'unknown', disptm: '00-00 00:00' },
  tournament: {},
  user: null,
  // userId: -1,
  // userype: 'none',
  // isAdmin: false,
  // doGroup: false,
  // pageTitle: '普林斯顿高尔夫俱乐部',
  pageTitle: null,
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
    'Member List Notes',
    'Regular user: click on the name will show the tournament Performance',
    'If you want to play a round and keep the record click on your name. You can check the play records as well by clicking the green button to the right of your name',
    'To revise a round, e.g. change course, tee time, note etc. click on play button after your revise - this will update the round info and show number pad to record the stroks for each hole',
    'Click on the right yellow icon to your name. You can check the your scores for your previous rounds',
    'H: Honorary Members ($1000)',
    'R: Regular Member ($100)',
    'C: Couple Member ($80 each)',
    'D: Developer Member ($50 introduces at least 3 new members)',
    'N: Non-member plays tournament only ($30)',
    'Admin(need to login): click on the name to edit, on X to delete the member'
  ],
  tmntListNotes: [
    'Tournament List Notes',
    'Regular user: Click on the expired tournament -- it will show the scores/ranking/index for tournaments; on active tournament will show the signup page for the tournament',
    'Admin(need to login): can edit/delete the unexpired(active) tournaments or remove players from the acitve tournaments'
  ],
  tmntScoreNotes: [
    'Click on the top to select for Gross Score, Gross Ranking, Net Score, Points of the Player of Year, Club Index so up to this tournament',
    'Click on the icon on the right for the score/ranking details of the player'
  ],
  signupNotes: [
    'Signup Notes',
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
  teamMatchNotes: [
    'Team Match Notes',
    'X The first circle is the front 9 score, 2nd back 9 score, the 3rd total stroks and the 4th is the tournament award',
    'X click on the circles to enter your games scores. you can always re-enter the scores if there are mistakes',
    'X click on the player names to set the rewords -- G1=Gross 1st, G2=Gross 2nd, N3=Net 3rd, LD=Longest Drive, KP=Closet to Pin, etc'
  ],
  courseInfoNotes: [
    'Course Info Notes',
    'Revise on the course name with the real golf course name at the top',
    'Click the button at the middle labeled with "YARDAGE FOR ..." to switch the teebox',
    'Click on the cell which you want to revise teebox, rating, slope, yardage, with the targeted golf course information',
    'Click on the cell which you want to revise hole pars',
    'Click on the cell which you want to revise hole yards',
    'Click on the cell which you want to revise hole handicaps'
  ],
  courseManagerNotes: [
    'Click on "SELECT COURSE" button to select golf course',
    'then click on the interested course to edit or delete',
    'TO ADD a new course:',
    '  1. Select any course',
    '  2. Change the course name',
    '  3. Save it',
    '  4. Reload the new course by selecting the new course name',
    '  5. Add info for new course',
    'Revise on the course name with the real golf course name at the top',
    'Click the button at the middle labeled with "YARDAGE FOR ..." to switch the teebox',
    'Click on the cell which you want to revise teebox, rating, slope, yardage, with the targeted golf course information',
    'Click on the cell which you want to revise hole pars',
    'Click on the cell which you want to revise hole yards',
    'Click on the cell which you want to revise hole handicaps'
  ],
  groupingNotes: [
    'this page is for game orgnizer to group players',
    'assuming:',
    'each group is consist of 4 player including one captain',
    'first group teeing off at tournament start time and 10 minutes apart for the following groups in order',
    'simply click "+" to add player to the first available group - that is the first group which has less than 4 players -- you can adjust the groups by doing the following:',
    'click the right side to the grouped player - the dropdown - sign and click on G^x to move the player to group x',
    'on the same dropdown, click on red "C" to assign the captain'
  ],
  page: 'home',
  playerCount: 0,
  dinnerCount: 0,
  sideNote: ''
}

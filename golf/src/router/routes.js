const routes = [
  {
    path: '/', component: () => import('../../src/layouts/MyLayout.vue'),
    children: [
      { path: '', component: () => import('../../src/pages/MyIndex.vue') },
      { name: 'PlayerList', path: 'PlayerList', component: () => import('../../src/pages/PlayerList.vue') },
      { path: 'TournamentList', component: () => import('../../src/pages/TournamentList.vue') },
      { path: 'Signup', component: () => import('../../src/pages/SignUp.vue') },
      { path: 'Signup/:tid', component: () => import('../../src/pages/SignUp.vue') },
      { path: 'PGCGroupList', component: () => import('../../src/pages/PGCGroupList.vue') },
      { path: 'TeamCompetition', component: () => import('../../src/pages/TeamCompetition.vue') },
      // { path: 'CourseDetails', component: () => import('../../src/pages/CourseDetails.vue') },
      { path: 'TournamentScore', component: () => import('../../src/pages/TournamentScore.vue') },
      // { path: 'EnterScores', component: () => import('../../src/pages/EnterScores.vue') },
      // { path: 'CourseInfo', component: () => import('../../src/pages/CourseInfo.vue') },
      { path: 'CourseDetails', component: () => import('../../src/pages/CourseDetails.vue') },
      // { path: 'CourseDetails', component: () => import('../../src/pages/CourseDetailsDesk') },
      // { path: 'TeamMatch/13', name: 'teamMatch', params: { gameId: 13 }, component: TeamMatch },
      // { path: 'DKsMatch', component: () => import('../../src/pages/DKsMatch') }
      // { path: 'PlayRounds', component: () => import('../../src/pages/PlayRounds') }
      // { path: 'AddNewCourse', component: () => import('../../src/pages/AddNewCourse') },
      // { path: 'login', component: () => import('../../src/pages/Login') }
      // { path: 'JZsMatch', name: 13, component: () => import('../../src/pages/TeamMatch') },
      // { path: 'KJsMatch', name: 14, component: () => import('../../src/pages/TeamMatch') },
      { path: '/:match', component: () => import('../../src/pages/TeamMatch.vue') },
      // { path: 'PGCGames', component: () => import('../../src/pages/PGCGames') },
      { path: 'PGCGameList', component: () => import('../../src/pages/PGCGameList.vue') },
      { path: 'LoadLogPage', component: () => import('../../src/pages/ShowLogPage.vue') },
      // { path: 'DevTest', component: () => import('../../src/pages/DevTest') },
    ]
  },
    // Always leave this as last one,
  // but you can also remove it
  // { path: '/:catchAll(.*)*', component: () => import('../../src/pages/Error404.vue') }
  // { path: '/:pathMatch(.*)*', name: 'not-found', component: '../../src/pages/Error404.vue' }
  { path: '/:catchAll(.*)*', component: () => import('../../src/pages/Error404Page.vue') }
  // { path: '/:pathMatch(.*)*', component: () => import('../../src/pages/Error404.vue') }

]

export default routes

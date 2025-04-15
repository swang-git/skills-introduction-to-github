const routes = [
  {
    path: '',
    component: () => import('layouts/MyLayout.vue'),
    children: [
      { path: '', component: () => import('pages/MyIndex') },
      { name: 'PlayerList', path: 'PlayerList', component: () => import('pages/PlayerList') },
      { path: 'TournamentList', component: () => import('pages/TournamentList') },
      { path: 'Signup', component: () => import('pages/SignUp') },
      { path: 'Signup/:tid', component: () => import('pages/SignUp') },
      { path: 'PGCGroupList', component: () => import('pages/PGCGroupList') },
      { path: 'TeamCompetition', component: () => import('pages/TeamCompetition') },
      // { path: 'CourseDetails', component: () => import('pages/CourseDetails') },
      { path: 'TournamentScore', component: () => import('pages/TournamentScore') },
      // { path: 'EnterScores', component: () => import('pages/EnterScores') },
      // { path: 'CourseInfo', component: () => import('pages/CourseInfo') },
      { path: 'CourseDetails', component: () => import('pages/CourseDetails') },
      // { path: 'CourseDetails', component: () => import('pages/CourseDetailsDesk') },
      // { path: 'TeamMatch/13', name: 'teamMatch', params: { gameId: 13 }, component: TeamMatch },
      // { path: 'DKsMatch', component: () => import('pages/DKsMatch') }
      // { path: 'PlayRounds', component: () => import('pages/PlayRounds') }
      // { path: 'AddNewCourse', component: () => import('pages/AddNewCourse') },
      // { path: 'login', component: () => import('pages/Login') }
      // { path: 'JZsMatch', name: 13, component: () => import('pages/TeamMatch') },
      // { path: 'KJsMatch', name: 14, component: () => import('pages/TeamMatch') },
      { path: '/:match', component: () => import('pages/TeamMatch') },
      // { path: 'PGCGames', component: () => import('pages/PGCGames') },
      { path: 'PGCGameList', component: () => import('pages/PGCGameList') },
      { path: 'LoadLogPage', component: () => import('pages/ShowLogPage') },
      // { path: 'DevTest', component: () => import('pages/DevTest') },
    ]
  },
    // Always leave this as last one,
  // but you can also remove it
  // { path: '/:catchAll(.*)*', component: () => import('pages/Error404.vue') }
  // { path: '/:pathMatch(.*)*', name: 'not-found', component: 'pages/Error404.vue' }
  { path: '/:catchAll(.*)*', component: () => import('pages/Error404Page.vue') }
  // { path: '/:pathMatch(.*)*', component: () => import('pages/Error404.vue') }

]

export default routes

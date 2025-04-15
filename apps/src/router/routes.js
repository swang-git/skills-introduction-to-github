
// console.log(' window.location.href=', window.location.pathname)
// const appname = window.location.pathname.replace('/', '').replace('/', '')
// const apps = ['apps', 'exlist', 'memo', 'reminder', 'bank', 'bankstatement', 'glucosecheck', 'shopping', 'watcher', 'arts', 'dictionary']
const routes = [
  {
    // path: '/', component: () =>  apps.includes(appname) ? import('layouts/AppsLayout') : import('layouts/GolfLayout.vue'),
    path: '/', component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', component: () => import('pages/Index.vue') },
      { path: 'exlist', component: () => import('../../exp/exlist.vue') },
      { path: 'shopping', component: () => import('../../shopping/Shopping.vue') },
      { path: 'reminder', component: () => import('../../reminder/relist.vue') },
      { path: 'memo', component: () => import('../../memo/melist.vue') },
      { path: 'watcher', component: () => import('../../watcher/Watcher.vue') },
      { path: 'bankstatement', component: () => import('../../bankstatement/BankStatement.vue') },
      { path: 'holdings', component: () => import('../../holdings/Holdings.vue') },
      // { path: 'bank', component: () => import('../../bank/Bank.vue') },
      { path: 'dictionary', component: () => import('../../dictionary/Dictionary.vue') },
      { path: 'holdings', component: () => import('../../holdings/Holdings.vue') },
      // { path: 'stock-chart', component: () => import('../../watcher/ChartStock') },
      // { path: 'gain-loss-chart', component: () => import('../../watcher/ChartGainLoss') },
      // { path: 'gain-percent-chart', component: () => import('../../watcher/ChartGainPercent') },
      // { path: 'weight-chart', component: () => import('../../watcher/ChartWeight') },
      { path: 'glucosecheck', component: () => import('../../glucosecheck/glulist.vue') },
      // { path: 'recon', component: () => import('../../bankstatementloader/ReconFidelityCC.vue') },
      { path: 'bankstatementloader', component: () => import('../../bankstatementloader/Loader.vue') },
      { path: 'painting', component: () => import('../../drawing/Drawing') },
      // { path: 'todo', component: () => import('../../todo/shlist') },
      // { path: 'year-charts', component: () => import('../../exp/ChartYear') },
      // { path: 'mnth-charts', component: () => import('../../exp/Charts/ChartMonth') },
      // { path: 'cats-charts', component: () => import('../../exp/Charts/ChartCats') },
      // { path: 'golf', component: () => import('../../golf/GolfHome') },
      // { path: 'arts', component: () => import('../../arts/ArtsHome.vue') },
      // { name: 'cont', path: '/:tag/:ymd', component: () => import('../../arts/ArtsCont') },
      // { name: 'text', path: '/:tag/:ymd/:qid', component: () => import('../../arts/ArtsText') },
      // { name: 'search', path: '/:cat/:txt', component: () => import('../../arts/ArtsCont') },
      // { name: 'editTxt', path: '/edit/:tag/:ymd/:qid', component: () => import('../../arts/ArtsEdit') },
      // { name: 'editFlw', path: '/edit/:tag/:ymd/:qid/:flwIdx', component: () => import('../../arts/ArtsEdit') },
      { path: 'htlist', component: () => import('../../healthtest/htlist') },
      { path: 'totext', component: () => import('../../totext/ToText') },
      { path: 'yalipics', component: () => import('../../yalipics/PicList') },
      { path: 'tvmanager', component: () => import('../../tvmanager/tvlist') },


      // { path: 'golf', component: () => import('../../golf/GolfHome') },
      // { path: 'golf/:match', component: () => import('../../golf/TeamMatch') },
      // { path: 'golf/:match', component: () => import('../../golf/TeamMatch') },
      // { path: '/apps', component: () => import('layouts/AppsLayout') },
      // { path: 'MemberList', component: () => import('../../golf/MemberList') },
      // { path: 'TournamentList', component: () => import('../../golf/TournamentList') },
      // { path: 'CourseDetails', component: () => import('../../golf/CourseDetails') },
      // { path: 'Signup', component: () => import('../../golf/Signup') },
    ]
  },

  // Always leave this as last one,
  // but you can also remove it
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/Error404.vue')
  }
]

export default routes

export default [
  {
    path: '/',
    component: () => import('layouts/ArtsHome.vue'),
    children: [
      { name: '', path: '', component: () => import('pages/Index.vue') },
      { name: 'cont', path: '/:tag/:ymd', component: () => import('pages/ArtsCont.vue') },
      { name: 'text', path: '/:tag/:ymd/:qid', component: () => import('pages/ArtsText.vue') },
      // { name: 'search', path: '/:cat/:txt', component: () => import('pages/ArtsCont.vue') },
      { name: 'editTxt', path: '/edit/:tag/:ymd/:qid', component: () => import('pages/ArtsEdit.vue') },
      { name: 'editFlw', path: '/edit/:tag/:ymd/:qid/:flwIdx', component: () => import('pages/ArtsEdit.vue') }
    ],
  },

  {
    // Always leave this as last one
    // path: '*',
    // component: () => import('pages/Error404')
    // path: '/:catchAll(.*)*', component: () => import('pages/Error404.vue')
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue'),
  },
]

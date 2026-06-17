const routes = [
  {
    path: '/',
    component: () => import('../../src/layouts/MainLayout.vue'),
    children: [
      { path: '', component: () => import('../../src/pages/PicDialog.vue') },
      // { path: '/clean', component: () => import('layouts/MainLayout.vue') }
      // { path: 'yali/clean', component: () => import('pages/IndexPage.vue') }
      // { name: '/', path: '/:cleanp', component: () => import('pages/IndexPage.vue') },
    ]
  },

  // Always leave this as last one,
  // but you can also remove it
  {
    path: '/:catchAll(.*)*',
    component: () => import('../../src/pages/ErrorNotFound.vue')
  }
]

export default routes

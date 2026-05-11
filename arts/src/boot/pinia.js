// // src/boot/pinia.js
// import { boot } from 'quasar/wrappers';
// import { createPinia } from 'pinia';

// export default async({ app }) => {
//   const pinia = createPinia();
//   app.use(pinia);
//   console.log('pinia initializing ...')
// }

// // src/boot/pinia.js
// import { createPinia } from 'pinia'

// export default async ({ app }) => {
//   // ONLY create Pinia ONCE HERE
//   const pinia = createPinia()
//   app.use(pinia)
// }
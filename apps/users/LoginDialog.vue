<template>
  <q-dialog v-model="opened" width="800px" max-height="90vh">
  <q-card class="no-shadow q-pa-0" style="height:80vh;display:flex;flex-col">
    <q-layout view="hHh lpR fFf" class="full-height">
      <q-header class="q-pa-md">Header</q-header>
      <q-page-container>
        <q-page class="q-pa-md">Scrollable content</q-page>
      </q-page-container>
      <q-footer class="q-pa-md">Footer buttons</q-footer>
    </q-layout>
  </q-card>
</q-dialog>
</template>
<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'
// import UserListDialog from './UserListDialog'
import { useRouter } from 'vue-router'
const router = useRouter()

import { axiosFunctions } from '../src/composables/axiosFunctions'
const { gaxios, paxios } = axiosFunctions()
import { libFunctions } from '../src/composables/libFunctions'
const { isAdmin, userType, store, $q, ENV_DEV } = libFunctions()

//== data sections
const name = ref(null)
const usertype = ref(null)
const username = ref('swang71')
const password = ref(null)
const accept = ref(true)
const opened = ref(false)
const appName = ref('')

// console.log('-ST-LoginAdmin', process.env.API)
console.log('-ST-LoginDialog', import.meta.env)
emitter.on('open-LoginDialog', app => openIt(app))

// if (process.env.API === '/api') {
//   username.value = 'swang71'
//   password.value = 'Ybsjll11'
// }
function openIt(app) {
  console.log(`-CK-LoginDialog-openIt app=${app}`)
  appName.value = app
  opened.value = true
}

function onSubmit() {
  if (accept.value !== true) {
    $q.notify({
      color: 'red-5',
      textColor: 'white',
      icon: 'warning',
      message: 'You need to accept the license and terms first'
    })
  } else {
    login()
    opened.value = false
    $q.notify({
      color: 'green-4',
      textColor: 'white',
      icon: 'cloud_done',
      message: 'Submitted'
    })
  }
}
function login() {
  console.log('-fn-login')
  const args = {}
  const inData = {}
  // inData.username = username.value
  inData.username = username.value
  inData.password = password.value
  // const path = process.env.API + '/apps/loginAdmin'
  const path = ENV_DEV + '/apps/loginAdmin'
  paxios(path, inData)
}
function onReset() {
  // username.value = null
  username.value = null
  password.value = null
  accept.value = false
}
emitter.on('apps-loginAdmin', da => setLogin(da))
function setLogin(da) {
  console.log('-CK-fn-setLogin', da)
  const user = da.user
  // store.commit('apps/setUserType', user.usertype)
  store.userType = user.usertype
  $q.localStorage.set('userType', user.usertype)
  userType.value = user.usertype
  router.replace({ path: appName.value })
  console.log(`-CK-setLogin ${appName.value}`)
  // emitter.emit('user-type', user.usertype)
  // console.log(`-CK-apps.loginAdmin usertype=${user.usertype} isAdmin=${isAdmin.value}`)
}
function add() {
  console.log('-fn-add')
  // const path = process.env.API + '/users/add'
  const path = '/users/add'
  const inData = {}
  inData.name = name.value
  inData.usertype = usertype.value
  inData.username = username.value
  inData.password = password.value
  paxios(path, inData)
}
// function getUserList () {
//   console.log("-fn-getUserList")
//   const path = process.env.API + '/users/getUserList'
//   gaxios(path)
// }
</script>

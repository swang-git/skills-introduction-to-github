<template>
  <q-dialog v-model="opened">
    <div class="q-pa-md bg-cyan-10 text-cyan" style="max-width: 400px">
      <q-form @submit="onSubmit" @reset="onReset" class="q-gutter-md">
        <!-- @csrf -->
        <q-input
          class="text-h6"
          dark
          filled
          v-model="username"
          label="username"
          hint="apps login for apps/applications"
          lazy-rules
          :rules="[
            val => (val && val.length > 5) || 'Please type the username'
          ]"
        />

        <q-input
          autocomplete
          class="text-h6"
          dark
          filled
          type="password"
          v-model="password"
          label="Password"
          lazy-rules
          :rules="[
            val => (val !== null && val !== '') || 'Please type the password'
          ]"
        />

        <q-toggle v-model="accept" label="I accept the license and terms" />

        <q-card-actions align="between">
          <q-btn flat label="Cancel" color="amber" v-close-popup />
          <q-btn flat label="Reset" type="reset" color="primary" />
          <q-btn flat label="Login" type="submit" color="secondary" />
        </q-card-actions>
        <!-- <q-card-actions align="between">
        <q-btn flat label="Create"  color="amber" @click="add"/>
        <q-btn flat label="Retrive" color="green" @click="getUserList" />
        <q-btn flat label="Update"  color="amber" />
        <q-btn flat label="Destroy" color="red" />
      </q-card-actions> -->
      </q-form>
    </div>
    <!-- <UserListDialog /> -->
  </q-dialog>
</template>
<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'
// import UserListDialog from './UserListDialog'
import { useRouter } from 'vue-router'
const router = useRouter()

import { axiosFunctions } from '../src/composables/axiosFunctions'
const { paxios } = axiosFunctions()
import { libFunctions } from '../src/composables/libFunctions'
<<<<<<< HEAD
const { isAdmin, userType, store, $q, ENV_DEV } = libFunctions()
=======
const { ENV_DEV, userType, $q } = libFunctions()
>>>>>>> f67d697ec603fc6e69dd4d286f3f63a3be8036be

//== data sections
const name = ref(null)
// const usertype = ref(null)
const username = ref('swang71')
const password = ref(null)
const accept = ref(true)
const opened = ref(false)
<<<<<<< HEAD
const appName = ref('')

// console.log('-ST-LoginAdmin', process.env.API)
console.log('-ST-LoginDialog', import.meta.env)
emitter.on('open-LoginDialog', (app) => openIt(app))
=======
const appName = ref(null)

// console.log('-ST-LoginAdmin', process.env.API)
console.log('-ST-LoginDialog', import.meta.env)
emitter.on('open-LoginDialog', (x) => openIt(x))
>>>>>>> f67d697ec603fc6e69dd4d286f3f63a3be8036be

// if (process.env.API === '/api') {
//   username.value = 'swang71'
//   password.value = 'Ybsjll11'
// }
<<<<<<< HEAD
function openIt(app) {
  console.log(`-CK-LoginDialog-openIt app=${app}`)
=======
function openIt (app) {
>>>>>>> f67d697ec603fc6e69dd4d286f3f63a3be8036be
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
  inData.username = username.value
  inData.password = password.value
<<<<<<< HEAD
  // const path = process.env.API + '/apps/loginAdmin'
=======
>>>>>>> f67d697ec603fc6e69dd4d286f3f63a3be8036be
  const path = ENV_DEV + '/apps/loginAdmin'
  paxios(path, inData)
}
function onReset() {
  // username.value = null
  username.value = null
  password.value = null
  accept.value = false
}
emitter.on('apps-loginAdmin', (da) => setLogin(da))
function setLogin(da) {
  console.log('-CK-fn-setLogin', da)
  const user = da.user
<<<<<<< HEAD
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
=======
  // $store.commit('apps/setUserType', user.usertype)
  // $q.localStorage.set('usertype', user.usertype)
  userType.value = user.usertype
  emitter.emit('user-type', user.usertype)
  emitter.emit('open-app', appName.value)
  console.log(`-CK-apps.loginAdmin userType=${userType.value} usertype=${user.usertype}`)
}
function add () {
  console.log("-fn-add")
  // const path = process.env.API + '/users/add'
  const path = ENV_DEV +  '/users/add'
>>>>>>> f67d697ec603fc6e69dd4d286f3f63a3be8036be
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

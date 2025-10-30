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
          (val) => (val && val.length>5) || 'Please type the username',
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
          (val) => (val !== null && val !== '') || 'Please type the password',
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

import { axiosFunctions } from '../src/composables/axiosFunctions'
const { gaxios, paxios } = axiosFunctions()
import { libFunctions } from '../src/composables/libFunctions'
const { isAdmin, userType, $store, $q } = libFunctions()

//== data sections
const name = ref(null)
const usertype = ref(null)
const username = ref('swang71')
const password = ref(null)
const accept = ref(true)
const opened = ref(false)

console.log('-ST-LoginAdmin', process.env.API)
emitter.on('open-LoginDialog', () => openIt())

if (process.env.API === '/api') {
  username.value = 'swang71'
  password.value = 'Ybsjll11'
}
function openIt () {
  opened.value = true
}

function onSubmit () {
  if (accept.value !== true) {
    $q.notify({
      color: "red-5",
      textColor: "white",
      icon: "warning",
      message: "You need to accept the license and terms first",
    });
  } else {
    login()
    opened.value = false
    $q.notify({
      color: "green-4",
      textColor: "white",
      icon: "cloud_done",
      message: "Submitted",
    })
  }
}
function login () {
  console.log("-fn-login")
  const args = {}
  const inData = {}
  // inData.username = username.value
  inData.username = username.value
  inData.password = password.value
  const path = process.env.API + '/apps/loginAdmin'
  paxios(path, inData)
}
function onReset () {
  // username.value = null
  username.value = null
  password.value = null
  accept.value = false
}
emitter.on('apps-loginAdmin', (da) => setLogin(da))
function setLogin (da) {
  console.log("-CK-fn-setLogin", da)
  const user = da.user
  $store.commit('apps/setUserType', user.usertype)
  $q.localStorage.set('usertype', user.usertype)
  // usertype.value = user.usertype
  emitter.emit('user-type', user.usertype)
  // console.log(`-CK-apps.loginAdmin usertype=${user.usertype} isAdmin=${isAdmin.value}`)
}
function add () {
  console.log("-fn-add")
  const path = process.env.API + '/users/add'
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

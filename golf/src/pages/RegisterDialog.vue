<template>
<q-dialog v-model="opened" persistent>
  <div class="q-pa-md bg-teal-10 text-cyan" style="max-width: 400px">
    <q-form @submit="onSubmit" @reset="onReset" class="q-gutter-md">
      <q-input
        class="text-h6"
        dark
        filled
        v-model="name"
        label="User's Fullname"
        hint="User's Fullname"
        lazy-rules
        :rules="[
          (val) => (val && val.length>5) || 'Please type the User\'s Fullname',
        ]"
      />

      <q-input
        class="text-h6"
        dark
        filled
        v-model="username"
        label="Username for login"
        hint="Admin Username for TeamMatch(e.g. JZsMatch)"
        lazy-rules
        :rules="[
          (val) => (val && val.length>5) || 'Please type the Admin username',
        ]"
      />

      <q-input
        class="text-h6"
        dark
        filled
        v-model="usertype"
        label="User Type for System Admin"
        hint="Admin Role for TeamMatch(e.g. JZsMatch)"
        lazy-rules
        :rules="[
          (val) => (val && val.length>5) || 'Please type the Admin Role',
        ]"
      />

      <q-input
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

      <q-input
        class="text-h6"
        dark
        filled
        type="password"
        v-model="confirmPassword"
        label="Type Password Again"
        hint="teamMatchYe3"
        lazy-rules
        :rules="[
          (val) => (val !== null && val !== '') || 'Please re-type the password',
        ]"
      />

      <q-input
        class="text-h6"
        dark
        filled
        type="email"
        v-model="email"
        label="email"
        lazy-rules
        :rules="[
          (val) => (val !== null && val !== '') || 'Please type the email',
        ]"
      />

      <q-toggle v-model="accept" label="I accept the license and terms" />

      <q-card-actions align="between">
        <q-btn flat label="Cancel" color="amber" v-close-popup />
        <q-btn flat label="Reset" type="reset" color="primary" />
        <q-btn flat label="Create" type="submit" color="secondary" />
      </q-card-actions>
    </q-form>
  </div>
</q-dialog>
</template>
<script setup>
import emitter from 'tiny-emitter/instance'
import { ref } from 'vue'
// import { useQuasar } from 'quasar'
import { axiosFunctions } from 'src/composables/axiosFunctions';
import { libFunctions } from 'src/composables/libFunctions';
// const emit = defineEmits([
//   // REQUIRED
//   "ok",
//   "hide",
//   "reset-grp-cpt",
// ])
const { paxios } = axiosFunctions()
const { $q, store } = libFunctions()
const name = ref('XYs TeamMatch')
const username = ref('XYsAdmin')
const password = ref('TM_XY_account')
const confirmPassword = ref(null)
const usertype = ref('XYsAdmin')
const email = ref('XYsAdmin@pgc.org')
const accept = ref(true)
const opened = ref(false)
console.log('-ST-RegisterDialog')
emitter.on('open-RegisterDialog', () => show())

function onSubmit() {
  if (accept.value !== true) {
    $q.notify({
      color: "red-5",
      textColor: "white",
      icon: "warning",
      message: "You need to accept the license and terms first",
    })
  } else if (password.value !== confirmPassword.value) {
    $q.notify({
      color: "amber-9",
      textColor: "black",
      icon: "info",
      message: "You passwords not match, please try it again",
    })
  } else {
    createAccount()
    hide()
    $q.notify({
      color: "green-4",
      textColor: "white",
      icon: "cloud_done",
      message: "Submitted",
    })
  }
}
function onReset() {
  name.value = null;
  username.value = null;
  password.value = null;
  confirmPassword.value = null;
  usertype.value = null;
  email.value = null;
  accept.value = false;
}
emitter.on('golf-createAccount', (x) => setAccount(x))
function setAccount(da) {
  store.usertype = da.usertype
  console.info('-ab-setAccount back', da.usertype)
}
function createAccount() {
  const inData = {}
  inData.name = name.value
  inData.username = username.value
  inData.password = password.value
  inData.usertype = usertype.value
  inData.email = email.value
  const path = process.env.API + '/golf/createAccount'
  paxios(path, inData)
}
function show() {
  console.info("-fn-show");
  // this.$refs.dialog.show();
  opened.value = true
}
// following method is REQUIRED
// (don't change its name --> "hide")
function hide() {
  // this.$refs.dialog.hide();
  opened.value = false
}
// function XXonDialogHide() {
//   // required to be emitted
//   // when QDialog emits "hide" event
//   emit("hide");
// }
// function XXonOKClick() {
//   console.info("-dl-onOKClick");
//   // on OK, it is REQUIRED to
//   // emit "ok" event (with optional payload)
//   // before hiding the QDialog
//   emit("ok");
//   // or with payload: this.$emit('ok', { ... })
//   // then hiding dialog
//   hide();
// }
// function onCancelClick() {
//   // we just need to hide the dialog
//   hide();
// }
</script>

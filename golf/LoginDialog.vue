<template>
  <q-dialog v-model="opened">
    <div class="q-pa-md bg-cyan-10 text-cyan" style="max-width: 400px">
      <q-form @submit="onSubmit" @reset="onReset" class="q-gutter-md">
        <q-input
          class="text-h6"
          dark
          filled
          v-model="username"
          label="Username"
          hint="system login for admin or grouping"
          lazy-rules
          :rules="[(val) => (val && val.length > 5) || 'Please type the username']"
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
          :rules="[(val) => (val !== null && val !== '') || 'Please type the password']"
        />

        <q-toggle v-model="accept" label="I accept the license and terms" />

        <q-card-actions align="between">
          <q-btn flat label="Cancel" color="amber" v-close-popup />
          <q-btn flat label="Reset" type="reset" color="primary" />
          <q-btn flat label="Login" type="submit" color="secondary" />
        </q-card-actions>
      </q-form>
    </div>
  </q-dialog>
</template>
<script setup>
import emitter from "tiny-emitter/instance";
import { ref } from "vue";
import { axiosFunctions } from "src/composables/axiosFunctions";
import { libFunctions } from "src/composables/libFunctions";
const { paxios } = axiosFunctions();
const { screenheight, screenwidth, $q, store } = libFunctions();

const username = ref("SysAdmin");
const password = ref(null);
const accept = ref(true);
const opened = ref(false);

if (process.env.API === "/api") password.value = "Ybsjll11";
// console.log(`-ST-LoginDialog process.env.API=${process.env.API}`)
emitter.on('open-LoginDialog', () => { opened.value = true });
function onSubmit() {
  if (accept.value !== true) {
    $q.notify({
      color: "red-5",
      textColor: "white",
      icon: "warning",
      message: "You need to accept the license and terms first",
    });
  } else {
    login();
    opened.value = false;
    $q.notify({
      color: "purple",
      badgeColor: "yellow",
      badgeTextColor: "dark",
      badgeClass: "shadow-3 glossy my-badge-class text-h5",
      icon: "login",
      message: "Login was successful",
    });
    let cookieKey = 'sid_system_admin'
    let cookieVal = 'system_admin_' + screenheight + '_' + screenwidth
    let cookieExp = '100d'
    console.log(`-CK-q.cookies.set - cookieKey=${cookieKey}, cookieVal=${cookieVal}, cookieExp=${cookieExp}`)
    $q.cookies.set(cookieKey, cookieVal, cookieExp)
  }
}
function onReset() {
  username.value = null;
  password.value = null;
  accept.value = false;
}
emitter.on("golf-login", (x) => setLogin(x));
function setLogin(da) {
  store.usertype = da.usertype
  // $q.localStorage.set('golf_usertype', da.usertype)
  // golf_usertype.value = da.usertype
  // console.log('-CK-login back', da.usertype)
  // emitter.emit("golf-usertype", da.usertype);
}
function login() {
  const inData = {};
  inData.username = username.value;
  inData.password = password.value;
  const path = process.env.API + "/golf/login";
  paxios(path, inData);
}
</script>

<template>
  <!-- <q-card class="flex flex-center" style="margin-top:-5px;height:500px;background-image:url('https://cdn.quasar.dev/img/material.png')"> -->
  <!-- <q-card class="flex flex-center" style="margin-top:-5px;height:500px;background-image:url('icons/material.png')"> -->
  <!-- <q-card class="flex flex-center" style="margin-top:-5px;height:500px;background-image:url('/api/apps/icons/bg-img-purple.png')"> -->
  <q-card class="flex flex-center q-pa-sm" :style="getBackgroundImg()">
    <!-- <q-card class="flex flex-center bg-teal-10" style="margin-top:-5px;height:500px;background-image:url('/icons/quasar-logo.svg')"> -->
    <!-- <img alt="Quasar logo" src="~assets/quasar-logo-vertical.svg" style="width:200px; height:200px" /> -->
    <!-- <img alt="Quasar logo" src="/assets/quasar-logo-vertical.svg" style="width:200px; height:200px" /> -->
    <!-- <img alt="Quasar logo" src="icons/materal.png" style="width:200px; height:200px" /> -->
    <q-card-actions align="between">
      <RoundButton size="22px" icon="monetization_on" clas="q-ma-xs" colr="purple-10" iclr="yellow" ttip="日 常 消 费" @click="openApp('exlist')" />
      <RoundButton size="22px" icon="add_shopping_cart" clas="q-ma-xs" colr="indigo-10" ttip="采 购 清 单" @click="openApp('shopping')" />
      <RoundButton size="22px" icon="schedule" clas="q-ma-xs" colr="cyan-10" iclr="amber" ttip="温 馨 提 示" @click="openApp('reminder')" />
      <RoundButton size="22px" icon="assignment" clas="q-ma-xs" colr="black" ttip="备　忘　录" @click="openApp('memo')" />
      <RoundButton size="22px" icon="健" clas="q-ma-xs q-pb-sm" colr="red-10" iclr="yellow" ttip="每 天 看 看" @click="openApp('watcher')" />
      <RoundButton size="22px" icon="account_balance" clas="q-ma-xs" colr="indigo-10" iclr="amber" ttip="银 行 月 报" @click="openApp('bankstatement')" />
      <RoundButton size="22px" icon="析" clas="q-ma-xs q-pb-sm" colr="green-10" ttip="月 报 分 析" @click="openApp('holdings')" />
      <RoundButton size="22px" icon="bloodtype" clas="q-ma-xs" colr="red-10" iclr="lime" ttip="血 糖 控 制" @click="openApp('glucosecheck')" />
      <RoundButton size="22px" icon="文" clas="q-ma-xs q-pb-sm" colr="indigo-10" ttip="网 上 阅 读" @click="openApp('../arts')" />
      <RoundButton size="22px" icon="画" clas="q-ma-xs q-pb-sm" colr="red-10" iclr="yellow" ttip="娅 莉 画 展" @click="openApp('../yali')" />
      <RoundButton size="22px" icon="golf_course" clas="q-ma-xs" colr="teal-10" iclr="yellow" ttip="高  尔  夫" @click="openApp('../golf')" />
      <RoundButton size="22px" icon="translate" clas="q-ma-xs" colr="brown-10" ttip="英 汉 字 典" @click="openApp('dictionary')" />
      <RoundButton size="22px" icon="card_giftcard" clas="q-ma-xs" colr="red-10" ttip="联 邦 节 日" @click="showHolidays()" />
      <RoundButton size="22px" icon="palette" clas="q-ma-xs q-pb-x" colr="indigo-10" ttip="Drawing" @click="openApp('painting')" />
      <RoundButton size="22px" icon="报" clas="q-ma-xs q-pb-sm" colr="cyan-10" ttip="信 用 卡 花 销" @click="openApp('bankstatementloader')" />
      <RoundButton size="22px" icon="转" clas="q-ma-xs q-pb-sm" colr="green-10" ttip="Convert To Text" @click="openApp('totext')" />
      <RoundButton size="22px" icon="查" clas="q-ma-xs q-pb-sm" colr="green-10" iclr="cyan-2" ttip="健 康 检 查" @click="openApp('htlist')" />
      <RoundButton size="22px" icon="胰" clas="q-ma-xs q-pb-sm" colr="purple-10" iclr="white" ttip="胰 流 报 告" @click="openApp('pfcheck')" />
      <RoundButton size="22px" icon="年" clas="q-ma-xs q-pb-sm" colr="teal-10" iclr="white" ttip="中 西 年 列 表" @click="openApp('chnyears')" />
      <RoundButton size="22px" icon="group" clas="q-ma-xs" colr="indigo-10" iclr="amber" ttip="用 户 管 理" @click="refUserList.getUserList()" v-if="AppAdmin" />
      <RoundButton size="22px" icon="logout" clas="q-ma-xs" colr="amber-10" iclr="grey-10" ttip="系 统 Logout" @click="logout()" v-show="AppAdmin" />
      <RoundButton size="22px" :icon="compVer" clas="q-ma-xs q-pb-sm" colr="blue-10" iclr="yellow" ttip="系 统 信 息" @click="showSysInfo()" />
      <RoundButton v-if="isFedora" size="22px" icon="视" clas="q-ma-xs q-pb-sm" colr="indigo-10" iclr="white" ttip="电 视 列 表" @click="openApp('tvmanager')" />
      <!-- <RoundButton size="22px" icon="login" clas="q-ma-xs" colr="grey-10" iclr="amber" ttip="系 统 管 理" @click="login()" v-if="!AppAdmin" /> -->
    </q-card-actions>
    <!-- <LoginAdmin /> -->
    <UserList ref="refUserList" />
  </q-card>
  <PlatformDataPad ref="refPlatformDataPad" />
</template>
<script setup>
import { ref, onMounted, computed } from 'vue'
import emitter from 'tiny-emitter/instance'
import { useRouter } from 'vue-router'
const router = useRouter()

import { libFunctions } from '../../src/composables/libFunctions'
const { isFedora, buildApp, store, $q, userType, AppAdmin, ENV_DEV } = libFunctions()
import { axiosFunctions } from '../../src/composables/axiosFunctions'
const { gaxios } = axiosFunctions()

import RoundButton from '../../src/components/RoundButton.vue'
// import LoginAdmin from '../../users/LoginDialog.vue'
import UserList from '../../users/UserList.vue'
import PlatformDataPad from '../components/PlatformDataPad.vue'

const refPlatformDataPad = ref(null)

const refUserList = ref(null)
const compVer = computed(() => { return import.meta.env.VITE_BUILD_TAG == null ? '测' : import.meta.env.VITE_BUILD_TAG })

console.log(`-ST-Index bg-img=${getBackgroundImg().backgroundImage} isFedora=${isFedora}`)
// logout()
buildApp('Apps Home', '家庭应用')
emitter.on('user-type', (x) => userType.value = x)
emitter.on('open-app', (x) => openApp(x))
// emitter.on('apps-logout', () => logout())
onMounted(() => {
  console.log(refUserList.value)
  console.log(refPlatformDataPad.value)
})

function getBackgroundImg() {
  return {
    backgroundImage: 'url("' + ENV_DEV + '/apps/assets/bg-img-purple.png"' + ')',
    // backgroundImage: 'url("' + ENV_DEV + '/apps/assets/material.svg"' + ')',
    backgroundSize: 'cover',
    backgroundPosition: 'center',
    backgroundRepeat: 'no-repeat',
    height: '400px'
  }
}

//== function sections
function showSysInfo() {
  // console.log(`-fn-showSysInfo ${compVER.value}`)
  refPlatformDataPad.value.openIt()
}
function openApp(app) {
  if (['../golf', '../arts', '../yali'].includes(app)) {
    // logout()
    AppAdmin.value = false
    console.log(`-CK-openApp ${app}`)
    // router.replace({ path: app })
    window.location.href = ENV_DEV + app
  } else if ([
      'glucosecheck',
      'holdings',
      'exlist',
      'reminder',
      'memo',
      'watcher',
      'bankstatement',
      'bankstatementloader',
      'healthtest',
      'pancreaticfluid',
      'dictionary',
      'pfcheck',
    ].includes(app) && !AppAdmin.value
  ) {
    return login(app)
  } else { 
    console.log(`-CK-openApp ${app}`)
    router.replace({ path: app })
  }
}
function login(app) {
  console.log(`-CK-fn-login app=${app}`)
  emitter.emit('open-LoginDialog', app)
}
function showHolidays() {
  console.log('-CK-fn-showHolidays')
  emitter.emit('open-Holidays')
}
// emitter.on('apps-logout', () => logout())
function logout() {
  console.log(`-fn-logout AppAdmin=${AppAdmin.value}`)
  const path = ENV_DEV + '/apps/logout'
  userType.value = null
  $q.localStorage.set('userType', null)
  store.userType = null
  // AppAdmin.value = false
  window.location.href = ENV_DEV + '/apps'
  gaxios(path)
}
</script>

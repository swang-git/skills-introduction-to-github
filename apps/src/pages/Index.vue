<template>
  <!-- <q-layout view="hHh lpR fFf"> -->
  <q-layout view="hhh lpR fFf">
    <q-header elevated>
      <q-toolbar class="bg-teal-9">
        <q-card class="flex flex-center">
        <!-- <q-card class="flex flex-center q-pr-xs" :style="getBackgroundImgM()"> -->
          <!-- <q-card-actions align="between" class="bg-teal-9" :style="isIM ? { 'margin':'-5px -5px 0 0' } : { 'margin-top': '-855px' }"> -->
          <q-card-actions align="between" class="bg-teal-9">
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
        </q-card>
      </q-toolbar>
    </q-header>
    <q-page-container v-if="isDesk" :style="getBackgroundImgM()">
      <div class="row justify-between">
        <q-btn flat icon="" size="150px" @click="getPrevPic" style="height:95vh; z-index:1" />
        <q-btn flat icon="" size="150px" @click="getNextPic" style="height:95vh; z-index:1" />
      </div>
    </q-page-container>
    <q-footer v-if="isDesk" elevated class="bg-teal-10">
      <q-toolbar>
        <q-btn rounded glossy label="上一幅" color="andigo" @click="getPrevPic" />
        <q-space /> <!-- this pushes next button all the way right -->
        <q-btn rounded glossy :label="showSlide ? '停止' : '幻灯'" :color="showSlide ? 'amber-9' : 'primary'" @click="showSlide=!showSlide" />
        <q-space /> <!-- this pushes next button all the way right -->
        <q-btn rounded glossy label="随机" color="primary" @click="showSlide=getRandomPic(-1)" />
        <q-space /> <!-- this pushes next button all the way right -->
        <q-btn rounded glossy label="下一幅" color="andigo" @click="getNextPic" />
      </q-toolbar>
    </q-footer>
  </q-layout>
  <PlatformDataPad ref="refPlatformDataPad" />
</template>
<script setup>
import { ref, onMounted, computed } from 'vue'
import emitter from 'tiny-emitter/instance'
import { useRouter } from 'vue-router'
const router = useRouter()

import { libFunctions } from '../../src/composables/libFunctions'
const { isIM, isFedora, buildApp, store, $q, userType, AppAdmin, isDesk, ENV_DEV } = libFunctions()
import { axiosFunctions } from '../../src/composables/axiosFunctions'
const { gaxios } = axiosFunctions()

import RoundButton from '../../src/components/RoundButton.vue'
import PlatformDataPad from '../components/PlatformDataPad.vue'

const refPlatformDataPad = ref(null)
const picidx = ref(-1)
const totalp = ref(0)
const piclnk = ref(null)
const rat = ref(null)
const showSlide = ref(true)

const refUserList = ref(null)
const compVer = computed(() => { return import.meta.env.VITE_BUILD_TAG == null ? '测' : import.meta.env.VITE_BUILD_TAG })
const picurl = computed(() => { return piclnk.value })
const picIdx = computed(() => { return parseInt(picidx.value) })

console.log(`-ST-Index bg-img=${getBackgroundImg().backgroundImage} isFedora=${isFedora}`)
// logout()
buildApp('Apps Home', '家庭应用')
emitter.on('user-type', (x) => userType.value = x)
emitter.on('open-app', (x) => openApp(x))
emitter.on('yali-getRandomPic', (x) => setRandomPic(x))
// emitter.on('apps-logout', () => logout())
onMounted(() => {
  console.log(refUserList.value)
  console.log(refPlatformDataPad.value)
})

getRandomPic(-1)
slideShow()

function slideShow () {
  setInterval(() => { 
    showSlide.value ? getNextPic() : null 
  }, 2500)
}
function getPrevPic () {
  console.log(`-fn-getNextPic picIdx=${picIdx.value}`)
  let pidx = picIdx.value - 1
  if (pidx < 0) pidx = totalp - 1
  getRandomPic(pidx)
}
function getNextPic () {
  console.log(`-fn-getNextPic picIdx=${picIdx.value} showSlide=${showSlide.value}`)
  let pidx = picIdx.value + 1
  if (pidx >= totalp.value) pidx = 0
  getRandomPic(pidx)
}

function setRandomPic (da) {
  console.log(`-fn-setRandomFile rat=${da.rat} picidx=${da.picidx} totalp=${da.totalp} randomFile=${da.randomFile}`)
  piclnk.value = ENV_DEV + da.randomFile
  rat.value = da.rat
  picidx.value = da.picidx
  totalp.value = da.totalp
}

function getRandomPic (pidx) {
  const path = ENV_DEV + '/yali/getRandomPic/' + pidx
  gaxios(path)
}
function getBackgroundImg() {
  return {
    // backgroundImage: 'url("' + picurl.value + '")',
    backgroundImage: 'url("' + ENV_DEV + '/apps/assets/bg-img-purple.png"' + ')',
    // backgroundImage: 'url("' + ENV_DEV + '/apps/assets/material.svg"' + ')',
    backgroundSize : 'cover',
    backgroundPosition: 'center',
    backgroundRepeat: 'no-repeat',
    // width: ($q.screen.width - 60) + 'px',
    // height: getHeight()
    height: 400
  }
}
function getBackgroundImgM() {
  if (isIM) return "backgroundColor:teal"
  return {
    backgroundImage: 'url("' + picurl.value + '")',
    // backgroundSize : 'cover',
    backgroundPosition: 'center',
    // backgroundRepeat: 'no-repeat',
    width: ($q.screen.width - 50) + 'px',
    height: ($q.screen.height - 10) + 'px',

  }
}

//== function sections
function showSysInfo() {
  // console.log(`-fn-showSysInfo ${compVER.value}`)
  refPlatformDataPad.value.openIt()
}
function openApp(app) {
  if (['../golf', '../arts', '../yali'].includes(app)) {
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

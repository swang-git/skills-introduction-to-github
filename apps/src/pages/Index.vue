<template>
  <q-layout view="hhh lpR fFf">
    <q-header elevated>
      <q-toolbar class="bg-teal-9">
        <q-card class="flex flex-center">
          <q-card-actions align="between" class="bg-teal-9" style="margin:-5px -12px 0 -12px">
            <RoundButton size="22px" icon="monetization_on" clas="q-ma-xs" colr="purple-10" iclr="yellow" ttip="日 常 消 费" @click="openApp('exlist')" />
            <RoundButton size="22px" icon="add_shopping_cart" clas="q-ma-xs" colr="indigo-10" ttip="采 购 清 单" @click="openApp('shopping')" />
            <RoundButton v-if="!isFedora" size="22px" icon="schedule" clas="q-ma-xs" colr="cyan-10" iclr="amber" ttip="温 馨 提 示" @click="openApp('reminder')" />
            <RoundButton size="22px" icon="备" clas="q-ma-xs q-pb-sm" colr="purple-10" iclr="cyan-2" ttip="备　忘　录" @click="openApp('memo')" />
            <RoundButton size="22px" icon="健" clas="q-ma-xs q-pb-sm" colr="red-10" iclr="yellow" ttip="每 天 看 看" @click="openApp('watcher')" />
            <RoundButton size="22px" icon="account_balance" clas="q-ma-xs" colr="indigo-10" iclr="amber" ttip="银 行 月 报" @click="openApp('bankstatement')" />
            <RoundButton size="22px" icon="析" clas="q-ma-xs q-pb-sm" colr="green-10" ttip="月 报 分 析" @click="openApp('holdings')" />
            <RoundButton size="22px" icon="bloodtype" clas="q-ma-xs" colr="red-10" iclr="lime" ttip="血 糖 控 制" @click="openApp('glucosecheck')" />
            <!-- <RoundButton size="22px" icon="文" clas="q-ma-xs q-pb-sm" colr="indigo-10" ttip="网 上 阅 读" @click="openApp('../arts')" /> -->
            <!-- <RoundButton size="22px" icon="画" clas="q-ma-xs q-pb-sm" colr="red-10" iclr="yellow" ttip="娅 莉 画 展" @click="openApp('../yali')" /> -->
            <RoundButton size="22px" icon="golf_course" clas="q-ma-xs" colr="teal-10" iclr="yellow" ttip="高  尔  夫" @click="openApp('../golf')" />
            <RoundButton size="22px" icon="translate" clas="q-ma-xs" colr="brown-10" ttip="英 汉 字 典" @click="openApp('dictionary')" />
            <RoundButton size="22px" icon="card_giftcard" clas="q-ma-xs" colr="red-10" ttip="联 邦 节 日" @click="showHolidays()" />
            <RoundButton size="22px" icon="palette" clas="q-ma-xs q-pb-x" colr="indigo-10" ttip="Drawing" @click="openApp('painting')" />
            <RoundButton size="22px" icon="报" clas="q-ma-xs q-pb-sm" colr="cyan-10" ttip="信 用 卡 花 销" @click="openApp('bankstatementloader')" />
            <RoundButton size="22px" icon="转" clas="q-ma-xs q-pb-sm" colr="red-10" ttip="Convert To Text" @click="openApp('totext')" />
            <RoundButton size="22px" icon="查" clas="q-ma-xs q-pb-sm" colr="green-10" iclr="cyan-2" ttip="健 康 检 查" @click="openApp('htlist')" />
            <RoundButton size="22px" icon="胰" clas="q-ma-xs q-pb-sm" colr="purple-10" iclr="white" ttip="胰 流 报 告" @click="openApp('pfcheck')" />
            <RoundButton size="22px" icon="年" clas="q-ma-xs q-pb-sm" colr="teal-10" iclr="white" ttip="中 西 年 列 表" @click="openApp('chnyears')" />
            <RoundButton size="22px" icon="group" clas="q-ma-xs" colr="indigo-10" iclr="amber" ttip="用 户 管 理" @click="refUserList.getUserList()" v-if="AppAdmin" />
            <RoundButton size="22px" icon="logout" clas="q-ma-xs" colr="amber-10" iclr="grey-10" ttip="系 统 Logout" @click="logout()" v-show="AppAdmin" />
            <RoundButton v-if="isFedora" size="22px" icon="视" clas="q-ma-xs q-pb-sm" colr="indigo-10" iclr="white" ttip="电 视 列 表" @click="openApp('tvmanager')" />
            <RoundButton size="22px" :icon="compVer" clas="q-ma-xs q-pb-sm" colr="indigo-10" iclr="cyan-2" ttip="系 统 信 息" @click="showSysInfo()" />
            <RoundButton size="22px" icon="login" clas="q-ma-xs" colr="grey-10" iclr="amber" ttip="系 统 管 理" @click="login()" v-if="!AppAdmin" />
          </q-card-actions>
        </q-card>
      </q-toolbar>
    </q-header>
    <div v-if="isDesk" class="row" :style="getBackgroundImg()">
      <div class="corner-btn top-left">
        <RoundButton size="33px" icon="画" clas="q-ma-xs q-pb-md" colr="purple-10" iclr="yellow" ttip="娅 莉 画 展" @click="openApp('../yali')" />
      </div>
      <div class="corner-btn top-right">
        <RoundButton size="33px" icon="文" clas="q-ma-xs q-pb-md" colr="purple-10" iclr="yellow" ttip="网 上 阅 读" @click="openApp('../arts')" />
      </div>
      <div class="q-pb-xl row justify-center">
        <q-avatar :size="screenwidth - 190 + 'px'" style="margin:237px 0 0 66px"><img :src="piclnk" class="circle-img" /></q-avatar>
      </div>
      <div class="corner-btn bottom-left">
        <RoundButton size="33px" icon="golf_course" clas="q-ma-xs" colr="teal-10" iclr="yellow" ttip="高  尔  夫" @click="openApp('../golf')" />
      </div>
      <div class="corner-btn bottom-right">
        <RoundButton size="33px" icon="schedule" clas="q-ma-xs" colr="teal-10" iclr="amber" ttip="温 馨 提 示" @click="openApp('reminder')" />
        <!-- <RoundButton size="33px" icon="logout" clas="q-ma-xs" colr="amber-10" iclr="grey-10" ttip="系 统 Logout" @click="logout()" v-show="AppAdmin" />
        <RoundButton size="33px" icon="login" clas="q-ma-xs" colr="grey-10" iclr="amber" ttip="系 统 管 理" @click="login()" v-if="!AppAdmin" /> -->
      </div>
    </div>
    <q-footer v-if="isDesk" elevated class="bg-teal-10">
      <q-toolbar>
          <q-btn glossy rounded class="bg-teal-9" @click="getPrevPic">
            <q-icon left name="arrow_circle_left" size="md" color="lime" />
            <span class="text-bold text-cyan-2 text-body1" style="margin: 0 4px 0 -10px">上一幅</span>
          </q-btn>
          <q-space /> <!-- this pushes next button all the way right -->
          <q-btn v-if="showSlide" glossy rounded class="bg-lime-9" @click="showSlide=!showSlide">
            <span class="text-bold text-cyan-2 text-body1" style="margin: 0 4px 0 4px">停止</span>
            <q-icon name="pause" size="md" color="lime" />
          </q-btn>
          <q-btn v-else glossy rounded class="bg-teal-9" @click="showSlide=!showSlide">
            <span class="text-bold text-cyan-2 text-body1" style="margin: 0 4px 0 4px">幻灯</span>
            <q-icon name="play_circle" size="md" color="lime" />
          </q-btn>
          <q-space /> <!-- this pushes next button all the way right -->
          <q-btn glossy rounded class="bg-teal-9" @click="getRandomPic(-1)">
            <q-icon name="slow_motion_video" size="md" color="lime" />
            <span class="text-bold text-cyan-2 text-body1" style="margin: 0 4px 0 4px">随机</span>
          </q-btn>
          <q-space /> <!-- this pushes next button all the way right -->
          <q-btn glossy rounded class="bg-teal-9" @click="getNextPic">
            <span class="text-bold text-cyan-2 text-body1" style="margin: 0 4px 0 4px">下一幅</span>
            <q-icon name="arrow_circle_right" size="md" color="lime" />
          </q-btn>
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
const { isIM, isFedora, screenwidth, buildApp, store, $q, userType, AppAdmin, isDesk, ENV_DEV } = libFunctions()
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
const isApps = computed(() => { return /apps/.test(import.meta.env.BASE_URL) })

console.log(`-ST-Index BASE_URL=${import.meta.env.BASE_URL} isApps=${isApps.value}`)
// logout()
buildApp('Apps Home', '家庭应用')
emitter.on('user-type', (x) => userType.value = x)
emitter.on('open-app', (x) => openApp(x))
emitter.on('yali-getRandomPic', (x) => setRandomPic(x))
emitter.on('stop-slide-show', () => showSlide.value = false)
onMounted(() => {
  console.log(refUserList.value)
  console.log(refPlatformDataPad.value)
})

getRandomPic(-1)
slideShow()

function slideShow () {
  console.log(`-fn-slideShow isApps=${isApps.value} BASE_URL=${import.meta.env.BASE_URL}`)
  setInterval(() => {
    showSlide.value ? getNextPic() : null
  }, 3000)
}
function getPrevPic () {
  console.log(`-fn-getNextPic picIdx=${picIdx.value}`)
  let pidx = picIdx.value - 1
  if (pidx < 0) pidx = totalp - 1
  getRandomPic(pidx)
}
function getNextPic () {
  // console.log(`-fn-getNextPic picIdx=${picIdx.value} showSlide=${showSlide.value}`)
  let pidx = picIdx.value + 1
  if (pidx >= totalp.value) pidx = 0
  getRandomPic(pidx)
}

function setRandomPic (da) {
  // console.log(`-fn-setRandomFile rat=${da.rat} picidx=${da.picidx} totalp=${da.totalp} randomFile=${da.randomFile}`)
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
    // height: getHeight(),
    height: 500
  }
}
function getBackgroundImgM() {
  if (isIM) return "backgroundColor:teal"
  return {
    backgroundImage: 'url("' + picurl.value + '")',
    backgroundSize : 'cover',
    backgroundPosition: 'center',
    backgroundRepeat: 'no-repeat',
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
<style>
.corner-btn {
  position: fixed;
  z-index: 10;
}
.top-left {
  top: 220px;
  left: 60px;
}
.top-right {
  top: 220px;
  right: 3px;
}
.bottom-left {
  bottom: 56px;
  left: 60px;
}
.bottom-right {
  bottom: 56px;
  right: 3px;
}
.Xcircle-img {
  width: 100%;
  height: 100%;
  object-fit: cover; /* fill circle, crop edges, NO distortion */
  /* object-fit: contain; → whole image visible, may have empty space inside circle */
  object-position: center;
}
</style>

<template>
<q-page class="flex flex-center" style="background-image:url('https://cdn.quasar.dev/img/material.png')">
  <!-- <transition appear enter-active-class="animated flipInY" style="animation-duration:5s;animation-delay:0.5s"> -->
  <!-- <transition appear enter-active-class="animated fadeIn" style="animation-duration:5s;animation-delay:0.5s"> -->
  <!-- <transition appear enter-active-class="animated bounceIn" style="animation-duration:5s;animation-delay:0.5s"> -->
  <transition appear enter-active-class="animated rotateIn" style="animation-duration:5s;animation-delay:0.5s">
    <q-img src="icons/club-logo-full.png" style="display:block;margin:auto;margin-top:20px" height="330px" width="360px">
      <div class="absolute-bottom bg-transparent">
        <q-avatar size="58px" class="q-pt-xl" style="margin-left:15px"><img src="icons/boy.png"></q-avatar>
        <span class="text-red text-h6 no-wrap" style="margin:0px 0 0 -48px">{{ mcount }}名</span>
        <q-avatar size="58px" style="margin:-10px 0 0 266px"><img src="icons/girl.png"></q-avatar>
        <span class="text-red text-h6 no-wrap" style="margin:0 0 0 266px">{{ fcount }}名</span>
      </div>
    </q-img>
  </transition>
  <!-- <q-card-actions class="row" style="margin:auto;margin-top:-370px" :align="isDesk ? 'between' : 'evenly'"> -->
  <q-card-actions class="row" style="margin:auto;margin-top:-370px" align="between">
    <RoundButton size="20px" icon="PL" clas="q-ma-xs q-pb-sm" colr="amber-9" ttip="Player List" @click="openApp('PlayerList')" />
    <RoundButton size="20px" icon="TL" clas="q-ma-xs q-pb-sm" colr="teal-10" ttip="All Club Games" @click="openApp('TournamentList')" />
    <RoundButton size="20px" icon="img:icons/hole_in_one.png" clas="q-ma-xs q-pb-sm" colr="green-9"  ttip="Golf Course Details" @click="openApp('CourseDetails')" />
    <RoundButton size="20px" icon="SG" colr="pink" clas="q-ma-xs q-pb-sm"   ttip="Game Registration" @click="openApp('Signup')" />
    <RoundButton size="20px" icon="JZ" colr="blue-9" clas="q-ma-xs q-pb-sm" ttip="JZ's Match" @click="openApp('JZsMatch')" />
    <RoundButton size="20px" icon="KJ" colr="red-10" clas="q-ma-xs q-pb-sm" ttip="MM's Match" @click="openApp('KJsMatch')" />
    <RoundButton size="20px" icon="AL" colr="indigo-10" clas="q-ma-xs q-pb-sm" ttip="AL's Match" @click="openApp('ALsMatch')" />
    <RoundButton size="20px" icon="GL" colr="green-10" clas="q-ma-xs q-pb-sm" ttip="(Admin)PGC Game Grouping, Entering Scores, etc." @click="openApp('PGCGroupList')" />
    <RoundButton size="20px" icon="PG" colr="green-10" clas="q-ma-xs q-pb-sm" ttip="PGC Game List of Expired Games with scores(regular users)" @click="openApp('PGCGameList')" />
    <RoundButton size="20px" icon="PR" colr="green-10" clas="q-ma-xs q-pb-sm" ttip="PGC Game Rules" @click="showPGCRules()" />
    <RoundButton size="20px" icon="card_giftcard" clas="q-ma-xs" colr="pink"   ttip="Holidays" @click="showHolidays" />
    <RoundButton size="20px" icon="DV" clas="q-ma-xs q-pb-sm" colr="blue-10"   ttip="device detect" @click="showDeviceType" />
    <RoundButton size="20px" icon="monitor" clas="q-ma-xs q-pb-sm" colr="green" ttip="Show Log" @click="openApp('LoadLogPage')" v-if="JZsAdmin" />
    <RoundButton size="20px" icon="login"  colr="green-9" clas="q-ma-xs" ttip='Admin Login'  @click="login()" v-if="!(JZsAdmin || SysAdmin || PGCsAdmin)" />
    <RoundButton size="20px" icon="logout" colr="brown-9" clas="q-ma-xs" ttip='SysAdmin Logout' @click="logout()" v-else-if="SysAdmin" />
    <RoundButton size="20px" icon="logout" colr="red" clas="q-ma-xs" ttip='PGCsAdmin Logout' @click="logout()" v-else-if="PGCsAdmin" />
    <RoundButton size="20px" icon="logout" colr="blue-9" clas="q-ma-xs" ttip='JZsAdmin Logout' @click="logout()" v-else-if="JZsAdmin" />
    <RoundButton size="20px" icon="question_mark" colr="blue-10" clas="q-ma-xs" ttip='App Details for Each Button' @click="opened=!opened" />
    <q-btn size="20px" round glossy icon="info" class="q-ma-xs" color="green-10" ttip='System Information' @click="showSysInfo">
      <b style="margin-top:-21px" class="text-cyan-2 text-h6">{{ compVer }}</b>
    </q-btn>
  </q-card-actions>
  <div>
    <q-dialog v-model="opened" :maximized="isIM">
      <q-list class="bg-cyan-1">
        <q-item clickable v-close-popup>
          <q-item-section avatar>
            <RoundButton icon="PL" colr="amber-9" clas="q-ma-xs q-pb-sm" ttip="Player List" @click="openApp('PlayerList')" />
          </q-item-section>
          <q-item-section class="cursor-pointer" @click="openApp('PlayerList')">
            <q-item-label class="text-h6">Players List</q-item-label>
            <q-item-label caption>Players in Club</q-item-label>
          </q-item-section>
        </q-item>
        <q-item>
          <q-item-section avatar>
            <RoundButton icon="TL" colr="teal-10" clas="q-ma-xs q-pb-sm" ttip="All Club Games" @click="openApp('TournamentList')" />
          </q-item-section>
          <q-item-section class="cursor-pointer" @click="openApp('TournamentList')">
            <q-item-label class="text-h6">Tournament List</q-item-label>
            <q-item-label caption>All Club Games</q-item-label>
          </q-item-section>
        </q-item>
        <q-item>
          <q-item-section avatar>
            <RoundButton icon="img:icons/hole_in_one.png" colr="green-9" clas="q-ma-xs q-pb-xs" ttip="Golf Course Details" @click="openApp('CourseDetails')" />
          </q-item-section>
          <q-item-section class="cursor-pointer" @click="openApp('CourseDetails')">
            <q-item-label class="text-h6">Golf Course Information</q-item-label>
            <q-item-label caption>Details with slop/rating/yardage/handicaps</q-item-label>
          </q-item-section>
        </q-item>
        <q-item>
          <q-item-section avatar>
            <RoundButton icon="SG" colr="pink" clas="q-ma-xs q-pb-sm" ttip="Game Registration" @click="openApp('Signup')" />
          </q-item-section>
          <q-item-section class="cursor-pointer" @click="openApp('Signup')">
            <q-item-label class="text-h6">PGC Game Signup </q-item-label>
            <q-item-label caption>Game Registration</q-item-label>
          </q-item-section>
        </q-item>
        <q-item>
          <q-item-section avatar>
            <RoundButton icon="GL" colr="purple" clas="q-ma-xs q-pb-sm" ttip="Game Groups" @click="openApp('PGCGroupList')" />
          </q-item-section>
          <q-item-section class="cursor-pointer" @click="openApp('PGCGroupList')">
            <q-item-label class="text-h6">PGC Game Groups </q-item-label>
            <q-item-label caption>Show groups for games of the year</q-item-label>
          </q-item-section>
        </q-item>
        <q-item>
          <q-item-section avatar>
            <RoundButton icon="AL" colr="teal-8" clas="q-ma-xs q-pb-sm" ttip="AL's Match" @click="openApp('ALsMatch')" />
          </q-item-section>
          <q-item-section class="cursor-pointer" @click="openApp('ALsMatch')">
            <q-item-label class="text-h6">AL's Matches</q-item-label>
            <q-item-label caption>Alan Long team matches/groups/team score personal avg/score</q-item-label>
          </q-item-section>
        </q-item>
        <q-item>
          <q-item-section avatar>
            <RoundButton icon="KJ" colr="red-10" clas="q-ma-xs q-pb-sm" ttip="KJ's Match" @click="openApp('KJsMatch')" />
          </q-item-section>
          <q-item-section class="cursor-pointer" @click="openApp('KJsMatch')">
            <q-item-label class="text-h6">KJ's Matches</q-item-label>
            <q-item-label caption>Team matches/groups/team score personal avg/score</q-item-label>
          </q-item-section>
        </q-item>
        <q-item>
          <q-item-section avatar>
            <RoundButton icon="JZ" colr="blue" clas="q-ma-xs q-pb-sm" ttip="JZ's Match" @click="openApp('JZsMatch')" />
          </q-item-section>
          <q-item-section class="cursor-pointer" @click="openApp('JZsMatch')">
            <q-item-label class="text-h6">JZ's Matches</q-item-label>
            <q-item-label caption>Team matches/groups/team score personal avg/score</q-item-label>
          </q-item-section>
        </q-item>
        <q-item>
          <q-item-section avatar>
            <RoundButton icon="PG" colr="green-10" clas="q-ma-xs q-pb-sm" ttip="PGC Games List/Admin" @click="openApp('PGCGameList')" />
          </q-item-section>
          <q-item-section class="cursor-pointer" @click="openApp('PGCGameList')">
            <q-item-label class="text-h6">PGC Games</q-item-label>
            <q-item-label caption>Admin:grouping, score entering, etc.</q-item-label>
          </q-item-section>
        </q-item>
        <q-item>
          <q-item-section avatar>
            <RoundButton icon="PR" colr="indigo" clas="q-ma-xs q-pb-sm" ttip="PGC Game Rules" @click="showPGCRules()" />
          </q-item-section>
          <q-item-section class="cursor-pointer" @click="showPGCRules()">
            <q-item-label class="text-h6">PGC Game Rules</q-item-label>
            <q-item-label caption>The rules applied to all PGC games unless otherwise specified</q-item-label>
          </q-item-section>
        </q-item>
        <q-item>
          <q-item-section avatar>
            <RoundButton icon="card_giftcard" colr="pink" clas="q-ma-xs q-pb-xs" ttip="Holidays" @click="showHolidays" />
          </q-item-section>
          <q-item-section class="cursor-pointer" @click="showHolidays()">
            <q-item-label class="text-h6">Show Federal Holidays</q-item-label>
            <q-item-label caption>Show holidays for chosen year</q-item-label>
          </q-item-section>
        </q-item>
        <q-item v-if="JZsAdmin">
          <q-item-section avatar>
            <RoundButton icon="monitor" colr="green" clas="q-ma-xs q-pb-sm" ttip="Log Page" @click="openApp('LoadLogPage')" />
          </q-item-section>
          <q-item-section class="cursor-pointer" @click="openApp('LoadLogPage')">
            <q-item-label class="text-h6">Show Golf Log Page</q-item-label>
            <q-item-label caption>Golf Log List</q-item-label>
          </q-item-section>
        </q-item>
        <q-item class="row">
          <q-item-section avatar>
            <RoundButton icon="logout" colr="black"   clas="q-ma-xs q-pb-xs" ttip='SysAdmin Logout' @click="logout()" v-if="SysAdmin" />
            <RoundButton icon="logout" colr="red"     clas="q-ma-xs q-pb-xs" ttip='PGCsAdmin Logout' @click="logout()" v-else-if="PGCsAdmin" />
            <RoundButton icon="logout" colr="blue-10" clas="q-ma-xs q-pb-xs" ttip='JZsAdmin Logout' @click="logout()" v-else-if="JZsAdmin" />
            <RoundButton icon="login"  colr="green-9" clas="q-ma-xs q-pb-xs" ttip='Admin Login'  @click="login()"  v-else />
          </q-item-section>
          <q-item-section v-if="SysAdmin" class="cursor-pointer" @click="logout()">
            <q-item-label class="text-h6">SysAdmin logout</q-item-label>
            <q-item-label caption>SysAdmin logged on</q-item-label>
          </q-item-section>
          <q-item-section v-else-if="PGCsAdmin" class="cursor-pointer" @click="logout()">
            <q-item-label class="text-h6">PGCsAdmin logout</q-item-label>
            <q-item-label caption>PGCsAdmin logged on</q-item-label>
          </q-item-section>
          <q-item-section v-else-if="JZsAdmin" class="cursor-pointer" @click="logout()">
            <q-item-label class="text-h6">JZsAdmin logout</q-item-label>
            <q-item-label caption>JZsAdmin logged on</q-item-label>
          </q-item-section>
          <q-item-section v-else class="cursor-pointer" @click="login()">
            <q-item-label class="text-h6">Admin Login</q-item-label>
            <q-item-label caption>for SysAdmin or PGCsAdmin or JZsAdmin</q-item-label>
          </q-item-section>
          <q-btn flat color="red" label="close" v-close-popup />
        </q-item>
      </q-list>
    </q-dialog>
  </div>
</q-page>
<DeviceType />
<PlatformDataPad ref="refPlatformDataPad" />
<LoginDialog />
</template>
<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
// import { useStore } from 'vuex'
import emitter from 'tiny-emitter/instance'
import { axiosFunctions } from 'src/composables/axiosFunctions'
import { libFunctions } from 'src/composables/libFunctions'
import RoundButton from '../components/RoundButton'
import LoginDialog  from './LoginDialog'
// import Holidays from 'src/components/HolidayDialog'
import DeviceType from 'src/components/DeviceType'
import PlatformDataPad from '../components/PlatformDataPad'

const { gaxios } = axiosFunctions()
const { store, isIM, SysAdmin, PGCsAdmin, JZsAdmin, golfUserType } = libFunctions()
const router = useRouter()

const opened = ref(false)
const mcount = ref(0)
const fcount = ref(0)
const refPlatformDataPad = ref(null)

const pageTitle = ref('Princeton SU Golf Club')
console.log(`-ST-MyIndex JZsAdmin=${JZsAdmin.value} SysAdmin=${SysAdmin.value} PGCsAdmin=${PGCsAdmin.value}`)
// let golf_usertype = q.localStorage.getItem('golf_usertype')
store.page = 'home'
store.pageTitle = pageTitle.value
getPlayerCount()
emitter.on('golf-usertype', (x) => store.usertype = x)
emitter.on('golf-logout', () => store.usertype = null)
emitter.on('golf-getPlayerCount', (x) => {mcount.value = x.mcnt;fcount.value = x.fcnt})

const compVer = computed(() => { return process.env.VER })

function showSysInfo () {
  // console.log(`-fn-showSysInfo ${compVER.value}`)
  refPlatformDataPad.value.openIt()
}
function getPlayerCount () {
  const path = process.env.API + '/golf/getPlayerCount'
  gaxios(path)
}
function openApp(app) {
  emitter.emit('app-path', app)
  router.push({ path:app })
  // window.location.href = app
}
emitter.on('golf-logout', () => golfUserType.value = null)
function logout () {
  const path = process.env.API + '/golf/logout'
  // q.localStorage.set('golf_usertype', null)
  emitter.emit('golf-usertype', null)
  store.usertype = null
  gaxios(path)
  // console.log(`-fn-logout JZsAdmin=${JZsAdmin.value}`)
}
function login () {
  console.log(`-fn-login usertype=${store.usertype} SysAdmin=${SysAdmin.value}`)
  // console.log(`-fn-login`)
  emitter.emit('open-LoginDialog')
}
function showDeviceType () {
  emitter.emit('show-DeviceType')
  console.log('-fn-showDeviceType')
}
function showHolidays () {
  // console.log('-fn-showHolidays')
  // Holidays.value.openIt()
  // currentTab.value = 'Holidays'
  emitter.emit('show-holidays')
}
function showPGCRules () {
  // console.log('-fn-showPGCRules')
  const gameId = 0 // 总则 1=1st tournament rule, 2=2nd tournament rule, etc.
  const path = process.env.API + '/golf/getPGCRules/' + gameId
  gaxios(path)
}
</script>

<template>
<q-layout view="hHh Lpr fFf" container style="height:1530px">
  <q-header elevated class="bg-cyan-8">
    <q-toolbar>
      <q-btn v-if="isDesk" flat @click="drawer=!drawer" round dense icon="menu" />
      <q-btn v-else to="/" round dense glossy color="blue-6"><q-icon name="🏠" style="margin:-12px 0 0 0" /></q-btn>
      <q-toolbar-title class="cursor-pointer" @click="reloadPage()">{{ pageTitle }}</q-toolbar-title>
      <q-input v-if="showSearch" :style="isIM ? { width:'90px' } : { width:'200px'}" dark borderless v-model="searchQuery" class="text-right text-h6" dense @keyup="search()">
        <template v-slot:append>
          <q-icon v-if="searchQuery===''" name="search"/>
          <q-icon v-else name="clear" class="cursor-pointer" @click="searchQuery='';search()" />
        </template>
      </q-input>
      <q-btn v-if="pageTitle=='Course Details' && SysAdmin" class="q-px-sm q-mx-sm" glossy dense icon="golf_course" label="Add New Course" color="red" @click="addNewCourse()" />
      <q-btn round dense icon="help" @click="showUserGuide()" />
      <!-- <q-btn round dense icon="devices" @click="refPlatformDataPad.openIt()" /> -->
      <q-btn v-if="SysAdmin && updateUserGuide" outline rounded dense icon-right="update" :label="isDesk ? 'update user guide' : 'user guide'" @click="updateUserGuide()" class="q-pl-sm" />
    </q-toolbar>
  </q-header>
  <q-drawer v-if="isDesk" v-model="drawer" :width="210" :breakpoint="400" show-if-above :style="{ background:'white', color:'black', fontSize:'18px' }">
    <q-scroll-area style="height: calc(100% - 200px); margin-top: 200px; border-right: 1px solid #ddd">
      <q-list padding>

        <!-- <q-item clickable v-ripple to='/PlayerList'> // impacted by style in the other components  -->
        <q-item clickable v-ripple @click="openApp('PlayerList', 'Player List')">
          <q-item-section avatar>
            <q-icon name="person" color="orange-9" size="30px" />
          </q-item-section>
          <q-item-section>
            Player List
          </q-item-section>
        </q-item>

        <!-- <q-item clickable v-ripple to='/TournamentList'> -->
        <q-item clickable v-ripple @click="openApp('TournamentList', 'Tournament List')">
          <q-item-section avatar>
            <q-icon name="assignment" color="blue" size="26px" />
          </q-item-section>
          <q-item-section>
            Tournaments
          </q-item-section>
        </q-item>

        <!-- <q-item clickable v-ripple to='/CourseDetails'> -->
        <q-item clickable v-ripple @click="openApp('CourseDetails', 'Course Details')">
          <q-item-section avatar>
            <q-icon name="golf_course" color="green-9" size="30px" />
          </q-item-section>
          <q-item-section>
            Golf Courses
          </q-item-section>
        </q-item>

        <q-item clickable v-ripple @click="openApp('Signup', 'Sign up Games')">
          <q-item-section avatar>
            <q-icon name="assignment" color="pink" size="30px" />
          </q-item-section>
          <q-item-section>
            Signup Game
          </q-item-section>
        </q-item>

        <!-- <q-item clickable v-ripple @click="openApp('PGCGroupList', 'PGC Group List')">
          <q-item-section avatar>
            <q-icon name="group_add" color="cyan-9" size="30px" />
          </q-item-section>
          <q-item-section v-if="doGroup">
            Do Grouping
          </q-item-section>
          <q-item-section v-else>
            Group List
          </q-item-section>
        </q-item> -->

        <q-item clickable v-ripple @click="openApp('JZsMatch', 'JZ\'s Match', 13)">
          <q-item-section avatar>
            <q-icon name="img:icons/JZ.png" size="30px" />
          </q-item-section>
          <q-item-section>
            JZ's Match
          </q-item-section>
        </q-item>

        <q-item clickable v-ripple @click="openApp('KJsMatch', 'KJ\'s Match', 14)">
          <q-item-section avatar>
            <q-icon name="img:icons/KJ.png" size="30px" />
          </q-item-section>
          <q-item-section>
            KJ's Match
          </q-item-section>
        </q-item>

        <q-item clickable v-ripple @click="openApp('ALsMatch', 'AL\'s Match', 16)">
          <q-item-section avatar>
            <q-icon name="img:icons/AL.png" size="30px" />
          </q-item-section>
          <q-item-section>
            AL's Match
          </q-item-section>
        </q-item>

        <q-item clickable v-ripple @click="openApp('PGCGroupList', 'PGC Group List')">
          <q-item-section avatar>
            <q-icon name="img:icons/PGC.png" color="cyan-9" size="30px" />
          </q-item-section>
          <q-item-section v-if="PGCsAdmin">
            Do Grouping
          </q-item-section>
          <q-item-section v-else>
            Groups
          </q-item-section>
        </q-item>

        <q-item clickable v-ripple @click="openApp('PGCGameList', 'Club Outings, Tournaments and PlayOff')">
          <q-item-section avatar>
            <q-icon name="img:icons/PGC.png" color="blue" size="30px" />
          </q-item-section>
          <q-item-section>
            Games
          </q-item-section>
        </q-item>

        <q-item clickable v-ripple @click="openApp('PGCGameRules', 'PGC Game Rules')">
          <q-item-section avatar>
            <q-icon name="img:icons/PGC.png" color="green-10" size="30px" />
          </q-item-section>
          <q-item-section>
            Rules
          </q-item-section>
        </q-item>

        <q-item clickable v-ripple  @click="showHolidays()">
          <q-item-section avatar>
            <q-icon name="card_giftcard" color="pink" size="30px"/>
          </q-item-section>
          <q-item-section>
            Holidays
          </q-item-section>
        </q-item>

        <q-item v-if="SysAdmin" clickable v-ripple>
          <q-item-section avatar><q-icon name="settings" color="grey-0" size="30px"/></q-item-section>
          <q-item-section class="text-no-wrap">SysAdmin On</q-item-section>
        </q-item>
        <q-item v-if="SysAdmin" clickable v-ripple>
          <q-item-section avatar><q-icon name="settings" color="grey-0" size="30px"/></q-item-section>
          <q-item-section class="text-no-wrap">Maintainence</q-item-section>
        </q-item>
        <q-item v-if="SysAdmin" clickable v-ripple @click="logout()">
          <q-item-section avatar><q-icon name="logout" color="green-9" size="30px"/></q-item-section>
          <q-item-section class="text-no-wrap">Sys Logout</q-item-section>
        </q-item>
        <q-item v-if="JZsAdmin" clickable v-ripple @click="logout()">
          <q-item-section avatar><q-icon name="logout" color="green-9" size="30px"/></q-item-section>
          <q-item-section class="text-no-wrap">JZs Logout</q-item-section>
        </q-item>
        <q-item v-if="KJsAdmin" clickable v-ripple @click="logout()">
          <q-item-section avatar><q-icon name="logout" color="green-9" size="30px"/></q-item-section>
          <q-item-section class="text-no-wrap">KJs Logout</q-item-section>
        </q-item>
        <q-item v-if="ALsAdmin" clickable v-ripple @click="logout()">
          <q-item-section avatar><q-icon name="logout" color="green-9" size="30px"/></q-item-section>
          <q-item-section class="text-no-wrap">ALs Logout</q-item-section>
        </q-item>
        <q-item v-if="PGCsAdmin" clickable v-ripple @click="logout()">
          <q-item-section avatar><q-icon name="logout" color="green-9" size="30px"/></q-item-section>
          <q-item-section class="text-no-wrap">PGCs Logout</q-item-section>
        </q-item>
        <q-item v-else-if="doGroup" clickable v-ripple @click="logout()">
          <q-item-section avatar><q-icon name="logout" color="pink" size="30px"/></q-item-section>
          <q-item-section class="text-no-wrap">DoGroup Logout</q-item-section>
        </q-item>
        <q-item v-else-if="!(SysAdmin || JZsAdmin || KJsAdmin || ALsAdmin || PGCsAdmin)" clickable v-ripple @click="showLoginDialog()">
          <q-item-section avatar><q-icon name="login" color="green" size="30px"/></q-item-section>
          <q-item-section>System Login</q-item-section>
        </q-item>
        <q-item v-if="SysAdmin" clickable v-ripple @click="showRegisterDialog()">
          <q-item-section avatar><q-icon name="app_registration" color="accent" size="30px"/></q-item-section>
          <q-item-section class="text-no-wrap">Create Account</q-item-section>
        </q-item>
        
        <q-item v-if="JZsAdmin" clickable v-ripple @click="openApp('LoadLogPage', 'Show Log Page', 'Golf Logs')">
          <q-item-section avatar>
            <q-icon name="monitor" color="red" size="30px" />
          </q-item-section>
          <q-item-section>
            Show Logs
          </q-item-section>
        </q-item>

        <q-item v-if="compVer==undefined" clickable v-ripple @click="openApp('DevTest', 'Testing New', 'Testing New')">
          <q-item-section avatar>
            <q-icon name="developer_mode" color="indigo" size="30px" />
          </q-item-section>
          <q-item-section>
            Testing New
          </q-item-section>
        </q-item>

        <div class="row text-grey-3 text-h5 q-pl-md">
          <q-icon :name="compVer" />
          <span class="q-pl-sm">{{ $q.version }}</span>
        </div>
      </q-list>
    </q-scroll-area>

    <q-img class="absolute-top cursor-pointer" src="icons/club-logo-full.png" style="height:170px" @click="goHome"> 
      <div class="absolute-bottom bg-transparent">
        <q-item clickable v-ripple @click="goHome" />
        <q-avatar size="38px" class="q-mb-sm"><img src="icons/boy.png"></q-avatar>
        <q-avatar size="38px" class="q-mb-sm" style="padding:0 0 0 105px"><img src="icons/girl.png"></q-avatar>
      </div>
    </q-img>
  </q-drawer>
  <q-page-container>
    <router-view />
  </q-page-container>
</q-layout>
<!-- <component :is="holidays" />
<component :is="LoginDialog" />
<component :is="UserGuidePad" />
<component :is="RegisterDialog" />
<component :is="InfoDisplay" />
<PlatformDataPad ref="refPlatformDataPad" /> -->

</template>
<script setup>
/* eslint-disable */
import { ref, computed, getCurrentInstance, onMounted } from 'vue'
// import { useQuasar } from 'quasar'
import holidays from 'src/components/HolidayDialog'
import InfoDisplay from 'src/components/InfoDisplay'
import LoginDialog from 'pages/LoginDialog'
import UserGuidePad from '../components/UserGuidePad'
import RegisterDialog from 'pages/RegisterDialog'
import emitter from 'tiny-emitter/instance'
import { axiosFunctions } from '../composables/axiosFunctions'
import { libFunctions } from '../composables/libFunctions'
import { dayFunctions } from '../composables/dayFunctions'
import PlatformDataPad from '../components/PlatformDataPad'

// const app = getCurrentInstance()
// const store = app.appContext.config.globalProperties.$store
// import { useStore } from 'vuex'
// const store = useStore()
// const $q = useQuasar()
const { $q, store, golfUserType, JZsAdmin, KJsAdmin, ALsAdmin, SysAdmin, isIM, isDesk, PGCsAdmin, doGroup, pagename, userGuidePage } = libFunctions()
const { yyyymmdd } = dayFunctions()

const oneHour = 1000 * 60 * 60
const curApp = ref(null)

emitter.on('golf-getUserType', (x) => setUserType(x))
// emitter.on('golf-usertype', (x) => { golfUserType.value = x })

store.pageTitle = 'Princeton SU Golf Club'

emitter.on('golf-getPGCRules', (x) => setPGCRules(x))
emitter.on('golf-delTournament', (x) => reloadMatches(x.gameId))
emitter.on('golf-updTournament', (x) => reloadMatches(x.gameId))
emitter.on('golf-getUserGuide', (x) => setUserGuide(x))
// emitter.on('golf-getUserGuideId', (x) => setUserGuideId(x))
// emitter.on('app-path', (x) => this.app = x)
// emitter.on('upd-user-guide', (x) => this.updateUserGuide = x)
emitter.on('cur-tit', (ctit) => { setTitle(ctit); setInterval(setTitle, oneHour, ctit) })
emitter.on('cur-app', (capp, from) => curApp.value = capp)
emitter.on('num-items', (x) => numItems.value = x)

const { gaxios }= axiosFunctions()
// const { isIM, isDesk, iPhone, iPhone13, screenwidth, screenheight } = libFunctions()

const compVer = computed(() => { return process.env.VER })
// const pagename = computed({ 
//   get: () => store.state.golf.page,
//   set: val => store.commit('golf/setPage', val)
// })
const showSearch = computed(() => { return ['PGCGames', 'PlayerList', 'tournmaentList', 'grouping', 'team_match_top', 'JZsMatch', 'LoadLogPage'].indexOf(pagename.value)>=0 })
const pageTitle = computed(() => { return store.pageTitle })
const userGuideId = computed({ 
  get: () => store.userGuideId,
  set: val => store.userGuideId = val
})
const userGuidePageInDb = computed({
  get: () => store.pageInDb,
  set: val => store.pageInDb = val
})
const userGuide = computed({
  get: () => store.userGuide,
  set: val => store.userGuide = val
})
const searchQuery = ref('')
const drawer = ref(false)
const gcinfo = ref(false)
const appname = ref(null)
const refPlatformDataPad = ref(false)

console.log(`-CK-showSearch=${showSearch.value}`)
console.log(`-CK-pageTitle=${pageTitle.value}`)
onMounted(() => {
  refPlatformDataPad
})

function addNewCourse () {
  // console.log('-CK-fn-addNewCourse')
  emitter.emit('add-new-course')
}
function setUserType (da) {
  // console.log('-CK-fn-setUsertype', da.usertype)
  store.userType = da.usertype
  // console.log(`-CK-fn-setUserType match Login JZsAdmin=${JZsAdmin.value} SysAdmin=${SysAdmin.value} PGCsAdmin=${PGCsAdmin.value} usertype=${da.usertype}`)
}
function getUserType () {
  const path = process.env.API + '/golf/getUserType'
  gaxios(path)
}
function logout () {
  const path = process.env.API + '/golf/logout'
  gaxios(path)
  emitter.emit('golf-usertype', null)
  store.userType = null
  $q.notify({
    color: "yellow",
    textColor: "red-10",
    icon: "logout",
    message: "Logout",
  })
}
function search () {
  // console.info('-fn-search', searchQuery.value)
  emitter.emit('search', searchQuery.value)
}
function showHolidays () {
  console.info('-fn-showHolidays')
  // holidays.value.openIt()
  emitter.emit('show-holidays')
}
function openApp (app, pageTitle, gameId=null) {
  console.info(`-fn-openApp app = ${app}`)
  appname.value = app
  if (app == 'DevTest') {
    // const xlsxFile = '\\\/home\\\/swang\\\/kjsfile.xlsx';
    const xlsxFile = 'kjsfile.xlsx';
    const path = process.env.API + '/DevTest/processKjExcel/' + xlsxFile
    return gaxios(path)
  }
  // gameId.value = gameId
  if (appname.value === 'PGCGameRules') { return showPGCRules() }
  // console.info(`-fn-openApp app=${appname.value}, pagename=${pagename.value}, pageTitle=${pageTitle.value}, gameId=${gameId.value}, SysAdmin=${SysAdmin.value}`)
  // this.$router.push({ path: app, params: { gameId: gameId } })
  // this.$router.push({ name: app, params: { gameId: gameId } })
  // this.$router.push({ path: app })
  window.location.href = appname.value
}
function reloadMatches (gameId) {
  console.log(`-fn-reloadMatches gameId=${gameId}`)
  if (gameId == 13) openApp('JZsMatch', 'JZ\'s Match', gameId)
  if (gameId == 14) openApp('KJsMatch', 'KJ\'s Match', gameId)
  if (gameId == 16) openApp('ALsMatch', 'AL\'s Match', gameId)
}
function showPGCRules () {
  console.info('-fn-showPGCRules')
  const gameId = 0 //　总则　1=1st tournament rule, 2=2nd tournament rule, etc.
  const path = process.env.API + '/golf/getPGCRules/' + gameId
  gaxios(path)
}
function setPGCRules (da) {
  console.info('-fn-setPGCRules', da)
  const tit = '<div class="text-amber">俱乐部比赛规则</div>'
  const msg = da.rules
  // InfoDisplay.value.openIt(tit, `${msg}`)
  emitter.emit('open-InfoDisplay', tit, msg)
}
const getUserGuideTitle = () => {
  return 'User Guide for ' + userGuidePage.value.replace('_', ' ')
}
function setUserGuide (da) {
  console.log(`-fn-setUserGuide userGuidePage=${userGuidePage.value}, userGuidePageInDb=${userGuidePageInDb.value}`)
  userGuideId.value = da.id
  userGuidePageInDb.value = da.pagename
  userGuide.value = da.userguide
  // const tit = 'User Guide for ' + pagename.value
  const tit = getUserGuideTitle()
  if (updateUserGuide.value) emitter.emit('open-UserGuidePad', userGuidePage.value, userGuideId.value, userGuide.value)
  else emitter.emit('open-InfoDisplay', tit, userGuide.value)
}
function showUserGuide () {
  console.log(`-fn-showUserGuide userGuide=${userGuide.value}, userGuidePageInDb=${userGuidePageInDb.value} pagename=${pagename.value}`)
  if (pagename.value === 'MatchGrouping14') {
    emitter.emit('user-guide-KJsMatch')
    return
  } else if (pagename.value === 'MatchGrouping') {
    emitter.emit('user-guide-JZsMatch')
    return
  }
  if (userGuidePage.value == userGuidePageInDb.value) {
    // const tit = 'User Guide for ' + pagename.value
    const tit = getUserGuideTitle()
    emitter.emit('open-InfoDisplay', tit, userGuide.value)
    return
  }
  getUserGuide()
  // InfoDisplay.value.openIt(tit, userGuide.value)
}
function getUserGuide (upd=false) {
  if (!upd) gcinfo.value = !gcinfo.value
  // let page = 'JZsMatch'
  // if (pagename.value == 'ALsMatch') page = 'JZsMatch'
  // else page = pagename.value
  const path = process.env.API + '/golf/getUserGuide/' + userGuidePage.value
  gaxios(path)
}
// function setUserGuideId (da) {
//   console.log(`-fn-setUserGuideId id=${da.userGuideId}`)
//   store.commit('golf/setUserGuideId', da.userGuideId)
//   userGuideId.value = da.userGuideId
//   // emitter.emit('open-UserGuidePad', userGuidePage.value, userGuideId.value, userGuide.value)
// }
// emitter.on('golf-getUserGuideId', (x) => setUserGuideId(x))
// function getUserGuideId () {
//   const path = process.env.API + '/golf/getUserGuideId/' + pagename.value
//   gaxios(path)
// }
function updateUserGuide () {
  updateUserGuide.value = true
  getUserGuide()
  // getUserGuide(true)
  // getUserGuideId()
  // console.info(`-fn-updateUserGuide for userGuidePage=${userGuidePage.value} id=${userGuideId.value}`)
  // UserGuidePad.value.openIt(userGuidePage.value, userGuideId.value, userGuide.value)
  // emitter.emit('open-UserGuidePad', userGuidePage.value, userGuideId.value, userGuide.value)
}
function showLoginDialog () {
  console.info('-fn-MyLayout showLoginDialog()')
  // LoginDialog.value.show()
  emitter.emit('open-LoginDialog')
}
function showRegisterDialog () {
  console.info('-fn-MyLayout showRegisterDialog')
  // RegisterDialog.value.show()
  emitter.emit('open-RegisterDialog')
}
function reloadPage () {
  // console.info('-fn-reloadPage', this.app, this.pageTitle, this.gameId, document.URL)
  // this.$router.push({ path: 'JZsMatch', params: { gameId: 13 } })
  // this.$router.push({ path:this.app })
  // window.location.href = this.app
  window.location.href = document.URL
}
function goHome () {
  userGuidePage.value = 'Home'
  window.location.href = '/'
}
function setTitle (tit) {
  let tm = (new Date()).toString().split(' ')[4]
  console.log(`-ck-%c${tm} setTitle`, 'color:indianRed;font-size:13px')
  const ymd = (new Date()).yyyymmdd()
  // this.appTitle = tit.split(' ')[0] + ' ' + ymd + ' (' + (this.isDesk ? this.getDay2(ymd) : this.getDay3(ymd)) + ')'
  let appTitle = tit
  if (isDesk) appTitle += ' ' + ymd + ' (' + ymd.chwk3() + ')'
  document.title = tit
}
// === main ===
userGuidePage.value = 'Home'
getUserType()
// getUserGuideId()
document.title = 'Golf'
</script>
<style>
html {
  overflow: scroll;
  overflow-x: hidden;
  -ms-overflow-style: none;
  scrollbar-width: none;
}
::-webkit-scrollbar {
    width: 0px;  /* Remove scrollbar space */
    background: transparent;  /* Optional: just make scrollbar invisible */
}
/* Optional: show position indicator in red */
::-webkit-scrollbar-thumb {
    background: #FF0000;
}
::-moz-scrollbar {
    width: 0px;  /* Remove scrollbar space */
    background: transparent;  /* Optional: just make scrollbar invisible */
}
/* Optional: show position indicator in red */
::-moz-scrollbar-thumb {
    background: #FF0000;
}
::-ms-scrollbar {
    width: 0px;  /* Remove scrollbar space */
    background: transparent;  /* Optional: just make scrollbar invisible */
}
::-ms-scrollbar-thumb {
    background: #FF0000;
}
.rdrawer {
  padding-left: -10px;
}
</style>

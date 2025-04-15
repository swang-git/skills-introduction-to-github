<template>
<!-- <div :class="{ flayout:isIM, dlayout:isDesk }"> -->
<div class="bg-teal-9">
  <q-layout view="hHh Lpr lFr">
    <q-header v-if="curApp!='arts'" class="bg-teal-9 inset-shadow-down">
      <q-toolbar style="margin-left:-5px">
        <q-btn v-if="isDesk" glossy @click="drawerClick()" round dense icon="img:icons/quasar-logo.svg" size="18px" />
        <!-- <q-btn v-else to="/" round dense glossy color="blue"><q-icon name="🏠" style="margin:-13px 0 0 0" /></q-btn> -->
        <q-btn v-else to="/" round dense glossy color="blue"><q-icon :name="compVer" style="margin:-13px 0 0 0" /></q-btn>
        <q-toolbar-title>
          <div class="row q-pt-sm no-wrap">
            <span >{{ appTitle }}</span>
            <div style="float:right">
              <q-btn v-if="/Expense/i.test(curApp)" round glossy color="teal-9" @click="showYearChart" style="margin:-8px 10px 0 3px">
                <q-icon name="donut_small" color="lime" />
                <q-tooltip class="text-h6 bg-indigo-9 text-yellow">消费分类总览（总支出分类）</q-tooltip>
              </q-btn>
              <q-btn v-if="/Expense/i.test(curApp) && audCookies" round glossy color="indigo-9" @click="toggleAUD" style="margin:-8px 10px 0 3px">
                <q-icon name="track_changes" color="cyan-2" />
                <q-tooltip class="text-h6 bg-cyan-9">toggle show upd/add/del lines</q-tooltip>
              </q-btn>
              <q-btn v-if="/Expense/i.test(curApp)" round glossy color="brown-9" @click="checkGiftCardBalance" style="margin:-8px 10px 0 3px">
                <q-icon name="balance" color="amber" />
                <!-- <q-tooltip class="text-h6 bg-cyan-9">check gift card balances</q-tooltip> -->
              </q-btn>
              <q-btn v-if="/watcher/i.test(curApp)"    round glossy color="indigo" :icon="weightUnit" class="chicon-pos" @click="selectWeightUnit" />
              <q-btn v-if="/watcher/i.test(curApp)"    round glossy color="purple" icon="图" class="chicon-pos" @click="showWatcherChart" />
              <q-btn v-if="/glucoseche/i.test(curApp)" round glossy color="blue-9" icon="释" class="chicon-pos" @click="getA1cDefinitions()" />
              <q-btn v-if="/glucoseche/i.test(curApp)" round glossy color="teal-9" icon="空" class="chicon-pos" @click="showClvlChart" />
              <q-btn v-if="/bankstatem/i.test(curApp)" round glossy color="teal-9" icon="图" class="chicon-pos" @click="emitter.emit('show-charts')" />
              <q-btn v-if="/healthtest/i.test(curApp)" round glossy color="teal-9" icon="图" class="chicon-pos" @click="showHealthTestChart" />
              <div v-if="/tvmanager/i.test(curApp)" class="q-mb-sm q-mx-sm">
                <q-fab color="brown-9" padding="xs sm sm" label="Upcoming Recordings" direction="right" icon="history" >
                  <q-fab-action v-for="hr in [3, 5, 8, 10, 12, 24]" :key="hr" color="primary" @click="getTvShows(hr)" icon="history" :label="hr" class="text-h6" />
                </q-fab>
              </div>
            </div>
            <div v-if="isDesk">
              <q-input dark borderless v-model="searchQuery" :label="curApp=='Expense' ? null : 'Quasar Version: ' + $q.version" input-class="text-right text-h6" class="absolute-bottom-right" dense @keyup="search()">
                <template v-slot:append>
                  <q-icon v-if="searchQuery===''" name="search" />
                  <q-icon v-else name="clear" class="cursor-pointer" @click.stop.prevent="searchQuery='';search()" />
                </template>
              </q-input>
            </div>
            <div v-else>
              <q-input dark borderless v-model="searchQuery" input-class="text-right text-h6" class="absolute-bottom-right" dense @keyup="search()"  style="width:100px">
                <template v-slot:append>
                  <q-icon v-if="searchQuery===''" name="search" />
                  <q-icon v-else name="clear" class="cursor-pointer" @click.stop.prevent="searchQuery='';search()" />
                </template>
              </q-input>
            </div>
          </div>
        </q-toolbar-title>
      </q-toolbar>
    </q-header>

    <div v-if="curApp!='arts' && curApp!=='golf' && isDesk">
      <q-drawer v-model="drawer" :mini="isDesk ? true : !drawer || miniState" :width="230" :breakpoint="400" show-if-above class="bg-teal-10">
        <q-scroll-area class="fit" style="font-family:youyuan">
          <q-list padding style="margin-left:-10px">
            <AppItem appl="日 常 消 费" colr="purple-9" iclr="yellow" size="27px" styl="margin:-1px 0 0 0" icon="monetization_on" appn="exlist" />
            <AppItem appl="采 购 清 单" colr="indigo-9" iclr="white"  size="27px" styl="margin: 1px 0 0 0" icon="add_shopping_cart" appn="shopping" />
            <AppItem appl="温 馨 提 示" colr="teal-9"   iclr="white"  size="27px" styl="margin:-0px 0 0 0" icon="schedule" appn="reminder" />
            <AppItem appl="备 忘 录 表" colr="grey-9"   iclr="white"  size="27px" styl="margin: 0px 0 0 0" icon="assignment" appn="memo" />
            <AppItem appl="每 天 看 看" colr="pink-9"   iclr="yellow" size="25px" styl="margin:-6px 0 0 0" icon="健" appn="watcher" />
            <AppItem appl="银 行 月 报" colr="indigo-9" iclr="yellow" size="28px" styl="margin: 1px 0 0 0" icon="account_balance" appn="bankstatement" />
            <AppItem appl="月 报 明 细" colr="brown-9"  iclr="white"  size="25px" styl="margin:-6px 0 0 0" icon="析" appn="holdings" />
            <AppItem appl="血 糖 控 制" colr="pink-7"   iclr="yellow" size="27px" styl="margin:-1px 0 0 0" icon="bloodtype" appn="glucosecheck" />
            <AppItem appl="高 尔 夫 球" colr="green-9"  iclr="yellow" size="27px" styl="margin:-1px 0 0 0" icon="golf_course" appn="../golf" />
            <AppItem appl="网 上 阅 读" colr="indigo-9" iclr="white"  size="25px" styl="margin:-6px 0 0 0" icon="文" appn="../arts" />
            <AppItem appl="英 汉 字 典" colr="brown-9"  iclr="yellow" size="25px" styl="margin:-1px 0 0 0" icon="translate" appn="dictionary" />
            <AppItem appl="法 定 假 日" colr="pink-9"   iclr="yellow" size="25px" styl="margin:-1px 0 0 0" icon="card_giftcard" appn="" @click="showHolidays()" />
            <AppItem appl="月 报 分 析" colr="brown-9"  iclr="white"  size="25px" styl="margin:-6px 0 0 0" icon="报" appn="bankstatementloader" />
            <AppItem appl="健 康 检 查" colr="indigo-9" iclr="white"  size="25px" styl="margin:-6px 0 0 0" icon="查" appn="htlist" />
            <AppItem appl="电 视 列 表" colr="grey-9"   iclr="yellow" size="25px" styl="margin:-5px 0 0 0" icon="视" appn="tvmanager" />
            <!-- <AppItem appl="跳 转 首 页" colr="amber-9"                size="25px" styl="margin:-9px 0 0 0" icon="🏠" appn="/" /> -->
            <AppItem appl="跳 转 首 页" colr="blue-9"                size="25px" styl="margin:-9px 0 0 0" :icon="compVer" appn="/" />

            <!-- <q-icon v-if="compVer==null" name="普" class="text-h5" color="teal-4" style="margin-left:20px" />
            <q-icon v-else :name="compVer" class="text-h5" color="teal-6" style="margin-left:20px"/> -->
          </q-list>
        </q-scroll-area>

        <!--
          in this case, we use a button (can be anything)
          so that user can switch back
          to mini-mode
        -->
        <div class="q-mini-drawer-hide absolute" style="top: 15px; right: -17px">
          <q-btn dense round unelevated color="accent" icon="chevron_left" @click="miniState=true" />
        </div>
      </q-drawer>
      </div>

      <q-page-container>
        <q-page class="q-py-xs">
          <router-view />
        </q-page>
      </q-page-container>
      <!-- <q-footer class="bg-teal-9" v-if="['memo','reminder','expense','watcher','bankstatement', 'bank'].indexOf(curApp)>=0"> -->
      <q-footer class="bg-teal-10" v-if="/memo|reminder|expense|watcher|bankstatement|shopping|bank|dictionary|glucosecheck/i.test(curApp)">
      <!-- <q-footer class="bg-teal-9" v-if="/memo|reminder|exlist|watcher|bankstatement|bank|dictionary|shopping|glucosecheck|todox/i.test(curApp)"> -->
        <q-toolbar>
          <Pagination :pNumPages="compNumPages" :pItemsPerPage="itemsPerPage" />
        </q-toolbar>
      </q-footer>
  </q-layout>
  <Holidays />
</div>
</template>
<script setup>
import { ref, computed } from 'vue'
import emitter from 'tiny-emitter/instance'
import Pagination from '../../src/components/Pagination'
import AppItem from '../../src/components/AppItem'
import Holidays from '../../holiday/HolidayDialog'
import { libFunctions } from 'src/composables/libFunctions'
import { dayFunctions } from 'src/composables/dayFunctions'
import { infoFunctions } from 'src/composables/infoFunctions'
import { useRouter } from 'vue-router'
const router = useRouter()

const { isIM, isDesk, isFone, dalist, $q } = libFunctions()
const { getDay3 } = dayFunctions()
const { getA1cDefinitions } = infoFunctions()

//== data
const tvfab = ref(false)
const wunit = ref(null)
const wlgame = ref(null)
const itemsPerPage = ref(11)
// var numPages = 1
const numItems = ref(12)
const curApp = ref(null)
var disableBtn = {
  expense: false,
  shopping: false,
  reminder: false,
  memo: false,
  watcher: false,
  bankstatement: false,
  holdings: false,
  bank: false,
  holidays: false,
  golf: false,
  arts: false,
  calendar: false,
  todo: false
}
var appTitle = '家庭应用'
var drawer = true
var miniState = false
const oneHour = 1000 * 60 * 60
// const fivHour = 5000 * 60 * 60
var isShowVersion = false
var flipVal = $q.version
var audCookies = ref(false)
var searchQuery = ref('')

//== emitter-on
emitter.on('cur-tit', (ctit) => { setTitle(ctit); setInterval(setTitle, oneHour, ctit) })
emitter.on('cur-app', (capp, from) => curApp.value = capp)
emitter.on('num-items', (x) => numItems.value = x)
// emitter.on('cur-app', (capp, from) => { curApp.value = capp; console.log(`emitter.on curApp=${curApp.value} from=${from}`) })
// emitter.on('num-items', (x) => { numItems.value = x; console.log(`emitter.on numItems.value=${x} curApp=${curApp.value}`) })
emitter.on('items-per-page', (x) => { itemsPerPage.value = x })
emitter.on('win-lost', (x) => { flipShow(x) })
emitter.on('weight-unit', (x) => { wunit.value = x })

//== main
console.log('-ST-MainLayout')
// console.timeStamp('-ST-MainLayout')
wunit.value = $q.localStorage.getItem('weightUnit')
if (isIM) {
  drawer = false
  miniState = true
}
// console.log('-dg-document.url:', window.location.pathname)
if (/arts/.test(window.location.href)) curApp.value = 'arts'
else if (/golf/.test(window.location.href)) curApp.value = 'golf'

const allCookies = $q.cookies.getAll()
const cookieKeys = Object.keys(allCookies)
audCookies.value = cookieKeys.find(key => /add_|upd_|del_/.test(key)) !== undefined

//== computed
const compVer = computed(() => { return process.env.VER })
const compNumPages = computed(() => { return Math.ceil(numItems.value / itemsPerPage.value) })
// const compVer = computed(() => { return process.env.VER })
const weightUnit = computed(() => {
  return wunit.value=='pond' ? '磅' : wunit.value=='kilo' ? '公' : wunit.value=='jing' ? '斤' : '磅'
})

//== functions
function getTvShows (hours) {
  console.log(`-fn-getTvShows hours=${hours}`)
  emitter.emit('tv-shows-in-hours', hours)
}
// function showConversionChart () {
//   console.log('-fn-showConversionChart')
//   window.location.href = "docs/A1c_conversion_to_mg_dl_mmol_MyMedicalScore.html"
// }
function checkGiftCardBalance () {
  // console.log('-fn-checkGiftCardBalance')
  emitter.emit('check-gc-balance')
}
function toggleAUD () {
  emitter.emit('toggle-aud')
}
function flipShow (x) {
  let tm = (new Date()).toString().split(' ')[4]
  if (isShowVersion) flipVal = 'V' +  $q.version
  else flipVal = getWLval(x)
  // console.log(`-ck-WL %c${tm} ${this.flipVal}`, 'color:indianRed;font-size:22px')
  isShowVersion = !isShowVersion
}
function getWLval (x) {
  let ret = null
  if (x > 0) ret = '净赢 ' + parseFloat(x/20).toFixed(2) + ' 场'
  else if (x < 0) ret = '静输 ' + parseFloat(-x/20).toFixed(2) + ' 场'
  else ret = '平'
  // console.log(`-ck- setWLval=%c${ret} ${x}`, 'color:red;font-size:24px')
  // this.wlgame = ret
  return ret
}
function setTitle (tit) {
  let tm = (new Date()).toString().split(' ')[4]
  // console.log(`-ck-%c${tm} setTitle`, 'color:indianRed;font-size:13px')
  const ymd = (new Date()).yyyymmdd()
  // this.appTitle = tit.split(' ')[0] + ' ' + ymd + ' (' + (this.isDesk ? this.getDay2(ymd) : this.getDay3(ymd)) + ')'
  appTitle = tit
  if (isDesk) appTitle += ' ' + ymd + ' (' + ymd.chwk3() + ')'
  document.title = tit
}
function XXopenApp (app, appTitle) {
  console.log(`-fn-openApp, appTitle, app=${app},appTitle=${appTitle},drawer=${drawer},numItems=${numItems.value},itemsPerPage=${itemsPerPage.value}`)
  curApp.value = app
  for (const key of Object.keys(disableBtn)) disableBtn[key] = false
  disableBtn[app] = true
  // if (['../golf', '../arts', 'glucosecheck','exlist', 'relist'].includes(app)) { // no need to reload for chart
  if (['../golf', '../arts'].includes(app)) { // no need to reload for chart
    window.location.href = app // this navigates to app and also trigger to loading. otherwise <canvas> not working
  }
  router.replace({ path: app })  // this is just navigating no loading
}
function showWatcherChart () {
  emitter.emit('show-watcher-chart')
}
function selectWeightUnit () {
  const storedUnit = $q.localStorage.getItem('weightUnit')
  wunit.value = storedUnit == 'null' || storedUnit == 'undefined' ? 'pond' : storedUnit
  console.log(`-fn-selectWeightUnit() localStorage.weightUnit=${wunit.value}`)
  if (wunit.value == null) {
    wunit.value = 'pond'
  } else if (wunit.value == 'pond') {
    wunit.value = 'kilo'
  } else if (wunit.value == 'kilo') {
    wunit.value = 'jing'
  } else if (wunit.value == 'jing') {
    wunit.value = 'pond'
  }
  console.log(`-CK- wunit=${wunit.value} send to Watcher app`)
  $q.localStorage.set('weightUnit', wunit.value)
  emitter.emit('selected-weight-unit')
}
function showHealthTestChart () {
  emitter.emit('show-healthtest-chart')
  // console.log(`-fn-showHealthTestChart from MainLayout curApp=${curApp.value}`)
}
function showYearChart () {
  console.log(`-fn-showYearChart from MainLayout curApp=${curApp.value}`)
  if (curApp.value === 'glucosecheck') emitter.emit('show-a1x-chart')
  else if (curApp.value === 'bankstatement') emitter.emit('show-pchart')
  else emitter.emit('show-year-chart', searchQuery.value)
}
function showClvlChart () {
  console.log('showClvlChart')
  // if (curApp.value == 'glucosecheck') emitter.emit('show-clv-chart')
  emitter.emit('show-clv-chart')
}
function showHolidays () {
  // console.log('showHolidays for layout in exp')
  emitter.emit('open-Holidays')
}
function search () {
  // console.log('search', searchQuery.value)
  emitter.emit('search', searchQuery.value)
}
function drawerClick () {
  miniState = !miniState
  drawer = drawer
}
</script>
<style>
::-webkit-scrollbar { display: none; }
html,div#absolute-full { /* this for firefox to hide the scrollbar */
  overflow: auto;
  /* the line that rules them all */
  scrollbar-width: 0px;
}
</style>

<!-- <style>>
a:hover {
  background-color: RGBA(100, 200, 300, 0.3);
  color: red;
}
a:visited {
  color: darkblue;
}
a:link {
  background-color: RGBA(100, 200, 300, 0.3);
  color: purple;
}
.row-border {
  border: 1px solid teal;
  margin: 0 auto;
  cursor: pointer;
}
.wid-border {
  width: 774px;
  border: 1px solid cyan;
  margin: 0 auto;
  margin-top: 5px;
}
div.q-item__section.column.q-item__section--nowrap.q-item__section--main.justify-center {
  font-size: 16px;
  font-family: stzhongsong;
  font-weight: 500;
}
.flayout {
  background:rgb(18,58,58)
}
.dlayout {
  background:rgb(28,68,78);
  /* width: 850px; */
}
.hdiv:hover {
  background-color: RGBA(100, 200, 300, 0.3);
  color:yellow;
}

::-webkit-scrollbar { display: none; }
html,div#absolute-full { /* this for firefox to hide the scrollbar */
  overflow: auto;
  /* the line that rules them all */
  scrollbar-width: 0px;
}
.cfont {
  font-family:youyuan;
  font-size:20px;
  font-weight:700;
  margin-left:-9px;
  color:white;
}
</style> -->

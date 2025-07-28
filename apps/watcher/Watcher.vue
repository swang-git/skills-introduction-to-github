<template>
<!-- <div class="q-pa-xs" :class="{ fixed: clickedIdx < 1}"> -->
<div style="margin:-23px 0 0 0;display:grid;place-items:center;height:99vh">">
  <div v-for="(e, i) in palist" :key=e.id>
    <div :style="getLineBackground(i)" :class="{ 'bg-purple-10':!e.hideIt }" class="q-px-xs">
      <div class="row cursor-pointer;q-qx-sm" style="font-size:20px">
        <div v-if="isDesk" class="q-pl-sm" @click="e.hideIt=true"><a :href="getDocLinkStr(e.date)" target="_blank" class="alnkclass">{{ e.date }}</a></div>
        <div v-if="isDesk" class="q-pl-md" @click="e.hideIt=true;getPositions(e)">({{ e.date.chwk2() }})</div>
        <div v-if="e.dowjones>0 && isDesk" class="q-px-md text-right" @click="showIt(i)"> {{ formatCurrency(e.dowjones) }}
          <q-tooltip class="text-h6 bg-accent">Dow Jones on {{ e.date }}</q-tooltip>
        </div>
        <div v-else class="q-pl-md text-right" @click="showIt(i)">{{ formatCurrency(e.portfolio - invested(e)) }}</div>
        <div class="q-pl-xs text-right"  @click="showIt(i)">{{ ((e.portfolio/invested(e) - 1) * 100).toFixed(2) }}%</div>
        <div class="q-pl-md text-center" @click="showIt(i)">{{ getWeight(e) }} / {{ getBMI(e) }}</div>
        <div class="q-px-xs text-right" :class="{ 'text-green-3':e.dif>0, 'text-pink-2':e.dif<0 }" style="width:113px" @click="showIt(i)"> {{ e.difs }} </div>
        <div class="q-plx-xs text-right cursor-pointer" style="width:133px" @click="showDar(e, 'add')">{{ formatCurrency(e.portfolio) }}</div>
        <div class="q-pl-md text-right"><q-icon :name="getIcon(i)" @click="showDar(e, 'add')" /></div>
      </div>
    </div>
    <div :class="{ hidden: e.hideIt }" class="row q-pa-sm" style="color:yellow;font-size:18px">
      <div class="q-pl-xs" :class="{ 'col-9':portfNote.length>0, 'col-10':portfNote.length<=0 }" style="font-size:18px;line-height:1.1">
        <q-tr><td class="text-no-wrap text-right">公斤:</td><td class="q-pl-xs">{{ (e.kilo).toFixed(2) }}</td></q-tr>
        <q-tr><td class="text-no-wrap text-right">市斤:</td><td class="q-pl-xs">{{ (e.kilo * 2).toFixed(2)  }}</td></q-tr>
        <q-tr><td class="text-no-wrap text-right">英磅:</td><td class="q-pl-xs">{{ formatCurrency(getPondx(e)) }}</td></q-tr>
        <q-tr v-if="e.dif !== 0"><td class="text-no-wrap text-right">日增:</td><td class="q-pl-xs" v-html="getColoredDiff(i)"></td></q-tr>
        <q-tr><td class="text-no-wrap text-right">金额:</td><td class="q-pl-xs">{{ formatCurrency(e.portfolio) }}</td></q-tr>
        <q-tr><td class="text-no-wrap text-right">投入:</td><td class="q-pl-xs">{{ formatCurrency(invested(e)) }}</td></q-tr>
        <q-tr><td class="text-no-wrap text-right">净赚:</td><td class="q-pl-xs">{{ formatCurrency(e.portfolio - invested(e)) }}</td></q-tr>
        <q-tr><td class="text-no-wrap text-right">增长:</td><td class="q-pl-xs">{{ formatCurrency(getPct(e)) }}% </td></q-tr>
        <q-tr><td class="text-no-wrap text-right">DOW JONES: </td><td class="q-pl-xs">{{ formatCurrency(e.dowjones) }} </td></q-tr>
        <q-tr><td class="text-no-wrap text-right">NASDAQ: </td><td class="q-pl-xs">{{ formatCurrency(e.nasdaq) }} </td></q-tr>
        <q-tr><td class="text-no-wrap text-right">S&P 500: </td><td class="q-pl-xs">{{ formatCurrency(e.sp500) }} </td></q-tr>
        <q-tr><td class="text-no-wrap text-right">FTSE 100: </td><td class="q-pl-xs">{{ formatCurrency(e.ftse100) }} </td></q-tr>
        <q-tr><td class="text-no-wrap text-right">NIKKEI 225: </td><td class="q-pl-xs">{{ formatCurrency(e.nikkei) }} </td></q-tr>
        <q-tr v-if="e.note!=null"><td class="text-cyan-2 text-h6 cursor-pointer" colspan="2" @click="showPNote(e)" v-html="e.note"></td></q-tr>
        <q-tr><td class="text-no-wrap text-right">Link: </td><td class="q-pl-xs" v-html="getDocLink(e.date)" /></q-tr>
        <q-tr v-if="e.date>=startedDate"><td class="text-no-wrap text-grey-5 text-right">注释: </td><td class="q-pl-xs text-grey-6">{{ startedNote }}</td></q-tr>
      </div>
      <div class="col-1">
         <q-fab v-model="fabOpen" flat icon="keyboard_arrow_down" direction="down">
          <q-btn round glossy icon="note" @click="showPNote(e)">
            <q-tooltip class="text-h6 bg-green-9">Daily Note - Optional(e.g. buy/sell/convert/pending)</q-tooltip>
          </q-btn>
          <q-btn round glossy color="red-10"    @click="showDar(e, 'del')" size="16px" icon="delete" />
          <q-btn round glossy color="indigo-10" @click="showDar(e, 'upd')" size="16px" icon="update" />
          <q-btn round glossy color="green-10"  @click="showDar(e, 'add')" size="16px" icon="add_circle" />
        </q-fab>
      </div>
      <!-- <div class="col-1">
        <q-btn round glossy icon="note" @click="showPNote(e)">
          <q-tooltip class="text-h6 bg-green-9">Daily Note - Optional(e.g. buy/sell/convert/pending)</q-tooltip>
        </q-btn>
      </div> -->
      <div v-if="portfData.length>0" class="col-1">
        <q-btn round glossy icon="assignment" @click="showPortf(ee)" />
      </div>
      <div :class="{ 'col-1':portfData.length>0, 'col-2':portfData.length<=0 }">
        <q-btn size="md" round glossy icon="edit" @click="showDar(e, 'add')" />
      </div>
    </div>
  </div>
  <UserInput />
  <PortfolioDisplay />
  <PortfolioNote  />
  <PortfolioPositions />
  <ChartsProxy :chdata="dalist" :chname="chname" />
  <InfoDisplay ref="refInfoDisplay" />
</div>
</template>
<script setup>
import emitter from 'tiny-emitter/instance'
import { ref, onMounted } from 'vue'

import { libFunctions } from 'src/composables/libFunctions'
import { dayFunctions } from 'src/composables/dayFunctions'
import { axiosFunctions } from 'src/composables/axiosFunctions'

import PortfolioPositions from './PortfolioPositions'
import UserInput from './UserInput'
import PortfolioNote from './PortfolioNote'
import PortfolioDisplay from './PortfolioDisplay'
import ChartsProxy from '../src/components/ChartsProxy'
import InfoDisplay from '../src/components/InfoDisplay'

const fabOpen = true
const { dalist, palist, buildApp, getLineBackground, formatCurrency, isDesk, $q } = libFunctions()
const { gaxios } = axiosFunctions()
const { getDay2 } = dayFunctions()

const annuityDate = '2020-07-10'
const startedDate = '2018-01-01'
const startedNote = 'on 2018-01-01 started Investment Recording (GL,buy/sell etc.)'
const annuityNote = 'on 2020-07-10 invested $100k to North American Company for a 7-year fixed income annuity with 3.5% annual rate'
const separator = 'horizontal'
const opened = ref(false)
const clickedIdx = ref(0)
const clickedRow = ref(0)
const type = ref('pond')
const dats = ref([])
const date = ref(null)
const accounts = ref([])
const accntOpts = ref([])
const stocks = ref([])
const actions = ref(false)
const portfData = ref([])
const portfNote = ref([])
const searchQuery = ref('')
const title = ref(null)
const refInfoDisplay = ref(null)
const chname = ref('Percentage Gain or Loss')
const columns = ref([
  { required: false, align: 'right', label: '年月日', name: 'date', field: 'date', sortable: true },
  { required: false, align: 'right', label: '星 期', name: 'day', field: 'day', sortable: true },
  { required: false, align: 'right', label: '总 增 长', name: 'sdif', field: 'sdif', sortable: true },
  { required: false, align: 'right', label: '增长率', name: 'pdif', field: 'pdif', sortable: true },
  { required: false, align: 'right', label: '体 重', name: 'kilo', field: 'kilo', sortable: true },
  { required: false, align: 'right', label: '日 增 长', name: 'ddif', field: 'ddif', sortable: true, sort: (a, b) => parseFloat(a, 10) - parseFloat(b, 10) },
  { required: false, align: 'right', label: '日 总 额', name: 'portfolio', field: 'portfolio', sortable: true, sort: (a, b) => parseFloat(a, 10) - parseFloat(b, 10) }
])
const pagination = ref({
  page: 1,
  rowsPerPage: 0
})
const itemsPerPage = 28
console.log('-ST-Watcher4Desk')
emitter.on('show-watcher-chart', () => { showChart() })
emitter.on('watcher-add', () => getList())
emitter.on('watcher-upd', () => getList())
emitter.on('watcher-del', () => getList())
emitter.on('selected-weight-unit', () => setWeight())
buildApp(isDesk ? '每日体重/退休金' : '体重/退休', 'Watcher')
getList()
emitter.emit('items-per-page', itemsPerPage)
// onMounted(() => refInfoDisplay.value)
onMounted(() => { console.log(`-MT-refInfoDisplay=${refInfoDisplay.value}`) })

function compTotalValue(date, toalval) {
  console.log(`compTotalValue date=${date} total=${toalval}`)
}
function showChart () {
  console.log('-fn-showChart')
  emitter.emit('open-ChartsProxy')
}
function addPropToChartData (dats) {
  dats.forEach((p, i) => {
    p.hideIt = true
    // p.wday = getDay2(p.date)
    // p.wday = p.date.chwk3()
    if  (dats[i + 1] === undefined) {
      p.dif = 0
    } else {
      p.dif = p.date === annuityDate ? p.portfolio - (dats[i + 1].portfolio - 100000) : p.portfolio - dats[i + 1].portfolio
      p.difs = formatCurrency(p.dif.toFixed(2).replace('-', ''))
    }
  })
  return dats
}
emitter.on('watcher-getList', (x) => setList(x))
function setList (da) {
  dats.value = addPropToChartData(da.dats)
  // console.log(`-fn-setList 1st note=${dats.value[0].note}`)
  // console.log(`-CK-setList actions`, da.actions)
  stocks.value = da.stocks
  actions.value = da.actions
  accounts.value = da.accnts
  accounts.value.forEach(p => {
    accntOpts.value.push( { value:p.id, label:p.accnt_num, aname:p.accnt_nam })
  })
  emitter.emit('dats', dats.value)
  // console.info('-CK-setList da', dats.value[0].date, palist.value)
  // console.info('-CK-setList accntOpts:', accntOpts.value)
}
function getList () {
  const path = process.env.API + '/watcher/getList'
  gaxios(path)
}
function getDocLinkStr (date) {
  let fname = '/docs/Portfolio/snapshot_' + date.replace('-', '').replace('-', '').trim()
  if (date < '2021-01-13') fname += '.html'
  else fname += '.pdf'
  const lnkstr = fname
  return lnkstr
}
function getDocLink (date) {
  let fname = '/docs/Portfolio/snapshot_' + date.replace('-', '').replace('-', '').trim()
  if (date < '2021-01-13') fname += '.html'
  else fname += '.pdf'
  const lnk = '<a href="' + fname + '" + target="_blank">' + date + ' Snapshot for the day</a>'
  return lnk
}
function invested (e) { return e.date >= annuityDate ? 1000000.00 : 1100000.00 }
function setWeight () {
  const stype = $q.localStorage.getItem('weightUnit')
  type.value = stype == 'null' || stype == 'undefined' ? 'pond' : stype == 'kilo' ? 'kilo' : stype == 'jing' ? 'jing' : stype == 'pond' ? 'pond' : 'pond'
  dalist.value.forEach(p => {
    p.kilo = parseFloat(p.kilo)
    const kilo = parseFloat(p.kilo)
    let wt = kilo.toFixed(2)
    if (type.value === 'pond') wt = (kilo * 2.2046244202).toFixed(1)
    else if (type.value === 'jing') wt = (kilo * 2.0).toFixed(1)
    p.weight = wt
  })
}
function getWeight (e) {
  const stype = $q.localStorage.getItem('weightUnit')
  if (stype != null) type.value = stype
  e.kilo = parseFloat(e.kilo)
  const kilo = parseFloat(e.kilo)
  let wt = kilo.toFixed(1)
  if (type.value === 'pond') wt = (kilo * 2.2046244202).toFixed(1)
  else if (type.value === 'jing') wt = (kilo * 2.0).toFixed(1)
  e.weight = wt
  return wt
}
function getBMI (e) {
  if (e.kilo == null) return null
  return (e.kilo / 1.73 / 1.73).toFixed(1)
}
function getColoredDiff (i) {
  const chg = palist.value[i].dif
  const val = formatCurrency(chg).replace('-', '')
  if (chg < 0) return '<div style="width:10px" class="q-pl-xs text-red text-right">' + val + '</div>'
  if (chg === 0) return ''
  return '<div style="width:10px" class="q-pl-xs text-green-4 text-right">' + val + '</div>'
}
function getIcon (i) {
  return palist.value[i].hideIt ? 'blur_circular' : 'blur_linear'
}
function getPondx (e) { return (e.kilo * 2.2046244202).toFixed(1) }
function getPct (e) { return (e.portfolio / invested(e) - 1) * 100 }
function showDar (row, act) {
  console.log(`-fn-showDar act=${act}`, type.value, row.weight, row.kilo, row)
  clickedRow.value = row
  const clone = JSON.parse(JSON.stringify(row))
  emitter.emit('open-UserInput4Desk', clone, act)
}
function showIt (i) {
  clickedIdx.value = i
  portfData.value = []
  palist.value.forEach((p, idx) => { if (idx !== i) p.hideIt = true })
  palist.value[i].hideIt = !palist.value[i].hideIt
  // to force it toggle showing details
  const dat = palist.value[i].date
  if (dat.length === 10) palist.value[i].date += ' '
  else palist.value[i].date = dat.replace(/ /g, '')
  const date = palist.value[i].date.trim()
  const rowOpened = !palist.value[i].hideIt
  // console.info('B hideIt', i, this.portfData.length, rowOpened, date, this.palist[i].hideIt, '[' + this.palist[i].date + ']', this.palist[i].dif)
  if (rowOpened) {
    const path = process.env.API + '/watcher/getPortfolio/' + date
    gaxios(path)
  }
}
emitter.on('watcher-getPortfolio', (x) => setPortfolio(x))
function setPortfolio (da) {
  console.log('-fn-setPortfolio', da.portf)
  portfNote.value = da.pnote
  portfData.value = da.portf
}
emitter.on('watcher-getPositions', (x) => setPositions(x))
function getPositions (row) {
  const path = process.env.API + '/watcher/getPositions/' + row.date
  clickedRow.value = row
  gaxios(path)
}
function showPNote (e) {
  console.log('-fn-showNote')
  emitter.emit('open-PortfolioNote', e.date, e.user_id, portfNote.value, accounts.value, accntOpts.value, stocks.value, actions.value)
}
function showPortf (e) {
  console.log('-fn-showPortf')
  emitter.emit('open-PortfolioDisplay', portfData.value)
}
function setPositions (da) {
  console.log(`-fn-showPositions status=${da.status} portfolio=${clickedRow.value.portfolio}`, da)
   if (da.preDate == '2024-05-23') { // 2024-05-24 transfer Z71 account from Fidelity to JP Morgen/Chase
    emitter.emit('open-PortfolioPositions', da, clickedRow.value.portfolio)
   } else if (da.status == 'misMatch') {
    let tit = `Num of Security Mismatched`
    let msg = `Num of Securities=${da.currSecurities}, pre Num of Securities=${da.preNumSecurities} for preDate=${da.preDate}`
    console.log(`tit=${tit}, msg=${msg}`)
    // refInfoDisplay.value.openIt(tit, msg)
    emitter.emit('open-PortfolioPositions', da, clickedRow.value.portfolio)
  } else if (da.status == "OK") {
    emitter.emit('open-PortfolioPositions', da, clickedRow.value.portfolio)
  } else {
    $q.dialog({title:'NO DATA FILE FOUND', message:da.status.substring(20)})
  }
}
</script>
<style>
a.alnkclass {
  text-decoration: none;
  color: lightcyan;
  background-color: darkslategray;
}
.alnkclass a:visited {
  color: lightcyan;
}
.alnkclass a:link {
  text-decoration: none;
  color: cyan;
}
</style>

<template>
<div style="display:grid;place-items:center">
  <div style="margin:-3px 0 0 5px;width:802px;border:cyan solid 1px">
    <div class="q-pa-xs row text-h6 text-grey-1 bg-teal-10" style="width:800px">
      <div class="q-pl-xs text-no-wrap text-left" style="width:86px">{{ month }}月份</div>
      <div class="q-pl-xs text-no-wrap" style="width:175px">股票: {{ fmtcy(stocksVal) }}</div>
      <div class="q-pl-xs text-no-wrap" style="width:175px">现金: {{ compCash }}</div>
      <div class="q-pl-xs text-no-wrap" style="width:50px">月增: </div>
      <div class="q-pl-xs text-no-wrap" style="width:90px" :class="getDiffClass()">{{ fmtcy(val=compDiff>=0 ? compDiff : -compDiff) }}</div>
      <div class="q-pl-xs text-no-wrap text-right" style="width:200px">总值: {{ fmtcy(compTotalVal) }}</div>
    </div>
    <div v-for="(e, i) in palist" :key=e.id class="cursor-pointer" style="margin-top:1.5px">
      <div @click="showDetails(e, i)" :style="getLineBackgroundByMonth(parseInt(e.month))">
        <div class="text-h6 row">
          <div v-if="isDesk" style="width:90px" class="q-pl-sm">{{ e.year }}年</div>
          <div :style="{'width':(isDesk ? '55px':'45px')}" class="text-center q-pr-sm">{{ e.month }}<span v-if="isDesk">月</span></div>
          <div class="q-pl-sm" style="width:80px" :class="getBankColorClass(e)">
            <span v-if="e.bank=='Chase'"> {{ e.bank.toUpperCase() }} </span>
            <span v-else-if="e.bank=='BOA'"> B &nbsp;O &nbsp;&nbsp;A </span>
            <span v-else-if="e.bank=='NAC'"> N &nbsp;A &nbsp;&nbsp;C </span>
            <span v-else-if="e.bank=='BKG'"> B &nbsp;K &nbsp;&nbsp;G </span>
            <span v-else>{{ e.bank }}</span>
          </div>
          <div v-if="isDesk" class="q-pl-sm text-white" style="font-size:17px">{{ e.begin_date }}</div>
          <div v-if="isDesk" style="width:20px" class="q-pl-md q-pt-xs text-grey text-right text-body1">{{ e.tran_cnt }}</div>
          <div v-if="isDesk" style="width:144px" class="text-right">{{ fmtcy(e.begin_balance) }}</div>
          <div style="width:144px" class="text-right">{{ fmtcy(e.end_balance) }}</div>
          <div style="width:118px" class="text-right" :class="getEdiffClass(e)">{{ fmtcy(e.diff>0 ? e.diff : -e.diff) }}</div>
          <div v-if="isDesk" style="width:10px"><q-btn size="12px" :icon="getIcon(i)" flat /></div>
        </div>
      </div>
      <div :class="{ hidden: e.hideIt }" class="q-px-xs text-cyan-1">
        <div class="bg-teal-10 q-px-none">
          <table class="text-h6 q-pl-xs" style="width:783px;border:1px solid cyan">
            <q-tr>
              <td class="text-no-wrap">月报周期</td>
              <td class="text-no-wrap">{{ e.begin_date }}</td>
              <td class="q-px-xl text-no-wrap">{{ e.end_date }}</td>
              <td class="text-no-wrap" v-html="getStatementLink(e)"></td>
            </q-tr>
            <q-tr>
              <td>主要账户</td>
              <td v-if="e.bank=='NAC'" class="text-cyan-2" colspan="4"> {{ e.primary_account }} - (Annuity in North American Company)</td>
              <td v-else-if="e.bank=='BKG'" class="text-cyan-2" colspan="4"> {{ e.primary_account }} - (Stocks)</td>
              <td v-else class="text-cyan-2" colspan="4"> {{ e.primary_account }} - 支付账户 (CHECKING)</td>
            </q-tr>
            <q-tr><td>银行总额</td><td class="text-cyan-2" colspan="4"> $ {{ fmtcy(e.end_balance) }}</td></q-tr>
            <q-tr><td class="text-no-wrap">银行增减</td>
              <!-- <td class="text-bold" colspan="4" :class="{ 'text-green': e.diff>0,'text-red':e.diff<0 }">${{ fmtcy(e.diff>0 ? e.diff:-e.diff) }}</td> -->
              <td class="text-bold" :colspan="e.bank=='NAC' ? 3 : 4" :class="{ 'text-green': e.diff>0,'text-red':e.diff<0 }">${{ fmtcy(e.diff>0 ? e.diff:-e.diff) }}</td>
              <td v-if="e.bank=='NAC'">Maturity Date: 2027-07-20</td>
            </q-tr>
            <!-- <q-tr v-if="e.bank!=='Fidelity' && chkInfo!=null"><span v-html="chkInfo" /></q-tr> -->
            <tr v-if="e.bank!=='Fidelity' && e.bank!=='NAC' && e.bank!=='BKG' && chkInfo!=null" v-html="chkInfo"></tr>
            <!-- <q-tr v-if="e.bank!=='Fidelity' && e.bank!=='NAC' && e.bank!=='BKG'"><span v-html="savInfo" /></q-tr> -->
            <tr v-if="e.bank!=='Fidelity' && e.bank!=='NAC' && e.bank!=='BKG'" v-html="savInfo"></tr>
            <!-- <q-tr v-if="e.bank==='BOA'"><span v-html="chkInfo2" /></q-tr> -->
          </table>
          <div v-if="e.bank==='Fidelity'">
            <table v-for="a in accounts" :key=a.x style="width:783px; border:1px solid cyan; background:#234" class="q-pa-xs q-my-xs">
                <q-tr><th colspan="7" class="text-h5 text-cyan-2 text-center">Holdings: {{ getAccountLabel(a) }}
                  <span class="q-pl-xs text-yellow text-h6">{{ getAccountEndBalance(a) }}</span> </th>
                </q-tr>
                <q-tr><td colspan="7"><hr></td></q-tr>
                <q-tr v-for="p in getAccountHoldings(a)" :key=p.id class="text-white" style="font-size:18px">
                  <td style="width:80px" class="q-pl-xs text-left">{{ p.symbol }}</td>
                  <td style="width:110px" class="text-right">{{ p.quantity }}</td>
                  <td style="width:120px" class="text-right">{{ p.start_balance }}</td>
                  <td style="width:120px" class="text-right">{{ fmtcy(p.price) }}</td>
                  <td style="width:130px" class="text-right">{{ p.end_balance }}</td>
                  <td style="width:100px" v-if="p.diff>=0" class="text-green-3 text-right">{{ fmtcy(p.diff) }}</td>
                  <td style="width:90px" v-else class="text-red text-right">{{ fmtcy(-p.diff) }}</td>
                  <td style="width:100px" class="q-pr-xs text-right">{{ p.cost }}</td>
                </q-tr>
                <q-tr v-if="getAccountHoldings(a).length>1">
                  <td colspan="7"><hr></td>
                </q-tr>
                <q-tr v-if="getAccountHoldings(a).length>1" class="text-cyan-2" style="font-size:20px">
                  <td style="width:80px"  class="q-pl-xs text-white text-center">TOTAL</td>
                  <td style="width:110px" class="text-right text-white">start_balance</td>
                  <td style="width:120px" class="text-right">{{ getAccountStartBalance(a) }}</td>
                  <td style="width:120px" class="text-right text-white">end_balance</td>
                  <td style="width:130px" class="text-right text-yellow">{{ getAccountEndBalance(a) }}</td>
                  <td style="width:100px" v-if="getADiff(a)>=0" class="text-green-3 text-right">{{ fmtcy(getADiff(a)) }}</td>
                  <td style="width:90px"  v-else class="text-red text-right">{{ fmtcy(-getADiff(a)) }}</td>
                  <td style="width:100px" class="q-pr-xs text-right">cost</td>
                </q-tr>
            </table>
          </div>
          <div v-else-if="e.bank!='NAC' && e.bank!='BKG'">
            <table style="width:783px; border:1px solid cyan; background:#234">
              <q-tr v-if="e.bank==='Fidelity'"><td colspan="6" class="text-h5 text-center q-pt-sm text-cyan-2">Account Holdings ({{ fhold1 }})</td></q-tr>
              <q-tr v-else><td colspan="6" class="text-h5 text-center q-pt-sm text-cyan-2">Checking Account Activities ({{ cANum }})</td></q-tr>
              <q-tr><td colspan="6" class="q-pr-0"><hr /></td></q-tr>
              <q-tr class="text-white text-body1" v-for="s in chkactvs" :key="s.id"><td></td>
                <td nowrap>{{ s.tran_date }}</td>
                <td class="ellipsis" v-if="s.description!==null">{{ s.description.substring(0, 40) }}</td>
                <td v-else class="ellipsis">{{ s.description }}</td>
                <td class="q-px-sm text-right">{{ s.begin_balance }}</td>
                <td class="q-px-sm text-right">{{ s.amount }}</td>
                <td class="q-px-sm text-right text-bold">{{ s.end_balance }}</td>
              </q-tr>
            </table>
            <table v-if="e.bank!='NAC'" class="q-my-xs" style="width:783px; border:1px solid cyan; background:#233">
              <q-tr v-if="e.bank==='Fidelity'"><td colspan="6" class="text-h5 text-center q-pt-sm text-cyan-4">Account Holdings ({{ fhold2 }})</td></q-tr>
              <q-tr v-else><td colspan="6" class="text-h5 text-center q-pt-sm text-cyan-4">Savings &nbsp; Account &nbsp;Activities ({{ sANum }})</td></q-tr>
              <q-tr><td colspan="6" class="q-pr-0"><hr /></td></q-tr>
              <q-tr class="text-white text-body1" v-for="s in savactvs" :key="s.id"><td></td>
                <td nowrap>{{ s.tran_date }}</td>
                <td class="ellipsis30x" v-if="s.description!==null">{{ s.description.substring(0, 45) }}</td>
                <td v-else>{{ s.description }}</td>
                <td class="q-px-sm text-right">{{ s.begin_balance }}</td>
                <td class="q-px-sm text-right">{{ s.amount }}</td>
                <td class="q-px-sm text-right text-bold">{{ s.end_balance }}</td>
              </q-tr>
            </table>
            <table v-if="e.bank!='NAC'" class="q-my-xs" style="width:783px; border:1px solid cyan; background:#364">
              <q-tr><td colspan="5" class="text-h5 text-center q-pl-xl text-cyan-2">Bank Statement Notes</td></q-tr>
              <q-tr><td colspan="6" class="q-pr-0"><hr /></td></q-tr>
              <q-tr class="text-white text-body1" v-for="s in notes" :key="s.id"><td></td>
                <td colspan="1" class="q-px-0">{{ s.note_id }}</td>
                <td colspan="3" class="q-px-0">{{ s.notes }}</td>
                <td v-if="s.notes.indexOf('Percentage')>=0" colspan="1" class="q-px-sm text-right">{{ s.amount }}%</td>
                <td v-else colspan="1" class="q-px-sm text-right">{{ s.amount }}</td>
              </q-tr>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<ChartsProxy v-show="openCharts" @close-charts="openCharts=false" chname="Portfolio Charts" :chdata="dats" />
</template>
<script setup>
import { ref, computed } from 'vue'
import emitter from 'tiny-emitter/instance'
import { libFunctions } from '../src/composables/libFunctions'
import { axiosFunctions } from '../src/composables/axiosFunctions'
import { cssFunctions } from '../src/composables/cssFunctions'
import ChartsProxy from '../src/components/ChartsProxy'
// import ChartPortfolio from './ChartPortfolio'
// import ChartBOA from './ChartBOA'
// import ChartChase from './ChartChase'

const { gaxios } = axiosFunctions()
const { getLineBackgroundByMonth } = cssFunctions()
const { buildApp, fmtcy, palist, isDesk } = libFunctions()
//== data sections
// const openPChart = ref(false)
// const openBChart = ref(false)
// const openCChart = ref(false)
const openCharts = ref(false)
const totalValue = ref(1500000)
const stocksVal = ref(0)
const notes = ref([])
const chkactvs = ref([])
const savactvs = ref([])
const holdings = ref([])
const accounts = ref([])
const dats = ref([])
const chartda = ref([])
const clickedRow = ref({ hideIt: true })
const clickedIdx = ref(0)
const chaseBal = ref(0)
const boaBal = ref(0)
const month = ref(0)
const monthDiff = ref(0)
const monthTotal = ref(0)
const intraday = ref(null)
const last_bkg_pdf = ref(null)
const chaseCash = ref(0)
const fidelCash = ref(0)

buildApp('银行月报', 'Bankstatement')
emitter.on('show-charts', () => showCharts())
// emitter.on('show-bchart', () => showBChart())
// emitter.on('close-charts', () => openCharts.value = false)
emitter.emit('items-per-page', 27)

console.log('-ST-Bankstatement')
getList()

//== computed sections
const compCash = computed(() => {
  // const val = holdings.value.filter(p => /INDIVIDUAL/.test(p.account_name)).reduce((a, b) => a + b.end_balance, chaseCash.value)
  const val = fidelCash.value + chaseCash.value
  return fmtcy(val)
})

const compTotalVal = computed(() => {
  let val = 0
  dats.value.forEach((p, i) => { if (i<5) val += parseFloat(p.end_balance) })
  // var val = 0
  // if (holdings.value.length === 0) dats.value.forEach((p, i) => { if (i<5) val += parseFloat(p.end_balance) })
  // else val = monthTotal.value
  return val
})
const compDiff = computed(() => {
  let diff = 0.0
  let da = holdings.value
  if (da.length === 0) dats.value.forEach((p, i) => { if (3 > i) diff += parseFloat(p.diff) })
  else diff = monthDiff.value
  return diff
})

// const compStocks = computed(() => {
//   // return holdings.value.length === 0 ? fmtcy(stocks.value) : fmtcy(getStockVal())
//   // return fmtcy(stocks.value)
//   return parseFloat(palist.value[0].end_balance) - chaseCash.value
//   // return chaseCash.value
// })
const savInfo = computed(() => {
  const actvs = savactvs.value
  let abal = null
  let ebal = null
  let anum = null
  actvs.forEach((p, i) => { if (i === 0) { abal = p.begin_balance; anum = p.account_num } })
  actvs.forEach((p, i) => { ebal = p.end_balance })
  let diff = ebal - abal
  abal = fmtcy(abal); ebal = fmtcy(ebal); diff = fmtcy(diff)
  return '<td class="one">节约账户</td><td class="text-white">' + anum + '</td><td class="text-right">' +
    abal + '</td><td class="text-right">' + ebal + '</td><td class="text-right q-px-sm">' + diff + '</td>'
})
const chkInfo2 = computed(() => {
  const actvs = chkactvs.value
  let abal = null
  let ebal = null
  let anum = null
  actvs.forEach((p, i) => { if (i === 0) { abal = p.begin_balance; anum = p.account_num; ebal = p.end_balance } })
  console.log(`-CK-abal=${abal} ebal=${ebal} anum=${anum}`, actvs)
  let diff = ebal - abal
  abal = fmtcy(abal)
  ebal = fmtcy(ebal)
  diff = fmtcy(diff)
  // anum = anum == null ? '' : anum
  return '<td class="one">支节账户</td><td class="text-white">' + anum + '</td><td class="text-right">' +
    abal + '</td><td class="text-right">' + ebal + '</td><td class="text-right q-px-sm">' + diff + '</td>'
})
const chkInfo = computed(() => {
  const actvs = chkactvs.value
  let abal = null
  let ebal = null
  let anum = null
  const start = clickedRow.value.bank === 'Chase' ? 0 : 1
  actvs.forEach((p, i) => { if (i === start) { abal = p.begin_balance; anum = p.account_num } })
  actvs.forEach((p, i) => { ebal = p.end_balance })
  if (anum === null) return null
  let diff = ebal - abal
  abal = fmtcy(abal); ebal = fmtcy(ebal); diff = fmtcy(diff)
  return '<td class="one">支付账户</td><td class="text-white">' + anum + '</td><td class="text-right">' +
    abal + '</td><td class="text-right">' + ebal + '</td><td class="text-right q-px-sm">' + diff + '</td>'
})
const fhold1 = computed(() => {
  let anum = null
  holdings.value.forEach(p => {
    if (!p.shown) {
      anum = p.account_name + ' ' + p.account_num
      p.shown = true
    }
  })
  return anum
})
const fhold2 = computed(() => {
  let anum = null
  holdings.value.forEach(p => {
    if (!p.shown) {
      anum = p.account_name + ' ' + p.account_num; p.shown = true
    }
  })
  return anum
})
const cANum = computed(() => {
  let anum = null
  chkactvs.value.forEach(p => { anum = p.account_num })
  return anum
})
const sANum = computed(() => {
  let anum = null
  savactvs.value.forEach(p => { anum = p.account_num })
  return anum
})

//== function sections
function getBankColorClass (e) {
  if (e.bank === 'Fidelity') return 'text-lime-14'
  else if (e.bank === 'Chase') return 'text-green-11'
  else if (e.bank === 'BOA') return 'text-green-13'
  else if (e.bank === 'NAC') return 'text-green-11'
  else if (e.bank === 'BKG') return 'text-cyan-6'
}
function getEdiffClass (e) {
  return e.diff > 0 ? 'text-green' : 'text-orange'
}
function getDiffClass () {
  return compDiff.value < 0 ?  'text-orange-9' : 'text-green-13'
}
function showCharts () {
  // console.log('-fn-showCharts', dats.value)
  emitter.emit('open-ChartsProxy', dats.value)
  openCharts.value = true
}
// function showBChart () {
//   console.log('-fn-showBChart')
//   emitter.emit('open-bChart', chartda.value)
//   openBChart.value = true
// }
// function showCChart () {
//   console.log('-fn-showCChart')
//   emitter.emit('open-cChart', chartda.value)
//   openCChart.value = true
// }
function setPos () {
  if (clickedRow.value.hideIt) return 'fixed'
  return 'relative'
}
function getAccountLabel (a) {
  let alabel = 0.0
  holdings.value.forEach(p => { if (p.account_num === a) { alabel = p.account_name + ' ~ ' + p.account_num } })
  return alabel
}
function getAccountEndBalance (a) {
  let abal = 0.0
  holdings.value.forEach(p => { if (p.account_num === a) { abal += parseFloat(p.end_balance) } })
  return fmtcy(abal)
}
function getAccountStartBalance (a) {
  let abal = 0.0
  holdings.value.forEach(p => { if (p.account_num === a && p.start_balance !== null) { abal += parseFloat(p.start_balance) } })
  return fmtcy(abal)
}
function Cost (a) {
  let abal = 0.0
  holdings.value.forEach(p => { if (p.account_num === a) { abal += parseFloat(p.cost) } })
  return fmtcy(abal)
}
function getADiff (a) {
  // console.log(`-CK-getADiff account=${a}`, holdings.value)
  let adiff = 0.0
  holdings.value.forEach(p => { if (p.account_num === a && p.diff != null) { adiff += parseFloat(p.diff) } })
  // if (a === '226-227936') console.log(`-CK-getADiff adiff=${fmtcy(adiff)}`)
  return adiff
}
function getAccountHoldings (a) {
  const holds = []
  holdings.value.forEach(p => { if (p.account_num === a) { holds.push(p) } })
  return holds
}
function getHoldings (e) {
  month.value = e.month
  // const curHideIt = !e.hideIt
  const path = process.env.API + '/bankstatement/getHoldings/' + e.bank + '/' + e.year + '/' + e.month
  // e.hideIt = curHideIt
  // if (!e.hideIt) axiosGet(args)
  gaxios(path)
}
function getAllHoldings (row, i) {
  let x = getBankRow(row, i)
  console.log('row: ', row)
  console.log(x[0].bank, x[1].bank, x[2].bank)
  if (x[0].bank !== 'Fidelity' ||  x[1].bank !== 'Chase' || x[2].bank !=='BOA') {
    let tit = 'Bank Row Assignment Wrong'
    let msg = 'at bank row=' + row.bank + ' and rowIdx=' +i
    emitter.emit('open-InfoDialog', tit, msg)
    return
  }
  getHoldings(x[0])
  chaseBal.value = parseFloat(x[1].end_balance)
  boaBal.value = parseFloat(x[2].end_balance)
  monthDiff.value = parseFloat(x[0].diff) + parseFloat(x[1].diff) + parseFloat(x[2].diff)
  monthTotal.value = parseFloat(x[0].end_balance) + parseFloat(x[1].end_balance) + parseFloat(x[2].end_balance)
  // console.log(`chaseBal=${chaseBal.value}, boaBal=${boaBal.value}` )
}
function getBankRow (row, i) {
  console.log(`-fn-getBankRow i=${i}`, row)
  let fidrow = null
  let charow = null
  let boarow = null
  if (row.bank === 'Fidelity') {
    fidrow = row
    charow = dats.value[i+1]
    boarow = dats.value[i+2]
  } else if (row.bank === 'Chase') {
    charow = row
    if (i === 0) {
      boarow = dats.value[1]
      fidrow = dats.value[2]
    } else if (i === 1) {
      fidrow = dats.value[0]
      boarow = dats.value[2]
    } else if (i === 2) {
      boarow = dats.value[0]
      fidrow = dats.value[1]
    } else {
      fidrow = dats.value[i-1]
      boarow = dats.value[i+1]
    }
  } else if (row.bank === 'BOA') {
    boarow = row
    if (i === 0) {
      fidrow = dats.value[1]
      charow = dats.value[2]
    } else if (i === 1) {
      charow = dats.value[0]
      fidrow = dats.value[2]
    } else if (i === 2) {
      fidrow = dats.value[0]
      charow = dats.value[1]
    } else {
      charow = dats.value[i-1]
      fidrow = dats.value[i-2]
    }
  }
  return [fidrow, charow, boarow]
}
function showDetails (e, i) {
  clickedRow.value = e
  clickedIdx.value = i
  // console.log(`-fn-showDetails clickedIdx=${clickedIdx.value}, hideIt=${!e.hideIt}`)
  const curHideIt = !e.hideIt
  dats.value.forEach(p => { p.hideIt = true })
  e.hideIt = curHideIt
  if (e.hideIt) return
  if (e.bank != 'NAC' && e.bank != 'BKG') getAllHoldings(e, i)
  if (e.bank == 'NAC' || e.bank == 'BKG') return
  if (e.bank === 'Fidelity') return
  const path = process.env.API + '/bankstatement/getDetails/' + e.bank + '/' + e.year + '/' + e.month
  if (!e.hideIt) gaxios(path)
}
emitter.on('bankstatement-getList', (da) => setList(da))

function setList(da) {
  console.log('-CK-setList', da.last_bkg_pdf)
  intraday.value = da.intraday
  last_bkg_pdf.value = da.last_bkg_pdf
  dats.value = da.dats
  let chartda = JSON.stringify(da.dats)   // clone dats
  chartda = JSON.parse(chartda).reverse()
  stocksVal.value = da.stocks_val
  month.value = da.intraday.substring(5, 7)
  chaseCash.value = da.chase_cash
  fidelCash.value = da.fidel_cash
  emitter.emit('dats', dats.value)
}
emitter.on('bankstatement-getHoldings', (da) => setHoldings(da))
function setHoldings(da) {
  console.log('-CK-setHoldings', da)
  holdings.value = da.holdings
  holdings.value.forEach(p => { accounts.value.push(p.account_num.trim()) })
  accounts.value = [...new Set(accounts.value)]
}
emitter.on('bankstatement-getDetails', (da) => setDetails(da))
function setDetails(da) {
  // console.log('-CK-setDetails', da)
  notes.value = da.notes
  // actvs.value = da.actvs
  chkactvs.value = da.chkactvs
  savactvs.value = da.savactvs
}
function getList () {
  const path = process.env.API + '/bankstatement/getList'
  gaxios(path)
}
// function setNumItemsPerPage (pageNumber) {
//   numItemsPerPage.value = parseInt(pageNumber)
//   console.log('setNumItemsPerPage', numItemsPerPage.value)
// }
function getIcon (i) {
  return palist.value[i].hideIt ? 'list' : 'dehaze'
}
function getStatementLink (e) {
  let lnk = ''
  let lnk2 = ''
  const yrmo = e.bank == 'NAC' ? String(e.year) + '07' : String(e.year) + e.month
  if (e.bank === 'BKG') {
    // lnk += 'Chase/' + intraday.value.substring(0, 7).replace('-', '') + '_bkg.pdf'
    lnk += 'Chase/' + last_bkg_pdf.value
    lnk = '<a href="/docs/' + lnk + '" target="_blank">Monthly Statement</a>&nbsp;&nbsp;&nbsp;'
  } else if (e.bank === 'NAC') {
    lnk += 'NAC/' + yrmo + '.pdf'
    lnk = '<a href="/docs/' + lnk + '" target="_blank">Annuity</a>&nbsp;&nbsp;&nbsp;'
  } else if (e.bank === 'Chase') {
    lnk += 'Chase/' + yrmo + '.pdf'
    lnk = '<a href="/docs/' + lnk + '" target="_blank">CHK SAV</a>&nbsp;&nbsp;&nbsp;'
    lnk1 = 'Chase/' + yrmo + '_bkg.pdf'
    lnk1 = '<a href="/docs/' + lnk1 + '" target="_blank">Brokerage</a>'
    lnk = lnk + lnk1
  } else if (e.bank === 'BOA') {
    lnk += 'BOA/' + yrmo + '_checking.pdf'
    lnk = '<a href="/docs/' + lnk + '" target="_blank">CHK</a>&nbsp;&nbsp;&nbsp;'
    var lnk1 = 'BOA/' + yrmo + '_savings.pdf'
    lnk1 = '<a href="/docs/' + lnk1 + '" target="_blank">SAV</a>'
    lnk = lnk + lnk1
  } else if (e.bank === 'Fidelity') {
    lnk += 'Fidelity/' + yrmo + '_ira.pdf'
    lnk = '<a href="/docs/' + lnk + '" target="_blank">IRA</a>'
    lnk1 = 'Fidelity/' + yrmo + '_roth.pdf'
    lnk1 = '<a href="/docs/' + lnk1 + '" target="_blank">ROTH</a>'
    lnk2 = 'Fidelity/' + getYQ(e) + '_annuity.pdf'
    lnk2 = '<a href="/docs/' + lnk2 + '" target="_blank">Annuity</a>'
    lnk = lnk + '&nbsp;' + lnk1 + '&nbsp;' + lnk2
  }
  e.mstatement = lnk
  return lnk
}
function getYQ (e) {
  let YQ = null
  const m = parseInt(e.month)
  const y = parseInt(e.year)
  // console.log('-dg-', e.year, m)
  if (m > 0 && m <= 2) YQ = String(y - 1) + 'Q4'
  else if (m === 3) YQ = y + 'Q1'
  else if (m > 3 && m <= 5) YQ = y + 'Q1'
  else if (m === 6) YQ = y + 'Q2'
  else if (m > 6 && m <= 8) YQ = y + 'Q2'
  else if (m === 9) YQ = y + 'Q3'
  else if (m > 9 && m <= 11) YQ = y + 'Q3'
  else if (m === 12) YQ = y + 'Q4'
  return YQ
}
</script>
<!-- style scoped>
a:link { color:white }
a:visited { color:white }
a:active { color:white }
a:hover { color:white }
</!-->

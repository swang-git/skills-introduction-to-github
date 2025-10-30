<template>
<q-dialog v-model="opened" maximized>
  <q-layout class="bg-teal-10">
    <div class="text-center text-h5 text-white q-py-sm">Portfolio {{ compAsOfTime }}</div>
    <!-- <table v-for="(a,i) in accounts" :key=a.id class="q-px-md q-pl-sm" :style="{ marginTop: i===0 ? '1px' : '1px' }"> -->
    <table v-for="a in accounts" :key=a.id class="q-px-md q-pl-sm">
      <q-tr v-show="showAccnt===a">
        <th class="wic">Symbol</th>
        <th class="wic">Price Change</th>
        <th class="wic">Today G/L</th>
        <th class="wic">Total G/L</th>
        <th class="wic">Current Value</th>
        <th class="wic">Quantity</th>
        <th class="wic">Cost Basis</th>
        <th class="wic cursor-pointer" @click="opened=false">52 Week Low/High</th>
      </q-tr>
      <template v-for="(row) in compDats.filter(p => p.account===a)" :key="row.x">
        <!-- <tr :key=row.x class="bg-teal-8" v-show="showAccnt===a"> -->
        <q-tr class="bg-teal-8" v-show="showAccnt===a">
          <td class="wic" rowspan="2" v-text="row.symbol"></td>
          <td class="wix" v-text="row.price" />
          <td class="wix" :class="{'bg-red-9':row.today_gain_loss<0}" v-text="row.today_gain_loss" />
          <td class="wid" :class="{'bg-red-9':row.total_gain_loss<0}" v-text="row.total_gain_loss" />
          <td class="wid" rowspan="2" v-text="row.current_value" />
          <td class="wid" rowspan="2" v-text="row.quantity" />
          <td v-if="row.share_cost===null"></td>
          <td v-else class="wid">{{ row.share_cost }}/share </td>
          <td class="wiw" v-text="row.low_52_week" />
        </q-tr>
        <!-- <tr :key=row.x class="bg-teal-9" v-show="showAccnt===a"> -->
        <q-tr class="bg-teal-9" v-show="showAccnt===a">
          <td class="wix" :class="{'bg-red-9':row.change<0}" v-text="row.change" />
          <td class="wix" v-if="row.today_gl_pct==null">{{row.today_gl_pct}}</td>
          <td class="wix" v-else :class="{'bg-red-9':row.today_gl_pct<0}">{{row.today_gl_pct}}%</td>
          <td class="wid" v-if="row.total_gl_pct==null || row.total_gl_pct==0"></td>
          <td class="wid" v-else :class="{'bg-red-9':row.total_gl_pct<0}">{{row.total_gl_pct}}%</td>
          <td class="wid" v-text="row.total_cost" />
          <td class="wiw" v-text="row.high_52_week" />
        </q-tr>
      </template>
      <q-tr class="text-white text-h6" :class="{ 'bg-brown-9':showAccnt!==a, 'bg-teal-10':showAccnt===a }">
        <td class="q-px-xs text-center" colspan="2" rowspan="2">Account Total</td>
        <td class="wid" :class="{'bg-red-9':getTodayGL(a)<0}" v-text="getTodayGL(a)" />
        <td class="wid" v-text="getTotalGL(a)" />
        <td class="wid" rowspan="2" v-text="getCVTotal(a)" />
        <td class="text-right" colspan="3" rowspan="2" style="width:400px">
          <span class="q-px-xs text-amber" style="float:left">{{ a }}</span>
          <span class="text-cyan-3 q-pr-sm cursor-pointer" @click="showDetails(a)">{{ accountName[a] }}</span>
        </td>
      </q-tr>
      <q-tr :class="{ 'bg-brown-9':showAccnt!==a, 'bg-teal-10':showAccnt===a }">
        <td class="wid" :class="{'bg-red-9':getTodayGL(a)<0}" v-text="getTodayGLPct(a)" />
        <td class="wid" v-text="getTotalGLPct(a)" />
      </q-tr>
    </table>
    <div class="q-pl-md">
    <table class="bg-blue-10">
      <q-tr class="q-pl-md text-h6 text-white">
        <td class="text-center text-h4 vertical-middle q-pl-md" colspan="2" rowspan="2">Total</td>
        <td class="q-pl-xl text-right" :class="{'bg-red-9':getTodayGLP()<0}" v-text="getTodayGLP()" />
        <td class="q-pl-xs text-right" v-text="getTotalGLP()" />
        <td class="q-pl-xs text-right" rowspan="2" v-text="getCVTotalP()" />
      </q-tr>
      <q-tr>
        <td class="wid" :class="{'bg-red-9':getTodayGLP()<0}" v-text="getTodayGLPctP()" />
        <td class="wid" v-text="getTotalGLPctP()" />
      </q-tr>
    </table>
    </div>
    <div class="q-px-lg float-right" style="margin-top:-60px"><q-btn round glossy icon="cancel" color="red" @click="opened=false" /></div>
  </q-layout>
</q-dialog>
</template>
<script setup>
import { ref, computed } from 'vue'
import emitter from 'tiny-emitter/instance'
const dats = ref([])
const accounts = ref([])
const showAccnt = ref('XXX')
const noSharePriceStocks = ['CHTR', 'CSCO', 'DELL', 'T', 'MSFT']
const opened = ref(false)

console.log('-ST-PortfolioDisplay')
emitter.on('open-PortfolioDisplay', (x) => openIt(x))

const accountName = {
  Z71367818: 'INDIVIDUAL - TOD',
  226227936: 'ROTH IRA',
  486579416: 'TRADITIONAL IRA',
  232080445: 'Deferred Annuity',
  X85275143: 'INDIVIDUAL - TOD'
}
const sharePrice = {
  CHTR: 36.2612, // this share price as of 2009
  CSCO: 38.65625, // this share price 77.3125/2 as of 2000-01(assuming bought on this day) -- split once on 2000-02-22
  DELL: 42.17, // this share price on 2018-12-21
  T: 20.00, // ???
  // average share price with beginning and ending of year2000 and split once(2003)
  MSFT: 40.00 // this price should be spliting accordingly if stock splited
}

const compAsOfTime = computed(() =>{
  if (dats.value.length > 0) return dats.value[0].asof_time.substr(0, 16)
  return n.valueull
})
const compDats = computed(() => {
  const da = dats.value
  da.forEach(p => {
    if (noSharePriceStocks.includes(p.symbol)) {
      p.share_cost = sharePrice[p.symbol].toFixed(2)
      p.total_cost = (p.share_cost * p.quantity).toFixed(3)
      p.total_gain_loss = (p.current_value - p.share_cost * p.quantity).toFixed(2)
      p.total_gl_pct = (p.total_gain_loss * 100.00 / p.total_cost).toFixed(2)
    }
  })
  return da
})

//== function sections
function showDetails (a) {
  showAccnt.value = showAccnt.value === a ? 'XXX' : a
}
function getCVTotal (accnt, getFloat = false) {
  const accntData = compDats.value.filter(p => p.account === accnt)
  let val = 0.0
  accntData.forEach(p => {
    val += p.current_value === null ? 0 : parseFloat(p.current_value)
  })
  if (getFloat) return val
  return val.toFixed(2)
}
function getCVTotalP () {
  let val = 0.0
  compDats.value.forEach(p => {
    val += p.current_value === null ? 0 : parseFloat(p.current_value)
  })
  return val.toFixed(2)
}
function getTodayGL (accnt) {
  const accntData = compDats.value.filter(p => p.account === accnt)
  let val = 0.0
  accntData.forEach(p => {
    val += p.today_gain_loss === null ? 0 : parseFloat(p.today_gain_loss)
  })
  if (val === 0) return null
  return val.toFixed(2)
}
function getTodayGLP () {
  let val = 0.0
  compDats.value.forEach(p => {
    val += p.today_gain_loss === null ? 0 : parseFloat(p.today_gain_loss)
  })
  if (val === 0) return null
  return val.toFixed(2)
}
function getTotalGL (accnt, getFloat = false) {
  const accntData = compDats.value.filter(p => p.account === accnt)
  let val = 0.0
  accntData.forEach(p => {
    val += p.total_gain_loss === null ? 0 : parseFloat(p.total_gain_loss)
  })
  if (val === 0) return null
  if (getFloat) return val
  return val.toFixed(2)
}
function getTotalGLP () {
  let val = 0.0
  compDats.value.forEach(p => {
    val += p.total_gain_loss === null ? 0 : parseFloat(p.total_gain_loss)
  })
  if (val === 0) return null
  return val.toFixed(2)
}
function getTotalCost (accnt) {
  const accntData = compDats.value.filter(p => p.account === accnt)
  let val = 0.0
  accntData.forEach(p => {
    val += p.total_cost === null ? 0 : parseFloat(p.total_cost)
  })
  return val
}
function getTotalCostP () {
  let val = 0.0
  compDats.value.forEach(p => {
    val += p.total_cost === null ? 0 : parseFloat(p.total_cost)
  })
  return val
}
function getTodayGLPct (accnt) {
  const todayGLtotal = getTodayGL(accnt, true)
  const totalCurrVal = getCVTotal(accnt, true)
  if (todayGLtotal === null) return null
  // const compCV = todayGLtotal > 0 ? todayGLtotal : -1 * todayGLtotal
  // const val = 100 * todayGLtotal / (totalCurrVal + compCV)
  const val = 100 * todayGLtotal / (totalCurrVal - todayGLtotal) // last day current value at bottom
  // console.log('=ST=== getTodayGLPct()', val)
  return val.toFixed(2) + '%'
  // if (val <eturn (val - 0.005).toFixed(2) + '%'
  // if (val > 0) return (val + 0.005).toFixed(2) + '%'
}
function getTodayGLPctP () {
  const todayGLtotalP = getTodayGLP()
  const totalCurrValP = getCVTotalP()
  if (todayGLtotalP === null) return null
  var ret = (100 * todayGLtotalP / (totalCurrValP - todayGLtotalP)).toFixed(2) + '%'
  // var ret = (100 * todayGLtotalP / (totalCurrValP - todayGLtotalP) + 0.005).toFixed(2) + '%'
  // if (todayGLtotalP < 0) ret = (100 * todayGLtotalP / (totalCurrValP - todayGLtotalP) - 0.005).toFixed(2) + '%'
  return ret
}
function getTotalGLPct (accnt) {
  const totalGLtotal = getTotalGL(accnt)
  const totalCost = getTotalCost(accnt)
  if (totalCost === 0) return null
  return (100 * totalGLtotal / totalCost).toFixed(2) + '%'
}
function getTotalGLPctP () {
  // console.log('=ST=====', (42.58470424423702).toFixed(2))
  const totalGLtotalP = getTotalGLP()
  if (totalCostP === 0) return null
  // return (100 * totalGLtotalP / totalCostP + 0.005).toFixed(2) + '%'
  return (100 * totalGLtotalP / totalCostP).toFixed(2) + '%'
}
function setData (ds) {
  dats.value = ds
  dats.value.forEach(p => {
    if (accounts.value.includes(p.account)) return
    accounts.value.push(p.account)
  })
}
function openIt (ds) {
  setData(ds)
  console.log(`-fn-openIt dats.length=${dats.value.length}`, dats.value)
  if (dats.value.length > 0) opened.value = true
}
</script>
<style>
.wic {
  width: 70px;
  padding: 0 4px 0 4px;
  text-align: center;
  font-size: 18px;
  color: white;
}
.wiw {
  width: 90px;
  padding: 0 4px 0 4px;
  text-align: right;
  font-size: 18px;
  color: white;
}
.wid {
  width: 107px;
  padding: 0 4px 0 4px;
  text-align: right;
  font-size: 18px;
  color: white;
}
.wix {
  width: 80px;
  padding: 0 4px 0 4px;
  text-align: right;
  font-size: 18px;
  color: white;
}
.hiw {
  width: 130px;
  padding: 0 4px 0 4px;
  text-align: center;
  font-size: 16px;
  color: white;
  vertical-align: middle;
}
.hcv {
  width: 135px;
  /* padding-left: -14px; */
  /* padding: 0 4px 0 -4px; */
  /* margin: 0 4px 0 -4px; */
  text-align: center;
  font-size: 16px;
  color: white;
  vertical-align: middle;
}
.hiq {
  width: 100px;
  /* margin: 0 4px 0 4px; */
  padding-left: 5px;
  text-align: left;
  font-size: 16px;
  color: white;
  vertical-align: middle;
}
.hid {
  width: 105px;
  /* margin: 0 4px 0 4px; */
  /* padding-left: 4px; */
  text-align: center;
  font-size: 16px;
  color: white;
  vertical-align: middle;
  /* background: teal; */
}
.hig {
  width: 90px;
  padding-left: 18px;
  text-align: left;
  font-size: 16px;
  color: white;
  vertical-align: middle;
  /* background: teal; */
}
.hil {
  width: 100px;
  padding-left: 18px;
  text-align: center;
  font-size: 16px;
  color: white;
  vertical-align: middle;
}
.hcb {
  /* padding-left: 14px; */
  /* margin-left: 5px; */
  width: 110px;
  text-align: center;
  font-size: 16px;
  color: white;
  vertical-align: middle;
}
.hix {
  width: 60px;
  padding: 0 4px 0 4px;
  text-align: center;
  font-size: 16px;
  color: white;
}
</style>

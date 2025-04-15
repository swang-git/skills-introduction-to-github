<template>
<q-dialog v-model="opened" full-width persistent>
<div style="margin:90px 0 0 15px; border:2px red solid">
  <div class="q-pt-sm q-px-md text-h5 text-bold bg-teal row" style="border:2px red solid">
    <div class="q-pb-sm"><q-btn round glossy icon="close" color="amber-10" v-close-popup/></div>
    <div v-if="!dataIn" class="q-px-md q-pt-xs">Chase Brockerage Account</div>
    <div v-if="dataIn"  class="q-px-md q-pt-xs">Chase Brockerage</div>
    <div v-if="dataIn" class="q-pb-sm">
      <q-btn v-if="pidx>0" round glossy icon="arrow_left" color="teal-9" @click="prevp()" />
      <q-btn v-if="pidx<=0" round flat />
    </div>
    <div class="q-px-md q-pt-xs">{{ datei }}</div>
    <div v-if="dataIn" class="q-pb-sm">
      <q-btn v-if="dataIn && pidx<dates.length-1" round glossy icon="arrow_right" color="primary" @click="nextp()" />
      <q-btn v-if="pidx>=dates.length-1" round flat color="primary" />
    </div>
    <div class="q-px-md q-pt-xs">{{ account }}</div>
    <div v-if="!dataIn" class="q-pb-sm q-pl-lg">
      <q-btn class="q-pb-sm" round glossy icon="存" color="primary" @click="saveData()" />
    </div>
  </div>
  <table class="bg-cyan q-px-md" style="width:100%">
    <q-tr style="font-size:17px">
      <th>Stock</th>
      <th>Price</th>
      <th>Prch</th>
      <th>Prch%</th>
      <th>CurrValue</th>
      <th class="q-pl-md">Share</th>
      <th>DateGL</th>
      <th>Total G/L</th>
      <th>TotalGL%</th>
      <th class="q-pl-xs">BaseCost</th>
    </q-tr>
    <q-tr v-for="pos in compPositions" :key="pos.symbol" style="font-size:17.1px">
      <td>{{ pos.symbol }}</td>
      <td class="text-right q-px-sm">{{ parseFloat(pos.price).toFixed(2) }}</td>
      <td class="text-right q-px-sm" v-if="pos.prchg>=0">{{ parseFloat(pos.prchg).toFixed(2) }}</td>
      <td class="text-right q-px-sm text-red" v-else >{{ (parseFloat(pos.prchg) * (-1)).toFixed(2) }}</td>
      <td class="text-right q-px-sm" v-if="pos.prchg_p>=0">{{ parseFloat(pos.prchg_p).toFixed(2) }}%</td>
      <td class="text-right q-px-sm text-red" v-else >{{ (parseFloat(pos.prchg_p) * (-1)).toFixed(2) }}%</td>
      <td class="text-right q-px-sm">{{ pos.value }}</td>
      <td class="text-right q-px-sm">{{ pos.share }}</td>
      <td class="text-right q-px-sm" v-if="pos.today_gl>=0">{{ parseFloat(pos.today_gl).toFixed(2) }}</td>
      <td class="text-right q-px-sm text-red" v-else >{{ (parseFloat(pos.today_gl) * (-1)).toFixed(2) }}</td>
      <td class="text-right q-px-sm" v-if="pos.total_gl>=0">{{ parseFloat(pos.total_gl).toFixed(2) }}</td>
      <td class="text-right q-px-sm text-red" v-else >{{ (parseFloat(pos.total_gl) * (-1)).toFixed(2) }}</td>
      <td class="text-right q-px-sm" v-if="pos.total_gl_p>=0">{{ parseFloat(pos.total_gl_p).toFixed(2) }}%</td>
      <td class="text-right q-px-sm text-red" v-else >{{ (parseFloat(pos.total_gl_p) * (-1)).toFixed(2) }}%</td>
      <td class="text-right q-px-sm">{{ parseFloat(pos.cost).toFixed(2) }}</td>
    </q-tr>
    <q-tr class="text-bold" style="font-size:18px">
      <td colspan="8" class="text-no-wrap text-right">Today Total Gain/Loss:</td>
      <td v-if="today_total_gl>=0" colspan="2" class="text-right q-px-sm">{{ today_total_gl.toFixed(2) }}</td>
      <td v-else colspan="2" class="text-right text-red q-px-sm">{{ -today_total_gl.toFixed(2) }}</td>
    </q-tr>
    <q-tr class="text-bold" style="font-size:18px">
      <td colspan="8" class="text-no-wrap text-right">Total Value:</td>
      <td colspan="2" class="text-right q-px-sm">{{ today_total_val.toFixed(2) }}</td>
    </q-tr>
  </table>
</div>
</q-dialog>
</template>
<script setup>
import { ref, computed } from 'vue'
import emitter from 'tiny-emitter/instance'
// import { dayFunctions } from 'src/composables/dayFunctions';
import { axiosFunctions } from 'src/composables/axiosFunctions'
import { libFunctions } from 'src/composables/libFunctions'
const { $q } = libFunctions()
const { gaxios, paxios } = axiosFunctions()
const opened = ref(false)
const statement = ref({})
const bank = ref(null)
const bankname = ref(null)
const date = ref(null)
const account = ref(null)
const positions = ref([])
const todayTotalVal = ref(null)
const todayTotalGL = ref(0)
const dataIn = ref(false)
const dates = ref([])
const pidx = ref(0)
const year = ref(null)
const month = ref(null)

const emit = defineEmits('intra-day')

console.log('-ST-MonthlyStatementsChaseBkg')

emitter.on('close-ChaseBrockerageIntraday', () => opened.value = false)
emitter.on('open-ChaseBrockerageIntraday', (x) => getData(x))
emitter.on('bankstatementloader-loadChaseBkg', (x) => setData(x))

const datei = computed(() => { return dates.value[pidx.value] })
const compPositions = computed(() => { return dataIn.value ? positions.value.filter(x => x.date == datei.value) : positions.value })
const today_total_val = computed(() => { return compPositions.value.map(x => parseFloat(x.value)).reduce((a, b) => a + b, 0) })
const today_total_gl  = computed(() => { return compPositions.value.map(x => parseFloat(x.today_gl)).reduce((a, b) => a + b, 0) })

function prevp () { pidx.value-- }
function nextp () { pidx.value++; console.log(`pidx=${pidx.value}`)}

function saveData () {
  // const inData = { positions: positions.value, totalVal: total_val.value, totalGL: total_gl.value }
  const inData = { positions: positions.value }
  const path = process.env.API + '/bankstatementloader/saveData'
  paxios(path, inData)
}
function setData (da) {
  console.log(`-fn-setData dataInDB=${da.dataIn}`)
  opened.value = true
  pidx.value = 0
  dataIn.value = da.dataIn > 0 ? true : false
  positions.value = da.pos
  account.value = da.pos[0].account
  date.value = da.pos[0].date
  emit('intra-day', date.value)

  if (dataIn.value) {
    let datex = da.pos.map(x => x.date)
    dates.value = [... new Set(datex)]
    console.log(`datei=${datei.value}`)
  } else { // new data from a txt file and not saved to Database yet
    dates.value = [da.pos[0].date]
    todayTotalVal.value = da.today_total_val
    todayTotalGL.value = da.today_total_gl
    if (!checkTotals()) return
  }
  // console.log(`-fn-setData datei=${datei.value} total_val=${total_val.value}`, dates.value, compPositions.value)
  console.log(`-fn-setData datei=${datei.value} today_total_val=${today_total_val.value} pidx=${pidx.value}`)
}

function checkTotals () {
  const todayTotalValx = positions.value.map(x => parseFloat(x.value)).reduce((a, b) => a + b, 0)
  console.log(`-fn-checkTotalVal: todayTotalValx=${todayTotalValx} todayTotalVal=${todayTotalVal.value}`)
  if (todayTotalValx != todayTotalVal.value) {
    $q.dialog({
      title: 'Total Value Check Failed',
      message: `${todayTotalValx} != ${todayTotalVal.value}`
    })
    return false
  }
  const x = positions.value.map(x => parseFloat(x.today_gl))
  const todayTotalGLx = x.reduce((a, b) => a + b, 0)
  console.log(`-fn-checkGL: todayTtalGL=${todayTotalGL.value} todayTotalGLx=${todayTotalGLx}`)
  if (Math.abs(parseFloat(todayTotalGLx) - todayTotalGL.value) > 0.001) {
    $q.dialog({
      title: 'Today Total GL Check Failed',
      message: `${todayTotalGL.value} != ${todayTotalGLx.toFixed(2)} (todayTotalGLx)`
    })
    return false
  }
  return true;
}

function getData(stmt) {
  console.log(`-fn-loadData stmt.bank=${stmt.bank}`, stmt)
  if (stmt.bank !== 'ChaseBkg') {
    opened.value = 'none'
    return
  }
  opened.value = ''
  statement.value = stmt
  bank.value = stmt.bank
  const x = stmt.date.split('-')
  year.value = x[0]
  month.value = x[1]
  let yyyymm = x[0]+x[1]
  const path = process.env.API + '/bankstatementloader/loadChaseBkg/' + yyyymm
  gaxios(path)
}
</script>

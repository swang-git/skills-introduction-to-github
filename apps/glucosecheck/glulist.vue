<template>
<div style="display:grid;place-items:center;height:100vh;margin:-18px 0 0 0">
  <q-table class="sh-sticky-header-table" :rows="palist" :columns="columns" dense dark hide-pagination :grid=false
    :visible-columns="isIM ? visibleColumnsFone : visibleColumnsDesk"
    wrap-cells style="font-family:stfangsong;font-weight:600"
    :fullscreen="isIM ? true : false" table-style="isIM ? {width:'373px'} : {}" :style="isIM ? {height: '585px'} : {}"
    row-key="datetime"
    :pagination="isIM ? {rowsPerPage:itemsPerPageIM} : {rowsPerPage:itemsPerPageDesk}"
    :separator="separator" :faVal="faVal">

    <!-- <template v-slot:top="props"> -->
    <template>
    <div v-if="isDesk" class="row">
      <div v-if="$q.screen.gt.xs" class="col text-bold">
        <q-toggle v-model="visibleColumnsDesk" val="datetime" label="时间" />
        <q-toggle v-model="visibleColumnsDesk" val="week" label="星期" />
        <q-toggle v-model="visibleColumnsDesk" val="fasting" label="血糖" />
        <q-toggle v-model="visibleColumnsDesk" val="food" label="食入" />
        <q-toggle v-model="visibleColumnsDesk" val="drink" label="饮入" />
        <q-toggle v-model="visibleColumnsDesk" val="fruit" label="水果" />
        <q-toggle v-model="visibleColumnsDesk" val="note" label="注释" />
        <q-toggle v-model="visibleColumnsDesk" val="a1cp" label="A1c" />
      </div>
    </div>
    <div v-else class="row">
      <div v-if="$q.screen.gt.xs" class="col">
        <q-toggle v-model="visibleColumnsFone" val="datetime" label="时间" />
        <q-toggle v-model="visibleColumnsFone" val="fasting" label="血糖量" />
        <q-toggle v-model="visibleColumnsFone" val="a1cp" label="%" />
      </div>
      <!-- <q-select
        v-model="visibleColumnsDesk" multiple borderless dense options-dense
        emit-value map-options
        option-value="name" style="min-width: 150px"
        :display-value="$q.lang.table.columns"
        :options="columns"
      /> -->
    </div>
    <!-- <div v-if="isIM" class="row q-pl-md">
      <q-btn flat round dense color="accent" :icon="props.inFullscreen ? 'fullscreen_exit' : 'fullscreen'" @click="props.toggleFullscreen" class="q-pr-xs" />
      <q-btn flat round icon="donut_small" color="cyan" @click="props.toggleFullscreen();showA1xChart()" class="float-right" />
      <q-btn flat round icon="equalizer" color="blue" @click="props.toggleFullscreen();showA1pChart()" class="float-right" />
      <q-btn flat round icon="trending_down" color="pink" @click="props.toggleFullscreen();showEagChart()" class="float-right" />
    </div> -->
  </template>

  <template v-slot:header="props">
    <q-tr :props="props">
      <q-th v-for="col in props.cols" :key="col.name" :props="props" class="text-lime text-center">{{ col.label }}</q-th>
    </q-tr>
  </template>

  <template v-slot:body="p">
    <q-tr :props="p" style="cursor:grab">
      <q-td v-for="col in p.cols" :key=col @click="showExpend(col.name, p)" :style="getStyle(col.name)" :class="getClass(col.name, p.row)">{{ getValue(col, p.row) }}</q-td>
    </q-tr>
    <q-tr v-show="p.expand" :props="p">
      <q-td colspan="100%" class="q-pt-xs">
        <table v-if="isDesk" style="border:1px solid #ccc;margin-top:-8px;min-width:100%">
          <q-tr v-if="p.row.food ==null">
            <td>今 日</td>
            <td>eAG: {{ p.row.fasting }} mg/dL (空腹血糖)</td>
            <td>A1c: {{ ((p.row.fasting + 46.7) / 28.7).toFixed(1) }}%</td>
            <td style="white-space:nowrap">eAG: {{ (p.row.fasting / 18.015).toFixed(1) }} mmol/L (中)</td>
            <td class="text-right">
              <q-fab v-model="fabOpen" flat icon="keyboard_arrow_down" direction="down">
                <q-btn round glossy color="red-10"   @click="showDar(p.row, 'del')" size="16px" style="border:1px solid cyan" icon="delete" />
                <q-btn round glossy color="indigo-9" @click="showDar(p.row, 'upd')" size="16px" style="border:1px solid cyan" icon="update" />
                <q-btn round glossy color="green-10" @click="showDar(p.row, 'add')" size="16px" style="border:1px solid cyan" icon="add_circle" />
              </q-fab>
            </td>
          </q-tr>
          <q-tr v-if="p.row.food !=null">
            <td>主 食</td><td colspan="3" style="width:557px">{{ p.row.food }} {{ p.row.fasting }}</td>
            <td>
              <q-fab v-model="fabOpen" flat icon="keyboard_arrow_down" direction="down">
                <q-btn round glossy color="pink-9"  @click="showDar(p.row, 'del')" size="16px" style="border:1px solid cyan" icon="delete" />
                <q-btn round glossy color="indigo"  @click="showDar(p.row, 'upd')" size="16px" style="border:1px solid cyan" icon="update" />
                <q-btn round glossy color="primary" @click="showDar(p.row, 'add')" size="16px" style="border:1px solid cyan" icon="add_circle" />
              </q-fab>
            </td>
          </q-tr>
          <q-tr v-if="p.row.drink!=null"><td style="width:20px">饮 料</td><td colspan="4" style="width:680px">{{ p.row.drink }}</td></q-tr>
          <q-tr v-if="p.row.fruit!=null && p.row.fasting"><td style="width:20px">水 果</td><td colspan="4" style="width:680px">{{ p.row.fruit }}(昨日)</td></q-tr>
          <q-tr v-else-if="p.row.fruit!=null"><td style="width:20px">水 果</td><td colspan="4" style="width:680px">{{ p.row.fruit }}</td></q-tr>
          <q-tr v-if="p.row.note !=null && p.row.fasting"><td style="width:20px">昨 日 三 顿 餐 饮</td><td colspan="4" style="width:680px" v-html="p.row.note" /></q-tr>
          <q-tr v-else-if="p.row.note !=null"><td style="width:20px">注 释</td><td colspan="4" style="width:680px" v-html="p.row.note" /></q-tr>
          <q-tr v-if="p.row.bloodPressure!=null">
            <td class="text-left" colspan="5">BLOOD PRESSURE : <span class="text-white"> {{ p.row.bloodPressure }} </span></td>
          </q-tr>
          <q-tr>
            <td class="bg-cyan-10">项 目</td>
            <td class="bg-cyan-9" style="white-space:nowrap">过 去 90 天 的 血 糖 平 均 值</td>
            <td class="bg-green-9 text-center">正 常 值</td>
            <td class="bg-amber-9 text-center text-black">Prediabetes</td>
            <td class="bg-pink-10 text-center">Diabetes</td>
          </q-tr>
          <q-tr>
            <td class="bg-cyan-10" style="width:20px">A1c%</td>
            <td class="bg-cyan-9" style="width:280px">{{ p.row.a1cpX }}</td>
            <td class="bg-green-9 text-center text-no-wrap">Below 5.7</td>
            <td class="bg-amber-9 text-center text-no-wrap text-black">5.7 ~ 6.4</td>
            <td class="bg-pink-10 text-center text-no-wrap">Above 6.4</td>
          </q-tr>
          <q-tr>
            <td class="bg-cyan-10" style="width:20px">EAG</td>
            <td class="bg-cyan-9" colspan="1" style="width:280px">{{ p.row.eagX }}</td>
            <td class="bg-green-9 text-center text-no-wrap text">Below 117</td>
            <td class="bg-amber-9 text-center text-no-wrap text text-black">117 ~ 137</td>
            <td class="bg-pink-10 text-center text-no-wrap text">Above 137</td>
          </q-tr>
          <q-tr>
            <td class="bg-cyan-10" style="width:20px">A1C</td>
            <td class="bg-cyan-9" colspan="1" style="width:280px">{{ p.row.a1cX }}</td>
            <td class="bg-green-9 text-center text-no-wrap">Below 38.8</td>
            <td class="bg-amber-9 text-center text-no-wrap text-black">38.8 ~ 46.5</td>
            <td class="bg-pink-10 text-center text-no-wrap">Above 46.5</td>
          </q-tr>
        </table>
      </q-td>
    </q-tr>
  </template>
  </q-table>
  <gludar @close-expand="lastClickedRow.expand = false" />
  <ChartProxy :clvs="clvs" :glus="gluSections" :gludata="gludata" :xlabel="xlabel" />
</div>
</template>
<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'
import gludar from './gludar'
import ChartProxy from './ChartProxy'

import { axiosFunctions } from '../src/composables/axiosFunctions'
const { gaxios } = axiosFunctions()
import { dayFunctions } from '../src/composables/dayFunctions'
const { today, getDateGap, between, chwk2 } = dayFunctions()
import { libFunctions } from '../src/composables/libFunctions'
const { isDesk, isIM, buildApp, palist, dalist, $q } = libFunctions()

//== data sections
const fabOpen = ref(true)
const clvs = ref([])
const xlabel = ref([])
const currIdx = ref(0)
const gluSections = ref([])
const gludata = ref([])
const lastClickedRow = ref({row:{id:0}})
const clickedIdx = ref(0)
const itemsPerPageDesk = 21
const itemsPerPageIM = 14
const dats = ref([])
const separator = ref('cell')
const faVal = ref(null)
const visibleColumnsDesk = ref(['datetime', 'week', 'fasting', 'weight', 'food', 'a1cp'])
const visibleColumnsFone = ref(['datetime', 'fasting', 'weight', 'a1cp'])
const columns = ref([
  { required: true, label: '测 试 时 间', align: 'center', name: 'datetime', field: 'datetime', sortable: true, headerStyle:'font-weight:800;font-size:22px' },
  // { required: false, label: '星期', align: 'center', name: 'week', field: 'week', sortable: true, headerStyle:'max-width:50px;font-weight:800;font-size:22px;white-space:nowrap' },
  { required: false, label: '周', align: 'center', name: 'week', field: 'week', sortable: true, headerStyle:'max-width:50px;font-weight:800;font-size:22px;white-space:nowrap' },
  { required: true, label: '血 糖 值', align: 'center', name: 'fasting', field: 'fasting', sortable: true, headerStyle:'max-width:30px;font-weight:800;font-size:22px;white-space:nowrap' },
  { required: false, label: '体 重', align: 'center', name: 'weight', field: 'weight', sortable: false, format:(val, row) => `${parseFloat(val).toFixed(1)}` },
  { required: false, label: '食入 或 空腹', align: 'center', name: 'food', field: 'food', sortable: false, headerStyle:'font-weight:800;font-size:22px' },
  { required: false, label: '饮 入', align: 'center', name: 'drink', field: 'drink', sortable: true, headerStyle:'font-weight:800;font-size:22px'},
  { required: false, label: '水 果', align: 'left', name: 'fruit', field: 'fruit', sortable: true, class: 'text-no-wrap ellipsis' },
  { required: false, label: '注 释', align: 'left', name: 'note', field: 'note', sortable: true },
  { required: false, label: '%', align: 'center', name: 'a1cp', field: 'a1cpX', sortable: false }
])

console.log('-ST-glulist')
emitter.on('glucosecheck-getList', (x) => setList(x))
emitter.on('glucosecheck-add', (x) => setList(x))
emitter.on('glucosecheck-upd', (x) => setList(x))
emitter.on('glucosecheck-del', (x) => setList(x))
emitter.on('search', (searchQuery) => { searchQuery = searchQuery })
emitter.on('show-clv-chart', () => { showAllCharts() })
buildApp('血糖控制', 'glucosecheck')
emitter.emit('items-per-page', isIM ? itemsPerPageIM : itemsPerPageDesk)
getList()

//== function section
function showA1xChart () {
// do nothing -- redirect to other charts
}
function X_OLD_showAllCharts () {
  console.log('-fn-showAllCharts', dalist.value)
  clvs.value = []
  dalist.value.filter(x => x.food == null).forEach((p, i) => clvs.value.push({ idx: p.idx, date: p.datetime, clv:p.clvl==undefined ? 0 : p.clvl.toFixed(1) }))
  clvs.value.forEach((p, i) => {
    const x = dalist.value[p.idx + 1] // prev day
    const y = dalist.value[p.idx]     // this morning
    p.note = y!=undefined ? y.note : 'no note'
    p.food = x!=undefined ? x.food : 'no food'
    p.fdtm = x!=undefined ? x.datetimeOrig : 'no food time'
    p.gptm = x!=undefined ? ((new Date(p.date).getTime() - new Date(x.datetimeOrig).getTime()) / 1000 / 60 / 60).toFixed(1) : 'no time gap'
    // console.log(p.idx, p.clv, p.date, x.datetime, p.food)
  })
  // this.a1cData = gluSections.value.map(p => (p.eag * 0.0555).toFixed(1))
  // this.a1cLabels = gluSections.value.map(p => p.dat)
  emitter.emit('open-ChartProxy')
}
function showAllCharts () {
  console.log('-fn-showAllCharts', dalist.value)
  clvs.value = []
  // dalist.value.filter(x => x.food == null).forEach((p, i) => clvs.value.push({ idx: p.idx, date: p.datetime, clv:p.clvl==undefined ? 0 : p.clvl.toFixed(1) }))
  dalist.value.filter(x => x.food == null).forEach((p, i) => clvs.value.push({ idx: p.idx, date: p.datetime, note: p.note, clv:p.clvl==undefined ? 0 : p.clvl.toFixed(1) }))
  clvs.value.forEach((p, i) => {
    const x = dalist.value[p.idx + 1] // prev day
    const y = dalist.value[p.idx]     // this morning
    // p.note = y!=undefined ? y.note : 'no note'
    p.food = x!=undefined ? x.food : 'no food'
    p.fdtm = x!=undefined ? x.datetimeOrig : 'no food time'
    p.gptm = x!=undefined ? ((new Date(p.date).getTime() - new Date(x.datetimeOrig).getTime()) / 1000 / 60 / 60).toFixed(1) : 'no time gap'
    // console.log(p.idx, p.clv, p.date, x.datetime, p.food)
  })
  // this.a1cData = gluSections.value.map(p => (p.eag * 0.0555).toFixed(1))
  // this.a1cLabels = gluSections.value.map(p => p.dat)
  emitter.emit('open-ChartProxy')
}
function getGluSections () {
  // console.log('-fn-getGluSection')
  let gluSecs = []
  gludata.value = dalist.value.map(x => { return { dat:x.datetimeOrig.substring(0, 10), date:x.datetimeOrig.substring(0, 13), eag:x.eag, a1p:x.a1cp, a1c:x.a1c, glu:x.glu } })
  let gluda = gludata.value.reverse()
  let gludaLen = gluda.length
  let prev = gluda[0]
  let x = null
  for (let i=0; i<gludaLen; ++i) {
    x = gluda[i]
    if (x.a1p != prev.a1p) {
      prev.bgnDate = prev.dat
      prev.endDate = x.dat
      prev.day = getDateGap(prev.dat, x.dat)
      prev.lbl = prev.dat + ' ~ ' + x.dat
      prev.exg = prev.eag + ' ~ ' + x.eag
      prev.sbl = prev.dat.substring(5, 10) + ' ~ ' + x.dat.substring(5, 10)
      gluSecs.push(prev)
      prev = x
    }
  }
  x.day = getDateGap(prev.dat, x.dat)
  x.bgnDate = prev.dat.substring(0, 10)
  x.endDate = x.dat.substring(0, 10)
  x.lbl = prev.dat + ' ~ ' + x.dat
  x.exg = prev.eag + ' ~ ' + x.eag
  x.sbl = prev.dat.substring(5, 10) + ' ~ ' + x.dat.substring(5, 10)
  x.dat = prev.dat.substring(0, 10)
  x.eag1 = prev.eag
  gluSecs.push(x)
  // console.log('-ck-gluSections', gluSections.value.map(p => p.dat), gludaLen, gluSections.value.length, '2022-03-13' === this.today())
  // console.table(gluSecs[0])
  // console.table(gluSecs[1])
  // this.checkSecs(gluSecs)
  setGluSections(gluSecs)
}
function checkSecs (gluSecs) {
  console.log('-fn-checkSecs')
  gluSecs.forEach(p => { console.table(p) })
}
function tolerance6_0 (cur, prv) {
  // if (x.a1p == prv.a1p || x.day <= 1) {
  // if (x.a1p == prv.a1p || x.day <= 2) {   // skip for 2 days
  // if ((x.a1p == 6.0 && prv.a1p == 5.9) || (x.a1p == prv.a1p || x.day <= 2)) {   // skip for 2 days
  // return ((cur.a1p == 6.0 && prv.a1p == 5.9) || (cur.a1p == prv.a1p || cur.day <= 2)) // skip for 1 day2
  return ((cur.a1p == 6.0 && prv.a1p == 5.9) || (cur.a1p == prv.a1p || cur.day <= 1)) // skip for 1 day
}
function setGluSections (gluSecs) {
  gluSections.value = []
  let prv = gluSecs[0]
  let len = gluSecs.length
  gluSections.value.push(prv)
  for (let i=1; i<len; ++i) {
    let x = gluSecs[i]
    if (tolerance6_0(x, prv)) {
      let pprv = gluSections.value.pop()
      pprv.endDate = x.endDate
      pprv.lbl = pprv.bgnDate + ' ~ ' + pprv.endDate
      pprv.sbl = pprv.bgnDate.substring(5, 10) + ' ~ ' + pprv.endDate.substring(5, 10)
      pprv.day += x.day
      pprv.exg = pprv.eag + ' ~ ' + x.eag // use the last eag instead of the first
      gluSections.value.push(pprv)
      continue
    } else if (x.a1p != prv.a1p) {
      gluSections.value.push(x)
      prv = JSON.parse(JSON.stringify(x))
    }
  }
  checkSecsTable(gluSections.value)
  calcXis(gluSections.value)
}
function calcXis (gluda) {
  const widx = window.innerWidth
  const days = gluda.map(p => p.day).reduce((a, b) => a + b, 0)
  // console.log(`total days=${days}`, window.innerWidth)
  let w = []
  let t = []
  gluda.forEach(p => { w.push(p.a1p + ' | ' + p.day + ' | ' + (widx * p.day / days).toFixed(0)) })
  gluda.forEach(p => { t.push(widx * p.day / days) })
  // console.table(w)
  // console.table(t)
  // console.log('total width =', t.reduce((a, b) => parseFloat(a) + parseFloat(b), 0))
  const rev = t.reverse()
  let xis = [rev[0]/2]
  const fsz = 8
  const delta = 5
  for (let i=1; i<rev.length; i++) {
    const p = xis[i - 1]
    const h = rev[i - 1] / 2
    const x = rev[i] / 2
    // console.log(p, h, x)
    xis.push(p + h + x - fsz + delta)
  }
  xlabel.value = xis.reverse()
  // console.table(xlabel.value)
}
function checkSecsTable (gluda) {
  let x = []
  gluda.forEach(p => { x.push(p.a1p + ' | ' + p.exg + ' | ' + p.lbl + ' | ' + p.day) })
  // console.table(x)
}
function calcEAG_A1C_A1Cp (row) {
  const dt = new Date(row.datetime.replace(' ', 'T'))
  const p90dt = prev90date(dt)
  // console.log(`-fn-geteAG from ${dt} to ${p90dt}`, (new Date(dt).getTime() - p90dt)/24/60/60/1000)
  const glucoses = dats.value.filter(a => (new Date(a.datetime.replace(' ', 'T'))).getTime() >= p90dt).map(x => x.fasting)
  if (glucoses.length === 0) {
    return
  }
  const eAG90 = glucoses.reduce((a, b) => a + b) / glucoses.length  // 90 days average = eAG
  const eAGml = (eAG90 / 18.015).toFixed(1) // 中国标准 mmol/L
  const a1cp = ((eAG90 + 46.7) / 28.7).toFixed(1)
  const a1c = (10.929 * (a1cp - 2.15)).toFixed(1)
  row.glu = eAGml // 中国标准 mmol/L
  row.clvl = row.fasting / 18.015
  // console.log(`row.clvl = ${row.clvl}`)
  row.eag = eAG90.toFixed(1) // mg/dL
  row.a1cp = a1cp
  row.a1c = a1c
  row.eagX = row.eag + ' (mg/dL), ' + eAGml + ' (mmol/L)'
  // row.a1cpX = a1cp + '%'
  row.a1cpX = a1cp
  row.a1cX = a1c + ' (mmol/mol)'
}
function prev90date (dt) {
  const dt90 = dt.getTime() - 90 * 24 * 60 * 60 * 1000
  // console.log(`in dt=${this.yyyymmddHHMM(dt)}, ${this.yyyymmddHHMM(new Date(dt90))}`)
  return dt90
}
function getValue (col, row) {
  calcEAG_A1C_A1Cp(row)
  if (col.name === 'fasting') {
    // return  col.value
    if (col.value / 18.015 < 10) return  col.value + ' / ' + (col.value / 18.015).toFixed(1)
    else return col.value + ' / ' + (col.value / 18.015).toFixed(0)
  } else if (col.name === 'food' && col.value == null) {
    // return row.a1cp + ' / ' + row.a1c + ' / ' + row.eag + ' / ' + (row.eag / 18.015).toFixed(1)
    // return row.a1cp + ' / ' + row.a1c + ' / ' + row.eag + ' / ' + row.glu
    // return '90天平均 ' + row.eag + ' (mg/dL) / ' + row.glu + ' (中国标准)'
    // return '过去90天加权平均 ' + row.eag + ' (mg/dL) / ' + row.glu + ' (中国标准)'
    // return '90天平均 ' + row.eag + ' (mg/dL) / ' + row.glu + ' (mmol/L)'
    return '90天平均 ' + row.eag + ' / ' + row.glu
  }
  return col.value
}
// function getValue (col, row) {
//   if (col.name === 'fasting') {
//     if (col.value / 18 < 10) return  col.value + '~' + (col.value / 18).toFixed(1)
//     else return col.value + '~' + (col.value / 18).toFixed(0)
//   } else if (col.name === 'food' && col.value == null) {
//     // return row.a1cp + ' / ' + row.a1c + ' / ' + row.eag + ' / ' + (row.eag / 18.015).toFixed(1)
//     return row.a1cp + ' / ' + row.a1c + ' / ' + row.eag + ' / ' + row.glu
//   }
//   return col.value
// }
function getStyle (col) {
  if (col === 'datetime') return "width:176px;white-space:nowrap;"
  // else if (col === 'fasting') return "max-width:0px"
  else if (col === 'drink') return "width:90px"
  else if (col === 'week') return "max-width:20px"
  else return "white-space:nowrap"
}
function getClass (col, row) {
  // console.log(`-CK-row.id = ${row.id} clickedIex = ${clickedIdx.value}`)
  let bgc = row.id == lastClickedRow.value.row.id ? 'bg-indigo-9 ' : ''
  if (col === 'datetime') return bgc + 'cursor-pointer no-wrap;text-center'
  else if (col === 'week') return bgc + 'text-center text-no-wrap'
  else if (col === 'food' && row.food == null) return bgc + 'text-cyan-2 text-bold text-body1 cursor-pointer'
  else if (col === 'food' || col === 'datetime') return bgc + 'text-left text-no-wrap cursor-pointer'
  else if (col === 'drink' || col === 'fruit' || col === 'a1cp') return bgc + 'text-center text-no-wrap'
  else if (col === 'fasting' && between(row.fasting,   0, 101) && isFasting(row)) return bgc + 'text-center text-green-9'
  else if (col === 'fasting' && between(row.fasting, 100, 126) && isFasting(row)) return bgc + 'text-center text-green-7'
  else if (col === 'fasting' && between(row.fasting, 125, 141) && isFasting(row)) return bgc + 'text-center text-green-5'
  else if (col === 'fasting' && between(row.fasting, 140, 156) && isFasting(row)) return bgc + 'text-center text-blue'
  else if (col === 'fasting' && between(row.fasting, 155, 999) && isFasting(row)) return bgc + 'text-center text-pink-4'
  else if (col === 'fasting' && between(row.fasting,   0, 141) && noFasting(row)) return bgc + 'text-center text-green'
  else if (col === 'fasting' && between(row.fasting, 140, 201) && noFasting(row)) return bgc + 'text-center text-amber'
  else if (col === 'fasting' && between(row.fasting, 200, 999) && noFasting(row)) return bgc + 'text-center text-pink-5'
  else return 'text-right'
}
function isFasting (row) {
  // return (row.drink === null && row.food === null && row.fruit === null)
  return (row.drink === null && row.food === null)
}
function noFasting (row) {
  // return (row.drink != null || row.food != null || row.fruit != null)
  return (row.drink != null || row.food != null)
}
function showExpend (col, p) {
  console.log(`%c-fn-showExpand col=${col} row.id=${p.row.id}, lastRowId=${lastClickedRow.value.row.id}`, 'color: red;font-size:18px')
  if (isDesk) return showExpendDesk(col, p)
  if (col == 'datetime') {
    lastClickedRow.value.expand = false
    return showDar(p.row, 'show')
  } else if (col == 'fasting') {
    lastClickedRow.value.expand = false
    return showDar(p.row, 'upd')
  } else if (col == 'a1cp') {
    lastClickedRow.value.expand = false
    return showDar(p.row, 'add')
  }
}
function showExpendDesk (col, p) {
  console.log(`%c-fn-showExpand col=${col} row.id=${p.row.id}, lastRowId=${lastClickedRow.value.row.id}`, 'color: red;font-size:18px')
  if (col == 'datetime') {
    lastClickedRow.value.expand = false
    return showDar(p.row, 'add')
  }
  if (lastClickedRow.value.id === 0 || p.row.id === lastClickedRow.value.row.id) {
    p.expand = !p.expand
    clickedIdx.value = 0
    return
  }
  lastClickedRow.value.expand = false
  lastClickedRow.value = p
  // clickedIdx.value = dats.value.map(x => x.id).indexOf(p.row.id) % itemsPerPageDesk
  clickedIdx.value = p.pageIndex
  // console.log('-CK-clickedIdx', clickedIdx.value)
  p.expand=!p.expand
}
function showDar (row, act) {
  console.log(`-fn-showDar act=${act}`, row)
  const clone = JSON.parse(JSON.stringify(row))
  // clone.datetime = row.datetimeOrig
  // this.$refs.gludar.openIt(clone)
  if (clone.note != null) clone.note = clone.note.replace(/<br \/>/g, '\n')
  emitter.emit('open-gludar', clone, act)
}
function getList () {
  const path = process.env.API + '/glucosecheck/getList'
  gaxios(path)
}
function setList (da) {
  // console.log('-fn-setList', da.lst.filter(p => p.fastingSearch==='fasting'), da)
  console.log('-fn-setList', da)
  // da.lst.forEach(p => { if (p.note !== null) p.note = p.note.replace(/\n/g, '<br />'); p.week = p.datetime.chwk2() })
  da.lst.forEach(p => { if (p.note !== null) p.note = p.note.replace(/\n/g, '<br />'); p.week = '(' + p.datetime.chwk1() + ')'})
  // da.lst.forEach(p => { if (p.note !== null) p.note = p.note.replace(/\n/g, '<br />'); p.week = p.datetime.chwk3() })
  dats.value = da.lst
  emitter.emit('dats', dats.value)
  // console.log('-dalist:', dalist.value)
  // setGluData()
}
function setGluData () {
  const year = today().substring(0, 4)
  // console.log(`-fn-setGluData year=${year}`, palist.value)
  dalist.value.forEach((p, i) => {
    calcEAG_A1C_A1Cp(p)
    p.datetimeOrig = p.datetime
    p.idx = i
    if (p.datetime.indexOf(year) >= 0) {
      appendWeekDay(p)
    }
  })
  getGluSections()
  // console.log('-ck-glucoseLevel', this.glucoseLevel)
}
function appendWeekDay (row) {
  // const wday = this.getDay1(row.datetime)
  const wday = row.datetime.chwk1()
  row.datetimeOrig = row.datetime
  row.datetime = row.datetime.substring(0, 10) === today() ? '今 天 ' + row.datetime.substring(11) : row.datetime.substring(5)
  row.datetime = row.datetime + ' (' + wday + ')'
  // console.log('-ck-appendWeekDay', row)
}
// function appendWeekDay (rw) {
//   const wday = rw.datetime.chwk1()
//   row.value.datetimeOrig = rw.datetime
//   row.value.datetime = rw.datetime.substring(0, 10) === today() ? '今 天 ' + rw.datetime.substring(11) : rw.datetime.substring(5)
//   row.value.datetime = rw.datetime + ' (' + wday + ')'
//   // console.log('-ck-appendWeekDay', row)
// }
function getIdx (row) {
  for (let i=0; i<dats.value.length; i++) {
    if (dats.value[i].datetimeOrig <= row.datetime) {
      // console.log('-ck-datetime compare', dats.value[i].datetimeOrig, row.datetime, i)
      return i
    }
  }
}
</script>

<template>
<!-- <div class="q-px-xs" :class="{ fixed: clickedIdx < 8 }"> -->
<div class="q-px-xs" :class="{ fixed: clickedIdx>0 && dalist[clickedIdx].details.length<99 }">
  <q-table class="sh-sticky-header-table" v-model:rows="dalist" :columns="columns" dense :hide-header="isIM"
    :grid=false :visible-columns="isDesk ? visibleColumnsDesk : visibleColumnsFone" :style="{ width:(screenwidth-4)+'px' }" 
    row-key="id" :separator="separator" wrap-cells :hide-pagination="false" 
    :pagination="isDesk ? { rowsPerPage: 23 } : { rowsPerPage: 13 }"
  >
  <!-- <template v-slot:header="props">
    <q-tr :props="props">
      <q-th v-for="col in props.cols" :key="col.name" :props="props" class="text-yellow text-center">{{ col.label }}</q-th>
    </q-tr>
  </template> -->

  <template v-slot:body="p">
    <q-tr :props="p">
      <q-td v-for="(col, i) in p.cols" :key="col" @click="showDar(col.name, p.row)" :style="getStyle(col.name)" class="cursor-pointer" :class="getClass(col.name, p.row)">{{ col.value }}</q-td>
      <!-- <q-td v-for="(col, i) in p.cols" :key="col" @click="showDar(col.name, p.row)" :style="getStyle(col.name)" class="cursor-pointer" :class="getClass(col.name, p.row)">{{ getVal(p.row, i) }}</q-td> -->
    </q-tr>
  </template>
  </q-table>
  <pfdar />
  <InfoDisplay />
  <ImgDisplay />
</div>
<div v-if="daysum" class="q-px-md float-right"><q-btn round icon="edit" color="red" glossy @click="setDaySum"/></div>
</template>
<script setup>
import { ref, computed } from 'vue'
import emitter from 'tiny-emitter/instance'
import pfdar from './pfdar'
import InfoDisplay from '../src/components/InfoDisplay'
import ImgDisplay from '../src/components/ImgDisplay'
import { libFunctions } from 'src/composables/libFunctions'
import { axiosFunctions } from 'src/composables/axiosFunctions'
import { dayFunctions } from 'src/composables/dayFunctions'
const { isIM, isDesk, buildApp, palist, dalist, screenwidth } = libFunctions()
const { chwk1, chwk3, yyyymmddHHMM } = dayFunctions()
const { gaxios } = axiosFunctions()

//== data 
const cols = ref(['ID'])
var lastClickedP = { key:0, pageIndex:0}
var clickedRow = {}
const clickedIdx = ref(0)
var separator = 'cell'
// var showCol = false
const visibleColumnsDesk = [col(1).name, col(2).name, col(3).name, col(4).name]
const visibleColumnsFone = [col(1).name, col(2).name, col(3).name, col(4).name]
// const visibleColumnsFone = [col(3).name]
const columns = ref([col(0), col(1), col(2), col(3)])
// const columns = ref([ col(1), col(2) ])
const fabOpen = ref(false)
const daylst = ref([])
const dayslst = ref([])
const daysum = ref(true)

const compDaySum = computed(() => { return daysum.value })

//== main
console.log('-ST-pflist')
buildApp ('胰流报告(Pancreatic Fluid CC)')
getList(screenwidth/13)
emitter.emit('items-per-page', isIM ? 13 : 22)

emitter.on('search', (searchQuery) => { searchQuery = searchQuery })
emitter.on('pfcheck-getList', (da) => setList(da))
emitter.on('pfcheck-add', (x) => addedRow(x.row))
emitter.on('pfcheck-upd', (x) => updedRow(x.row))
emitter.on('pfcheck-del', () => deledRow())

function setDaySum () {
  console.log(`-fn-setDaySum daysum=${daysum.value}`)
  daysum.value = !daysum.value
  if (compDaySum.value) emitter.emit('dats', daylst.value)
  else emitter.emit('dats', dayslst.value)
}
function col (idx) {
  const cols = [
    { required: false, label: '身份', align: 'left', name: 'id', field: 'id', sortable: true, headerClasses: 'text-white text-no-wrap' },
    { required: false, label: 'Check Time', align: 'center', name: 'datetime', field: 'datetime', headerStyle: 'width:1%', headerClasses: 'text-right text-no-wrap' },
    { required: false, label: 'Count', align: 'center', name: 'cnt', field: 'cnt', headerStyle: 'width:4%' },
    { required: true, label: 'Volumn', align: 'right', name: 'vol', field: 'vol', headerStyle: 'width:5%' },
    { required: false, label: 'Week', align: 'center', name: 'week', field: 'week', headerStyle: 'width:4%' },
    { required: false, label: 'note', align: 'left', name: 'note', field: 'note' },
    // { required: false, label: '链接', align: 'left', name: 'lnk', field: 'lnk', sortable: true },
    // { required: false, label: '日期', align: 'left', name: 'dtwk', field: 'dtwk', headerStyle: 'max-width:370px' },
  ]
  return cols[idx]
}
// const compFixed = computed(() => {
//   return lastClickedP.pageIndex < 9 ? "fixed" : "none"
// })
function getVal (row, idx) {
  // if (idx === 4) console.log(`%c-CK-fn-getVal name=${row[col(idx).name]} clkIdx=${clickedIdx.value}`, 'color:red; font-size:16px')
  // console.log(`%c-CK-fn-getVal name=${row[col(idx).name]}`, 'color:red; font-size:16px')
  return row[col(idx).name]
}
function ishow (row, idx) {
  // if (idx === 4) console.log(`%c-CK-fn-getVal name=${palist.value[clickedIdx.value].details} clkIdx=${clickedIdx.value}`, 'color:red; font-size:16px')
  const cont = getVal(row, idx)
  if (/<img/.test(cont)) return 'img'
  else return cont != null && cont != ''
}
function openImg (row, idx) {
  // const imgstr = '<img src="http://devx/docs/fishing/SaltwaterRegistry.nj.gov.png" class="q-pt-xl rotate-90" style="margin:50px 0 0 -55px">'
  const imgx = getVal(row,idx)
  console.log(`-fn-getVal idx=${idx}`, imgx)
  // const imgstr = '<img src="http://devx/docs/fishing/SaltwaterRegistry.nj.gov.png" class="rotate-90" style="margin:80px 0 0 -80px">'
  const imgstr = imgx + ':class="{ \'rotate-90\':is90 }" style="margin:80px 0 0 -80px">'
  const isMax = true
  return emitter.emit('open-ImgDisplay', 'Saltwater Registry', imgstr, isMax)
}
function addedRow (row) {
  console.log(`-fn-addedRow row.datetime=${row.datetime}`, row)
  row.color = 'bg-lime-10 text-white'
  row.week = '(' + row.datetime.chwk1() + ')'
  row.dtwk = isDesk ? row.datetime + ' (' + row.datetime.chwk3() + ')' : row.datetime  + ' (' + row.datetime.chwk1() + ')'
  // dalist.value.unshift(row)
  let rowIdx = dalist.value.findIndex(p => p.datetime <= row.datetime)
  dalist.value.splice(rowIdx, 0, row)
}
function updedRow (row) {
  let rowIdx = dalist.value.map(p => p.id).indexOf(row.id)
  row.color = 'bg-indigo-10 text-cyan-2'
  row.week = '(' + row.datetime.chwk1() + ')'
  // row.dtwk = row.datetime + ' (' + isDesk ? row.datetime.chwk3() : row.datetime.chwk1() + ')'
  row.dtwk = isDesk ? row.datetime + ' (' + row.datetime.chwk3() + ')' : row.datetime  + ' (' + row.datetime.chwk1() + ')'
  // console.log(`%c-fn-updedRow rowIdx=${rowIdx}`, 'color:red;font-size:16px', row)
  dalist.value.splice(rowIdx, 1, row)
}
function deledRow () {
  console.log('user confirmed to delete row', clickedRow)
  dalist.value.splice(clickedIdx.value, 1)
}
function getStyle (coln) {
  // console.log(`-fn-getStyle coln=${coln}`)
  if (coln === col(1).name)      return 'min-width:120px;max-width:120px'
  else if (coln === col(2).name) return 'min-width:70px;max-width:70px'
  else if (coln === col(3).name) return 'min-width:50px;max-width:50px'
  else if (coln === col(4).name) return 'min-width:70px;max-width:70px'
}
function getClass (coln, row) {
  // console.log(`-fn-getClass coln=${coln}`, row)
  const rowColor = row.color == null ? getClickedBG(row) : row.color
  // return 'text-no-wrap text-left ' + rowColor
  // if (row.color != null) rowColor = row.color
  if (coln === col(1).name) return 'text-no-wrap text-center ' + rowColor
  else if (coln === col(2).name) return 'text-no-wrap text-center ' + rowColor
  else if (coln === col(3).name) return 'text-no-wrap text-center ' + rowColor
  else if (coln === col(4).name) return 'text-no-wrap text-right ' + rowColor
}
function getClickedBG (row) { 
  return (row.id === clickedRow.id ? ' bg-purple text-yellow-2' : '')
}
function expandRow (p) {
  if (lastClickedP.key === p.key) {
    p.expand = !p.expand
    return
  } else if (lastClickedP.key > 0) {
    lastClickedP.expand = false
  }
  lastClickedP = p
  p.expand = !p.expand
  clickedRow = p.row
  // console.log(`-fn-getRowIdx from dats for row.id=${clickedRow.id}`)
  const id = clickedRow.id
  // const ids = dalist.value.map(p => { return p == undefined ? 0 : p.id })
  // clickedIdx.value = ids.indexOf(id)
  clickedIdx.value = p.pageIndex
  console.log(`-fn-expendRow clickedIdx=${lastClickedP.pageIndex}`, p)
}
function showDar (coln, row) {
  const clone = JSON.parse(JSON.stringify(row))
  let act = coln == 'vol' ? 'add' : 'upd'

  if (act == 'add') clone.datetime = yyyymmddHHMM(new Date())
  console.log(`coln=${coln}`, clone)
  emitter.emit('open-pfdar', clone, act)
}
function getList (swProp) {
  const path = process.env.API + `/pfcheck/getList/${swProp}`
// function getList () {
  // const path = process.env.API + `/pfcheck/getList`
  gaxios(path)
}
function setList (da) {
  console.log(`-fn-setList numchecks=${da.lst.length}`, da)
  da.lst.forEach(p => { p.week = '(' + p.datetime.chwk1() + ')'; p.cnt = 1 })
  // da.lst.forEach(p => { p.dtwk = p.date + (' (' + isDesk ? p.date.chwk3() : pdate.chwk1() + ')') })
  // da.lst.forEach(p => { p.dtwk = isDesk ? p.datetime + ' (' + p.datetime.chwk3() + ')' : p.datetime  + ' (' + p.datetime.chwk1() + ')' })

  let pplst = da.lst.filter(p => p.vol % 5 == 1)
  console.log('pplst', pplst)
  let x = pplst.shift()
  let daylst = [{ id: x.id, datetime:x.datetime, week:x.week, cnt: x.cnt, vol: x.vol }]
  pplst.forEach(p => {
  //   if (daylst.length === 0) daylst.push({ datetime:p.datetime, week:p.week, vol:p.vol })
    let lastelm = daylst[daylst.length - 1]
    if (p.datetime.substring(0, 10) == lastelm.datetime.substring(0, 10)) {
      lastelm.vol += p.vol
      lastelm.cnt += 1
    } else {
      daylst.push({ id: p.id, datetime: p.datetime, week:p.week, cnt: 1, vol: p.vol })
    }
  })
  daylst.value = daylst
  dayslst.value = da.lst
  if (compDaySum.value) emitter.emit('dats', daylst)
  else emitter.emit('dats', da.lst)
  // emitter.emit('dats', da.lst)
  // console.log(`palist:`, palist.value)
  // console.log(`dalist:`, dalist.value)
  // emitter.emit('num-items', da.lst.length)
}
</script>

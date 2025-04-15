<template>
<div class="q-px-xs" style="display:grid;place-items:center;height:93vh">
<div class="{ fixed: clickedIdx < 8 }">
  <q-table class="sh-sticky-header-table" v-model:rows="palist" :columns="columns" dense :hide-header="isIM"
    :grid=false :visible-columns="isDesk ? visibleColumnsDesk : visibleColumnsFone" :style="{ width:(screenwidth-4)+'px' }" 
    row-key="id" :separator="separator" :showCol="showCol" wrap-cells hide-pagination
    :pagination="isDesk ? { rowsPerPage: 23 } : { rowsPerPage: 13 }"
  >
  <template v-slot:header="props">
    <q-tr :props="props">
      <q-th v-for="col in props.cols" :key="col.name" :props="props" class="text-yellow text-center">{{ col.label }}</q-th>
    </q-tr>
  </template>

  <template v-slot:body="p">
    <q-tr :props="p">
      <q-td v-for="col in p.cols" :key="col" @click="expandRow(p)" :style="getStyle(col.name)" class="cursor-pointer" 
        :class="getClass(col.name, p.row)">{{ col.value }}</q-td>
    </q-tr>
    <q-tr v-show="p.expand" :props="p">
      <q-td class="bg-cyan-8" :colspan="isDesk ? 3 : 2">
        <table :style="isDesk ? {margin:'-4px 0 -5px -17px'} : {margin:'-4px 0px 0 -17px' }" style="width:109%">
          <tr v-if="ishow(p.row,2)">
            <td class="bg-teal-10 text-center" colspan="2"><b style="font-size:32px">{{ getVal(p.row,6)}}</b>
              <q-fab v-model="fabOpen" direction="down" class="q-ml-md">
                <q-btn round glossy icon="delete"     size="16px" color="red-10"    @click="showDar(p.row, 'del')" />
                <q-btn round glossy icon="update"     size="16px" color="indigo-10" @click="showDar(p.row, 'upd')" />
                <q-btn round glossy icon="add_circle" size="16px" color="green-10"  @click="showDar(p.row, 'add')" />
              </q-fab>
            </td>
          </tr>
          <tr v-if="ishow(p.row,3)=='img'">
            <td class="text-center">{{col(3).label}}</td>
            <td><q-btn flat label="click to show image" class="text-h6" @click="openImg(p.row, 3)" /></td>
          </tr>
          <tr v-else-if="ishow(p.row,3)">
            <td class="text-center text-h4">{{col(3).label}}</td>
            <td class="bg-teal-9" v-html="getVal(p.row,3)" :style="{ minWidth:isIM ? '300px' : '337px', maxWidth:isIM ? '300px' : '337px' }" />
          </tr>
          <tr v-if="ishow(p.row,4)">
            <td class="text-center">{{col(4).label}}</td>
            <td class="bg-teal-8" v-html="getVal(p.row,4)" />
          </tr>
          <tr v-if="ishow(p.row,5)">
            <td class="text-center">{{col(5).label}}</td>
            <td class="bg-teal-8" v-html="getVal(p.row,5)" style="max-width:200px" />
          </tr>
          <!-- <tr v-if="isIM"><td style="min-width:52px">{{col(1).label}}</td><td class="bg-teal-9">{{ getVal(p.row,1) }} {{ getDay(getVal(p.row,1)) }}</td></tr> -->
        </table>
      </q-td>
    </q-tr>
  </template>
  </q-table>
  <div class="row">
    <q-option-group v-model="separator" inline class="text-h6 text-white" :options="[
        { label: 'Horizontal', value: 'horizontal' },
        { label: 'Vertical', value: 'vertical' },
        { label: 'Cell', value: 'cell' },
        { label: 'None', value: 'none' }
        ]"
    />
    <q-option-group v-model="showCol" inline class="text-h6 text-white" :options="[
        { label: '信息', value: col(2).name },
        { label: '详情', value: col(3).name },
        { label: '所有', value: null }
      ]"
    />
  </div>
  <medar />
  <InfoDisplay />
  <ImgDisplay />
</div>
</div>
</template>
<script setup>
import { ref, computed } from 'vue'
import emitter from 'tiny-emitter/instance'
import medar from './medar'
import InfoDisplay from '../src/components/InfoDisplay'
import ImgDisplay from '../src/components/ImgDisplay'
import { libFunctions } from 'src/composables/libFunctions'
import { axiosFunctions } from 'src/composables/axiosFunctions'
import { dayFunctions } from 'src/composables/dayFunctions'
const { isIM, isDesk, buildApp, palist, dalist, screenwidth } = libFunctions()
const { chwk1, chwk3 } = dayFunctions()
const { gaxios } = axiosFunctions()

//== data 
const cols = ref(['ID'])
var lastClickedP = { key:0, pageIndex:0}
var clickedRow = {}
const clickedIdx = ref(0)
var separator = 'cell'
var showCol = false
const visibleColumnsDesk = [col(1).name, col(2).name, col(3).name]
const visibleColumnsFone = [col(3).name]
const columns = ref([col(0), col(1), col(2), col(3), col(4) ])
const fabOpen = ref(false)
const dalst = ref([])

//== main
console.log('-ST-melist')
buildApp ('备忘录', 'memo')
getList(screenwidth/13)
emitter.emit('items-per-page', isIM ? 13 : 22)

emitter.on('search', (searchQuery) => { searchQuery = searchQuery })
emitter.on('memo-getList', (da) => setList(da))
emitter.on('memo-add', (x) => addedRow(x.row))
emitter.on('memo-upd', (x) => updedRow(x.row))
emitter.on('memo-del', () => deledRow())

function col (idx) {
  const cols = [
    { required: false, label: '身份', align: 'left', name: 'id', field: 'id', sortable: true, headerClasses: 'text-white text-no-wrap' },
    { required: false, label: '日期 时间', align: 'left', name: 'date', field: 'date', sortable: true, headerStyle: 'max-width:110px', headerClasses: 'text-right text-no-wrap' },
    { required: false, label: '周', align: 'center', name: 'week', field: 'week', headerStyle: 'max-width:30px' },
    { required: false, label: '内容', align: 'left', name: 'tag', field: 'tag', sortable: true, headerClasses: 'text-no-wrap ellipsis' },
    { required: false, label: '详情', align: 'left', name: 'details', field: 'details', sortable: true, },
    { required: false, label: '链接', align: 'left', name: 'lnk', field: 'lnk', sortable: true },
    { required: false, label: '日期', align: 'left', name: 'dtwk', field: 'dtwk', headerStyle: 'max-width:370px' },
  ]
  return cols[idx]
}
// const compFixed = computed(() => {
//   return lastClickedP.pageIndex < 9 ? "fixed" : "none"
// })
function getVal (row, idx) {
  // if (idx == 5) console.log(`%c-CKK-fn-getVal name=${row[col(idx).name]}`, 'color:red; font-size:16px')
  return row[col(idx).name]
}
function ishow (row, idx) {
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
  console.log(`-fn-addedRow row.date=${row.date}`, row)
  row.color = 'bg-lime-10 text-white'
  row.week = '(' + row.date.chwk1() + ')'
  row.dtwk = isDesk ? row.date + ' (' + row.date.chwk3() + ')' : row.date  + ' (' + row.date.chwk1() + ')'
  // dalist.value.unshift(row)
  let rowIdx = dalist.value.findIndex(p => p.date <= row.date)
  dalist.value.splice(rowIdx, 0, row)
}
function updedRow (row) {
  let rowIdx = dalist.value.map(p => p.id).indexOf(row.id)
  row.color = 'bg-indigo-10 text-cyan-2'
  row.week = '(' + row.date.chwk1() + ')'
  // row.dtwk = row.date + ' (' + isDesk ? row.date.chwk3() : row.date.chwk1() + ')'
  row.dtwk = isDesk ? row.date + ' (' + row.date.chwk3() + ')' : row.date  + ' (' + row.date.chwk1() + ')'
  // console.log(`%c-fn-updedRow rowIdx=${rowIdx}`, 'color:red;font-size:16px', row)
  dalist.value.splice(rowIdx, 1, row)
}
function deledRow () {
  console.log('user confirmed to delete row', clickedRow)
  dalist.value.splice(clickedIdx.value.value, 1)
}
function getStyle (coln) {
  if (coln === col(1).name)      return isDesk ? 'min-width:160px;max-width:100px' : 'min-width:0px;max-width:0px'
  else if (coln === col(2).name) return isDesk ? 'min-width:55px;max-width:55px' : 'min-width:90px;max-width:88px'
  else if (coln === col(3).name) return isDesk ? 'min-width:620px;max-width:620px' : 'min-width:90px;max-width:88px'
}
function getClass (coln, row) {
  const rowColor = row.color == null ? getClickedBG(row) : row.color
  // return 'text-no-wrap text-left ' + rowColor
  // if (row.color != null) rowColor = row.color
  if (coln === col(1).name) return 'text-no-wrap text-center ' + rowColor
  else if (coln === col(2).name) return 'text-no-wrap text-center ' + rowColor
  else if (coln === col(3).name) return 'text-no-wrap text-left ellipsis ' + rowColor
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
function showDar (row, act) {
  if (getVal(row, 4) === 'Golf') {
    const tit = 'Can Not Change Golf Play'
    const msg = 'Please reevise it in expense app'
    emitter.emit('open-InfoDisplay', tit, msg)
    return
  }
  const clone = JSON.parse(JSON.stringify(row))
  emitter.emit('open-medar', clone, act)
}
function getList (swProp) {
  const path = process.env.API + `/memo/getList/${swProp}`
  gaxios(path)
}
function setList (da) {
  console.log('-fn-setList', da)
  da.lst.forEach(p => { p.week = '(' + p.date.chwk1() + ')' })
  // da.lst.forEach(p => { p.dtwk = p.date + (' (' + isDesk ? p.date.chwk3() : pdate.chwk1() + ')') })
  da.lst.forEach(p => { p.dtwk = isDesk ? p.date + ' (' + p.date.chwk3() + ')' : p.date  + ' (' + p.date.chwk1() + ')' })
  dalst.value = da.lst
  emitter.emit('dats', da.lst)
  // emitter.emit('num-items', da.lst.length)
}
</script>

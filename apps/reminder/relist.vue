<template>
<div style="display:grid;place-items:center">
<!-- <div class="q-px-xs" :class="{ fixed: clickedIdx < 8 }"> -->
<q-table class="sh-sticky-header-table" v-model:rows="palist" :columns="columns" dense :hide-header="isIM"
  :grid=false :visible-columns="isDesk ? visibleColumnsDesk : visibleColumnsFone" :style="{height:isIM ? '565px':''}"
  row-key="id" :separator="separator" :showCol="showCol" wrap-cells hide-pagination
  :pagination="isDesk ? { rowsPerPage:rowsPerPageDesk } : { rowsPerPage:rowsPerPageFone }"
>
  <template v-slot:top="props">
    <q-select v-if="isIM"
      v-model="visibleColumnsDesk" multiple borderless dense options-dense
      emit-value map-options
      option-value="name" style="min-width: 60px"
      :display-value="$q.lang.table.columns"
      :options="columns"
    />
    <q-btn v-if="isIM" flat round dense color="accent" :icon="props.inFullscreen ? 'fullscreen_exit' : 'fullscreen'" @click="props.toggleFullscreen" class="q-pr-xs" />
  </template>

  <template v-slot:header="props">
    <q-tr :props="props">
      <q-th v-for="col in props.cols" :key="col.name" :props="props" class="text-yellow text-center">{{ col.label }}</q-th>
    </q-tr>
  </template>

  <template v-slot:body="p">
    <q-tr :props="p">
      <q-td v-for="col in p.cols" :key="col" @click="expandRow(p, col.name)" :style="getStyle(col.name)" :class="getClass(col, p.row)">
        <!-- <span v-if="col.name=='due_date'">{{ col.value.substring(5) }}</span><span v-else>{{ col.value }}</span> -->
        {{ col.value }}
      </q-td>
    </q-tr>
    <q-tr v-show="p.expand" :props="p">
      <q-td class="bg-cyan-8" :colspan="isDesk ? 4 : 3">
        <table style="margin:-7px 0 -6px -16px;width:105%">
          <q-tr><td style="min-width:52px">{{ cols[2].label }}</td>
            <td v-if="/今|明|后/.test(getVal(p.row,2))" class="bg-teal-10">
              <b class="vertical-center text-h5 q-px-xs text-bold">就 是</b>
              <b class="text-red text-h4 text-bold">{{getVal(p.row,2)}}</b>
              <b class="vertical-center q-pl-sm text-bold text-h5">马 上 到 期</b>
              <!-- <q-btn v-if="!/PX|(QG|WX|WW)|Play at/.test(p.row.tag) && !p.row.memo" round glossy icon="edit" @click="showDar(p.row)" class="float-right" /> -->
              <div v-if="p.row.memo==null && p.row.golf==null">
                <q-fab  v-model="fabOpen" flat hide-icon direction="left" color="cyan-2">
                  <q-btn round glossy icon="add_circle" color="green-10" size="16px" @click="showDar(p.row, 'add')" class="q-mr-md" />
                  <q-btn round glossy icon="update"     color="indigo"   size="16px" @click="showDar(p.row, 'upd')" class="q-mr-md" />
                  <q-btn round glossy icon="delete"     color="red-10"   size="16px" @click="delRow(p.row)" class="q-mr-md" />
                </q-fab>
              </div>
            </td>
            <td v-else-if="/过/.test(getVal(p.row,2))" class="bg-teal-10">
              <b class="vertical-top text-h5 q-px-xs text-bold">已 经</b>
              <b class="text-grey text-h4 tet-bold">{{getVal(p.row,2)}}</b>
              <!-- <q-btn round glossy icon="edit" @click="showDar(p.row)" class="float-right" /> -->
              <q-fab  v-model="fabOpen" flat hide-icon direction="right" color="cyan-2">
                <q-btn round glossy icon="delete"     size="16px" color="red-10"    @click="delRow(p.row)" class="q-mx-md" />
                <q-btn round glossy icon="update"     size="16px" color="indigo-10" @click="showDar(p.row, 'upd')" class="q-mr-md" />
                <q-btn round glossy icon="add_circle" size="16px" color="green-10"  @click="showDar(p.row, 'add')" class="q-mr-md" />
              </q-fab>
            </td>
            <td v-else class="bg-teal-10"><b class="vertical-center text-h5 q-px-xs text-bold">还 有</b>
              <b class="text-amber text-h4 text-bold">{{getVal(p.row,2).replace(/^0/,'')}}</b>
              <b class="vertical-center q-pl-xs text-bold text-h5">到 期</b>
              <div v-if="p.row.memo==null && p.row.golf==null" class="float-right">
                <q-fab  v-model="fabOpen" flat hide-icon direction="left" color="cyan-2">
                  <q-btn round glossy icon="delete"     size="16px" color="red-10"    @click="delRow(p.row)" class="q-mx-md" />
                  <q-btn round glossy icon="update"     size="16px" color="indigo-10" @click="showDar(p.row, 'upd')" class="q-mr-md" />
                  <q-btn round glossy icon="add_circle" size="16px" color="green-10"  @click="showDar(p.row, 'add')" class="q-mr-md" />
                </q-fab>
              </div>
            </td>
          </q-tr>
          <q-tr v-if="ishow(p.row,3)"><td style="min-width:52px">{{ cols[3].label }}</td>
            <td class="bg-teal-9">{{ getVal(p.row,3) }}</td>
          </q-tr>
          <q-tr v-if="ishow(p.row,4)"><td style="min-width:52px">{{ cols[4].label }}</td>
            <td class="bg-teal-10" :style="{ minWidth:isDesk ? '730px' : '318px' }">{{getVal(p.row,4)}}<b v-if="getVal(p.row,4)==='Golf'">Game</b>
              <q-btn v-if="/PX|(QG|WX|WW)/.test(p.row.tag)" round glossy icon="list" color="indigo-10" class="float-right" @click="showArt" />
            </td>
          </q-tr>
          <q-tr v-if="ishow(p.row,5)"><td style="min-width:52px">{{ cols[5].label }}</td>
            <td class="bg-teal-9" v-html="getVal(p.row,5)" />
          </q-tr>
          <q-tr v-if="ishow(p.row,6) && getVal(p.row,4) === 'Golf'"><td style="min-width:52px">开球</td>
            <td><b class="text-amber text-h4 text-bold">Tee Time: {{ getVal(p.row,6) }}</b></td>
          </q-tr>
          <q-tr v-else-if="ishow(p.row,6)"><td style="min-width:52px">{{ cols[6].label }}</td>
            <td class="bg-teal-10">下一次是 <b class="text-yellow text-h4 text-bold">{{ parseInt(getVal(p.row,6)) }}</b> 天以后</td>
          </q-tr>
          <q-tr v-if="ishow(p.row,7) && isDesk"><td style="min-width:52px">{{ cols[7].label }}</td><td class="bg-teal-9" v-html="getVal(p.row,7)" /></q-tr>
        </table>
      </q-td>
    </q-tr>
  </template>
</q-table>
<div class="row">
  <q-option-group v-model="separator" inline class="text-h6 text-white" :options = "[
      { label: 'Horizontal', value: 'horizontal' },
      { label: 'Vertical', value: 'vertical' },
      { label: 'Cell', value: 'cell' },
      { label: 'None', value: 'none' },
    ]"
  />
  <q-option-group v-model="showCol" inline class="text-h6 text-white" :options="[
      { label: '信息', value: cols[4].name },
      { label: '详情', value: cols[5].name },
      { label: '所有', value: null },
    ]"
  />
</div>
<redar @del-confirmed="deledRow" @added-row="addedRow" @upded-row="updedRow"/>
<InfoDisplay />
</div>
</template>
<script setup>
import { ref, computed } from 'vue'
import emitter from 'tiny-emitter/instance'
import { libFunctions } from '../src/composables/libFunctions'
import { axiosFunctions } from '../src/composables/axiosFunctions'
import { dayFunctions } from '../src/composables/dayFunctions'
const { chwk1, chwk2, today } = dayFunctions()
const { gaxios } = axiosFunctions()
const { isIM, isDesk, buildApp, palist, $q } = libFunctions()

import redar from './redar.vue'
import InfoDisplay from '../src/components/InfoDisplay'

// const cols = ref(['ID'])
var lastClickedP = { key:0, pageIndex: 0 }
var clickedRow = {}
const clickedIdx = ref(0)
const dats = ref([])
const separator = ref('cell')
const rowsPerPageDesk = ref(22)
const rowsPerPageFone = ref(12)
const showCol = ref(null)
const fabOpen = ref(true)
const cols = [
  { required: false, label: '身份', align: 'left', name: 'id', field: 'id', sortable: true, headerClasses: 'text-white text-no-wrap' },
  { required: true,  label: '时间', align: 'left', name: 'due_date', field: 'due_date', sortable: true, headerStyle: 'max-width:50px', headerClasses: 'text-no-wrap' },
  { required: false, label: '期限', align: 'left', name: 'due_in', field: 'due_in', sortable: true, headerStyle:'max-width:50px', headerClasses:'text-no-wrap text-center' },
  { required: false, label: '内容', align: 'left', name: 'tag', field: 'tag', sortable: true, headerClasses: 'text-no-wrap ellipsis' },
  { required: false, label: '信息', align: 'left', name: 'message',  field: row => row.message, sortable: false },
  { required: false, label: '详情', align: 'left', name: 'details', field: 'details', sortable: true, },
  { required: false, label: '重复', align: 'left', name: 'recursive', field: 'recursive', sortable: true },
  { required: false, label: '链接', align: 'left', name: 'lnk', field: 'lnk', sortable: true }
]
const visibleColumnsDesk = [cols[1].name, cols[2].name, cols[3].name, cols[4].name]
const visibleColumnsFone = [cols[1].name, cols[3].name]
const columns = [cols[0], cols[1], cols[2], cols[3], cols[4], cols[5], cols[6], cols[7]]

console.log('-ST-relist')
buildApp('温馨提示', 'reminder')
emitter.emit('items-per-page', isIM ? rowsPerPageFone.value : rowsPerPageDesk.value)
getList()

//== function section
function showArt () {
  console.log(`-fn-showArt`, clickedRow)
  const tag = clickedRow.tag.split(' ')[0]
  const ymd = clickedRow.due_date.split(' ')[0]
  window.open(`http://prod/arts/${tag}/${ymd}`)
}
function ishow (row, idx) {
  return row[cols[idx].name] != null && row[cols[idx].name] != ''
}
function getVal (row, idx) {
  // if (idx == 5) console.log(`%c-fn-getVal col name colname=${row[cols[idx].name]}`, 'color:red; font-size:14px')
  return row[cols[idx].name]
}
function getRowIdx () {
  const id = clickedRow.id
  for (var i = 0; i < dats.value.length; i++) {
    var p = dats.value[i]
    if (p.id === id) { return i }
  }
  return -1
}
function addedRow (row) {
  row.color = 'orange'
  row.day = row[cols[2].name].chwk2()
  dats.value.unshift(row)
}
function updedRow (row) {
  console.log('-fn-updedRow', getRowIdx(), row)
  dats.value.splice(getRowIdx(), 1, row)
}
function delRow (rw) {
  console.log('-fn-delRow: send row to redar', rw)
  emitter.emit('reminder-del-row', rw)
}
function deledRow () {
  console.log('-fn-delRow user confirmed to delete row', clickedRow)
  dats.value.splice(getRowIdx(), 1)
}
function getStyle (col) {
  if (col === cols[1].name)      return isDesk ? 'min-width:150px;max-width:150px' : 'min-width:53px;max-width:53px'
  else if (col === cols[2].name) return isDesk ? 'min-width:72px;max-width:72px'   : 'min-width:105x;max-width:105px'
  else if (col === cols[3].name) return isDesk ? 'min-width:283px;max-width:283px;font-size:16px' : 'min-width:113px;max-width:113px'
  else if (col === cols[4].name) return 'cursor:grab;' + (isDesk ? 'max-width:285px;min-width:285px;font-size:18px' : 'max-width:160px;min-width:160px')
}
function getClickedBG (row) {
  return (row.id === clickedRow.id ? ' bg-purple text-yellow-2' : '')
}
function getClass (col, row) {
  // console.log('-fn-getClass, row value', row.tag)
  const col2val = row[cols[2].name]
  let rowColor = getClickedBG(row)
  if (col2val === '今 天' && /PX|(PXQG|PXWW|PXWX)/.test(row.tag)) rowColor = 'bg-indigo-10 text-cyan-1'
  else if (col2val === '今 天') rowColor = 'bg-red-10 text-yellow-2'
  else if (col2val === '明 天') rowColor = 'bg-amber-9 text-black'
  else if (col2val === '后 天') rowColor = 'bg-amber-6 text-grey-10'
  if (col.name === cols[1].name) {
    return 'cursor-pointer text-no-wrap text-center ' + rowColor
  } else if (col.name === cols[2].name) {
    return 'cursor-pointer text-no-wrap text-center ' + rowColor
  } else if (col.name === cols[3].name) {
    return 'cursor-pointer text-no-wrap ellipsis ' + rowColor
  } else if (col.name === cols[4].name) {
    return 'text-no-wrap ellipsis ' + rowColor
  }
}
function expandRow (p, col) {
  console.log(`-CK-fn-expandRow colname=${col}`, p.row, p.cols)
  if (col === 'message' && /PX(QG|WW|WX)/.test(p.row.tag)) {
    let tag = p.row.tag.split(' ')[0]
    let ymd = p.row.due_date.split(' ')[0]
    return window.open(`http://69.141.101.98/arts/${tag}/${ymd}`)
  }
  if (lastClickedP.key === p.key) {
    p.expand = !p.expand
    return
  } else if (lastClickedP.key > 0) {
    lastClickedP.expand = false
  }
  lastClickedP = p
  p.expand = !p.expand
  clickedRow = p.row
  clickedIdx.value = p.pageIndex
  console.log(`-CK-fn-expandRow clickedIdx=${clickedIdx.value}`, lastClickedP)
}
function showDar (row, act) {
  console.log(`-fn-showDar act=${act}`, row.memo)
  if (getVal(row, 4) === 'Golf') {
    const tit = 'Can Not Change Golf Play'
    const msg = "Please reevise it in expense app"
    emitter.emit('open-InfoDisplay', tit, msg)
    return
  } else if (row.memo === 1) {
    const tit = 'Can Not Change Memo'
    const msg = "Please reevise it in memo app"
    emitter.emit('open-InfoDisplay', tit, msg)
    return
  }
  const clone = JSON.parse(JSON.stringify(row))
  emitter.emit('open-redar', clone, act)
}
function getList () {
  const path = process.env.API + '/reminder/getList'
  gaxios(path)
}
emitter.on('reminder-getList', (x) => setList(x))
function setList(da) {
  const todayIdx = da.lst.findIndex(p => p.due_date == today() && /PX|(QG|WW|WX)/.test(p.tag))
  let top = da.lst.slice(0, todayIdx)
  top.sort((a, b) => a.due_date < b.due_date ? 1 : -1)
  console.log(`-CK-fn-setList todayIdx=${todayIdx}`, today(), top)
  let end = da.lst.slice(todayIdx)
  dats.value = top
  dats.value = dats.value.concat(end)
  da.lst.forEach(p => p.due_date = p.due_date + ' (' + p.due_date.chwk1() + ')')
  dats.value = da.lst
  emitter.emit('dats', dats.value)
}
</script>

<template>
<div class="q-px-xs" style="margin-top:-30px">
<q-table class="sh-sticky-header-table" v-model:rows="palist" :columns="columns"
  :grid=false :visible-columns="isDesk ? visibleColumnsDesk : visibleColumnsFone"
  row-key="basename" :separator="separator" :showCol="showCol" wrap-cells :pagination="isDesk ? { rowsPerPage: nRow } : { rowsPerPage: 13 }"
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
      <q-th v-for="col in props.cols" :key="col.name" :props="props" class="bg-red-10 text-yellow-1 text-center text-no-wrap">{{ col.label }}</q-th>
    </q-tr>
  </template>

  <template v-slot:body="p">
    <q-tr :props="p">
      <q-td v-for="col in p.cols" :key="col" class="text-no-wrap" @click="expandRow(p, col.name)" :style="getStyle(p, col)" :class="getClass(p, col)">
        {{ col.value>0 ? parseFloat(col.value).toFixed(1) : col.value }}
      </q-td>
    </q-tr>
    <q-tr v-show="p.expand" :props="p">
      <q-td class="bg-cyan-8" colspan="6">
        <table style="width:100%;margin:1px 1px 1px 1px">
          <q-tr>
            <td style="font-size:24px;width:150px">{{ cols[0].label }}</td>
            <td class="bg-teal-9" style="width:560px">{{ getVal(p.row,0) }}</td>
            <td><q-btn glossy round icon="delete" color="red" @click="del(p.row)" /></td></q-tr>
          <q-tr>
            <td style="font-size:24px">{{ cols[5].label }}</td>  <!-- endtime -->
            <td colspan="2">{{ getVal(p.row,5) }}</td>
          </q-tr>
          <q-tr>
            <td style="font-size:24px">{{ cols[4].label }}</td>
            <td colspan="3" class="bg-teal-9 text-white">{{ getVal(p.row,4) }}</td>
          </q-tr>
          <q-tr>
            <td style="font-size:24px">{{ cols[6].label }}</td>
            <td colspan="3" class="bg-teal-10 text-white" style="line-height:1.2;font-size:26px;width:0px">{{ getVal(p.row,6) }}</td>
          </q-tr>
          <q-tr>
            <td style="font-size:24px">视 频 文 件</td>
            <td colspan="2" class="bg-teal-10 text-white" style="line-height:1.2;font-size:26px;width:100px">vlc {{ p.row.filename }}</td>
          </q-tr>
          <q-tr v-if="getVal(p.row,7).length>0"><td colspan="3">{{ cols[7].label }}</td></q-tr>
          <q-tr v-if="getVal(p.row,7).length>0"><td colspan="3" class="bg-teal-9">{{ getVal(p.row,7) }}</td></q-tr>
        </table>
      </q-td>
    </q-tr>
  </template>
</q-table>
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
const { gaxios, paxios } = axiosFunctions()
const { isIM, isDesk, buildApp, palist, $q } = libFunctions()

import InfoDisplay from '../src/components/InfoDisplay'

//======= variables =========
var lastClickedP = { key:0 }
var clickedRow = { id:0 }
const dats = ref([])
const separator = ref('cell')
const showCol = ref(null)
const cols = [
  { required: true, label: '开 播 时 间', align: 'center', name: 'starttime', field: 'starttime', sortable: true, headerStyle: 'min-width:170px' },
  { required: true, label: 'GB', align: 'center', name: 'filesize', field: 'filesize', sortable: true, headerStyle: 'max-width:30px' },
  { required: true, label: '频道', align: 'center', name: 'channum', field: 'channum', sortable: true,  headerStyle: 'max-width:30px' },
  { required: true, label: '分钟', align: 'center', name: 'duration', field: 'duration', sortable: true, headerStyle: 'max-width:30px', },
  { required: true, label: 'DK', align: 'left', name: 'dsk', field: 'dsk', sortable: true, headerStyle: 'max-width:50px', },
  { required: true, label: '电 视 节 目', align: 'left', name: 'title', field: 'title', sortable: true, headerStyle:'max-width:50px', headerClasses: 'ellipsis'},
  { required: false, label: '结 束 时 间', align: 'center', name: 'endtime', field: 'endtime', sortable: true },
  { required: false, label: '节 目 内 容', align: 'center', name: 'description', field: 'description', sortable: true },
  { required: false, label: 'Subtitle', align: 'center', name: 'subtitle', field: 'subtitle', sortable: true },
]
const visibleColumnsDesk = [cols[1].name, cols[2].name, cols[3].name, cols[4].name]
const visibleColumnsFone = [cols[1].name, cols[3].name]
const columns = [cols[0], cols[1], cols[2], cols[3], cols[4], cols[5], cols[6], cols[7]]
// const columns = [cols[0], cols[1], cols[2], cols[3], cols[4]]
const nRow = ref(20)

//======= main =========
emitter.on('tvmanager-getList', (x) => setList(x))
emitter.on('tvmanager-del', (x) => setList(x))
emitter.on('tv-shows-in-hours', (x) => getList(x))
console.log('-ST-tvlist')
buildApp('电视列表', 'tvmanager')
// emitter.emit('items-per-page', isIM ? 12 : `${nRow.value}`)
// emitter.emit('items-per-page', isIM ? 12 : 100)
getList(1)

//======= functions =========
function getList(hours) {
  $q.dialog({
    title: `TV Shows Recorded in ${hours} hours`
  })
  const path = process.env.API + '/tvmanager/getList/' + hours
  gaxios(path)
}
function setList(da) {
  console.log('-fn-setList', da.lst)
  // da.lst.forEach(p => { if (p.duration > 60) { p.duration /= 60; p.duration = p.duration.toFixed(0) }})
  // da.lst.forEach(p => { if (p.duration > 100) { p.duration = (''+p.duration).substring(0, 2) }})
  nRow.value = da.lst.length
  dats.value = da.lst
  emitter.emit('items-per-page', isIM ? 12 : `${nRow.value}`)
  emitter.emit('dats', dats.value)
}
function del (row) {
  console.log('-fn-del', row)
  const path = process.env.API + '/tvmanager/del'
  paxios(path, row)
}
function copyToClipboard (p) {
  p.expand = true
  const row = p.row
  console.log(`-fn-copyToClipboard filename=${row.filename} copy to clipboard only supported pages served over https`)
  const tit = 'File Name and TV Title'
  const msg = '<div class="text-center text-h4 text-lime">' + row.title  + '</div><p><p>vlc ' + row.filename
  emitter.emit('open-InfoDisplay', tit, msg)
  // navigator.permissions.query({ name: "write-on-clipboard" }).then((result) => {
  // if (result.state == "granted" || result.state == "prompt") {
  //   alert("Write access granted!");
  // }
  // });
  // navigator.clipboard.writeText(row.filename);
}
function expandRow (p, col) {
  console.log(`-CK-fn-expandRow col=${col} p.key=${p.key}`, p.row, p.cols)
  if (/duration|dsk/i.test(col) && /.ts/.test(p.key)) return del(p.row)
  else if (col === 'starttime') return copyToClipboard(p)
  else if (col !== 'title') return
  if (lastClickedP.key === p.key) {
    p.expand = !p.expand
    return
  } else {
    lastClickedP.expand = false
  }
  lastClickedP = p
  p.expand = !p.expand
  clickedRow = p.row
}
function getVal (row, idx) {
  // console.log('-fn-getVal col name', typeof(cols[idx]))
  // return row[cols[idx].name].substring(0, 40)
  return row[cols[idx].name]
}
function getStyle (p, col) {
  // console.log(`-fn-getStyle col name p.key=${p.key}`)
  if (p.key < 0) return;
  if (col.name === cols[0].name) return 'cursor:pointer;width:50px'
  if (col.name === cols[1].name) return 'cursor:progress;width:10px'
  if (col.name === cols[2].name) return 'width:10px'
  if (col.name === cols[3].name) return 'cursor:no-drop;width:10px'
  if (col.name === cols[4].name) return 'cursor:no-drop;width:10px'
  // if (col.name === cols[4].name) return 'width:410px'
  // if (col.name === cols[1].name) return 'cursor:wait'
  // else if (col === cols[2].name) return isDesk ? 'min-width:72px;max-width:72px'   : 'min-width:105x;max-width:105px'
  // else if (col === cols[3].name) return isDesk ? 'min-width:283px;max-width:283px;font-size:16px' : 'min-width:113px;max-width:113px'
  // else if (col === cols[4].name) return 'cursor:grab;' + (isDesk ? 'max-width:285px;min-width:285px;font-size:18px' : 'max-width:160px;min-width:160px')
}
// function getClickedBG (row) {
//   return (row.id === clickedRow.id ? ' bg-purple text-yellow-2' : '')
// }
function getClass (p, col) {
  // console.log('-fn-getClass, row value', row.tag)
  if (col.name == cols[0].name) return'text-no-wrap text-center'
  else if (col.name == cols[1].name) return 'text-no-wrap text-right bg-cyan-10'
  else if (col.name == cols[2].name) return 'text-no-wrap text-right bg-cyan-9'
  else if (col.name == cols[3].name) return 'text-no-wrap text-right bg-teal-9'
  else if (col.name == cols[4].name) return 'text-no-wrap text-right bg-teal-10'
  else if (col.name == cols[5].name) return 'text-no-wrap ellipsis cursor-pointer'
  // else if (col.name == cols[3].name) return 'text-no-wrap text-center bg-teal-10 text-yellow'
  else if (col.name == cols[1].name) return 'text-no-wrap text-center'
  else return 'text-no-wrap ellipsis text-left'
}
</script>

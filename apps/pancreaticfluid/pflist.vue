<template>
  <!-- <div class="q-px-xs" :class="{ fixed: clickedIdx < 8 }"> -->
  <!-- <div class="q-px-xs" :class="{ fixed: clickedIdx>0 && dalist[clickedIdx].details.length<99 }"> -->
  <div style="width: 800px">
    <q-table
      class="sh-sticky-header-table"
      v-model:rows="palist"
      :columns="columns"
      dense
      :hide-header="isIM"
      :visible-columns="isDesk ? visibleColumnsDesk : visibleColumnsFone"
      :style="isIM ? { width: '402px' } : {}"
      style="border: 1px solid cyan"
      row-key="id"
      :separator="separator"
      wrap-cells
      :pagination="
        isIM ? { rowsPerPage: rowsPerPageIM } : { rowsPerPage: rowsPerPageDesk }
      "
      hide-pagination
    >
      <!-- <template v-slot:header="props">
    <q-tr :props="props">
      <q-th v-for="col in props.cols" :key="col.name" :props="props" class="text-yellow text-center">{{ col.label }}</q-th>
    </q-tr>
  </template> -->

      <template v-slot:body="p">
        <q-tr :props="p">
          <q-td
            v-for="(col, i) in p.cols"
            :key="col"
            @click="showDar(col.name, p.row)"
            :style="getStyle(col.name)"
            class="cursor-pointer"
            :class="getClass(col.name, p.row)"
            >{{ col.value }}</q-td
          >
          <!-- <q-td v-for="(col, i) in p.cols" :key="col" @click="showDar(col.name, p.row)" :style="getStyle(col.name)" class="cursor-pointer" :class="getClass(col.name, p.row)">{{ getVal(p.row, i) }}</q-td> -->
        </q-tr>
      </template>
    </q-table>
    <pfdar />
    <InfoDisplay />
    <ImgDisplay />
  </div>
  <!-- <div v-if="daysum" class="q-px-md float-right"><q-btn round icon="edit" color="red" glossy @click="toggleDaySum"/></div> -->
</template>
<script setup>
import { ref, computed } from 'vue'
import emitter from 'tiny-emitter/instance'
import pfdar from './pfdar.vue'
import InfoDisplay from '../src/components/InfoDisplay.vue'
import ImgDisplay from '../src/components/ImgDisplay.vue'
import { libFunctions } from '../src/composables/libFunctions'
import { axiosFunctions } from '../src/composables/axiosFunctions'
import { dayFunctions } from '../src/composables/dayFunctions'
const { isIM, isDesk, buildApp, palist, dalist, screenwidth, ENV_DEV } =
  libFunctions()
const { chwk1, chwk3, yyyymmddHHMM } = dayFunctions()
const { gaxios } = axiosFunctions()

//== data
const cols = ref(['ID'])
var lastClickedP = { key: 0, pageIndex: 0 }
var clickedRow = {}
const clickedIdx = ref(0)
var separator = 'cell'
// var showCol = false
const visibleColumnsDesk = [col(1).name, col(2).name, col(3).name]
const visibleColumnsFone = [col(1).name, col(2).name, col(3).name]
// const visibleColumnsFone = [col(3).name]
const columns = ref([col(0), col(1), col(2), col(3)])
// const columns = ref([ col(1), col(2) ])
const fabOpen = ref(false)
const dayxlst = ref([])
const dayslst = ref([])
const daysum = ref(true)
const rowsPerPageDesk = 23
const rowsPerPageIM = 14

const compDaySum = computed(() => {
  return daysum.value
})

//== main
console.log('-ST-pflist')
buildApp('胰流报告', 'PancreaticFluid')
getList(screenwidth / 13)
emitter.emit('items-per-page', isIM ? rowsPerPageIM : rowsPerPageDesk)

emitter.on('search', searchQuery => {
  searchQuery = searchQuery
})
emitter.on('pfcheck-getList', da => setList(da))
emitter.on('pfcheck-add', x => addedRow(x.row))
emitter.on('pfcheck-upd', x => updedRow(x.row))
emitter.on('pfcheck-del', () => deledRow())
emitter.on('toggle-pf-sum', () => {
  daysum.value = !daysum.value
  toggleDaySum()
})

function toggleDaySum() {
  console.log(`-fn-toggleDaySum daysum=${daysum.value}`)
  if (compDaySum.value) {
    emitter.emit('dats', dayxlst.value)
  } else {
    emitter.emit('dats', dayslst.value)
    // daysum.value = !daysum.value
  }
}

function col(idx) {
  const cols = [
    { required: false, name: 'id', field: 'id' },
    {
      required: true,
      label: 'Check Time',
      align: 'center',
      name: 'datetime',
      field: 'datetime',
      headerClasses: 'text-center text-no-wrap'
    },
    {
      required: true,
      label: 'Count',
      align: 'center',
      name: 'cnt',
      field: 'cnt'
    },
    {
      required: true,
      label: 'Volumn',
      align: 'center',
      name: 'vol',
      field: 'vol'
    }
    // { required: false, label: 'Week', align: 'center', name: 'week', field: 'week', headerStyle: 'width:4%' },
    // { required: false, label: 'note', align: 'left', name: 'note', field: 'note' },
    // { required: false, label: '链接', align: 'left', name: 'lnk', field: 'lnk', sortable: true },
    // { required: false, label: '日期', align: 'left', name: 'dtwk', field: 'dtwk', headerStyle: 'max-width:370px' },
  ]
  return cols[idx]
}
// const compFixed = computed(() => {
//   return lastClickedP.pageIndex < 9 ? "fixed" : "none"
// })
function getVal(row, idx) {
  // if (idx === 4) console.log(`%c-CK-fn-getVal name=${row[col(idx).name]} clkIdx=${clickedIdx.value}`, 'color:red; font-size:16px')
  // console.log(`%c-CK-fn-getVal name=${row[col(idx).name]}`, 'color:red; font-size:16px')
  return row[col(idx).name]
}
function addedRow(row) {
  console.log(`-fn-addedRow row.datetime=${row.datetime}`, row)
  row.color = 'bg-lime-10 text-white'
  row.week = '(' + row.datetime.chwk1() + ')'
  row.dtwk = isDesk
    ? row.datetime + ' (' + row.datetime.chwk3() + ')'
    : row.datetime + ' (' + row.datetime.chwk1() + ')'
  // dalist.value.unshift(row)
  let rowIdx = dalist.value.findIndex(p => p.datetime <= row.datetime)
  dalist.value.splice(rowIdx, 0, row)
}
function updedRow(row) {
  let rowIdx = dalist.value.map(p => p.id).indexOf(row.id)
  row.color = 'bg-indigo-10 text-cyan-2'
  row.week = '(' + row.datetime.chwk1() + ')'
  // row.dtwk = row.datetime + ' (' + isDesk ? row.datetime.chwk3() : row.datetime.chwk1() + ')'
  row.dtwk = isDesk
    ? row.datetime + ' (' + row.datetime.chwk3() + ')'
    : row.datetime + ' (' + row.datetime.chwk1() + ')'
  // console.log(`%c-fn-updedRow rowIdx=${rowIdx}`, 'color:red;font-size:16px', row)
  dalist.value.splice(rowIdx, 1, row)
}
function deledRow() {
  console.log('user confirmed to delete row', clickedRow)
  dalist.value.splice(clickedIdx.value, 1)
}
function getStyle(coln) {
  // console.log(`-fn-getStyle coln=${coln}`)
  // if (coln === col(1).name)      return 'min-width:145px;max-width:145px'
  if (coln === col(1).name) return 'width:145px'
  else if (coln === col(2).name) return 'min-width:70px;max-width:70px'
  else if (coln === col(3).name) return 'min-width:50px;max-width:50px'
  else if (coln === col(4).name) return 'min-width:70px;max-width:70px'
}
function getClass(coln, row) {
  // console.log(`-fn-getClass coln=${coln}`, row)
  const rowColor = row.color == null ? getClickedBG(row) : row.color
  // return 'text-no-wrap text-left ' + rowColor
  // if (row.color != null) rowColor = row.color
  if (coln === col(1).name) return 'text-no-wrap text-center ' + rowColor
  else if (coln === col(2).name) return 'text-no-wrap text-center ' + rowColor
  else if (coln === col(3).name) return 'text-no-wrap text-center ' + rowColor
  else if (coln === col(4).name) return 'text-no-wrap text-right ' + rowColor
}
function getClickedBG(row) {
  return row.id === clickedRow.id ? ' bg-purple text-yellow-2' : ''
}
function showDar(coln, row) {
  const clone = JSON.parse(JSON.stringify(row))
  let act = coln == 'vol' ? 'add' : 'upd'

  if (act == 'add') clone.datetime = yyyymmddHHMM(new Date())
  console.log(`coln=${coln}`, clone)
  emitter.emit('open-pfdar', clone, act)
}
function getList(swProp) {
  const path = ENV_DEV + `/pfcheck/getList/${swProp}`
  // function getList () {
  // const path = ENV_DEV + `/pfcheck/getList`
  gaxios(path)
}
function setList(da) {
  console.log(`-fn-setList numchecks=${da.lst.length}`, da)
  da.lst.forEach(p => {
    p.datetime += ' (' + p.datetime.chwk1() + ')'
    p.cnt = 1
  })
  // da.lst.forEach(p => { p.week = '(' + p.datetime.chwk1() + ')'; p.cnt = 1 })
  // da.lst.forEach(p => { p.dtwk = p.date + (' (' + isDesk ? p.date.chwk3() : pdate.chwk1() + ')') })
  // da.lst.forEach(p => { p.dtwk = isDesk ? p.datetime + ' (' + p.datetime.chwk3() + ')' : p.datetime  + ' (' + p.datetime.chwk1() + ')' })

  // let pplst = da.lst.filter(p => p.vol % 5 == 1)
  let pplst = structuredClone(da.lst)
  console.log('pplst', pplst)
  let x = pplst.shift()
  x.cnt = 1
  // let daylst = [{ id: x.id, datetime:x.datetime + ' (' + x.datetime.chwk1() + ')', week:x.week, cnt: x.cnt, vol: x.vol }]
  let dxlst = [{ id: x.id, datetime: x.datetime, cnt: x.cnt, vol: x.vol }]
  pplst.forEach(p => {
    //   if (dxlst.length === 0) dxlst.push({ datetime:p.datetime, week:p.week, vol:p.vol })
    let lastelm = dxlst[dxlst.length - 1]
    if (p.datetime.substring(0, 10) == lastelm.datetime.substring(0, 10)) {
      lastelm.vol += p.vol
      lastelm.cnt += 1
    } else {
      // dxlst.push({ id: p.id, datetime: p.datetime + ' (' + p.datetime.chwk1() + ')', week:p.week, cnt: 1, vol: p.vol })
      dxlst.push({ id: p.id, datetime: p.datetime, cnt: 1, vol: p.vol })
    }
  })
  dayxlst.value = dxlst
  dayslst.value = da.lst
  if (compDaySum.value) emitter.emit('dats', dayxlst.value)
  else emitter.emit('dats', dayslst.value)
  // emitter.emit('dats', da.lst)
  // console.log(`palist:`, palist.value)
  // console.log(`dalist:`, dalist.value)
  // emitter.emit('num-items', da.lst.length)
}
</script>

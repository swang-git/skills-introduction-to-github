<template>
<div class="q-px-xs">
  <div class="q-pa-xs text-h6 text-yellow text-center no-wrap">HEALTH TEST RESULTS</div>
  <div :style="isIM ? { margin:'-4px 0 0 -0px' } : { margin:'0 1px 0 0', border:'1px solid cyan' }">
    <q-table v-if="isDesk" class="bg-teal-10" dark v-model:rows="palist" :columns="columns" dense
      :card-class="isIM ? 'bg-teal-10' : null" :card-style="isIM ? 'width:344px;margin:0 0 0 40px;font-size:30px' : null"
      :grid=isIM :visible-columns="isIM ? visibleColumnsFone : visibleColumnsDesk" 
      :style="isIM ? { height:'511px', margin:'-3px 0 0 1.5px' } : { marginTop:'-1px' }" style="width:100%;border-top:1px solid cyan"
      row-key="id" separator="cell" wrap-cells :hide-pagination="true" :pagination="isIM ? { rowsPerPage:rowsPerPageIM } : { rowsPerPage: rowsPerPageDesk }"
    >
      <template v-slot:header="props">
        <q-tr :props="props">
          <q-th v-for="col in props.cols" :key="col.name" :props="props" class="text-yellow text-center">{{ col.label }}</q-th>
        </q-tr>
      </template>

      <template v-if="isDesk" v-slot:body="p">
        <q-tr :props="p" class="cursor-pointer" :class="!showAUD || p.rowIndex>25 ? null : {'bg-cyan-8':p.row.upd, 'bg-lime-9':p.row.add, 'bg-indigo-9':p.row.del}">
          <q-td v-for="col in p.cols" :key=col class="text-no-wrap ellipsis" @click="showRow(col, p)" :style="getStyle(col.name)">
            <span v-if="col.name==='subc' && col.value==='Play'">{{ col.value }} ({{ p.cols[0].value.chwk3() }})</span>
            <!-- <span v-else-if="col.name==='date' && yearReg.test(col.value) && isIM">{{ col.value.substring(5, 16) }}</span> -->
            <span v-else>{{ col.value }}</span>
          </q-td>
        </q-tr>
        <q-tr v-show="p.expand" :props="p">
          <q-td colspan="110%">
            <GridPropTable :record="clickedRow" :expColor="'bg-teal-10'"  :idx="clickedIdx" @open-dar="openDar" @del-row="delRow" />
          </q-td>
        </q-tr>
      </template>
    </q-table>
    <q-table v-if="isIM" class="bg-teal-10" dark v-model:rows="palist" :columns="columns"
        card-class="bg-teal-10" card-style="width:373px;margin:0 0 0 40px"
        :grid=isIM :visible-columns="visibleColumnsFone"
        row-key="id" :hide-pagination="true" :pagination="{ rowsPerPage:rowsPerPageIM }"
        card-container-style="font-size:18.6px;width:373px;padding:0 0 0 2px"
    >
      <template v-slot:item="p">
        <q-card dark square class="text-cyan-1" style="margin:0 0 0px 0;width:379px;border:0px solid gold">
          <q-card-section :class="{ 'bg-teal-10':p.rowIndex%2==0, 'bg-cyan-10':p.rowIndex%2==1 }">
            <q-btn glossy round icon="toc" color="indigo-10" class="float-right" @click="showDetails(p)" />
            <div>{{ p.cols[0].value }} ({{ p.cols[0].value.chwk3() }})</div>
            <div>{{ p.cols[1].value }} / {{ p.cols[2].value }}</div>
            <div>{{ p.cols[3].value }}</div>
            <div>{{ p.cols[4].value }}</div>
          </q-card-section>
        </q-card>
      </template>
    </q-table>
  </div>
</div>
<htdar />
</template>
<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import emitter from 'tiny-emitter/instance'
import { Constants } from '../src/config.js'
import { libFunctions } from '../src/composables/libFunctions'
import { axiosFunctions } from '../src/composables/axiosFunctions'
import { dayFunctions } from '../src/composables/dayFunctions'
import ChartsProxy from './charts/ChartsProxy'
import GridPropTable from '../src/components/GridPropTable'
import htdar from './htdar'

//== data
const chname = ref('Health Test Results')
const { isIM, isDesk, buildApp, fmtcy, dalist, palist, $q } = libFunctions()
const { gaxios, paxios } = axiosFunctions()
const { today, getFutureDate } = dayFunctions()

const refGiftCardBalanceSheet = ref(null)
const searchQuery = ref('')
const showAUD = ref(true)
var chkspeed = (new Date()).getTime()
const cookyExpires = {expires:'1d 2h 3m 4s'}
const addKey = 'addExList'
const updKey = 'updExList'
const delKey = 'delExList'
const year = ref(today().year())
// const yearReg = ref(null)
const month = ref(today().yyyymm())
const futureDate = ref(getFutureDate(Constants.PLUS_DAYS))
const scoreId = ref(0)
var purchaselst = ref([])
var clickedRow = reactive({})
var lastClicked = reactive({row:{id:0}})
var clickedIdx = 0
var ccardPayment = null
var ccardDueDay = null
var betweenDays = null
const rowsPerPageDesk = 25
const rowsPerPageIM = 4
const visibleColumnsDesk = ref([col(1).name,col(2).name,col(3).name,col(4).name,col(5).name,col(6).name,col(7).name,col(8).name,col(9).name,col(10).name])
const visibleColumnsFone = ref([col(1).name,col(2).name,col(3).name,col(4).name,col(5).name])
// const visibleColumnsFone = ref([col(1).name,col(2).name,col(3).name,col(5).name])
const columns = ref([col(0), col(1), col(2), col(3), col(4), col(5), col(6), col(7), col(8), col(9), col(10)])

console.log(`-ST-tstlist`)
// console.log(`-ST-exlist isIPad=${isIPad} isIM=${isIM} isDesk=${isDesk}`, chkspeed)

//== emitter.on
emitter.on('healthtest-getList', (x) => { setList(x) })
emitter.on('show-healthtest-chart', () => { showChart() })

//== main ===
buildApp('健康检查', 'Health Tests')
getList()
emitter.emit('cur-app', 'HealthTest') // sent to MainLayout to setup currApp
emitter.emit('items-per-page',  isIM ? rowsPerPageIM : rowsPerPageDesk) // keep to show data

//== function sections
onMounted(() => {
  // console.log(`-MT-GiftCardBalanceSheet=${refGiftCardBalanceSheet.value}`)
})
function delRow (row) {
  console.log(`-fn-delRow action=del-row`, row)
  // emitter.emit('open-dar', action)
}
function openDar (action, row) {
  console.log(`-fn-openDar action=${action}`, row)
  emitter.emit('open-htdar', action, row)
}
function showChart () {
  console.log(`-fn-showChart`)
  emitter.emit('open-ChartsProxy')
}
function testDB () { 
  const path = process.env.API + '/exp/testDB/' + '2022-02-16 13:00/15/55555'
  gaxios(path)
}
function col(idx) {
  const cols = [
    { name: 'id', field: 'id' },
    { required: false, label: 'Test Date', align: 'center', name: 'date', field: 'date', sortable: true, headerStyle:'font-size:18px', headerClasses: 'text-yellow' },
    { required: false, label: 'A1c', align: 'center', name: 'A1c', field: 'A1c', sortable: true, headerStyle:'font-size:18px', headerClasses: 'text-amber-9' },
    { required: false, label: 'AST', align: 'center', name: 'AST', field: 'AST', sortable: true, headerStyle:'font-size:18px', headerClasses: 'text-amber-9' },
    { required: false, label: 'ALT', align: 'center', name: 'ALT', field: 'ALT', sortable: true, headerStyle:'font-size:18px', headerClasses: 'text-amber-9' },
    { required: false, label: 'HDL', align: 'center', name: 'HDL', field: 'HDL', sortable: true, headerStyle:'font-size:18px', headerClasses:'text-amber text-no-wrap' },
    { required: false, label: 'LDL', align: 'center', name: 'LDL', field: 'LDL', sortable: true, headerStyle:'font-size:18px', headerClasses:'text-amber' },
    { required: false, label: 'Bili', align: 'center', name: 'Bilirubin', field: 'Bilirubin', sortable: true, headerStyle:'font-size:18px', headerClasses: 'text-yellow' },
    { required: false, label: 'Alkal', align: 'center', name: 'Alkaline', field: 'Alkaline', sortable: true, headerStyle:'font-size:18px', headerClasses: 'text-yellow' },
    { required: false, label: 'Trig', align: 'center', name: 'Triglycerides', field: 'Triglycerides', sortable: true, headerStyle:'font-size:18px', headerClasses: 'text-yellow' },
    { required: false, label: 'PSA', align: 'center', name: 'PSA', field: 'PSA', sortable: true, headerStyle:'font-size:18px', headerClasses: 'text-yellow' },
  ]
  return cols[idx]
}
function getClickedIdx (rowId) {
  // console.log(`-CK-fn-getClickedIdx row.id=${rowId}`)
  return dalist.value.map(p => p.id).indexOf(rowId) % 25
}
function showRow (col, p) {
  if (col.name === 'date') {
    clickedIdx = getClickedIdx(p.row.id)
    console.log(`-fn-showRow clickedIdx=${clickedIdx}, col.name=${col.name}`, p)
    // emitter.emit('open-exdar', p.row)
  } else showDetails(p)
}
function getStyle (coln) {
  // console.log('-fn-getStyle', coln)
  const fz = 'font-size:18.3px;'
  if (isDesk) {
    if (coln === col(1).name)      return fz + 'cursor:grab;min-width:1px;max-width:1px;'
    else if (coln === col(2).name) return fz + 'text-align:right;min-width:1px;max-width:1px;'
    else if (coln === col(3).name) return fz + 'text-align:right;min-width:1px;max-width:1px;' 
    else if (coln === col(4).name) return fz + 'text-align:right;min-width:1px;max-width:1px;'
    else if (coln === col(5).name) return fz + 'text-align:right;min-width:1px;max-width:1px;'
    else if (coln === col(6).name) return fz + 'text-align:right;min-width:1px;max-width:1px;'
    else if (coln === col(7).name) return fz + 'text-align:right;min-width:1px;max-width:1px;'
    else if (coln === col(8).name) return fz + 'text-align:right;min-width:1px;max-width:1px;'
    else if (coln === col(9).name) return fz + 'text-align:right;min-width:1px;max-width:1px;'
    else if (coln === col(10).name) return fz + 'text-align:right;min-width:1px;max-width:1px;'
  } else {
    if (coln === col(1).name)      return 'min-width:30%;max-width:30%;' + fz
    else if (coln === col(2).name) return 'min-width:30%;max-width:30%;' + fz
    else if (coln === col(5).name) return 'min-width:40%;max-width:40%;text-align:right;' + fz
  }
}
function getRowIdx (date) {
  for (let i=0; i<dalist.value.length; i++) { 
    if (date >= dalist.value[i].date) return i
  }
  return 0
}
function getRowIdxById (rowId) {
  for (let i=0; i<dalist.value.length; i++) { 
    if (dalist.value[i].id === rowId) return i
  }
  return 0
}
function showDetails(p) {
  // console.log('-fn-showDetails lastClicked Row A', lastClicked, p.row.add, p.row.hasOwnProperty('upd'))
  if (lastClicked.row.id === p.row.id) {
    p.expand = !p.expand
    if (!p.expand) clickedIdx = 0 // to make it fixed
    return
  } else {
    // p.expand = !p.expand
    p.expand = true
    lastClicked.expand = false
    lastClicked = p
    // console.log(`A-fn-showDetails clickedIdx=${clickedIdx}`, new Date())
  }
  // p.row.day = isDesk ? getDay2(p.row.date) : null
  // p.row.day = isDesk ? p.row.date.chwk3() : null
  p.row.day = p.row.date.chwk3()
  const row = p.row
  clickedRow = row
  clickedIdx = getRowIdx(row.date)
  purchaselst.value = []
  if (row.cats === 'Shopping' || row.subc === 'Wedge Set' ) {
    // console.log(`-fn-showDetails -CK- row.date=${row.date}`, typeof row.date)
    const date = row.date.yyyymmdd()
    const payeId = row.payeId
    const path = process.env.API + '/exp/getPurchasedList/' + date + '/' + payeId
    gaxios(path)
  }
  scoreId.value = 0
  if (row.cats === 'Golf' && row.subc === 'Play') {
    const path = process.env.API + '/exp/getScoreId'
    const data = { courseId: row.payeId, playerId: row.user_id, teetime: row.date }
    paxios(path, data)
  }
  if (!p.expand) clickedIdx = 0 // to make it fixed
  // console.log('-fn-showDetails lastClicked Row B', lastClicked, p.row.add, p.row.hasOwnProperty('upd'))
}
function getList () {
  console.log('-CK-fn-tstlist/getList')
  const path = process.env.API + '/healthtest/getList'
  // const path = process.env.API + '/exp/getList'
  gaxios(path)
}
function setList (da) {
  console.log('-on-setList returned da', da.lst)
  // {'bg-cyan-8':p.row.upd, 'bg-lime-9':p.row.add, 'bg-indigo-9':p.row.del}"
  if (da.lst.length === 0) console.log('-on-getList no data return from getList', da)
  emitter.emit('dats', da.lst)
  palist.value.forEach((p, idx) => {
    // p.rowColor = null
    const cookieKey = 'add_' + p.id
    if ($q.cookies.get(cookieKey) > 0) {
      p.add = true
      // p.rowColor = 'bg-lime-8'
    }
  })

  palist.value.forEach((p, idx) => {
    const cookieKey = 'upd_' + p.id
    if ($q.cookies.get(cookieKey) > 0) {
      p.upd = true
      // p.rowColor = 'bg-cyan-8'
    }
  })

  const delCookies = $q.cookies.get('del_key')
  if (delCookies != null) {
    // console.table(delCookies)
    delCookies.forEach(p => { p.del = true; palist.value.unshift(p) })
  }
}
// function setList (da) {
//   console.log('-on-setList returned da', da)
//   if (da.lst.length === 0) console.log('-on-getList no data return from getList', da)
//   emitter.emit('dats', da.lst)
//   // const pa = palist.value.map(x => x.id)
//   palist.value.forEach((p, idx) => {
//     const cookieKey = 'add_' + p.id
//     if ($q.cookies.get(cookieKey) > 0) palist.value[idx].add = true
//   })

//   palist.value.forEach((p, idx) => {
//     const cookieKey = 'upd_' + p.id
//     if ($q.cookies.get(cookieKey) > 0) palist.value[idx].upd = true
//   })

//   const delCookies = $q.cookies.get('del_key')
//   if (delCookies != null) {
//     // console.table(delCookies)
//     delCookies.forEach(p => { p.del = true; palist.value.unshift(p) })
//   }
// }
function setFutureDate () {
  if (futureDate.value === getFutureDate(Constants.PLUS_DAYS)) futureDate.value = getFutureDate(365 * 10)
  else futureDate = getFutureDate(Constants.PLUS_DAYS)
}

const loadingTime = computed(() => { return ((new Date().getTime() - chkspeed) / 1000).toFixed(1) })
// const dateExpense = computed(() => { return fmtcy(dateSpend.value) })
// const compScoreId = computed(() => { return scoreId.value })
const yearSpend = computed(() => {
  let exp = 0.0
  // const lst = dalist.value.filter(p => p != null && p.date != null && p.date.substring(0, 4) == year.value)
  const lst = dalist.value.filter(p => p != null && p.date != null && p.date.year() === year.value)
  // console.log('y-lst', lst)
  if (searchQuery.value.indexOf('autopay') >= 0) lst.forEach(p => { exp += parseFloat(p.unip) })
  else lst.forEach(p => { exp += parseFloat(p.cost) })
  return fmtcy(exp)
})
const gWL = computed(() => {
  let amt = 0.0
  const lst = dalist.value.filter(p => p != null && p.subc === 'Play' && p.quan !== null)
  lst.forEach(p => { amt += parseFloat(p.quan) })
  return amt
})
const monthSpend = computed(() => {
  let exp = 0.0
  // const lst = dalist.value.filter(p => p !=null && p.date != null && p.date.substring(0, 7) === month.value)
  const lst = dalist.value.filter(p => p !=null && p.date != null && p.date.yyyymm() === month.value)
  if (searchQuery.value.indexOf('autopay') >= 0) lst.forEach(p => { exp += parseFloat(p.unip) })
  else lst.forEach(p => { exp += parseFloat(p.cost) })
  return fmtcy(exp)
})
</script>
<style>
tr:hover {
  background-color: red;
  color: yellow;
}
</style>
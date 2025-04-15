<template>
<!-- <div style="display:flex;justify-content:center;align-items:center;height:100vh"> -->
<div style="display:grid;place-items:center;height:100vh">
<div :class="{ fixed : clickedIdx < 9}" style="width:800px">
  <div v-if="isDesk" class="row text-h6 no-wrap" style="height:36px;margin:0 1px 0 0;border:1px solid cyan">
    <div style="width:28%" class="text-yellow-9 q-pl-sm">总支出: {{ totalSpend }}</div>
    <div style="width:28%" class="text-yellow-8 text-center">年支出: {{ yearSpend }}</div>
    <div style="width:25%" class="text-yellow-6 text-center">月支出: {{ monthSpend }}</div>
    <div style="width:4%"  class="text-teal-4 text-center" v-if="loadingTime>0">{{ loadingTime }}</div>
    <div style="width:15%" class="text-yellow-3 text-right">比赛: <b :class="gWL>=0 ? 'text-green' : 'text-red'">${{ gWLval }}</b></div>
  </div>
  <div :style="isIM ? { margin:'-4px 0 0 0' } : { margin:'0px 1px 0 0', border:'1px solid cyan' }">
    <q-table v-if="isDesk" class="bg-teal-10" dark v-model:rows="palist" :columns="columns" dense
      :card-class="isIM ? 'bg-teal-10' : null" :card-style="isIM ? 'width:344px;margin:0 0 0 40px;font-size:30px' : null"
      :grid=isIM :visible-columns="isIM ? visibleColumnsFone : visibleColumnsDesk"
      :style="isIM ? { height:'screenheight', margin:'-3px 0 0 1.5px' } : { marginTop:'-1px' }" style="width:100%;border-top:1px solid cyan"
      row-key="id" separator="cell" wrap-cells :hide-pagination="true" :pagination="isIM ? { rowsPerPage:rowsPerPageIM } : { rowsPerPage: rowsPerPageDesk }"
    >
      <template v-slot:header="props">
        <q-tr :props="props">
          <q-th v-for="col in props.cols" :key="col.name" :props="props" class="text-yellow text-center">{{ col.label }}</q-th>
        </q-tr>
      </template>

      <template v-if="isDesk" v-slot:body="p">
        <q-tr :props="p" class="cursor-pointer" :class="!showAUD || p.rowIndex>23 ? null : {'bg-cyan-8':p.row.upd,'bg-lime-9':p.row.add,'bg-indigo-9':p.row.del,'bg-indigo-10':p.expand}">
          <q-td v-for="col in p.cols" :key=col class="text-no-wrap ellipsis" @click="showRow(col, p)" :style="getStyle(col.name)">
            <span v-if="col.name==='subc' && col.value==='Play'">{{ col.value }} ({{ p.cols[0].value.chwk3() }})</span>
            <span v-else-if="col.name==='date' && yearReg.test(col.value) && isIM">{{ col.value.substring(5, 16) }}</span>
            <span v-else>{{ col.value }}</span>
          </q-td>
        </q-tr>
        <q-tr v-show="p.expand" :props="p">
          <q-td colspan="100%">
            <ExpDetails :record="clickedRow" :hasPurchases="purchaselst.length>0" :isReconcileC="isReconcile()" :hasGolfScore="scoreId>0"
              :expColor="p.row.upd ? 'bg-cyan-10' : 'bg-teal-10'"
              :idx="clickedIdx" @open-dar="openExdar" @open-plist="openPlst" @open-recon="openRecon" @open-score="getScore" />
          </q-td>
        </q-tr>
      </template>
    </q-table>
    <q-table v-if="isIM" class="bg-teal-10" dark v-model:rows="palist" :columns="columns"
        style="border:1px solid cyan;font-size:17.63px"
        :grid=isIM :visible-columns="visibleColumnsFone"
        row-key="id" :hide-pagination="true" :pagination="{ rowsPerPage:rowsPerPageIM }"
    >
      <template v-slot:item="p">
        <q-card dark square class="text-cyan-1" style="margin:0 0 0px 0;width:379px;border:0px solid gold">
          <q-card-section :class="{ 'bg-teal-10':p.rowIndex%2==0, 'bg-cyan-10':p.rowIndex%2==1 }">
            <q-btn glossy round icon="toc" color="indigo-10" class="float-right" @click="showDetails(p)" />
            <div>{{ p.cols[0].value }} ({{ p.cols[0].value.chwk3() }})</div>
            <div>{{ p.cols[1].value }} / {{ p.cols[2].value }}</div>
            <div>{{ p.cols[3].value }}</div>
            <div>${{ p.cols[4].value }}</div>
          </q-card-section>
        </q-card>
        <tr v-show="p.expand" :props="p" height="0">
          <td>
            <ExpDetails :record="clickedRow" :hasPurchases="purchaselst.length>0" :isReconcileC="isReconcile()" :hasGolfScore="scoreId>0"
              :expColor="p.row.upd ? 'bg-amber-10' : p.row.del ? 'bg-cyan-10' : 'bg-green-10'" :idx="clickedIdx"
              @open-dar="openExdar" @open-plist="openPlst" @open-recon="openRecon" @open-score="getScore" />
          </td>
        </tr>
      </template>
    </q-table>
  </div>
	<exdar />
	<PurchasedList />
	<CCardReconcileSheet />
	<ScoreDisplay />
	<ChartsProxy1 :data="dalist" />
	<ChartsProxy2 :data="dalist" />
	<ChartsProxy3 :data="dalist" />
	<ExpDetailsPad :data="dalist" />
  <GiftCardBalanceSheet ref="refGiftCardBalanceSheet" />
</div>
</div>
</template>
<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
// import { dom } from 'quasar'
// const { height, width } = dom
import emitter from 'tiny-emitter/instance'
import { Constants } from '../src/config.js'
import ExpDetails from '../src/components/ExpDetails'
import exdar from './exdar'
import PurchasedList from './PurchasedList'
import CCardReconcileSheet from './CCardReconcileSheet'
import GiftCardBalanceSheet from './GiftCardBalanceSheet'
import ScoreDisplay from './ScoreDisplay'
import  ChartsProxy1 from './Charts/ChartsProxy1'
import  ChartsProxy2 from './Charts/ChartsProxy2'
import  ChartsProxy3 from './Charts/ChartsProxy3'
import  ExpDetailsPad from '../src/components/ExpDetailsPad'
import { libFunctions } from '../src/composables/libFunctions'
import { axiosFunctions } from '../src/composables/axiosFunctions'
import { dayFunctions } from '../src/composables/dayFunctions'

//== data
const { isIM, isDesk, isFone, buildApp, fmtcy, dalist, palist, $q } = libFunctions()
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
const yearReg = ref(null)
const month = ref(today().yyyymm())
const futureDate = ref(getFutureDate(Constants.PLUS_DAYS))
const scoreId = ref(0)
var purchaselst = ref([])
var dats = ref([])
var clickedRow = reactive({})
var lastClicked = reactive({row:{id:0}})
const clickedIdx = ref(0)
var ccardPayment = null
var ccardDueDay = null
var betweenDays = null
const rowsPerPageDesk = 23
const rowsPerPageIM = 4
const visibleColumnsDesk = ref([col(1).name,col(2).name,col(3).name,col(4).name,col(5).name])
const visibleColumnsFone = ref([col(1).name,col(2).name,col(3).name,col(4).name,col(5).name])
// const visibleColumnsFone = ref([col(1).name,col(2).name,col(3).name,col(5).name])
const columns = ref([col(0), col(1), col(2), col(3), col(4), col(5)])
const baseHeight = 6
// const domHeight = ref(baseHeight)

console.log(`-ST-exlist`)
// console.log(`-ST-exlist isIPad=${isIPad} isIM=${isIM} isDesk=${isDesk}`, chkspeed)

//== emitter.on
// emitter.on('del-row', (x) => { console.log(x) })
emitter.on('search', (x) => { searchQuery.value = x })
emitter.on('show-year-chart', () => { showChart('cata') })
emitter.on('set-future-date', () => { setFutureDate() })
emitter.on('exp-getList', (x) => { setList(x) })
emitter.on('exp-updSpend', (x) => { updedRow(x) })
emitter.on('exp-addSpend', (x) => { addedRow(x.row) })
emitter.on('exp-delSpend', (x) => { deledRow(x.row) })
emitter.on('exp-getPurchasedList', (x) => { setPurchasedList(x.lst) })
emitter.on('exp-getCreditCardSpendings', (x) => { setCreditCardSpendings(x) })
emitter.on('exp-getScoreId', (x) => { setScoreId(x.scoreId) })
emitter.on('exp-getScore', (x) => { setScore(x) })
emitter.on('show-purchased-list', () => { openPlst() })
emitter.on('toggle-aud', () => { showAUD.value = !showAUD.value })
emitter.on('show-golf-scores', (row) => { showGolfSores(row) })
emitter.on('check-gc-balance', () => { openGiftCardBalanceSheet() })

//== main ===
buildApp('消费记录', 'Expense')
getList()

emitter.emit('items-per-page',  isIM ? rowsPerPageIM : rowsPerPageDesk)
yearReg.value = new RegExp(year.value + '-')

// const compClickedIdx = computed(() => { return clickedIdx.value })

//== function sections
onMounted(() => {
  console.log(`-MT-GiftCardBalanceSheet=${refGiftCardBalanceSheet.value}`)
  // console.log(`-MT-refExpDetails=${refExpDetails.value}`)
})
// function delRow (rowId) {
//   console.log(`-CK-fn delRow with Id=${rowId}`)
//   dats.value = dats.value.filter(p => p.id != rowId)
//   emitter.emit('dats', dats.value)
// }
function isGolfPlay (col) {
  return col[1].value === 'Golf' && col[2].value === 'Play'
}
function openGiftCardBalanceSheet () {
  console.log('-fn-openGiftCardBalanceSheet')
  refGiftCardBalanceSheet.value.openIt()
}
function getAudClass (p) {
  // console.log('-fn-getAudClass', p)
  if (!showAUD.value) return null
}
function checkBalance () {
  const path = process.env.API + '/exp/checkBalance/9'
  gaxios(path)
}
function showGolfSores (rw) {
  clickedRow = rw
  console.log(`-FN-showGolfScores`, clickedRow)
  const path = process.env.API + '/exp/getScoreId'
  const data = { courseId: rw.payeId, playerId: rw.user_id, teetime: rw.date }
  paxios(path, data)
}
function showChart (ymc) {
  emitter.emit('open-ChartsProxy1', 'cats')
}
function testDB () {
  const path = process.env.API + '/exp/testDB/' + '2022-02-16 13:00/15/55555'
  gaxios(path)
}
function col(idx) {
  const cols = [
    { name: 'id', field: 'id' },
    { required: false, label: '采购时间', align: 'left', name: 'date', field: 'date', sortable: true, headerStyle:'font-size:20px', headerClasses: 'text-yellow' },
    { required: false, label: '类别', align: 'left', name: 'cats', field: 'cats', sortable: true, headerStyle:'font-size:20px', headerClasses: 'text-yellow' },
    { required: false, label: '具体类别', align: 'left', name: 'subc', field: 'subc', sortable: true, headerStyle:'font-size:20px', headerClasses: 'text-yellow' },
    { required: false, label: '购买自 / 支付给', align: 'left', name: 'paye', field: 'paye', sortable: true, headerStyle:'font-size:20px', headerClasses: 'text-yellow' },
    { required: false, label: '支付', align: 'right', name: 'cost', field: 'cost', sortable: true, headerStyle:'font-size:20px', headerClasses:'text-amber-9 text-no-wrap' }
  ]
  return cols[idx]
}
// function getExpandColor (row) {
//   console.log('-fn-getExpandColor')
//   return row.add ? addColor : row.upd ? updColor : row.del ? delColor : rowColor

//   consoconsole.log('-CK-EXLIST TIME USED TO LOAD', (new Date().getTime() - chkspeed) /1000, ' seconds')
//   return row.add ? 'text-pink-9' : row.upd ? 'text-lime-5' : row.del ? 'text-cyan-2' : 'text-yellow'
// }
function getClickedIdx (rowId) {
  // console.log(`-CK-fn-getClickedIdx row.id=${rowId}`)
  return dalist.value.map(p => p.id).indexOf(rowId) % 25
}
function showRow (col, p) {
  if (col.name === 'date') {
    clickedIdx.value = getClickedIdx(p.row.id)
    // console.log(`-fn-showRow clickedIdx=${clickedIdx}, col.name=${col.name}`, p)
    // emitter.emit('open-exdar', p.row)
  } else showDetails(p)
}
function getStyle (coln) {
  // console.log('-fn-getStyle', coln)
  const fz = 'font-size:18.3px;'
  if (isDesk) {
    if (coln === col(1).name)      return 'cursor:grab;min-width:169px;max-width:169px;' + fz
    else if (coln === col(2).name) return 'min-width:110px;max-width:110px;' + fz
    else if (coln === col(3).name) return 'min-width:160px;max-width:160px;' + fz
    else if (coln === col(4).name) return 'min-width:250px;max-width:250px;' + fz
    else if (coln === col(5).name) return fz + 'text-align:right'
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
function clearCookies(row) {
  $q.cookies.remove('add_' + row.id)
  $q.cookies.remove('upd_' + row.id)
  $q.cookies.remove('del_' + row.id)
  row.add = false
  row.upd = false
  row.del = false
  delete row.add
  delete row.upd
  delete row.del
}
function addedRow (row) {
  clickedIdx.value = 0
  console.log('-fn-addedRow', row)
  clearCookies(row)
  row.add = true
  // row.day = getDay2(row.date)
  row.day = row.date.chwk3()
  lastClicked.expand = false
  const cookieKey = 'add_' + row.id
  $q.cookies.set(cookieKey, row.id, cookyExpires)
  var dateIdx = getRowIdx(row.date)
  console.log('-ck-dates', dateIdx, row.date)
  dalist.value.splice(dateIdx, dateIdx, row)
}
function updedRow(da) {
  if (da.status == 'FAILED') {
    emitter.emit('open-InfoDisplay', 'Update Failed', da.msg)
    return
  }
  console.log('-CK-updedRow', da.row)
  const row = da.row
  clearCookies(row)
  row.upd = true
  clickedRow = row
  row.day = row.date.chwk3()
  const rowIdx = getRowIdxById(row.id)
  dalist.value.splice(rowIdx, 1, row)
  const cookieKey = 'upd_' + row.id
  // console.log(`-fn-updedRow clickedIdx=${clickedIdx.value}`)
  $q.cookies.set(cookieKey, row.id, cookyExpires)
}
function cleanCookies(key, rowId) {
  let x = $q.cookies.get(key)
  if (x == null || x.length === 0) return
  const xCookies = x.filter(p => p !== rowId)
  console.log('-fn-cleanCookies', key, x, xCookies)
  $q.cookies.set(key, Array.from(new Set(xCookies)), cookyExpires)
}
function deledRow(row) {
  // console.log('user confirmed to delete row', row, clickedIdx, clickedRow)
  dalist.value.splice(clickedIdx.value, 1)
  row.del = true
  // row.deleted_at = yyyymmddHHMMSS(new Date())
  row.deleted_at = (new Date()).yyyymmddHHMMSS()
  dalist.value.unshift(row)

  clearCookies(row)

  const cookieKey = 'del_key'
  let delCookies = $q.cookies.get(cookieKey)
  if (delCookies == null) delCookies = []
  if (!delCookies.map(p => p.id).some(pid => pid === row.id)) delCookies.push(row)
  $q.cookies.set(cookieKey, delCookies, cookyExpires)
}
function showDetails(p) {
  if (lastClicked.row.id === p.row.id) {
    p.expand = !p.expand
    if (!p.expand) clickedIdx.value = 0 // to make it fixed
    return
  } else {
    p.expand = true
    lastClicked.expand = false
    lastClicked = p
    // console.log(`%cA-fn-showDetails clickedIdx=${clickedIdx}`, 'color: red', p)
  }
  // p.row.day = isDesk ? getDay2(p.row.date) : null
  // p.row.day = isDesk ? p.row.date.chwk3() : null
  p.row.day = p.row.date.chwk3()
  const row = p.row
  clickedRow = row
  clickedIdx.value = getRowIdx(row.date)
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
  if (!p.expand) clickedIdx.value = 0 // to make it fixed
  // console.log('-fn-showDetails lastClicked Row B', lastClicked, p.row.add, p.row.hasOwnProperty('upd'))
  console.log(`%c-fn-showDetails clickedIdx=${clickedIdx.value} numProperties=${Object.values(row).length}`, 'color: red', row)
  emitter.emit('clicked-idx', clickedIdx)
}
function getScore() {
  console.log('-fn-getScore', scoreId.value)
  const row = clickedRow
  const path = process.env.API + '/exp/getScore'
  const data = { playerId:row.user_id, scoreId:scoreId.value }
  paxios(path, data)
}
function setScore(da) {
  // console.log('-CK-fn-setScore', da)
  const playData = da.playData
  const tmnt = null
  emitter.emit('open-ScoreDisplay', playData, tmnt)
}
function setScoreId (x) {
  // console.log(`-CK-fn-setScoreId scoreId=${x}`, clickedRow)
  scoreId.value = x
  const e = clickedRow
  if (x == null && e.hasScore) {
    const tit = 'No Scores'
    const msg = `No Scores for your golf play at ${e.paye} on ${e.date}`
    emitter.emit('open-InfoDisplay', tit, msg)
    return
  }
  // if (!e.hasOwnProperty('add') && !e.hasOwnProperty('upd')) {
  //   const gridPropTit = 'Purchase Details'
  //   const add_upd = 'bg-black'
  // }
  if (e.hasScore && scoreId.value > 0) getScore()
}
function isReconcile () {
  // console.log('-fn-isReconcile', clickedRow)
  const e = clickedRow
  // return parseFloat(e.unip) > 0 && parseFloat(e.cost) === 0 && e.catsId === 15 && e.subcId === 158 && +(new Date(e.date.substring(0, 10))) > +(new Date(2017, 10, 1))
  return parseFloat(e.unip) > 0 && parseFloat(e.cost) === 0 && e.catsId === 15 && e.subcId === 158 && +(new Date(e.date.yyyymmdd())) > +(new Date(2017, 10, 1))
}
function openRecon () {
  getCreditCardSpendings()
}
function setCreditCardSpendings (x) {
  // console.log('-fn-setCreditCardSpendings', x.ccdata)
  x.ccdata.forEach(p => p.checked = p.isReconed === 0 ? false : true)
  emitter.emit('open-CCardReconcileSheet', x.ccdata, ccardPayment, ccardDueDay, betweenDays)
}
function getCreditCardSpendings () {
  const e = clickedRow
  const bdays = e.note.replace(/(\d\d)\/(\d\d)\/(\d{4}) - (\d\d)\/(\d\d)\/(\d{4})(.*)/, '$3-$1-$2 ~ $6-$4-$5')
  console.log('-fn-getCreditCardSpendings', bdays, e.unip, e)
  betweenDays = bdays.split(' ~ ')
  const startDay = bdays.split(' ~ ')[0]
  const endDay = bdays.split(' ~ ')[1]
  // console.log(`-CK-getCreditCardSpendings bdays=${bdays} startDay=${startDay} endDay=${endDay} e.date=${e.date}`)
  const path = process.env.API + '/exp/getCreditCardSpendings/' + startDay + '/' + endDay + '/' + e.date
  ccardPayment = parseFloat(e.unip)
  ccardDueDay = e.date
  gaxios(path)
}
function openPlst () {
  // console.log('-CK-fn-openPlst', clickedRow)
  const date = clickedRow.date
  const paye = clickedRow.paye
  const payeId = clickedRow.payeId
  const plst = purchaselst.value
  emitter.emit('open-PurchasedList', date, plst, paye, payeId)
}
function openExdar (act) {
  console.log(`-CK-fn-openExdar act=${act}`, clickedRow)
  const crow = JSON.parse(JSON.stringify(clickedRow))
  // const cidx = getClickedIdx(crow.id)
  emitter.emit('open-exdar', crow, act)
}
function setPurchasedList (plst) {
  // console.log('-CK-fn-purchasedList from exlist', plst)
  purchaselst.value = plst
}
function getList () {
  console.log(`-fn-getList process.env.API=${process.env.API}`)
  const path = process.env.API + '/exp/getList'
  gaxios(path)
}
function setList (da) {
  // console.log('-on-setList returned da', da.lst)
  // {'bg-cyan-8':p.row.upd, 'bg-lime-9':p.row.add, 'bg-indigo-9':p.row.del}"
  if (da.lst.length === 0) console.log('-on-getList no data return from getList', da)
  dats.value = da.lst
  emitter.emit('dats', da.lst)
  dalist.value.forEach((p, idx) => {
    if      ($q.cookies.get('add_' + p.id) > 0) p.add = true
    else if ($q.cookies.get('upd_' + p.id) > 0) p.upd = true
    else if ($q.cookies.get('del_' + p.id) > 0) p.del = true
    // p.height = 6
    // if (p.gcardId > 0) p.height += 3
    // if (p.link != null) p.height += 1 // need to refined -- count num of links
    // if (p.note != null) p.height += 1
  })

  // const cookieKey = 'add_' + p.id
  // if ($q.cookies.get(cookieKey) > 0) {
  //   p.add = true
  //   // p.rowColor = 'bg-lime-8'
  // }
  // palist.value.forEach((p, idx) => {
  //   const cookieKey = 'upd_' + p.id
  //   if ($q.cookies.get(cookieKey) > 0) {
  //     p.upd = true
  //     // p.rowColor = 'bg-cyan-8'
  //   }
  // })

  // const delCookies = $q.cookies.get('del_key')
  // if (delCookies != null) {
  //   // console.table(delCookies)
  //   delCookies.forEach(p => { p.del = true; palist.value.unshift(p) })
  // }
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
const gWLval = computed(() => {
  return (gWL.value < 0 ? -1 : 1) * gWL.value
})
const monthSpend = computed(() => {
  let exp = 0.0
  // const lst = dalist.value.filter(p => p !=null && p.date != null && p.date.substring(0, 7) === month.value)
  const lst = dalist.value.filter(p => p !=null && p.date != null && p.date.yyyymm() === month.value)
  if (searchQuery.value.indexOf('autopay') >= 0) lst.forEach(p => { exp += parseFloat(p.unip) })
  else lst.forEach(p => { exp += parseFloat(p.cost) })
  return fmtcy(exp)
})
const dateSpend = computed(() => {
  let exp = 0.0
  // const lst = dalist.filter(p => p.date != null && p.date.substring(0, 10) === date.value)
  const lst = dalist.filter(p => p.date != null && p.date.yyyymmdd() === date.value)
  // console.log('d-lst', lst)
  lst.forEach(p => { exp += parseFloat(p.cost) })
  return exp.toFixed(2)
})
const totalSpend = computed(() => {
  let exp = 0.0
  if (searchQuery.value.indexOf('autopay') >= 0) dalist.value.forEach(p => { exp += parseFloat(p.unip) })
  else dalist.value.forEach(p => { if (p != null) exp += parseFloat(p.cost) })
  return fmtcy(exp)
})
</script>
<style>
tr:hover {
  background-color: red;
  color: yellow;
}
</style>

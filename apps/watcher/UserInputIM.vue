<template>
<q-dialog v-model="opened" persistent>
  <q-layout container style="height:620px;width:555px;margin-top:30px" class="bg-teal-10">
    <LayoutHeader tit="今日体重 / 退休金市价" @do-action="doAction" />
    <LayoutFooter :act="action" tit="NOTE_LINK" @do-action="doAction" />
    <q-page-container>
      <q-page>
        <div class="q-pa-xs">
          <div>
            <dap style="width:99%" :date="date" @upd-date="updDate" label="Watching Date" txsz="text-h6" />
          </div><div>
            <num style="width:99%" :obj="row" label="Portfolio" mask="#######.##" icon="monetization_on" iColor="orange" :rightIcon="true" :showRight="true" />
          </div>
          <div class="row">
            <div style="width:81%">
              <num :wtype="type" :obj="row" label="Weight" mask="#.#" icon="重" iconSize="35" iColor="green" :suffix="ctype" :rightIcon="true" :showRight="true" />
            </div>
            <div class="q-pt-xs q-pr-sm" style="max-width:10%">
              <q-fab icon="keyboard_arrow_down" color="grey" direction="down">
                <q-fab-action class="text-h6" color="primary"   @click="changeType('kilo')" icon="alarm"  label="公斤" v-if="type!='kilo'" />
                <q-fab-action class="text-h6" color="secondary" @click="changeType('pond')" icon="alarm"  label="磅"   v-if="type!='pond'" />
                <q-fab-action class="text-h6" color="amber-10"  @click="changeType('jing')" icon="alarm"  label="市斤" v-if="type!='jing'" />
              </q-fab>
            </div>
          </div>
          <div>
            <num style="width:99%" :obj="row" label="Dow Jones" mask="#######.##" iconSize="35" icon="DJ" iColor="amber" :rightIcon="true" :showRight="true" />
            <num style="width:99%" :obj="row" label="Nasdaq"    mask="#######.##" iconSize="35" icon="NQ" iColor="blue"  :rightIcon="true" :showRight="true" />
            <num style="width:99%" :obj="row" label="S&P 500"   mask="#######.##" iconSize="35" icon="SP" iColor="lime"  :rightIcon="true" :showRight="true" />
            <num style="width:99%" :obj="row" label="FTSE 100"  mask="#######.##" iconSize="35" icon="FT" iColor="green" :rightIcon="true" :showRight="true" />
            <num style="width:99%" :obj="row" label="NIKKEI"    mask="#######.##" iconSize="35" icon="NK" iColor="lime"  :rightIcon="true" :showRight="true" />
          </div>
        </div>
      </q-page>
    </q-page-container>
  </q-layout>
  <NotePad @save-details="saveNote" />
  <LnkInput @upd-link="saveLnk" />
</q-dialog>
</template>
<script setup>
import { ref, createApp } from 'vue'
import emitter from 'tiny-emitter/instance'
import LayoutHeader from '../src/components/LayoutHeader.vue'
import LayoutFooter from '../src/components/LayoutFooter.vue'
import num from '../src/components/NumInput.vue'
import dap from '../src/components/DatePicker.vue'
import NotePad from '../src/components/NotePad.vue'
import LnkInput from '../src/components/LnkInput.vue'
import { axiosFunctions } from '../src/composables/axiosFunctions'
import { dayFunctions } from '../src/composables/dayFunctions'
// const { yyyymmdd } = dayFunctions()
const { paxios } = axiosFunctions()
import { libFunctions } from '../src/composables/libFunctions'
const { $q, ENV_DEV } = libFunctions()

const type = ref('pond')
const ctype = ref('磅')
const opened = ref(false)
const action = ref(null)
const dense = ref(false)
const date = ref('0000-00-00')
const row = ref({})
console.log('-ST-UserInputIM')
emitter.on('open-UserInputIM', (rw, act) => openIt(rw, act))
emitter.on('user-confirmed', (x) => userConfirmed(x))
const emit = defineEmits(['user-confirmed'])

function setWeight() {
  let wkilo = row.value.kilo
  if (type.value == 'jing') row.value.weight = (wkilo * 2).toFixed(1)
  else if (type.value == 'kilo') row.value.weight = wkilo.toFixed(1)
  else if (type.value == 'pond') row.value.weight = (wkilo * 2.2046244202).toFixed(1)
}
function changeType (wtype) {
  $q.localStorage.set('weightUnit', wtype)
  let otype = type.value
  type.value = wtype
  ctype.value = wtype == 'pond' ? '磅' : wtype == 'jing' ? '市斤' : '公斤'
  emitter.emit('weight-unit', wtype)
  setWeight()
  console.log(`-CK-row.kilo=${row.value.kilo} row.value.weight=${row.value.weight}`)
}
function openIt (rw, act) {
  action.value = act
  type.value = $q.localStorage.getItem('weightUnit')
  ctype.value = type.value == 'pond' ? '磅' : type.value == 'kilo' ? '公斤' : type.value == 'jing' ? '市斤' : '磅'
  console.log(`-fn-openIt type=${type.value}`, rw)
  row.value = rw
  if (act == 'del') return del()
  // date.value = (new Date()).yyyymmdd()
  if (/add/.test(act)) date.value = null
  if (/upd/.test(act)) date.value = row.value.date
  opened.value = true
}
// function getMask () {
//   // return ['pond', 'jing'].includes(this.type) ? '#.#' : '#.##'
//   return '#.#'  // decimal number changing will trigger compInput.set(val) so must keep it the same length
// }
function updDate (val) {
  date.value = val
}
function userConfirmed (x) {
  if (x !== 'del-watcher') return
  console.log('user confirmed to delete row', row)
  opened.value = false
  delFromDB()
  emit('user-confirmed')
}
function saveNote (note) {
  console.log(`-fn-saveNote`)
  row.value.note = note
}
function saveLnk (lnks) {
  console.log(`-fn-saveLnk-toBeImplimented lnks=${lnks}`)
  // this.row.lnks = lnks
}
function doAction(act) {
  // console.log(`-fn-doAction act=${act}`)
  return act==='add' ? add() : act==='upd' ? upd() : act==='msg' ? msg() : act==='lnk' ? lnk() : act==='del' ? del() : null
}
function delFromDB () {
  var inData = { id: row.value.id }
  const path = ENV_DEV + '/watcher/del'
  paxios(path, inData)
}
function del () {
  const tit = 'Confirm Delete Watcher Record'
  // const msg = 'Delete record (id:' + row.value.id + ') with portfolio:' + parseFloat(row.value.portfolio).toFixed(2) + ' Permanently ?'
  // const msg = 'Delete record (id:' + row.value.id + ') with portfolio:' + parseFloat(row.value.portfolio).toFixed(2) + ' ?'
  const msg = 'Delete portfolio:' + parseFloat(row.value.portfolio).toFixed(2) + ' (record id:' + row.value.id + ') ?'
  emitter.emit('open-ConfirmDialog', tit, msg, 'del-watcher')
}
function upd () {
  if (date.value == null) {
    let tit = 'Date is Empty'
    let msg = 'Please provide Date'
    return emitter.emit('open-InfoDisplay', tit, msg)
  }
  $q.localStorage.set('weightUnit', type.value)
  var inData = getInputData()
  // console.log('inData', inData)
  const path = ENV_DEV + '/watcher/upd'
  paxios(path, inData)
  opened.value = false
}
function add () {
  if (date.value == null) {
    let tit = 'Date is Empty'
    let msg = 'Please provide Date'
    return emitter.emit('open-InfoDisplay', tit, msg)
  }
  // LocalStorage.set('weightUnit', this.type)
  $q.localStorage.set('weightUnit', type.value)
  var inData = getInputData()
  inData.id = -1
  const path = ENV_DEV + '/watcher/add'
  paxios(path, inData)
  opened.value = false
}
function lnk () {
  // console.log('-fn-lnk', row.value.link)
  let lnks = row.value.link
  if (lnks == null) {
    lnks = []
  } else if (lnks.indexOf('@') >=0 ) {
    lnks = lnks.split('@')
  } else {
    lnks = [lnks]
  }
  // console.log('-fn-lnk.openIt, lnka', lnks)
  // refs.lnk.openIt(lnks)
  emitter.emit('open-LnkInput', lnks)
}
function msg () {
  // refs.NotePad.openIt(row.value.note)
  emitter.emit('open-NotePad', row.value.note)
}
function getInputData () {
  // console.log('-fn-getInputData-row', row.value)
  type.value = $q.localStorage.getItem('weightUnit')
  var inData = {}
  inData.id = row.value.id
  inData.user_id = row.value.user_id
  inData.date = date.value.trim()
  inData.kilo = type.value === 'pond' ? row.value.weight / 2.2046244202 : type.value === 'jing' ? row.value.weight / 2 : row.value.weight // convert to float
  // inData.kilo = row.value.kilo
  inData.portfolio = row.value.portfolio
  inData.dowjones = row.value.dowjones
  inData.nasdaq = row.value.nasdaq
  inData.sp500 = row.value.sp500
  inData.ftse100 = row.value.ftse100
  inData.nikkei = row.value.nikkei
  if (row.value.note !== null) {
    if (row.value.note.indexOf('PNotes:') === 0) inData.note = row.value.note
    else inData.note = 'PNotes: ' + row.value.note
  }
  return inData
}
</script>

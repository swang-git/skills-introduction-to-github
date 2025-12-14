<template>
<q-dialog v-model="opened" :transition-show="action==='add' ? 'slide-right' : 'rotate'" persistent>
  <q-layout container class="bg-teal-10 fixed-center" style="height:250px;width:310px">
    <LayoutHeader tit="Upd / Addd / Del" @do-action="doAction" />
    <LayoutFooter :act=action tit="NOTE_LINK" @do-action="doAction" />
    <q-page-container>
      <q-page>
        <DateTimePicker style="width:300px" label="TODO Date" :dateTime="row.datetime" @upd-dt="updDate" txsz="text-h6" />
        <NumInput :obj="row" label="Volume" icon="V" iconSize="28px" iColor="lime" rightIcon showRight />
      </q-page>
    </q-page-container>
  </q-layout>
</q-dialog>
<LnkInput @upd-link="updLink" />
<NotePad @save-details="saveNote" />
<ConfirmDialog @user-confirmed="delFromDB" />
</template>
<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'
import { libFunctions } from 'src/composables/libFunctions'
import { dayFunctions } from 'src/composables/dayFunctions'
import { axiosFunctions } from 'src/composables/axiosFunctions'
import ConfirmDialog from '../src/components/ConfirmDialog'
import NumInput from '../src/components/NumInput'
import LnkInput from '../src/components/LnkInput'
import NotePad from '../src/components/NotePad'
import DateTimePicker from '../src/components/DateTimePicker'
import LayoutHeader from '../src/components/LayoutHeader'
// import LayoutFooter from '../src/components/LayoutFooter'
import LayoutFooter from '../src/components/LayoutFooter'

//== data
const { isDesk, screenwidth } = libFunctions()
const { yyyymmddHHMM } = dayFunctions()
const { paxios, gaxios } = axiosFunctions()
const opened = ref(false)
const forReminder = ref(false)
const row = ref(null)
const action = ref(null)
var rowOrig = null

//== main ==
console.log('-ST-pfdar')
emitter.on('open-pfdar', (row, act) => openIt(row, act))
const emit = defineEmits(['added-row', 'upded-row', 'deled-row'])
// emitter.on('memo-add', (x) => emit('added-row', x))
// emitter.on('memo-upd', (x) => emit('upded-row', x))
// emitter.on('memo-del', (x) => emit('deled-row', x))

//== function sections
function updLink (lnks) {
  row.value.link = lnks.join('@')
  console.log(`-fn-updLink link=${row.value.link}`)
}
function openIt (rw, act) {
  console.log(`-fn- pfdar.openIt act=${act}`, rw)
  action.value = act
  row.value = rw
  // row.value.datetime = '2025-11-17 23:00'
  if (act == 'del') return del()
  opened.value = true
}
function doAction (act) {
  if (act === 'add') add()
  else if (act === 'upd') upd()
  else if (act === 'del') del()
  else if (act === 'lnk') lnk()
  else if (act === 'msg') msg()
}
function saveNote (val) { row.value.note = val }
// function saveLnk (val) { row.value.link = val }
function msg () {
  // console.log('-CK-fn-msg', row)
  emitter.emit('open-NotePad', row.value.note)
}
function lnk () {
  // console.log('-CK-fn-lnk', row.value.link)
  let lnks = row.value.link
  if (lnks == null) {
    lnks = []
  } else if (lnks.indexOf('@') >=0 ) {
    lnks = lnks.split('@')
  } else {
    lnks = [lnks]
  }
  // console.log('-fn-lnk.openIt, lnks[]', lnks)
  emitter.emit('open-LnkInput', lnks)
}
function add () {
  row.value.swProp = screenwidth/13
  row.value.datetime = yyyymmddHHMM(new Date())
  console.log('-fn-add', row.value)
  const path = process.env.API + '/pfcheck/add'
  const data = row.value
  // data.link = Array.isArray(row.value.link) ? row.value.link.join('@') : row.value.link
  data.note = row.value.note
  console.log(`-fn-add vol=${data.vol} datetime=${data.datetime} note=${data.note}`)
  paxios(path, data)
  opened.value = false
}
function upd () {
  console.log('-fn-upd', row.value)
  const path = process.env.API + '/pfcheck/upd'
  const data = {}
  data.swProp = screenwidth/13
  data.id = row.value.id
  data.datetime = row.value.datetime
  data.vol = row.value.vol
  data.note = row.value.note
  console.log(`-fn-upd vol=${data.vol} datetime=${data.datetime} note=${data.note}`)
  paxios(path, data)
  opened.value = false
}
function del () {
  const tit = 'Delete PfCheck'
  const msg = `Please confirm deleting pfcheck with vol=${row.value.vol}`
  emitter.emit('open-ConfirmDialog', tit, msg)
}
function delFromDB () {
console.log('-fn-del', row.value.id, row.value.vol)
  const path = process.env.API + '/pfcheck/del/' + row.value.id
  // const data = row
  // paxios(path, data)
  gaxios(path)
  opened.value = false
}
function updDate (dt) {
  row.value.datetime = dt
}
</script>

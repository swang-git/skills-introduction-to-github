<template>
<q-dialog v-model="opened" persistent :maximized="isIM" :full-height="isIM">
  <q-layout container style="height:320px;max-width:555px" class="bg-teal-10" >
    <LayoutHeader tit="英汉字典" @do-action="doAction" />
    <LayoutFooter tit="NOTE_LINK" @do-action="doAction" />
    <div class="q-px-xs" style="height:320px">
      <DateTimePicker label="Created Date and Time" class="q-px-xs" style="padding-top:66px" :date-time="row.datetime" txsz="text-h6" @upd-dt="updDateTime" />
      <TxtInput class="col-12 q-pt-sm" :obj="row" label="chinese word" icon="中" iColor="red-6" />
      <TxtInput class="col-12 q-pt-sm" :obj="row" label="english word" icon="英" iColor="green" />
    </div>
  </q-layout>
  <NotePad @save-details="saveNote" />
  <LnkInput @upd-link="saveLnk" />
  <ConfirmDialog @user-confirmed="userConfirmed" />
</q-dialog>
</template>
<script setup>
import { ref, onMounted } from 'vue'
import emitter from 'tiny-emitter/instance'
import { libFunctions } from '../src/composables/libFunctions'
const { buildApp, isIM, isDesk, palist, getLineBackground } = libFunctions()
import { axiosFunctions } from '../src/composables/axiosFunctions'
const { gaxios, paxios } = axiosFunctions()

import NotePad from '../src/components/NotePad'
import LnkInput from '../src/components/LnkInput'

import DateTimePicker from '../src/components/DateTimePicker'
import TxtInput from '../src/components/TxtInput'
import LayoutHeader from '../src/components/LayoutHeader'
import LayoutFooter from '../src/components/LayoutFooter'
import ConfirmDialog from '../src/components/ConfirmDialog'

defineExpose({ openIt })
const emit = defineEmits(['deled-word', 'added-word', 'upded-word'])

const file = ref(null)
const opened = ref(false)
const dense = ref(false)
var id = ref(-1)
const row = ref({datetime:null, details: null})

console.log('-ST-UserInput')

function closeIt () {
  opened.value = false
}
function updDateTime (val) {
  // console.log('-fn-updDateTime', val)
  row.value.datetime = val
}
function userConfirmed () {
  console.log('user confirmed to delete row', row.value)
  opened.value = false
  delFromDB()
  emit('deled-word')
}
function openIt (rw) {
  row.value = JSON.parse(rw)
  // console.log('-fn- UserInput.openIt', row.value)
  opened.value = true
}
function getInputData () {
  var inData = {}
  inData.id = row.value.id
  inData.datetime = row.value.datetime
  inData.english = row.value.english
  inData.chinese = row.value.chinese
  inData.note = row.value.note
  // inData.note = row.value.note === '' ? null : row.value.note
  inData.lnks = Array.isArray(row.value.lnks) ? row.value.lnks.join('@') : row.value.lnks
  return inData
}
function doAction (act) {
  console.log(`-fn-doAction act=${act}`)
  // act === 'add' ? add() : act === 'upd' ? upd() : act === 'msg' ? showMsg() : act === 'lnk' ? showLink() : delFromDB()
  return act==='add' ? add() : act==='upd' ? upd() : act==='msg' ? msg() : act==='lnk' ? lnk() : act==='del' ? del() : null
}
function saveNote (note) {
  console.log(`-fn-saveNote`)
  row.value.note = note
}
function saveLnk (links) {
  console.log(`-CK-saveLnk`, links)
  row.value.lnks = links.join('@')
}
function lnk () {
  console.log('-fn-lnk', row.value.lnks)
  let lnks = row.value.lnks
  if (lnks == null) {
    lnks = []
  } else if (lnks.indexOf('@') >=0 ) {
    lnks = lnks.split('@')
  } else {
    lnks = [lnks]
  }
  console.log(`-CK-lnks=${lnks}`)
  // refs.lnk.openIt(lnks)
  emitter.emit('open-LnkInput', lnks)
}
function msg () {
  // refs.NotePad.openIt(row.value.note)
  emitter.emit('open-NotePad', row.value.note)
}
function delFromDB () {
  var inData = { id: row.value.id }
  const path = process.env.API + '/dictionary/del'
  paxios(path, inData)
}
function del () {
  console.log(`-CK-del()`)
  const tit = 'Delete dictionary record (' + row.value.id + ')' 
  const msg = 'Please confirm to delete record ' + ' "' + row.value.chinese +'" at ' + row.value.datetime.yyyymmddHHMM() + ' permanently ?'
  emitter.emit('open-ConfirmDialog', tit, msg)
}
function upd () {
  const inData = getInputData()
  // console.log('inData', inData)
  const path = process.env.API + '/dictionary/upd'
  paxios(path, inData)
  opened.value = false
  const lnks = inData.lnks
  if (lnks == null) return emit('upded-word', inData)
  inData.lnk = getLnk(lnks)
  emit('upded-word', inData)
}
function add () {
  const inData = getInputData()
  inData.id = -1
  const path = process.env.API + '/dictionary/add'
  paxios(path, inData)
  opened.value = false
  const lnks = inData.lnks
  if (lnks == null) return emit('added-word', inData)
  inData.lnk = getLnk(lnks)
  emit('added-word', inData)
}
function getLnk (lnks) {
  if (lnks.indexOf('@') >= 0) {
    const links = lnks.split('@')
    let lnk = '<ol>'
    links.forEach(link => {
      const linkName = link.replace('/_|-/', ' ')
      lnk += '<li><a href="' + link + '" target="_blank">' + linkName + '</a></li>'
    })
    lnk += '</ol>'
    return lnk
  } else {
    const link = lnks
    const linkName = link.replace('/_|-/', ' ')
    return '<a href="' + link + '" target="_blank">' + linkName + '</a>'
  }
}
</script>

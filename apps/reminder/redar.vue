<template>
<!-- <div> -->
<q-dialog v-model="opened" transition-show="slide-right" persistent>
  <q-layout container class="bg-teal-10 fixed-center" :style="{ height:isDesk ? '300px' : '600px', width:isDesk ? '488px' : '' }">
    <layout-header tit="Update/Create/Delete Reminder" @do-action="doAction" />
    <layout-footer :act="action" tit="NOTE_LINK" @do-action="doAction" />
    <q-page-container v-if="isDesk" class="">
      <q-page>
        <div class="row">
          <DatePicker class="col-7 q-pl-xs q-pt-xs" label="TODO Date" :date="row.due_date" @upd-date="updDate" txsz="text-h6" />
          <NumInput class="col-5" :obj="row" label="Recursive" icon="event_repeat" iColor="green" :prefix=null :rightIcon="true" />
        </div>
        <TxtInput class="col-12" :obj="row" label="Tag" icon="label_important" iColor="amber-9" :rightIcon="true" />
        <TxtInput class="col-12" :obj="row" label="Message" icon="message" iColor="cyan-2" :rightIcon="true" />
      </q-page>
    </q-page-container>
    <q-page-container v-else class="q-ma-xs">
      <q-page>
        <DatePicker class="col-12 q-pt-xs" label="TODO Date" :date="row.due_date" @upd-date="updDate" txsz="text-h6" />
        <NumInput class="col-12" :obj="row" label="Recursive" icon="event_repeat" iColor="green" :prefix=null />
        <TxtInput class="col-12" :obj="row" label="Tag" icon="label_important" iColor="amber-9" />
        <TxtInput class="col-12" :obj="row" label="Message" icon="message" iColor="cyan-2" />
      </q-page>
    </q-page-container>
  </q-layout>
</q-dialog>
<LnkInput @upd-link="updLink" />
<NotePad @save-details="saveDetails" />
<ConfirmDialog @user-confirmed="delFromDB" />
<!-- </div> -->
</template>
<script setup>
import emitter from 'tiny-emitter/instance'
import { ref } from 'vue'
import TxtInput from '../src/components/TxtInput'
import NumInput from '../src/components/NumInput'
import layoutHeader from '../src/components/LayoutHeader'
import layoutFooter from '../src/components/LayoutFooter'
import DatePicker from '../src/components/DatePicker'
import LnkInput from '../src/components/LnkInput'
import NotePad from '../src/components/NotePad'
import ConfirmDialog from '../src/components/ConfirmDialog'

import { libFunctions } from '../src/composables/libFunctions'
import { axiosFunctions } from '../src/composables/axiosFunctions'
import { dayFunctions } from '../src/composables/dayFunctions'
const { chwk1, chwk2 } = dayFunctions()
const { gaxios, paxios } = axiosFunctions()
const { isIM, isDesk, buildApp, palist } = libFunctions()
const opened = ref(false)
const action = ref(null)
const row = ref({ datetime: null })
const rowOrig = ref({})

console.log('-ST-redar')
emitter.on('open-redar', (row, act) => openIt(row, act))

const emit = defineEmits ([
  'added-row', 'upded-row', 'del-confirmed', 'upd-link', 'save-details'
])

emitter.on('reminder-add', (da) => emit('added-row', da.row))
emitter.on('reminder-upd', (da) => emit('upded-row', da.row))
emitter.on('reminder-del', (da) => emit('del-confirmed', da.row))
emitter.on('reminder-del-row', (rw) => { row.value = rw; del() })

function openIt (rw, act) {
  // console.log('-CK-fn-openIt', rw)
  action.value = act
  row.value = rw
  opened.value = true
}
function updLink (lnks) {
  row.value.link = lnks.join('@')
  console.log('-fn-updLink', row.value.link)
}
function doAction (act) {
  if (act === 'add') add()
  else if (act === 'upd') upd()
  else if (act === 'del') del()
  else if (act === 'lnk') lnk()
  else if (act === 'msg') msg()
}
function saveDetails (val) {
  row.value.details = val
  console.log('-fn-saveDetails', row.value.details)
}
function msg () {
  // console.log('-CK-fn-msg', row.value)
  emitter.emit('open-NotePad', row.value.details)
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
  // console.log('-CK-fn-lnk.openIt, lnks', lnks)
  emitter.emit('open-LnkInput', lnks)
}
function add () {
  console.log('-fn-add', row.value)
  const path = process.env.API + '/reminder/add'
  paxios(path, row.value)
  opened.value = false
}
function upd () {
  console.log('-fn-upd', row.value)
  const path = process.env.API + '/reminder/upd'
  const inData = {}
  inData.id = row.value.id
  inData.due_date = row.value.due_date.substring(0, 10)
  inData.tag = row.value.tag
  inData.message = row.value.message
  inData.details = row.value.details
  inData.link = row.value.link
  inData.recursive = row.value.recursive === 0 ? null : row.value.recursive
  paxios(path, inData)
  opened.value = false
}
function del () {
  const tit = 'Delete Reminider ' + row.value.tag
  const msg = 'Delete Reminider ' + row.value.message
  emitter.emit('open-ConfirmDialog', tit, msg)
}
function delFromDB () {
  console.log('-fn-del', row.value.id, row.value.tag)
  const path = process.env.API + '/reminder/del'
  paxios(path, row.value)
  opened.value = false
}
function updDate (val) {
  row.value.due_date = val
}
</script>

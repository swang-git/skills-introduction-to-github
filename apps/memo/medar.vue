<template>
<q-dialog v-model="opened" transition-show="slide-right" persistent>
  <q-layout container class="bg-teal-10 fixed-center" :style="{ height:isDesk ? '240px' : '600px', width:isDesk ? '510px' : '' }">
    <LayoutHeader tit="Update/Create/Delete memo" @do-action="doAction" />
    <LayoutFooter :act=action tit="NOTE_LINK" @do-action="doAction" />
    <q-page-container class="">
      <q-page>
        <div class="row">
          <DateTimePicker style="width:300px" label="TODO Date" :dateTime="row.date" @upd-dt="updDate" txsz="text-h6" />
          <div v-if="row.reminder" class="text-h6 text-cyan-2 q-pt-md q-pl-xs"><span v-if="isDesk">for Reminder</span></div>
          <div v-else class="text-h6 text-teal-9 q-pt-md q-pl-xs">for Reminder</div>
          <q-item clickable @click="row.reminder=!row.reminder">
            <q-item-section class="q-pl-xs">
              <q-btn round color="cyan-9" class="text-yellow" glossy>
                <q-icon name="schedule" color="yellow-9" size="32px" style="padding:0 1.5px 2.5px 0"/>
              </q-btn>
            </q-item-section>
          </q-item>
        </div>
        <TxtInput :obj="row" label="Tag" icon="message" iColor="cyan-2" :rightIcon="true" />
      </q-page>
    </q-page-container>
  </q-layout>
</q-dialog>
<LnkInput @upd-link="updLink" />
<NotePad @save-details="saveDetails" />
<ConfirmDialog @user-confirmed="delFromDB" />
</template>
<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'
import { libFunctions } from 'src/composables/libFunctions'
import { axiosFunctions } from 'src/composables/axiosFunctions'
import ConfirmDialog from '../src/components/ConfirmDialog'
import TxtInput from '../src/components/TxtInput'
import LnkInput from '../src/components/LnkInput'
import NotePad from '../src/components/NotePad'
import DateTimePicker from '../src/components/DateTimePicker'
import LayoutHeader from '../src/components/LayoutHeader'
// import LayoutFooter from '../src/components/LayoutFooter'
import LayoutFooter from '../src/components/LayoutFooter'

//== data
const { isDesk, screenwidth } = libFunctions()
const { paxios, gaxios } = axiosFunctions()
const opened = ref(false)
const forReminder = ref(false)
const row = ref(null)
const action = ref(null)
var rowOrig = null

//== main ==
console.log('-ST-medar')
emitter.on('open-medar', (row, act) => openIt(row, act))
const emit = defineEmits(['added-row', 'upded-row', 'deled-row'])
// emitter.on('memo-add', (x) => emit('added-row', x))
// emitter.on('memo-upd', (x) => emit('upded-row', x))
// emitter.on('memo-del', (x) => emit('deled-row', x))

//== function sections
function updLink (lnks) {
  row.value.link = lnks.join('@')
  console.log('-fn-updLink', row.value.link)
}
function openIt (rw, act) {
  console.log(`-fn- medar.openIt act=${act}`, rw)
  action.value = act
  row.value = rw
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
function saveDetails (val) { row.value.details = val }
function saveLnk (val) { row.value.link = val }
function msg () {
  // console.log('-CK-fn-msg', row)
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
  // console.log('-fn-lnk.openIt, lnks[]', lnks)
  emitter.emit('open-LnkInput', lnks)
}
function add () {
  console.log('-fn-add', row.value)
  row.value.swProp = screenwidth/13
  const path = process.env.API + '/memo/add'
  const data = row.value
  data.link = Array.isArray(row.value.link) ? row.value.link.join('@') : row.value.link
  paxios(path, data)
  opened.value = false
}
function upd () {
  console.log('-fn-upd', row.value)
  const path = process.env.API + '/memo/upd'
  const data = {}
  data.swProp = screenwidth/13
  data.id = row.value.id
  data.date = row.value.date
  data.tag = row.value.tag
  data.reminder = row.value.reminder
  data.details = row.value.details
  data.link = Array.isArray(row.value.link) ? row.value.link.join('@') : row.value.link
  data.recursive = row.value.recursive === 0 ? null : row.value.recursive
  paxios(path, data)
  opened.value = false
}
function del () {
  const tit = 'Delete Memo'
  const msg = `Please confirm deleting memo with tag=${row.value.tag}`
  emitter.emit('open-ConfirmDialog', tit, msg)
}
function delFromDB () {
console.log('-fn-del', row.value.id, row.value.tag)
  const path = process.env.API + '/memo/del/' + row.value.id
  // const data = row
  // paxios(path, data)
  gaxios(path)
  opened.value = false
}
function updDate (val) {
  row.value.date = val
}
</script>

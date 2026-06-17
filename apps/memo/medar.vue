<template>
<!-- <q-dialog v-model="opened" transition-show="slide-right" persistent> -->
<q-dialog v-model="opened" :transition-show="action=='upd' ? 'rotate' : 'slide-right'" persistent>
  <q-layout container class="bg-teal-10 fixed-center" :style="{ height:isDesk ? '240px' : '240px', width:isDesk ? '510px' : '' }">
    <LayoutHeader tit="Update/Create/Delete memo" @do-action="doAction" />
    <LayoutFooter :act=action tit="NOTE_LINK" @do-action="doAction" />
    <q-page-container class="">
      <q-page>
        <div class="row">
            <!-- <DateTimePicker style="width:40.4%" class="q-pt-sm" label="Created Date and Time" :date-time="row.datetime" @upd-dt="updDateTime" txsz="text-h6" /> -->
            <!-- <DateTimePicker style="width:83%" label="TODO Date" :dateTime="row.date" @upd-dt="updDate" txsz="text-h6" /> -->
            <DateTimePicker v-if="isDesk" style="width:60%" label="Match Starting Date Time" :dateTime="row.date" @upd-dt="updDate" txsz="text-h6" />
            <DateTimeIMPicker v-else style="width:72%" label="Match Starting Date Time" txsz="text-h6" :dateTime="row.date" @upd-dt="updDate" />
          <div v-if="row.reminder" class="text-h6 text-cyan-2 q-pt-md q-pl-xs">
            <span v-if="isDesk">for Reminder</span>
            <span v-else>RD</span>
          </div>
          <div v-else class="text-h6 text-teal-9 q-pt-md q-pl-xs"></div>
          <q-item clickable @click="row.reminder=!row.reminder">
            <q-item-section class="q-pl-">
              <q-btn round color="cyan-9" class="text-yellow" glossy>
                <q-icon name="schedule" color="lime" size="32px" style="padding:0 1.5px 2.5px 0" />
              </q-btn>
            </q-item-section>
          </q-item>
        </div>
        <!-- <TxtInput :obj="row" label="Tag" icon="message" iColor="lime-2" :rightIcon="true" /> -->
        <TxtInput class="col-12" :obj="row" label="Tag" icon="message" iColor="lime" :rightIcon="true" @click="openSelection('message', 'Tag', tagOpt)" />
      </q-page>
    </q-page-container>
  </q-layout>
</q-dialog>
<LnkInput @upd-link="updLink" />
<NotePad @save-details="saveDetails" />
<ConfirmDialog @user-confirmed="delFromDB" />
<TxtPad @upd-selected-opt="updSelectedOpt" />
</template>
<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'
import { libFunctions } from '../src/composables/libFunctions'
import { axiosFunctions } from '../src/composables/axiosFunctions'
import ConfirmDialog from '../src/components/ConfirmDialog.vue'
import TxtInput from '../src/components/TxtInput.vue'
import LnkInput from '../src/components/LnkInput.vue'
import NotePad from '../src/components/NotePad.vue'
import DateTimePicker from '../src/components/DateTimePicker.vue'
import DateTimeIMPicker from '../src/components/DateTimeIMPicker.vue'
import LayoutHeader from '../src/components/LayoutHeader.vue'
// import LayoutFooter from '../src/components/LayoutFooter'
import LayoutFooter from '../src/components/LayoutFooter.vue'
import TxtPad from '../src/components/TxtPad.vue'

//== data
const { isDesk, screenwidth, ENV_DEV } = libFunctions()
const { paxios, gaxios } = axiosFunctions()
const opened = ref(false)
const forReminder = ref(false)
const row = ref(null)
const action = ref(null)
var rowOrig = null
const tagOpt = ref([])

//== main ==
console.log('-ST-medar')
emitter.on('open-medar', (row, act) => openIt(row, act))
const emit = defineEmits(['added-row', 'upded-row', 'deled-row'])
// emitter.on('memo-add', (x) => emit('added-row', x))
// emitter.on('memo-upd', (x) => emit('upded-row', x))
// emitter.on('memo-del', (x) => emit('deled-row', x))

//== function sections
function updSelectedOpt (model, txt) {
  console.log(`-fn-updSelectedOpt model=${model} selectedOpt=${txt}`)
  if (model == 'Tag') row.value.tag = txt
  // else if (model == 'Breakfast') row.value.breakfast = txt
}
function openSelection (icon, model, opts) {
  console.log(`-fn-openSelection`, opts)
  emitter.emit('open-SelRevOption', icon, model, opts)
}
function updLink (lnks) {
  row.value.link = lnks.join('@')
  console.log(`-fn-updLink link=${row.value.link}`)
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
  const path = ENV_DEV + '/memo/add'
  const data = row.value
  data.link = Array.isArray(row.value.link) ? row.value.link.join('@') : row.value.link
  paxios(path, data)
  opened.value = false
}
function upd () {
  console.log('-fn-upd', row.value)
  const path = ENV_DEV + '/memo/upd'
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
  const path = ENV_DEV + '/memo/del/' + row.value.id
  // const data = row
  // paxios(path, data)
  gaxios(path)
  opened.value = false
}
function updDate (val) {
  row.value.date = val
}
</script>

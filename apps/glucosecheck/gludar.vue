<template>
<q-dialog v-model="opened" :transition-show="action=='upd' ? 'rotate' : 'slide-right'" persistent fullWidth :maximized="isIM">
  <q-layout container class="bg-teal-10" :style="isDesk ? { 'height':'660px' } : { 'height':'670px' }">
    <LayoutHeader tit="Glucose Daily Check" @do-action="doAction" />
    <LayoutFooter :act=action tit="TIT_GLUCOSE" @do-action="doAction" class="q-pb-"/>
    <q-page-container v-if="isDesk">
      <q-page>
        <div class="row" style="width:102.3%">
          <DateTimePicker style="width:40.4%" class="q-pt-sm" label="Created Date and Time" :date-time="row.datetime" @upd-dt="updDateTime" txsz="text-h6" />
          <q-chip style="max-width:7.7%;margin-left:-3px" class="text-h6 text-bold text-cyan-2 bg-teal-10 q-mt-md">{{ (row.glucose/18).toFixed(1) }}</q-chip>
          <NumInput style="width:25%" class="q-pt-sm" :obj="row" label="Sugar Level" icon="bloodtype" iColor="red" :rightIcon="true" :showRight="true" @click="showNumPad('GL')" />
          <TxtInput class="q-mt-xs" style="width:24%" :obj="row" label="Check Type" icon="bloodtype" iColor="green-9" @click="openSelection('bloodtype', 'Check Type', tyOpt)" />
        </div>
        <div class="row" style="width:99.1%">
          <NumInput class="col-3" :obj="row" label="Hi Blood Pressure" icon="tire_repair" iColor="pink-3" :rightIcon="true" :showRight="true" />
          <NumInput class="col-3" :obj="row" label="Lo Blood Pressure" icon="tire_repair" iColor="blue-3" :rightIcon="true" :showRight="true" />
          <NumInput class="col-3" :obj="row" label="Heart Pulse" icon="monitor_heart" iColor="red" :rightIcon="true" :showRight="true" />
          <NumInput class="col-3" :obj="row" label="Weight" mask="#.#" icon="重" iconSize="30" iColor="cyan-2" :rightIcon="true" :showRight="true" />
        </div>
        <TxtInput class="col-12" :obj="row" label="Food" icon="ramen_dining" iColor="teal-4" :rightIcon="true" @click="openSelection('ramen_dining', 'Food', foOpt)" />
        <TxtInput class="col-12" :obj="row" label="Exercise" icon="run_circle" iColor="pink-4" :rightIcon="true" @click="openSelection('sports_golf', 'Exercise', exOpt)" />
        <TxtInput class="col-12" :obj="row" label="Breakfast" icon="egg" iColor="brown-6" :rightIcon="true" @click="openSelection('egg', 'Breakfast', brOpt)" />
        <TxtInput class="col-12" :obj="row" label="Lunch" icon="lunch_dining" iColor="yellow-9" :rightIcon="true" @click="openSelection('lunch_dining', 'Lunch', luOpt)" />
        <TxtInput class="col-12" :obj="row" label="Dinner" icon="dinner_dining" iColor="indigo-3" :rightIcon="true" @click="openSelection('dinner_dining', 'Dinner', diOpt)" />
        <TxtInput class="col-12" :obj="row" label="Note" icon="note" iColor="cyan-3" :rightIcon="true" @click="openSelection('note', 'Note', diOpt)" />
        <div class="row">
          <TxtInput class="col-6" :obj="row" label="Drink" icon="local_bar" iColor="green" :rightIcon="true" @click="openSelection('local_bar', 'Drink', drOpt)" />
          <TxtInput class="col-6" :obj="row" label="Fruit" icon="apple" iColor="green-3" :rightIcon="true" @click="openSelection('apple', 'Fruit', frOpt)" />
        </div>
      </q-page>
    </q-page-container>
    <!-- Phone session -->
    <q-page-container v-else class="q-ma-xs">
      <DateTimeIMPicker class="q-pa-xs" label="Match Starting Date Time" txsz="text-h6" :dateTime="row.datetime" @upd-dt="setDateTime" />
      <div class="row">
        <NumInput style="width:36%" :obj="row" label="Blood Glucose Level" icon="bloodtype" iColor="red" @click="openNumPad()" />
        <div style="width:18%"><q-chip class="text-h6 text-cyan-2 bg-teal-10 q-mt-sm">{{ (row.glucose/18).toFixed(1) }}</q-chip></div>
        <NumInput style="width:44.3%" :obj="row" label="Weight" icon="重" icon-size="24px" iColor="yellow" mask="#.#"/>
      </div>
      <div class="row">
        <TxtInput style="width:55%" :obj="row" label="Blood Pressure" icon="tire_repair" iColor="pink-3" @click="openNumPad('BP')" />
        <TxtInput style="width:44.3%" :obj="row" label="Check Type" icon="bloodtype" iColor="lime" @click="openSelection('bloodtype', 'Check Type', tyOpt)" />
      </div>
      <TxtInput class="col-12" :obj="row" label="Food" icon="ramen_dining" iColor="green" :rightIcon="true" />
      <TxtInput class="col-12" :obj="row" label="Exercise" icon="run_circle" iColor="pink-4" :rightIcon="true" @click="openSelection('sports_golf', 'Exercise', exOpt)" />
      <TxtInput class="col-12" :obj="row" label="Breakfast" icon="egg" iColor="brown-6" :rightIcon="true" @click="openSelection('egg', 'Breakfast', brOpt)" />
      <TxtInput class="col-12" :obj="row" label="Lunch" icon="lunch_dining" iColor="yellow-9" :rightIcon="true" @click="openSelection('lunch_dining', 'Lunch', luOpt)" />
      <TxtInput class="col-12" :obj="row" label="Dinner" icon="dinner_dining" iColor="indigo-3" :rightIcon="true" @click="openSelection('dinner_dining', 'Dinner', diOpt)" />
      <TxtInput class="col-12" :obj="row" label="Note" icon="note" iColor="cyan-3" :rightIcon="true" @click="openSelection('note', 'Note', diOpt)" />
      <div class="row">
        <TxtInput class="col-6" :obj="row" label="Drink" icon="local_bar" iColor="green" :rightIcon="true" @click="openSelection('local_bar', 'Drink', drOpt)" />
        <TxtInput class="col-6" :obj="row" label="Fruit" icon="apple" iColor="green-3" :rightIcon="true" @click="openSelection('apple', 'Fruit', frOpt)" />
      </div>
    </q-page-container>
  </q-layout>
</q-dialog>
<NumPad @set-num="setNum" />
<NumPadAuto @sugar-level="setSugarLevel" @blood-pressure="setBloodPressure" />
<ConfirmDialog @user-confirmed="delFromDB" />
<SelRevOption @selected-option="setSelectedOpt" />
<TxtPad @upd-selected-opt="updSelectedOpt" />
<gludarInfo :row="row" />
</template>
<script setup>
import { ref, onMounted } from 'vue'
import emitter from 'tiny-emitter/instance'
import ConfirmDialog from '../src/components/ConfirmDialog.vue'
import TxtInput from '../src/components/TxtInput.vue'
// import TxaInput from '../src/components/TxaInput.vue'
import NumInput from '../src/components/NumInput.vue'
import LayoutHeader from '../src/components/LayoutHeader.vue'
import LayoutFooter from '../src/components/LayoutFooter.vue'
import DateTimePicker from '../src/components/DateTimePicker.vue'
import DateTimeIMPicker from '../src/components/DateTimeIMPicker.vue'
import NumPadAuto from '../src/components/NumPadAuto.vue'
import NumPad from '../src/components/NumPad.vue'
import SelRevOption from '../src/components/SelRevOption.vue'
import TxtPad from '../src/components/TxtPad.vue'
import gludarInfo from './gludar_m_info.vue'

import { axiosFunctions } from '../src/composables/axiosFunctions'
const { gaxios, paxios } = axiosFunctions()
import { dayFunctions } from '../src/composables/dayFunctions'
const { yyyymmddHHMM, yyyymmdd } = dayFunctions()
import { libFunctions } from '../src/composables/libFunctions'
const { isDesk, isIM, ENV_DEV } = libFunctions()

const opened = ref(false)
const action = ref(null)
const dtTimeDone = ref(false)
const row = ref({ datetime: null })
const emit = defineEmits(['close-expand'])
var rowOrig = {}

const exOpt = ref([])
const brOpt = ref([])
const luOpt = ref([])
const diOpt = ref([])
const drOpt = ref([])
const frOpt = ref([])
const foOpt = ref([])
const tyOpt = [{value: 1, label: '空腹'}, {value: 2, label: '餐一'} , {value: 3, label: '餐二'}, {value: 4, label: '餐三'} , {value: 5, label: '随机'}]


console.log('-ST-gludar')
emitter.on('open-gludar', (rw, act, exOpt, brOpt, luOpt, diOpt, drOpt, frOpt, foOpt) => openIt(rw, act, exOpt, brOpt, luOpt, diOpt, drOpt, frOpt, foOpt))

//== function section
function setDateTime (dt) {
  row.value.datetime = dt
  dtTimeDone.value = true
  // if (isLocal()) return
  const date = dt.substring(0, 10)
  console.log(`dt time=${row.value.purchasedon}, check if there are other appointments on the date=${date}`)
  // const path = process.env.API + '/golf/checkReminder/' + date
  // gaxios(path)
}

function setNum (flg, n) {
  console.log(`-fn-setNum flag=${flg} n=${n}`)
  if (flg == 'GL') {
    row.value.glucose = n
    showNumPad('WT', '当日体重')
  } else if (flg == 'WT') {
    row.value.weight = n
  }
  // opened.value = false
}
function updSelectedOpt (model, txt) {
  console.log(`-fn-updSelectedOpt model=${model} selectedOpt=${txt}`)
  if (model == 'Exercise') row.value.exercise = txt
  else if (model == 'Breakfast') row.value.breakfast = txt
  else if (model == 'Lunch') row.value.lunch = txt
  else if (model == 'Dinner') row.value.dinner = txt
  else if (model == 'Drink') row.value.drink = txt
  else if (model == 'Fruit') row.value.fruit = txt
  else if (model == 'Food') row.value.food = txt
  else if (model == 'Note') row.value.note = txt
}
function setSelectedOpt (model, opt) {
  console.log(`-fn-setSelectedOpt model=${model} selectedOpt=${opt.label}`)
  if (model == 'Exercise') row.value.exercise = opt.label
  else if (model == 'Breakfast') row.value.breakfast = opt.label
  else if (model == 'Lunch') row.value.lunch = opt.label
  else if (model == 'Dinner') row.value.dinner = opt.label
  else if (model == 'Drink') row.value.drink = opt.label
  else if (model == 'Fruit') row.value.fruit = opt.label
  else if (model == 'Check Type') row.value.type = opt.label
  else if (model == 'Food') row.value.food = opt.label
  else if (model == 'Notes') row.value.note = opt.label
}
function openSelection (icon, model, opts) {
  console.log(`-fn-openSelection`, opts)
  emitter.emit('open-SelRevOption', icon, model, opts)
}
function setSugarLevel (x) {
  console.log(`-fn-setSugarLevel=${x}`)
  row.value.glucose = x
}
function showNumPad (flg) {
  console.log(`-fn-showNumPadSL flag=${flg}`)
  if (flg == "GL") return emitter.emit('open-num-pad', flg, '血糖测试')
  else return emitter.emit('open-num-pad', flg, '当日体重')
}
function openNumPad (flag) {
  console.log(`-fn-openNumPadSL flag=${flag}`)
  if (isDesk) return
  if (flag == 'BP') return emitter.emit('open-num-pad-auto', '输入血压', flag, null)
  else return emitter.emit('open-num-pad-auto', '血糖测试', 60, 300)
}
function openIt (rw, act, exop, brop, luop, diop, drop, frop, foop) {
  console.log(`-fn-gludar.openIt act=${act}`, rw)
  action.value = act
  exOpt.value = exop
  brOpt.value = brop
  luOpt.value = luop
  diOpt.value = diop
  drOpt.value = drop
  frOpt.value = frop
  foOpt.value = foop

  row.value = rw
  if (act == 'del') return del()
  if (act == 'add') {
    let dt = new Date()
    // dt.setSeconds(dt.getSeconds() - 20)
    rw.datetimeOrig = rw.datetime
    // rw.datetime = yyyymmdd(dt) + ' ' + '08:15'
    rw.datetime = yyyymmddHHMM(dt)
  }
  if (rw.bloodPressure != null) {
    let x = rw.bloodPressure.split(' / ')
    row.value.hibp = x[0]
    row.value.lobp = x[1]
    row.value.hpls = x[2]
  }
  opened.value = true
  // openNumPad()
}
function adupNote () {
  console.log(`-fn-adupNote`)
  emitter.emit('open-TxtPad', 'Note', row.value.note, 'Edit Notes')
}
function doAction (act) {
  if (act === 'add') add()
  else if (act === 'upd') upd()
  else if (act === 'del') del()
  else if (act === 'info') showInfo()
  else if (act === 'note') adupNote()
}

function setBloodPressure (x) {
  if (row.value.bloodPressure == null) return
  // var re1 = new RegExp('/', 'g')
  // var re2 = /\s+/g
  if (isDesk) {
    row.value.bloodPressure = row.value.hibp + ' / '  + row.value.lobp + ' / ' + row.value.hpls
  } else {
    // let x = row.value.bloodPressure
    // let xx = x.replace(re1, ' ')
    // row.value.bloodPressure = xx.replace(re2, ' / ')
    row.value.bloodPressure = x
  }
  console.log(`bloodPressure=[${row.value.bloodPressure}]`)
}
function showInfo () {
  console.log('%c-fn-showInfo', 'color:purple;font-size:16px', row.value)
  emitter.emit('open-info')
  // const path = process.env.API + '/glucosecheck/add'
  // const inData = row.value
  // if (isDesk) setBloodPressure()
  // paxios(path, inData)
  // opened.value = false
  // emit('close-expand')
  // // this.$emit('create', row.value)
}
function add () {
  convType()
  console.log('-fn-add', row.value)
  const path = ENV_DEV + '/glucosecheck/add'
  const inData = row.value
  if (isDesk) setBloodPressure()
  paxios(path, inData)
  opened.value = false
  emit('close-expand')
}
function convType () {
  let ty = row.value.type
  if (['空腹','餐一', '餐二', '餐三', '随机'].includes(ty)) return
  row.value.type = ty == 'FAST' ? '空腹' : ty == 'HR-1' ? '餐一' : ty == 'HR-2' ? '餐二' : ty == 'HR-3' ? '餐三' : '随机'
}
function upd () {
  convType()
  console.log('-fn-upd', row.value)
  const path = ENV_DEV + '/glucosecheck/upd'
  if (isDesk) setBloodPressure()
  const inData = row.value
  paxios(path, inData)
  opened.value = false
  emit('close-expand')
  // this.$emit('update', row.value)
}
function del () {
  console.log('-fn-del', row.value)
  const tit = 'Delete Glucose Check'
  const msg = `Please confirm deleting data for ${row.value.datetime}`
  emitter.emit('open-ConfirmDialog', tit, msg)
}
function delFromDB () {
console.log('-fn-del', row.value.id)
  const path = ENV_DEV + '/glucosecheck/del'
  paxios(path, row.value)
  opened.value = false
}
function updDateTime (dt) {
  row.value.datetime = dt
}
</script>

<template>
<q-dialog v-model="opened" transition-show="rotate" persistent>
  <q-layout container class="bg-teal-10" :style="isDesk ? { 'min-width':'777px', 'height':'500px' } : { 'min-width':'455px', 'height':'620px' } ">
    <LayoutHeader tit="Glucose Daily Check" @do-action="doAction" />
    <LayoutFooter :act=action tit="TIT_GLUCOSE" @do-action="doAction" class="q-pb-"/>
    <q-page-container v-if="isDesk">
      <q-page>
        <div class="row" style="width:102.3%">
          <DateTimePicker style="width:40%" class="q-pt-sm" label="Created Date and Time" :date-time="row.datetime" @upd-dt="updDateTime" txsz="text-h6" />
          <q-chip style="max-width:8.5%;margin-left:-3px" class="text-h6 text-bold text-cyan-2 bg-teal-10 q-mt-md">{{ (row.fasting/18).toFixed(1) }}</q-chip>
          <NumInput style="width:24%" class="q-pt-sm" :obj="row" label="Sugar Level" icon="bloodtype" iColor="red" :rightIcon="true" :showRight="true" @click="openNumPad()" />
          <NumInput style="width:25%" class="q-pt-sm" :obj="row" label="Weight" mask="#.#" icon="重" iconSize="30" iColor="cyan-2" :rightIcon="true" :showRight="true" />
        </div>
        <div class="row" style="width:99.9%"> 
          <NumInput class="col-4" :obj="row" label="Hi Blood Pressure" icon="tire_repair" iColor="pink-3" :rightIcon="true" :showRight="true" />
          <NumInput class="col-4" :obj="row" label="Lo Blood Pressure" icon="tire_repair" iColor="blue-3" :rightIcon="true" :showRight="true" />
          <NumInput class="col-4" :obj="row" label="Heart Pulse" icon="monitor_heart" iColor="red" :rightIcon="true" :showRight="true" />
        </div>
        <TxtInput class="col-12" :obj="row" label="Food" icon="ramen_dining" iColor="yellow" :rightIcon="true" />
        <div class="row">
          <TxtInput class="col-6" :obj="row" label="Drink" icon="local_bar" iColor="green" />
          <TxtInput class="col-6" :obj="row" label="Fruit" icon="apple" iColor="green-3" :iconRight="true" />
        </div>
        <TxaInput class="col-12 q-pa-xs" :obj="row" label="昨 日 餐 饮" icon="description" iColor="white" />
      </q-page>
    </q-page-container>
    <q-page-container v-else class="q-ma-xs">
      <div class="row">
        <DateTimePicker style="width:68.8%" class="q-px-sm" label="Created Date and Time" :date-time="row.datetime" @upd-dt="updDateTime" txsz="text-h6" />
        <NumInput style="width:31.2%" :obj="row" label="Weight" icon="重" icon-size="24px" iColor="yellow" mask="#.#"/>
      </div>
      <div class="row">
        <TxtInput style="width:50%" :obj="row" label="Blood Pressure" icon="tire_repair" iColor="pink-3" @click="openNumPad('BP')" />
        <div style="width:18%"><q-chip class="text-h6 text-cyan-2 bg-teal-10 q-mt-sm">{{ (row.fasting/18).toFixed(1) }}</q-chip></div>
        <NumInput style="width:32%" :obj="row" label="Blood Glucose Level" icon="bloodtype" iColor="red" @click="openNumPad()" />
      </div>
      <TxtInput :obj="row" label="Food"  icon="ramen_dining" iColor="green" :rightIcon="true" />
      <TxtInput :obj="row" label="Drink" icon="local_bar" iColor="yellow" :rightIcon="true" />
      <TxtInput :obj="row" label="Fruit" icon="apple" iColor="cyan-3" :rightIcon="true" />
      <TxaInput :obj="row" label="昨 日 餐 饮" icon="description" iColor="cyan-3" />
      <q-chip v-if="action=='add'" class="q-ma-sm text-body1" color="cyan-2">
        the above is the <b class="q-px-sm text-h6"> {{ row.datetimeOrig }}</b>data
      </q-chip>
    </q-page-container>
  </q-layout>
</q-dialog>
<NumPadAuto @sugar-level="setSugarLevel" @blood-pressure="setBloodPressure" />
<ConfirmDialog @user-confirmed="delFromDB" />
<gludarInfo :row="row" />
</template>
<script setup>
import { ref, onMounted } from 'vue'
import emitter from 'tiny-emitter/instance'
import ConfirmDialog from '../src/components/ConfirmDialog'
import TxtInput from '../src/components/TxtInput'
import TxaInput from '../src/components/TxaInput'
import NumInput from '../src/components/NumInput'
import LayoutHeader from '../src/components/LayoutHeader'
import LayoutFooter from '../src/components/LayoutFooter'
import DateTimePicker from '../src/components/DateTimePicker'
import NumPadAuto from '../src/components/NumPadAuto'
import gludarInfo from './gludar_m_info'

import { axiosFunctions } from '../src/composables/axiosFunctions'
const { gaxios, paxios } = axiosFunctions()
import { dayFunctions } from '../src/composables/dayFunctions'
const { yyyymmddHHMM, yyyymmdd } = dayFunctions()
import { libFunctions } from '../src/composables/libFunctions'
const { isDesk } = libFunctions()

const opened = ref(false)
const action = ref(null)
const row = ref({ datetime: null })
var rowOrig = {}

const emit = defineEmits(['close-expand'])

console.log('-ST-gludar')
emitter.on('open-gludar', (rw, act) => openIt(rw, act))

//== function section
function XXXsetBloodPressureFone (x) {
  console.log(`setBloodPressure=${x}`)
  row.value.bloodPressure = x
}
function setSugarLevel (x) {
  console.log(`-fn-setSugarLevel=${x}`)
  row.value.fasting = x
}
function openNumPad (flag) {
  // console.log(`-fn-openNumPadSL flag=${flag}`)
  if (isDesk) return 
  if (flag == 'BP') return emitter.emit('open-num-pad-auto', '输入血压', flag, null)
  else return emitter.emit('open-num-pad-auto', '血糖测试', 60, 300)
}
function openIt (rw, act) {
  console.log(`-fn-gludar.openIt act=${act}`, rw)
  action.value = act
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
function doAction (act) {
  if (act === 'add') add()
  else if (act === 'upd') upd()
  else if (act === 'del') del()
  else if (act === 'info') showInfo()
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
  console.log('-fn-add', row.value)
  const path = process.env.API + '/glucosecheck/add'
  const inData = row.value
  if (isDesk) setBloodPressure()
  paxios(path, inData)
  opened.value = false
  emit('close-expand')
  // this.$emit('create', row.value)
}
function upd () {
  console.log('-fn-upd', row.value)
  const path = process.env.API + '/glucosecheck/upd'
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
  const path = process.env.API + '/glucosecheck/del'
  const inData = row.value
  paxios(path, row.value)
  opened.value = false
}
function updDateTime (dt) {
  row.value.datetime = dt
}
</script>

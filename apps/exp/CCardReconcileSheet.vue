<template>
<q-dialog v-model="opened" transition-show="slide-left" transition-hide="slide-right" :maximized="isIM" no-shake>
  <q-card class="q-pa- bg-teal-9 text-h6" dark square :style="getPosition()">
    <q-card-section class="q-px-md q-pa-sm bg-cyan-8 shadow-box inset-shadow-down flex justify-between" v-touch-pan.prevent.mouse="moveDialog">
      <q-btn round glossy icon="chevron_left" color="amber-9" size="md" v-close-popup />
      <div v-if="isDesk" class="cursor-pointer">{{ startDate }} ~ {{ endDate }} </div>
      <div v-else>{{ startDate.substring(5,10) }} ~ {{ endDate.substring(5,10) }} </div>
      <div v-if="compResidual>0" :style="isIM ? {fontSize:'16px'} : {fontSize:'20px'}" class="text-center text-amber"> {{ compResidual }}</div>
      <div v-else-if="compResidual<0" :style="isIM ? {fontSize:'16px'} : {fontSize:'20px'}" class="text-center text-red-6"> {{ compResidual }}</div>
      <div v-else :style="isIM ? {fontSize:'16px'} : {fontSize:'20px'}" class="text-center text-green-10">{{ compResidual }}</div>
      <q-btn round glossy icon="credit_card" color="amber-9" size="md" v-close-popup />
    </q-card-section>

    <div :style="isIM ? { width:'370px',fontSize:'16px' } : { width:'520px', fontSize:'19px' }" class="q-mx-xs">
      <q-tr v-for="e in ccardSpendings" :key=e.id :class="{'bg-red':!e.isReconed }" class="q-mx-sm">
        <td class="q-pl-sm ellipsis cursor-pointer" @click="getSpendingDetails(e.id)">{{ e.date }}
          <q-tooltip class="text-h6 bg-cyan-10">get spending details for {{ e.id }}</q-tooltip>
        </td>
        <td v-if="isDesk" class="q-pl-sm ellipsis" style="max-width:255px;min-width:255px">{{ e.cats + ' ' + e.subc }}</td>
        <td v-else class="q-pl-xs ellipsis" style="width:150px">{{ e.cats }}</td>
        <td style="width:50px" class="text-right cursor-pointer" @click="toggleRecon(e)">{{ e.cost }}
          <q-tooltip class="text-h6 bg-cyan-10">toggle reconciliation</q-tooltip>
        </td>
        <td><q-checkbox v-model="e.checked" @click="setReconcile(e)" /></td>
      </q-tr>
    </div>
    <div class="flex justify-between q-py-sm q-px-sm bg-cyan-8 shadow-box inset-shadow-down" v-touch-pan.prevent.mouse="moveDialog">
      <q-td class="q-pt-xs">{{ ccardSpendings.length }} items</q-td>
      <q-td class="q-pt-xs">${{ ccardPayment }}</q-td>
      <q-td v-if="isDesk" class="q-pt-xs cursor-pointer">Due on {{ ccardDueDay.yyyymmdd() }}</q-td>
      <q-td v-else class="q-pt-xs">Due on {{ ccardDueDay.substring(5, 10) }}</q-td>
      <q-td><q-btn round glossy :icon="isAllSelected() ? 'check_box' : 'check_box_outline_blank'" :color="isAllSelected() ? 'green' : 'pink'" @click="selectAllCosts" /></q-td>
    </div>
  </q-card>
  <exdar />
</q-dialog>
</template>
<script setup>
import { ref, reactive, computed } from 'vue'
import emitter from 'tiny-emitter/instance'
import exdar from '../exp/exdar'
import { axiosFunctions } from 'src/composables/axiosFunctions'
import { libFunctions } from 'src/composables/libFunctions'
// import { dayFunctions } from 'src/composables/dayFunctions'
// const { yyyymmddHHMM } = dayFunctions()

console.log('-ST-CCardReconcileSheet') // reconcile credit card spendings with fidelity credit card monthly statement
emitter.on('open-CCardReconcileSheet', (da, ccpay, ccDueDay, betweenDays) => openIt(da, ccpay, ccDueDay, betweenDays))

var ccardSpendings = ref([])
var date = null
var startDate = null
var endDate = null
var ccardPayment = 0
var residual = ref(0)
var ccardDueDay = '2000-00-00'
var opened = ref(false)
const posX = ref(170)
const posY = ref(-30)
const draggingCard = ref(false)

const { paxios, gaxios } = axiosFunctions()
const { isIM, isDesk, fmtcy } = libFunctions()

const compResidual = computed(() => { return Math.abs(residual.value) < 0.0001 ? 'Done' : fmtcy(residual.value) })
// const compResidual = computed(() => { return Math.abs(residual.value) < 0.01 ? 'Done' : residual.value.toFixed(2) })
// const compResidual = computed(() => { return Math.abs(residual.value) < 0.01 ? 'Done' : residual.value })
emitter.on('expense-getSpending', (da) => setSpendingDetails(da))
function setSpendingDetails (da) {
  console.log(`-fn-setSpendingDetails`, da.spending[0])
  emitter.emit('open-exdar', da.spending[0])
}
function getSpendingDetails (sid) {
  console.log(`-fn-CK-getSpendingDetails for spendId=${sid}`)
  const path = process.env.API + '/expense/getSpending/' + sid
  // const path = process.env.API + '/bankstatementloader/getSpending/' + sid
  gaxios(path)
}
function toggleRecon (e) {
  console.log(`-fn-toggleRecon for`, e)
}
function getPosition () {
  if (isDesk) return 'margin:' + posY.value + 'px 0 0 ' + posX.value + 'px'
  // else if (isIM) return 'margin:' + posY.value + 'px 0 ' + posX.value + 'px' + ' 0'
  else if (isIM) return 'margin:0 0 0 0'
}
function moveDialog (ev) {
  // console.log(`-CK-fn-moveDialog`)
  draggingCard.value = ev.isFirst !== true && ev.isFinal !== true
  posY.value += ev.delta.y
  posX.value += ev.delta.x
}
function openIt (da, ccpay, ccDueDay, betweenDays) {
  console.log(`-CK-openIt-ccpay=${ccpay}, ccDueDay=${ccDueDay}, betweenDays=${betweenDays} residual=${residual.value} compResidual=${compResidual.value}`, da)
  ccardSpendings.value = da
  ccardPayment = ccpay
  residual.value = ccpay
  ccardDueDay = ccDueDay
  // startDate = betweenDays.split(' ~ ')[0]
  // endDate = betweenDays.split(' ~ ')[1]
  startDate = betweenDays[0]
  endDate = betweenDays[1]
  ccardSpendings.value.forEach(p => {
    if (p.isReconed) {
      residual.value -= parseFloat(p.cost)
      p.checked = true
    }
  })
  opened.value = true
}
function selectAllCosts () {
  console.info('-fn-selectAllCosts')
  ccardSpendings.value.forEach(p => {
    p.checked = !p.checked
    setReconcile(p)
  })
}
function isAllSelected () {
  for (var i = 0; i < ccardSpendings.value.length; i++) {
    if (!ccardSpendings.value[i].checked) return false
  }
  return true
}
function setReconcile (e) {
  if (e.checked) {
    residual.value -= parseFloat(e.cost)
  } else {
    residual.value += parseFloat(e.cost)
  }
  const inData = { Id: e.id, reconciledAt: e.checked ? ccardDueDay : null }
  // console.info('setReconcile for', e.checked, e.cost, residual.value, args, e)
  const path = process.env.API + '/expense/setReconcile'
  paxios(path, inData)
}
</script>

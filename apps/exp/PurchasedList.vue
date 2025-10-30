<template>
<q-dialog v-model="opened" position="right">
  <q-card class="bg-teal-10 text-white" :style="isDesk ? { 'min-width': '610px' } : { 'min-width': '380px' }">
    <div class="text-center text-h6 q-pa-sm bg-cyan-10 shadow-box inset-shadow">{{ payee }} on {{ date }} ({{pList.length}} items)</div>
    <q-card-section>
      <!-- <div class="text-h6" :style="isIM ? { margin:'0 0 0 230px' } : {}"> -->
      <div class="text-h6">
        <tr glossy v-for="p in pList" :key=p>
          <td class="text-left text-no-wrap" :style="isIM ? {width:'110px'} : {width:'200px'}">
            <q-checkbox v-model="p.isStoreSet" color="amber" :label="p.name" @click="setStore(p)" dark />
          </td>
          <td class="text-right" style="width:80px">{{ p.price }}</td>
          <td class="text-right" style="width:80px">{{ p.units }}</td>
          <td class="text-right" style="width:70px" v-if="isDesk">{{ p.tax>0 ? p.tax : null }}</td>
          <td class="text-right" style="width:70px" v-if="isDesk">{{ p.disct!==0 ? p.disct : null }}</td>
          <td class="text-right" style="width:80px">{{ p.costs }}
            <q-tooltip v-if="p.tax>0" content-class="text-h6 text-grey-9 bg-yellow-6">Sales Tax: {{ p.tax }}</q-tooltip>
          </td>
        </tr>
      </div>
    </q-card-section>
    <q-card-actions class="q-pa-sm q-px-xs bg-cyan-10 flex justify-between shadow-box inset-shadow-down text-h6">
      <q-btn round glossy icon="chevron_left" color="orange" v-close-popup />
      <div>Total: $ {{ totalCosts }}</div>
    </q-card-actions>
  </q-card>
</q-dialog>
</template>

<script setup>
import emitter from 'tiny-emitter/instance'
import { ref } from 'vue'
import { libFunctions } from 'src/composables/libFunctions'
import { axiosFunctions } from 'src/composables/axiosFunctions'
const { isIM, isDesk } = libFunctions()
const { gaxios } = axiosFunctions()
const date = ref(null)
const payeeId = ref(null)
const payee = ref('Payee Name to be overridden')
const pList = ref([])
const totalCosts = ref(0)
const opened = ref(false)

console.log('-ST-PurchasedList')
emitter.on('open-PurchasedList', (date, plst, paye, payeId) => { openIt(date, plst, paye, payeId) })

function setStore (item) {
  console.log(`-fn-setStore item.isStoreSet=${item.isStoreSet}`, item)
  if (item.tax == null) item.tax = 0.0
  if (item.disct == null) item.disct = 0.0
  item.disct = parseFloat(item.disct)
  item.costs = parseFloat(item.costs)
  item.tax = parseFloat(item.tax)
  let tcosts = parseFloat(totalCosts.value)
  const xcost = (item.costs + item.disct) * (1.0 + item.tax / 100.0)    // tax after discount
  // const xcost = (item.costs + item.disct) + item.costs * item.tax / 100.0   // tax before discount
  if (item.isStoreSet) tcosts += xcost
  else tcosts -= xcost
  totalCosts.value = tcosts.toFixed(2)
  const pyId = item.isStoreSet ? payeeId.value : 0
  const path = process.env.API + '/expense/setStore/' + item.id + '/' + pyId
  const itm = pList.value.find(p => p.itemId == item.itemId)
  itm.isStoreSet = pyId > 0 ? true : false
  itm.payee_id = pyId
  // console.log(`-fn-setStore item.isStoreSet=${item.isStoreSet} pyId=${pyId}`, item)
  gaxios(path)
}
function openIt (dt, plst, paye, payeId) {
  opened.value = true
  // console.log(`-CK-fn-PurchasedList.openIt date=${dt} paye=${paye} payeId=${payeId}`, plst)
  pList.value = plst
  date.value = dt
  payeeId.value = payeId
  payee.value = paye

  let tcosts = 0.0
  plst.forEach(p => {
    if (p.tax == null) p.tax = 0
    if (p.disct == null) p.disct = 0
    p.isStoreSet = false
    if (p.payee_id > 0) {
      tcosts += (parseFloat(p.costs) + parseFloat(p.disct)) * (1.0 + parseFloat(p.tax) / 100.0) // + parseFloat(p.disct)
      p.isStoreSet = true
    }
  })
  totalCosts.value = tcosts.toFixed(2)
  // console.log(`-CK-totalCosts=${totalCosts.value}`)
}
</script>
<!-- <style>
.qX-checkbox__label {
  color: whtie;
  font-size: 18px;
}
</style> -->

<template>
<div class="q-pa-sm q-gutter-sm">
  <q-dialog v-model="opened" persistent transition-show="scale" transition-hide="scale" class="bg-teal-10">
    <q-card style="width:350px;background:teal-10">
      <q-card-section class="bg-secondary">
        <div class="text-h5 text-center text-bold">Add New Golf Gift Card</div>
      </q-card-section>

      <q-card-section class="bg-teal-10">
        <!-- <div class="text-h6 text-white">Current Balance : ${{ row.gcardVal }}</div> -->
        <div class="text-h6 text-white">Current Balance : ${{ curBalance }}</div>
        <div class="text-h6 text-white">Current Card Number : {{ curCardNum }}</div>
        <div class="text-h6 text-white">This Spending : ${{ cost }}</div>
      </q-card-section>

      <q-card-section class="bg-teal-10">
        <q-input class="text-h6 q-ma-xs" outlined rounded dark label="New Gift Card Num" v-model="newCardNum" autofocus />
        <q-input class="text-h6 q-ma-xs" outlined rounded dark label="New Gift Card Face Value" prefix="$" v-model="newCardVal" />
        <q-input class="text-h6 q-ma-xs" outlined rounded dark label="New Gift Card ID (paymId)" v-model="paymId" />
        <q-input class="text-h6 q-ma-xs" outlined rounded dark label="New Gift Card Name" v-model="newCardName" />
      </q-card-section>

      <q-card-actions align="right" class="text-primary">
        <q-btn flat label="Cancel" v-close-popup />
        <q-btn flat label="Add New GolfGift Card" @click="addNewGiftCard" />
      </q-card-actions>
    </q-card>
  </q-dialog>
</div>
</template>
<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'
import { axiosFunctions } from '../src/composables/axiosFunctions'
const { paxios } = axiosFunctions()
var cost = 0
var paymId = 0
// const curBalance = ref(0)
var curBalance = 0
var newCardVal = 600.00
var curCardNum = ''
var newCardNum = null
var newCardName = 'Mercer County Golf Gift Card'
const opened = ref(false)
// const row = ref({})
const emit = defineEmits(['new-gift-card'])

console.log('-ST-addNewGiftCardDialog')
emitter.on('open-AddNewGiftCardDialog', (x) => openIt(x))

function addNewGiftCard () {
  if (newCardNum == null) {
    emitter.emit('open-InfoDisplay', 'Please Add New Gift Card Number')
    return
  }
  // console.log(`-CK-fn-add new gift card curBalance=${curBalance} newCardNum=${newCardNum} newCardVal=${newCardVal}, newCarName=${newCardName}, paymId=${paymId}`)
  const path = process.env.API + '/expense/addNewGiftCard'
  let inData = { paym_id: paymId, name: newCardName, card_num: newCardNum, value: newCardVal }
  paxios(path, inData)
  let newBalance = parseFloat(newCardVal) + parseFloat(curBalance)
  emit('new-gift-card', newCardNum, newBalance.toFixed(2), paymId)
  opened.value = false
}
function openIt (r) {
  // console.log('-CK-fn--fn-opening add new gift card dialog', r)
  // row.value = r
  paymId = r.paymId
  cost = r.cost
  // curBalance = parseFloat(row.gcardVal) - row.cost
  curBalance = r.curBalance
  // console.log(`-CK-fn--fn-opening add new gift card dialog curBalance=${curBalance}`)
  curCardNum = r.curCardNum
  // newCardNum = r.gcardNum 
  newCardName = paymId === 15 ? 'Somerset County Golf Gift Card' : 'Mercer County Golf Gift Card' 
  opened.value = true
}
</script>

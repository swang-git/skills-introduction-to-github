<template>
<q-dialog v-model="opened">
<div class="fixed-top" :style="isDesk ? { maxWidth:'655px'} : { minWidth:'360px' }">
  <q-card class="bg-teal-10">
    <q-card-actions align="between">
      <q-btn flat round glossy text-color="cyan-1" v-close-popup>{{ balances.length}}</q-btn>
      <q-btn :disable="cardIdx>cardNums.length-2" glossy rounded class="bg-teal-9" @click="showCardBalanes(1)">
        <q-icon left name="arrow_circle_left" size="md" color="lime" />
        <span v-if="isDesk" class="text-cyan-2 text-h6" style="margin: 0 4px 0 -8px">上一张卡</span>
        <span v-else-if="isIM" class="text-cyan-2 text-h6" style="margin: 0 4px 0 -8px">上一张</span>
      </q-btn>
      <div v-if="isDesk" class="text-white text-h5 text-center q-pl-lg q-pt-sm">Card Balance List</div>
      <q-btn :disable="cardIdx<1" glossy rounded class="bg-teal-9" @click="showCardBalanes(-1)">
        <span v-if="isDesk" class="text-cyan-2 text-h6" style="margin: 0 4px 0 -1px">下一张卡</span>
        <span v-else-if="isIM" class="text-cyan-2 text-h6" style="margin: 0 4px 0 -1px">下一张</span>
        <q-icon name="arrow_circle_right" size="md" color="lime" />
      </q-btn>
      <q-btn flat round glossy text-color="cyan-1" :class="{ 'bg-red-10':checkStatus=='NO'}" v-close-popup>{{ checkStatus }}</q-btn>
    </q-card-actions>
    <q-card-actions v-if="isDesk" class="p-px-md">
      <Selection class="col-8" :obj="currCard" label="Select Golf Gift Card" icon="credit_card" iColor="amber-7" :optList="cards" @set-opt="selectCard" />
      <Selection class="col-4" :obj="currCardNum" label="Select Card Number" icon="numbers"     iColor="cyan-4"  :optList="cardNums" @set-opt="setCardNums" />
    </q-card-actions>
    <q-card-actions v-else-if="isIM" align="between">
      <Selection class="col-7" :obj="currCard" label="Select Golf Gift Card" icon="credit_card" iColor="amber-7" :optList="cards" @set-opt="selectCard" />
      <Selection class="col-5" :obj="currCardNum" label="Select Card Number" icon="numbers"     iColor="cyan-4"  :optList="cardNums" @set-opt="setCardNums" />
    </q-card-actions>
    
    <q-card-section v-if="isDesk" class="text-h6 text-cyan-2" dark>
      <tr v-for="b in balances" :key=b>
        <td class="q-pl-">{{ b.spendId }}</td>
        <td class="q-pl-md text-no-wrap">{{ b.spendDate }}</td>
        <td class="q-pl-sm text-right" style="min-width:90px;max-width:90px">{{ b.cost }}</td>
        <td class="q-pl-md text-right text-amber">{{ b.balance }}</td>
        <td class="q-pl-xs text-no-wrap text-right" style="min-width:166px;max-width:166px">{{ b.check }}</td>
        <td class="q-pl-md">
        <q-btn glossy round @click="getSpending(b.spendId)">
          <q-icon :color="b.status=='OK' ? 'amber-3' : 'red-10'" :name="b.status=='OK' ? 'check' : 'close'" />
        </q-btn>
        </td>
      </tr>
    </q-card-section>
    <q-card-section v-else class="text-bold text-cyan-1" dark style="font-size:16px">
      <tr v-for="b in balances" :key=b>
        <td class="text-no-wrap">{{ b.spendDate }}</td>
        <!-- <td class="q-pl-sm text-right" style="min-width:120px;max-width:120px">{{ b.cost }}</td> -->
        <td class="q-pl-xs text-right text-amber">{{ b.balance }}</td>
        <td class="q-pl-xs text-no-wrap" style="min-width:140px;max-width:140px">{{ b.check }}</td>
        <td class="q-pl-xs">
        <q-btn glossy round size="11.3px" @click="getSpending(b.spendId)">
          <q-icon :color="b.status=='OK' ? 'amber-3' : 'red-10'" :name="b.status=='OK' ? 'check' : 'close'" />
        </q-btn>
        </td>
      </tr>
    </q-card-section>
  </q-card>
  <exdar ref="refExdar" />
</div>
</q-dialog>
</template>
<script setup>
import { ref, onMounted, computed, reactive } from 'vue'
import emitter from 'tiny-emitter/instance'
import { axiosFunctions } from '../src/composables/axiosFunctions'
const { gaxios } = axiosFunctions()
import { libFunctions } from '../src/composables/libFunctions'
const { fmtcy, isDesk, isIM } = libFunctions()
import Selection from '../src/components/Selection'
import exdar from '../exp/exdar'

//== data sections
const opened = ref(false)
const cardIdx = ref(0)
const paymId = ref(9)
// const currCard = ref({})
const balances = ref([])
const pymCards = reactive({ paym9:[], paym15:[] })
const ppmCards = ref([])
const refExdar = ref(null)
const allCards = ref([])
const giftCards = ref([])
const checkStatus = ref('OK')
// const cardNums09 = ref([ {sval:0, slbl:'45116'}, {sval:1, slbl:'40816'}, {sval:2, slbl:'40815'}, {sval:3, slbl:'230761'} ])
const cardNums09 = ref([])
const cardNums15 = ref([])
const cards = ref([])

emitter.on('exp-getGiftCards', (da) => setGiftCards(da))
emitter.on('exp-getGiftCardBalances', (da) => setGiftCardBalances(da))
emitter.on('exp-getSpending', (da) => setSpending(da))
defineExpose({ openIt })
onMounted(() => { refExdar })

const currCard = computed(() => { return paymId.value == 9 ? cards.value[0] : cards.value[1]})
const cardNums = computed(() => { return paymId.value == 9 ? cardNums09.value : cardNums15.value})
const currCardNum = computed(() => { return cardNums.value[cardIdx.value] })
const prevCardNum = computed(() => { return cardNums.value[cardIdx.value + 1] == undefined ? 0 : cardNums.value[cardIdx.value + 1] })

//== function sections
function openIt () {
  console.log(`-fn-openIt`)
  const path = process.env.API + '/exp/getGiftCards'
  gaxios(path)
}
function setGiftCards (da) {
  console.log(`-fn-setGiftCards`, da)
  cards.value = da.giftCards
  currCard.value = cards.value[0]
  cardNums09.value = da.cardNums09
  cardNums15.value = da.cardNums15
  // console.table(currCardNum.value) 
  getGiftCardBalances()
  opened.value = true
}
function getCardNumIndex (selectedCardNum) {
  for (let i=0; i<ppmCards.value.length; i++) {
    const c = ppmCards.value[i]
    if (c.label == selectedCardNum) {
      return i
    }
  }
  return -100
}
function selectCard (label, opt) {
  console.log(`-CK-selectCard label=${label} opt.sval=${opt.sval}`, opt)
  cardIdx.value = 0
  paymId.value = opt.sval == 0 ? 9 : 15
  getGiftCardBalances()
}
function getGiftCardBalances () {
  // console.log(`-fn-getGiftCardBalances`, currCard.value)
  console.log(`-CK-fn-getGiftCardBalances`)
  const currNum = currCardNum.value.slbl
  const prevNum = prevCardNum.value.slbl
  const path = process.env.API + '/exp/getGiftCardBalances/' + paymId.value + '/' + currNum + '/' + prevNum
  gaxios(path)
}
function setGiftCardBalances (da) {
  checkStatus.value = 'OK'
  console.log(`-CK-fn-setGiftCardBalances`, da)
  balances.value = []
  const bals = da.balances
  bals.forEach((p, i) => {
    if (i == bals.length - 1) return
    let nbal = 0
    let status = 'NO'
    nbal = parseFloat(bals[i+1].balance - p.cost)
    // console.log(`-CK-setGiftCardBalances nbal=${nbal} balance=${p.balance}`, bals[i+1].balance)
    if (Math.abs(nbal - p.balance) < 0.0001) status = 'OK'
    p.check = (' == ' +  bals[i+1].balance + ' - ' + p.cost)
    p.status = status
    balances.value.push(p)
  })
  let lastBal = bals[bals.length - 1]
  let cardValue = da.cardValue == null ? 600 : da.cardValue 
  let prevBalance = da.prevBalance == null ? 0 : da.prevBalance 
  let nStartValue = parseFloat(cardValue) + parseFloat(prevBalance)
  lastBal.check = ' == ' + nStartValue + ' - ' + lastBal.cost
  lastBal.status = 'NO'
  if (Math.abs(nStartValue - lastBal.cost - lastBal.balance) < 0.001) lastBal.status = 'OK'
  balances.value.push(lastBal)
  let sts = balances.value.find(p => p.status == 'NO')
// console.log(`-CK-balances=`, sts, balances.value, Math.abs(nStartValue - lastBal.cost - lastBal.balance))
  if (sts !== undefined) checkStatus.value = 'NO'
}
function setCardNums (label, selOpt) {
  console.log(`-CK-label=${label} setCardNums=${selOpt.label}`)
  cardIdx.value = selOpt.sval
  getGiftCardBalances()
  // console.log(`-CK- prevCardNum=${prevCardNum.value.slbl}`)
} 
function showCardBalanes(idx) {
  if (idx === 1) {
    cardIdx.value += idx
  } else if (idx === -1) {
    cardIdx.value += idx
  }
  getGiftCardBalances()
}
function setSpending (da) {
  console.log(`-fn-setSpending`, da.spending)
  refExdar.value.openIt(da.spending, 'upd')
}
function getSpending (spendId) {
  console.log(`-fn-getSpending`)
  const path = process.env.API + '/exp/getSpending/' + spendId
  gaxios(path)
}
</script>
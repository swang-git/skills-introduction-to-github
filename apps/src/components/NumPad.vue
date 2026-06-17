<template>
<q-dialog v-model="opened">
  <div class="bg-amber q-pa-xs" style="width:240px;border-radius:30px">
    <q-card class="text-h6 bg-teal-10" style="border-radius:30px">
      <div class="q-pa-sm text-lime text-h5 text-center text-no-wrap">{{ padTit }}</div>
      <q-card-actions class="justify-center">
        <q-btn v-for="i in [1,2,3]" :key=i class="q-ma-xs" size="lg" color="cyan-9" round @click="setNumber(i)">{{ i }}</q-btn>
        <q-btn v-for="i in [4,5,6]" :key=i class="q-ma-xs" size="lg" color="cyan-9" round @click="setNumber(i)">{{ i }}</q-btn>
        <q-btn v-for="i in [7,8,9]" :key=i class="q-ma-xs" size="lg" color="cyan-9" round @click="setNumber(i)">{{ i }}</q-btn>
        <q-btn class="q-ma-xs" size="lg" color="teal-9" glossy round @click="setSpeed"><q-icon name="motion_photos_auto" color="yellow-9" /></q-btn>
        <q-btn class="q-ma-xs" size="lg" color="cyan-9" round @click="setNumber(0)" label="0" />
        <q-btn class="q-ma-xs" size="lg" color="teal-9" glossy round @click="setPicIdx"><q-icon name="check" color="yellow-9" /></q-btn>
        <q-input v-model="keyedIn" outlined rounded type="number" dark class="text-h6 q-pa-xs" />
      </q-card-actions>
    </q-card>
  </div>
</q-dialog>
<!-- <InfoDisplay /> -->
</template>
<script setup>
import { ref, computed } from 'vue'
import emitter from 'tiny-emitter/instance'
import InfoDisplay from './InfoDisplay.vue'
// const emit = defineEmits(['sugar-level', 'blood-pressure'])

const opened = ref(false)
const lowerBound = ref(null) // blood sugar level lower bound
const upperBound = ref(null) // blood sugar level upper bound
const padTit = ref(null)     // numPad title
const numPadTitle = ref(null)     // numPad title
const keyedIn = ref('')
const keyedNum = ref(0)
const bloodPressure = ref(null)
const counter = ref(0)
const flag = ref(-1)
const emit = defineEmits(['set-pic-idx', 'set-interval-delay', 'set-num'])

console.log('-ST-NumPad')
emitter.on('open-num-pad', (x, y) => openIt(x, y))

//== function sections
function setNumber (n) {
  keyedIn.value += n
  if (flag.value == 'GL' && parseInt(keyedIn.value)>=89) {
    emit('set-num', 'GL', parseInt(keyedIn.value))
    // opened.value = false
  } else if (flag.value == 'WT' && parseInt(keyedIn.value) > 999) {
    emit('set-num', 'WT', keyedIn.value)
    opened.value = false
  }
}
function setPicIdx () {
  console.log(`-fn-setPicIdx keyedId=${keyedIn.value}`)
  emit('set-pic-idx', parseInt(keyedIn.value) - 1)
  opened.value = false
}
function setSpeed (flg, tit) {
  console.log(`-fn-setSpeed keyedId=${keyedIn.value}`)
  emit('set-interval-delay', parseInt(keyedIn.value))
  opened.value = false
}
function openIt (flg, tit) {
  console.info(`flag=${flg} tit=${tit}`)
  flag.value = flg
  padTit.value = tit
  opened.value = true
  // numPadTitle.value = tit
  // lowerBound.value = lw
  // upperBound.value = up
  keyedIn.value = ''
  keyedNum.value = ''
}
function setBloodPressure (n) {
  keyedIn.value += n
  counter.value++
  if (counter.value == 3) {
    bloodPressure.value = keyedIn.value + ' / '
    emit('blood-pressure', bloodPressure.value)
    keyedIn.value = ''
  } else if (counter.value == 5) {
    bloodPressure.value += keyedIn.value + ' / '
    emit('blood-pressure', bloodPressure.value)
    keyedIn.value = ''
  } else if (counter.value == 7) {
    bloodPressure.value += keyedIn.value
    emit('blood-pressure', bloodPressure.value)
    keyedIn.value = ''
    counter.value = 0
    console.log(`BloodPressure=[${bloodPressure.value}]`)
    opened.value = false
  }
}
// function setNumber (n) {
//   if (n === 'X') {
//     keyedIn.value = ''
//     return
//   }
//   if (numPadTitle.value == '输入血压') return setBloodPressure(n)
//   keyedIn.value += n
//   keyedNum.value = parseInt(keyedIn.value)
//   if (keyedNum.value >= lowerBound.value && keyedNum.value <= upperBound.value) {
//     console.log(`-CK-keyedNum=${keyedNum.value}`)
//     opened.value = false
//     emit('sugar-level', keyedNum.value)
//   } else if (keyedNum.value > upperBound.value) {
//     keyedIn.value = ''
//     emitter.emit('open-InfoDisplay', `${keyedNum.value} is Too Hight`)
//     return
//   } //else if (keyedNum.value >= 300) {
// }
</script>

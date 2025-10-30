<template>
<q-dialog v-model="opened">
  <div class="bg-amber q-pa-xs" style="width:312px;margin:200px 0 0 0;border-radius:40px">
    <q-card class="text-h6 bg-teal-10" style="border-radius:40px">
      <q-card-actions class="justify-center">
        <div class="inset-shadow-down flex inline shadow-box-lg flex-center">{{ numPadTitle[0] }}</div>
        <q-btn round class="q-ma-xs" color="amber" size="lg" icon="chevron_left" v-close-popup />
        <q-btn round class="q-ma-xs" color="red-5" size="lg" icon="delete" @click="setNumber('X')" />
        <q-btn round class="q-ma-xs" color="cyan-9" size="lg" @click="setNumber(0)"> 0 </q-btn>
        <div class="inset-shadow-down flex inline shadow-box-lg flex-center">{{ numPadTitle[1] }}</div>
        <q-btn v-for="i in [6,3,1]" :key=i class="q-ma-xs" size="lg" color="cyan-9" round :disable="i==='糖'" @click="setNumber(i)">{{ i }}</q-btn>
        <div class="inset-shadow-down flex inline shadow-box-lg flex-center">{{ numPadTitle[2] }}</div>
        <q-btn v-for="i in [7,4,2]" :key=i class="q-ma-xs" size="lg" color="cyan-9" round :disable="i==='测'" @click="setNumber(i)">{{ i }}</q-btn>
        <div class="inset-shadow-down flex inline shadow-box-lg flex-center">{{ numPadTitle[3] }}</div>
        <q-btn v-for="i in [8,5,9]" :key=i class="q-ma-xs" size="lg" color="cyan-9" round :disable="i==='试'" @click="setNumber(i)">{{ i }}</q-btn>
      </q-card-actions>
    </q-card>
  </div>
</q-dialog>
<InfoDisplay />
</template>
<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'
import InfoDisplay from './InfoDisplay'
const emit = defineEmits(['sugar-level', 'blood-pressure'])

const opened = ref(false)
const lowerBound = ref(null) // blood sugar level lower bound
const upperBound = ref(null) // blood sugar level upper bound
const numPadTitle = ref(null)     // numPad title
const keyedIn = ref('')
const keyedNum = ref(0)
const bloodPressure = ref(null)
const counter = ref(0)

console.log('-ST-NumPadAuto')
// emitter.on('open-num-pad-blood-pressure', (x,y,z) => openIt(x, y, z))
emitter.on('open-num-pad-auto', (x,y,z) => openIt(x,y,z))

//== function sections
function openIt (ap, lw, up) {
  opened.value = true,
  numPadTitle.value = ap
  lowerBound.value = lw
  upperBound.value = up
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
function setNumber (n) {
  if (n === 'X') {
    keyedIn.value = ''
    return
  }
  if (numPadTitle.value == '输入血压') return setBloodPressure(n)
  keyedIn.value += n
  keyedNum.value = parseInt(keyedIn.value)
  if (keyedNum.value >= lowerBound.value && keyedNum.value <= upperBound.value) {
    console.log(`-CK-keyedNum=${keyedNum.value}`)
    opened.value = false
    emit('sugar-level', keyedNum.value)
  } else if (keyedNum.value > upperBound.value) {
    keyedIn.value = ''
    emitter.emit('open-InfoDisplay', `${keyedNum.value} is Too Hight`)
    return
  } //else if (keyedNum.value >= 300) {
}
</script>

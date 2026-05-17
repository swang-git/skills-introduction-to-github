<template>
  <q-dialog v-model="opened">
    <div class="bg-amber q-pa-xs" style="width: 240px; border-radius: 30px">
      <q-card class="text-h6 bg-teal-10" style="border-radius: 30px">
        <div class="q-pa-sm text-lime text-h5 text-center text-no-wrap">{{ padTit }}</div>
        <q-card-actions class="justify-center">
          <q-btn
            v-for="i in [1, 2, 3]"
            :key="i"
            class="q-ma-xs"
            size="lg"
            color="cyan-9"
            round
            @click="setNumber(i)"
            >{{ i }}</q-btn
          >
          <q-btn
            v-for="i in [4, 5, 6]"
            :key="i"
            class="q-ma-xs"
            size="lg"
            color="cyan-9"
            round
            @click="setNumber(i)"
            >{{ i }}</q-btn
          >
          <q-btn
            v-for="i in [7, 8, 9]"
            :key="i"
            class="q-ma-xs"
            size="lg"
            color="cyan-9"
            round
            @click="setNumber(i)"
            >{{ i }}</q-btn
          >
          <q-btn class="q-ma-xs" size="lg" color="teal-9" glossy round @click="setSpeed"
            ><q-icon name="motion_photos_auto" color="yellow-9"
          /></q-btn>
          <q-btn class="q-ma-xs" size="lg" color="cyan-9" round @click="setNumber(0)" label="0" />
          <q-btn class="q-ma-xs" size="lg" color="teal-9" glossy round @click="setPicIdx"
            ><q-icon name="check" color="yellow-9"
          /></q-btn>
          <q-input v-model="keyedIn" outlined rounded type="number" dark class="text-h6 q-pa-xs" />
        </q-card-actions>
      </q-card>
    </div>
  </q-dialog>
  <!-- <InfoDisplay /> -->
</template>
<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'
// import InfoDisplay from './InfoDisplay'

const opened = ref(false)
const padTit = ref(null) // numPad title
const keyedIn = ref('')
const keyedNum = ref(0)
const totalPix = ref(0)
const flag = ref(-1)
const emit = defineEmits(['set-pic-idx', 'set-interval-delay', 'set-num'])

console.log('-ST-NumPad')
emitter.on('open-num-pad', (x, y, z) => openIt(x, y, z))

//== function sections
function setNumber(n) {
  keyedIn.value += n
  if (flag.value == 'YALI' && parseInt(keyedIn.value) > totalPix.value) {
    console.log(
      `number is too big(> numPix), set it to num: input number=${keyedIn.value} totalPix=${totalPix.value}`,
    )
    keyedIn.value = totalPix.value - 1
    emit('set-pic-idx', parseInt(keyedIn.value))
    opened.value = false
  } else if (flag.value == 'GL' && parseInt(keyedIn.value) >= 89) {
    emit('set-num', 'GL', parseInt(keyedIn.value))
    // opened.value = false
  } else if (flag.value == 'WT' && parseInt(keyedIn.value) > 999) {
    emit('set-num', 'WT', keyedIn.value)
    opened.value = false
  }
}
function setPicIdx() {
  console.log(`-fn-setPicIdx keyedId=${keyedIn.value}`)
  emit('set-pic-idx', parseInt(keyedIn.value) - 1)
  opened.value = false
}
function setSpeed() {
  console.log(`-fn-setSpeed keyedId=${keyedIn.value}`)
  emit('set-interval-delay', parseInt(keyedIn.value))
  opened.value = false
}
function openIt(flg, tit, numPix) {
  console.info(`flag=${flg} tit=${tit} totalPix=${numPix}`)
  flag.value = flg
  padTit.value = tit
  totalPix.value = numPix
  opened.value = true
  keyedIn.value = ''
  keyedNum.value = ''
}
</script>

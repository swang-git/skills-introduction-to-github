<template>
  <q-dialog v-model="opened">
    <div class="bg-amber q-pa-xs" style="width: 312px; border-radius: 30px">
      <q-card class="text-h6 bg-teal-10" style="border-radius: 30px">
        <div class="q-pa-sm text-lime text-h5 text-center text-no-wrap">{{ padTit }}</div>
        <q-card-actions class="justify-center">
          <q-input class="q-mx-xs q-pb-xs" v-model="keyedIn" outlined rounded dark dense readonly input-class="text-h6 q-pa-xs text-center" />
          <q-btn v-for="i in [1, 2, 3, 4]" :key="i" class="q-ma-xs" size="lg" color="cyan-9" round @click="setNumber(i)" >{{ i }}</q-btn>
          <q-btn v-for="i in [5, 6, 7, 8]" :key="i" class="q-ma-xs" size="lg" color="cyan-9" round @click="setNumber(i)" >{{ i }}</q-btn>
          <q-btn class="q-ma-xs" size="lg" color="teal-9" glossy round @click="opened=false"><q-icon name="cancel" color="lime" /></q-btn>
          <q-btn v-for="i in [9]" :key="i" class="q-ma-xs" size="lg" color="cyan-9" round @click="setNumber(i)" >{{ i }}</q-btn>
          <q-btn class="q-ma-xs" size="lg" color="cyan-9" round @click="setNumber(0)" label="0" />
          <q-btn class="q-ma-xs" size="lg" color="teal-9" glossy round @click="setPicIdx" ><q-icon name="check_circle" color="blue-4" /></q-btn>
        </q-card-actions>
      </q-card>
    </div>
  </q-dialog>
  <!-- Only render if GLOBAL state is open -->
  <div v-if="numPadStore.isOpen" class="numpad">
    Singleton NumPad
  </div>
</template>
<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'
import { useNumPadStore } from '../../src/stores/numPadStore'
const numPadStore = useNumPadStore()

const opened = ref(false)
const padTit = ref(null) // numPad title
const keyedIn = ref('')
const keyedNum = ref(0)
const totalPix = ref(0)
const flag = ref(null)
const emit = defineEmits(['pix-pidx', 'set-interval-delay', 'per-page', 'jump-page'])

console.log('-ST-NumPad')

emitter.on('open-NumPad', (x, y, z) => openIt(x, y, z))

//== function sections
function setNumber(n) {
  keyedIn.value += n
  if (flag.value == 'YALI_IMG_IDX' && parseInt(keyedIn.value) > totalPix.value) {
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
  console.log(`-fn-setPicIdx keyedId=${keyedIn.value} flag=${flag.value}`)
  if (flag.value == 'YALI_PIX_PIDX') emit('pix-pidx', parseInt(keyedIn.value) - 1)
  if (flag.value == 'YALI_PIX_PAGE') emit('jump-page', parseInt(keyedIn.value))
  if (flag.value == 'YALI_PER_PAGE') emit('per-page', parseInt(keyedIn.value))
  opened.value = false
}
// function setSpeed() {
//   console.log(`-fn-setSpeed keyedId=${keyedIn.value}`)
//   emit('set-interval-delay', parseInt(keyedIn.value))
//   opened.value = false
// }
function openIt(flg, tit, numPix) {
  console.info(`-fn-NumPad.openIt flag=${flg} tit=${tit} totalPix=${numPix}`)
  flag.value = flg
  padTit.value = tit
  totalPix.value = numPix
  keyedIn.value = ''
  keyedNum.value = ''
  opened.value = true
}
</script>

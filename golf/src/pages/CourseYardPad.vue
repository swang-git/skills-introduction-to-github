<template>
<q-dialog v-model="opened" persistent>
  <q-card class="bg-teal-10" style="margin:155px 0 0 205px">
    <q-card-section>
      <div class="inset-shadow-down text-h5 text-center text-cyan-2">Yards for Hole {{ holeIdx }} (Par {{ hole(holeIdx) }})</div>
    </q-card-section>
    <table style="y-overflow:auto;margin:auto">
      <q-tr>
        <td v-for="i in [1, 2, 3]" :key=i><q-btn size="xl" outline round :class="bgColor[i]" style="width:70px;height:70px" @click="setYard(i)">{{i}}</q-btn></td>
        <td><q-btn glossy round color="indigo-10" size="xl" icon="cancel" v-close-popup /></td>
      </q-tr>
      <q-tr><td v-for="i in [4, 5, 6,  0 ]" :key=i><q-btn size="xl" outline round :class="bgColor[i]" style="width:70px;height:70px" @click="setYard(i)">{{i}}</q-btn></td></q-tr>
      <q-tr>
        <td v-for="i in [7, 8, 9]" :key=i><q-btn size="xl" outline round :class="bgColor[i]" style="width:70px;height:70px" @click="setYard(i)">{{i}}</q-btn></td>
        <td><q-btn glossy round color="indigo-10" size="xl" icon="cancel" v-close-popup /></td>
      </q-tr>
    </table>
  </q-card>
</q-dialog>
</template>
<script setup>
import { ref } from 'vue'
import { axiosFunctions } from '../composables/axiosFunctions'
const { paxios } = axiosFunctions()
import { storeFunctions } from '../composables/storeFunctions'
const { hole } = storeFunctions()

import { libFunctions } from '../composables/libFunctions'
const { opened, store } = libFunctions()
// const store = useStore()
const holeIdx = ref(0)
// const opened = ref(false)
const bgColor = ref(new Array(10).fill('bg-grey'));
var yardx = ''
var yardsx = {}
console.log(`-ST-CourseYardPad`)
const emit = defineEmits(['set-yard'])

function setYard (y) {
  console.log(`-CK-fn-setYard y=${y} holeIdx=${holeIdx.value}`)
  bgColor.value[y] = 'bg-red'
  yardx += y
  if (parseInt(yardx) > 66) {
    yardsx['y' + holeIdx.value] = parseInt(yardx)
    let yards = JSON.parse(JSON.stringify(yardsx))
    store.yards = yards
    // console.log(`-CK-fn-setYard for hole holeIdx=${holeIdx.value}`, yardsx)
    const path = process.env.API + '/golf/updCourseYardage'
    let inData = { id: yardsx.id, yx: 'y' + holeIdx.value, yard: yardx }
    paxios(path, inData)
    if (holeIdx.value === 18) {
      bgColor.value.fill('bg-grey')
      emit('set-yard', holeIdx.value, parseInt(yardx))
      console.log(`-CK-fn hidx=${holeIdx.value} yard=${yardx}`)
      holeIdx.value = 1
      yardx = ''
      opened.value = false
      return
    }
    bgColor.value.fill('bg-grey')
    emit('set-yard', holeIdx.value, parseInt(yardx))
    console.log(`-CK-fn hidx=${holeIdx.value} yard=${yardx}`)
    yardx = ''
    holeIdx.value++
  }
}
defineExpose({ openIt })
function openIt (idx, yards) {
  holeIdx.value = idx
  yardsx = yards
  // console.log(`-CK-fn-openIt yardPad for hole ${holeIdx.value}`)
  opened.value = true
}
</script>

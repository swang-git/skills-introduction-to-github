<template>
<q-dialog v-model="opened" persistent>
  <div style="margin:-150px 0 0 210px" class="bg-teal-9">
    <q-card-actions align="between">
      <div class="text-h6 q-pl-xl text-cyan-2"> Set Handicap for Hole {{ holeIdx }} </div>
      <q-btn glossy round color="amber-10" v-close-popup icon="close" />
    </q-card-actions>
    <table style="y-overflow:auto;margin:auto">
      <q-tr><td v-for="hcap in [1, 2, 3, 4, 5,  6]" :key=hcap><q-btn size="xl" :label="hcap" :color="getColor(hcap)" style="width:60px;height:60px" @click="setHcap(hcap)" /></td></q-tr>
      <q-tr><td v-for="hcap in [7, 8, 9, 10,11,12]" :key=hcap><q-btn size="xl" :label="hcap" :color="getColor(hcap)" style="width:60px;height:60px" @click="setHcap(hcap)" /></td></q-tr>
      <q-tr><td v-for="hcap in [13,14,15,16,17,18]" :key=hcap><q-btn size="xl" :label="hcap" :color="getColor(hcap)" style="width:60px;height:60px" @click="setHcap(hcap)" /></td></q-tr>
    </table>
  </div>
</q-dialog>
</template>
<script setup>
import { ref } from 'vue'
import { libFunctions } from '../composables/libFunctions'
const { opened, store } = libFunctions()
// var { opened } = libFunctions()
// import { useStore } from 'vuex'
// const store = useStore()
var hcaps = {}
var selectedCaps = []
const holeIdx = ref(0)
// const opened = ref(false)

console.log(`-ST-CourseHcapPad`)
defineExpose({ openIt })
const emit = defineEmits(['set-hcap'])
function openIt (idx, hcps) {
  holeIdx.value = idx
  hcaps = hcps
  // console.log('-ck-fn openIt member pad for hole', holeIdx, hcaps['p' + holeIdx])
  opened.value = true
  // opened = true
}
function getColor (hcap) {
  holeIdx.value // trigger this get called
  // console.log(`-CK-fn getColor for hole holeIdx=${holeIdx.value} hcap=${hcap}`, selectedCaps)
  // console.log(`-CK-holeIdx=${holeIdx.value}`)
  return selectedCaps.includes(hcap) ? 'red' : 'grey'
}
function setHcap (hcap) {
  selectedCaps.push(hcap)
  hcaps['p' + holeIdx.value] = hcap
  let hcapx = JSON.parse(JSON.stringify(hcaps))
  store.hcaps = hcapx
  emit('set-hcap', holeIdx.value, hcap)
  holeIdx.value++
  console.log(`-CK-fn setHcap for hole holeIdx=${holeIdx.value} cap=${hcap}`, selectedCaps)
}
</script>

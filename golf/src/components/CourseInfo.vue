<template>
<q-dialog v-model="opened" position="top" full-height>
<div style="width:screenwidth+'px'" class="bg-teal-10" v-close-popup>
  <div style="height:33px;width:screenwidth+'px'" class="flex justify-center">
    <q-card class="bg-teal-10 inset-shadow-down">
      <div class="text-h5 text-white q-pa-sm">{{ courseName }} ({{ tee }}) </div>
    </q-card>
  </div>
  <div style="height:30px;width:screenwidth+'px'" class="flex justify-center">
    <q-card class="bg-teal-10 inset-shadow-down">
      <div class="text-h6 text-cyan-1 justify-center">
        Slope: <b class="text-white">{{ slope }}</b> 
        Rating: <b class="text-white">{{ rating }}</b> 
        Yardage: <b class="text-white">{{ totalyard }}</b> 
      </div>
    </q-card>
  </div>
  <div class="row q-pa-xs justify-center">
    <div style="width:60px">
      <shadowBox v-for="i in [1,2,3,4,5,6,7,8,9]" :key=i :val="String(i)" />
      <ShadowBox class="bg-green-9" :val="String(f9par)" />
    </div>
    <div style="width:60px">
      <div v-for="i in [0,1,2,3,4,5,6,7,8]" :key=i :class="shadowClass(i)" class="shadow-box-hole">
        <div style="margin:12px 0 0 -11px;font-size:22px">{{ yards[i] }}</div>
        <div style="font-size:18px;margin:-28px 0 0 -22px" class="text-bold">{{ pars[i] }}</div>
      </div>
      <div class="text-center shadow-box-reg bg-green-9">{{ f9yard }}</div>
    </div>
    <div style="width:75px">
      <ShadowBox v-for="i in [0,1,2,3,4,5,6,7,8]" :key=i class="bg-green-9" :val="String(caps[i])" />
    </div>
    <div style="width:60px">
      <ShadowBox v-for="i in [10,11,12,13,14,15,16,17,18]" :key=i :val="String(i)" />
      <ShadowBox class="bg-green-9" :val="String(b9par)" />
    </div>
    <div style="width:60px">
      <div v-for="i in [9,10,11,12,13,14,15,16,17]" :key=i :class="shadowClass(i)" class="shadow-box-hole">
        <div style="margin:12px 0 0 -11px;font-size:22px">{{ yards[i] }}</div>
        <div style="font-size:18px;margin:-28px 0 0 -22px" class="text-bold">{{ pars[i] }}</div>
      </div>
      <div class="text-center shadow-box-reg bg-green-9">{{ b9yard }}</div>
    </div>
    <div style="width:60px">
      <ShadowBox v-for="i in [9,10,11,12,13,14,15,16,17]" :key=i class="bg-green-9" :val="String(caps[i])" />
    </div>
  </div>
</div>
</q-dialog>
</template>
<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'
// import { libFunctions } from 'src/composables/libFunctions';
import ShadowBox from 'src/components/ShadowBox.vue';
const opened = ref(false)
const courseName = ref(null)
const tee = ref(null)
const pars = ref([])
const caps = ref([])
const yards = ref([])
const f9par = ref(0)
const b9par = ref(0)
const totalpar = ref(0)
const f9yard = ref(0)
const b9yard = ref(0)
const totalyard = ref(0)
const slope = ref(0)
const rating = ref(0)
console.log('-ST-CourseInfo')
emitter.on('open-CourseInfo', (cinfo, tx) => { openIt(cinfo, tx) })
// const { screenwidth } = libFunctions()

function openIt (cinfo, tx) {
  console.log(`-openIt-CourseInfo=`, cinfo)
  tee.value = tx
  courseName.value = cinfo.name
  slope.value = cinfo.slope
  rating.value = cinfo.rating
  Object.keys(cinfo.pars).forEach((key, i) => pars.value[i] = cinfo.pars[key])
  Object.keys(cinfo.hcaps).forEach((key, i) => caps.value[i] = cinfo.hcaps[key])
  Object.keys(cinfo.yards).forEach((key, i) => yards.value[i] = cinfo.yards[key])
  f9par.value = pars.value.slice(0,  9).reduce((a, b) => a + b, 0)
  b9par.value = pars.value.slice(9, 18).reduce((a, b) => a + b, 0)
  totalpar.value = pars.value.reduce((a, b) => a + b, 0)
  f9yard.value = yards.value.slice(0,  9).reduce((a, b) => a + b, 0)
  b9yard.value = yards.value.slice(9, 18).reduce((a, b) => a + b, 0)
  totalyard.value = yards.value.reduce((a, b) => a + b, 0)
  opened.value = true
}
function shadowClass (i) {
  if (i>=0) return "inset-shadow-down flex flex-center inline " + getColorBG(i)
  // else return "inset-shadow-down flex flex-center inline"
}
function getColorBG (i) {
  return pars.value[i] == 4 ? 'shadow-box-blue' : pars.value[i] == 3 ? 'shadow-box-red' : 'shadow-box-pink'
}
</script>

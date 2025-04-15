<template>
<q-dialog v-model="opened">
  <div style="border:2px solid #ccc" class="text-h6">
    <q-tr><td colspan="5" class="bg-teal-10 text-cyan-3 text-h5 text-center">过 去 90 天 的 <b class="text-green">测试</b> 平 均 值</td></q-tr>
    <q-tr>
      <td class="bg-cyan-10  text-center text-px-sm text-cyan-1" style="width:110px">测试</td>
      <td class="bg-green-10 text-center text-px-sm text-cyan-1 text-no-wrap" style="width:110px">正常</td>
      <td class="bg-amber-10 text-center text-px-sm text-cyan-1 text-black" style="width:320px">Prediabetes</td>
      <td class="bg-pink-10  text-center text-px-sm text-cyan-1" style="width:200px">Diabetes</td>
    </q-tr>
    <q-tr>
      <td class="bg-cyan-10 text-center text-cyan-2">{{ row.a1cpX }}</td>
      <td class="bg-green-9 text-center text-cyan-2">5.7</td>
      <td class="bg-amber-10 text-center text-no-wrap text-cyan-1">5.7 ~ 6.4</td>
      <td class="bg-pink-10 text-cyan-2 text-center -text-no-wrap">> 6.4</td>
    </q-tr>
    <q-tr>
      <td class="bg-cyan-10 text-center text-cyan-2">{{ row.eag }}</td>
      <td class="bg-green-9 text-center text-cyan-2"> 117</td>
      <td class="bg-amber-10 text-center text-cyan-1">117 ~ 137</td>
      <td class="bg-pink-10 text-cyan-2 text-center"> > 137</td>
    </q-tr>
    <q-tr>
      <td class="bg-cyan-10 text-center text-cyan-2">{{ row.a1c }}</td>
      <td class="bg-green-9 text-center text-cyan-2">38.8</td>
      <td class="bg-amber-10 text-center text-cyan-1 text-no-wrap">38.8 ~ 46.5</td>
      <td class="bg-pink-10 text-center text-cyan-2"> > 46.5</td>
    </q-tr>
  </div>
</q-dialog>
</template>
<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'

import { axiosFunctions } from '../src/composables/axiosFunctions'
const { gaxios, paxios } = axiosFunctions()
import { dayFunctions } from '../src/composables/dayFunctions'
const { yyyymmddHHMM, today, getDateGap, between, chwk2 } = dayFunctions()
import { libFunctions } from '../src/composables/libFunctions'
const { isDesk, isIM, palist, dalist } = libFunctions()

const props = defineProps({
  row: { type: Object },
})
//== data sections
const opened = ref(false)

console.log('-ST-gluar_m_info')
emitter.on('open-info', () => { opened.value = true })

//== function section
function funct () {
  console.log('-fn-showAllCharts',)
  // emitter.emit('open-ChartProxy')
}
</script>

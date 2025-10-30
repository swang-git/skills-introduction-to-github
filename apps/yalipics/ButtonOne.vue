<template>
<q-btn glossy round class="bg-teal-10" @click="openChart()">
  <q-icon :name="cicon" :style="cstyle" :size="csize" color="cyan-2" />
</q-btn>
</template>
<script setup>
import emitter from "tiny-emitter/instance";
import { ref } from 'vue'
const props = defineProps({
  chart: { type: String, required: true },
  cicon: { type: String, required: true },
  cstyl: { type: String, required: false },
  csize: { type: String, required: false },
})
const cstyle = ref(null)
console.log(`-ST-ButtonOne`)
if (['全','年'].includes(props.cicon)) cstyle.value = 'padding: 1px 1px 9px 1px'
else if (['月/年','年/月'].includes(props.cicon)) cstyle.value='padding: 5px 1px 9px 1px'

function openChart() {
  console.log(`-fn- open-ChartsProxy1 ${props.chart}`);
  emitter.emit("open-ChartsProxy1", props.chart);
}
</script>

<template>
<q-card-actions>
  <q-btn glossy rounded class="bg-teal-9" @click="moveChart(-1)">
    <q-icon left name="arrow_circle_left" size="md" color="lime" />
    <span class="text-bold text-cyan-2 text-body1" style="margin: 0 4px 0 -10px">{{ ptag }}</span>
  </q-btn>
  <q-btn round glossy class="bg-teal-9" @click="openChart()">
    <q-icon name="年" style="padding: 1px 1px 9px 1px" size="25px" color="cyan-2" />
  </q-btn>
  <q-btn glossy rounded class="bg-teal-9" @click="moveChart(1)">
    <span class="text-bold text-cyan-2 text-body1" style="margin: 0 4px 0 4px">{{ ntag }}</span>
    <q-icon name="arrow_circle_right" size="md" color="lime" />
  </q-btn>
</q-card-actions>
</template>
<script setup>
import emitter from "tiny-emitter/instance"
import { ref } from 'vue'
const props = defineProps({
  chart: { type: String, required: true },
})
const emit = defineEmits(['move-chart'])
const ptag = ref(null)
const ntag = ref(null)
console.log(`-ST-ButtonTwo`)
if (['cats', 'yrmo'].includes(props.chart)) {
  ptag.value = "上一年"
  ntag.value = "下一年"
} else if (['ympi', 'moyr'].includes(props.chart)) {
  ptag.value = "上个月"
  ntag.value = "下个月"
}

function moveChart (pn) {
  console.log(`-fn-moveChart emit to AllButtons.moveChart pn=${pn}`)
  emit('move-chart', pn)
}
function openChart () {
  console.log(`-fn-open-ChartsProxy1 ${props.chart}`)
  emitter.emit('open-ChartsProxy1', 'year')
}
</script>

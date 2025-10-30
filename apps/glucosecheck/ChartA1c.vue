<template>
<div class="text-bold">
  <canvas id="a1c-chart" :height="isIM ? 240 : 150" class="bg-cyan-3" />
  <q-card square>
    <q-card-actions class="text-h6" align="between">
      <div v-if="isDesk">90 Day Hemoglobin A1C</div>
      <div v-else>90 Day A1C</div>
      <q-btn round glossy icon="note" color="blue-9" @click="showA1cDefinitions()">
        <q-tooltip v-if="isDesk" class="text-h6 text-cyan-2 bg-blue-10">注释</q-tooltip></q-btn>
      <q-btn round glossy icon="donut_small" color="pink" @click="$emit('open-chart', 'dnt')">
        <q-tooltip v-if="isDesk" class="text-h6 text-cyan-2 bg-blue-10">90 day details</q-tooltip></q-btn>
      <q-btn round glossy icon="trending_down" color="accent" @click="$emit('open-chart', 'eag')">
        <q-tooltip v-if="isDesk" class="text-h6 text-cyan-2 bg-blue-10">Sugar Level Trending</q-tooltip></q-btn>
      <q-btn round glossy icon="空" color="teal-10" style="padding:0 0 8px 1.1px" @click="$emit('open-chart', 'clv')">
        <q-tooltip v-if="isDesk" class="text-h6 text-yellow bg-blue-10">Fasting(空腹)Curve(中国标准)</q-tooltip></q-btn>
      <q-btn round glossy icon="close" color="red" v-close-popup />
    </q-card-actions>
  </q-card>
</div>
</template>
<script setup>
import { ref, onMounted, reactive } from 'vue'
import emitter from 'tiny-emitter/instance'
import Chart from 'chart.js/auto'
import ChartDataLabels from 'chartjs-plugin-datalabels'
Chart.register(ChartDataLabels);
import cfg from './chart-bars-config.js'
import { libFunctions } from '../src/composables/libFunctions'
const { isDesk, isIM } = libFunctions()
import { infoFunctions } from '../src/composables/infoFunctions'
const { getA1cDefinitions } = infoFunctions()
const emit = defineEmits(['open-chart'])
const props = defineProps({
  gluSections: { type: Array },
})
const config = cfg
var ctx = {}
var myChart = reactive({})

console.log('-ST-ChartA1c')
onMounted(() => {
console.log('-MT-ChartA1c')
  createChart()
  setData()
})
function setData () {
  const a1pData = props.gluSections.map(p => p.a1p)
  const a1cData = props.gluSections.map(p => p.glu)
  const a1cLabels = props.gluSections.map(p => p.dat)
  // console.log('-fn-setData for a1c', a1cLabels, this.gluSections)
  // console.table(a1pData)
  config.data.labels = isDesk ? a1cLabels : a1cLabels.map(p => p.substring(5, 10))
  config.data.datasets[0].data = a1cData
  config.data.datasets[0].label = '中国度量(mmol/L) below 6.5% is normal'
  // config.data.datasets[0].backgroundColor = 'green'
  // config.data.datasets[0].borderColor = 'red'
  // config.data.datasets[0].borderWidth = 1
  config.data.datasets[1].data = a1pData
  config.data.datasets[1].label = '美国度量(a1c) below 5.7% is normal'
  myChart.update()
}
function createChart () {
  ctx = document.getElementById('a1c-chart').getContext('2d')
  myChart = new Chart(ctx, config)
}
function showA1cDefinitions () {
  let x = getA1cDefinitions()
  emitter.emit('open-InfoDisplay', x.tit, x.msg)
}
</script>

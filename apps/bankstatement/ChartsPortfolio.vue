<template>
<div class="q-pa-xs" style="margin-top:-2px">
  <canvas id="charts-portfolio" height="150" style="background:lightgreen" />
  <q-card square dense>
    <q-card-actions class="text-h6" align="between">
      <q-btn round glossy icon="close" color="red" @click="emit('close-charts')" style="" />
      <div v-if="isDesk">The Charts of My Portfolios</div>
      <div v-else>My Portfolios</div>
      <q-btn v-if="start<props.chdata.length" round glossy icon="arrow_left"  color="teal" @click="prev_12_month()" />
      <q-btn v-else round flat />
      <q-btn v-if="start>36" round glossy icon="arrow_right" color="teal" @click="next_12_month()" />
      <q-btn v-else round flat />
    </q-card-actions>
  </q-card>
</div>
</template>
<script setup>
import { ref, onMounted } from 'vue'
import Chart from 'chart.js/auto'
import cfg from '../src/components/chart-multi-line-config'
// import cfg from '../src/components/chart-multi-bar-config'
import { libFunctions } from '../src/composables/libFunctions'
const { isDesk } = libFunctions()
const props = defineProps(['chname', 'chdata'])
const emit = defineEmits(['close-charts'])
const config = cfg
// const start = ref(props.chdata.length)
const start = ref(36)
var myChart = {}

console.log(`-ST-ChartsPortfolio chartsname=${props.chname}`)

onMounted(() => {
  // console.log('-MT-ChartsPortfolio', props.chdata)
  createChart()
})
setData()

function prev_12_month () {
  start.value = start.value + 36
  if (start.value > props.chdata.length) start.value = props.chdata.length
  console.log(`-fn-prev_12_month start=${start.value}`)
  setData()
  myChart.update()
}
function next_12_month () {
  start.value = start.value - 36
  if (start.value < 36) start.value = 36
  console.log(`-fn-next_12_month start=${start.value}`)
  setData()
  myChart.update()
}
function setData () {
  console.log('-fn-setData ChartsPortfolio', props.chdata)
  console.log(`-fn-setData start=${start.value}`)
  const da = props.chdata.slice(start.value-36, start.value)
  // console.table(da[0])
  let fida = da.map(p => { if (p.bank === 'Fidelity') return p.end_balance }).filter(p => p > 0).reverse()
  let fila = da.map(p => { if (p.bank === 'Fidelity') return p.end_date.substring(0, 7) }).filter(p => p != undefined).reverse()
  let chda = da.map(p => { if (p.bank === 'Chase') return p.end_balance }).filter(p => p > 0).reverse()
  let boda = da.map(p => { if (p.bank === 'BOA') return p.end_balance }).filter(p => p > 0).reverse()
  config.data.labels = fila
  // console.log(fida)
  // console.log(fila)
  config.data.datasets[0].data = fida
  config.data.datasets[0].label = 'Fidelity'
  config.data.datasets[1].data = boda
  config.data.datasets[1].label = 'B O A'
  config.data.datasets[2].data = chda
  config.data.datasets[2].label = 'Chase'
}
function createChart () {
  const ctx = document.getElementById('charts-portfolio').getContext('2d')
  myChart = new Chart(ctx, cfg)
}
</script>

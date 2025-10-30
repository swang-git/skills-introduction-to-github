<template>
<div class="text-bold">
  <canvas id="chart-a1x" :style="{ background:compDcenterTxt[4] || compIcenterTxt[4] || 'cyan' }" />
  <div v-if="isDesk" class="absolute-center text-center text-h4" style="position:relative;margin:-277px 0 0 2px;height:270px">
    <div v-if="compDcenterTxt[0]==null" class="text-h4 text-pink q-pt-xl">
      <div class="text-h4 text-green-9">当前 {{ gluNow }}(%)</div>
      <div class="text-h5 text-amber-10">九十天糖化血红蛋白</div>
      <div class="text-h4 text-red-8">最初 {{ gluStart }}(%)</div>
    </div>
    <div v-else class="text-h4 text-white">
      <div>{{ compDcenterTxt[0] }}</div>
      <div style="font-size:24px">{{ compDcenterTxt[3] }}</div>
      <div style="font-size:26px">{{ compDcenterTxt[1] }}</div>
      <div style="font-size:40px">持续{{ compDcenterTxt[2] }}天</div>
      <div style="font-size:36px">{{ compDcenterTxt[5] }}</div>
    </div>
  </div>
  <div v-else class="absolute-center text-white text-center" :style="isDesk ? { 'margin-top':'-105px' }: { marginTop:'-15px' }">
    <div v-if="compIcenterTxt[0]==null" class="text-bold">
      <div class="text-body1 text-bold text-green-9">当前 {{ gluNow }}(%)</div>
      <div class="text-h6    text-bold text-amber-10">糖化血红蛋白</div>
      <div class="text-body1 text-bold text-red-8">最初 {{ gluStart }}(%)</div>
    </div>
    <div v-else>
      <div>{{ compIcenterTxt[0] }}</div>
      <div>{{ compIcenterTxt[3] }}</div>
      <div>{{ compIcenterTxt[1] }}</div>
      <div>持续{{ compIcenterTxt[2] }}天</div>
    </div>
  </div>
  <q-card square>
    <q-card-actions class="text-h6" align="between">
      <span v-if="isDesk">九十天糖化血红蛋白各种数据</span>
      <q-btn round glossy icon="note" color="blue-9" @click="showA1cDefinitions">
        <q-tooltip v-if="isDesk" class="text-h6 text-cyan-2 bg-blue-10">注释</q-tooltip></q-btn>
      <q-btn round glossy icon="trending_down" color="accent" @click="$emit('open-chart', 'eag')">
        <q-tooltip v-if="isDesk" class="text-h6 text-cyan-2 bg-blue-10">Sugar Level Trending</q-tooltip></q-btn>
      <q-btn round glossy icon="空" color="teal-9" style="padding:0 0 8px 1.1px" @click="$emit('open-chart', 'clv')">
        <q-tooltip v-if="isDesk" class="text-h6 text-yellow bg-blue-10">Fasting(空腹)Curve(中国标准)</q-tooltip>
      </q-btn>
      <q-btn round glossy icon="equalizer" color="primary" @click="$emit('open-chart', 'a1c')">
        <q-tooltip v-if="isDesk" class="text-h6 text-cyan-2 bg-blue-10">Hemoglobin mmol/L中国度量</q-tooltip></q-btn>
      <q-btn round glossy icon="circle" color="cyan" @click="showInit()"><q-tooltip v-if="isDesk" class="text-h6 text-cyan-2 bg-blue-10">回到初始状态</q-tooltip></q-btn>
      <q-btn round glossy icon="close" color="red"  v-close-popup><q-tooltip v-if="isDesk" class="text-h6 text-cyan-2 bg-blue-10">关闭</q-tooltip></q-btn>
    </q-card-actions>
  </q-card>
</div>
</template>
<script setup>
import { ref, reactive, onMounted, computed }from 'vue'
import emitter from 'tiny-emitter/instance'
import Chart from 'chart.js/auto'
Chart.register('ChartDataLabels')
import chartConfig from './chart-donut-config.js'
import { libFunctions } from '../src/composables/libFunctions'
const { isDesk, isIM } = libFunctions()
import { infoFunctions } from '../src/composables/infoFunctions'
const { getA1cDefinitions } = infoFunctions()

const emit = defineEmits(['open-chart'])
const props = defineProps({
  gluSections: { type: Array }
})
var config = chartConfig.Desk
var ctx = {}
var myChart = reactive({})
const icenterTxt = ref([null, null, null, null])
const dcenterTxt = ref([null, null, null, null])
var gluNow = ref(null)
var gluStart = ref(null)

console.log(`-ST-ChartA1h isDesk=${isDesk}`)
config = (isIM ? chartConfig.IM : chartConfig.Desk)
emitter.on('icenter-txt', (x) => icenterTxt.value = x)
emitter.on('dcenter-txt', (x) => dcenterTxt.value = x)

onMounted (() => {
  console.log('-MT-ChartA1h')
  createChart()
  setData()
})

function createChart () {
  ctx = document.getElementById('chart-a1x').getContext('2d')
  myChart = new Chart(ctx, config)
}
function showA1cDefinitions () {
  let x = getA1cDefinitions()
  emitter.emit('open-InfoDisplay', x.tit, x.msg)
}
function showInit () {
  icenterTxt.value[0] = null
  dcenterTxt.value[0] = null
  icenterTxt.value[4] = null
  dcenterTxt.value[4] = null
}
function setData () {
  let da = props.gluSections
  gluNow = da[da.length-1].a1p
  gluStart = props.gluSections[0].a1p
  config.data.labels = da.map(p => p.sbl)
  config.data.datasets[0].glus = da.map(p => p.glu)
  config.data.datasets[0].data = da.map(p => p.a1c)
  config.data.datasets[0].a1cp = da.map(p => p.a1p)
  config.data.datasets[0].eags = da.map(p => p.eag)
  config.data.datasets[0].days = da.map(p => p.day)
  config.data.datasets[0].dgap = da.map(p => p.lbl)
  config.data.datasets[0].eagx = da.map(p => p.exg)
  myChart.update()
}
const compIcenterTxt = computed(() => { return icenterTxt.value })
const compDcenterTxt = computed(() => { return dcenterTxt.value })

</script>


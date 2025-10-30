<template>
<div style="margin:190px 0 0 161px;width:580px" class="fixed">
  <canvas id="convas3" class="bg-amber-4"  />
  <q-card square class="bg-cyan-2">
    <q-card-actions align="center">
      <q-btn v-if="nPayes>step" glossy rounded color="teal-9" @click="moveChart(-1)"><q-icon name="arrow_circle_left"  color="amber-4" />上 个 图</q-btn>
      <q-btn glossy round color="amber-10" icon="cancel" v-close-popup />
      <q-btn v-if="nPayes>step" glossy rounded color="teal-9" @click="moveChart(+1)">下 个 图<q-icon name="arrow_circle_right" color="amber-4" /></q-btn>
    </q-card-actions>
  </q-card>
</div>
</template>
<script setup>
import { ref, reactive, onMounted } from 'vue'
import Chart from 'chart.js/auto'
import ChartDataLabels from 'chartjs-plugin-datalabels'
import cfg from './chart-paye-config.js'
Chart.register(ChartDataLabels)
const props = defineProps({
  data: { type: Array },
  year: { type: Number },
})
let myChart = reactive({})
let ctx = reactive({})
let config = reactive({})
let datv = reactive([])
let showda = reactive([])
let cntx = reactive({})
let payeIdv = reactive({})
const totalspending = ref(0)
const end = ref(null)
const numStep = ref(null)
const step = 5
const idx = ref(0)
const catx = ref(null)
const subc = ref(null)
const nPayes = ref(null)
const courseAlias = {}

console.info(`-ST-ChartPaye`)
config = cfg

onMounted(() => {
  console.log('-MT-ChartPaye')
  createChart()
  setData()
})
function createChart () {
  console.log('-fn-createChart Paye')
  ctx = document.getElementById('convas3').getContext('2d')
  myChart = new Chart(ctx, {
    type: config.type,
    data: config.data,
    options: config.opts
  })
  totalspending.value = props.data.map(p => p.cost).reduce((total, next) => total + parseFloat(next), 0)
  catx.value = props.data[0].cats
  subc.value = props.data[0].subc
  let costx = {}
  let pyidx = {}
  let payex = []
  props.data.forEach(row => {
    if (row.paye == null) {
      // console.log(`-fn-createChart`, row)
      return
    }
    let paye = row.paye
    const cost = parseFloat(row.cost)
    const payeId = row.payeId
    const AT = new RegExp(/Architects(.*)/i)
    const CSn = new RegExp(/Charleston\s+Springs\s+North(.*)/i)
    const CSs = new RegExp(/Charleston\s+Springs\s+South(.*)/i)
    const CV = new RegExp(/Cherry\s+Valley(.*)/i)
    const CC = new RegExp(/Country\s+Club/i)
    const ER = new RegExp(/Eagle\s+Ridge(.*)/i)
    const GC = new RegExp(/Golf\s+Club/i)
    const Gc = new RegExp(/Golf\s+Course/i)
    const NV = new RegExp(/Neshanic\s+Valley(.*)/i)
    const Nv = new RegExp(/Neshanic\s+GC(.*)/i)
    const MF = new RegExp(/Make\s+Field(.*)/i)
    const LW = new RegExp(/Lakewood(.*)/i)
    const HB = new RegExp(/High Bridge Hill(.*)/i)
    const BP = new RegExp(/Bethpage SP(.*)/i)
    const HH = new RegExp(/Hominy Hill(.*)/i)
    const HG = new RegExp(/Heron Glen(.*)/i)
    const Cs = new RegExp(/Crystal\s+Spring(.*)/i)       
    const FV = new RegExp(/Flanders\s+Valley(.*)/i)       
    const JP = new RegExp(/(.*)Jasna\s+Polana(.*)/i)       
    const HV = new RegExp(/Hopewell\s+Valley(.*)/i)       
    const FG = new RegExp(/Forsgate\s+CC(.*)/i)  
    const RB = new RegExp(/Royce\s+Brook(.*)/i)  
    const OC = new RegExp(/Ocean\s+County(.*)/i)  
    const SV = new RegExp(/Seaview\s+GC(.*)/i)  
    const MV = new RegExp(/Mountain View(.*)/i)  
    const MOe = new RegExp(/Mercer Oaks GC East(.*)/i)  
    const MOw = new RegExp(/Mercer Oaks GC West(.*)/i)  
    paye = paye
    .replace(Gc, 'GC')
    .replace(GC, 'GC')
    .replace(CC, 'CC')
    .replace(AT, 'Architects')
    .replace(BP, 'Bethpage Black')
    .replace(CSn, 'Charleston Springs North')
    .replace(CSs, 'Charleston Springs South')
    .replace(CV, 'Cherry Valley')
    .replace(Cs, 'Crystall Springs')
    .replace(ER, 'Eagle Ridge GC')
    .replace(NV, 'Neshanic Valley')
    .replace(Nv, 'Neshanic Valley')
    .replace(MF, 'Makefield Highlands')
    .replace(LW, 'Lakewood')
    .replace(HB, 'High Bridge Hills')
    .replace(HH, 'Hominy Hill')
    .replace(HG, 'Heron Glen GC')
    .replace(FV, 'Flanders Valley')
    .replace(JP, 'Jasna Polana')
    .replace(HV, 'Hopewell')
    .replace(FG, 'Forsgate CC')
    .replace(RB, 'Royce Brook')
    .replace(OC, 'Ocean County GC')
    .replace(SV, 'Seaview GC')
    .replace(MV, 'Mountain View')
    .replace(MOw, 'Mercer Oaks West')
    .replace(MOe, 'Mercer Oaks East')
    .trim()
    catx.value = props.data[0].cats
    subc.value = props.data[0].subc
    if (payex.includes(payeId)) {
      costx[payeId] += cost
      cntx[payeId] += 1
    } else {
      payex.push(payeId)
      pyidx[payeId] = payeId
      courseAlias[payeId] = paye
      costx[payeId] = cost
      cntx[payeId] = 1
    }
  })
  datv = Object.entries(costx).sort((a, b) => a[0].toUpperCase() < b[0].toUpperCase() ? -1 : 1)
  nPayes.value = datv.length
  console.log(`-CK-numPayes=${nPayes.value} step=${step}`)
  // console.table(datv)
  for (const [key, val] of Object.entries(pyidx).sort((a, b) => a[0].toUpperCase() < b[0].toUpperCase() ? -1 : 1)) {
    payeIdv[key] = val
  }
  numStep.value = parseInt(datv.length/step)
  end.value = datv.length % step === 0 ? numStep.value - 1: numStep.value
}
function moveChart (pn) {
  idx.value += pn
  const nextda = datv.slice((idx.value + 0) * step, (idx.value + 1) * step)
  if (idx.value < 0) idx.value = end.value
  else if (nextda.length === 0) idx.value = 0
  setData()
}
function setData () {
  // console.info(`-ck-idx+1=${idx.value + 1} datv.length=${datv.length}`)
  console.info(`-fn-setData`)
  showda = datv.slice(idx.value * step, (idx.value + 1) * step)
  showda.sort((a, b) => a[1] - b[1])
  const dfmt = []
  const cats = []
  const vals = []
  const cnts = []
  const pyid = []
  let comp = 0
  const len = showda.length
  const max = Math.max(...showda.map(p => p[1]))
  comp = max / (step - 1)
  showda.forEach(d => {
    if (Math.abs(d[1]) < comp) {
      dfmt.push((d[1] + (d[1] > 0 ? comp : -comp)).toFixed(2))
      vals.push(d[1].toFixed(2))
    } else {
      dfmt.push(d[1].toFixed(2))
      vals.push(null)
    }
    cats.push(courseAlias[d[0]])
    pyid.push(payeIdv[d[0]])
    cnts.push(cntx[d[0]])
  })
  config.data.datasets[0].cats = cats
  config.data.datasets[0].data = dfmt
  config.data.datasets[0].vals = vals
  config.data.datasets[0].cnts = cnts
  config.data.datasets[0].pyid = pyid
  config.data.cats = catx.value
  config.data.year = props.year
  // console.log(`%ctotalspending=${totalspending.value}`, 'color:red;font-size:12px')
  if (props.year == null) myChart.options.plugins.title.text = catx.value + '/' + subc.value + ' 总消费分类（' + totalspending.value.toFixed(2) + '）'
  else myChart.options.plugins.title.text = catx.value + '/' + subc.value + '（' + props.year + '）消费分类（' + totalspending.value.toFixed(2) + '）'
  config.data.total = totalspending.value
  myChart.update()
}
</script>

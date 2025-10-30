<template>
  <div style="margin:85px 0 0 8px">
    <canvas id="convas" class="bg-lime-1" height="200" />
    <AllButtons chart="moyr" @move-chart="moveChart" />
  </div>
</template>
<script setup>
import Chart from 'chart.js/auto'
import ChartDataLabels from 'chartjs-plugin-datalabels'
import cfg from './chart-moyr-config.js'
import { ref, reactive, onMounted } from 'vue'
import AllButtons from './AllButtons'
Chart.register(ChartDataLabels)
const props = defineProps({
	data: { type: Array },
	mnth: { type: Number }
})
const emit = defineEmits(['set-mnth'])
// const app = createApp({})
// app.component(AllButtons, AllButtons)
let myChart = reactive({})
let ctx = reactive({})
let config = reactive({})
// const chartMonth = ref(11)
let chartMonth = 1
console.log("-ST-ChartMoYr")
config = cfg
onMounted(() =>{
console.log("-MT-ChartMoyr")
	createChart()
	setData()
})
function createChart () {
	console.log("-fn-createChart")
	ctx = document.getElementById('convas').getContext("2d")
	myChart = new Chart(ctx, {
		type: config.type,
		data: config.data,
		options: config.opts
	})
}
function moveChart (pn) {
	chartMonth += pn
	if (chartMonth > 12) chartMonth = 1
	else if (chartMonth < 1) chartMonth = 12
	console.log(`-fn-moveChart pn=${pn} mnth=${chartMonth}`)
	emit("set-mnth", pn)
	setTimeout(() => setData(), 100)
}
function setData () {
	console.log(`-fn-moyr-setData mnth=${chartMonth}`)
	myChart.options.plugins.title.text = '各 年 度 的 ' + chartMonth + ' 月 份 支 出'
	var mnth = chartMonth < 10 ? '0' + chartMonth : chartMonth
	const mpatt = new RegExp('-' + mnth + '-')
	const da = props.data.filter(p => mpatt.test(p.date))
	var mdata = {}
	da.forEach(row => {
		var year = row.date.substring(0, 4), cost = parseFloat(row.cost)
		if (mdata[year] == undefined) {
			mdata[year] = cost
		} else {
			mdata[year] += cost
		}
	})
	const keyvals = Object.entries(mdata).sort((a, b) => a[0] - b[0])
	// console.log(`-setData-CK: keyvals`, keyvals.map(p => p[0]))
	config.data.labels = keyvals.map(p => p[0])
	const fmtda = keyvals.map(p => p[1])
	const data = []
	const vals = []
	const K = 1000
	fmtda.forEach(p => {
		let x = parseFloat(p)
		if (x > 10*K) {
			data.push((x/10).toFixed(0))
			vals.push(x.toFixed(2))
		} else {
			data.push(x.toFixed(0))
			vals.push(x.toFixed(2))
		}
	})
	config.data.mnth = chartMonth
	config.data.datasets[0].data = data
	config.data.datasets[0].vals = vals
	// config.data.datasets[0].data = keyvals.map(p => p[1].toFixed(0))
	// config.data.datasets[0].vals = keyvals.map(p => p[1].toFixed(2))
	myChart.update()
}
</script>
  
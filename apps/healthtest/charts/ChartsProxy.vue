<template>
<div>
<q-dialog v-model="opened" :full-width="isDesk" :maximized="isIM" position="top">
  <div :class="{ 'q-ml-xl':isDesk }">
    <HTCharts v-if="chname==='Health Test Results'" :chdata="chdata" :chname="chname" />
    <ChartsGlucose v-if="chname==='Glucose Charts'" :chdata="chdata" :chname="chname" @close-charts="opened=false" />
  </div>
</q-dialog>
</div>
</template>
<script setup>
import { ref, createApp } from 'vue'
import emitter from 'tiny-emitter/instance'
import { libFunctions } from 'src/composables/libFunctions'
import HTCharts from './charts'
import ChartsGlucose from '../../glucosecheck/ChartClv'
const props = defineProps({
  chdata: { type: Array },
  chname: { type: String },
})
// const app = createApp({})
// app.component('charts', charts)
const { isDesk, isIM } = libFunctions()
emitter.on('open-ChartsProxy', () => { opened.value = true })
const opened = ref(false)
console.log(`-ST-ChartsProxy chartname=${props.chname}`)
</script>

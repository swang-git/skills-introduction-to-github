<template>
<q-dialog v-model="opened" :full-width="isDesk" :maximized="isIM" position="top">
<div :class="{ 'q-ml-xl':isDesk }">
  <ChartClv :clvdata="clvs"     @open-chart="showChart" v-if="showx.clv" />
  <chartEag :gluSections="glus" :gludata="gludata" :xlabel="xlabel" @open-chart="showChart" v-if="showx.eag" />
  <chartA1c :gluSections="glus" @open-chart="showChart" v-if="showx.a1c" />
  <ChartA1cxDonut :gluSections="glus" @open-chart="showChart" v-if="showx.dnt" />
</div>
</q-dialog>
</template>
<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'

import { libFunctions } from '../src/composables/libFunctions'
const { isDesk, isIM } = libFunctions()

import ChartClv from '../glucosecheck/ChartClv'
import ChartEag from '../glucosecheck/ChartEag'
import ChartA1c from '../glucosecheck/ChartA1c'
import ChartA1cxDonut from '../glucosecheck/ChartA1xDonut'

const props = defineProps({
  clvs: { type: Array },
  glus: { type: Array },
  gludata: { type: Array },
  xlabel: { type: Array },
  a1cData: { type: Array },
  a1cLabels: { type: Array },
})
const opened = ref(false)
const showx = ref({})

console.log(`-ST-ChartProxy`)
showx.value = { clv:true, dnt:false, eag:false, a1c:false }
emitter.on('open-ChartProxy', () => openIt())

function openIt () {
  // showx.value = { clv:true, dnt:false, eag:false, a1c:false }
  // console.log(`-fn-CK-openIt gluSections`, props.glus)
  opened.value = true
}
function showChart (chartName) {
  // console.log(`-CK-fn-showChart chartName=${chartName}`, props.glus)
  for (const key in showx.value) { showx.value[key] = false }
  showx.value[chartName] = true
}
// function show () {
//   // console.log('-fn-show', this.glus)
//   this.$refs.dialog.show()
// },

// // following method is REQUIRED
// // (don't change its name --> "hide")
// hide () {
//   this.$refs.dialog.hide()
// },

// onDialogHide () {
//   // required to be emitted
//   // when QDialog emits "hide" event
//   this.$emit('hide')
// }

// onOKClick () {
//   // on OK, it is REQUIRED to
//   // emit "ok" event (with optional payload)
//   // before hiding the QDialog
//   this.$emit('ok')
//   // or with payload: this.$emit('ok', { ... })

//   // then hiding dialog
//   this.hide()
// }

// onCancelClick () {
//   // we just need to hide the dialog
//   this.hide()
// }
</script>

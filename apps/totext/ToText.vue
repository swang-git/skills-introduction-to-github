<template>
<div class="row justify-evenly">
  <div class="text-h6 text-white q-pa-xs">File to be converted to text</div>
  <q-input v-model="filename" filled borderless dense dark type="text" class="bg-cyan-10 text-h6 q-pa-xs">
    <template v-slot:append>
      <q-icon v-if="filename===''" name="convert" />
      <q-icon v-else name="clear" class="cursor-pointer" @click.stop.prevent="filename=''" />
    </template>
  </q-input>
  <q-btn glossy rounded class="bg-teal-9 q-my-xs" @click="convert2text">
    <span class="text-bold text-cyan-2 text-body1" style="margin: 0 4px 0 4px">convert</span>
    <q-icon name="arrow_circle_right" size="md" color="lime" />
  </q-btn>
</div>
<div>
  <div v-for="line in lines" :key="line">
    <div class="text-h6 text-white q-px-xs">{{ line }}</div>
  </div>
</div>
</template>
<script setup>
import { ref, computed } from 'vue'
import emitter from 'tiny-emitter/instance'
import { axiosFunctions } from '../src/composables/axiosFunctions'
const { gaxios } = axiosFunctions()
import { libFunctions } from '../src/composables/libFunctions'
const { buildApp } = libFunctions()
const lines = ref([])
const filename = ref('20230822_ERCP_results.pdf')
// const filename = ref('20231016_ERCP_instruction.pdf')

//== main
console.log('-ST-totext')
buildApp ('转换文字', 'totext')
emitter.on('totext-getText', (x) => showText(x))

function convert2text () {
  console.log(`-fn-convert2text filename=${filename.value}`)
  const path = process.env.API + '/totext/getText/' + filename.value
  gaxios(path)
}
function showText (da) {
  console.log(`-fn-showText status=${da.status}`)
  lines.value = da.text
}
</script>
<template>
<q-dialog v-model="opened" transition-show="rotate" transition-hide="rotate">
  <q-card style="min-height:136px">
    <q-card-section class="bg-teal-9 q-pa-sm">
      <table style="y-overflow:auto;margin:auto" class="bg-teal-7">
        <q-tr><td v-for="i in [ 5, 10, 15]"    :key=i.x><q-btn size="20px" color="teal-10" round :label="i" @click="setNumber(i)" /></td></q-tr>
        <q-tr><td v-for="i in [20, 25, 30]"    :key=i.x><q-btn size="20px" color="teal-10" round :label="i" @click="setNumber(i)" /></td></q-tr>
        <q-tr><td v-for="i in [40, 50, 60]"    :key=i.x><q-btn size="20px" color="teal-10" round :label="i" @click="setNumber(i)" /></td></q-tr>
        <q-tr><td v-for="i in ['-', 'X', '+']" :key=i.x><q-btn size="20px" color="teal-10" round :label="i" @click="setNumber(i)" /></td></q-tr>
      </table>
    </q-card-section>
  </q-card>
</q-dialog>
</template>
<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'
const opened = ref(false)
var pItemsPerPage = 25

console.log('-ST-PageNumPad')
emitter.on('items-per-page', (x) => pItemsPerPage = x)
emitter.on('open-PageNumPad', () => openIt())

function setNumber (np) {
  let ipp = pItemsPerPage
  console.info('setNumber', np, ipp)
  if (np === 'X') {
    opened.value = false
    return
  }
  if (np === '-') ipp -= 1
  else if (np === '+') ipp += 1
  else ipp = np
  emitter.emit('items-per-page', ipp)
  opened.value = false
}
function openIt () {
  console.log('open number pad')
  opened.value = true
}
</script>

<template>
<q-dialog v-model="opened" transition-show="rotate" transition-hide="rotate">
  <q-card style="min-height:136px">
    <q-card-section class="bg-teal-9 q-pa-sm">
      <table style="overflow:auto;margin:auto" class="bg-teal-7">
        <q-tr><td v-for="i in [ 8, 9, 10]"    :key=i.x><q-btn size="20px" color="teal-10" round :label="i" @click="setNumber(i)" /></td></q-tr>
        <q-tr><td v-for="i in [12, 20, 30]"    :key=i.x><q-btn size="20px" color="teal-10" round :label="i" @click="setNumber(i)" /></td></q-tr>
        <q-tr><td v-for="i in [40, 50, 60]"    :key=i.x><q-btn size="20px" color="teal-10" round :label="i" @click="setNumber(i)" /></td></q-tr>
        <q-tr><td v-for="i in ['-', 'X', '+']" :key=i.x><q-btn size="20px" color="teal-10" round :label="i" @click="setNumber(i)" /></td></q-tr>
      </table>
    </q-card-section>
  </q-card>
</q-dialog>
</template>
<script setup>
import { ref } from 'vue'
// import emitter from 'tiny-emitter/instance'
const opened = ref(false)

defineExpose({ openIt, closeIt })
const emit = defineEmits(['time-gap'])

console.log('-ST-NumGapPad')
// emitter.on('open-NumGapPad', () => openIt())

function setNumber (np) {
  console.log(`-fn-setNumber np=${np}`)
  if (np === 'X') {
    opened.value = false
    return
  }
  let gap = 0
  if (np === '-') gap -= 1
  else if (np === '+') gap += 1
  else gap = np
  emit('time-gap', gap)
  opened.value = false
}
function closeIt () { opened.value = false }
function openIt () {
  console.log('open num gap pad')
  opened.value = true
}
</script>

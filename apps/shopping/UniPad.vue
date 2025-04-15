<template>
<q-dialog v-model="opened" :maximized="isIM">
  <div class="absolute" :style="padpos">
    <table class="bg-teal-10">
      <q-tr class="text-white"><td v-for="x in ['qt', 'gl', 'oz', 'lb']" :key=x.i @click="setUni(x)"><q-btn round glossy size="xl">{{x}}</q-btn></td></q-tr>
      <q-tr class="text-white"><td v-for="x in ['gm', 'kg', '瓶', '个']" :key=x.i @click="setUni(x)"><q-btn round glossy size="xl">{{x}}</q-btn></td></q-tr>
      <q-tr class="text-white"><td v-for="x in ['块', '片', '粒', '只']" :key=x.i @click="setUni(x)"><q-btn round glossy size="xl">{{x}}</q-btn></td></q-tr>
      <q-tr class="text-white"><td v-for="x in ['把', '根', '打', '束']" :key=x.i @click="setUni(x)"><q-btn round glossy size="xl">{{x}}</q-btn></td></q-tr>
      <q-tr class="text-white"><td v-for="x in ['包', '盒', '罐', 'X']" :key=x.i @click="setUni(x)"><q-btn round glossy size="xl">{{x}}</q-btn></td></q-tr>
      <q-tr><td colspan="4" class="text-grey text-h5 text-center">{{ item.name }}: 度量</td></q-tr>
    </table>
  </div>
</q-dialog>
</template>
<script setup>
import emitter from 'tiny-emitter/instance'
import { ref, computed } from 'vue'
import { libFunctions } from '../src/composables/libFunctions'
const { isIM } = libFunctions()
const posX = ref(0)
const posY = ref(0)
const item = ref({})
const opened = ref(false)

const emit = defineEmits(['upd-item'])

console.log('-ST-UniPad')
emitter.on('open-UniPad', (itm) => openIt(itm))

const padpos =computed(() => { return 'top:' + posY.value + 'px;left:' + posX.value + 'px' })

function setUni (np) {
  np = np === 'X' ? null : np.toUpperCase()
  item.value.uni = np
  emit('upd-item', item.value)
  opened.value = false
}
function openIt (itm) {
  console.info('unipad', event, event.pageX, event.pageY, event.view.outerWidth, event.view.outerHeight)
  posX.value = event.pageX - 150
  posY.value = event.pageY + 20
  const winHeight = event.view.outerHeight
  const padHeight = 390
  if (posY.value + padHeight > winHeight) posY.value -= padHeight
  item.value = itm
  opened.value = true
  if (isIM) {
    posX.value = 0
    posY.value = 0
  }
}
</script>

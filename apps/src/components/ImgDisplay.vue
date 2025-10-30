<template>
<q-dialog v-model="opened" transition-show="scale" transition-hide="scale" :maximized="isMax">
  <q-card class="bg-teal-9">
    <q-card-section> <div class="text-h5 text-amber text-center" v-html="tit" /></q-card-section>
    <q-card-section class="text-h6 justify-center" style="margin:-15px 0 0 72px" v-html="msg" :class="{ 'rotate-90':is90 }"/>
    <q-card-actions align="between" style="margin:60px 0 0 0">
      <q-btn flat round icon="close" color="amber"  v-close-popup />
      <q-btn flat round :icon="is90 ? 'stay_current_portrait' : 'stay_current_landscape'" color="lime" @click="is90=!is90" />
    </q-card-actions>
  </q-card>
</q-dialog>
</template>

<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'
const isMax = ref(false)
const is90 = ref(false)
const tit = ref(null)
const msg = ref(null)
const opened = ref(false)

console.info('-ST-ImgDisplay')
emitter.on('open-ImgDisplay', (x) => openIt(x))

function openIt(t, m, max=false) {
  tit.value = t
  msg.value = m
  isMax.value = max
  opened.value = true
}
</script>
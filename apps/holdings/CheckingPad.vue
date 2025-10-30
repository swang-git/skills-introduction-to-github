<template>
<q-dialog v-model="opened" seamless fade>
  <div class="fixed" style="top:77px; max-width:200vw; border:2px solid cyan">
    <q-card :class="bgColor" class="text-white text-h6">
      <q-bar>
        <div class="q-pl-xl">{{ tit }}<q-tooltip anchor="top middle" class="bg-cyan-9 text-h6">{{ tip }}</q-tooltip></div>
        <q-space />
        <q-btn round dense flat icon="content_copy" @click="copyIt(astr + '==' + asum)"  />
        <q-space />
        <q-btn round dense flat icon="close" v-close-popup />
      </q-bar>

      <q-card-section>
        <div class="full-width no-wrap row">{{ astr }} == {{ asum }}</div>
      </q-card-section>
    </q-card>
  </div>
</q-dialog>
</template>
<script setup>
import { computed, ref } from 'vue'
import { copyToClipboard } from 'quasar'
var asum = null
var astr = null
var status = null
var tit = null
var tip = null
const opened = ref(false)
defineExpose({ openIt, closeIt })
console.log('-ST-CheckingPad')

const bgColor = computed(() => {
  return status === 'OK' ? 'bg-green-10' : 'bg-amber-10'
})
function closeIt () {
  opened.value = false
}
function openIt (actvstr, actvsum, tt, tp, sts) {
  astr = actvstr
  asum = actvsum
  status = sts
  tit = tt
  tip = tp
  opened.value = true
}
function copyIt (text) {
  copyToClipboard(text)
}
</script>
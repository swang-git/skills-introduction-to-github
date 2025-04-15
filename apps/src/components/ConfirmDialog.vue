<template>
<q-dialog v-model="opened" persistent>
  <q-card class="bg-amber-3">
    <q-card-section>
      <q-avatar icon="question_answer" color="red-9" text-color="yellow" />
      <span class="q-ml-sm text-h5 text-red">{{ tit }}</span>
    </q-card-section>

    <q-card-section>
      <span class="text-h6 text-black">{{ msg }}</span>
    </q-card-section>

    <q-card-actions align="right">
      <q-btn flat label="Cancel" color="green" v-close-popup @click="opened=false" />
      <q-btn glossy label="OK" round color="amber-10" @click="confirmed" v-close-popup />
    </q-card-actions>
  </q-card>
</q-dialog>
</template>

<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'
const emit = defineEmits(['user-confirmed'])
const opened = ref(false)
var tit = null
var msg = null
var action = null

// console.log('-ST-ConfirmDialog')
emitter.on('open-ConfirmDialog', (x, y, action) => openIt(x, y, action))

function confirmed () {
  // console.log('-fn-confirmed', tit, msg)
  opened.value = false
  emitter.emit('user-confirmed', action)
  emit('user-confirmed', action)
}
defineExpose({ openIt })
function openIt (t, m, act) {
  console.log(`-fn-openIt title=${t}`)
  tit = t
  msg = m
  action = act
  opened.value = true
  // console.log(`-fn-openIt open=${opened.value}`, t, m, act)
}
</script>

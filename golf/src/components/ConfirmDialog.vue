<template>
<div class="q-pa-md q-gutter-sm">
  <q-dialog v-model="opened" persistent>
    <q-card class="bg-amber-3">
      <q-card-section>
        <q-avatar icon="signal_wifi_off" color="primary" />
        <span class="q-ml-sm text-h5 text-red">{{ tit }}</span>
      </q-card-section>

      <q-card-section>
        <span class="text-h6 text-black">{{ msg }}</span>
      </q-card-section>

      <q-card-actions align="right">
        <q-btn flat label="Cancel" color="green" @click="cancelIt" />
        <!-- <q-btn flat label="Cancel" color="green" v-close-popup @click="opened=false" /> -->
        <q-btn flat label="Confirm" color="primary" @click="confirmed" v-close-popup />
      </q-card-actions>
    </q-card>
  </q-dialog>
</div>
</template>
<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'
const emit = defineEmits(['add-new-player', 'user-confirmed', 'user-cancelled'])
emitter.on('open-ConfirmDialog', (t, m) => openIt(t, m))
const opened = ref(false)
const tit = ref(null)
const msg = ref(null)
console.log('-ST-ConfirmeDialog')
function confirmed () {
  console.log('-fn-confirmed')
  opened.value = false
  if (tit.value === 'Add New Player') emit('add-new-player')
  else emit('user-confirmed')
}
function cancelIt () {
  console.log('-fn-cancelIt')
  opened.value = false
  if (tit.value === 'Add New Player') emit('add-new-player')
  else emit('user-cancelled')
}
function openIt (t, m) {
  console.log('-fn-ConfirmDialog.openIt', t, m)
  tit.value = t
  msg.value = m
  opened.value = true
}
</script>

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
var action = ref(null)

// console.log('-ST-ConfirmDialog')
emitter.on('open-ConfirmDialog', (x, y, action) => openIt(x, y, action))

function confirmed () {
  // console.log('-fn-confirmed', tit, msg)
  // emitter.emit('user-confirmed', action.value)
  emit('user-confirmed', action.value)
  opened.value = false
}
defineExpose({ openIt })
function openIt (tt, mg, act) {
  console.log(`-fn-openIt title=${tt} mg=${mg} actle=${act}`)
  tit = tt
  msg = mg
  action.value = act
  opened.value = true
  // console.log(`-fn-openIt open=${opened.value}`, t, m, act)
}
</script>

<template>
<q-dialog v-model="opened">
  <q-card class="bg-teal-10">
    <q-card-section>
      <div class="q-pa-xs">
        <q-date v-model="date" mask="YYYY-MM-DD" color="teal-8" class="bg-cyan-1" @click="setDate()" />
      </div>
    </q-card-section>
    <q-card-actions align="evenly">
      <q-btn round color="amber-10" icon="chevron_left" v-close-popup />
      <q-btn round color="pink-10" icon="delete" @click="setNullDate()" />
      <q-btn round color="teal-10" glossy label="OK" @click="sendDate()" />
    </q-card-actions>
  </q-card>
</q-dialog>
</template>
<script setup>
import { libFunctions } from '../composables/libFunctions'
const { opened } = libFunctions()
const emit = defineEmits(['set-dat'])
const date = null
const postDate = null

console.info(`-ST-DatePad date=${date}`)
function openIt (d) {
  date = d
  opened.value = true
}
function setNullDate () {
  postDate = null
  sendDate()
}
function setDate () {
  postDate = date
  console.log('-CK-fn-setDate-', date)
}
function sendDate () {
  console.log('-CK-fn-sendDate-', postDate)
  emit('set-date', postDate)
  opened.value = false
}
</script>

<template>
<q-dialog v-model="opened" persistent>
  <q-card class="bg-secondary" :style="{ width:isIM ? '100%' : '522px' }">
    <q-card-actions class="bg-teal-10" align="between">
      <q-btn round icon="link_off" color="red" @click="lnks.pop()"><q-tooltip class="text-body1 bg-red" anchor="top middle">remove the last link</q-tooltip></q-btn>
      <q-btn round icon="add_link" color="green" @click="lnks.push('add new link')"><q-tooltip class="text-body1 bg-green" anchor="top middle">add a new link</q-tooltip></q-btn>
    </q-card-actions>
    <q-card-section>
      <div v-for="(lnk, i) in lnks" :key=lnk>
        <q-btn rounded outlined no-caps class="text-left text-h6" style="width:100%" :label="lnk" @click="showEditLnk(i)">
          <q-tooltip class="text-body1 bg-teal-9" anchor="top middle">Edit this link</q-tooltip>
        </q-btn>
      </div>
    </q-card-section>
    <q-card-actions align="between" class="bg-teal-10">
      <q-btn round glossy v-close-popup icon="chevron_left" color="amber-8" />
      <span class="text-h5 text-center text-cyan-3">Add Document URL</span>
      <q-btn round icon="save" glossy color="primary" @click="$emit('upd-link', lnks); opened=false" />
    </q-card-actions>
  </q-card>
  <TxtPad @upd-lnk="updLnk" />
</q-dialog>
</template>
<script setup>
import { ref, computed } from 'vue'
import emitter from 'tiny-emitter/instance'
import { libFunctions } from '../composables/libFunctions'
const { isIM } = libFunctions()
import TxtPad from './TxtPad'

const props = defineProps(['label', 'obj'])
const emit = defineEmits(['upd-link'])

const opened = ref(false)
const lnks = ref([])

// console.log('-ST-lnkInput')
emitter.on('open-LnkInput', (x) => openIt(x))

function openIt (lnk) {
  // console.log(`-CK-fn-LnkInput.openIt lnk=${lnk}`)
  opened.value = true
  lnks.value = lnk
}
function showEditLnk(i) {
  console.log('-fn-editLnk', i)
  emitter.emit('open-TxtPad', i, lnks.value[i], 'Revise / Add Link')
}
function updLnk(i, lnk) {
  lnks.value[i] = lnk
  console.log(`-fn-updLnk i=${i} lnk=${lnk}`, lnks.value)
  emit('upd-link', lnks.value)
}
</script>
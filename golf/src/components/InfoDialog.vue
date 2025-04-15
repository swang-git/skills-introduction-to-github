<template>
  <!-- <div class="q-pa-md q-gutter-sm"> -->
    <q-dialog v-model="opened" transition-show="scale" transition-hide="scale">
      <q-card class="bg-teal-9 text-white text-center">
        <q-card-section v-if="subtit!=null"><div class="text-h5 text-cyan-1">{{ tit }}<br>{{ subtit }}</div> </q-card-section>
        <q-card-section v-else><div class="text-h5 text-cyan-1">{{ tit }} </div> </q-card-section>
        <q-card-section v-if="msg=='AllSlotsAreFilled'" class="q-pt-none text-h6">
          Click on <q-btn round glossy icon="add_circle" @click="addSelected" /> to add the selected players Or "CLOSE" and re-select
        </q-card-section>
        <q-card-section v-else class="q-pt-none text-h6">{{ msg }}</q-card-section>
        <q-card-actions align="right" class="bg-cyan-1 text-teal-9"><q-btn flat label="close" v-close-popup /> </q-card-actions>
      </q-card>
    </q-dialog>
  <!-- </div> -->
</template>

<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'
console.log('-ST-InfoDialog')
emitter.on('open-info-dialog', (x) => opened.value = x)
// const props = defineProps({
defineProps({
  tit: String,
  msg: String,
  subtit: String,
})
const emit = defineEmits(['add-selected-players'])
const opened = ref(false)
function addSelected() {
  console.log('-fn-addSelected')
  emit('add-selected-players')
  opened.value = false
}
</script>
<template>
  <div class="q-pa-md q-gutter-sm">
    <q-dialog v-model="opened" transition-show="flip-up" transition-hide="scale">
      <q-card square class="bg-teal-9 text-white" style="max-width:370px;border:3px solid cyan">
        <!-- <q-card-section><div class="text-h5 text-cyan-1 text-bold text-center">{{ tit }}</div> </q-card-section> -->
        <q-card-section><div class="inset-shadow-down text-h5 text-cyan-1 text-bold text-center" v-html="tit"></div></q-card-section>
        <q-card-section v-if="msg=='AllSlotsAreFilled'" class="q-pt-none text-h6">
          Click on <q-btn round glossy icon="add_circle" @click="addSelected" /> to add the selected players Or "CLOSE" and re-select
        </q-card-section>
        <q-card-section v-else-if="fmt==='html'" class="q-pt-none text-h6 justify-between">
          <div v-html="msg" /> 
        </q-card-section>
        <q-card-section v-else class="text-h6" style="margin:-16px 0 0 0;border:0px solid cyan">{{ msg }}</q-card-section>
        <q-card-actions align="right" class="bg-cyan-1 text-teal-9"><q-btn flat label="close" @click="closeIt" /> </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>
<script setup>
import emitter from 'tiny-emitter/instance'
import { ref } from 'vue'
console.log('-ST-InfoDisplay')
emitter.on('open-InfoDisplay', (tit, msg, html) => openIt(tit, msg, html))
const fmt = ref(true)
const tit = ref(null)
const msg = ref(null)
const opened = ref(false)
const emit = defineEmits(['add-selected-players'])
function closeIt () {
  opened.value = false
  emitter.emit('close-tips')
}
function openIt (titx, msgx, fmtx='html') {
  // console.log(`-fn-InfoDisplay.openIt(${titx}, ${msgx}`)
  tit.value = titx
  msg.value = msgx
  fmt.value = fmtx
  opened.value = true
}
function addSelected () {
  // console.log('-fn-addSelected')
  emit('add-selected-players')
}
</script>
<style>
a {   
  color: #ccc;   /* original colour state*/
  text-decoration: none;
}
a:active {
  color: #ABC;  
}
a:visited {
  color: #ABC;  
}
a[tabindex]:focus {
  color: #F66;
  outline: none;
}
</style>
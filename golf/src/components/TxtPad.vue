<template>
<q-dialog v-model="opened">
  <q-card class="bg-teal-9" style="width:100%;height:300px" rounded>
    <div class="text-h5 q-pt-md bg-teal-9 text-cyan-2 text-center">{{ title }}</div>
    <q-card-section>
      <q-input v-model='txt' type="textarea" autogrow dark counter input-style="line-height:1.2;min-height:140px" input-class="text-h6 bg-teal-10 q-px-xs" />
    </q-card-section>
    <q-card-actions align="between" class="bg-teal-9 q-pb-xs">
    <q-btn round glossy color="amber-8" icon="chevron_left" v-close-popup />
    <q-btn glossy color="primary" icon-right="save" label="save" size="15px" rounded @click="saveLnk()" />
    </q-card-actions>
  </q-card>
</q-dialog>
</template>
<script setup>
import emitter from 'tiny-emitter/instance'
import { ref } from 'vue'
const emit = defineEmits(['upd-lnk'])
var idx = null
const txt = ref(null)
const title = ref(null)
const opened = ref(false)

console.log('-ST-TxtPad')
emitter.on('open-TxtPad', (x, y, z) => openIt(x, y, z))
function openIt(i, lnk, tit) { 
  console.log(`-fn-openIt i=${i} lnk=${lnk} tit=${tit}`)
  idx = i
  txt.value = lnk
  title.value = tit
  opened.value = true
}
function saveLnk () {
  emit('upd-lnk', idx, txt.value)
  opened.value = false
}
</script>
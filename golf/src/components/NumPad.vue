<template>
<q-dialog v-model="opened">
  <div class="bg-cyan q-pa-xs">
    <q-card class="text-h6 bg-teal-10">
      <q-card-section class="text-center" :class="{'text-cyan-2':title.indexOf('Gap')>0, 'text-amber':title.indexOf('Gap')<0 }">{{ title }}</q-card-section>
      <q-card-actions v-if="title.indexOf('Gap')>0" align="between" class="row" style="margin:0 auto">
        <div v-for="i in numList" :key=i>
          <q-btn size="18.3px" color="cyan-1" round outline @click="emit('teetime-gap', i)">{{ i }}</q-btn>
        </div>
      </q-card-actions>
      <q-card-actions v-else align="evenly">
        <div v-for="i in numList" :key=i>
          <q-btn size="lg" color="amber" round outline @click="emit('set-teetimes', i)">{{ i }}</q-btn>
        </div>
      </q-card-actions>
    </q-card>
  </div>
</q-dialog>
</template>
<script setup>
import { ref } from 'vue'
// import emitter from 'tiny-emitter/instance'
const emit = defineEmits(['set-teetimes'])
const opened = ref(false)
const title = ref(null)
const numList = ref(null)

defineExpose({ openIt, closeIt })
console.info('-ST-NumPad')

function closeIt() { opened.value = false }
function openIt(tit, nlist) {
  title.value = tit
  numList.value = nlist
  opened.value = true
}
// emitter.on('open-NumPad', (x,y) => openIt(x,y))
// emitter.on('close-NumPad', () => opened.value = false)
</script>

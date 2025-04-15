<template>
<q-dialog v-model="opened" transition-show="rotate" transition-hide="rotate">
  <q-card style="min-height:100px">
    <q-card-actions class="bg-teal-10 text-h6" align="evenly">
      <div class="text-white">{{ lastStartAt }}</div>
      <q-radio keep-color v-model="AorB" val="" label="One Group on the Hole" color="amber-10" class="text-amber-10" />
      <q-radio keep-color v-model="AorB" val="A" label="A 组" color="cyan-4" class="text-cyan-4" />
      <q-radio keep-color v-model="AorB" val="B" label="B 组" color="yellow" class="text-yellow" />
    </q-card-actions>
    <q-card-section class="bg-teal-9 q-pa-sm">
      <table style="y-overflow:auto;margin:auto" class="bg-teal-7">
        <q-tr><td v-for="i in [1, 2, 3, 4, 5, 6, 7, 8, 9]"  :key=i.x>
          <q-btn size="17px" color="teal-10" round :label="i" @click="emit('set-start-at', i+AorB)" v-close-popup />
        </td></q-tr>
        <q-tr><td v-for="i in [10,11,12,13,14,15,16,17,18]" :key=i.x>
          <q-btn size="17px" color="teal-10" round :label="i" @click="emit('set-start-at', i+AorB)" v-close-popup />
        </td></q-tr>
      </table>
    </q-card-section>
  </q-card>
</q-dialog>
</template>
<script setup>
import { ref } from 'vue'
const opened = ref(false)
const AorB = ref('')
var lastStartAt = null
console.log('-ST-HolePad')
defineExpose({openIt})
const emit = defineEmits(['set-start-at'])

function openIt (lastST) {
  console.log(`-CK-fn openIt lastST=${lastST}`)
  lastStartAt = lastST
  opened.value = true
}
</script>

<template>
  <div class="q-pa-md q-gutter-sm">
    <q-dialog v-model="opened" persistent transition-show="scale" transition-hide="scale" class="bg-teal-10">
    <q-card style="min-width:400px;background:teal-10">
      <q-card-section>
        <div class="text-h6">Key in New {{ newCSP.model }}</div>
      </q-card-section>

      <q-card-section>
        <q-input input-style="font-size:18px" standout v-model="newCSP.name" autofocus @keyup.enter="opened=false" />
      </q-card-section>

      <q-card-actions align="right" class="text-primary">
        <q-btn flat label="Cancel" v-close-popup />
        <q-btn flat label="Add New CSP" @click="addNewCSP" />
      </q-card-actions>
    </q-card>
  </q-dialog>
  </div>
</template>
<script setup>
import { ref, reactive } from 'vue'
import emitter from 'tiny-emitter/instance'
// var model = ref(null)
var newCSP = reactive({})
const opened = ref(false)
console.log('-ST-AddNewCSPDialog')
emitter.on('open-AddNewCSPDialog', (x) => openIt(x))

const emit = defineEmits(['add-new-csp'])
function addNewCSP () {
  console.log('-fn-addNewCSP', newCSP)
  emit('add-new-csp', newCSP)
  opened.value = false
}
function openIt (cspModel) {
  console.log('-fn-open AddNewCSPDialog', cspModel)
  newCSP.model = cspModel
  opened.value = true
}
</script>

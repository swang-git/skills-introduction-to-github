<template>
<div class="q-pa-md q-gutter-sm" >
  <q-dialog v-model="opened" persistent transition-show="rotate" transition-hide="flip-up">
    <q-card class="bg-teal-10 text-white">
      <q-card-section class="row text-h6">
          <q-icon :name="iconName" color="green-8" left size="30px" /> <div style="margin:auto">{{ model }}</div> <q-btn flat icon="cancel" color="red" v-close-popup />
      </q-card-section>

      <q-separator />

      <div class="q-pa-xs">
        <q-input class="bg-teal-10" standout bottom-slots v-model="searchQuery" label="Search Courses by Name" dense dark>
          <template v-slot:prepend>
            <q-icon name="search" color="white" />
          </template>
          <template v-slot:append>
            <q-icon name="close" @click="searchQuery=null" class="cursor-pointer" />
          </template>
        </q-input>
      </div>

      <q-card-section style="max-height:40vh" class="scroll">
        <div class="scroll" v-for="o in options" :key=o.value >
          <q-radio v-model="cspId" :val="o.value" :label="o.label" @click="selectedOpt(o)" />
        </div>
      </q-card-section>

      <q-separator />

      <q-card-actions align="right">
        <q-btn flat label="close" color="red" v-close-popup />
      </q-card-actions>
    </q-card>
  </q-dialog>
</div>
</template>
<script setup>
import emitter from 'tiny-emitter/instance'
import { ref, computed } from 'vue'
import { libFunctions } from '../../src/composables/libFunctions'
const { $q } = libFunctions()

const emit = defineEmits(['selected-option', 'add-new-csp'])
var searchQuery = ref('')
const cspOptions = ref([])
const opened = ref(false)
var model = null
var iconName = null
var cspId = 0
console.log('-ST-SelOptionsWithSearch')

const options = computed(() =>{
  var filterKey = searchQuery.value && searchQuery.value.toLowerCase()
  var data = cspOptions.value
  if (filterKey) {
    var words = filterKey.split(' ')
    words.forEach(word => {
      data = data.filter(row => {
        return Object.keys(row).filter(key => { return !['id', 'catsId'].includes(key) }).some(key => {
          return String(row[key]).toLowerCase().indexOf(word) >= 0
        })
      })
    })
  }
  return data
})

function selectedOpt (opt) {
  console.log(`-fn-selectedOpt: model=${model} cspId=${cspId}`, opt)
  if (opt.value === -1) {
    $q.notify({ message: model + '(Add New)' })
  }
  emit('selected-option', cspId, model, opt)
  opened.value = false
}
emitter.on('open-SelOptionsWithSearch', (icon, mod, opts, idx) => openIt(icon, mod, opts, idx))
function openIt (icon, mod, opts, idx) {
  // console.log('-fn-selOptionsWithSearch.openIt', opts)
  cspOptions.value = opts
  model = mod
  iconName = icon
  cspId = idx
  opened.value = true
}
</script>

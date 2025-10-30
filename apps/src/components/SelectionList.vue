<template>
<div class="q-pa-md q-gutter-sm" >
  <q-dialog v-model="opened" persistent transition-show="rotate" transition-hide="flip-up">
    <q-card class="bg-teal-10 text-white">
      <q-card-section class="row text-h6">
          <q-icon :name="iconName" color="green-8" left size="30px" />
          <div style="margin:auto">{{ model }}</div>
          <!-- <q-btn round glossy icon="close" color="red" v-close-popup /> -->
      </q-card-section>

      <q-separator />

      <div class="q-pa-xs" v-if="cspOptions.length>8">
        <q-input class="bg-teal-10" standout bottom-slots v-model="searchQuery" label="Search Courses by Name" dense dark>
          <template v-slot:prepend>
            <q-icon name="search" color="white" />
          </template>
          <template v-slot:append>
            <q-icon name="close" @click="searchQuery=null" class="cursor-pointer" />
          </template>
        </q-input>
      </div>

      <q-card-section style="max-height:48vh" class="scroll">
        <div class="scroll text-h6" v-for="o in options" :key=o >
          <q-radio keep-color color="red" v-model="cspId" :val="o.sval" :label="o.slbl" @click="selectedOpt(o)" />
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

const emit = defineEmits(['selected-option'])
var searchQuery = ref('')
const cspOptions = ref([])
const opened = ref(false)
var model = null
var iconName = null
const cspId = ref(0)
console.log('-ST-SelOptionsWithSearch')
emitter.on('set-option', (model, xid) => setOption(model, xid))

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

function setOption (mod, xid) {
  console.log(`-fn-setOption: model=${mod} xid=${xid}`)
  cspId.value = xid
  model = mod
}
function selectedOpt (opt) {
  console.log(`-fn-selectedOpt: model=${model}`, opt)
  if (opt.sval === -1) {
    $q.notify({ message: model + '(Add New)' })
  }
  emit('selected-option', model, opt)
  opened.value = false
}
defineExpose({ openIt })
function openIt (icon, mod, opts, xid) {
  // console.log(`-fn-openI-optList`, opts)
  console.table(opts)
  cspOptions.value = opts
  model = mod
  iconName = icon
  opened.value = true
  cspId.value =xid
}
</script>

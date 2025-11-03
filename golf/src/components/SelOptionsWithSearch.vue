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
import { ref, computed } from 'vue'
// import { useQuasar } from 'quasar'
import emitter from 'tiny-emitter/instance'
import { libFunctions } from '../composables/libFunctions'
const { opened, $q } = libFunctions()
// const emit = defineEmits(['selected-option', 'selected-course'])
const emit = defineEmits(['selected-option'])

// const q = useQuasar()

const searchQuery = ref(null)
const cspId = ref(0)
const cspOptions = ref([])
const model = ref(null)
const iconName = ref(null)
// const opened = ref(false)
console.log('-ST-SelOptionsWithSearch')
emitter.on('open-SelOptionsWithSearch', (x,y,z) => openIt(x,y,z))
// emitter.on('open-SelOptionsWithSearchFromSelection', (x,y,z) => openIt(x,y,z))
defineExpose({ openIt })
const options = computed(() => {
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
  // console.log(`-CK-fn-selectedOpt for user selectedOpt model=${model.value}`, opt)
  if (opt.value === -1) {
    $q.notify({ message: model.value + '(Add New)' })
  }
  emitter.emit('selected-option', model.value, opt)
  emit('selected-option', model.value, opt)
  // if (model.value == 'Select Course') emitter.emit('selected-option', model.value, opt)
  // else emit('selected-option', model.value, opt)
  // let selectedModel = 'selected-' + model.value.replace(/ /g, '-')
  // if (model.value == 'Select Course') emit('selected-course', model.value, opt)
  opened.value = false
}
function openIt (iname, md, opts) {
  console.log('-CK-fn-openIt', md)
  searchQuery.value = null
  cspOptions.value = opts
  model.value = md
  iconName.value = iname
  cspId.value = 0
  opened.value = true
}
</script>

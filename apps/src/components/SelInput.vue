<template>
<div class="q-px-none q-pt-xs shadow-box shadow-9">
  <q-input rounded outlined class="text-h6" dark v-model="compSelectedName" :label="label" @click="showOptList()">
    <template v-if="iconRight" v-slot:append>
      <q-icon :name="icon" :color="iColor" size="lg" />
    </template>
    <template v-else v-slot:prepend>
      <q-icon :name="icon" :color="iColor" size="lg" />
    </template>
    <!-- <SelOptionsWithSearch @selected-option="selectedOption" /> -->
    <DatePad @set-date="setCCPostDate" />
    <div v-if="compSelectedName==='Fidel CCard'" class="text-white q-pt-sm"><q-btn round outline color="amber" icon="event" @click="openDatePad()" /></div>
    <div v-else class="text-white q-pt-sm"><q-btn round outline :color="iColor" :icon="icon" /></div>
  </q-input>
</div>
</template>
<script setup>
import { ref, computed } from 'vue'
import emitter from 'tiny-emitter/instance'
// import SelOptionsWithSearch from './SelOptionsWithSearch'
import DatePad from './DatePad'
const props = defineProps([
  'label',
  'icon',
  'iconRight',
  'iColor',
  'optList',
  'obj',
])
const emit = defineEmits([
  'add-new-csp',
  'get-gc-balance',
  'get-subc-opt',
  'get-paye-opt',
  'add-new-paye'
])
const compObj = computed({ 
  get: () => { return props.obj },
  // set(val) { props.obj}
})
const compSelectedName = computed(() => {
  if (props.label === 'Selected Paid with') return props.obj.paym
  else if (props.label === 'Select Paid with') return props.obj.paym
  else if (props.label === 'Selected Category') return props.obj.cats
  else if (props.label === 'Select Category') return props.obj.cats
  else if (props.label === 'Selected Subcategory') return props.obj.subc
  else if (props.label === 'Select Subcategory') return props.obj.subc
  else if (props.label === 'Selected Payee') return props.obj.paye
  else if (props.label === 'Select Payee') return props.obj.paye
  else if (props.label === 'Select Course') return props.obj.courseName
  else if (props.label === 'Select Mens Tee') return props.obj.mtee
  else if (props.label === 'Select Lady Tee') return props.obj.ltee
  else if (props.label === 'Notes') return props.obj.note
  return props.label
})

// newDatetime = dateTime
// console.log('-cr-SelInput', props.label, props.icon, props.optList, props.obj)
console.log(`-ST-SelInput label=${props.label}`)

//== function sections
function showOptList () {
  // console.log('-fn-showOptList', props.optList)
  emitter.emit('open-SelOptionsWithSearch', props.icon, props.label, props.optList)
}
function setCCPostDate (val) {
  console.log(`-fn-setCCPostDate inValue=${val}`) 
  compObj.value.post_date = val
}

// function selectedOption (label, opt) {
//   console.log(`-fn-selInput label=${label}`, opt)
//   if (opt.value === -1) emit('add-new-csp', label, opt)
//   // selectedName = opt.label
//   if (label === 'Select Paid with') {
//     compObj.value.paymId = opt.value
//     compObj.value.paym = opt.label
//     console.log('select Paid with', label, opt.value, opt.label, compObj.value)
//     if (opt.label === 'Mercer County Golf Gift Card' || opt.label === 'Somerset County Golf Gift Card') emit('get-gc-balance', opt)
//   } else if (label === 'Select Category') {
//     compObj.value.catsId = opt.value
//     compObj.value.cats = opt.label
//     console.log('-CK-get-subc-opt', label, opt.value)
//     emit('get-subc-opt', opt.value)
//   } else if (label === 'Select Subcategory') {
//     compObj.value.subcId = opt.value
//     compObj.value.subc = opt.label
//     // emitter.emit('get-paye-opt', opt.value)
//     emit('get-paye-opt', opt.value)
//   } else if (label === 'Select Payee') {
//     compObj.value.payeId = opt.value
//     compObj.value.paye = opt.label
//     emit('add-new-paye', opt.value)
//   } else if (label === 'Select Course') {
//     compObj.value.course_id = opt.value
//     compObj.value.courseName = opt.label
//   }
//   // row.value = props.obj
// }
// function showOptList () {
//   console.log('-fn-showOptList', props.optList)
//   emitter.emit('open-SelOptionsWithSearch', props.icon, props.label, props.optList)
// }
// function setCCPostDate (val) {
//   console.log(`-fn-setCCPostDate inValue=${val}`) 
//   compObj.value.post_date = val
// }
// function openDatePad () {
//   // console.log('-fn-openDatePad', props.optList)
//   const pdate = props.obj.post_date == null ? props.obj.date.substring(0, 10) : props.obj.post_date
//   emitter.emit('open-DatePad', pdate)
// }
</script>

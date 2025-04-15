<template>
<q-input rounded outlined class="q-pa-xs bg-teal-10 text-white text-h6" dark v-model="compSelectedName" :label="label" @click="showOptList()" readonly>
  <template v-slot:prepend>
    <q-icon :name="icon" :color="iColor" size="40px" />
  </template>
  <SelOptionsWithSearch ref="refSelOptionsWithSearch" />
  <!-- <SelOptionsWithSearch ref="refSelOptionsWithSearch" @selected-option="setSelected" /> -->
  <!-- <component :is="SelOptionsWithSearch" ref="refSelOptionsWithSearch" @selected-option="setSelected" /> -->
  <!-- <SelOptionsWithSearch @selected-option="selectedOption" /> -->
  <!-- <SelOptionsWithSearch @selected-option="setSelected" /> -->
  <!-- <SelOptionsWithSearch /> -->
</q-input>
</template>
<script setup>
import { ref, computed, onMounted } from 'vue'
import emitter from 'tiny-emitter/instance'
import SelOptionsWithSearch from './SelOptionsWithSearch'
const refSelOptionsWithSearch = ref(null)
const emit = defineEmits(['set-opt', 'get-TeeboxList', 'do-action'])
const props = defineProps([
  'iColor',
  'icon',
  'optList',
  'obj',
  'label'
])
// newDatetime.value = dateTime
console.log(`-ST-Selection', label=${props.label} icon=${props.icon}`, props.optList, props.obj)
onMounted(() => {
  console.log(`-CK-onMounted refSelOptionsWithSearch=${refSelOptionsWithSearch.value}`)
})
const compObj = computed(() => { return props.obj })
const compSelectedName = computed(() => {
  if (props.label === 'Select Game') return props.obj.game
  if (props.label === 'Select Course') return props.obj.courseName
  if (props.label === 'Select Mens Tee') return props.obj.mtee
  if (props.label === 'Select Lady Tee') return props.obj.ltee
  if (props.label === 'Tournament Start Time') return props.obj.start_at
  if (props.label === 'Match Start Time') return props.obj.start_at
  return props.label
})
emitter.on('selected-option', (label, opt) => setSelected(label, opt))
function setSelected (label, opt) {
  console.log(`-fn-setSelected label=${label}`, opt) 
  // selectedName.value = opt.label
  if (label === 'Select Game') {
    compObj.value.game_id = opt.value
    compObj.value.game = opt.label
  } else if (label === 'Select Course') {
    compObj.value.course_id = opt.value
    // compObj.value.courseName = opt.label
    compObj.value.courseName = opt.cname
    emit('get-TeeboxList', label, opt)
    return
  } else if (label === 'Select Mens Tee') {
    compObj.value.mtee_id = opt.value
    compObj.value.mtee = opt.label
    emit('do-action', label, opt)
    return
    // console.log('-ck-selectedOption', label, compObj.value.mtee)
  } else if (label === 'Select Lady Tee') {
    compObj.value.ltee_id = opt.value
    compObj.value.ltee = opt.label
    emit('set-opt', label, opt)
    return
  }
  // tmnt = compObj.value
}
function showOptList () {
  console.log('-fn-showOptList', props.optList)
  // emitter.emit('open-SelOptionsWithSearch',  props.icon, props.label, props.optList)
  // emitter.emit('open-SelOptionsWithSearchFromSelection',  props.icon, props.label, props.optList)
  refSelOptionsWithSearch.value.openIt(props.icon, props.label, props.optList)
}
</script>

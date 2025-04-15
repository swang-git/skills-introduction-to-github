<template>
<q-input rounded outlined class="q-pa-xs bg-teal-10 text-white text-h6" dark v-model="compSelectedName" :label="label" @click="showOptList()" readonly>
  <template v-slot:prepend>
    <q-icon :name="icon" :color="iColor" size="md" />
  </template>
  <SelectionList ref="refSelectionList" @selected-option="selectedOption" />
</q-input>
</template>
<script setup>
import { ref, computed, onMounted } from 'vue'
import SelectionList from './SelectionList'
const emit = defineEmits(['set-opt'])
const props = defineProps([
  'iColor',
  'icon',
  'optList',
  'obj',
  'label'
])
//== data sections
const selectedName = ref(null)
const refSelectionList = ref(null)

const compObj = computed(() => { return props.obj })
const compSelectedName = computed(() => {
  console.log('-STx-Selection', compObj.value)
  if (props.label === 'Select Game') return props.obj.game
  if (props.label === 'Select Course') return props.obj.courseName
  if (props.label === 'Select Mens Tee') return props.obj.mtee
  if (props.label === 'Select Lady Tee') return props.obj.ltee
  if (props.label === 'Tournament Start Time') return props.obj.start_at
  if (props.label === 'Match Start Time') return props.obj.start_at
  if (props.label === 'Select Golf Gift Card') return props.obj.slbl
  if (props.label === 'Select Card Number') return props.obj.slbl
  return props.label
})
  
console.log('-ST-Selection')
onMounted(() => {
  console.log(`-MT-Selection refSelectionList=${refSelectionList.value}`)
})
function selectedOption (label, opt) {
  // console.log(`-fn-selectedOption paymId=${paymId} label=${label} opt=${opt}`)
  // console.log(`-fn-selectedOption label=${label}`, opt)
  selectedName.value = opt.label
  if (label === 'Select Game') {
    compObj.value.game_id = opt.value
    compObj.value.game = opt.label
  } else if (label === 'Select Course') {
    compObj.value.course_id = opt.value
    compObj.value.courseName = opt.label
    emit('set-opt', label, opt)
  } else if (label === 'Select Mens Tee') {
    compObj.value.mtee_id = opt.value
    compObj.value.mtee = opt.label
    emit('set-opt', label, opt)
    // console.log('-STlectedOption', label, compObj.value.mtee)
  } else if (label === 'Select Lady Tee') {
    compObj.value.ltee_id = opt.value
    compObj.value.ltee = opt.label
  } else if (label === 'Select Golf Gift Card') {
    // compObj.value.paym_id = opt.value
    // compObj.value.name = opt.label
    // compObj.value.start_time = opt.start_time
    // compObj.value.value = opt.start_value
    // compObj.value.card_num = opt.card_num
    emit('set-opt', label, opt)
  } else if (label === 'Select Card Number') {
    // compObj.value.card_num = opt.label
    // compObj.value.start_time = opt.start_time
    // compObj.value.value = opt.start_value
    emit('set-opt', label, opt)
  }
}
function showOptList () {
  console.log('-fn-showOptList')
  refSelectionList.value.openIt(props.icon, props.label, props.optList, compObj.value.sval)
}
</script>

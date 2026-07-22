<template>
  <q-input rounded outlined class="text-h6 q-px-sm q-pt-xs" v-model="compInput" :label="label" dark>
    <template v-if="iconRight" v-slot:append>
      <q-icon :name="icon" :color="iColor" size="lg" />
    </template>
    <template v-else v-slot:prepend>
      <q-icon :name="icon" :color="iColor" size="lg" />
    </template>
    <div v-if="rightIcon" class="q-pt-sm">
      <!-- <q-btn round outline :color="iColor" :icon="label=='Notes' ? 'delete' : icon" @click="clearField()" /> -->
      <q-btn round glossy :color="iColor" icon="edit" @click="editSelected(props.label.toLowerCase())" />
      <!-- <q-btn round outline :color="iColor" icon="question_mark">
        <q-tooltip class="text-h5 bg-indigo text-cyan-3" v-if="label == 'Full Name'">required: max length 255</q-tooltip>
        <q-tooltip class="text-h5 bg-indigo text-cyan-3" v-else-if="label == 'username'">required and unique: max length 8</q-tooltip>
        <q-tooltip class="text-h5 bg-indigo text-cyan-3" v-else-if="label == 'usertype'">nullable: max length 16</q-tooltip>
        <q-tooltip class="text-h5 bg-indigo text-cyan-3" v-else-if="label == 'password'">required: upper/lower/special chars, numbers</q-tooltip>
        <q-tooltip class="text-h5 bg-indigo text-cyan-3" v-else-if="label == 'email'"> required and unique: bc@xxx.com</q-tooltip>
      </q-btn> -->
    </div>
  </q-input>
</template>
<script setup>
import { computed } from 'vue'
import emitter from 'tiny-emitter/instance'
// import InfoDisplay from './InfoDisplay.vue'
const props = defineProps([
  'label',
  'icon',
  'iconRight',
  'rightIcon',
  'iColor',
  'obj'
])

function showInputInfo(lbl) {
  console.log(`-fn-showInputInfo label=${lbl}`)
}
function getPropertyKey() {
  if (props.label === 'english word') return 'english'
  else if (props.label === 'chinese word') return 'chinese'
  else if (props.label === 'Gift Card Number') return 'gcardNum'
  else if (props.label === 'Document Link') return 'link'
  else if (props.label === 'Type') return 'tag'
  else if (props.label === 'Message') return 'message'
  else if (props.label === 'Note') return 'note'
  else if (props.label === 'Food') return 'food'
  else if (props.label === 'Drink') return 'drink'
  else if (props.label === 'Fruit') return 'fruit'
  else if (props.label === 'Glucose Check Notes') return 'note'
  else if (props.label === 'Dictionary Notes') return 'note'
  else if (props.label === 'Tag') return 'tag'
  else if (props.label === 'Full Name') return 'name'
  else if (props.label === 'username') return 'username'
  else if (props.label === 'usertype') return 'usertype'
  else if (props.label === 'password') return 'password'
  else if (props.label === 'email') return 'email'
  else if (props.label === 'Blood Pressure') return 'bloodPressure'
  else if (props.label === 'Won or Lost') return 'quan'
  else if (props.label === 'Check Type') return 'type'
  else if (props.label === 'Exercise') return 'exercise'
  else if (props.label === 'Breakfast') return 'breakfast'
  else if (props.label === 'Lunch') return 'lunch'
  else if (props.label === 'Dinner') return 'dinner'
  // else if (props.label === 'Notes') return 'note'
  return props.label
}
const compObj = computed(() => {
  return props.obj
})
const compInput = computed({
  get: () => {
    return Reflect.get(props.obj, getPropertyKey())
  },
  set: val => {
    // console.log(`-CK- compInput set ${props.label} = ${val}`)
    if (props.label === 'Gift Card Number') {
      compObj.value.gcardNum = val
      compObj.value.gcardId = -1
    }
    Reflect.set(compObj.value, getPropertyKey(), val)
  }
})
// console.log(`-ST-TxtInput label=${props.label}`)

// function clearField() { Reflect.set(compObj.value, getPropertyKey(), null) }
function editSelected(label) {
  console.log(`-fn-editSelected selectedValue label=${label} props.label=${props.label} row-value[${label}]=${compObj.value[label]}`)
  // console.log(`-fn-editSelected selectedValue=${label} ${props.label} ${compObj.value.label}`)
  emitter.emit('open-TxtPad', props.label, compObj.value[label], 'Edit ' + props.label)
  // emitter.emit( 'open-TxtPad', props.label, 'testing Notes field ABCXX', 'Edit ' + props.label)
}
// function clearField () {
//   if (props.label === 'Tag') compObj.value.tag = null
//   else if (props.label === 'Food') compObj.value.food = null
//   else if (props.label === 'Notes') compObj.value.note = null
//   // console.log('-fn-clearField', props.label, props.obj.tag)
// }
</script>

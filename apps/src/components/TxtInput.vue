<template>
<q-input rounded outlined class="text-h6 q-px-sm q-pt-xs" v-model="compInput" :label="label" dark>
  <template v-if="iconRight" v-slot:append>
    <q-icon :name="icon" :color="iColor" size="lg" />
  </template>
  <template v-else v-slot:prepend>
    <q-icon :name="icon" :color="iColor" size="lg" />
  </template>
  <div v-if="rightIcon" class="q-pt-sm">
    <q-btn round outline :color="iColor" :icon="label=='Notes' ? 'delete' : icon" @click="clearField()" />
  </div>
</q-input>
</template>
<script setup>
import { computed } from 'vue'
const props = defineProps([
  'label',
  'icon',
  'iconRight',
  'rightIcon',
  'iColor',
  'obj'
])
function getPropertyKey () {
  if (props.label === 'english word') return 'english'
  else if (props.label === 'chinese word') return 'chinese'
  else if (props.label === 'Gift Card Number') return 'gcardNum'
  else if (props.label === 'Document Link') return 'link'
  else if (props.label === 'Type') return 'tag'
  else if (props.label === 'Message') return 'message'
  else if (props.label === 'Notes') return 'note'
  else if (props.label === 'Food') return 'food'
  else if (props.label === 'Drink') return 'drink'
  else if (props.label === 'Fruit') return 'fruit'
  else if (props.label === 'Glucose Check Notes') return 'note'
  else if (props.label === 'Dictionary Notes') return 'note'
  else if (props.label === 'Notes') return 'note'
  else if (props.label === 'Tag') return 'tag'
  else if (props.label === 'Full Name') return 'name'
  else if (props.label === 'username') return 'username'
  else if (props.label === 'usertype') return 'usertype'
  else if (props.label === 'password') return 'password'
  else if (props.label === 'email') return 'email'
  else if (props.label === 'Blood Pressure') return 'bloodPressure'
  else if (props.label === 'Won or Lost') return 'quan'
  return props.label
}
const compObj = computed(() => { return props.obj })
const compInput = computed({
  get: () => {
    return Reflect.get(props.obj, getPropertyKey())
    // if (props.label === 'english word') return props.obj.english
    // else if (props.label === 'chinese word') return props.obj.chinese
    // else if (props.label === 'Gift Card Number') return props.obj.gcardNum
    // else if (props.label === 'Document Link') return props.obj.link
    // else if (props.label === 'Type') return props.obj.tag
    // else if (props.label === 'Message') return props.obj.message
    // else if (props.label === 'Notes') return props.obj.note
    // else if (props.label === 'Food') return props.obj.food
    // else if (props.label === 'Drink') return props.obj.drink
    // else if (props.label === 'Fruit') return props.obj.fruit
    // else if (props.label === 'Glucose Check Notes') return props.obj.note
    // else if (props.label === 'Dictionary Notes') return props.obj.note
    // else if (props.label === 'Notes') return props.obj.note
    // else if (props.label === 'Tag') return props.obj.tag
    // else if (props.label === 'Full Name') return props.obj.name
    // else if (props.label === 'username') return props.obj.username
    // else if (props.label === 'usertype') return props.obj.usertype
    // else if (props.label === 'password') return props.obj.password
    // else if (props.label === 'email') return props.obj.email
    // else if (props.label === 'Blood Pressure') return props.obj.bloodPressure
    // else if (props.label === 'Won or Lost') return props.obj.quan
    // return props.label
  },
  set: val => {
    // console.log(`-CK- compInput set ${props.label} = ${val}`)
    if (props.label === 'Gift Card Number') {
      compObj.value.gcardNum = val
      compObj.value.gcardId = -1
    } 
    Reflect.set(compObj.value, getPropertyKey(), val)
    // else if (props.label === 'Document Link') compObj.value.link = val
    // else if (props.label === 'Type') compObj.value.tag = val
    // else if (props.label === 'Message') compObj.value.message = val
    // else if (props.label === 'Notes') compObj.value.note = val
    // else if (props.label === 'english word') compObj.value.english = val
    // else if (props.label === 'chinese word') compObj.value.chinese = val
    // else if (props.label === 'Food') compObj.value.food = val
    // else if (props.label === 'Drink') compObj.value.drink = val
    // else if (props.label === 'Fruit') compObj.value.fruit = val
    // else if (props.label === 'Glucose Check Notes') compObj.value.note = val
    // else if (props.label === 'Dictionary Notes') compObj.value.note = val
    // else if (props.label === 'Tag') compObj.value.tag = val
    // else if (props.label === 'Full Name') compObj.value.name = val
    // else if (props.label === 'username') compObj.value.username = val
    // else if (props.label === 'usertype') compObj.value.usertype = val
    // else if (props.label === 'password') compObj.value.password = val
    // else if (props.label === 'email') compObj.value.email = val
    // else if (props.label === 'Blood Pressure') compObj.value.bloodPressure = val
    // else if (props.label === 'Won or Lost') compObj.value.quan = parseFloat(val)
  }
})
// console.log(`-ST-TxtInput label=${props.label}`)

function clearField () {
  Reflect.set(compObj.value, getPropertyKey(), val)
}
// function clearField () {
//   if (props.label === 'Tag') compObj.value.tag = null
//   else if (props.label === 'Food') compObj.value.food = null
//   else if (props.label === 'Notes') compObj.value.note = null
//   // console.log('-fn-clearField', props.label, props.obj.tag)
// }
</script>

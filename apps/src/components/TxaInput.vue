<template>
  <q-input rounded outlined class="text-h6 q-px-sm q-pt-xs" input-style="line-height:1.6;height:100px" v-model="compInput" :label="label" type="textarea" dark>
    <template v-slot:prepend>
      <q-icon :name="icon" :color="iColor" />
    </template>
  </q-input>
</template>
<script setup>
import { computed } from 'vue'
const props = defineProps([
  'label',
  'icon',
  'iColor',
  'obj'
])
console.info('-ST-TxaInput')
const compObj = computed(() => { return props.obj })
const compInput = computed({
  get: () => { return Reflect.get(props.obj, getPropertyKey()) },
  set: (val) => { Reflect.set(compObj.value, getPropertyKey(), val) }
})
function getPropertyKey () {
  if (props.label === 'english word') return 'english'
  else if (props.label === 'chinese word') return 'chinese'
  else if (props.label === 'Gift Card Number') return 'gcardNum'
  else if (props.label === 'Document Link') return 'link'
  else if (props.label === 'Type') return 'tag'
  else if (props.label === 'Message') return 'message'
  else if (props.label === 'Food') return 'food'
  else if (props.label === 'Drink') return 'drink'
  else if (props.label === 'Fruit') return 'fruit'
  else if (props.label === 'Glucose Check Notes') return 'note'
  else if (props.label === 'Dictionary Notes') return 'note'
  else if (props.label === 'Notes') return 'note'
  else if (props.label === '昨 日 餐 饮') return 'note'
  else if (props.label === 'Details') return 'details'
  return props.label
}
</script>
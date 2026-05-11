<template>
<div>
  <q-btn round glossy :size="size" :class="clas" :color="colr">
    <q-icon :name="icon" :color="iclr" />
    <q-tooltip class="text-h5 bg-accent">{{ ttip }}</q-tooltip>
  </q-btn>
</div>
</template>
<script setup>
const props = defineProps({
  size: { type: String },
  icon: { type: String },
  iclr: { type: String },
  colr: { type: String },
  ttip: { type: String },
  clas: { type: String },
})
console.log(`-ST-RoundButton`)
// console.log(`-ST-RoundButton ${props.size} ${props.icon} ${props.colr} ${props.iclr} ${props.ttip} ${props.clas}`)
</script>
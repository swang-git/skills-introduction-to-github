<template>
  <q-tooltip :class="clx">{{ txt }}</q-tooltip>
</template>
<script setup>
const props = defineProps({
  txt: { Type: String },
  clx: { Type: String, default() { return 'bg-cyan-9 text-white text-h6' } }
})
// console.log(`-ST-Tooltip ${props.txt} ${props.clx}`)
</script>
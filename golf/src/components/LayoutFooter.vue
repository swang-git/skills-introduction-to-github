<template>
<q-card>
  <q-card-actions class="bg-teal-10" align="between">
    <div><slot name="lbtn" /></div>
    <q-btn v-if="/NOTE_LINK/.test(tit)" icon="message"  round @click="msg" color="blue-9" glossy  />
    <q-btn v-if="/NOTE_LINK/.test(tit)" icon="link"     round @click="lnk" color="cyan-9" glossy  />
    <div class="text-center"><slot name="ctit" /></div>
    <div><slot name="rbtn"/></div>
  </q-card-actions>
</q-card>
</template>
<script setup>
defineProps({
  tit: { type: String }
})
const emit = defineEmits(['do-action'])
function lnk () { emit('do-action', 'lnk') }
function msg () { emit('do-action', 'msg') }
// function doAction (label) {
//   console.log(`-fn-doAction label=${label}`);
//   emit('do-action', label)
// }
</script>

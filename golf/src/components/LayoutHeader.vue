<template>
  <q-header class="bg-teal-10 inset-shadow-down" style="position:fixed">
    <div class="text-h6 q-my-sm text-cyan-2" align="center">{{ tit }}</div>
  </q-header>

  <q-card>
    <q-card-actions class="bg-teal-10" align="between">
      <q-btn v-if="tit==null" rounded glossy label="close" color="amber-9" dense icon="cancel" size="16px" v-close-popup />
      <div><slot name="lbtn" /></div>
      <q-btn v-if="/NOTE_LINK/.test(tit)" icon="message"  round @click="msg" color="blue-9" glossy  />
      <q-btn v-if="/NOTE_LINK/.test(tit)" icon="link"     round @click="lnk" color="cyan-9" glossy  />
      <div class="text-center"><slot name="ctit" /></div>
      <div><slot name="rbtn"/></div>
      <q-btn v-if="tit==null" rounded glossy :label="act" color="indigo-9" class="q-mx-xs" size="16px" dense icon-right="add_circle" @click="doAction" />
    </q-card-actions>
  </q-card>
</template>
<script setup>
defineProps({
  tit: { type: String },
  act: { type: String }
})
const emit = defineEmits(['do-action'])
function lnk () { emit('do-action', 'lnk') }
function msg () { emit('do-action', 'msg') }
function doAction () {
  console.log(`-fn-doAction`);
  emit('do-action')
}
</script>

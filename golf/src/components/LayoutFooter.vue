<template>
<q-card>
  <q-card-actions class="bg-teal-10" align="between">
    <q-btn v-if="tit==null" rounded glossy label="cancel" color="amber-9" dense icon="cancel" size="16px" v-close-popup />
    <div><slot name="lbtn" /></div>
    <q-btn v-if="/NOTE_LINK/.test(tit)" icon="message"  round @click="msg" color="blue-9" glossy  />
    <q-btn v-if="/NOTE_LINK/.test(tit)" icon="link"     round @click="lnk" color="cyan-9" glossy  />
    <div class="text-center"><slot name="ctit" /></div>
    <div><slot name="rbtn"/></div>
    <q-btn v-if="tit==null" rounded glossy :label="act" color="indigo-9" class="q-mx-xs" size="16px" dense icon-right="update" @click="doAction" />
    <!-- <q-btn v-else-if="tit==null && act=='add'" rounded glossy :label="act" color="indigo-9" class="q-mx-xs" size="16px" dense icon-right="create" @click="doAction" /> -->
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

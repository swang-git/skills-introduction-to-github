<template>
<q-footer elevated>
  <div v-if="isDesk && /add|upd/.test(act)" class="q-pa-sm bg-teal-10 flex justify-between">
    <q-btn icon="cancel" v-close-popup color="amber-9" text-color="cyan-1" rounded glossy label="Cancel" />
    <q-btn v-if="/NOTE_LINK_POST|Gas Mileage/.test(tit)" icon="schedule" round @click="psd" color="green-10" glossy size="16px" />
    <q-btn v-if="/NOTE_LINK/.test(tit)" icon="message"   round @click="msg" color="blue-10" glossy size="16px"  />
    <q-btn v-if="/NOTE_LINK/.test(tit)" icon="link"      round @click="lnk" color="cyan-10" glossy size="16px"  />
    <q-btn v-else-if="/Miles/.test(tit)" outline rounded :label="tit" no-caps no-wrap readonly />
    <q-btn v-else-if="tit==='NO_TIT'" flat />
    <q-btn v-if="act=='add'" icon-right="add_circle" @click="add" color="green-9" rounded glossy label="Create" />
    <q-btn v-if="act=='upd'" icon-right="update"     @click="upd" color="indigo-9"  rounded glossy label="update" />
  </div>
  <div v-else-if="isDesk" class="q-pa-sm bg-teal-10 flex justify-between">
    <q-btn icon="update" @click="upd" color="purple-6" rounded glossy label="Update" />
    <q-btn v-if="/NOTE_LINK_POST|Gas Mileage/.test(tit)" icon="schedule" round @click="psd" color="green-10" glossy size="16px" />
    <q-btn v-if="/NOTE_LINK/.test(tit)" icon="message"   round @click="msg" color="blue-10" glossy size="16px"  />
    <q-btn v-if="/NOTE_LINK/.test(tit)" icon="link"      round @click="lnk" color="cyan-10" glossy size="16px"  />
    <q-btn v-else-if="/Miles/.test(tit)" outline rounded :label="tit" no-caps no-wrap readonly />
    <q-btn v-else-if="tit==='NO_TIT'" flat />
    <q-btn icon-right="add_circle" @click="add" color="primary" rounded glossy label="Create" />
  </div>
  <div v-else class="q-pa-sm bg-teal-10 flex justify-between">
    <q-btn v-if="act!='show'" icon="chevron_left" v-close-popup color="amber-10" text-color="cyan-2" round glossy size="16px" />
    <q-btn v-if="/NOTE_LINK_POST|Miles/.test(tit)" icon="schedule" round @click="psd" color="lime-9" glossy size="16px" />
    <q-btn v-if="/NOTE_LINK/.test(tit)" icon="message"   round @click="msg" color="blue-10" glossy size="16px" />
    <q-btn v-if="/NOTE_LINK/.test(tit)" icon="link"      round @click="lnk" color="cyan-10" glossy size="16px" />
    <q-btn v-else-if="/Miles/.test(tit)" outline rounded :label="tit" no-caps no-wrap readonly />
    <q-btn v-else-if="tit==='NO_TIT'" flat />
    <q-btn v-if="tit=='TIT_GLUCOSE'" icon="info" color="green-10" text-color="cyan-2" @click="info" round glossy size="16px" />
    <q-btn v-if="act=='add'"       icon="add_circle" @click="add" color="green-10"  round glossy size="16px" />
    <q-btn v-else-if="act=='upd'"  icon="update"     @click="upd" color="indigo-10" round glossy size="16px" />
    <q-btn v-else v-close-popup color="cyan-1" rounded outline size="14px" class="q-ml-md" label="data show only and take no actions"/>
  </div>
</q-footer>
</template>
<script setup>
import { libFunctions } from '../../src/composables/libFunctions'
const { isDesk } = libFunctions()
const emit = defineEmits(['do-action'])
const props = defineProps({
  tit: { type: String },
  act: { type: String },
  label: { type: String },
  icon:  { type: String },
})

console.log(`-ST-LayoutFooter title=${props.tit} action=${props.act} tit=${props.tit}`) 

function add () { emit('do-action', 'add') }
function upd () { emit('do-action', 'upd') }
function oth () { emit('do-action', 'oth') }
function lnk () { emit('do-action', 'lnk') }
function msg () { emit('do-action', 'msg') }
function psd () { emit('do-action', 'psd') }
function info () { emit('do-action', 'info') }
// psd() { console.log('-ck-showPostDate'); this.$emit('do-action', 'psd') },
  
</script>

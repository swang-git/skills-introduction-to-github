<template>
<q-header class="bg-teal-10 shadow-box shadow-10" elevated>
  <div class="q-ma-sm q-px-xs flex justify-between">
    <q-btn round glossy icon='chevron_left' v-close-popup color="orange-8" />
    <q-toolbar-title class="text-center">{{ tit }}</q-toolbar-title>
    <q-btn v-if="rbtn!=null" round glossy color="green-9" :icon="rbtn" @click="showSGList" />
    <q-btn v-if="updtm" round glossy color="cyan-9" icon="schedule" @click="showUpdTM" class="showUpdTM" />
    <q-btn round glossy color="red" icon="delete" @click="del" />
  </div>
</q-header>
</template>
<script setup>
// import emitter from 'tiny-emitter/instance'
const emit = defineEmits(['do-action', 'show-purchased-list', 'show-updtm'])
const props = defineProps({
  tit: { type: String },
  rbtn: { type: String },
  updtm: { type: Boolean }
})
function showUpdTM () {
  console.log('-CK-emit show-updtm')
  emit('show-updtm')
}
function del () {
  // console.log('-CK-emit do-action del')
  emit('do-action', 'del')
}
function showSGList () {
  if (props.rbtn === 'shopping_cart') emit('do-action', 'plt')
  else if (props.rbtn === 'golf_course') emit('do-action', 'gsc')
}
// function plt () { emit('do-action', 'plt') }
// function gsc () { emit('do-action', 'gsc') }
</script>

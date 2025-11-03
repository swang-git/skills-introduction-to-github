<template>
  <q-header class="bg-teal-10 inset-shadow-down" style="position:fixed">
    <q-toolbar>
      <slot name="lbtn" />
      <slot name="ctit" />
      <slot name="rbtn" />
    </q-toolbar>
  </q-header>
</template>
<script setup>
// const emit = defineEmits(['do-action'])
// const props = defineProps({
//   tit: { type: String },
//   ricon: { type: String },
// })
// function del () { emit('do-action', 'del') }
// function add () { emit('do-action', 'add') }

</script>

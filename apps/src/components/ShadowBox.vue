<template>
  <div class="inset-shadow-down flex flex-center inline shadow-box-hole">{{ val }}</div>
</template>
<script setup>
const props = defineProps({
  val: { type: String },
  // bgColor: { type: String },
  // shape: { type: String },
})
</script>
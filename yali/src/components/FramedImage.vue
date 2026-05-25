<template>
  <!-- Frame wrapper -->
  <div class="image-frame">
    <!-- Quasar image: KEEPS ASPECT RATIO, NO VIEWPORT FIT -->
    <q-img
      :src="src"
      :alt="alt"
      fit="contain"
      class="natural-size"
    />
  </div>
</template>

<script setup>
const props = defineProps({
  src: { type: String, required: true },
  alt: { type: String, default: 'Image' }
})
</script>

<style scoped>
/* The frame around the image */
.image-frame {
  /* Frame styling */
  background: white;
  border: 4px solid #2d3748;
  padding: 12px;
  border-radius: 6px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);

  /* Wrap tightly to the image (no forced size) */
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

/* Image: natural aspect ratio, NO viewport stretching */
.natural-size {
  max-width: 100%;
  height: auto !important;
  object-fit: contain;
}
</style>
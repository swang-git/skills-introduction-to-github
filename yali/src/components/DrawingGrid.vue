<template>
  <div class="drawing-container">
    <div class="row items-center q-pa-sm text-grey-7">
      <span>Showing {{ drawings.length }} of {{ total }}</span>
      <q-space />
      <q-btn
        v-if="hasMore"
        :loading="loading"
        label="Load More"
        color="primary"
        @click="loadMore"
      />
    </div>
    
    <div class="row q-col-gutter-sm q-pa-sm">
      <div
        v-for="drawing in drawings"
        :key="drawing.id"
        :class="columnClass"
      >
        <q-card
          class="drawing-card cursor-pointer bg-red-9"
          @click="showFullImage(drawing)"
        >
          <!-- Use direct URL from backend -->
          <q-img
            :src="drawing.thumbnail_url"
            :alt="drawing.name"
            class="drawing-thumb"
            loading="lazy"
          >
            <template v-slot:loading>
              <q-skeleton class="full-width full-height" />
            </template>
          </q-img>
          
          <q-card-actions align="between" class="bg-black-5 text-white">
            <q-btn flat dense icon="zoom_in" size="sm" />
            <span class="text-caption ellipsis">{{ drawing.name }}</span>
          </q-card-actions>
        </q-card>
      </div>
    </div>
    
    <!-- Full image dialog -->
    <q-dialog v-model="dialogOpen" maximized>
      <q-card class="bg-black">
        <q-bar class="bg-black text-white">
          <span>{{ selectedDrawing?.name }}</span>
          <q-space />
          <q-btn dense flat icon="close" v-close-popup />
        </q-bar>
        <q-card-section class="flex flex-center">
          <q-img
            :src="selectedDrawing?.full_url"
            style="max-height: 90vh; max-width: 90vw"
          />
        </q-card-section>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useDrawings } from 'composables/useDrawings'

const props = defineProps({
  itemsPerRow: { type: Number, default: 6 }
})

const { drawings, total, loading, hasMore, loadDrawings, loadMore } = useDrawings()

const dialogOpen = ref(false)
const selectedDrawing = ref(null)

const columnClass = computed(() => {
  const cols = Math.floor(12 / props.itemsPerRow)
  return `col-${cols}`
})

const showFullImage = (drawing) => {
  selectedDrawing.value = drawing
  dialogOpen.value = true
}

onMounted(() => loadDrawings(1, false))
</script>

<style scoped>
.drawing-card {
  transition: transform 0.15s;
}
.drawing-card:hover {
  transform: scale(1.03);
}
.drawing-thumb {
  height: 100px;
  object-fit: cover;
}
</style>

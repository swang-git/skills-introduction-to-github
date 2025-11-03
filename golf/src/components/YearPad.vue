<template>
<q-dialog v-model="opened" :full-width="isIM">
  <div class="fixed-center" style="border:solid cyan 1px">
    <q-card class="bg-cyan-9">
      <q-card-actions>
        <q-btn glossy round color="amber-10" icon="chevron_left" @click="setPrevYears" />
        <div v-for="year in compYears" :key="year">
          <!-- <q-btn class="text-white" :style="{'font-size':isDesk ? '20px' : '16.9px'}" flat :label="year" @click="setYear(year)" /> -->
          <q-btn class="text-white" :style="{'font-size':isIM ? '16.9px' : '20px'}" flat :label="year" @click="setYear(year)" />
        </div>
        <q-btn glossy round color="amber-10" icon="chevron_right" @click="setNextYears" />
      </q-card-actions>
    </q-card>
  </div>
</q-dialog>
</template>
<script setup>
import { ref, computed } from 'vue'
import { libFunctions } from 'src/composables/libFunctions'
const { isIM, opened } = libFunctions()
const year = ref(2021)
// const opened = ref(false)

console.log('-ST-YearPad')
const compYears = computed(() => {
  const yr = year.value
  return  [yr - 2, yr - 1, yr, yr + 1, yr + 2]
})
function setPrevYears () { year.value -= 1 }
function setNextYears () { year.value += 1 }
function setYear (yr) {
  emit('upd-year', yr)
  opened.value = false
}
const emit = defineEmits(['upd-year'])
defineExpose({ openIt })
function openIt (yr) {
  // console.log(`-CK-fn-openIt year=${yr}`)
  year.value = yr
  opened.value = true
}
</script>

<template>
  <q-dialog v-model="opened">
    <div class="bg-cyan q-pa-xs">
      <q-card class="text-h5 bg-teal-10">
        <q-card-section class="text-center text-cyan-2"> {{ title }} </q-card-section>
        <q-card-actions v-for="(tm, i) in teeTimes" :key="tm">
          <TimePicker :idx="i" :time="tm" txsz="text-h5" label="Tee Time" @upd-tm="updTeeTime" />
        </q-card-actions>
        <q-card-actions align="between" class="text-center text-cyan-2">
          <q-btn round flat :label="compTeeTimeGap" @click="openNumGapPad" />
          <q-btn rounded glossy color="teal-9" label="Save Tee Times" class="q-px-md" @click="saveTeeTimes" />
        </q-card-actions>
      </q-card>
    </div>
  </q-dialog>
  <NumGapPad ref="refNumGapPad" @time-gap="setTeeTimeGap" />
</template>
<script setup>
import { ref, computed, onMounted } from 'vue'
import { dayFunctions } from '../composables/dayFunctions'
import TimePicker from './TimePicker.vue'
import NumGapPad from './NumGapPad.vue'
const { yyyymmddHHMM } = dayFunctions()
const emit = defineEmits(['save-teetimes'])
const opened = ref(false)
const title = ref(null)
const startAt = ref(null)
const numTeeTimes = ref(null)
const teeTimes = ref(Array(numTeeTimes.value))
const teeTimeGap = ref(10)
const refNumGapPad = ref(null)
onMounted(() => refNumGapPad)
console.info('-ST-TimeTable', refNumGapPad.value)

defineExpose({ openIt, closeIt })
const compTeeTimeGap = computed(() => { return teeTimeGap.value })
function openNumGapPad() {
  refNumGapPad.value.openIt()
}
function closeIt() {
  opened.value = false
}
function openIt(tit, nTeeTimes, startTeeTime) {
  title.value = tit
  numTeeTimes.value = nTeeTimes
  startAt.value = startTeeTime
  for (let i=0; i<nTeeTimes; i++) teeTimes.value[i] = addMinutes(startTeeTime, i, compTeeTimeGap.value)
  opened.value = true
}
function setTeeTimeGap(tmgap) {
  console.log(`-fn-setTeeTimeGap teeTimeGap=${tmgap}`)
  teeTimeGap.value = tmgap
  for (let i=0; i<numTeeTimes.value; i++) teeTimes.value[i] = addMinutes(startAt.value, i, compTeeTimeGap.value)
}
function updTeeTime(idx, tm) {
  console.log(`-fn-updTeeTime newTeeTime=${tm} idx=${idx}`)
  teeTimes.value[idx] = tm
}
function saveTeeTimes() {
  // teeTimes.value[0] = startAt.value
  console.log(`-fn-saveTeeTimes`, teeTimes.value)
  emit('save-teetimes', teeTimes.value)
}
function addMinutes(dateString, i, tmGap) {
  console.log(`-fn-addMinutes dateString=${dateString} i=${i} compTeeTimeGap=${tmGap}`)
  const date = new Date(dateString)
  let newTeeTime = yyyymmddHHMM(new Date(date.getTime() + i * tmGap * 60000))
  teeTimes.value[i] = newTeeTime
  console.log(`-fn-addMinutes dateString=${dateString} i=${i} newTeeTime=${newTeeTime}`)
  return newTeeTime
}
</script>

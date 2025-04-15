<template>
<q-input rounded outlined v-model="newDate" :label="label" class="q-px-xs q-pt-xs" :class="txsz" dark>
  <template v-slot:prepend>
    <q-icon name="schedule" size="lg" class="text-amber-2 cursor-pointer text-h5">
      <q-popup-proxy v-model="showDate" transition-show="flip-up" transition-hide="flip-down">
        <q-date v-model="newDate" mask="YYYY-MM-DD" dark today-btn :landscape="isDesk ? true : false">
          <div class="row items-center justify-end">
            <q-btn label="OK" color="green" glossy dark round @click="updDate()" v-close-popup />
          </div>
        </q-date>
      </q-popup-proxy>
    </q-icon>
  </template>
  <template v-slot:append>
    <q-btn class="q-pb-sm" round outline size="12px" :icon="date==null ? null : date.chwk1()" color="cyan-2" @click="showDate=true">
    </q-btn>
  </template>
</q-input>
</template>
<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'
import { libFunctions } from '../composables/libFunctions'
const { isDesk } = libFunctions()
import { dayFunctions } from '../composables/dayFunctions'
const { chwk1 } = dayFunctions()
const props = defineProps(['date', 'label', 'txsz'])
const showDate = ref(false)
const newDate = ref(null)

console.log(`-ST-DatePicker props.date=${props.date}`)
emitter.on('new-date', (ndate) => setNewDate(ndate))
setNewDate(null)
    
const emit = defineEmits(['upd-date'])
function setNewDate (ndate) {
  newDate.value = ndate == null ? props.date : ndate
}
function updDate () {
  // console.log('-fn-updDate from DatePicker', newDate.value)
  showDate.value = false
  emit('upd-date', newDate.value)
}
</script>

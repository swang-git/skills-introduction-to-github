<template>
<q-dialog v-model="opened" transition-show="rotate" transition-hide="scale" :maximized="isIM">
  <q-card class="bg-teal-10 text-white" style="width: 500px">
    <q-card-section>
      <div class="bg-teal-8 text-h5 text-bold text-center">
        <div>{{ tit }}<q-btn flat class="text-h5 text-bold" @click="setYear" :label="year" /></div>
      </div>
    </q-card-section>

    <q-card-section class="q-pt-none">
      <div v-for="h in compHolidays" :key=h.i class="text-h6">
        <span class="text-bold" style="font-family:youyuan">{{ h[0].toDateString() }}, </span>
        <span>{{ h[1] }}
          <q-tooltip content-style="font-size:20px;color:yellow;background:blue">
            {{ h[2] }}
          </q-tooltip></span><br /><hr />
      </div>
    </q-card-section>

    <q-card-actions align="right" class="bg-white text-teal">
      <q-btn flat label="close" v-close-popup />
    </q-card-actions>
  </q-card>
</q-dialog>
<HolidayYearPad @upd-year="updYear" />
</template>
<script setup>
import emitter from 'tiny-emitter/instance'
import { ref, computed } from 'vue' 
import { libFunctions } from '../src/composables/libFunctions'
const { isDesk, isIM } = libFunctions()
import { Calendar } from './Calendar'
import HolidayYearPad from './HolidayYearPad'

const tit = ref('Holidays of')
const holidays = ref({})
const year = ref((new Date()).getFullYear())
const opened = ref(false)


holidays.value = new Calendar(new Date())
tit.value = isDesk ? 'Holidays in the Year' : 'Holidays of'
emitter.on('open-Holidays', () => openIt())

const compHolidays = computed(() => {
  const h = new Calendar(new Date(year.value, 0, 1))
  // console.log('-CK- holidays:', h.getHolidays())
  return h.getHolidays()
})

function updYear (yr) {
  year.value = yr
  // console.log(`-fn- updYear year=${year.value}`)
}
function setYear () {
  emitter.emit('open-HolidayYearPad', year.value)
}
function openIt () {
  // console.log('-fn-openIt', holidays.value) 
  opened.value = true
}
</script>

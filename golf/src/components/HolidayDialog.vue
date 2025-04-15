<template>
<q-dialog v-model="opened" transition-show="rotate" transition-hide="scale" :maximized="isIM">
  <div class="bg-teal-10 text-white" style="border:1px solid cyan">
    <q-card-section class="inset-shadow-down">
      <div class="bg-teal-8 text-h5 text-bold text-center">
        <div>{{ tit }}<q-btn flat class="text-h5 text-bold" @click="setYear" :label="year" /></div>
      </div>
    </q-card-section>

    <q-card-section>
      <div v-for="(h, i) in compHolidays" :key=h.i class="text-h6">
        <span class="text-bold">{{ h[0].toDateString() }}, </span>
        <span>{{ h[1] }}
          <q-tooltip class="text-h6 bg-blue">{{ h[2] }}</q-tooltip>
        </span>
        <hr v-if="i<compHolidays.length-1" style="width:100%" />
      </div>
    </q-card-section>

    <q-card-actions align="right" class="bg-cyan text-teal-10">
      <q-btn flat label="close" v-close-popup />
    </q-card-actions>
  </div>
  <YearPad ref="refYearPad" @upd-year="updYear" />
</q-dialog>
</template>
<script setup>
import emitter from 'tiny-emitter/instance'
import { ref, computed, onMounted } from 'vue' 
import { libFunctions } from '../composables/libFunctions'
const { isDesk, isIM, opened } = libFunctions()
import { Calendar } from './Calendar'
import YearPad from './YearPad'

var tit = null
const year = ref(new Date().getFullYear())
const refYearPad = ref(null)

console.log('-ST-HolidayDialog')

onMounted(() => { refYearPad })

tit = isDesk ? 'Holidays in the Year' : 'Holidays of'
emitter.on('show-holidays', () => opened.value = true)

const compHolidays = computed(() => {
  const h = new Calendar(new Date(year.value, 0, 1))
  return h.getHolidays()
})

function updYear (yr) {
  year.value = yr
  // console.log(`-CK-fn-updYear year=${year.value}`)
}
function setYear () {
  refYearPad.value.openIt(year.value)
}
</script>

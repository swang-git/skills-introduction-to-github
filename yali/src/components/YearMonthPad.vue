<template>
  <q-dialog v-model="opened">
    <div class="bg-cyan-19 q-pa-xs bg-red-9" style="border-radius:1px">
      <q-card class="bg-teal-10">
      <q-card-section>
        <div class="q-pa-sm bg-teal-10 text-lime text-h5 text-center text-no-wrap">{{ padTit }}</div>
        <div class="row">
          <q-card class="text-h6 bg-cyan-6" style="border-radius:1px;width:130px">
            <q-card-section>
              <div class="q-pa-md">
                <q-option-group :options="years" type="radio" v-model="year" dark dense />
              </div>
            </q-card-section>
          </q-card>
          <q-card class="text-h6 bg-cyan-7" style="border-radius:1px;width:233px">
            <q-card-actions class="">
              <!-- <q-input class="q-mx-xs q-pb-xs" v-model="keyedIn" outlined rounded dark dense readonly input-class="text-h6 q-pa-xs text-center" /> -->
              <q-btn glossy v-for="i in [1,  2, 3]" :key="i" class="q-ma-xs" size="lg" color="teal-9" round @click="month=i">{{ i }}</q-btn>
              <q-btn glossy v-for="i in [4,  5, 6]" :key="i" class="q-ma-xs" size="lg" color="teal-9" round @click="month=i">{{ i }}</q-btn>
              <q-btn glossy v-for="i in [7,  8, 9]" :key="i" class="q-ma-xs" size="lg" color="teal-9" round @click="month=i">{{ i }}</q-btn>
              <q-btn glossy v-for="i in [10,11,12]" :key="i" class="q-ma-xs" size="lg" color="teal-9" round @click="month=i">{{ i }}</q-btn>
              <q-btn class="q-ma-xs" size="lg" color="teal-9" glossy round @click="opened=false"><q-icon name="cancel" color="lime" /></q-btn>
              <q-btn outline class="q-ma-xs" size="lg" color="cyan-10" round><span class="text-bold text-red-9 text-h5">{{ month }}</span></q-btn>
              <q-btn class="q-ma-xs" size="lg" color="teal-9" glossy round @click="setYM"><q-icon name="check_circle" color="blue-4" /></q-btn>
            </q-card-actions>
          </q-card>
        </div>
      </q-card-section>
      </q-card>
      </div>
  </q-dialog>
  <!-- Only render if GLOBAL state is open -->
  <div v-if="numPadStore.isOpen" class="numpad">
    Singleton NumPad
  </div>
</template>
<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'
import { useNumPadStore } from '../../src/stores/numPadStore'
const numPadStore = useNumPadStore()

const opened = ref(false)
const padTit = ref(null) // numPad title
const month = ref(1)
const flag = ref(null)
const year = ref('2018')
const years = ref([])
const emit = defineEmits(['year-month'])

emitter.on('open-YearMonthPad', (x, y, z) => openIt(x, y, z))
console.log('-ST-YearMonthPad')

function setYM () {
  console.log(`-fn-setYM year=${year.value} month=${month.value} flag=${flag.value}`)
  emit('year-month', year.value + '.' + String(month.value).padStart(2, '0'))
  opened.value = false 
}
function openIt(flg, tit, yrs) {
  console.info(`flag=${flg} tit=${tit} years:`, yrs)
  flag.value = flg
  padTit.value = tit
  // years.value = yrs
  opened.value = true

  years.value = yrs.map((y) => ({ label: y, value: y, color: 'red' }))
}
</script>

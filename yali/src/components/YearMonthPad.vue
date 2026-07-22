<template>
  <q-dialog v-model="opened">
    <div class="bg-cyan-10 q-pa-xs bg-teal-9" style="border-radius:0">
      <q-card class="bg-teal-10" >
        <q-card-section>
          <div class="q-pa-sm bg-teal-10 text-lime text-h5 text-center text-no-wrap">{{ padTit }}</div>
          <div class="row no-wrap">
            <q-card class="text-h6 bg-cyan-6" style="border-radius:0;width:100px;height:340px">
              <q-card-section>
                <div class="q-pa-md text-h5" style="margin:-17px 0 0 -22px">
                  <q-option-group class="q-py-xs" :options="years" type="radio" v-model="year" dark dense />
                </div>
              </q-card-section>
            </q-card>
            <q-card class="text-h6 bg-cyan-7" style="border-radius:0;width:220px;height:340px">
              <q-card-actions class="q-py-md">
                <q-btn glossy v-for="i in [1,  2, 3]" :key="i" class="q-ma-xs" :size="isHarmony6 ? '16.4px' : '18px'" color="teal-9" round @click="month=i">{{ i }}</q-btn>
                <q-btn glossy v-for="i in [4,  5, 6]" :key="i" class="q-ma-xs" :size="isHarmony6 ? '16.4px' : '18px'" color="teal-9" round @click="month=i">{{ i }}</q-btn>
                <q-btn glossy v-for="i in [7,  8, 9]" :key="i" class="q-ma-xs" :size="isHarmony6 ? '16.4px' : '18px'" color="teal-9" round @click="month=i">{{ i }}</q-btn>
                <q-btn glossy v-for="i in [10,11,12]" :key="i" class="q-ma-xs" :size="isHarmony6 ? '16.4px' : '18px'" color="teal-9" round @click="month=i">{{ i }}</q-btn>
                <q-btn class="q-ma-xs" :size="isHarmony6 ? '16.4px' : '18px'" color="teal-9" glossy round @click="opened=false"><q-icon name="cancel" color="lime" /></q-btn>
                <q-btn outline class="q-ma-xs" :size="isHarmony6 ? '16.4px' : '18px'" color="cyan-10" round><span class="text-bold text-red-9 text-h5">{{ month }}</span></q-btn>
                <q-btn class="q-ma-xs" :size="isHarmony6 ? '16.4px' : '18px'" color="teal-9" glossy round @click="setYM"><q-icon name="check_circle" color="blue-4" /></q-btn>
              </q-card-actions>
            </q-card>
          </div>
        </q-card-section>
      </q-card>
    </div>
  </q-dialog>
  <!-- Only render if GLOBAL state is open -->
  <!-- <div v-if="numPadStore.isOpen" class="numpad">
    Singleton NumPad
  </div> -->
</template>
<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'
import { libFunctions } from '../composables/libFunctions.js'
const { isHarmony6 } = libFunctions()
// import { useNumPadStore } from '../../src/stores/numPadStore'
// const numPadStore = useNumPadStore()

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

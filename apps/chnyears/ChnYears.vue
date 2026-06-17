<template>
<div>
  <div class="row" :class="{'q-pl-sm':'isDesk', 'q-pl-lg':'isIM'}">
    <NumInput v-if="isDesk" class="col-2" :obj="row" label="year" icon="年" iconSize="28px" iColor="cyan" />
    <NumInput v-else class="col-5" :obj="row" label="year" icon="年" iconSize="28px" iColor="cyan" />
    <q-btn flat icon="GO" color="yellow" @click="setYear" />
  </div>
  <div class="text-h5 text-cyan-1 q-pl-md">
    <div v-for="(a, i) in dalist" :key="a">
      <div v-if="a[0]==row.year" class="bg-red row">
        <div class="q-pl-xs" v-if="i<9">0{{ i+1 }}.</div>
        <div class="q-pl-xs" v-else>{{ i+1 }}.</div>
        <div class="q-pl-md">{{ a[0] }}</div>
        <div class="q-pl-md">{{ a[1] }}</div>
        <div class="q-pl-md">{{ a[2] }}</div>
      </div>
      <div v-else class="row">
        <div class="q-pl-xs text-right" v-if="i<9">0{{ i+1 }}.</div>
        <div class="q-pl-xs text-right" v-else>{{ i+1 }}.</div>
        <div class="q-pl-md">{{ a[0] }}</div>
        <div class="q-pl-md">{{ a[1] }}</div>
        <div class="q-pl-md">{{ a[2] }}</div>
      </div>
    </div>
  </div>
</div>
</template>
<script setup>
import { ref, computed } from 'vue'
import emitter from 'tiny-emitter/instance'
import { libFunctions } from '../src/composables/libFunctions'
import { axiosFunctions } from '../src/composables/axiosFunctions'
import { dayFunctions } from '../src/composables/dayFunctions'
const { chwk1, chwk2, today } = dayFunctions()
const { gaxios, paxios } = axiosFunctions()
const { isIM, isDesk, buildApp, dalist, $q } = libFunctions()

import NumInput from '../src/components/NumInput.vue'

console.log(`-ST-ChnYears`)

//======= variables =========
const year = ref(2025)
const row = ref({ year:year.value })
const dats = ref([])
const nRow = ref(null)
const separator = ref('cell')
const showCol = ref(null)

//======= main =========
emitter.on('chnyears-getList', (x) => setList(x))
buildApp('公历农历生肖对照表', 'ChnYears')
if (row.value.year > 0) getList(year.value)
console.log(`-CK-isDesk=${isDesk} isIM=${isIM}`)

//======= functions =========
function setYear() {
  console.log(`-fn-setYear year=${year.value} row.year=${row.value.year}`)
  row.value.year
  getList(row.value.year)
}
function getList(year) {
  console.log(`-fn-chnyears.getList year=${year}`)
  row.value.year = year
  const path = process.env.API + '/chnyears/getList/' + year
  gaxios(path)
}
function setList(da) {
  dats.value = da.lst
  nRow.value = da.lst.length
  emitter.emit('items-per-page', isIM ? 12 : `${nRow.value}`)
  emitter.emit('dats', dats.value)
  // row.value.year = 1001
}

</script>
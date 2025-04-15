<template>
<q-dialog v-model="opened">
  <div class="bg-amber q-pa-xs" style="width:240px;margin:-180px 0 0 0;border-radius:40px">
    <q-card class="text-h6 bg-teal-10" style="border-radius:40px">
      <!-- <q-card-actions class="justify-center text-white"><span class="text-body1 text-grey">YOU KEYED-IN: </span>{{ keyedIn }}</q-card-actions> -->
      <q-card-actions class="justify-center text-white"><span class="text-body1 text-grey">Enter value for {{ label }}</span></q-card-actions>
      <q-card-actions class="justify-center">
        <q-btn round class="q-ma-xs" color="amber" size="lg" icon="chevron_left" v-close-popup />
        <q-btn round class="q-ma-xs" color="red-9" size="lg" icon="delete" @click="setNumber('X')" />
        <q-btn round class="q-ma-xs" color="cyan-9" size="lg" @click="setNumber(0)"> 0 </q-btn>
        <q-btn v-for="i in [1,2,3]" :key=i class="q-ma-xs" size="lg" color="cyan-9" round @click="setNumber(i)">{{ i }}</q-btn>
        <q-btn v-for="i in [4,5,6]" :key=i class="q-ma-xs" size="lg" color="cyan-9" round @click="setNumber(i)">{{ i }}</q-btn>
        <q-btn v-for="i in [7,8,9]" :key=i class="q-ma-xs" size="lg" color="cyan-9" round @click="setNumber(i)">{{ i }}</q-btn>
      </q-card-actions>
    </q-card>
  </div>
</q-dialog>
</template>
<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'

const opened = ref(false)
const label = ref(null)
const row = ref(null)
const field = ref({ value: '' })

console.log('-ST-FloatPad')

function openIt (lbl, rw) {
  console.log(`-openIt-label=%c${lbl}`, 'color:red;font-size:30px;')
  opened.value = true,
  label.value = lbl
  row.value = rw
  if (label.value === 'Total Cost') row.value.cost = ''
  else if (label.value === 'Unit Price') row.value.unip = ''
  else if (label.value === 'Quantities') row.value.quan = ''
  else if (label.value === 'Miles Run') row.value.mile = ''
}
function setNumber (n) {
  console.log(`-setNumber-${n}`, label.value)
  if (label.value === 'Total Cost') {
    if (n === 'X') {
      row.value.cost = ''
      return
    } else {
      row.value.cost += n
    }
  } else if (label.value === 'Unit Price' || label.value === 'Fidelity CCard Payment') {
    console.log(`-set-${n} label=${label.value}`, row.value.unip)
    if (n === 'X') {
      row.value.unip = ''
      return
    } else {
      row.value.unip += n
    }
  } else if (label.value === 'Quantities') {
    if (n === 'X') {
      row.value.quan = ''
      return
    } else {
      row.value.quan += n
    }
  } else if (label.value === 'Won or Lost') {
    if (n === 'X') {
      row.value.quan = ''
      return
    } else {
      row.value.quan += n
    }
  } else if (label.value === 'Miles Run') {
    if (n === 'X') {
      row.value.mile = ''
      return
    } else {
      row.value.mile += n
    }
  }
}
</script>
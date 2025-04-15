<template>
<q-input ref="nref" rounded outlined class="bg-teal-10 text-right text-h6" reverse-fill-mask :mask="mask" 
  v-model="compInput" :label="label" dark clearable clear-icon="delete" :disable=disable :prefix="prefix" inputmode="numeric">
  <template v-slot:prepend>
    <q-icon class="q-pl-none" :name="icon" :color="iColor" size="40px" />
  </template>
  <div v-if="rightIcon && (isDesk || showRight)" class="text-white q-pt-sm">
    <q-btn round outline :color="iColor" :icon="(label==='Won or Lost' || label==='W or L') ? 'remove_circle' : 'delete'" @click="reset()" />
  </div>
</q-input>
</template>
<script setup>
/* eslint-disable */
import { computed, ref, onMounted } from 'vue'
const emit = defineEmits([
  'calc-mileage',
  'green-fee-done',
  'disable-gc',
])
const props = defineProps( {
  wtype: { type: String },
  mask: { type: String },
  label: { type: String },
  icon: { type: String },
  rightIcon: { type: Boolean },
  showRight: { type: Boolean },
  iColor: { type: String },
  disable: { type: Boolean },
  prefix: { type: String, default: '' },
  obj: { type: Object }
})

console.log('-ST-NumInput')

onMounted(() => { nref })
const nref = ref(null)

const compObj = computed(() => { return props.obj })

function reset () {
  if (props.label === 'Won or Lost' || props.label === 'W or L') compObj.value.quan = -compObj.value.quan
  else { Reflect.set(compObj.value, getPropertyKey(), null); nref.value.focus() }
}
function getPropertyKey () {
  if (props.label === 'Total Cost') return 'cost'
  else if (props.label === 'Gift Card Balance') return 'gcardVal'
  else if (props.label === 'Gift Card Number') return 'gcardNum'
  else if (props.label === 'Green Fee') return 'fees'
  else if (props.label === 'Won or Lost') return 'quan'
  else if (props.label === 'Teetime Gap') return 'ttgap'
  else if (props.label === 'Num of Groups') return 'numGroup'
  else if (props.label === 'Tee Time Gap') return 'teetime_gap'
}
const compInput = computed({
  get: () => {
    return Reflect.get(props.obj, getPropertyKey())    
    // if (props.label === 'Total Cost') return props.obj.cost
    // if (props.label === 'FCard Pay') return parseFloat(props.obj.unip).toFixed(2)
    // else if (props.label === 'Unit Price') return props.obj.unip
    // if (props.label === 'Quantities') return props.obj.quan
    // if (props.label === 'Miles Run') return props.obj.mile
    // if (props.label === 'Gift Card Balance') return props.obj.gcardVal
    // if (props.label === 'Gift Card Number') return props.obj.gcardNum
    // if (props.label === 'Weight') return parseFloat(props.obj.weight).toFixed(props.wtype === 'pond' ? 1 : 2)
    // if (props.label === 'Portfolio') return props.obj.portfolio
    // if (props.label === 'Repeated Days') return props.obj.recursive
    // if (props.label === 'Green Fee') return props.obj.fees
    // if (props.label === 'Won or Lost') return props.obj.quan
    // if (props.label === 'Teetime Gap') return props.obj.ttgap
    // if (props.label === 'Num of Groups') return props.obj.numGroup
    // if (props.label === 'Fasting') return props.obj.fasting
    // if (props.label === 'Two Hour After Eating') return props.obj.af2hour
    // if (props.label === 'Green Fees') return props.obj.fees
    // if (props.label === 'Tee Time Gap') return props.obj.teetime_gap
    return props.label
  },
  set: val => {
    Reflect.set(compObj.value, getPropertyKey(), val)
    // console.log('-CK-NumInput set', props.wtype, val, props.obj)
    // if (props.label === 'Total Cost') compObj.value.cost = parseFloat(val)
    // if (props.label === 'Unit Price' || props.label === 'FCard Pay') {
    //   compObj.value.unip = parseFloat(val)
    //   if (props.label === 'FCard Pay') {
    //     compObj.value.cost = 0.00
    //     compObj.value.quan = null
    //   } else {
    //     if (compObj.value.unip > 0) compObj.value.quan = (compObj.value.cost / compObj.value.unip).toFixed(2)
    //   }
    // }
    // if (props.label === 'Quantities') compObj.value.quan = parseFloat(val)
    // if (props.label === 'Miles Run') {
    //   compObj.value.mile = parseFloat(val)
    //   emit('calc-mileage')
    // }
    // if (props.label === 'Gift Card Balance') compObj.value.gcardVal = parseFloat(val)
    // if (props.label === 'Gift Card Number') compObj.value.gcardNum = parseInt(val)
    // if (props.label === 'Weight') {
    //   compObj.value.weight = parseFloat(val)
    //   if (event.type === 'input') {
    //     if (props.wtype === 'kilo') compObj.value.kilo = parseFloat(val)
    //     else if (props.wtype === 'jing') compObj.value.kilo = parseFloat(val) / 2
    //     else if (props.wtype === 'pond') compObj.value.kilo = parseFloat(val) / 2.2046244202
    //   }
    //   console.log('-dg-NumInput setX', event.type, props.wtype, val, compObj.value.kilo)
    // }
    // if (props.label === 'Portfolio') compObj.value.portfolio = parseFloat(val)
    // if (props.label === 'Repeated Days') compObj.value.recursive = parseFloat(val)
    // if (props.label === 'Green Fee') {
    //   // compObj.value.fees = parseFloat(val)
    //   compObj.value.fees = val
    //   emit('green-fee-done')
    // }
    // if (props.label === 'Won or Lost') compObj.value.quan = parseFloat(val)
    // if (props.label === 'Teetime Gap') compObj.value.ttgap = parseInt(val)
    // if (props.label === 'Num of Groups') compObj.value.numGroup = parseInt(val)
    // if (props.label === 'Fasting') compObj.value.fasting = val
    // if (props.label === 'Two Hour After Eating') compObj.value.af2hour = val
    // if (props.label === 'Green Fees') compObj.value.fees = val
    // if (props.label === 'Tee Time Gap') compObj.value.teetime_gap = val
  }
})
function inputChange () {
  if (parseFloat(parseFloat(props.obj.cost).toFixed(2)) > props.obj.gcardVal && props.label === 'Total Cost' && props.obj.paym === 'MGift Card') {
    console.log('-CK-fn-inputChange', props.obj.cost)
    emit('disable-gc', false)
    q.dialog({
      title: 'Gift card balance is low, please provide new gift card with NEW BALANCE and NUMBER'
    })
  } else {
    emit('disable-gc', true)
  }
}
</script>

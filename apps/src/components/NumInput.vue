<template>
<q-input ref="nref" rounded outlined class="text-h6 q-px-xs q-pt-xs" :mask="mask" reverse-fill-mask v-model="compInput" :label="label" dark :disable=disable inputmode="numeric" :prefix="prefix" :suffix="suffix">
  <template v-if="appedIcon" v-slot:append>
    <q-icon :size="iconSize" :name="icon" :color="iColor" />
  </template>
  <template v-else v-slot:prepend>
    <q-icon v-if="/FT|DJ|SP|NQ|NK/.test(icon)" :size="iconSize" :name="icon" :color="iColor" style="margin-top:-10px" />
    <q-icon v-else :size="iconSize" :name="icon" :color="iColor" />
  </template>
  <div v-if="rightIcon && (isDesk || showRight)" class="text-white q-pt-sm">
    <q-btn round outline :color="iColor" :icon="(label==='Won or Lost' || label==='W or L') ? 'remove_circle' : 'delete'" @click="reset()" />
  </div>
</q-input>
</template>
<script setup>
import { ref, computed, onMounted } from 'vue'
import emitter from 'tiny-emitter/instance';
import { libFunctions } from '../composables/libFunctions'
const { isDesk } = libFunctions()
const nref = ref(null)
const props = defineProps({
  wtype: { type: String },
  mask: { type: String },
  label: { type: String },
  icon: { type: String },
  iColor: { type: String },
  disable: { type: Boolean },
  obj: { type: Object },
  appedIcon: { type: Boolean },
  rightIcon: { type: Boolean },
  showRight: { type: Boolean },
  iconSize: { type: String, default: 'lg' },
  prefix: { type: String, default: '' },
  suffix: { type: String, default: '' },
  id: { type: String, default: null },
})
onMounted(() => { nref })

const compObj = computed(() => { return props.obj })

function getPropertyKey () { // used by reflection functions get/set and others
  if (props.label === 'Total Cost' || props.label === 'Cost') return 'cost'
  else if (props.label === 'FCard Pay') return 'unip' // parseFloat(props.obj.unip).toFixed(2)
  else if (props.label === 'Fidelity CCard Payment') return 'unip'
  else if (props.label === 'Unit Price' || props.label === '单价') return 'unip' 
  else if (props.label === 'Quantities' || props.label === '数量') return 'unip'
  else if (props.label === 'Miles Run') return 'mile'
  else if (props.label === 'Gift Card Balance') return 'gcardVal'
  else if (props.label === 'Gift Card Number') return 'gcardNum'
  else if (props.label === 'Weight') return 'weight' 
  else if (props.label === 'Portfolio') return 'portfolio'
  else if (props.label === 'Repeated Days') return 'recursive'
  else if (props.label === 'Green Fee') return 'fees'
  else if (props.label === 'Won or Lost' || props.label === 'W or L') return 'quan'
  else if (props.label === 'Teetime Gap') return 'ttgap'
  else if (props.label === 'Num of Groups') return 'numGroup'
  else if (props.label === 'Fasting') return 'fasting'
  else if (props.label === 'Blood Glucose Level') return 'fasting'
  else if (props.label === 'Sugar Level') return 'fasting'
  else if (props.label === 'Recursive') return 'recursive'
  else if (props.label === 'Dow Jones') return 'dowjones'
  else if (props.label === 'Nasdaq') return 'nasdaq'
  else if (props.label === 'S&P 500') return 'sp500'
  else if (props.label === 'NIKKEI') return 'nikkei'
  else if (props.label === 'FTSE 100') return 'ftse100'
  else if (props.label === 'Hi Blood Pressure') return 'hibp'
  else if (props.label === 'Lo Blood Pressure') return 'lobp'
  else if (props.label === 'Heart Pulse') return 'hpls'
  else if (props.label === 'weight') return 'weight'
  return props.label
}
function reset () {
  if (props.label === 'Won or Lost' || props.label === 'W or L') compObj.value.quan = -compObj.value.quan
  else { Reflect.set(compObj.value, getPropertyKey(), null); nref.value.focus() }
}
const compInput = computed({
  get: () => {
    let pkey = getPropertyKey()
    // if (pkey === 'weight') return props.obj.weight == null ? null : typeof(props.obj.weight) == 'number' ? props.obj.weight.toFixed(1) : props.obj.weight
    return Reflect.get(props.obj, pkey)
  },
  set: (val) => { Reflect.set(compObj.value, getPropertyKey(), val) }
})
</script>

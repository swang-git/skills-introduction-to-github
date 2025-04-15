<template>
<div :style="isDesk ? { border:'1px solid cyan',marginTop:'-10px' } : { margin:'-0px 0 0 47px' }">
  <q-table :rows="rows" :columns="columns" :class="expColor" :style="isIM ? { width:'370px' } : {}"
    row-key="name" hide-header hide-pagination dark :pagination="{ rowsPerPage:12 }" dense>

    <template v-slot:top-left v-if="!props.record.del">
      <div v-if="Object.keys(props.record).length>0" class="text-h5 text-center">{{ getTitle() }}</div>
    </template>
    <template v-slot:top-right="p">
      <div class="col-1">
        <q-fab v-model="fabOpen" flat icon="keyboard_arrow_down" direction="down">
          <q-btn v-if="isIM" flat color="amber" :icon="p.inFullscreen ? 'fullscreen_exit' : 'fullscreen'" @click="p.toggleFullscreen" class="q-pt-sm" />
          <!-- <q-btn round glossy class="q-mr-sm" size="16px" icon="delete"     color="red-10"    @click="emitter.emit('del-row', props.record)" /> -->
          <q-btn round glossy class="q-mr-sm" size="16px" icon="delete"     color="red-10"    @click="emit('del-row', props.record)" />
          <q-btn round glossy class="q-mr-sm" size="16px" icon="update"     color="indigo-10" @click="emit('open-dar', 'upd', props.record)" />
          <q-btn round glossy class="q-mr-sm" size="16px" icon="add_circle" color="green-10"  @click="emit('open-dar', 'add', props.record)" />
        </q-fab>
      </div>
    </template>

    <template v-slot:body="p">
      <tr class="bg-cyan-10 text-white">
        <td style="font-size:18px;width:68px;text-align:left" :class="colColor">{{p.cols[0].value}}</td>
        <td style="font-size:18px" class="ellipsis" v-html="p.cols[1].value" />
      </tr>
    </template>
  </q-table>
</div>
</template>
<script setup>
import { ref, watch } from 'vue'
import emitter from 'tiny-emitter/instance'
import { libFunctions } from '../composables/libFunctions'
import Tooltip from './Tooltip.vue'
const { isIM, isDesk } = libFunctions()
const columns = [
  { name: 'name', required: true, field: 'colname' },
  { name: 'details', required: true, align: 'left', field: 'details'},
]
const emit = defineEmits([
  'del-row',
  'open-dar'
])
const props = defineProps({
  record: { type: Object },
  expColor: { type: String },
  colColor: { type: String },
  idx: { type: Number },
})
const fabOpen = true
console.log('-ST-GridPropTable')
let bgColor = ref('bg-teal-10')
let rows = ref([])
function getTitle () {
  // if (Object.keys(pr.record).length === 0) return
  // if (pr.record == null || pr.record == undefined) return
  const p = props.record
  const idx = props.idx+1
  console.log('-CK-clickedRow-gridPropsTable watched row', p)
  return idx + ') Test data entered at ' + p.created_at.substring(0, 16).replace('T', ' ') + ' (' + p.id +')'
  // if (p.add) {
  //   return idx.value + ') Test data newly added at ' + p.created_at.substring(0, 16).replace('T', '') + ' (' + p.id +')'
  // } else if (p.upd) {
  //   return  idx.value + ') Test data Updated at ' + p.created_at.substring(0, 16).replace('T', '') + ' (' + p.id +')'
  // } else if (p.del) {
  //   return idx.value + ') Test data Deleted at ' + p.created_at.substring(0, 16).replace('T', '') + ' (' + p.id +')'
  // } else {
  //   return idx.value + ') Test data entered at ' + p.created_at.substring(0, 16).replace('T', '') + ' (' + p.id +')'
  // }
}
function showDetails() {
  // console.log(`-fn-showDetails`, props.record)
  rows.value = []
  const p = props.record
  const date = { colname:'时间', details: p.date + (p.day == null ? '' : ' (' + p.day + ')')}; rows.value.push(date)
  const A1c = { colname:'A1c', details: p.A1c }; rows.value.push(A1c)
  const AST = { colname:'AST', details: p.AST }; rows.value.push(AST)
  const ALT = { colname:'ALT', details: p.ALT }; rows.value.push(ALT)
  const ALTH = { colname:'ALTH', details: p.ALTH }; rows.value.push(ALTH)
  const HDL = { colname:'HDL', details: p.HDL }; rows.value.push(HDL)
  const LDL = { colname:'LDL', details: p.LDL }; rows.value.push(LDL)
  const Bilirubin = { colname:'Bilirubin', details: p.Bilirubin }; rows.value.push(Bilirubin)
  const Alkaline = { colname:'Alkaline', details: p.Alkaline }; rows.value.push(Alkaline)
  const Triglycerides = { colname:'Triglycerides', details: p.Triglycerides }; rows.value.push(Triglycerides)
  const TriglyceridesH = { colname:'TriglyceridesH', details: p.TiglyceridesH }; rows.value.push(TriglyceridesH)
  const PSA = { colname:'PSA', details: p.PSA }; rows.value.push(PSA)
  const PSAH = { colname:'PSAH', details: p.PSAH }; rows.value.push(PSAH)
}
watch(props, showDetails) 
function getColor () {
  const row = record
  console.log('-CK-fn-getColor', row)
  return row.add ? 'text-purple' : row.upd ? 'text-amber-10' : row.del ? 'white' : 'text-amber-10'
}
</script>

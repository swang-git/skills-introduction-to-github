<template>
<div id="expd" :style="isDesk ? { marginTop:getMarginTop() } : { margin:'-90px 0 0 -2px' }">
  <q-table :rows="rows" :columns="columns" :class="expColor" :style="isIM ? { 'width':screenwidth + 'px' } : { }"
    row-key="name" hide-header hide-pagination dark :pagination="{ rowsPerPage:12 }" dense>

    <template v-slot:top-right="p">
      <!-- <q-fab  v-model="fabOpen" flat hide-icon :label="getTitle()" direction="left" color="cyan-2" :style="idx>23 ? { marginTop:'-180px' } : {}"> -->
      <q-fab  v-model="fabOpen" flat hide-icon :label="isIM ? '' : getTitle()" direction="left" color="cyan-2">
        <q-btn round glossy class="q-mr-sm" size="16px" icon="add_circle" color="green-10"  @click="$emit('open-dar', 'add')" />
        <q-btn round glossy class="q-mr-sm" size="16px" icon="update"     color="indigo-10" @click="$emit('open-dar', 'upd')" v-if="!props.record.del"  />
        <q-btn v-if="hasPurchases" round glossy size="16px" icon="shopping_cart" color="green-10" text-color="amber-1" class="q-mr-sm" @click="$emit('open-plist')" />
        <q-btn v-if="isReconcileC" round glossy size="16px" icon="credit_card"   color="amber-10" text-color="amber-2" class="q-mr-sm" @click="$emit('open-recon')" />
        <q-btn v-if="hasGolfScore" round glossy size="16px" icon="golf_course"   color="green-10" text-color="amber-5" class="q-mr-sm" @click="$emit('open-score')" />
        <q-btn round glossy class="q-mr-sm" size="16px" icon="delete"     color="red-10"    @click="emitter.emit('del-row', props.record)" />
        <q-btn v-if="isIM" round size="10px" color="pink" @click="p.toggleFullscreen" class="q-pt-sm" />
      </q-fab>
    </template>

    <template v-slot:body="p">
      <tr class="bg-cyan-7 text-grey-10">
        <td style="font-size:18px;width:68px;text-align:left" :class="colColor">{{p.cols[0].value}}</td>
        <td style="font-size:18px" class="ellipsis" v-html="p.cols[1].value" />
      </tr>
    </template>
  </q-table>
</div>
</template>
<script setup>
import { ref, watch, computed } from 'vue'
import emitter from 'tiny-emitter/instance'
import { libFunctions } from '../composables/libFunctions'
import Tooltip from './Tooltip.vue'
const { isIM, isDesk, screenwidth } = libFunctions()
const columns = [
  { name: 'name', required: true, field: 'colname' },
  { name: 'details', required: true, align: 'left', field: 'details'},
]
const props = defineProps({
  record: { type: Object },
  hasPurchases: { type: Boolean },
  isReconcileC: { type: Boolean },
  hasGolfScore: { type: Boolean },
  expColor: { type: String },
  colColor: { type: String },
  idx: { type: Number },
})
const fabOpen = ref(true)
const cIdx = ref(0)
emitter.on('clicked-idx', (x) => cIdx.value = x)
// console.log('-ST-ExpDetails')
let bgColor = ref('bg-teal-10')
let rows = ref([])

function getMarginTop () {
  if (cIdx.value % 25 < 17) return '-10px'
  let expH = props.record.height * 42 + 140 + (25 - cIdx.value % 25) * 2
  console.log(`-CK-getMarginTop nLine=${props.record.height} expH=${expH}`, props.record)
  return -expH + 'px'
}
function getTitle () {
  const p = props.record
  const idx = ref(props.idx+1)
  // console.log('-CK-getTitle clickedRow-gridPropsTable watched row', p)
  if (isDesk) {
    if (p.add) {
      return idx.value + ') Newly Added Purchase at ' + p.created_at + ' (' + p.id + ')'
    } else if (p.upd) {
      return  idx.value + ') This Purchase Updated at ' + p.updated_at + ' (' + p.id + ')'
    } else if (p.del) {
      return idx.value + ') This Purchase Deleted at ' + p.deleted_at + ' (' + p.id + ')'
    } else return idx.value + ') Purchase Details created at ' + p.created_at + ' (' + p.id +')'
  } else {
    if (p.add) {
      return idx.value + ') Add at ' + p.created_at.substring(0, 16) + ' (' + p.id + ')'
    } else if (p.upd) {
      return  idx.value + ') Upd at ' + p.updated_at.substring(0, 16) + ' (' + p.id + ')'
    } else if (p.del) {
      return idx.value + ') Del at ' + p.deleted_at.substring(0, 16) + ' (' + p.id + ')'
    } else return idx.value + ') Add at ' + p.created_at.substring(0, 16).substring(0, 16) + ' (' + p.id +')'
    // return idx.value + ') Add at ' + p.created_at.substring(0, 16) + ' (' + p.id +')'
  }
}
function showDetails () {
  // console.log(`-fn-showDetails`, props.record)
  rows.value = []
  const p = props.record
  const isCCard = props.isReconcileC
  const isGPlay = p.cats === 'Golf' && p.subc === 'Play'
  const isShopp = p.cats === 'Shopping'
  const date = { colname:'时间', details: p.date + (p.day == null ? '' : ' (' + p.day + ')')}; rows.value.push(date)
  const cats = { colname:'类别', details: p.cats }; rows.value.push(cats)
  const subc = { colname:'次类', details: p.subc }; rows.value.push(subc)
  const paye = { colname:isCCard ? '卡名' : isGPlay ? '球场' : isShopp ? '购自' : '付给', details: p.paye }; rows.value.push(paye)
  const cost = { colname:'支付', details: '$' + p.cost }; if (!isCCard) rows.value.push(cost)
  const paym = { colname:'方式', details: p.paym }; rows.value.push(paym)
  if (p.lnk  != null) { const link = { colname:'链接', details: '<div style="margin-left:-2px">' + p.lnk + '</div>'}; rows.value.push(link) }
  if (p.unip != null) { const unip = { colname: isCCard ? '月付' : '单价', details: isCCard ? parseFloat(p.unip).toFixed(2) : p.unip }; rows.value.push(unip) }
  if (p.quan != null) { const quan = { colname: isGPlay ? '比赛' : '数量', details: p.quan }; rows.value.push(quan) }
  if (p.mile != null) { const mile = { colname:'里程', details: p.mile + ' Miles (Gas Mileage: ' + (p.mile/p.quan).toFixed(1) + ' Miles/Gallon)' }; rows.value.push(mile)}
  if (p.note != null) { const note = { colname:isCCard ? '周期' : '注释', details: p.note }; rows.value.push(note) }
  if (p.post_date != null) { const post = { colname: 'Post', details: p.post_date }; rows.value.push(post) }
  // if (p.cats === 'Golf' && p.subc === 'Play' && p.paym === 'MGift Card') {
  if (p.cats === 'Golf' && ['Play', 'Membership'].includes(p.subc) && [9, 15].includes(p.paymId)) {
    { const cval = { colname:'GCB', details: '$' + p.gcardVal + ' (Gift Card Current Balance)' }; rows.value.push(cval) }
    { const cnum = { colname:'GCN', details: p.gcardNum + ' (Gift Card Number)'}; rows.value.push(cnum) }
    { const pval = { colname:'PVB', details: '$' + p.prevbal + ' (Previous Balance of the Card) = ' + p.gcardVal + ' + ' + p.cost }; rows.value.push(pval) }
  }
  console.log(`%c-CK-showDetails p.height=${p.height} p.date=${p.date}`, 'color: red')
  // if (isIM) p.inFullscreen = true
}
const compIdx = computed(() => { return props.idx })
watch(compIdx, showDetails) 
// watch(cIdx, (newIdx) => {
//     console.log(`%c-CK-watch cIdx=${cIdx.value} pIdx=${props.idx}`, 'color:red')
//     // if (cIdx.value >= 0) showDetails()
//   }
// )
</script>

<template>
<div class="q-px-xs full-width">
  <div v-for="(e, i) in palist" :key=e.id class="row-border text-h6">
    <div v-if="isDesk" @click="showIt(i)" :style="getLineBackground(i)">
      <div class="row" :class="{ 'bg-cyan-9':e.add, 'bg-purple-9':e.upd }">
        <q-td class="q-px-xs col-4 ellipsis">{{ e.chinese }}</q-td>
        <q-td class="q-pl-xs col-8 ellipsis cursor-pointer">{{ e.english }}</q-td>
      </div>
    </div>
    <div v-else @click="showIt(i)" class="row text-h6" :style="getLineBackground(i)">
      <!-- <div class="col-4">{{ e.datetime }}</div> -->
      <div class="col-4 ellipsis">{{ e.chinese }}</div>
      <div class="col-8 ellipsis">{{ e.english }}</div>
    </div>
    <div :class="{ hidden: e.hideIt }" class="q-pa-sm text-yellow text-h6 bg-cyan-3">
      <q-card class="q-pa-xs" square>
        <q-card-section class="q-px-sm text-red-6 bg-teal-10">{{ e.chinese }}</q-card-section>
        <q-btn color="red-6" round glossy icon="edit" @click="editIt(e)" style="margin:-55px 10px 0 0;float:right" />
        <q-card-section class="q-px-sm text-white bg-teal-10">{{ e.english }}</q-card-section>
        <q-card-section v-if="e.note!=null" class="q-px-md q-pt-xs text-black bg-cyan-3"><span  v-html="e.note" /></q-card-section>
        <q-card-section v-if="e.lnks!=null" class="q-px-md text-black bg-cyan-2" style="margin:-15px 0 0 0"><span  v-html="e.lnk" /></q-card-section>
      </q-card>
    </div>
  </div>
  <UserInput ref="refUserInput" @deled-word="delRow" @added-word="addRow" @upded-word="updRow" />
</div>
</template>
<script setup>
import { ref, onMounted } from 'vue'
import emitter from 'tiny-emitter/instance'
import { libFunctions } from '../src/composables/libFunctions'
const { buildApp, isIM, isDesk, palist, getLineBackground } = libFunctions()
import { axiosFunctions } from '../src/composables/axiosFunctions'
const { gaxios, paxios } = axiosFunctions()
import UserInput from './UserInput.vue'

const dats = ref([])
const refUserInput = ref(null)
var clickedRow = {}
var clickedRowId = -1

// const emit = defineEmits(['user-confirm'])
console.log('-ST-dictionary')
onMounted(() => { refUserInput })
buildApp('英汉字典', 'Dictionary')
emitter.emit('items-per-page', isIM ? 13 : 30)
getList()
emitter.on('dictionary-getList', (x) => setList(x))

function getList () {
  const path = process.env.API + '/dictionary/getList'
  gaxios(path)
}
function setList (da) {
  // console.log('-fn-setList', da.list)
  dats.value = da.list
  dats.value.forEach(p => { p.hideIt = true })
  // this.$root.$emit('num-items', dats.value.length)
  emitter.emit('dats', da.list)
  // console.log('-fn-setList palist', palist.value)
}
// function axiosBack (target, da) {
//   if (target === 'apps.getAttached') {
//     console.log('-dg-getAttached', da.files)
//     const rootdir = '/docs/' + clickedRow.link
//     clickedRow.lnk = '<ol>'
//     da.files.forEach((p) => {
//       const lnkname = p.replace(/[_|-]/g, ' ').replace('.pdf', '')
//       clickedRow.lnk += '<li><a href="' + rootdir + '/' + p + '" target="_blank">' + lnkname + '</a>'
//     })
//   }
// }
function editIt (row) {
  if (row.details == null) row.details = ''
  // console.log('-CK-fn-editIt', row)
  refUserInput.value.openIt(JSON.stringify(row))
}
function getIcon (i) {
  return palist.value[i].hideIt ? 'list_alt' : 'web'
}
function showIt (i) {
  palist.value.forEach((p, idx) => { if (idx !== i) p.hideIt = true })
  clickedRow = palist.value[i]
  clickedRow.hideIt = !clickedRow.hideIt
  clickedRowId = i
  // console.log(`-CK-showIt B hideIt clickedRowId=${clickedRowId} hideIt=${clickedRow.hideIt}`)
}
function addRow (row) {
  row.hideIt = true
  row.add = true
  console.log('-CK-addRow', row)
  dats.value.unshift(row)
}
function updRow (row) {
  row.hideIt = true
  row.upd = true
  console.log('-CK-updRow', row)
  dats.value.splice(clickedRowId, 1, row)
}
function delRow () {
  console.log(`-CK-user confirmed to delete row clickedRowId=${clickedRowId}`, clickedRow)
  dats.value.splice(clickedRowId, 1)
}
function showUserInput (row, rowIdx) {
  console.log('-fn-clicked info', rowIdx, row)
  clickedRow = row
  refUserInput.value.openIt(row)
}
</script>
<style>
div>td:hover {
  background: red;
  color: yellow;
}
</style>

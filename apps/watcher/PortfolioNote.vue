<template>
<q-dialog v-model="opened">
  <q-layout container class="bg-teal-9" style="height:600px;min-width:800px" >
    <q-header class="bg-teal-9">
      <q-toolbar>
        <q-btn round glossy icon='keyboard_arrow_left' @click='opened=false' color="orange" />
        <q-toolbar-title class="text-center">{{ date }} ({{ today().chwk3() }}) Portfolio Notes </q-toolbar-title>
        <q-btn round glossy color="red" icon="delete" @click="del" />
      </q-toolbar>
    </q-header>
    <div class="q-pt-xl">
    <q-footer v-if="isDesk" class="bg-teal-10">
      <q-toolbar>
        <q-toolbar-title>
          <q-card-actions align="between">
            <q-btn v-if="pnote.length>0" glossy icon="update" color="orange" label="Update" @click="upd" />
            <q-btn v-if="pnote.length>0" glossy icon="cancel" color="red" label="delete row" @click="delRow" />
            <q-btn glossy icon="add_circle" color="green" label="add row" @click="addRow" />
            <q-btn glossy icon="create" color="teal-6" label="Create" @click="add" />
          </q-card-actions>
        </q-toolbar-title>
      </q-toolbar>
    </q-footer>
    </div>
    <div class="q-pt-xl q-pa-sm">
      <div v-for="(pn, i) in pnote" :key=pn.id>
        <q-card class="q-ma-sm q-pa-xs bg-teal-10" bordered dark style="height:150px">
          <div class="row">
            <q-input filled style="font-size:19px" class="col-2" label="From Account" v-model="pn.faccount" dark @click="showOptList(i, 'faccnt')">
              <q-tooltip class="text-h6 bg-red-10">{{ pn.tooltip }}</q-tooltip>
            </q-input>
            <q-input filled style="font-size:19px" class="col-2" label="Symbol" v-model="pn.symbol" dark @click="showStockList(i, 'stock')" />
            <q-input filled style="font-size:19px" class="col-2" label="Action" v-model="pn.action" dark @click="showActionList(i, 'action')" />
            <q-input filled style="font-size:19px" class="col-2" label="Price" v-model="pn.price" dark />
            <q-input filled style="font-sizepx" class="col-2" label="Share" v-model="pn.share" dark />
            <q-input filled style="font-size:19px" class="col-2" label="To Account" v-model="pn.taccount" dark @click="showOptList(i, 'taccnt')">
              <q-tooltip v-if="pn.taccount != null" class="text-h6 bg-cyan-9">{{ pn.txoltip }}</q-tooltip>
            </q-input>
          </div>
          <q-input filled style="font-size:19px" label="Notes" v-model="pn.note" dark autogrow />
        </q-card>
      </div>
    </div>
  </q-layout>
</q-dialog>
<SelOptionsWithSearch @selected-option="selectedOption" />
<ConfirmDialog @user-confirmed="userConfirmed" />
</template>
<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'
import ConfirmDialog from '../src/components/ConfirmDialog'
import SelOptionsWithSearch from '../src/components/SelOptionsWithSearch'

import { axiosFunctions } from '../src/composables/axiosFunctions'
import { libFunctions } from '../src/composables/libFunctions'
import { dayFunctions } from '../src/composables/dayFunctions'
const { paxios } = axiosFunctions()
const { isDesk } = libFunctions()
const { today, chwk3 } = dayFunctions()

const opened = ref(false)
var date = null
var userId = null
const pnote = ref([])
const accounts = ref([])
const accntOpts = ref([])
const stocks = ref([])
const actions = ref([])

console.log('-ST-PortfolioNote')
emitter.on('open-PortfolioNote', (date,user_id,portfNote,accts,acOpts,stcks,acts) => openIt(date,user_id,portfNote,accts,acOpts,stcks,acts))

function addRow () {
  pnote.value.push({ user_id: userId, date: date, faccount: null, symbol: null, action: null, price: null, share: null, taccount: null, note: null })
}
function delRow () {
  pnote.value.pop()
}
function selectedOption (idx, optInd, selected) {
  // console.log(`-fn-idx=${idx} optInd=${optInd} selected=${selected.label} selected-account=${accounts.value.find(p => p.accnt_num === selected.label).accnt_num}`)
  const pIdx = pnote.value.length - 1
  console.log(`-fn-pIdx=${pIdx} optInd=${optInd} selected=${selected.label}`)
  if (optInd === 'faccnt') {
    pnote.value[pIdx].faccount = selected.label
    pnote.value[pIdx].tooltip = accounts.value.find(p => p.accnt_num === selected.label).accnt_nam
  } else if (optInd === 'taccnt') {
    pnote.value[pIdx].taccount = selected.label
    pnote.value[pIdx].txoltip = accounts.value.find(p => p.accnt_num === selected.label).accnt_nam
  } else if (optInd === 'stock') {
    pnote.value[pIdx].symbol = selected.label
  } else if (optInd === 'action') {
    pnote.value[pIdx].action = selected.label
  }
}
function showOptList (i, optInd) {
  console.log(`showOptList(i) i=${i} optId=${optInd}`, accntOpts.value)
  // emitter.emit('open-SelOptionsWithSearch', 'category', 'Account', i, optInd, accntOpts.value)
  emitter.emit('open-SelOptionsWithSearch', 'category', optInd, accntOpts.value, i)
}
function showStockList (i, optInd) {
  console.log('showStockList(i)', i, stocks.value)
  emitter.emit('open-SelOptionsWithSearch', 'Symbol', optInd, stocks.value, i)
}
function showActionList (i, optInd) {
  console.log('showActionList(i)', i, actions.value)
  emitter.emit('open-SelOptionsWithSearch', 'Action', optInd, actions.value, i)
}
function add () {
  pnote.value.forEach(p => {
    delete p.tooltip
    delete p.txoltip
  })
  console.log('add pnote', pnote.value)
  const path = process.env.API + '/watcher/addPNote'
  paxios(path, pnote.value)
  opened.value = false
}
function upd () {
  pnote.value.forEach(p => {
    delete p.tooltip
    delete p.txoltip
  })
  console.log('upd pnote', pnote.value)
  const path = process.env.API + '/watcher/updPNote'
  paxios(path, pnote.value)
  opened.value = false
}
function userConfirmed () {
  console.log('user confirmed to delete pnote', pnote.value)
  opened.value = false
  delPNote()
  // this.$emit('user-confirmed')
}
function delPNote () {
  let inData = { date: date }
  const path = process.env.API + '/watcher/delPNote'
  paxios(path, inData)
}
function del () {
  const tit = 'Delete Portfolio Notes'
  const msg = 'Delete Portfolio Notes on ' + date + '(' + today().chwk3() + ') Permanently?'
  emitter.emit('open-ConfirmDialog', tit, msg)
}
function openIt (dat, uId, pno, accts, aOpts, stcks, acts) {
  date = dat
  userId = uId
  pnote.value = pno
  accounts.value = accts
  accntOpts.value = aOpts
  stocks.value = stcks
  actions.value = acts
  console.log('-fn-open PortfolioNote', pnote.value, accounts.value, accntOpts.value, stocks.value, actions.value)
  pnote.value.forEach(p => {
    const tempf = accounts.value.filter(f => f.accnt_num === p.faccount)
    const tempt = accounts.value.filter(t => t.accnt_num === p.taccount)
    p.tooltip = tempf[0].accnt_nam
    if (tempt.length === 1) p.txoltip = tempt[0].accnt_nam
  })
  opened.value = true
}
</script>

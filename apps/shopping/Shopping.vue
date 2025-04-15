<template>
<div style="display:flex;justify-content:center">
<div class="q-px-xs fixed" style="width:800px;border:cyan solid 1px">
  <div class="row q-pl-xs q-pr-sm text-h6 text-amber">
    <div class="col-5">
      <q-option-group dense :options="storeOpt" type="checkbox" v-model="chkdStores" @update:model-value="showItemsInChkdStores()" :disable="storeOpt.length==1" />
    </div>
    <div class="col-3 text-right q-pr-xs">{{ date }} 采购单</div>
    <div class="col-4 text-right q-pr-xs">Total {{ compSpent }}: $ {{ compTotal }}</div>
  </div>
  <q-table class="sh-sticky-header-table" :rows="palist" :columns="columns"
    card-class="bg-teal-9 text-white" dense auto-width row-key="name"
    :pagination="{rowsPerPage:rwsPerPage}" hide-pagination :separator="separator">
    <template v-slot:header-cell-name="props">
      <q-th :props="props"> 共采购 {{palist.length}} 种商品 </q-th>
    </template>
    <template v-slot:body="p">
      <q-tr :props="p" v-if="isEdit">
        <q-td key="name"  :props="p" class="edt" :class="a(p.row)" @click="delPurchasedItemDialog(p.row)">{{ p.row.name }}</q-td>
        <q-td key="price" :props="p" class="edt" @click="openNumPad('price', p.row)">{{ p.row.price }}</q-td>
        <q-td key="units" :props="p" class="edt" @click="openNumPad('units', p.row)">{{ p.row.units }}</q-td>
        <q-td key="uni"   :props="p" class="edt" @click="openUniPad('uni',   p.row)">{{ p.row.uni }}</q-td>
        <q-td key="tax"   :props="p" class="edt" @click="openTaxPad('tax',   p.row)">{{ p.row.tax }}</q-td>
        <q-td key="disct" :props="p" class="edt" @click="openNumPad('disct', p.row)">{{ p.row.disct }}</q-td>
        <q-td key="costs" :props="p" class="edt" @click="openNumPad('costs', p.row)">{{ p.row.costs }}</q-td>
      </q-tr>
      <q-tr :props="p" v-else>
        <q-td key="name"  :props="p" :class="a(p.row)">{{ p.row.name }}</q-td>
        <q-td key="price" :props="p">{{ p.row.price }}</q-td>
        <q-td key="units" :props="p">{{ p.row.units }}</q-td>
        <q-td key="uni"   :props="p"><span class="q-mr-sm">{{ p.row.uni }}</span></q-td>
        <q-td key="tax"   :props="p">{{ p.row.tax }}</q-td>
        <q-td key="disct" :props="p">{{ p.row.disct }}</q-td>
        <q-td key="costs" :props="p">{{ p.row.costs }}</q-td>
      </q-tr>
    </template>
  </q-table>
  <div class="q-pt-xs" style="width:794px;margin-left:-2px">
    <q-btn-group spread glossy class="col-12">
      <q-btn dense no-wrap color="teal-8" label="select purchase dates" icon-right="history" @click="showSelOpt('Date')" />
      <q-btn :class="{'text-red':!isEdit, 'text-amber':isEdit}" icon="update" @click="isEdit=!isEdit" style="max-width:40px" />
      <q-btn dense no-wrap color="teal-9" label="select purchased itms" icon="history" @click="showSelOpt('Item')" />
      <q-btn dense no-wrap color="teal-8" label="select store" icon-right="store" @click="showStoreList()" />
    </q-btn-group>
    <PUCPad @upd-item="updItem" @restore-num="restoreNum" />
    <UniPad @upd-item="updItem" />
    <TaxPad @upd-item="updItem" />
    <SelOptPad @selected-opt="setSelectedOpt" />
    <StoreList @get-this-date-purchases="getList" @add-purchasing-item="addPurchasingItem" />
  </div>
  <q-option-group v-model="separator" inline class="text-h6 text-white" :options="[
      { label: 'Horizontal', value: 'horizontal' },
      { label: 'Vertical', value: 'vertical' },
      { label: 'Cell', value: 'cell' },
      { label: 'None', value: 'none' },
    ]"
  />
</div>
</div>
</template>
<script setup>
import { ref, computed } from 'vue'
import { libFunctions } from '../src/composables/libFunctions'
import { dayFunctions } from '../src/composables/dayFunctions'
import { axiosFunctions } from '../src/composables/axiosFunctions'
const { gaxios, paxios } = axiosFunctions()
const { buildApp, isDesk, isIM, palist, $q } = libFunctions()
const { yyyymmdd } = dayFunctions()
import emitter from 'tiny-emitter/instance'
import PUCPad from './PUCPad'
import UniPad from './UniPad'
import TaxPad from './TaxPad'
import SelOptPad from '../src/components/SelOptPad'
import StoreList from './StoreList'

console.log('-ST-Shopping')
buildApp('采购记录', 'Shopping')
const rwsPerPage = isIM ? 8 : 20
emitter.emit('items-per-page', rwsPerPage)

//== data sections
const separator = ref('cell')
const isEdit = ref(true)
const selOptTitle = ref('Item')
const chkdStores = ref([])
const storeOpt = ref([])
const spent = ref('COST')
const selectedItemId = ref(null)
const selectedItem = ref(null)
const searchTitle = ref(null)
const date = ref(null)
const today = (new Date).yyyymmdd()
const dats = ref([])
// const pItems = ref([])
const tag = ref(null)
const purchasedItems = ref([])
const searchQuery = ref('')
// const visibleColumns = ref(['price', 'units', 'uni', 'tax', 'costs'],
const visibleColumns = ['price', 'costs']
const columns = [
  {
    required: true, label: '品名', align: 'left', name: 'name', field: 'name', sortable: true,
    headerStyle: 'font-size:21px;width:10px'
  },
  {
    label: '单价', align: 'right', name: 'price', field: 'price', sortable: true, sort: (a, b) => parseFloat(a) - parseFloat(b),
    // style: 'font-size: 19px', headerStyle: 'font-size:20px'
  },
  {
    label: '数量', align: 'right', name: 'units', field: 'units', // sortable: true, sort: (a, b) => parseFloat(a) - parseFloat(b),
    // style: 'font-size: 19px', headerStyle: 'font-size:20px'
  },
  {
    label: '度量', align: 'right', name: 'uni', field: 'uni', // sortable: true,
    // style: 'font-size: 19px', headerStyle: 'font-size:20px'
  },
  {
    label: '课税', align: 'right', name: 'tax', field: 'tax', //sortable: true,
    // style: 'font-size: 19px', headerStyle: 'font-size:20px'
  },
  {
    label: '减价', align: 'right', name: 'disct', field: 'disct', // sortable: true, sort: (a, b) => parseFloat(a) - parseFloat(b),
    // style: 'font-size: 19px', headerStyle: 'font-size:20px'
  },
  {
    required: true, label: '花费', align: 'right', name: 'costs', field: 'costs', sortable: true, sort: (a, b) => parseFloat(a) - parseFloat(b),
    // style: 'font-size: 19px', headerStyle: 'font-size:20px'
  }
]
//== main ==
getList()
emitter.on('search', (searchQuery) => { searchQuery.value = searchQuery })
//== computed sections
emitter.on('shopping-delPurchasedItem', () => {
  getList()
  emitter.emit('put-item-back-to-candidateItemsInClass', candidateItem)
})
emitter.on('shopping-getThisDatePurchases', (da) => setList(da))
// emitter.on('shopping-addPurchasedItem', (da) => addPurchasedItem(da.addedItem))
// emitter.on('shopping-addPurchasedItem', () => getList())
const compTotal = computed({
  get: () => {
    let total = 0.00
    let disct = 0.00
    palist.value.forEach((p) => {
      total += p.costs > 0 && p.status === 'A' ? parseFloat(p.costs - p.disct) * (1 +  p.tax / 100) : 0
    })
    return total.toFixed(2)
  }
})
const compDate = computed(() => { return date.value })
const compSpent = computed(() =>{
  if (dats.value.length > 0 && dats.value[0].payee_id > 0) {
    return 'SPENT'
  } else {
    return 'COST'
  }
})
const searchItems = computed(() => {
  var filterKey = searchQuery.value && searchQuery.value.toLowerCase()
  var data = purchasedItems.value
  if (filterKey) {
    var words = filterKey.split(' ')
    words.forEach(word => {
      data = data.filter(row => {
        return Object.keys(row).filter(key => { return !['id', 'itemId', 'class_id', 'id'].includes(key) }).some(key => {
          return String(row[key]).toLowerCase().indexOf(word) >= 0
        })
      })
    })
  }
  return data
})

//== function sections
function addPurchasingItem(item) {
  console.log(`-fn-addPurchasingItem`, item)
  dats.value.push(item)
  date.value = today
  dats.value = dats.value.filter(x => x.date == today)
  emitter.emit('dats', dats.value)
}
function showItemsInChkdStores () {
  // console.log(`-fn-showItemsInChkdStores storeOpt.length=${storeOpt.value.length} chkStores.length=${chkdStores.value.length}`, palist.value)
  if (storeOpt.value.length === 1 && chkdStores.value.length === 0) return
  // pItems.value = purchasedItems.value
  var pItems = dats.value.filter(p => { return chkdStores.value.includes(p.payee_id) })
  // console.log(`-fn-showItemsInChkdStores storeOpt.length=${storeOpt.value.length} chkStores.length=${chkdStores.value.length}`, palist.value)
  emitter.emit('dats', pItems)
}
function showStoreList () {
  emitter.emit('open-StoreList')
}
function getStyle (col) {
  if (col === 'name') {
    return isIM ? 'max-width:156px' : ''
  } else {
    return 'text-align:right'
  }
}
function getClass (col, row) {
  if (col === 'name') {
    return a(row) + ' ellipsis' 
  }
}
function test_sortZhArray () { // testing chinese char sorting
  const zharray = ['如何才', '能避免', '安排在', '食用木', '木耳中', '五食品', '安全干', '营养信', '息交流', '中心副', '主任钟', '凯表示', '城区其']
  const zs = zharray.sort((a, b) => a.localeCompare(b, 'zh', { ignorePunctuation: true }))
  console.log(zs)
}
function a (row) {
  console.log('-CK-storeOpt', storeOpt.value, row.payee_id)
  const matched = storeOpt.value.filter(p => { return p.value === row.payee_id })[0]
  return matched == null ? 'text-cyan-1' : 'text-' + matched.color
}
function showSelOpt (tit) {
  emitter.emit('open-SelOptPad', tit)
}
function setSelectedOpt (da, itemName = '') {
  // console.log('=XX= selected item', da)
  // var fromChild = true
  if (da > 0) { // lookup purchased item
    selectedItemId.value = da
    selectedItem.value = '「' + itemName.substring(5) + '」'
    searchTitle.value = '买的' + selectedItem.value
    getPurchasedDate() // for the item
    console.log('-fn-selectedItemId', selectedItemId.value, searchTitle.value)
  } else { // select shopping date
    selectedItemId.value = null
    selectedItem.value = null
    date.value = da
    searchTitle.value = date.value + ' 买的物品'
    // console.log('--x-- searchTitle', earchTitle.value)
    getList()
  }
}

function setList (da) {
  // console.log('-fn-setList', da)
  var items = da.lst
  items.forEach(p => { if (p.tax === '0.000') p.tax = null })
  date.value = da.theDate
  storeOpt.value = da.opts
  chkdStores.value = da.stores
  const colors = ['pink-2', 'yellow', 'cyan-2', 'brown-3', 'red']
  let i = 0
  storeOpt.value.forEach(p => { p.color = colors[i++ % 5] })
  console.log('-CK-', storeOpt.value)
  let tit = date.value.substring(2) + ' 买的所有物品'
  if (items.length > 0 && items[0].payee_id === null) tit = date.value.substring(2) + ' 要买的所有物品'
  if (isIM) tit = date.value.substring(2) + ' 买的物品'
  if (selectedItemId.value > 0) {
    const tms1 = items.filter(p => { return p.item_id === selectedItemId.value })
    const tms2 = items.filter(p => { return p.item_id !== selectedItemId.value })
      // .sort((a, b) => a.name.localeCompare(b.name, 'zh', { ignorePunctuation: true }))
    items = tms1.concat(tms2)
    tit = date.value.substring(2) + ' 买的' + selectedItem.value + '及其它'
  }
  // dats.value = items.sort((a, b) => (a.payee + a.name).localeCompare(b.payee+b.name, 'zh', { ignorePunctuation: true })).sort((a, b) => a.payee_id - b.payee_id)
  dats.value = items
  emitter.emit('dats', dats.value)
  purchasedItems.value = palist.value
  // console.table(palist.value.map(p => p.payee + ' ~ ' + p.name))
  if (items.length > 0) {
    $q.notify({
      actions: [{ icon: 'cancel', color: 'cyan' }],
      textColor: 'cyan-1',
      timeout: 500,
      message: '<div class="q-pl-md text-h6">' + tit + '</div>',
      position: 'bottom',
      color: 'brown-9',
      html: true
    })
  }
}
function getList () {
  const path = process.env.API + '/shopping/getThisDatePurchases/' + date.value
  gaxios(path)
}
emitter.on('shopping-getPurchasedDate', (da) => {
  if (da === 'neverBoughtThisItem') {
    const tit = '你没买过' + selectedItem.value
    $q.dialog({ title: tit, autoClose: true, ok: { label: 'OK' } })
  } else {
    date.value = da
    // console.log('-ab-selected Item:', selectedItem.value, selectedItemId.value)
    getList()
  }
})
function getPurchasedDate () {
  const path = process.env.API + '/shopping/getPurchasedDate/' + selectedItemId.value
  gaxios(path)
}
function updDate (d) {
  date.value = d
  getList()
}
function restoreNum (oItem) {
  console.log('restore-num', oItem.price, oItem.units, oItem.costs, oItem)
  let idx = -1
  purchasedItems.value.forEach((p, i) => { if (p.id === oItem.id) idx = i })
  purchasedItems.value.splice(idx, 1, oItem)
  console.log('restore-num', purchasedItems.value)
}
function updItem (item) {
  // console.log('upd-item', item)
  updPurchasedItem(item)
  const rowIdx = -1
}
function delPurchasedItemDialog (item) {
  // console.log('-dg-delPurchasedItemDialog', item)
  $q.dialog({
    html: true,
    color: 'primary',
    position: 'right',
    title: '<center>今天不买「 <b>' + item.name + '</b> 」了?</center>',
    cancel: { label: '唔，我再想想' },
    ok: { label: '不买了' }
  }).onOk(() => {
    // console.log('OK Clicked')
    delPurchasedItem(item)
    // drawer = false
  }).onCancel(() => {
    console.log('Cancelled')
    // drawer = false
  })
}
function updPurchasedItem (item) {
  // console.log('-dg-updPurchasesItem', item)
  const path = process.env.API + '/shopping/addPurchasedItem'
  paxios(path, item)
}
var candidateItem = null
function delPurchasedItem (item) {
  if (item.id == null) {
    $q.dialog({
      title:'item.id is null'
    })
  }
  item.status = 'D'
  console.log('-dg-delPurchasedItem', item)
  const path = process.env.API + '/shopping/delPurchasedItem/' + item.id
  gaxios(path)
  candidateItem = item
}
function openNumPad (tg, itm) {
  // console.log('openNumPad', showSearch.value, date.value, today, tag, item)
  tag.value = tg
  emitter.emit('open-PUCPad', tg, itm)
}
function openUniPad (tg, itm) {
  tag.value = tg
  emitter.emit('open-UniPad', itm)
}
function openTaxPad (tg, itm) {
  tag.value = tg
  emitter.emit('open-TaxPad', itm)
}
</script>

<template>
<q-dialog v-model="opened" transition-show="scale" transition-hide="slide-right" maximized>
<div class="q-px-xs" style="display:grid;place-items:center;height:100vh">
  <q-layout view="hHh Lpr fff" container style="background:RGB(28, 68, 78);width:780px">
    <q-header>
      <q-toolbar class="glossy bg-indigo-10 row text-no-wrap">
        <q-btn flat @click="drawerL = !drawerL" round><q-icon name="list" color="amber" size="lg" /></q-btn>
        <div style="margin:auto" class="row text-h6 text-no-wrap">
          <q-btn size="17px" outline round color="amber-9" icon="chevron_left" v-close-popup @click="emit('get-this-date-purchases')" />
          <q-btn style="width:160px" flat @click="openItemPad" no-caps :label="isDesk ? 'Add' : ''" >
            <q-icon name="fiber_new" size="lg" :color="compBtnColor" />{{ compCI }}
          </q-btn>
          <q-btn flat @click="showCoupon=!showCoupon" size="lg" icon="store" color="amber" no-caps>{{ className }}</q-btn>
        </div>
        <q-input dark borderless v-model="searchQuery" input-class="text-right text-h6" class="fixed-right">
          <template v-slot:append>
            <q-icon v-if="searchQuery === ''" name="pageview" size="lg" class="q-pr-xl" />
            <q-icon v-else name="clear" size="md" class="cursor-pointer q-pr-xl" @click="searchQuery=''" />
          </template>
        </q-input>
        <q-btn @click="drawerR = !drawerR" round><q-icon name="store" size="lg" color="cyan" /></q-btn>
      </q-toolbar>
    </q-header>
    <q-footer v-if="isDesk" v-model="footerState">
      <q-toolbar>
        <q-btn icon="shopping_cart" color="green" round flat />
        <div style="margin:auto" class="text-h6"> Shopping List </div>
        <q-btn round glossy icon="close" color="orange" v-close-popup size="md" @click="emit('get-this-date-purchases')" />
      </q-toolbar>
    </q-footer>
    <q-drawer side="left" v-model="drawerL" :width="500" :breakpoint="300" lass="bg-cyan-10 q-pa-xs" behavior=mobile>
      <q-input dark borderless v-model="searchItems" class="text-h5" input-class="text-right text-h6" bg-color="teal-10" label="Search Items">
        <template v-slot:append>
          <q-icon v-if="searchItems === ''" name="pageview" size="lg" class="q-pr-xl" />
          <q-icon v-else name="clear" size="md" class="cursor-pointer q-pr-xl" @click="searchItems=''" />
        </template>
      </q-input>
      <div v-for="p in compSearchItems" :key="p.id" class="text-no-wrap bg-cyan-10 q-pa-xs q-pl-xs">
        <!-- <q-btn @click="delShoppingItem(p)" flat icon="cancel" color="yellow-9" dark dense size="lg">{{ p.name }} ~ {{ p.class }}</q-btn> -->
        <q-btn @click="removeItem(p)" flat icon="cancel" color="yellow-9" dark dense size="lg">{{ p.name }} ~ {{ p.class }}</q-btn>
      </div>
    </q-drawer>
    <q-drawer side="right" v-model="drawerR" :width="245" :breakpoint="300" content-class="text-h6 text-amber-2 text-no-wrap q-pa-xs" behavior="mobile">
      <div v-for="c in itemClasses" :key="c.value" class="bg-cyan-10 q-pa-xs q-pl-md text-white">
        <q-radio :label="c.label" v-model="classId" :val="c.value" color="red" dark dense @click="showItems4Class(c)" class="text-h6" />
      </div>
    </q-drawer>
    <div v-show="showCoupon" class="outer q-px-xl bg-teal-10" style="margin-top:60px" />
    <div>
      <div v-if="!showCoupon && !addNewClassOrItem && compCandidateItems.length===0 && itemList.length===0" class="q-pa-xl text-white text-h5">
        <span>No items in this store click on "Add New Items" to add new item</span>
      </div>
      <div v-else style="margin-top: 50px">
        <div v-if="showCoupon">
          <div v-for="f in attached" :key=f.x><span colspan="4" class="text-left q-pl-xl q-pb-sm" v-html="getShoppingURL(f)"></span></div>
        </div>
        <div v-else style="margin:0 0 0 120px" :style="getContHeight">
          <table class="q-pl-xl q-pb-xl">
            <!-- <q-tr class="text-h6 text-purple-2" style="position:fixed;z-index:10"> -->
            <q-tr class="text-h6 text-purple-2">
              <th class="text-right">品名</th>
              <th class="q-pl-xl text-right" style="width:100px">单价</th>
              <th class="q-pl-xl text-right" style="width:100px">数量</th>
              <th class="q-pl-xl text-right" style="width:100px">花费</th>
            </q-tr>
            <q-tr v-show="compCandidateItems.length>0"><td></td><td colspan="3"><hr style="margin:-2px 0 0 -44px"></td></q-tr>
            <q-tr v-for="m in compCandidateItems" :key=m class="text-white text-h6 text-right">
              <td class="cursor-pointer text-amber" @click="addPurchasedItem(m, true) ">
                <div v-if="isDesk">{{ m.name }}</div><div v-else class="ell">{{ m.name }}</div>
              </td>
              <td>{{ m.price }}</td>
              <td>{{ m.units }}</td>
              <td>{{ m.costs }}</td>
            </q-tr>
          </table>
        </div>
      </div>
    </div>
  </q-layout>
</div>
</q-dialog>
<ItemPad @add-item="addNewItem" @add-class="addNewClass" />
<ConfirmDialog @user-confirmed="removeItemFromDB"/>
</template>
<script setup>
import { ref, computed } from 'vue'
import emitter from 'tiny-emitter/instance'
import ItemPad from './ItemPad'
import ConfirmDialog from '../src/components/ConfirmDialog'

import { axiosFunctions } from '../src/composables/axiosFunctions'
import { libFunctions } from '../src/composables/libFunctions'
import { dayFunctions } from '../src/composables/dayFunctions'
const { gaxios, paxios } = axiosFunctions()
const { isIM, isDesk } = libFunctions()
const { yyyymmdd, today } = dayFunctions()

//== data sections
const className = ref(null)
const showCoupon = ref(false)
const closeCan = ref(false)
const attached = ref([])
const candidateItemsInClass = ref([])
const addNewClassOrItem = ref(false)
const classId = ref(0)
const drawerL = ref(false)
const drawerR = ref(false)
const footerState = ref(true)
const opened = ref(false)
const searchQuery = ref('')
const searchItems = ref('')
const selClasses = ref([])
const itemClasses = ref([])
const newCIname = ref(null)
const itemList = ref([])

const emit = defineEmits(['get-this-date-purchases'])

console.info('-ST-StoreList')
emitter.on('open-StoreList', () => openIt())
emitter.on('put-item-back-to-candidateItemsInClass', (itm) => { candidateItemsInClass.value.unshift(itm) })

//== emitter.on sections
emitter.on('shopping-getShoppingList', (da) => {
  itemClasses.value = da.itemClasses
  itemClasses.value.push({ value: 0, label: 'NewClass' })
  attached.value = da.attached
  itemList.value = da.itemList
  // console.log('-ab-getStoreList:', itemList.value, itemClasses.value)
  // console.table(itemClasses.value)
  // console.table(itemList.value.map(p => p.name + ' ~ ' + p.costs))
  // console.log('-ab-getStoreList:', itemList.value, itemClasses.value)
  // showItems4Class(itemClasses.value.filter(p => p.value === itemList.value[0].class_id)[0]) // last shopping at this store
  showItems4Class(itemClasses.value.find(p => p.value === itemList.value[0].class_id)) // last shopping at this store
})
// emitter.on('shopping-delShoppingItem', (da) => {
//   itemList.value = da.itemList
//   // if (this.inData.class_id === classId.value) candidateItemsInClass.value.push(this.inData)
// })
emitter.on('shopping.addShoppingItem', (da) => { itemList.value = da.itemList })
// emitter.on('shopping.addPurchasedItem', (da) => { purchasedItems.value = da.itemList })

//== function sections
function addNewItem (newItem) {
  console.log(`-fn-addNewItem`, newItem)
  candidateItemsInClass.value.unshift(newItem)
}
function addNewClass(newClass) {
  console.log(`-fn-addNewItem value=${newClass.value} label=${newClass.label}`, newClass)
}
function getContHeight () {
  let cpx = Math.max(itemList.value.length + candidateItemsInClass.value.length, 10) * 40
  let cheight = 'margin-top:40px;min-height:' + cpx + 'px'
  if (showCoupon.value) {
    cpx = Object.values(attached.value).length * 44
    cheight = 'margin-top:240px;min-height:' + cpx + 'px'
  }
  return cheight
}
function openItemPad () {
  console.log(`-fn-openItemPad classId=${classId.value} className=${className.value}`)
  emitter.emit('open-ItemPad', classId.value, className.value)
}
function getShoppingURL (f) {
  const regx1 = /_|-/g
  const regx2 = /.jpg|.pdf|.png|.jpeg/ig
  const name = f.replace(regx1, ' ').replace(regx2, '')
  return '<a href="/docs/shopping/' + f + '" target="_blank" class="text-white text-h5 text-no-wrap"><div class="u-ell">' + name + '</div></a>'
}
function addToShoppingList (m) {
  // console.log('-fn-addToShoppingList')
  itemList.value.forEach(p => { if (p.item_id === m.item_id) p.status = 'S' })
  const items = candidateItemsInClass.value.filter(p => { return p.item_id !== m.item_id })
  candidateItemsInClass.value = items
  m.id = null
  m.status = 'S'
  m.payee_id = null
  m.date = (new Date()).yyyymmdd()
  const path = process.env.API + '/shopping/addShoppingItem'
  paxios(path, m)
}
var deletedItem = null
function removeItem (m) {
  deletedItem = m
  console.log(`-fn-removeItem item=${m.name} classId=${m.class_id} class=${m.class} pdate=${m.date}`)
  const tit = `Remove Item Permanently itemId=${m.item_id}`
  const msg = `Delete item: 『${m.name}』 purchased on 『${m.date}』 at 『${m.class}』 WILL implement when needed`
  emitter.emit('open-ConfirmDialog', tit, msg)
}
// emitter.on('user-confirmed', () => removeItemFromDB())
function removeItemFromDB () {
  console.log(`-fn-removeItemFromDB WILL IMPLEMENT WHEN NEEDED`, deletedItem)
}
function delShoppingItem (m) {
  console.log('-fn-delShoppingItem', m.name, m, m.class_id, parseInt(m.class_id) === parseInt(classId.value))
  return
  itemList.value.forEach(p => { if (p.item_id === m.item_id) p.status = 'A' })
  const path = process.env.API + '/shopping/delShoppingItem'
  console.log(`-CK- del item from shoppingList m.name=${m.name}`)
  paxios(path, m)
  drawerL.value = false
  if (m.class_id === classId.value) candidateItemsInClass.value = candidateItemsInClass.value.filter(p => p.item_id !== m.item_id)
  itemList.value = itemList.value.filter(p => p.item_id !== m.item_id)
}
function XXXaddPurchasedItem (item, fromCan=false) {
  console.log('-fn-addPurchasedItem', item.name, item)
  if (fromCan) {
    const items = candidateItemsInClass.value.filter(p => { return p.item_id !== item.item_id })
    candidateItemsInClass.value = items
    item.id = null
  }
  item.date = (new Date()).yyyymmdd()
  item.payee_id = null
  item.status = 'A'
  if (item.costs === null) item.costs = 0.01
  const path = process.env.API + '/shopping/addPurchasedItem'
  // console.log('-fn- adding  purchased item', item.name, args.inData)
  paxios(path, item)
  emit('add-item')
}
function showItems4Class (clsOpt) {
  console.log(`-fn-showItems4Class classId=${clsOpt.value} className=${clsOpt.label}`)
  classId.value = clsOpt.value
  className.value = clsOpt.label
  console.log(`-CK- selected Class classId=${classId.value} className=${className.value}`)
  const cId = classId.value
  const items = itemList.value.filter(p => {
    return p.class_id === cId && (p.date < today() || p.costs === null)
  })
  items.sort((a, b) => a.name.localeCompare(b.name, 'zh'))
  candidateItemsInClass.value = items
  drawerR.value = false
}
function openIt () {
  opened.value = true
  if (itemList.value.length > 0) return
  const path = process.env.API + '/shopping/getShoppingList'
  gaxios(path)
}
function openItemLookupDialog () {
  this.$refs.itemLookupDialog.openIt()
}
function setSelClasses (classId) {
  selClasses.value = []
  selClasses.value.push(classId)
}

//== computed sections
const compBtnColor = computed(() => { return classId.value > 0 ? 'yellow' : 'yellow-9' })
const compCI = computed(() => {
  if (isDesk) {
    return classId.value > 0 ? 'Items' : 'Class'
  } else {
    return ''
  }
})
const cbLabel = computed(() => { return addNewClassOrItem.value ? null : 'click to show add new class or item' })
const compTotal = computed({
  get: () => {
    let total = 0.00
    itemList.value.forEach((item) => { total += item.costs > 0 && item.status === 'A' ? parseFloat(item.costs) : 0 })
    return total.toFixed(2)
  }
})
const compCandidateItems = computed(() => {
  // console.log('searchQuery =', searchQuery.value, candidateItemsInClass.value)
  if (searchQuery.value !== '') {
    var filterKey = searchQuery.value && searchQuery.value.toLowerCase()
    let data = candidateItemsInClass.value
    if (filterKey) {
      const words = filterKey.split(' ')
      words.forEach(word => {
        data = data.filter(row => {
          return Object.keys(row).filter(key => { return !['id', 'item_id', 'class_id', 'id'].includes(key) }).some(key => {
            return String(row[key]).toLowerCase().indexOf(word) >= 0
          })
        })
      })
    }
    return data
  }
  return candidateItemsInClass.value
})
const compTotalCost = computed(() => {
  let tcost = 0.0
  itemList.value.forEach(p => { if (p.costs > 0) tcost += parseFloat(p.costs) })
  return tcost.toFixed(2)
})
const compSearchItems = computed(() => {
  var filterKey = searchItems.value && searchItems.value.toLowerCase()
  var data = itemList.value
  if (filterKey) {
    var words = filterKey.split(' ')
    words.forEach(word => {
      data = data.filter(row => {
        return Object.keys(row).filter(key => { return !['id', 'item_id', 'class_id', 'id'].includes(key) }).some(key => {
          return String(row[key]).toLowerCase().indexOf(word) >= 0
        })
      })
    })
  }
  if (data.length === 1) {
    setSelClasses(data[0].class_id)
  }
  return data
})
</script>

<template>
<div class="q-pa-sm">
  <q-dialog v-model="opened" transition-show="slide-left" transition-hide="slide-right">
    <q-layout container style="height:500px;max-width:380px">
        <q-card class="bg-teal-10 text-h6" dark>
          <q-card-actions v-if="opts[title].length>10" class="no-wrap fixed-top bg-primary z-top">
            <q-btn round color="amber-9" glossy icon="chevron_left" v-close-popup @click="searchQuery=''" />
            <q-input dense dark borderless v-model="searchQuery" :placeholder="'    Look up ' + title" class="text-h6 q-pl-sm" @keyon="search()" >
              <template v-slot:append>
                <q-icon v-if="searchQuery===''" name="pageview" size="50px" />
                <q-icon v-else name="clear" class="cursor-pointer q-pl-sm" @click="searchQuery=''" size="md" />
              </template>
            </q-input>
          </q-card-actions>
          <q-card-actions v-else>
            <q-btn round glossy icon="close" v-close-popup color="amber-9" />
            <q-btn :label="title + ' list'" flat class="text-h5 event-no-pointer" disable />
          </q-card-actions>
          <q-card-section style="margin:35px 0 0 25px">
            <div v-for="o in compOpts" :key=o>
              <q-radio v-model="selItem" :val="o.value" :label="o.label" @click="selectedItem(o.label)" />
            </div>
          </q-card-section>
        </q-card>
    </q-layout>        
  </q-dialog>
</div>
</template>
<script setup>
import emitter from 'tiny-emitter/instance'
import { ref, computed } from 'vue'
import { axiosFunctions } from '../../src/composables/axiosFunctions'
const { gaxios } = axiosFunctions()
const showSearch = ref(false)
const footerState = ref(true)
const searchQuery = ref('')
const selItem = ref(null)
const opened = ref(false)
const title = ref(null)
const opts = ref({ Item:[], Date:[] })

console.log('-ST-SelOptPad')
emitter.on('open-SelOptPad', (tit) => openIt(tit) )

const compOpts = computed(() =>{
  var filterKey = searchQuery.value && searchQuery.value.toLowerCase()
  var data = opts.value[title.value]
  if (filterKey) {
    var words = filterKey.split(' ')
    words.forEach(word => {
      data = data.filter(row => {
        return Object.keys(row).filter(key => { return !['value'].includes(key) }).some(key => {
          return String(row[key]).toLowerCase().indexOf(word) >= 0
        })
      })
    })
  }
  return data
})

function selectedItem (itemName) {
  searchQuery.value = ''
  console.log('user selected shoppingDate/item:', selItem.value, itemName)
  emit('selected-opt', selItem.value, itemName)
  opened.value = false
}
emitter.on('shopping-getAllItems', (da) => { opts.value.Item = da.lst; opened.value = true })
emitter.on('shopping-getShoppingDates', (da) => { opts.value.Date = da.lst; opened.value = true })
const emit = defineEmits(['selected-opt'])

function getOpts() {
  var path = null
  if (title.value === 'Item') {
    path = process.env.API + '/shopping/getAllItems'
  } else if (title.value === 'Date') {
    path = process.env.API + '/shopping/getShoppingDates'
  } 
  gaxios(path)
}
function openIt (tit) {
  title.value = tit
  if (opts.value[tit].length > 0) return opened.value = true
  getOpts()
}
</script>

<template>
<div class="bg-teal-10">
  <q-dialog  v-model="opened" transition-show="slide-right" transition-hide="flip-down" fade>
    <div class="absolute" style="top:9px;left:194px;max-width:100vw; border:1px solid white">
      <div class="bg-cyan-10 text-h6 text-center q-pa-xs text-cyan-2">{{ tag }}</div>
      <div class="row justify-between bg-cyan-10">
        <q-btn flat color="amber" icon="chevron_left" class="bg-cyan-10" v-close-popup />
        <q-input v-model="searchQuery" filled dark dense type="search" class="bg-cyan-10 text-h6 q-pa-xs" @keyup="search()">
          <template v-slot:append>
            <q-icon v-if="searchQuery===''" name="search" />
            <q-icon v-else name="clear" class="cursor-pointer" @click.stop.prevent="searchQuery='';search()" />
          </template>
        </q-input>
      </div>
      <q-card style="padding-left:0px">
        <q-card-section class="bg-green-10 text-whit q-pa-xs">
          <tr v-for="d in dic" :key=d.id class="text-body1"
              :class="{
                'bg-teal-9':d.security=='MSFT',
                'bg-cyan-9':d.security=='CSCO',
                'bg-teal-8':d.security=='FXAIX',
                'bg-cyan-8':d.security=='FSKAX',
                'bg-purple':d.security=='FFTWX',
                'bg-cyan-7':d.security=='T',
                'bg-lime-7':d.security=='VMW',
                'bg-lime-7':d.security=='WBD',
              }">
            <td style="width:80px" class="q-px-xs q-py-xs text-no-wrap cursor-pointer" @click="searchQuery='';searchQuery=d.account_num;search()">{{ d.account_num }}</td>
            <td style="width:90px" class="q-px-xs q-py-xs text-no-wrap">{{ d.account_name }}</td>
            <td style="width:55px" class="q-px-xs q-py-xs text-right">{{ d.DorI }}</td>
            <td style="width:65px" class="q-px-xs q-py-xs text-no-wrap cursor-pointer" @click="searchQuery='';searchQuery=d.security;search()">{{ d.security }}</td>
            <td style="width:30px" class="q-px-xs q-py-xs text-no-wrap" v-if="d.month>9">{{ d.month }}月</td>
            <td style="width:30px" class="q-px-xs q-py-xs text-no-wrap" v-else>{{ '0'+d.month }}月</td>
            <td style="width:80px" class="q-px-xs q-py-xs text-right">{{ fmtcy(d.amount) }}</td>
          </tr>
          <tr><td colspan="6" class="text-right q-px-xs text-h6 text-bold text-cyan-2">{{ fmtcy(totals) }}</td></tr>
        </q-card-section>
      </q-card>
    </div>
  </q-dialog>
</div>
</template>
<script setup>
import { ref, computed } from 'vue'
import { libFunctions } from '../src/composables/libFunctions'
const { fmtcy } = libFunctions()
const dic = ref([])
const opened = ref(false)
const searchQuery = ref('')
const allInSecs = ref([])
const totals = ref(0)
// const searchedItem = ref({ dvd:'DIVIDEND'})
const tag = ref(null)

defineExpose({ openIt })
console.log('-ST-DICDetailsPad')

function search () {
  console.log(`searchQuery=${searchQuery.value}`)
  dic.value = allSecs.value.sort((a, b) => { return a.security > b.security ? 1 : -1 })
  totals.value = 0
  allSecs.value.forEach(p => totals.value += isNaN(p.amount) ? 0 : parseFloat(p.amount))
}
function openIt (allx, tg) {
  console.log(`-fn-openIt tag=${tg}`, allx)
  allInSecs.value = allx
  tag.value = tg
  // totals.value = allSecs.value.reduce((a, b) => parseFloat(a.amount) + parseFloat(b.amount), 0)
  totals.value = 0
  allSecs.value.forEach(p => totals.value += isNaN(p.amount) ? 0 : parseFloat(p.amount))
  dic.value = allSecs.value.sort((a, b) => { return a.security > b.security ? 1 : -1 })
  opened.value = true
}
const allSecs = computed(() => {
  var filterKey = searchQuery.value.length > 0 && searchQuery.value.toLowerCase()
  var data = allInSecs.value
  if (filterKey.length > 0) {
    var words = filterKey.split(' ')
    words.forEach(word => {
      data = data.filter(row => {
        return Object.keys(row).filter(key => { return ![
          'id', 'catsId', 'subcId', 'payeId', 'paymId', 'note', 'link', 'post_date', 'created_at', 'updated_at', 'deleted_at'
          ].includes(key) }).some(key => {
            return String(row[key]).toLowerCase().indexOf(word) >= 0
        })
      })
    })
  }
  console.log('-fn-allSecs', data)
  // emitter.emit('num-items', data.length)
  return data
})
</script>

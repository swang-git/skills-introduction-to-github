<template>
<q-card square class="bg-cyan-3 text-h6 inset-shadow-down" style="height:50px">
  <q-card-section style="margin-top:-15px">
    Golf Logs
    <span class="float-right" style="margin-top:-5px"><q-toggle v-model="nolocal" color="pink" /></span>
  </q-card-section>
</q-card>
<div>
  <div class="q-pa-md text-body1 text-white bg-teal-10" style="border:cyan solid 1px">
    <q-list v-for="(ip) in nolocal ? Object.keys(daPack).filter(k => k.substring(0,9) !== '192.168.1') : Object.keys(daPack)" :key="ip">
      <q-expansion-item expand-separator icon="list" dark :label="ip" :caption="daPack[ip][0].datetime + ' (' + daPack[ip].length + ' lines)'" group dense>
        <!-- <q-card-section style="border:cyan solid 2px" class="bg-teal-10"> -->
          <!-- <div v-for="(d, i) in daPack[ip].filter(p => !(p.page_name.indexOf('GScore')>=0 && p.params == 'from __construct'))" :key="d" class="q-my-sm row" :class="{'bg-teal-9':i%2==0, 'bg-teal-8':i%2==1}"> -->
          <div v-for="(d, i) in daPack[ip].filter(p => !(p.page_name.indexOf('GScore')>=0 && p.params == 'from __construct'))" :key="d" class="q-my-sm row">
            <div class="text-no-wrap q-pl-xs" v-if="isDesk">{{ (i+1).toFixed(0).padStart(3, '0') }}</div>
            <div class="text-no-wrap q-pl-sm">{{ d.datetime.substring(5) }}</div>
            <div class="text-no-wrap q-pl-sm ellipsis" :style="(d.params=='from __construct' || d.params==null) ? {'max-width':'350px'} : {'max-width':'200px'}">{{ d.page_name.replace('/golf/', '') }}</div>
            <div class="text-no-wrap q-pl-sm" v-if="d.params!=null">{{ d.params.replace('from __construct', '') }}</div>
            <div class="text-no-wrap q-pl-sm" v-else></div>
          </div>
        <!-- </q-card-section> -->
      </q-expansion-item>
    </q-list>
  </div>
</div>
</template>
<script setup>
import emitter from 'tiny-emitter/instance'
import { libFunctions } from 'src/composables/libFunctions'
const { store } = libFunctions()
// const dats = []
const daPack = []
const nolocal = false

console.log(`-ST-ShowLogPage`)
store.pageTitle = 'Golf Log List'
store.page = 'LoadLogPage'
// emitter.on('search', (x) => { searchQuery=x; console.log(`-em-searchQuery=${searchQuery}`) })
emitter.on('golf-loadLogPage', (x) => setData(x))
getLogPage()

// const compLogList = computed(() => {
//   if (this.nolocal) return this.dalist.filter(p => !p.ip_address.includes('192.168.'))
//   return this.dalist
// })

function getLogPage () {
  const path = process.env.API + '/golf/loadLogPage'
  this.gaxios(path)
}
function setData (da) {
  this.dats = da.lst
  console.log(`-fn-setData dats length=${this.dats.length}`)
  // if (this.isIM)
  this.condenseData()
}
// function condenseData () {
//   const x = this.dalist.map(p => p.ip_address)
//   const xx = [...new Set(x)]
//   this.daPack = []
//   xx.forEach(p => {
//     this.daPack[p] = this.dalist.filter(d => d.ip_address == p)
//   })
//   // console.log(`-ck-all ips`, xx, this.daPack)
// }
</script>

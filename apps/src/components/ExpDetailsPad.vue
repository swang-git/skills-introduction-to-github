<template>
  <q-dialog v-model="opened" :transition-show="getAni()">
    <div class="bg-teal-10" style="width:630px;margin:-480px 0 0 -14px">
      <q-card square flat class="bg-teal-10 fixed scroll" dark style="width:inherit;max-height:200px">
        <q-card-actions align="between" class="bg-cyan-10 row fixed" style="margin:-52px 0 0 0;width:inherit">
          <q-btn glossy round icon="chevron_left" color="indigo" v-close-popup />
          <div class="q-px-sm text-h6 text-white ellipsis" style="max-width:400px">{{ tit }}</div>
          <div v-if="exlist.length>4" class="q-px-sm text-h6 text-white">({{ exlist.length }})</div>
          <div class="q-px-sm text-h6 text-white">Total: {{ totalspending }}</div>
        </q-card-actions>
        <q-card-section class="q-pa-sm">
          <tr class="row" v-for="ex in exlist" :key="ex.id" style="font-size:18px" @click="showDetails(ex)">
            <td class="q-pl-xs text-no-wrap" style="cursor:grab;width:157px" @click="showDetails(ex)">{{ ex.date }}
              <q-tooltip class="bg-amber-10 text-h6">close detailed list</q-tooltip>
            </td>
            <td class="text-center text-no-wrap ellipsis cursor-pointer" style="width:61%" @click="showDetails(ex)">{{ ex.paye }}
              <q-tooltip class="bg-blue-10 text-h6">show spending details {{cats}}/{{subc}}</q-tooltip>
            </td>
            <td class="q-pl-xs text-right" style="width:12%">{{ ex.cost }}</td>
          </tr>
        </q-card-section>
      </q-card>
    </div>
  </q-dialog>
  </template>
  <script setup>
  import emitter from 'tiny-emitter/instance'
  import { ref, reactive } from 'vue'
  // const exlist = ref([])
  var exlist = reactive([])
  const opened = ref(false)
  const tit = ref(null)
  const cats = ref(null)
  const subc = ref(null)
  const ani = ref(false)
  const totalspending = ref(0)
  const props = defineProps({ data : { type: Array }})
  console.log(`-ST-%cExpDetailsPad`, 'color:red;font-size:12px')
  emitter.on('open-ExpDetailsPad', (ca,su,yr,mn,payeId) => openIt(ca,su,yr,mn,payeId))
  function getAni () {
    return ani.value==0 ? 'slide-up' : ani.value==1 ? 'slide-down' : ani.value==2 ? 'slide-right' : 'slide-left'
  }
  function openIt (ca, su, yr, mn, payeId) {
    ani.value = (new Date()).getSeconds() % 4
    console.log(`-fn-openIt ca=${ca} su=${su} yr=${yr} mn=${mn} payeId=${payeId}`)
    if (ca != null) cats.value = ca
    if (su != null) subc.value = su
    let cstit = ''
    let ymtit = ''
    let dx = props.data
    if (ca != null) {
      dx = dx.filter(x => x.cats == ca)
      cstit += ca
    }
    if (su != null) {
      dx = dx.filter(x => x.subc == su)
      cstit += '/' + su
    }
    if (yr > 0) {
      dx = dx.filter(x => x.date.substring(0, 4) == yr)
      ymtit += yr + '年'
    }
    if (mn > 0) {
      dx = dx.filter(x => x.date.substring(5, 7) == mn)
      ymtit += ' ' + mn + '月'
    }
    if (payeId > 0) {
      dx = dx.filter(x => x.payeId == payeId)
    }
    console.log(`-fn-CK ca=${ca} su=${su} yr=${yr} mn=${mn} payeId=${payeId} cstit=${cstit} ymtit=${ymtit} dx.length=${dx.length}`)
    // if (data != null) exlist.value = data
    exlist = dx
    // const deepcp = JSON.stringify(dx)
    // exlist = JSON.parse(deepcp)
    // console.table(exlist.map(p => [p.id, p.payeId, p.paye]).sort((a,b) => a[0]-b[0]))
    tit.value = ymtit.length>0 ? ymtit + '(' + cstit + ') 消 费' :  '(' + cstit + ') 消 费'
    opened.value = true
    totalspending.value = exlist.reduce((total, next) => total + parseFloat(next.cost), 0).toFixed(2)
  }
  function showDetails (ex, idx) {
    console.log(`-FN-showDetails cats=${cats.value} subc=${subc.value} idx=${idx}`, ex, exlist[idx])
    // console.table(Object.entries(ex))
    if (cats.value === 'Shopping' && subc.value === 'Grocery') ex.hasPlst = true
    if (cats.value === 'Golf' && subc.value === 'Play') ex.hasScore = true
    emitter.emit('open-exdar', JSON.parse(JSON.stringify(ex)))
    // console.table(exlist.map(p => [p.id, p.payeId, p.paye]).sort((a,b) => a[0]-b[0]))
  }
  function showChart () { // should triggered from ChartsProxy2/ChartYmpi to show ChartsProxy3/ChartSubc
    if (year.value == null) emitter.emit('open-ChartsProxy2', 'suba', exlist)
    else emitter.emit('open-ChartsProxy2', 'subc', exlist, year.value, mnth.value)
  }
  // function showChart () { // should triggered from ChartsProxy2/ChartYmpi to show ChartsProxy3/ChartSubc
  // 	if (year.value == null) emitter.emit('open-ChartsProxy2', 'suba', exlist.value)
  // 	else emitter.emit('open-ChartsProxy2', 'subc', exlist.value, year.value, mnth.value)
  // }
  </script>
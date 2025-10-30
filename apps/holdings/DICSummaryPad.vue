<template>
<q-dialog  v-model="opened" transition-show="rotate" transition-hide="jump-down">
  <div class="absolute" style="top:95px; left:98px; margin:auto; max-width:100vw; border:2px solid cyan">
    <q-card style="margin-left:0px">
      <q-card-section class="bg-teal-10 text-white q-pr-lg q-pt-lg">
        <tr v-if="allzval>0" class="text-h6">
          <!-- <td style="width:90px" class="q-px-sm text-no-wrap cursor-pointer" @click="$refs.dic.openIt('allz', compAllz)">{{ tagz }}</td> -->
          <!-- <td style="width:90px" class="q-px-sm text-no-wrap cursor-pointer" @click="refDicDetailsPad.openIt('allz', compAllz)">{{ tagz }}</td> -->
          <td style="width:90px" class="q-px-sm text-no-wrap cursor-pointer" @click="refDicDetailsPad.openIt(allz, tagz)">{{ tagz }}</td>
          <td style="width:50px" class="text-right">{{ fmtcy(allzval) }}</td>
        </tr>
        <tr v-if="allmval>0" class="text-h6">
          <td style="width:90px" class="q-px-sm text-no-wrap cursor-pointer" @click="refDicDetailsPad.openIt(allm, tagm)">{{ tagm }}</td>
          <td style="width:50px" class="text-right">{{ fmtcy(allmval) }}</td>
        </tr>
        <tr v-if="allaval>0" class="text-h6">
          <!-- <td style="width:90px" class="q-px-sm text-no-wrap cursor-pointer" @click="$refs.dic.openIt('alla', compAlla)">{{ taga }}</td> -->
          <td style="width:90px" class="q-px-sm text-no-wrap cursor-pointer" @click="refDicDetailsPad.openIt(alla, taga)">{{ taga }}</td>
          <td style="width:50px" class="text-right">{{ fmtcy(allaval) }}</td>
        </tr>
        <tr v-if="allxval>0" class="text-h6"> <!-- this year < month in account  -->
          <!-- <td style="width:90px" class="q-px-sm text-no-wrap cursor-pointer" @click="$refs.dic.openIt('allx', compAllx)">{{ tagx }}</td> -->
          <td style="width:90px" class="q-px-sm text-no-wrap cursor-pointer" @click="refDicDetailsPad.openIt(allx, tagx)">{{ tagx }}</td>
          <td style="width:50px" class="text-right">{{ fmtcy(allxval) }}</td>
        </tr>
      </q-card-section>
    </q-card>
  </div>
  <DicDetailsPad ref="refDicDetailsPad" />
</q-dialog>
</template>
<script setup>
import { ref, onMounted } from 'vue'
import { libFunctions } from '../src/composables/libFunctions'
const { fmtcy } = libFunctions()
import DicDetailsPad from './DICDetailsPad'

const tagz = ref(null)
const taga = ref(null)
const tagm = ref(null)
const tagx = ref(null)
const allz = ref([])
const alla = ref([])
const allm = ref([])
const allx = ref([])
const allzval = ref(0)
const allaval = ref(0)
const allmval = ref(0)
const allxval = ref(0)
const year = ref(null)
const month = ref(null)
const anum = ref(null)
const opened = ref(false)
const refDicDetailsPad = ref(null)

console.log('-ST-DICSummaryPad')
defineExpose({openIt})
onMounted(() => {
  console.log(`-MT-refDicDetailsPad=${refDicDetailsPad.value}`)
})

function openIt (tg, yr, mnth, anu, da) {
  console.log(`-CK-fn-DICSummaryPad.openIt tag=${tg} year=${yr} month=${mnth} anum=${anu}`, da)
  allz.value = da
  year.value = yr
  month.value = mnth
  anum.value = anu
  getAll(tg, allz.value)
  opened.value = true
}
function getAll (tag, allz) {
  let allzv = 0
  let allav = 0
  let allmv = 0
  let allxv = 0
  alla.value = []
  allm.value = []
  allx.value = []
  for (let i = 0; i < allz.length; i++) {
    const p = allz[i]
    allzv += parseFloat(p.amount)
    if (p.account_num == anum.value && p.month == month.value) {
      allxv += parseFloat(p.amount)
      allx.value.push(p)
      console.log(`-CK-fn-getAll i=${i} p.account_num=${p.account_num} p.month=${p.month} acct_num=${anum.value} month=${month.value}`, allx.value)
    }
    if (p.account_num === anum.value) {
      allav += parseFloat(p.amount)
      alla.value.push(p)
    }
    if (p.month <= month.value) {
      allmv += parseFloat(p.amount)
      allm.value.push(p)
    }
  }
  allzval.value = allzv
  allaval.value = allav
  allmval.value = allmv
  allxval.value = allxv
  // alla.value = alla
  // allm.value = allm
  // allx.value = allx
  // console.log('-CK-dg- Xvals', allzval.value, allaval.value, allmval.value, allxval.value)
  if (tag === 'dvd') {
    tagz.value = 'Dividend Received This Year in All Accounts:'
    taga.value = 'Dividend Received This Year in this Account ' + anum.value + ':'
    tagm.value = 'Dividend Received This Year upto this month ' + month.value + '月:'
    tagx.value = 'Dividend Received This Year in this Account:' + anum.value + ' ' + month.value + '月:'
  } else if (tag === 'ccd') {
    tagz.value = 'Credit Card Redeemed This Year in All Accounts:'
    taga.value = 'Credit Card Redeemed This Year in this Account ' + anum.value + ':'
    tagm.value = 'Credit Card Redeemed This Year upto this month ' + month.value + '月:'
    tagx.value = 'Credit Card Redeemed This Year in this Account:' + anum.value + ' ' + month.value + '月:'
  } else if (tag === 'int') {
    tagz.value = 'Interest Recieved This Year in All Accounts:'
    taga.value = 'Interest Recieved This Year in this Account ' + anum.value + ':'
    tagm.value = 'Interest Recieved This Year upto this month ' + month.value + '月:'
    tagx.value = 'Interest Recieved This Year in this Account:' + anum.value + ' ' + month.value + '月:'
  }
}
</script>
<style>
.num-pad {
  width: 50px;
  height: 50px;
  font-size: 18px;
}
</style>

<template>
<div v-if="bank=='Chase'" :style="{ display:opened }" style="margin:-33px 0 0 0">
  <table v-if="showData[0]">
    <q-tr><td class="text-right q-px-md">Bank Name</td><td>{{ assets.bank }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Year</td><td>{{ assets.year }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Month</td><td>{{ assets.month }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Begin Date</td><td>{{ assets.begin_date }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">End Date</td><td>{{ assets.end_date }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Begin Balance</td><td>{{ assets.begin_balance }}</td></q-tr>
    <!-- <q-tr><td class="text-right q-px-md">Begin Balance</td><td>{{ chk.begin_balance + sav.begin_balance }}</td></q-tr> -->
    <q-tr><td class="text-right q-px-md">End Balance</td><td>{{ assets.end_balance }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Primary Account</td><td>{{ assets.primary_account }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Transaction Count</td><td>{{ assets.tran_cnt }}</td></q-tr>
  </table>
  <div v-if="showData[1]">
    <div class="text-center q-pl-sm text-amber-10">CHECKING ACCOUNT INFORMATION</div>
    <q-tr><td class="text-right q-px-sm">Begin Balance</td><td>{{ chk.begin_balance }}</td></q-tr>
    <!-- <q-tr><td class="text-right q-px-sm">Withdrawals</td><td>{{ chk.withdrawals }}</td></q-tr> -->
    <q-tr><td class="text-right q-px-sm">End Balance</td><td>{{ chk.end_balance }}</td></q-tr>
    <q-tr><td class="text-right q-px-sm">Account Number</td><td>{{ assets.primary_account }}</td></q-tr>
    <div class="text-h5 text-amber q-pl-md">Checking Account Activity</div>
    <q-tr class="q-pl-sm" v-for="(ac) in chk.act" :key="ac">
      <td class="q-pl-sm">{{ ac[0] }}</td>
      <td class="q-px-sm">{{ ac[1].substring(0, 61) }}</td>
      <td class="q-px-sm text-right">{{ ac[2] }}</td>
      <td class="q-pl-sm text-right">{{ ac[3] }}</td>
    </q-tr>
  </div>
  <div v-if="showData[2]">
    <div class="text-center q-pl-sm text-amber-10">SAVINGS ACCOUNT INFORMATION</div>
    <q-tr><td class="text-right q-px-sm">Begin Balance</td><td>{{ sav.begin_balance }}</td></q-tr>
    <q-tr><td class="text-right q-px-sm">End Balance</td><td>{{ sav.end_balance }}</td></q-tr>
    <q-tr><td class="text-right q-px-sm">Account Number</td><td>{{ sav.account }}</td></q-tr>
    <div v-if="sav.act.length>0">
      <div class="text-h5 text-amber q-pl-md">Savings Account Activity</div>
      <q-tr class="q-pl-sm" v-for="(act) in sav.act" :key="act">
        <td class="q-pl-sm">{{ act[0] }}</td>
        <td class="q-px-sm">{{ act[1].substring(0, 61) }}</td>
        <td class="q-px-sm text-right">{{ act[2] }}</td>
        <td class="q-pl-sm text-right">{{ act[3] }}</td>
      </q-tr>
    </div>
  </div>
  <div v-if="showData[3]">
    <div v-if="sav.nos.length>0">
      <div class="text-h5 text-amber q-pl-md">Statement Notes</div>
      <q-tr class="q-pl-sm" v-for="(act) in sav.nos" :key="act">
        <td class="q-pl-sm">{{ act[0] }}</td>
        <td class="q-pl-sm text-right">{{ act[1] }}</td>
      </q-tr>
    </div>
  </div>
  <q-btn-group glossy spread>
    <q-btn v-if="!showData[0]" label="Assets"   @click="showSection(0)" /><q-btn v-if="showData[0]" label="Add Assets"   @click="addAssets()"   color="amber-10"/>
    <q-btn v-if="!showData[1]" label="Checking" @click="showSection(1)" /><q-btn v-if="showData[1]" label="Add Checking" @click="addChecking()" color="amber-10"/>
    <q-btn v-if="!showData[2]" label="Savings"  @click="showSection(2)" /><q-btn v-if="showData[2]" label="Add Savings" :disable="!checkingAdded" @click="addSavings()"  color="amber-10"/>
    <q-btn v-if="!showData[3]" label="Notes"    @click="showSection(3)" /><q-btn v-if="showData[3]" label="Add Notes"    @click="addNotes()"    color="amber-10"/>
  </q-btn-group>
</div>
</template>
<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'
import { dayFunctions } from 'src/composables/dayFunctions';
import { axiosFunctions } from 'src/composables/axiosFunctions'
import { libFunctions } from 'src/composables/libFunctions'
const { $q } = libFunctions()
const opened = ref('')
const statement = ref({})
const showData = ref([4])
const bank = ref(null)
const year = ref(null)
const month = ref(null)
const checkingAdded = ref(false)
const assets = ref({ bank:bank.value, year:year.value, month:month.value })
const sav = ref({})
const chk = ref({})

showData.value.fill(false)
showData.value[0] = true

console.log('-ST-MonthlyStatementsChase')

emitter.on('open-MonthlyStatementChase', (x) => loadData(x))
emitter.on('close-MonthlyStatementChase', () => opened.value = 'none')

function addAssets () {
  const path = process.env.API + '/bankstatementloader/addAssets'
  paxios(path, assets.value)
}
function addNotes () {
  const inData = []
  let note_id = 0
  sav.value.nos.forEach(p => {
    const x = {}
    x.bank = assets.value.bank
    x.year = assets.value.year
    x.month = assets.value.month
    x.note_id = ++note_id
    x.notes = p[0]
    x.amount = p[1]
    inData.push(x)
  })
  const path = process.env.API + '/bankstatementloader/addNotes'
  paxios(path, inData)
}
function addSavings () {
  const inData = []
  let tran_num = chk.value.act.length
  let begin_balance = sav.value.begin_balance
  sav.value.act.forEach(p => {
    const x = {}
    x.bank = assets.value.bank
    x.year = assets.value.year
    x.month = assets.value.month
    x.account_num = sav.value.account
    x.acct_type = 'Savings'
    x.tran_num = ++tran_num
    const md = p[0].split('/')
    x.tran_date = assets.value.year + '-' + md[0] + '-' + md[1]
    x.description = p[1]
    x.begin_balance = begin_balance
    x.amount = p[2]
    x.end_balance = p[3]
    begin_balance = x.end_balance
    inData.push(x)
  })
  const path = process.env.API + '/bankstatementloader/addChecking'
  paxios(path, inData)
}
function addChecking () {
  console.log(`-fn-addChecking year=${year.value} month=${month.value}`, chk.value.act)
  const inData = []
  let tran_num = 0
  let begin_balance = chk.value.begin_balance
  if (chk.value.act.length == 0) {
    const x = {}
      x.bank = 'Chase'
      x.year = year.value
      x.month = month.value
      x.account_num = assets.value.primary_account
      x.acct_type = 'Checking'
      x.tran_num = tran_num
      x.tran_date = null
      x.description = 'No Withdrawals'
      x.begin_balance = chk.value.begin_balance
      x.amount = null
      x.end_balance = chk.value.end_balance
      begin_balance = x.end_balance
      inData.push(x)
  } else {
    chk.value.act.forEach(p => {
      const x = {}
      x.bank = assets.value.bank
      x.year = assets.value.year
      x.month = assets.value.month
      x.account_num = assets.value.primary_account
      x.acct_type = 'Checking'
      x.tran_num = ++tran_num
      const md = p[0].split('/')
      x.tran_date = assets.value.year + '-' + md[0] + '-' + md[1]
      x.description = p[1]
      x.begin_balance = begin_balance
      x.amount = p[2]
      x.end_balance = p[3]
      begin_balance = x.end_balance
      inData.push(x)
    })
  }
  const path = process.env.API + '/bankstatementloader/addChecking'
  paxios(path, inData)
  checkingAdded.value = true
}
function getList () {} //cheating createApp
function showSection (i) {
  showData.value.fill(false)
  showData.value[i] = true
}
emitter.on('bankstatementloader-loadMonthlyStatementChase', (x) => loadStmt(x))
const { paxios, gaxios } = axiosFunctions()
function loadStmt (da) {
  if (da.status === 'NO_FILE') {
    $q.dialog({
      title: 'The PDF File Not Exists, Get File in First',
      message: da.info + ' will try privous month'
    })
    return reLoadDataTryNewYmon()
  }
  assets.value = da.assets
  chk.value = da.chk
  console.log('chk.act=', chk.value.act[0])
  sav.value = da.sav
  assets.value.tran_cnt = da.chk.act.length + da.sav.act.length
  assets.value.bank = bank.value
  assets.value.year = year.value
  assets.value.month = month.value
  assets.value.begin_date = (new Date(assets.value.begin_date)).yyyymmdd()
  assets.value.end_date = (new Date(assets.value.end_date)).yyyymmdd()
  assets.value.begin_balance = parseFloat(chk.value.begin_balance) + parseFloat(sav.value.begin_balance)
  assets.value.end_balance = parseFloat(chk.value.end_balance) + parseFloat(sav.value.end_balance)
}
function loadData(stmt) {
  if (stmt.bank !== 'Chase') {
    opened.value = 'none'
    return
  }
  opened.value = ''
  statement.value = stmt
  bank.value = stmt.bank
  const x = stmt.date.split('-')
  year.value = x[0]
  month.value = x[1]
  let yyyymm = x[0]+x[1]
  // console.log(`-CK-fn-loadData Chase stmt yyyymm=${yyyymm}`)
  const path = process.env.API + '/bankstatementloader/loadMonthlyStatementChase/' + yyyymm
  gaxios(path)
}
function reLoadDataTryNewYmon() {
  let mx = month.value - 1
  month.value = month.value.replace(/[1-9]/, mx)
  let yyyymm = year.value + month.value
  console.log(`-fn-loadData Chase stmt for ${yyyymm}`)
  const path = process.env.API + '/bankstatementloader/loadMonthlyStatementChase/' + yyyymm
  gaxios(path)
}
</script>

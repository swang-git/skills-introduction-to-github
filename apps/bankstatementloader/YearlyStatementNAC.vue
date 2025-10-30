<template>
<div v-if="statement.bank==='BOA'" :style="{ display:opened }" style="margin:-33px 0 0 0">
  <div v-if="showData[0]">
    <q-tr><td class="text-right q-px-md">Bank Name</td><td>{{ bank }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Year</td><td>{{ year }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Month</td><td>{{ month }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Begin Date</td><td>{{ assets.begin_date }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">End Date</td><td>{{ assets.end_date }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Primary Account</td><td>{{ assets.primary_account }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Tran Cnt</td><td>{{ assets.tran_cnt }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Begin Balance</td><td>{{ assets.begin_balance }}</td></q-tr>
    <q-tr>
      <td class="text-right q-px-md">End Balance</td><td>{{ assets.end_balance }}</td>
    </q-tr>
  </div>

  <div v-if="showData[1]">
    <div class="text-h5 text-amber q-pl-md q-pt-md">Checking Account Info</div>
    <q-tr><td class="text-right q-px-md">Checking Begin Balance</td><td>{{ chkinfo.chk.begin_balance }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Checking End Balance</td><td>{{ chkinfo.chk.end_balance }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">CheckSav Account</td><td>{{ chkinfo.sav.account }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">CheckSav Begin Balance</td><td>{{ chkinfo.sav.begin_balance }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">CheckSav End Balance</td><td>{{ chkinfo.sav.end_balance }}</td></q-tr>

    <div class="text-h5 text-amber q-pl-md">Checking Account Activity</div>
    <q-tr class="q-pl-sm" v-for="(act) in chkinfo.chk.act" :key="act">
      <td class="q-pl-sm">{{ act[0] }}</td>
      <td class="q-px-sm">{{ act[1].substring(0, 56) }}</td>
      <td class="text-right">{{ act[2] }}</td>
    </q-tr>
  </div>

  <div v-if="showData[2]">
    <div class="text-h5 text-amber q-pl-md q-pt-md">Savings Account Info</div>
    <q-tr><td class="text-right q-px-md">Savings Account</td><td>{{ savinfo.account }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Savings Begin Balance</td><td>{{ savinfo.begin_balance }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Savings End Balance</td><td>{{ savinfo.end_balance }}</td></q-tr>

    <div class="text-h5 text-amber q-pl-md">Savings Account Activity</div>
      <q-tr class="q-pl-sm" v-for="(act) in savinfo.act" :key="act">
        <td class="q-pl-sm">{{ act[0] }}</td>
        <td class="q-px-sm">{{ act[1].substring(0, 61) }}</td>
        <td class="text-right">{{ act[2] }}</td>
      </q-tr>
    </div>

    <div v-if="showData[3]" class="q-pa-md">
      <div class="text-h5 text-amber">Checking/Savings Account Notes</div>
      <q-tr v-for="(nos) in chkinfo.chk.nos" :key="nos">
        <td class="q-pr-sm">Checking Account {{ nos[0] }}</td>
        <td class="justify-end">{{ nos[1] }}</td>
      </q-tr>
      <q-tr v-for="(nos) in savinfo.nos" :key="nos">
        <td class="q-px-">Savings Account {{ nos[0] }}</td>
        <td class="text-right">{{ nos[1] }}</td>
      </q-tr>
    </div>
    <q-btn-group glossy spread>
      <q-btn v-if="!showData[0]" label="Assets"   @click="showSection(0)" /><q-btn v-if="showData[0]" label="Add Assets"   color="amber-10" @click="addAssets()" />
      <q-btn v-if="!showData[1]" label="Checking" @click="showSection(1)" /><q-btn v-if="showData[1]" label="Add Checking" color="amber-10" @click="addChecking()" />
      <q-btn v-if="!showData[2]" label="Savings"  @click="showSection(2)" /><q-btn v-if="showData[2]" label="Add Savings"  color="amber-10" @click="addSavings()" />
      <q-btn v-if="!showData[3]" label="Notes"    @click="showSection(3)" /><q-btn v-if="showData[3]" label="Add Notes"    color="amber-10" @click="addNotes()" />
    </q-btn-group>
  </div>
</template>
<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'
// import { dayFunctions } from 'src/composables/dayFunctions'
import { axiosFunctions } from 'src/composables/axiosFunctions'
import { libFunctions } from 'src/composables/libFunctions'
const { $q } = libFunctions()
const opened = ref('')
const { gaxios, paxios } = axiosFunctions()
const statement = ref({ bank:null })
const bank = ref(null)
var ymon = null
const year = ref(null)
const month = ref(null)
const showData = ref([4])
const assets = ref({ begin_date:null, end_date:null, primary_account:null, tran_cnt:null, begin_balance:null, end_balance:null })
const chkinfo = ref({ chk:{ begin_balance:null, end_balance:null, act:null, nos:null }, sav: { account:null, begin_balance:null, end_balance:null, nos:null } })
const savinfo = ref({ account:null, begin_balance:null, end_balance:null, act:null, nos:null })
console.log('-ST-YearlyStatementNAC')

emitter.on('open-YearlyStatementNAC', (x) => loadData(x))
emitter.on('close-YearlyStatementNAC', () => opened.value = 'none')
emitter.on('bankstatementloader-loadYearlyStatementNAC', (x) => setStmt(x))

showData.value.fill(false)
function getList() {} // full the parent's createApp
function showSection (i) {
  showData.value.fill(false)
  showData.value[i] = true
}
function addNotes () {
  const inData = []
  let note_id = 0
  chkinfo.value.chk.nos.forEach(p => {
    note_id++
    const x = {}
    x.bank = bank.value
    x.year = year.value
    x.month = month.value
    x.note_id = note_id
    x.notes = p[0]
    x.amount = parseFloat(p[1])
    inData.push(x)
  })
  savinfo.value.nos.forEach(p => {
    note_id++
    const x = {}
    x.bank = bank.value
    x.year = year.value
    x.month = month.value
    x.note_id = note_id
    x.notes = p[0]
    x.amount = parseFloat(p[1])
    inData.push(x)
  })
  console.log('-CK-fn-notes Data', Object.values(chkinfo.value.chk.nos), inData)
  const path = process.env.API + '/bankstatementloader/addNotes'
  paxios(path, inData)
}
function addSavings () {
  const inData = []
  let tran_num = 0
  let begin_balance = parseFloat(savinfo.value.begin_balance)
  savinfo.value.act.forEach(p => {
    tran_num++
    const x = {}
    x.bank = bank.value
    x.year = year.value
    x.month = month.value
    x.account_num = savinfo.value.account
    x.acct_type = 'Savings'
    x.tran_num = tran_num
    x.tran_date = (new Date(p[0])).yyyymmdd()
    x.description = p[1]
    x.begin_balance = begin_balance
    x.amount = parseFloat(p[2].replace(',', ''))
    x.end_balance = begin_balance + x.amount
    begin_balance = x.end_balance
    inData.push(x)
  })
  // console.log('-fn-Activity Data', inData)
  const path = process.env.API + '/bankstatementloader/addActivityBOA'
  paxios(path, inData)
}
function addChecking () {
  const inData = []
  let tran_num = 0
  let begin_balance = parseFloat(chkinfo.value.sav.begin_balance)   // no activities for this account
  const x = {}
  x.bank = bank.value
  x.year = year.value
  x.month = month.value
  x.account_num = chkinfo.value.sav.account
  x.acct_type = 'Checking'
  x.tran_num = tran_num
  x.tran_date = assets.value.begin_date
  x.description = 'No Transactions for Accnt' + x.account_num
  x.begin_balance = begin_balance
  x.amount = 0
  x.end_balance = begin_balance + x.amount
  inData.push(x)
  begin_balance = parseFloat(chkinfo.value.chk.begin_balance)
  chkinfo.value.chk.act.forEach(p => {
    tran_num++
    const x = {}
    x.bank = bank.value
    x.year = year.value
    x.month = month.value
    x.account_num = assets.value.primary_account
    x.acct_type = 'Checking'
    x.tran_num = tran_num
    // x.tran_date = yyyymmdd(new Date(p[0]))
    x.tran_date = (new Date(p[0])).yyyymmdd()
    x.description = p[1]
    x.begin_balance = begin_balance
    x.amount = parseFloat(p[2])
    x.end_balance = begin_balance + x.amount
    begin_balance = x.end_balance
    inData.push(x)
  })
  // console.log('-CK-fn-Activity Data', inData)
  const path = process.env.API + '/bankstatementloader/addActivityBOA'
  paxios(path, inData)
}
function addAssets () {
  // console.log('-CK-fn-addAssets')
  const path = process.env.API + '/bankstatementloader/addAssets'
  assets.value.bank = bank.value
  assets.value.year = year.value
  assets.value.month =  month.value
  paxios(path, assets.value)
}
function setStmt (da) {
  if (da.status === 'NO_FILE') {
    $q.dialog({
      title: 'The PDF File Not Exists, Get File in First',
      message: da.info + 'move to previous month and try'
    })
    return reLoadDataTryNewYmon()
  }
  console.log(`-CK-fn-setStmt loadYearlyStatementNAC da.status=${da.status} da.assets=`, da.assets, da)

  assets.value = da.assets
  assets.value.begin_date = (new Date(assets.value.begin_date)).yyyymmdd()
  assets.value.end_date = (new Date(assets.value.end_date)).yyyymmdd()
  chkinfo.value = da.chkinfo
  savinfo.value = da.savinfo
  assets.value.begin_balance = parseFloat(chkinfo.value.chk.begin_balance) + parseFloat(chkinfo.value.sav.begin_balance) + parseFloat(savinfo.value.begin_balance)
  assets.value.end_balance = parseFloat(chkinfo.value.chk.end_balance) + parseFloat(chkinfo.value.sav.end_balance) + parseFloat(savinfo.value.end_balance)
  assets.value.tran_cnt = chkinfo.value.chk.act.length + savinfo.value.act.length
}
function loadData (stmt) {
  console.log('-CK-fn-loadData NAC statement', stmt)
  if (stmt.bank !== 'NAC') {
    opened.value = 'none'
    return
  }
  opened.value = ''
  showData.value[0] = true
  statement.value = stmt
  bank.value = stmt.bank
  const x = stmt.date.split('-')
  year.value = x[0]
  month.value = x[1]
  ymon = x[0] + x[1]
  let yyyymmdd = x[0] + x[1] + x[2]
  const path = process.env.API + '/bankstatementloader/loadYearlyStatementNAC/' + yyyymmdd
  gaxios(path)
}
function reLoadDataTryNewYmon () {
  console.log('-CK-fn-reLoadDataTryNewYmon BOA statement')
  let mx = month.value - 1
  month.value = month.value.replace(/[1-9]/, mx)
  ymon = year.value + month.value
  // console.log(`-CK-new ymon=${ymon}`)
  const path = process.env.API + '/bankstatementloader/loadYearlyStatementNAC/' + ymon
  gaxios(path)
}
</script>

<template>
<div class="text-h6 text-white">
  <q-card square class="bg-teal-10">
    <!-- <q-card-section class="text-center text-h6" v-html="getStatementLink()" /> -->
    <div class="text-center text-h6 q-pt-sm">
      <a :href=url target="_blank">{{ urlname }}</a>
    </div>
    <div v-for="(o) in options" :key="o">
      <q-radio class="q-px-md" size="lg" keep-color :key="o" v-model="statement.bank" :val="o.value" :label="o.label" :color="o.color" @click="setStatementLink()"/>
    </div>
    <q-card-actions align="between">
      <DatePicker :date="compDate" txsz="text-h6" @upd-date="updDate" style="width:240px" />
      <div class="q-pt-sm q-pl-md">
        <q-btn glossy rounded icon="pending" class="bg-indigo" size="20px"
          :label="statement.bank=='ChaseBkg' ? 'Load Chase Brockerage Intraday' : 'Load Monthly Statement'" @click="loadStateements">
          <q-icon name="playlist_add_check_circle" size="33px" color="amber-4" />
        </q-btn>
      </div>
    </q-card-actions>
  </q-card>
  <ReconFidelityCC />
  <MonthlyStatementsFidelity />
  <MonthlyStatementsBOA />
  <MonthlyStatementChase />
  <ChaseBrockerageIntraday @intra-day="setIntraday"/>
  <YearlyStatementNAC />
</div>
<!-- <q-btn v-show="isLoading" glossy rounded icon="arrow_left" class="bg-yellow" label="back" @click="isLoading=false" /> -->
</template>
<script setup>
import { reactive, computed, ref } from 'vue'
import ReconFidelityCC from './ReconFidelityCC'
import MonthlyStatementsFidelity from './MonthlyStatementsFidelity'
import YearlyStatementNAC from './YearlyStatementNAC'
import MonthlyStatementsBOA from './MonthlyStatementsBOA'
import MonthlyStatementChase from './MonthlyStatementChase'
import ChaseBrockerageIntraday from './ChaseBrockerageIntraday'
import DatePicker from '../src/components/DatePicker'
import emitter from 'tiny-emitter/instance'
import { libFunctions } from '../src/composables/libFunctions'
const { isDesk, buildApp, $q } = libFunctions()

const intraday = ref(null)
// emitter.on('intraday', (x) => intraday.value = x)

console.log(`-ST-bankstatementloader`)

const options = ([
  { label: 'Fidelity Credit Card', value: 'FidelCC', color: 'red' },
  { label: 'Fidelity Retirement Accounts', value: 'Fidelity', color: 'cyan' },
  { label: 'Chase', value: 'Chase', color: 'green' },
  { label: 'Bank of America', value: 'BOA', color: 'amber' },
  { label: 'Chase Brokerage Intraday', value: 'ChaseBkg', color: 'blue' },
  // { label: 'North American Company', value: 'NAC', color: 'lime' },
])
const isLoading = ref(false)
const url = ref(null)
const urlname = ref(null)
const statement = reactive({
  // bank: 'NAC',
  // bank: 'Chase',
  // bank: 'ChaseBkg',
  bank: 'FidelCC',
  // bank: 'Fidelity',
  // bank: 'BOA',
  date: new Date().yyyymmdd()
  // date: '2021-07-20'
})
const compDate = computed({
  get: () => {
    const newDate = getBankStatementDate()
    emitter.emit('new-date', newDate)
    return newDate
  }
})

setStatementLink()

function setIntraday (x) {
  intraday.value = x
  setStatementLink()
}
function closeOthersAndBuildApp () {
  console.log('-CK-fn-closeOthers for', statement.bank)
  if (statement.bank === 'FidelCC')  {
    emitter.emit('close-MonthlyStatementChase')
    emitter.emit('close-ChaseBrockerageIntraday')
    emitter.emit('close-MonthlyStatementsFidelity')
    emitter.emit('close-MonthlyStatementsBOA')
    emitter.emit('close-YearlyStatementsNAC')
    buildApp("信用卡月报核查 FidelCC", "Credit Card Statement Check with DB spends");
  } else if (statement.bank === 'Chase') {
    emitter.emit('close-ChaseBrockerageIntraday')
    emitter.emit('close-MonthlyStatementsFidelity')
    emitter.emit('close-ReconFidelityCC')
    emitter.emit('close-MonthlyStatementsBOA')
    emitter.emit('close-YearlyStatementsNAC')
    buildApp('Chase Monthly Statement')
  } else if (statement.bank === 'ChaseBkg') {
    emitter.emit('close-MonthlyStatementChase')
    emitter.emit('close-MonthlyStatementsFidelity')
    emitter.emit('close-ReconFidelityCC')
    emitter.emit('close-MonthlyStatementsBOA')
    emitter.emit('close-YearlyStatementsNAC')
    buildApp('Brockerage Chase')
  } else if (statement.bank === 'Fidelity') {
    emitter.emit('close-MonthlyStatementChase')
    emitter.emit('close-ChaseBrockerageIntraday')
    emitter.emit('close-ReconFidelityCC')
    emitter.emit('close-MonthlyStatementsBOA')
    emitter.emit('close-YearlyStatementsNAC')
    buildApp('Fidelity Monthly Statement')
  } else if (statement.bank === 'BOA') {
    emitter.emit('close-MonthlyStatementChase')
    emitter.emit('close-ChaseBrockerageIntraday')
    emitter.emit('close-MonthlyStatementsFidelity')
    emitter.emit('close-ReconFidelityCC')
    emitter.emit('close-YearlyStatementsNAC')
    buildApp('B o A Monthly Statement')
  } else if (statement.bank === 'NAC') {
    // emitter.emit('open-YearlyStatementNAC')
    emitter.emit('close-MonthlyStatementsBOA')
    emitter.emit('close-MonthlyStatementChase')
    emitter.emit('close-ChaseBrockerageIntraday')
    emitter.emit('close-MonthlyStatementsFidelity')
    emitter.emit('close-ReconFidelityCC')
    buildApp('NAC Yearly Statement')
  }
}
function setStatementLink() {
  // const date = statement.date
  const date = compDate.value
  const yyyymm = date.yyyymm().replace('-', '')
  // const yyyymm = '2025-03'
  console.log(`-fn-setStatementLink bank=${statement.bank} date=${date}`)
  if (statement.bank === 'FidelCC') { url.value = '/docs/fidelity_credit_card/' + date + '.pdf'; urlname.value='FIDELITY CREDIT CARD MONTHLY STATEMENT' }
  else if (statement.bank === 'BOA') { url.value = '/docs/BOA/' + yyyymm + '_savings.pdf'; urlname.value = 'BANK OF AMERICA MONTHLY STATEMENT' }
  else if (statement.bank === 'Chase') { url.value = '/docs/Chase/' + yyyymm + '.pdf'; urlname.value = 'CHASE MONTHLY STATEMENT' }
  else if (statement.bank === 'ChaseBkg') { url.value = '/docs/Chase/' + intraday.value + '_bkg.pdf'; urlname.value = 'Chase Brokerage Intraday' }
  else if (statement.bank === 'Fidelity') { url.value = '/docs/Fidelity/' + yyyymm + '_ira.pdf'; urlname.value = 'FIDELITY MONTHLY STATEMENT (IRA/ROTH)' }
  else if (statement.bank === 'NAC') { url.value = '/docs/NAC/' + yyyymm + '.pdf'; urlname.value = 'North American Company Yearly Statement' }
  return closeOthersAndBuildApp()
}
function getBankStatementDate() {
  const bank = statement.bank
  var d = new Date()
  let month = d.getMonth() + 1
  // if (bank === 'Chase' || bank === 'BOA') month += 1
  let date = d.getDate()
  if (date >= 5 && bank === 'Fidelity') month -= 1 // do last month if in the first 4 days of the month
  else if (date < 5 && bank === 'Fidelity') month -= 1 // do last month if in the first 4 days of the month
  // else if (date >= 20 && bank === 'BOA') month -= 1 // do last month if in the first 4 days of the month
  // else if (date < 20 && bank === 'BOA') month -= 1 // do last last month if in the first 4 days of the month
  else if (date < 20 && bank === 'BOA') month -= 0 // do last last month if in the first 4 days of the month
  // else if (date < 20 && bank === 'BOA') month -= 1 // do last last month if in the first 4 days of the month
  else if (date < 25 && /Chase/.test(bank)) month -= 1 // do last month if in the first 4 days of the month
  // else if (date <  16 && bank === 'Chase') month -= 2 // do last last month if in the first 4 days of the month
  // else if (date <  7 && bank === 'FidelCC') month -= 1 // do last last month if in the first 4 days of the month
  // else if (date >= 7 && bank === 'FidelCC') month -= 0 // do last last month if in the first 4 days of the month
  else if (date >= 5 && bank === 'FidelCC') month += 1 // do last last month if in the first 4 days of the month
  // else if (bank === 'NAC') { month = 7; date = 21 } // yearly statement

  // else if (date >= 7 && bank === 'FidelCC') month -= 7 // do last last month if in the first 4 days of the month
  // console.log(`-CK-bank=${bank} month=${month} date=${date}`)

  if (bank === 'FidelCC') {
    date = '03'
  // } else if (bank === 'NAC') {
  //   month = '07'
  //   date = '20'
  } else {
    date = '01'
    // month -= 2 // for development
  }
  let year = d.getFullYear()
  if (month == 0) {
    month = 12
    year -= 1
  }
  month = month < 10 ? '0' + month : month
  if (bank === 'NAC') {
    year = 2021  // for development
    month = '07'
    date = '20'
  }
  let ret = year + '-' + month + '-' + date
  if (bank === 'ChaseBkg') {
    let month = d.getMonth() + 1
    ret = year + '-' + month + '-' + d.getDate()
  }
  // console.log(`-CK-setBankSatementDate bank=${bank} compDate=${ret}`)
  statement.date = ret
  return ret
}
function updDate(x) {
  statement.date = x
  // console.log(`-CK-fn-updDate statement.date=${statement.date}`)
}
function loadStateements() {
  isLoading.value = true
  $q.notify('Loading Statement ' + statement.bank)
  // setStatementLink()
  if (statement.bank === 'FidelCC')  {
    emitter.emit('open-ReconFidelityCC', statement)
    emitter.emit('close-MonthlyStatementChase')
    emitter.emit('close-ChaseBrockerageIntraday')
    emitter.emit('close-MonthlyStatementsFidelity')
    emitter.emit('close-MonthlyStatementsBOA')
  } else if (statement.bank === 'Chase') {
    emitter.emit('open-MonthlyStatementChase', statement)
    emitter.emit('close-ChaseBrockerageIntraday')
    emitter.emit('close-MonthlyStatementsFidelity')
    emitter.emit('close-ReconFidelityCC')
    emitter.emit('close-MonthlyStatementsBOA')
  } else if (statement.bank === 'ChaseBkg') {
    emitter.emit('open-ChaseBrockerageIntraday', statement)
    emitter.emit('close-MonthlyStatementChase')
    emitter.emit('close-MonthlyStatementsFidelity')
    emitter.emit('close-ReconFidelityCC')
    emitter.emit('close-MonthlyStatementsBOA')
  } else if (statement.bank === 'Fidelity') {
    emitter.emit('open-MonthlyStatementsFidelity', statement)
    emitter.emit('close-ChaseBrockerageIntraday')
    emitter.emit('close-MonthlyStatementChase')
    emitter.emit('close-ReconFidelityCC')
    emitter.emit('close-MonthlyStatementsBOA')
  } else if (statement.bank === 'BOA') {
    emitter.emit('open-MonthlyStatementsBOA', statement)
    emitter.emit('close-ChaseBrockerageIntraday')
    emitter.emit('close-MonthlyStatementChase')
    emitter.emit('close-MonthlyStatementsFidelity')
    emitter.emit('close-ReconFidelityCC')
  } else if (statement.bank === 'NAC') {
    emitter.emit('open-YearlyStatementNAC', statement)
    emitter.emit('claose-MonthlyStatementsBOA')
    emitter.emit('close-ChaseBrockerageIntraday')
    emitter.emit('close-MonthlyStatementChase')
    emitter.emit('close-MonthlyStatementsFidelity')
    emitter.emit('close-ReconFidelityCC')
  }
  console.log('-fn-CK-LoadStateement for', statement.bank)
}
</script>

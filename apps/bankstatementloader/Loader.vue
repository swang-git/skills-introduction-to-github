<template>
<div class="text-h6 text-white">
  <q-card square class="bg-teal-10">
    <!-- <q-card-section class="text-center text-h6" v-html="getStatementLink()" /> -->
    <div class="text-center text-h6 q-pt-sm">
      <!-- <a :href=url target="_blank">{{ urlname }}</a> -->
      <a :href=compUrl target="_blank">{{ compUrlname }}</a>
    </div>
    <div v-for="(o) in options" :key="o">
      <!-- <q-radio class="q-px-md" size="lg" keep-color :key="o" v-model="statement.bank" :val="o.value" :label="o.label" :color="o.color" @click="setStatementLink()"/> -->
      <q-radio class="q-px-md" size="lg" keep-color :key="o" v-model="statement.bank" :val="o.value" :label="o.label" :color="o.color" @click="storeBank()" />
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
const today = new Date().yyyymmdd()
// const initDate = ref(null)

const intraday = ref(null)
// emitter.on('intraday', (x) => intraday.value = x)
// emitter.on('init-date', (x) => { initDate.value = x; console.log(`initDate=${initDate.value}`)})

console.log(`-ST-bankstatementloader`)

const options = ([
  { label: 'Fidelity Credit Card', value: 'FidelCC', color: 'red' },
  { label: 'Fidelity Retirement Accounts', value: 'Fidelity', color: 'cyan' },
  { label: 'Chase', value: 'Chase', color: 'green' },
  { label: 'Bank of America', value: 'BOA', color: 'amber' },
  { label: 'Chase Brokerage Intraday', value: 'ChaseBkg', color: 'blue' },
  // { label: 'North American Company', value: 'NAC', color: 'lime' },
])
const statement = reactive({
  bank: localStorage.getItem('bank'),
  date: today
})
setBankStatementDate()

const compUrlname = computed({
  get: () => {
    let urlname = null
    if (statement.bank === 'FidelCC') urlname ='FIDELITY CREDIT CARD MONTHLY STATEMENT'
    else if (statement.bank === 'BOA') urlname = 'BANK OF AMERICA MONTHLY STATEMENT (Savings)'
    else if (statement.bank === 'Chase') urlname = 'CHASE MONTHLY STATEMENT'
    else if (statement.bank === 'ChaseBkg') urlname = 'Chase Brokerage Intraday'
    else if (statement.bank === 'Fidelity') urlname = 'FIDELITY MONTHLY STATEMENT (Roth)'
    else if (statement.bank === 'NAC') urlname = 'North American Company Yearly Statement'
    return urlname
  }
})
const compUrl = computed({
  get: () => {
    const yyyymm = compDate.value.yyyymm().replace('-', '')
    statement.date = compDate.value
    let url = null
    console.log(`-CP-compUrl compDate=${compDate.value} statement.date=${statement.date} statement.bank=${statement.bank} from compUrl`)
    if (statement.bank === 'FidelCC') url = '/docs/fidelity_credit_card/' + compDate.value + '.pdf'
    else if (statement.bank === 'BOA') url = '/docs/BOA/' + yyyymm + '_savings.pdf'
    else if (statement.bank === 'Chase') url = '/docs/Chase/' + yyyymm + '.pdf'
    else if (statement.bank === 'ChaseBkg') url = '/docs/Chase/' + yyyymm + '_bkg.pdf'
    else if (statement.bank === 'Fidelity') url = '/docs/Fidelity/' + yyyymm + '_roth.pdf'
    else if (statement.bank === 'NAC') url = '/docs/NAC/' + yyyymm + '.pdf'
    return url
  }
})
const compDate = computed({
  get: () => {
    const newDate = statement.date
    console.log(`-CP-compDate newDate=${newDate} statement.date=${statement.date} from compDate`)
    emitter.emit('new-date', newDate)
    return newDate
  }
})

function setIntraday (x) {
  intraday.value = x
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
function storeBank () {
  localStorage.setItem('bank', statement.bank)
  setBankStatementDate()
}

// function setStatementLink() {
//   let date = statement.date
//   // if (date == today) date = compDate.value
//   // let date = compDate.value
//   console.log(`-fn-setStatementLink bank=${statement.bank} calDate=${calDate.value}`)
//   let yyyymm = calDate == null ? today.yyyymm().replace('-', '') : '202505' //calDate.value.yyyymm().replace('-', '')
//   // let yyyymm = date.yyyymm().replace('-', '')
//   localStorage.setItem('bank', statement.bank)
//   if (statement.bank === 'FidelCC') { compUrl.value = 'docs/fidelity_credit_card/' + date + '.pdf'; compUrlname.value='FIDELITY CREDIT CARD MONTHLY STATEMENT' }
//   else if (statement.bank === 'BOA') { compUrl.value = 'docs/BOA/' + yyyymm + '_savings.pdf'; compUrlname.value = 'BANK OF AMERICA MONTHLY STATEMENT' }
//   else if (statement.bank === 'Chase') { compUrl.value = '/docs/Chase/' + yyyymm + '.pdf'; compUrlname.value = 'CHASE MONTHLY STATEMENT' }
//   // else if (statement.bank === 'ChaseBkg') { compUrl.value = '/docs/Chase/' + intraday.value + '_bkg.pdf'; compUrlname.value = 'Chase Brokerage Intraday' }
//   else if (statement.bank === 'ChaseBkg') { compUrl.value = '/docs/Chase/' + yyyymm + '_bkg.pdf'; compUrlname.value = 'Chase Brokerage Intraday' }
//   else if (statement.bank === 'Fidelity') { compUrl.value = '/docs/Fidelity/' + yyyymm + '_roth.pdf'; compUrlname.value = 'FIDELITY MONTHLY STATEMENT (IRA/ROTH)' }
//   else if (statement.bank === 'NAC') { compUrl.value = '/docs/NAC/' + yyyymm + '.pdf'; compUrlname.value = 'North American Company Yearly Statement' }
//   return closeOthersAndBuildApp()
// }
function setBankStatementDate() {
  console.log('-fn-setBankStatementDate()', statement)
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
  else if (date < 25 && /ChaseBkg/.test(bank)) month -= 1 // do last month if in the first 4 days of the month
  // else if (date <  16 && bank === 'Chase') month -= 2 // do last last month if in the first 4 days of the month
  // else if (date <  7 && bank === 'FidelCC') month -= 1 // do last last month if in the first 4 days of the month
  // else if (date >= 7 && bank === 'FidelCC') month -= 0 // do last last month if in the first 4 days of the month
  else if (date >= 7 && bank === 'FidelCC') month += 1 // do last last month if in the first 4 days of the month
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
  console.log(`-CK-setBankSatementDate bank=${bank} compDate=${ret}`)
  statement.date = ret
  return ret
}
function updDate(x) {
  statement.date = x
  console.log(`-CK-fn-updDate from Loader.vue statement.date=${statement.date}`)
}
function loadStateements() {
  $q.notify('Loading Statement ' + statement.bank)
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

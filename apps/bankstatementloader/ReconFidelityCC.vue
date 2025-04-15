<template>
<div v-if="bank==='FidelCC'" style="width:788px" class="text-white text-h6" :style="{ display:opened }">
  <q-card class="q-ml-xs bg-indigo-10" dark square flat style="border-top:solid yellow 1px;border-left:solid yellow 1px;border-right:solid yellow 1px">
    <q-card-actions align="between">
      <q-btn flat class="no-pointer-events text-h6" no-caps>Open Date: {{ openDate }}</q-btn>
      <q-btn flat class="no-pointer-events text-h6" no-caps>Close Date: {{ closeDate }}</q-btn>
      <q-btn flat class="text-amber text-h6" icon="chevron_left"  :disable="dueDate<='2019-04-03'" @click="prevDueDate()" />
      <q-btn flat class="no-pointer-events text-h6" no-caps>{{ dueDate }}</q-btn>
      <q-btn flat class="text-amber text-h6" icon="chevron_right" :disable="dueDate>=nextMonth3rd()" @click="nextDueDate()" />
    </q-card-actions>
  </q-card>
  <q-card dark square flat class="q-ml-xs bg-indigo-9" style="border-top:solid yellow 1px;border-left:solid yellow 1px;border-right:solid yellow 1px">
    <q-card-actions align="between">
      <q-btn flat class="no-pointer-events text-h6" no-caps>Prev Balance: {{ data.preb }}</q-btn>
      <q-btn flat class="no-pointer-events text-h6" no-caps>Last Payment: {{ data.paym }}</q-btn>
      <q-btn flat class="no-pointer-events text-h6" no-caps>Current Balance: {{ data.balc.replace(/\$/, '') }}</q-btn>
    </q-card-actions>
    <q-card-actions align="between" style="margin-top:-30px">
      <q-btn flat class="no-pointer-events text-h6 text-red-9" no-caps v-if="data.fees!=undefined && data.fees!=0">Fees: {{ data.fees }}</q-btn>
      <q-btn flat class="no-pointer-events text-h6" no-caps v-if="data.inst!=0">Interests: {{ data.inst }}</q-btn>
      <q-btn flat class="no-pointer-events text-h6" no-caps v-if="data.debt!=undefined && data.debt!=0">Debits: {{ data.debt }}</q-btn>
    </q-card-actions>
  </q-card>
  <q-card v-if="totalspend>0" dark square flat class="q-ml-xs" style="border-top:solid yellow 1px;border-left:solid yellow 1px;border-right:solid yellow 1px">
    <q-card-actions v-if="comp1stChk=='NO' || comp2ndChk=='NO' || creshow" class="bg-blue-10" align="between">
      <!-- <q-btn flat no-wrap class="no-pointer-events"> -->
      <q-btn flat no-wrap class="cursor-pointer" @click="calcPrevbalPayCredit(data.preb, data.paym, comp1stChk)">
        <div :class="{'bg-purple':comp1stChk=='NO'}">
          <div class="text-h6">{{ data.preb }} - {{data.paym}} ({{ comp1stChk }}) </div>
        </div>
      </q-btn>
      <q-btn flat no-wrap class="cursor-pointer" @click="calcTotalSpendCurrBal(totalspend, data.balc)">
        <div :class="{'bg-purple':comp2ndChk=='NO'}">
          <div class="text-h6">${{ totalspend }} - {{ data.balc }} = 
            <span v-if="credit_applied_to_this_month()">{{ data.cred }}</span>
           ({{ comp2ndChk }}) </div>
        </div>
      </q-btn>
    </q-card-actions>
  </q-card>
  <div class="q-ml-xs" style="border:solid yellow 1px">
    <div class="row q-px-xs bg-amber-10">
      <tr v-for="(p, i) in payment" :key=p.id><td :style="getStyle(i)">{{ p }}</td></tr>
    </div>
    <div v-for="c in credits" :key=c class="justify-center row">
      <tr v-for="(e, i) in c" :key=e class="bg-blue-10" :class="{'bg-blue-10':!c.noDBmatch, 'bg-red':c.noDBmatch}" @click="getMatchedSpends(c)">
        <div v-if="i<4 && /RETURN/i.test(e)" class="ellipsis" :style="getStyle(i)">{{ 'RETURN ' + e }}</div>
        <div v-else :class="{ 'cursor-pointer':i==4 }" :style="getStyle(i)">{{ e }}</div>
      </tr>
    </div>
    <div v-for="s in cspends" :key="s" class="justify-center row q-py-xs">
      <tr v-for="(e, i) in s" :key="e" :class="{'bg-teal-10':!s.noDBmatch, 'bg-red':s.noDBmatch}" @click="getMatchedSpends(s)">
        <div v-if="i<4" :class="{'cursor-pointer':i==4}" class="ellipsis" :style="getStyle(i)">{{ e }}</div>
        <div v-else :class="{'cursor-pointer':i==4}" class="ellipsis" :style="getStyle(i)">{{ e }}</div>
      </tr>
    </div>
  </div>
  <div class="row q-ml-xs" style="border-left:solid yellow 1px;border-right:solid yellow 1px;border-bottom:solid yellow 1px">
    <tr v-if="totalspend>0" class="bg-cyan-9">
      <td style="width:150px" class="q-pl-xs text-left">{{ cspends.length + credits.length }} Spendings</td>
      <td colspan="2" style="width:560px" class="text-right">Total Credit Card Spending {{data.open}} ~ {{data.clos}}</td>
      <td style="width:90px" class="text-right q-px-sm">{{ totalspend }}</td>
    </tr>
    <tr v-if="totalspend>0" class="bg-cyan-9">
      <td class="q-pl-sm q-pt-"><q-btn glossy round color="amber-10" icon="sort" @click="doSorting" /></td>
      <td class="q-pl-sm q-pt-"><q-btn glossy round color="amber-10" icon="checklist_rtl" @click="getCreditCardSpendings" /></td>
      <td class="q-pl-sm q-pt-"><q-btn glossy round color="amber-10" icon="verified" @click="doMatchStatementAndExpenseRecords" /></td>
      <td style="width:710px" class="text-right">Total Amount Due on {{ dueDate }}</td>
      <td v-if="spendAndCredits>0" style="width:90px" class="text-right q-px-sm">{{ spendAndCredits }}</td>
      <td v-else style="width:90px" class="text-right q-px-sm">{{ totalspend }}</td>
    </tr>
  </div>
</div>
<MatchedSpendsDialog ref=refMatchedSpendsDialog @set-post-date="getCreditCardData" />
<CCardReconcileSheet />
<InfoDisplay />
</template>
<script setup>
import { ref, computed, onMounted } from "vue";
import { dayFunctions } from "src/composables/dayFunctions";
const { getDateGap } = dayFunctions()
import { axiosFunctions } from "src/composables/axiosFunctions";
import { libFunctions } from "src/composables/libFunctions";
import emitter from "tiny-emitter/instance";
import MatchedSpendsDialog from './MatchedSpendsDialog'
import CCardReconcileSheet from '../exp/CCardReconcileSheet'
import InfoDisplay from '../src/components/InfoDisplay'
const { gaxios, paxios } = axiosFunctions()
const { $q } = libFunctions()

const opened = ref('')
const bank = ref(null)
const oneDay = ref(1000 * 60 * 60 * 24)
const data = ref({ preb: "", paym: "", cred: "", balc: "", purchases: [], clos: "" })
const creshow = ref(true)
const statement = ref({})
const dueDate = ref(null)
const credited = ref(0)
const credit4next = ref(0)
const totalspend = ref(0)
const cspends = ref(null)
const openDate = ref(null)
const closeDate = ref(null)
const refMatchedSpendsDialog = ref(null)

var lookingupSpend = null
var payment = null
var credits = null
var fCCardSpendings = []
var postDate = null
var tranDate = null
// const yearOpenDate = openDate.value.substring(0, 4) 
var prevPayDate = null
const spendAndCredits = ref(0)

onMounted(() => refMatchedSpendsDialog)
// onMounted(() => spendAndCredits.value = getTotalSpendAndCredits())

console.log("-ST-ReconFidelityCC")
emitter.on('bankstatementloader-getCreditCardData', (x) => setCreditCardData(x))
emitter.on('open-ReconFidelityCC', (statement) => openIt(statement))
// emitter.on('close-ReconFidelityCC', () => { opened.value = 'none'; console.log(`-CK-closeFCC opened=${opened.value}`) })
emitter.on('close-ReconFidelityCC', () => opened.value = 'none')
emitter.on('bankstatementloader-getMatchedSpends', (da) => setMatchedSpends(da))
// emitter.on('user-confirmed', (act) => setPostDate(act))

//== function sections
function calcTotalSpendCurrBal (totspend, currbal) {
  let tspt = totspend.replace(/[$|,]/g, '')
  let cbal = currbal.replace(/[$|,]/g, '')
  // console.log(`-CK-calcTotalSpendCurrBal totalspend=${tspt} cbal=${cbal}}`)
  let tsptcbal = (tspt - cbal).toFixed(2)
  console.log(`-CK-totalspendCurrBal=${tsptcbal}`)
  let tit = 'Calc Total Spending - Current Balance'
  let msg = `${tspt} - ${cbal} = ${tsptcbal}`
  emitter.emit('open-InfoDisplay', tit, msg)
}
function calcPrevbalPayCredit (preb, paym, credit) {
  let prb = preb.replace(/[$|,]/g, '')
  let pym = paym.replace(/[$|,]/g, '')
  // console.log(`-CK-calcPrevbalPayCredit preb=${prb} paym=${pym} credit=${credit}`, credits.map(p => p[4]))
  let prevbalPayCredit = (prb - pym - credit).toFixed(2)
  // console.log(`-CK-prevbalPayCredit=${prevbalPayCredit}`)
  let tit = 'Calc Prev Balance - Payment - Credit'
  let msg = `${prb} - ${pym} - ${credit} = ${prevbalPayCredit}`
  if (credits.length > 0) {
    let mxg = credits.map(p => p[4]).join(' + ')
    msg += '<br>' + mxg + ' = ' + credit
  }
  emitter.emit('open-InfoDisplay', tit, msg)
}
function credit_applied_to_this_month () {
  return data.value.cred_date>=data.value.clos
}
function doMatchStatementAndExpenseRecords () {
  if (fCCardSpendings.length === 0) return getCreditCardSpendings()
  const dbRecords = fCCardSpendings.map(p => p.cost)
  const stRecords = cspends.value.map(p => p[4])
  console.log(`-CK-doMatchStatementAndExpenseRecords`, stRecords, dbRecords)
  const tit = 'Match Statement vs Spending Records'
  let msg = '<b class="q-py-sm q-pl-xl text-h4 text-green-3">Perfect Match</b>'
  if (stRecords.length === dbRecords.length && stRecords.every((value, index) => value === dbRecords[index])) {
    let mpair = stRecords.map((p, idx) => '(' + p + '~' + dbRecords[idx] + ')')
    msg += '<br />' + mpair.join(', ')
    emitter.emit('open-InfoDisplay', tit, msg) 
  } else {
    let longA = stRecords
    let shrtA = dbRecords
    if (stRecords.length < dbRecords.length) {
      longA = dbRecords
      shrtA = stRecords
    }
    let mpair = longA.map((p, idx) => '(' + p + '~' + (shrtA[idx] == undefined ? 'NoData' : shrtA[idx]) + ')')
    msg = '<p class="q-pa-xs text-amber">Statement Not Match Spending Records in DB</p>'
    msg += '<br />' + mpair.join(', ')
    emitter.emit('open-InfoDisplay', tit, msg) 
  }
}
emitter.on('expense-getCreditCardSpendings', (da) => { fCCardSpendings = da.ccdata; showCCardReconcileSheet() })
function getCreditCardSpendings () {
  // console.log(`-CK-getCreditCardSpendings`, cspends.value)
  if (fCCardSpendings.length > 0) {
    showCCardReconcileSheet()
  }
  const path = process.env.API + '/expense/getCreditCardSpendings/' + openDate.value.addDays(-1) + '/' + closeDate.value + '/' + dueDate.value
  gaxios(path)
}
function showCCardReconcileSheet () {
  const ccpay = data.value.balc.replace(/[$|,]/g, '')
  const dueday = dueDate.value
  const bedays = [openDate.value, closeDate.value]
  // console.log(`-fn-showCCardReconcileSheet ccpay=${ccpay} dueday=${dueday} bedays=${bedays}`, fCCardSpendings)
  emitter.emit('open-CCardReconcileSheet', fCCardSpendings, ccpay, dueday, bedays)
}
function setPostTranDate () {
  let openYear = openDate.value.substring(0, 4) 
  let closeYear = closeDate.value.substring(0, 4)
  let trdate = null
  if (openYear < closeYear) {
    const openMonth = lookingupSpend[0].substring(0, 2)
    const closeMonth = lookingupSpend[1].substring(0, 2)
    postDate = (openMonth == 12 ? openYear : closeYear) + '-' + lookingupSpend[0].replace('/', '-')
    tranDate = closeMonth == 12 ? openYear : closeYear + '-' + lookingupSpend[1].replace('/', '-')
    console.log(`-fn-getMatchedSpends postDate=${postDate} openYear=${openYear} closeYear=${closeYear} openMonth=${openMonth} closeMonth=${closeMonth}`, lookingupSpend)
  } else {
    const year = openYear
    postDate = year + '-' + lookingupSpend[0].replace('/', '-')
    tranDate = year + '-' + lookingupSpend[1].replace('/', '-')
  }
}
function getMatchedSpends (lookupspend) {
  lookingupSpend = lookupspend
  setPostTranDate()
  // let bedate = openDate.value
  // let afdate = closeDate.value
  const cost = /RETURN/.test(lookingupSpend[3]) ? -lookingupSpend[4] : lookingupSpend[4]
  const path = process.env.API + '/bankstatementloader/getMatchedSpends'
  // console.log(`-fn-getMatchedSpends postDate=${postDate} bedate=${bedate} tranDate=${tranDate} afdate=${afdate} cost=${cost}`, lookupspend)
  const inData = {postDate:postDate, openDate:openDate.value.addDays(-1), closeDate:closeDate.value, cost:cost}
  paxios(path, inData)
}
function setMatchedSpends (da) {
  console.log(`-fn-setMatchedSpends matched.length=${da.matchedSpends.length}`, da.matchedSpends)
  // let dgap = 10
  let matched = da.matchedSpends[0]
  // da.matchedSpends.forEach(p => {
  //   if (Math.abs(getDateGap(p.purchasedate, postDate)) < dgap) {
  //     // dgap = Math.abs(getDateGap(p.purchasedate, postDate))
  //     if (p.postdate != postDate) matched = p
  //   }
  // })
  // emitter.emit('open-MatchedSpendsDialog', postDate, matched, lookingupSpend)
  if (matched != null) {
    refMatchedSpendsDialog.value.openIt(postDate, matched, lookingupSpend)
  } else {
    $q.dialog({
      title: 'No Matched Spends in the database. Please check your dates and the cost.',
    })
  }
}
// function setMatchedSpends (da) {
//   console.log(`-fn-setMatchedSpends`, da.matchedSpends)
//   let dgap = 10
//   let matched = null
//   da.matchedSpends.forEach(p => {
//     if (Math.abs(getDateGap(p.purchasedate, postDate)) < dgap) {
//       // dgap = Math.abs(getDateGap(p.purchasedate, postDate))
//       if (p.postdate != postDate) matched = p
//     }
//   })
//   // emitter.emit('open-MatchedSpendsDialog', postDate, matched, lookingupSpend)
//   if (matched != null) refMatchedSpendsDialog.value.openIt(postDate, matched, lookingupSpend)
// }
function setPaymentCreditsSpends () {
  openDate.value = data.value.open.yyyymmdd()
  closeDate.value = data.value.clos.yyyymmdd()
  let oYear = openDate.value.substring(0, 4)
  // console.log(`-fn-setPaymentCreditsSpends openDate=${openDate.value} closeDate=${closeDate.value}`)
  let pdata = data.value.purchases
  // payment = pdata.filter(p => p[2] === 'MTC')[0]
  payment = pdata.filter(p => /PAYMENT THANK YOU|PAYMENT REVERSAL DEBIT ADJUSTMENT/.test(p[3]))[0]
  prevPayDate = payment[0] + '/' + oYear
  // credits = pdata.filter(p => /RETURN|REVERSAL OF RETURNED PAYMENT FEE/i.test(p[3]))
  credits = pdata.filter(p => /RETURN|RETURNED PAYMENT FEE/i.test(p[3]))
  cspends.value = pdata.filter(p => p[2] !== 'MTC' && !/RETURN|PAYMENT\s+REVERSAL\s+DEBIT\s+ADJUSTMENT/i.test(p[3]))
  totalspend.value = cspends.value.reduce((total, next) => total + parseFloat(next[4]), 0).toFixed(2)
  console.log(`-CK-prevPayDate=${prevPayDate}`, data.value)
  console.log('-CK-returns', credits)
  // console.log('-CK-cspends', cspends.value)
}
function sortByPostDate () {
  // console.log(`-fn-sortByPostDate sortby: ${data.value.sort}`, cspends.value)
  data.value.sort ='pdate'
  cspends.value.sort((a, b) => { return a[0] < b[0] ? -1 : 1 })
}
function sortBySpending () {
  data.value.sort ='spend'
  cspends.value.sort((a, b) => { return parseFloat(a[4]) - parseFloat(b[4]) })
}
function doSorting () {
  // console.log(`-fn-doSorting sortby: ${data.value.sort}`)
  data.value.sort === 'spend' ? sortByPostDate() : sortBySpending()
}
function openIt (stmt) {
  if (stmt.bank !== 'FidelCC') {
    opened.value = 'none'
    return
  }
  // console.log('-fn-openIt', stmt)
  opened.value = ''
  statement.value = stmt
  bank.value = stmt.bank
  dueDate.value = stmt.date
  getCreditCardData()
}
function setCreditCardData (da) {
  console.log("-fn-setCreditCardData", da)
  if (da.status === 'NO_FILE') {
    $q.dialog({
      title: 'The PDF File Not Exists, Get File in First',
      message: da.info
    })
  } else if (da.status != 'OK') {
    $q.dialog({
      title: 'Backend Error',
      message: da.status
    })
    return
  }
  data.value = da.data;
  // console.log('-setCreditCardData-match', this.match)
  // let purchase = 0;
  data.value.purchases.forEach((p, i) => {
    p.noDBmatch = da.data.noDBmatch[i]
    const cost = p[4].slice(1).replace(/,/g, "")
    // if (p[2] !== "MTC" && p[3].indexOf("RETURN") < 0) purchase += parseFloat(cost === undefined ? 0 : cost);
  })
  // const cost = data.value.fees
  // console.log(`fees=${cost}`)
  creditsDistr()
  setPaymentCreditsSpends()
  // // data.value.purchases.forEach((p, i) => p[4] = p[4].replace(/\$/, '').replace(/,/g, ''))
  // // data.value.purchases.forEach((p, i) => p[4] = p[4].replace(/,/g, ''))
  // data.value.purchases.forEach(p => { 
  //   if (p[3].indexOf("RETURN") >= 0) {
  //     // console.log(`return line p[3]=${p[3]}`)
  //     let refundComp = p[3].replace('RETURN', '').substring(0, 30)
  //     p[3] = refundComp + ' ··· ' + 'RETURN'
  //   } 
  // })
  // spendAndCredits.value = getTotalSpendAndCredits()
  // sortBySpending()
}
function getPropDate(mmdd) {
  const oyear = data.value.open.split("/")[2];
  let pyear = oyear;
  const odtm = new Date(data.value.open).getTime();
  const mmddyy = new Date(mmdd + "/" + oyear).getTime();
  if (Math.abs(odtm - mmddyy) > 32 * oneDay.value) pyear++;
  const ppDate = new Date(mmdd + "/" + pyear);
  // console.log('-fn-getPropDate', this.data.open, mmdd, ppDate)
  return ppDate.getTime();
}
function creditsDistr() {
  credited.value = 0
  credit4next.value = 0
  // const MTCline = compPurchases.value.filter((p) => p[2] === "MTC")[0]
  const MTClinex = compPurchases.value.filter((p) => /PAYMENT THANK YOU/.test(p[3]))
  console.log('-fn-creditsDistr',MTClinex, compPurchases.value)
  if (MTClinex == undefined || MTClinex.length == 0) {
    const tit = 'No "PAYMENT THANK YOU" line'
    const msg = 'Please check the statement'
    emitter.emit('open-InfoDisplay', tit, msg)
    return
  }
  const MTCline = MTClinex[0]
  const paymmdd = MTCline[0]
  const payDate = getPropDate(paymmdd)
  // const mcreds = compPurchases.value.filter((p) => p[3].indexOf("RETURN") >= 0)
  const mcreds = compPurchases.value.filter((p) => /REVERSAL OF RETURNED PAYMENT FEE/.test(p[3]))
  mcreds.forEach((p) => {
    const creDate = getPropDate(p[0])
    const creditx = parseFloat(p[4].slice(1).replace(',', ''))
    if (payDate > creDate && (payDate - creDate) > 2 * oneDay.value) credited.value += creditx
    else credit4next.value += creditx
  })
  credited.value -= parseFloat(data.value.inst.replace("$", ""))
  credited.value = credited.value.toFixed(2)
  credit4next.value = credit4next.value.toFixed(2)
}
function getCreditCardData() {
  // console.log("-fn-getData");
  const path = process.env.API + "/bankstatementloader/getCreditCardData/" + dueDate.value + "/" + bank.value
  gaxios(path)
}
function getStyle (i) {
  if (i === 0) return "width:70px";
  else if (i === 1) return "width:70px";
  else if (i === 2) return "width:60px";
  else if (i === 3) return "width:480px";
  else if (i === 4) return "width:90px; text-align:right";
}
function setDueDate(x) {
  dueDate.value = x;
  // console.log("-fn-setDueDate", x);
  const dx = parseInt(x.split("-")[2]);
  if (dx !== 3) {
    $q.dialog({
      title: "The due date must be on the third date(i.e. 03) of the month",
    })
    return
  }
  getCreditCardData()
}
function the3rdDayOfMonth() {
  // const thedate = statement.value.date
  const thedate = dueDate.value
  let [year, mont, date] = thedate.split("-");
  // console.log("-fn-nextMonth3rd", year, mont, date);
  mont = parseInt(mont)
  if (date === 1) return year + "-" + mont + "-" + (date + 2);
  else if (date === 2) return year + "-" + mont + "-" + ++date;
  else if (date === 3) return today;
  else if (mont === 12) return ++year + "-01-03";
  else return year + "-" + ++mont + "-03";
}
function nextMonth3rd () {
  let nextMon3rd = null
  const today = new Date().yyyymmdd()
  let [year, mont, date] = today.split("-")
  // console.log(`-CK0-nextMonth3rd=${nextMon3rd} dueDate=${dueDate.value} today=${today} mont=${mont}`)
  if (date === 1) nextMon3rd = year + "-" + mont + "-" + (date + 2)
  else if (date === 2) nextMon3rd = year + "-" + mont + "-" + ++date
  else if (date === 3) nextMon3rd = today;
  else if (mont === 12) nextMon3rd = ++year + "-01-03"
  else {
    ++mont
    if (mont < 10) mont = '0' + mont
    nextMon3rd = year + "-" + mont + "-03"
  }
  return nextMon3rd
}
function prevDueDate() {
  const x = dueDate.value.split("-")
  const dd = "03"
  let mm = parseInt(x[1])
  mm--
  let yy = parseInt(x[0])
  if (mm === 0) {
    mm = 12
    yy--
  }
  if (mm < 10) mm = "0" + mm;
  const duedd = yy + "-" + mm + "-" + dd;
  console.log("-fn-prevDueDate", this.dueDate);
  dueDate.value = duedd;
  emitter.emit("new-date", duedd);
  getCreditCardData()
  fCCardSpendings = []
}
function nextDueDate() {
  console.log(`-fn-nextDueDate dueDate=${dueDate.value}`)
  const x = dueDate.value.split("-")
  const dd = "03"
  let mm = parseInt(x[1])
  mm++;
  let yy = parseInt(x[0])
  if (mm === 13) {
    mm = 1
    yy++
  }
  if (mm < 10) mm = "0" + mm
  const duedd = yy + "-" + mm + "-" + dd
  dueDate.value = duedd
  console.log(`-CK-nextDueDate=${dueDate.value}`)
  emitter.emit('new-date', duedd)
  getCreditCardData()
  fCCardSpendings = []
}
function getTotalSpendAndCredits () {
  if (data.value.crDates == undefined) return totalspend
  let totalspendAndCredits = totalspend.value
  data.value.crDates.forEach((d, i) => { if (d >= prevPayDate) totalspendAndCredits -= data.value.credits[i] })
  return Math.abs(totalspendAndCredits).toFixed(2)
}
const comp1stChk = computed(() => {
  const pb = parseFloat(data.value.preb.replace(/[\$|,]/g, ""))
  const py = parseFloat(data.value.paym.replace(/[\$|,]/g, ""))
  let ptotalspendAndCredits = pb - py
  if (data.value.crDates == undefined) return ptotalspendAndCredits.toFixed(2)
  data.value.crDates.forEach((d, i) => { if (d < prevPayDate) ptotalspendAndCredits -= data.value.credits[i] })
  console.log(`-CK-credits crDates=${data.value.credits} crDate=${data.value.crDates}`)
  return Math.abs(ptotalspendAndCredits) < 0.001 ? "OK" : "NO"
})
const comp2ndChk = computed(() => {
  const balc = parseFloat(data.value.balc.replace(/[\$|,]/g, ""))
  if (credit_applied_to_this_month()) return Math.abs(totalspend.value - balc - data.value.cred - data.value.fees) < 0.001 ? "OK" : "NO"
  let totalspendAndCredits= totalspend.value - balc
  if (data.value.crDates == undefined) return totalspendAndCredits
  data.value.crDates.forEach((d, i) => { if (d >= prevPayDate) totalspendAndCredits -= data.value.credits[i] })
  totalspendAndCredits = Math.abs(totalspendAndCredits)
  console.log(`-CK2-comp2ndChk totalspend=${totalspend.value} data.balc=${data.value.balc} cred_date=${data.value.cred_date} data.cred=${data.value.cred}`, getTotalSpendAndCredits())
  return totalspendAndCredits < 0.001 ? "OK" : "NO"
})
const compPurchases = computed(() => {
  return data.value.purchases
})
const compChkPaymOK = computed(() => {
  if (data.value.purchases.length === 0) return false
  const dpaym = parseFloat(data.value.paym.replace(/[\$|,]/, ""))
  const x = data.value.purchases.filter((p) => p[2] === "MTC")[0]
  const xpaym = parseFloat(x[4].replace(/[\$|,]/, ""))
  return dpaym === xpaym;
})
const compChkCredOK = computed(() => {
  if (compPurchases.value.length === 0) return false;
  const dcred = parseFloat(data.value.cred.replace(/[\$|,]/, ""));
  const x = compPurchases.value.filter((p) => p[3].indexOf("RETURN") >= 0);
  if (x === undefined) return true; // no credit
  // console.log('-compChkCredOK', compPaymDate)
  let xcred = 0;
  x.forEach((a) => (xcred += parseFloat(a[4].replace(/[\$|,]/, ""))));
  return Math.abs(dcred - xcred) < 0.001;
})
</script>
<style>
td>b:hover {
  background:cyan;
  color:indigo;
}
</style>

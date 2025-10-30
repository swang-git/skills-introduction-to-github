<template>
<div style="display:flex;justify-content:center">
<q-page expend position="top" style="width:100%">
  <div class="doc-container" :style="isIPad ? 'margin:-10px 0 0 0x' : 'margin:0'">
    <div class="no-wrap row justify-center text-h5 text-lime">
      <div>Holdings and Activities for {{ bank }} in</div>
      <div><q-btn @click="showYearMonthPad" dense flat class="text-h5"><span style="margin-top:-17px">{{year}}年{{month}}月</span></q-btn></div>
      <div class="text-h6 q-pl-sm" v-html="getAnnuityLink()" />
    </div>
    <div v-for="a in accounts" :key=a class="q-px-sm q-py-xs">
      <div class="text-white text-subtitle1">
        <table class="q-px-xs h-border">
          <q-tr class="text-cyan-4 text-right">
            <th colspan="5" class="text-h6">{{ a.name }} ~ {{ a.num }} <span class="text-amber">Holdings</span></th>
            <th colspan="2" class="text-center" v-html="getStatementLink(a.num)"></th>
          </q-tr>
          <q-tr>
            <th style="width:14.2%" class="q-pl-xs text-left">SYMBOL</th>
            <th style="width:14.2%" class="text-right">S-BALANCE</th>
            <th style="width:14.2%" class="text-right">QUANTITY</th>
            <th style="width:14.2%" class="text-right">PRICE</th>
            <th style="width:14.2%" class="text-right">E-BALANCE</th>
            <th style="width:14.2%" class="text-right">GAIN/LOSS</th>
            <th style="width:14.2%" class="q-pr-xs text-right">EAI</th>
          </q-tr>
          <q-tr v-for="p in compAccountHoldings[a.num]" :key="p" :class="{'bg-cyan-9 cursor-pointer text-lime':isCash(p)}" @click="checkCashActv(a, p)">
            <td style="width:14.2%" class="q-pl-xs text-left">{{ p.symbol }}</td>
            <td style="width:14.2%" class="text-right">{{ p.start_balance }}</td>
            <td style="width:14.2%" class="text-right">{{ p.quantity }}</td>
            <td style="width:14.2%" class="text-right">{{ fmtcy(p.price) }}&nbsp;</td>
            <td style="width:14.2%" class="text-right">{{ p.end_balance }}</td>
            <td style="width:14.2%" v-if="p.diff>=0" class="text-green-3 text-right">{{ fmtcy(p.diff) }}</td>
            <td style="width:14.2%" v-else class="text-pink-9 text-right">{{ fmtcy(-p.diff) }}</td>
            <td style="width:14.2%" class="q-pr-xs text-right">{{ p.eai }}</td>
          </q-tr>
          <q-tr v-if="compAccountHoldings[a.num].length>1"><td colspan="7"><hr class="q-px-xs"></td></q-tr>
          <q-tr v-if="compAccountHoldings[a.num].length>1" class="text-cyan-2" style="font-size:20px">
            <td style="width:14.2%" class="q-pl-xs text-white text-center">TOTAL</td>
            <td style="width:14.2%" class="text-right text-white text-no-wrap">Start Balance</td>
            <td style="width:14.2%" class="text-right">{{ fmtcy(compAccountStartBalances[a.num]) }}</td>
            <td style="width:14.2%" class="text-right text-white text-no-wrap">End Balance</td>
            <td style="width:14.2%" class="text-right text-yellow">{{ fmtcy(compAccountEndBalances[a.num]) }}</td>
            <td style="width:14.2%" v-if="compBalanceDiffs[a.num]>=0" class="text-green-3 text-right">{{ fmtcy(compBalanceDiffs[a.num]) }}</td>
            <td style="width:14.2%" v-else class="text-red text-right">{{ fmtcy(-compBalanceDiffs[a.num]) }}</td>
            <td style="width:14.2%" class="text-right"><q-btn glossy class="bg-amber-10" label="EAI" @click="showAcctEAI(a.num)" /></td>
            <!-- <td style="width:14.2%" class="text-right">{{ fmtcy(compTotalEai[a.num]) }} </td> -->
          </q-tr>
        </table>
      </div>
      <div v-if="a.show" class="text-white text-subtitle1">
        <div class="text-cyan text-center q-pt-sm a-border">
          <div class="row justify-evenly text-lime text-bold text-no-wrap">
            <div v-if="compAA[a.num+'ccd']>0" class="cursor-pointer q-pt-xs" @click="getDICSummary('ccd', a)">CREDIT CARD REDEEMED</div>
            <div v-if="compAA[a.num+'int']>0" class="cursor-pointer q-pt-xs" @click="getDICSummary('int', a)">INTEREST</div>
            <div class="text-h6 text-cyan">{{ a.name }} ~ {{ a.num }}
              <span class="text-amber q-pl-md">Activities</span>
            </div>
            <div v-if="compAA[a.num+'dvd']>0" class="cursor-pointer q-pt-xs" @click="getDICSummary('dvd', a)">DIVIDEND</div>
          </div>

          <div class="row justify-evently no-wrap text-white" v-for="t in compAA[a.num]" :key="t.t">
            <div style="width:12%" class="text-left q-pl-sm text-no-wrap">{{ t.date }}</div>
            <div style="width:10%" class="text-center">{{ t.secu }}</div>
            <div style="width:68%" class="text-left text-no-wrap overflow-auto ellipsis">{{ t.desc }}</div>
            <div style="width:12%" class="text-right q-px-sm">{{ fmtcy(t.amnt) }}</div>
          </div>
          <div v-if="compAA[a.num + 'dvd']>0" class="row text-body1 text-bold">
            <hr style="width:98.8%" />
            <div class="text-right col-10 text-cyan-1 text-uppercase">Total Dividend Received:</div>
            <div class="text-right col-2 q-pr-sm text-cyan-1">$ {{ fmtcy(compAA[a.num + 'dvd']) }}</div>
          </div>
          <div v-if="compAA[a.num + 'int']>0" class="row text-body1 text-bold">
            <div class="full-width"><hr /></div>
            <div class="text-right col-10 text-cyan-1 text-uppercase">Total interest &nbsp; earned:</div>
            <div class="text-right col-2 q-pr-sm text-cyan-1">$ {{ fmtcy(accountActivity[a.num + 'int']) }}</div>
          </div>
          <div v-if="compAA[a.num + 'ccd']>0" class="row text-body1 text-bold">
            <div class="full-width"><hr /></div>
            <div class="text-right col-10 text-cyan-1 text-uppercase">Total Credit Card Redeemed:</div>
            <div class="text-right col-2 q-pr-sm text-cyan-1">$ {{ fmtcy(compAA[a.num + 'ccd']) }}</div>
          </div>
        </div>
      </div>
    </div>
    <DICSummaryPad ref="refDICSummaryPad" />
    <YearMonthPad ref="refYearMonthPad" @upd-ym="newYM" />
    <CheckingPad ref="refCheckingPad" />
    <InfoDisplay />
    <ConfirmDialog @user-confirmed="showPriorMonthData" />
  </div>
</q-page>
</div>
</template>
<script setup>
import { ref, computed, onMounted } from 'vue'
import emitter from 'tiny-emitter/instance'
import { libFunctions } from '../src/composables/libFunctions'
const { buildApp, isDesk, isIPad, fmtcy } = libFunctions()
import { axiosFunctions } from '../src/composables/axiosFunctions'
const { gaxios } = axiosFunctions()
import DICSummaryPad from './DICSummaryPad'
import YearMonthPad from '../src/components/YearMonthPad'
import CheckingPad from './CheckingPad'
import InfoDisplay from '../src/components/InfoDisplay'
import ConfirmDialog from '../src/components/ConfirmDialog'

const refDICSummaryPad = ref(null)
const refYearMonthPad = ref(null)
const refCheckingPad = ref(null)
onMounted(() => {
  refDICSummaryPad
  refYearMonthPad
  refCheckingPad
})
const holdings = ref([])
const accounts = ref([])
const allAccounts = ref([])
const transActivity = ref([])
const accountActivity = ref({})
const accountsStatus = ref([])
const openedacctNum = ref(null)
const openedacctName = ref(null)
const actv = ref([])
const tag = ref(null)
const anum = ref(null)
const pos = ref({})
const dicLists = ref(null)

var bank = 'Fidelity'
var year = (new Date()).yyyymmdd().substring(0, 4)
var month = (new Date()).yyyymmdd().split('-')[1] - 1
const cashSec = ['SPAXX', 'FNJXX', 'FDRXX', 'FZDXX', 'QPCBQ', 'QBYIQ', 'CORE']

//== main
console.log("-ST-Holdings yyyymmdd=", new Date().yyyymmdd())
buildApp('月报分析', 'Holding Details')
if (month == 0) {
  month = 12
  year -= 1
}
getHoldings()

//== function sections
function showYearMonthPad () {
  emitter.emit('open-YearMonthPad', year)
}
function showPriorMonthData () {
  month--
  getHoldings()
}
function showAcctEAI (anum) {
  const xx = compAccountHoldings.value[anum]
  const x = xx.filter(p => p.ey != null).map(p => [p.symbol, p.ey, p.eai ])
  // console.log(`-CK-showAcctEAI for account ${anum}`, x)
  const tit = 'Account ' + anum
  var msg = '<table><tr>'
  msg += '<td colspan="2"">Estimate Annual Income:</td><td>' + fmtcy(compTotalEai.value[anum]) + '</td></tr>'
  x.forEach(p => {
    msg += '<td>' + p[0] + '</td>'
    msg += '<td class="text-left">' + p[1] + '</td>'
    msg += '<td class="text-right">' + p[2] + '</td></tr>'
  })
  msg += '</table>'

  emitter.emit('open-InfoDisplay', tit, msg)
}
function isCash (p) {
  return cashSec.includes(p.symbol)
}
function checkCashActv (a, holding) {
  showActivity(a)
  if (!isCash(holding)) return
  const symbol = holding.symbol
  const acct = a.num
  const diff = holding.diff
  let acctActv = actv.value.filter(p => p.account_num === acct)
  let actvxsum = 0
  let actvx = []
  // console.log(`symbol=${symbol} account=${acct} holding=`, holding)
  if (symbol === 'SPAXX') {
    // actvx = acctActv.filter(p => p.security===symbol && /(Sold|Bought|Reinvestment)\s+CASH/.test(p.description)).map(p => p.amount)
    actvx = acctActv.filter(p => p.security===symbol && /CASH/.test(p.description)).map(p => p.amount)
    actvx.push(-diff)
    const x = actvx.reduce((total, next) => total + parseFloat(next), 0)
    actvxsum = x < 0.001 ? 0 : x
  } else if (symbol === 'FNJXX') {
    actvx = acctActv.filter(p => p.security===symbol && /Reinvestment/.test(p.description)).map(p => p.amount)
    const x = actvx.reduce((total, next) => total + parseFloat(next), 0)
    actvxsum = x < 0.001 ? 0 : x
    actvx.push(diff)
  } else if (symbol === 'FDRXX') {
    // actvx = acctActv.filter(p => p.security===symbol && /(Sold|Bought|Reinvestment)\s+CASH/.test(p.description)).map(p => p.amount)
    actvx = acctActv.filter(p => p.security===symbol && /CASH/.test(p.description)).map(p => p.amount)
    actvx.push(-diff)
    const x = actvx.reduce((total, next) => parseFloat(total) + parseFloat(next), 0)
    actvxsum = Math.abs(x) < 0.001 ? 0 : x
  } else if (symbol === 'FZDXX') {
    // actvx = acctActv.filter(p => p.security===symbol && /REDEEMED\s+TO\s+COVER\s+A\s+|Reinvestment/.test(p.description)).map(p => p.amount)
    // actvx = acctActv.filter(p => p.security===symbol && /Reinvestment|Dividend Received/.test(p.description)).map(p => p.amount)
    actvx = acctActv.filter(p => p.security===symbol && /Reinvestment/.test(p.description)).map(p => p.amount)
    actvx.push(diff)
    const x = actvx.reduce((total, next) => parseFloat(total) + parseFloat(next), 0)
    actvxsum = Math.abs(x) < 0.001 ? 0 : x
  } else if (symbol === 'QPCBQ') {
    actvx = acctActv.filter(p => p.security===symbol && /You\s+Bought\s+CASH/.test(p.description)).map(p => p.amount)
    actvx.push(-diff)
    const x = actvx.reduce((total, next) => parseFloat(total) + parseFloat(next), 0)
    actvxsum = Math.abs(x) < 0.001 ? 0 : x
  } else if (symbol === 'QBYIQ') {
    actvx = acctActv.filter(p => p.security===symbol && /You\s+Bought\s+CASH/.test(p.description)).map(p => p.amount)
    actvx.push(-diff)
    const x = actvx.reduce((total, next) => parseFloat(total) + parseFloat(next), 0)
    actvxsum = Math.abs(x) < 0.001 ? 0 : x
  } else if (symbol === 'CORE') {
    // actvx = acctActv.filter(p => p.security===symbol && /You\s+Bought\s+CASH/.test(p.description)).map(p => p.amount)
    actvx = acctActv.filter(p => p.security===symbol && /^CASH\s+You\s+(Bought|Sold)/.test(p.description)).map(p => p.amount)
    actvx.push(-diff)
    const x = actvx.reduce((total, next) => parseFloat(total) + parseFloat(next), 0)
    actvxsum = Math.abs(x) < 0.001 ? 0 : x
  }
  const actvxstr = actvx.join('+').replaceAll(/\+\-/g, ' - ').replaceAll(/\+/g, ' + ')
  // console.warn(`-wn-X symbol=${symbol} account=${acct} diff=${diff} asum=${actvsum} astr=${actvstr} actv`, actv)
  if (actvxsum === 0) {
    const tit = symbol + ' ACTIVITIES CHECKING'
    const tip = 'Sum ' + symbol + ' Activities and ' + symbol + ' Holding Diff'
    refCheckingPad.value.openIt(actvxstr, actvxsum, tit, tip, 'OK')
  } else {
    const tit = symbol + ' ACTIVITIES CHECKING'
    const tip = 'Sum ' + symbol + ' Activities =/= ' + symbol + ' Holding Diff'
    refCheckingPad.value.openIt(actvxstr, actvxsum, tit, tip, 'CHECK')
  }
}
function getDICSummary (tg, a) {
  console.log('-fn-getDICSummary(tag, anum)', tg, a.num)
  if (dicLists.value !== null && dicLists.value[tg][0].year === year && dicLists.value[tg][0].month === month) {
    const xlist = dicLists.value[ta]
    refDICSummaryPad.value.openIt(tg, year, month, a.num, xlist)
  } else {
    tag.value = tg
    anum.value = a.num
    getDICLists(a.num)
  }
}
emitter.on('holdings-getDICLists', (x) => setDICLists(x))
function setDICLists (da) {
  console.log(`-CK-setDICLists Dividend, Interest, CCardRedeem anum=${anum.value}`, da)
  dicLists.value = da
  const xlist = da[tag.value]
  refDICSummaryPad.value.openIt(tag.value, year, month, anum.value, xlist)
}
function getDICLists (anu) {
  console.log('-fn-getDICLists()', anu)
  // acctNum.value = anum
  anum.value = anu
  const path = process.env.API + `/holdings/getDICLists/${bank}/${year}`
  gaxios(path)
}
function newYM (yr, mo) {
  year = yr
  month = mo
  getListWithYearMonth()
  // console.log('month', month)
}
function newMonth (mo) {
  month = mo
  getListWithYearMonth()
  // console.log('month', month)
}
function updYear (yr) {
  year = yr
  getListWithYearMonth()
  // console.log('year', year)
}
function showActivity (a) {
  console.log('-CK-showActivity', a)
  refCheckingPad.value.closeIt()
  if (accounts.value.length === 1) {
    a.show = false
    accounts.value = allAccounts.value
  } else {
    accounts.value = [a]
    a.show = true
  }
  accountsStatus.value[a.num] = a.show
  // openedAcctNum = a.num
  // openedAcctName = a.name
  // console.warn('accountsStatus', a.num, this.openedanum, accounts.valueStatus)
  let totalDividend = 0.0
  let totalInterest = 0.0
  let totalCcardred = 0.0
  accountActivity.value[a.num] = []
  transActivity.value.forEach(p => {
    if (p.anum === a.num) {
      accountActivity.value[a.num].push(p)
      if (/DIVIDEND RECEIVED/i.test(p.desc)) totalDividend += parseFloat(p.amnt)
      if (/INTEREST EARNED/i.test(p.desc)) totalInterest += parseFloat(p.amnt)
      if (/ELAN CARDSVC/i.test(p.desc)) totalCcardred += parseFloat(p.amnt)
    }
  })
  accountActivity.value[a.num + 'dvd'] = totalDividend
  accountActivity.value[a.num + 'int'] = totalInterest
  accountActivity.value[a.num + 'ccd'] = totalCcardred
  // console.warn('-wn-account activity', a.num, accountActivity.value[a.num])
  const actvx = accountActivity.value[a.num]
  console.log('-CK-account activity', actv.value)
  actvx.sort((a, b) => Math.abs(a.amnt) - Math.abs(b.amnt))
}
function getListWithYearMonth () {
  const path = process.env.API + `/holdings/getHoldings/${bank}/${year}/${month}`
  gaxios(path)
}
function getHoldings () {
  const path = process.env.API + `/holdings/getHoldings/${bank}/${year}/${month}`
  gaxios(path)
}
emitter.on('holdings-getHoldings', (x) => setHoldings(x))
function setHoldings (da) {
  // console.log('-CK-fn-setHoldings', da)
  if (da.status === 'NoData' || da.actv.length === 0) {
    const tit = 'No data for this month (month ' + month + ')'
    const msg = 'Do you want to load data for prior month (month ' + parseInt(month-1) + ') ?'
    emitter.emit('open-ConfirmDialog', tit, msg)
    return
  }
  const acctOrd = {
  'Z71-367818':1,
  '486-579416':2,
  'X85-275143':3,
  '226-227936':4,
  }
  holdings.value = []
  actv.value = []
  holdings.value = da.holdings
  actv.value = da.actv
  transActivity.value = []
  accounts.value = []
  allAccounts.value = []
  const ack = []
  actv.value.forEach(p => {
    transActivity.value.push({ secu: p.security, anum: p.account_num, date: p.sett_date, desc: p.description, amnt: p.amount })
    if (ack.indexOf(p.account_num) < 0) {
      ack.push(p.account_num)
      p.account_name = p.account_name.replace('  ', ' ')
      const acname0 = p.account_name.split(' ')[0]
      const acname1 = p.account_name.split(' ')[1]
      let showStatus = accountsStatus.value[p.account_num]
      if (showStatus === null) showStatus = false
      // console.log('back accountsStatus', p.account_num, accounts.valueStatus[p.account_num])
      accounts.value.push({ acname0: acname0, acname1: acname1, num: p.account_num, name: p.account_name, show: showStatus, ao:acctOrd[p.account_num] })
      allAccounts.value.push({ acname0: acname0, acname1: acname1, num: p.account_num, name: p.account_name, show: showStatus, ao:acctOrd[p.account_num] })
    }
  })
  accounts.value = accounts.value.sort((a, b) => a.ao - b.ao)
  accounts.value.forEach(a => { a.show = false })
  allAccounts.value.forEach(a => { a.show = false })
}
function XXaxiosBack (target, da) {
  if (target === 'getDICLists') {
    console.log('-ab-getDICLists Dividend, Interest, CCardRedeem', da)
    dicLists.value = da
    const xlist = da[tag.value]
    refDICSummaryPad.value.openIt(tag.value, year, month, acctNum.value, xlist)
  }
}
function getAnnuityLink () {
  return '<a href="/docs/Fidelity/' + getYQ() + '_annuity.pdf' + '" target="_blank">Annuity</a>'
}
function getYQ () {
  let YQ = null
  const m = parseInt(month)
  const y = parseInt(year)
  // console.log('-dg-', e.year, m)
  if (m > 0 && m <= 2) YQ = String(y - 1) + 'Q4'
  else if (m === 3) YQ = y + 'Q1'
  else if (m > 3 && m <= 5) YQ = y + 'Q1'
  else if (m === 6) YQ = y + 'Q2'
  else if (m > 6 && m <= 8) YQ = y + 'Q2'
  else if (m === 9) YQ = y + 'Q3'
  else if (m > 9 && m <= 11) YQ = y + 'Q3'
  else if (m === 12) YQ = y + 'Q4'
  return YQ
}
function getStatementLink (anum) {
  let lnk = ''
  const mon = month < 10 ? '0' + String(month) : month
  const yrmo = String(year) + mon
  if (['Z71-367818', '486-579416'].indexOf(anum) >= 0) lnk += 'Fidelity/' + yrmo + '_ira.pdf'
  else lnk += 'Fidelity/' + yrmo + '_roth.pdf'
  lnk = '<a href="/docs/' + lnk + '" target="_blank">statement</a>'
  // console.log('-dg- year, month', month, year, yrmo, lnk)
  return lnk
}

//== computed sections
const compAccounts = computed(() => {
  let ordAccts = []
  accounts.value.forEach(a => {
    if (/Z71/.test(a.num)) ordAccts[0] = a
    else if (/486/.test(a.num)) ordAccts[1] = a
    else if (/X85/.test(a.num)) ordAccts[2] = a
    else if (/226/.test(a.num)) ordAccts[3] = a
  })
  return ordAccts
})
const compAA = computed(() => { return accountActivity.value })
const compHoldings = computed(() => { return holdings.value })
const compAccountHoldings = computed(() => {
  // console.log('compAccountHoldings this.openedanum', this.openedanum)
  const acholds = {}
  accounts.value.forEach(a => {
    const holds = []
    holdings.value.forEach(p => { if (p.account_num === a.num) holds.push(p) })
    acholds[a.num] = holds
  })
  return acholds
})
const compTotalEai = computed(() => {
  const eais = {}
  for (const a of accounts.value) {
    let eai = 0.0
    compAccountHoldings.value[a.num].forEach(p => { if (p.eai != null) eai += parseFloat(p.eai) })
    eais[a.num] = eai
  }
  return eais
})
const compAccountEndBalances = computed(() => {
  const balances = {}
  for (const a of accounts.value) {
    let abal = 0.0
    compAccountHoldings.value[a.num].forEach(p => { abal += parseFloat(p.end_balance) })
    balances[a.num] = abal
  }
  return balances
})
const compAccountStartBalances = computed(() => {
  const balances = {}
  for (const a of accounts.value) {
    let abal = 0.0
    compAccountHoldings.value[a.num].forEach(p => { if (p.start_balance !== null) abal += parseFloat(p.start_balance) })
    balances[a.num] = abal
  }
  return balances
})
const compBalanceDiffs = computed(() => {
  const diffs = {}
  for (const a of accounts.value) {
    const diff = compAccountEndBalances.value[a.num] - compAccountStartBalances.value[a.num]
    diffs[a.num] = diff
  }
  return diffs
})
const compDiff = computed(() => {
  let lastMonthTotal = 0.0
  dats.value.forEach((p, i) => { if (i >= 3 && i < 6) lastMonthTotal += parseFloat(p.end_balance) })
  return compTotalVal.value - lastMonthTotal
})
// palist () {
//   return this.dalist.slice(this.firstOnPage, this.firstOnPage + this.numItemsPerPage)
// },
// actvlist () {
//   var filterKey = this.searchQuery && this.searchQuery.toLowerCase()
//   var data = actv.value
//   if (filterKey) {
//     var words = filterKey.split(' ')
//     words.forEach(word => {
//       data = data.filter(row => {
//         return Object.keys(row).filter(key => { return !['id', 'user_id'].includes(key) }).some(key => {
//           return String(row[key]).toLowerCase().indexOf(word) >= 0
//         })
//       })
//     })
//   }
//   return data
// },
// dalist () {
//   var filterKey = this.searchQuery && this.searchQuery.toLowerCase()
//   var data = dats.value
//   if (filterKey) {
//     var words = filterKey.split(' ')
//     words.forEach(word => {
//       data = data.filter(row => {
//         return Object.keys(row).filter(key => { return !['id', 'user_id'].includes(key) }).some(key => {
//           return String(row[key]).toLowerCase().indexOf(word) >= 0
//         })
//       })
//     })
//   }
//   return data
// }
</script>
<style>
.h-border {
  width: 100%;
  border: 2px solid white;
  background: #378;
  margin-top: -6px;
}
.a-border {
  width: 100%;
  border: 2px solid white;
  background: #123;
  /* margin-top: -6px; */
}
td.one {
  font-family: youyuan;
  font-weight: 600;
  width: 100px;
  white-space: nowrap;
  padding-left: 6px;
  color: pink;
}
button.q-btn.q-btn-item.bg-lime {
  height: 38px;
  width:  38px;
  border-radius: 20px;
  border: 0px solid teal;
}
td.ellipsis300 {
  white-space: nowrap;
  width: 40px;
  overflow: hidden;
  text-overflow: ellipsis;
  border: 1px solid cyan;
}
</style>

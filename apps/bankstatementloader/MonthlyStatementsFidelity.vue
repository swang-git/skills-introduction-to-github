<template>
<div v-if="statement.bank==='Fidelity'" :style="{ display:opened }" style="margin:-33px 0 0 0">
  <div v-if="showData[0]">
    <q-tr><td class="text-right q-px-md">Bank Name</td><td>{{ assets.bank }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Year</td><td>{{ assets.year }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Month</td><td>{{ assets.month }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Begin Date</td><td>{{ assets.begin_date }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">End Date</td><td>{{ assets.end_date }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Begin Balance</td><td>{{ assets.begin_balanceX }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">End Balance</td><td>{{ assets.end_balanceX }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Primary Account</td><td>{{ assets.primary_account }}</td></q-tr>
    <q-tr>
      <td class="text-right q-px-md">This Month Gain & Lost</td>
      <td :class="{'text-red':aGL<0, 'text-green':aGL>0}">{{ Math.abs(aGL) }}</td>
    </q-tr>
  </div>
  <div v-if="showData[1]">
    <q-tr><td class="text-right q-px-md">Contact Number</td><td>{{ dataAnn.cnum }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Begin Units</td><td>{{ dataAnn.bunit }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Begin Price</td><td>{{ dataAnn.bpric }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Begin Balance</td><td>{{ dataAnn.sbal }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">End Units</td><td>{{ dataAnn.eunit }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">End Price</td><td>{{ dataAnn.epric }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">End Balance</td><td>{{ dataAnn.ebal }}</td>
      <td style="width:17%" class="q-pb-xs">
        <q-btn v-if="showData[0]" color="indigo-9" glossy label="Add Holdings" @click="addAnnuityHoldings()" />
      </td>
    </q-tr>
  </div>
  <div v-if="showData[2]">
    <div>Start {{ dataIra.bdate }} to {{ dataIra.edate }} </div>
    <q-tr><td class="text-right q-px-md">Current Portfolio Value</td><td>{{ dataIra.cpval }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Portfolio Start Value</td><td>{{ dataIra.bpval }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Year-to-Date</td><td>{{ dataIra.ypval }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Additions</td><td>{{ dataIra.cadd }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Additions(Year-to-Date)</td><td>{{ dataIra.yadd }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Subtractions</td><td>{{ dataIra.csub }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Subtractions(Year-to-Date)</td><td>{{ dataIra.ysub }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Investment Changes</td><td>{{ dataIra.cchg }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Investment Changes(Year-to-Date)</td><td>{{ dataIra.ychg }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Ending Portfolio Value</td><td>{{ dataIra.cend }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Ending Portfolio Value(Year-to-Date)</td><td>{{ dataIra.yend }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">IRA Individual Account</td><td>{{ dataIra.indAcct }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">IRA Individual Account Begin Value</td><td>{{ dataIra.indSval }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">IRA Individual Account Endin Value</td><td>{{ dataIra.indEval }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">IRA Account</td><td>{{ dataIra.acct }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">IRA Account Begin Value</td><td>{{ dataIra.sval }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">IRA Account Endin Value</td><td>{{ dataIra.eval }}</td></q-tr>
  </div>
  <div v-if="showData[3]">
    <div>Start {{ dataRoth.bdate }} to {{ dataRoth.edate }} </div>
    <q-tr><td class="text-right q-px-md">Current Portfolio Value</td><td>{{ dataRoth.cpval }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Portfolio Start Value</td><td>{{ dataRoth.bpval }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Year-to-Date</td><td>{{ dataRoth.ypval }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Additions</td><td>{{ dataRoth.cadd }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Additions(Year-to-Date)</td><td>{{ dataRoth.yadd }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Subtractions</td><td>{{ dataRoth.csub }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Subtractions(Year-to-Date)</td><td>{{ dataRoth.ysub }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Transaction Costs, Fees & Charges</td><td>{{ dataRoth.tranFee }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Investment Changes</td><td>{{ dataRoth.cchg }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Investment Changes(Year-to-Date)</td><td>{{ dataRoth.ychg }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Ending Portfolio Value</td><td>{{ dataRoth.cend }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Ending Portfolio Value(Year-to-Date)</td><td>{{ dataRoth.yend }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Individual Account</td><td>{{ dataRoth.indAcct }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Roth Individual Account Begin Value</td><td>{{ dataRoth.indSval }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Roth Individual Account Endin Value</td><td>{{ dataRoth.indEval }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Roth IRA Account</td><td>{{ dataRoth.acct }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Roth IRA Account Begin Value</td><td>{{ dataRoth.sval }}</td></q-tr>
    <q-tr><td class="text-right q-px-md">Roth IRA Account Endin Value</td><td>{{ dataRoth.eval }}</td></q-tr>
  </div>
  <div v-if="showData[4]" style="font-size:19.1px"> <!-- holding section-->
    <q-tr>
      <td colspan="7" class="text-h5 q-pl-xs text-amber-9">Holdings for INDIVIDUAL TOD Account(Ira) {{ dataIra.indAcct }}</td>
      <td colspan="1" class="text-right">
        <q-btn color="indigo-9" label="Add" @click="addHoldings(dataIra.indAcct, 'INDIVIDUAL TOD', dataIra.holdingsInd)" />
      </td>
    </q-tr>
    <q-tr>
      <th calss="q-px-md">Symbol</th>
      <th>Begin Value</th>
      <th>Quantity</th>
      <th>Price</th>
      <th>End Value</th>
      <th>Cost</th>
      <th class="q-px-sm">UnrlzdGL</th>
      <th class="q-px-sm text-right">EAI</th>
      <th class="text-right">EY</th>
    </q-tr>
    <q-tr class="q-pl-" v-for="h in dataIra.holdingsInd" :key="h">
      <td class="q-pl-xs text-left">{{ h[0] }}</td>
      <td class="q-px-sm text-right">{{ h[1] }}</td>
      <td class="q-px-sm text-right">{{ h[2] }}</td>
      <td class="q-px-sm text-right">{{ h[3] }}</td>
      <td class="q-px-sm text-right">{{ h[4] }}</td>
      <td class="q-px-sm text-right">{{ h[5] }}</td>
      <td class="q-px-sm text-right">{{ h[6] }}</td>
      <td class="q-px-sm text-right">{{ h[7] }}</td>
      <td class="text-right">{{ h[8] }}</td>
    </q-tr>

    <q-tr>
      <td colspan="7" class="text-h6 q-pl-xs text-amber-9">Holdings for TRADITIONAL IRA Account {{ dataIra.acct }}</td>
      <td colspan="1" class="text-right">
        <q-btn glossy color="indigo-9" label="Add" @click="addHoldings(dataIra.acct, 'TRADITIONAL IRA', dataIra.holdings)" />
      </td>
    </q-tr>
    <!-- <div v-for="h in dataIra.holdings" :key="h.x"><td>{{ h }}</td></div> -->
    <q-tr class="q-pl-" v-for="h in dataIra.holdings" :key="h">
      <td class="q-pl-xs text-left">{{ h[0] }}</td>
      <td class="q-px-sm text-right">{{ h[1] }}</td>
      <td class="q-px-sm text-right">{{ h[2] }}</td>
      <td class="q-px-sm text-right">{{ h[3] }}</td>
      <td class="q-px-sm text-right">{{ h[4] }}</td>
      <td class="q-px-sm text-right">{{ h[5] }}</td>
      <td class="q-px-sm text-right">{{ h[6] }}</td>
      <td class="q-px-sm text-right">{{ h[7] }}</td>
      <td class="text-right">{{ h[8] }}</td>
    </q-tr>

    <q-tr>
      <td colspan="7" class="text-h5 q-pl-xs text-amber-9">Holdings for Individual Account(Roth) {{ dataRoth.indAcct }}</td>
      <td colspan="1" class="text-right">
        <q-btn glossy color="indigo-9" label="Add" @click="addHoldings(dataRoth.indAcct, 'INDIVIDUAL TOD', dataRoth.holdingsInd)" />
      </td>
    </q-tr>
    <!-- <div v-for="h in dataRoth.holdingsInd" :key="h.x"><td>{{ h }}</td></div> -->
    <q-tr class="q-pl-" v-for="h in dataRoth.holdingsInd" :key="h">
      <td class="q-pl-xs text-left">{{ h[0] }}</td>
      <td class="q-px-sm text-right">{{ h[1] }}</td>
      <td class="q-px-sm text-right">{{ h[2] }}</td>
      <td class="q-px-sm text-right">{{ h[3] }}</td>
      <td class="q-px-sm text-right">{{ h[4] }}</td>
      <td class="q-px-sm text-right">{{ h[5] }}</td>
      <td class="q-px-sm text-right">{{ h[6] }}</td>
      <td class="q-px-sm text-right">{{ h[7] }}</td>
      <td class="text-right">{{ h[8] }}</td>
    </q-tr>

    <q-tr>
      <td colspan="7" class="text-h5 q-pl-xs text-amber-9">Holdings for Roth IRA Account {{ dataRoth.acct }}</td>
      <td colspan="1" class="text-right">
        <q-btn glossy color="indigo-9" label="Add" @click="addHoldings(dataRoth.acct, 'ROTH IRA', dataRoth.holdings)" />
      </td>
    </q-tr>
    <q-tr class="q-pl-" v-for="h in dataRoth.holdings" :key="h">
      <td class="q-pl-xs text-left">{{ h[0] }}</td>
      <td class="q-px-sm text-right">{{ h[1] }}</td>
      <td class="q-px-sm text-right">{{ h[2] }}</td>
      <td class="q-px-sm text-right">{{ h[3] }}</td>
      <td class="q-px-sm text-right">{{ h[4] }}</td>
      <td class="q-px-sm text-right">{{ h[5] }}</td>
      <td class="q-px-sm text-right">{{ h[6] }}</td>
      <td class="q-px-sm text-right">{{ h[7] }}</td>
      <td class="text-right">{{ h[8] }}</td>
    </q-tr>
  </div>

  <div v-if="showData[5]" style="font-size:19px"> <!-- activity section -->
    <q-tr>
      <td colspan="5" class="text-h5 q-pl-xs text-amber-9">Activity for Individual Account(Ira) {{ dataIra.indAcct }}</td>
      <td colspan="1" class="text-right">
        <q-btn color="indigo-9" label="Add" @click="addActivity(dataIra.indAcct, 'INDIVIDUAL TOD', dataIra.activityInd)" />
      </td>
    </q-tr>
    <q-tr><th>Date</th><th>Symbol</th><th>Description</th><th>Quantity</th><th>Price</th><th class="text-right">Amount</th></q-tr>
    <q-tr class="q-pl-" v-for="h in dataIra.activityInd" :key="h">
      <td class="q-pl-xs text-left">{{ h[0] }}</td>
      <td class="q-px-md text-right">{{ h[1] }}</td>
      <td class="q-px-md text-right text-no-wrap ellipsis">{{ h[2].substring(0, 27) }}</td>
      <td class="q-px-md text-right">{{ h[3] }}</td>
      <td class="q-px-md text-right">{{ h[4] }}</td>
      <td class="q-pl-md text-right">{{ h[5] }}</td>
    </q-tr>

    <q-tr>
      <td colspan="5" class="text-h5 q-pl-xs text-amber-9">Activity for Traditional IRA Account {{ dataIra.acct }}</td>
      <td colspan="1" class="text-right">
        <q-btn color="indigo-9" label="Add" @click="addActivity(dataIra.acct, 'TRADITIONAL IRA', dataIra.activity)" />
      </td>
    </q-tr>
    <q-tr class="q-pl-" v-for="h in dataIra.activity" :key="h">
      <td class="q-pl-xs text-left">{{ h[0] }}</td>
      <td class="q-px-md text-right">{{ h[1] }}</td>
      <td class="q-px-md text-right text-no-wrap ellipsis">{{ h[2].substring(0, 27) }}</td>
      <td class="q-px-md text-right">{{ h[3] }}</td>
      <td class="q-px-md text-right">{{ h[4] }}</td>
      <td class="q-pl-md text-right">{{ h[5] }}</td>
    </q-tr>

    <q-tr>
      <td colspan="5" class="text-h5 q-pl-xs text-amber-9">Activity for Individual Account(Roth) {{ dataRoth.indAcct }}</td>
      <td colspan="1" class="text-right">
        <q-btn color="indigo-9" label="Add" @click="addActivity(dataRoth.indAcct, 'INDIVIDUAL TOD', dataRoth.activityInd)" />
      </td>
    </q-tr>
    <q-tr class="q-pl-" v-for="h in dataRoth.activityInd" :key="h">
      <td class="q-pl-xs text-left">{{ h[0] }}</td>
      <td class="q-px-md text-right">{{ h[1] }}</td>
      <td class="q-px-md text-right text-no-wrap ellipsis">{{ h[2].substring(0, 27) }}</td>
      <td class="q-px-md text-right">{{ h[3] }}</td>
      <td class="q-px-md text-right">{{ h[4] }}</td>
      <td class="q-pl-md text-right">{{ h[5] }}</td>
    </q-tr>

    <q-tr>
      <td colspan="5" class="text-h5 q-pl-xs text-amber-9">Activity for Roth IRA Account {{ dataRoth.acct }}</td>
      <td colspan="1" class="text-right">
        <q-btn color="indigo-9" label="Add" @click="addActivity(dataRoth.acct, 'ROTH IRA', dataRoth.activity)" />
      </td>
    </q-tr>
    <!-- <div v-for="h in dataRoth.activity" :key="h.x"> <td>{{ h }}</td></div> -->
    <q-tr class="q-pl-" v-for="h in dataRoth.activity" :key="h">
      <td class="q-pl-xs text-left">{{ h[0] }}</td>
      <td class="q-px-md text-right">{{ h[1] }}</td>
      <td class="q-px-md text-right text-no-wrap ellipsis">{{ h[2].substring(0, 27) }}</td>
      <td class="q-px-md text-right">{{ h[3] }}</td>
      <td class="q-px-md text-right">{{ h[4] }}</td>
      <td class="q-pl-md text-right">{{ h[5] }}</td>
    </q-tr>
  </div>

  <q-btn-group spread glossy class="bg-grey-9" v-if="dataIra.holdings!==undefined">
    <q-btn v-if="!showData[0]" label="Assets"    @click="showSection(0)"/>
    <q-btn v-if="showData[0]" label="Add Assets" @click="addAssets()"  color="indigo-9"/>
    <q-btn v-if="!showData[1]" label="Annuity"   @click="showSection(1)"/>
    <q-btn v-if="!showData[2]" label="IRA Head"  @click="showSection(2)"/>
    <q-btn v-if="!showData[3]" label="Roth Head" @click="showSection(3)"/>
    <q-btn v-if="!showData[4]" label="Holdings"  @click="showSection(4)"/>
    <q-btn v-if="!showData[5]" label="Activity"  @click="showSection(5)"/>
    <!-- <q-btn v-if="!showData[1]" label="Annuity"   @click="showSection(1)"/><q-btn v-if="showData[1]" label="Add Annuity"   @click="addAnnuity()" color="amber-10"/>
    <q-btn v-if="!showData[2]" label="IRA Head"  @click="showSection(2)"/><q-btn v-if="showData[2]" label="Add Ira Head"  @click="addIraHeader()" color="amber-10"/>
    <q-btn v-if="!showData[3]" label="Roth Head" @click="showSection(3)"/><q-btn v-if="showData[3]" label="Add Roth Head" @click="addRothHeader()" color="amber-10"/>
    <q-btn v-if="!showData[4]" label="Holdings"  @click="showSection(4)"/><q-btn v-if="showData[4]" label="Add Holdings"  @click="addHoldings()" color="amber-10"/>
    <q-btn v-if="!showData[5]" label="Activity"  @click="showSection(5)"/><q-btn v-if="showData[5]" label="Add Activity"  @click="addActivity()" color="amber-10"/> -->
  </q-btn-group>
</div>
</template>
<script setup>
import { ref } from 'vue';
import emitter from 'tiny-emitter/instance'
import { axiosFunctions } from 'src/composables/axiosFunctions'
import { dayFunctions } from 'src/composables/dayFunctions'
import { libFunctions } from 'src/composables/libFunctions'
const { $q } = libFunctions()
const { yyyymmdd } = dayFunctions()
const { gaxios, paxios } = axiosFunctions()
const opened = ref('')
const showBank = ref([4])
const dataAnn = ref({})
const dataIra = ref({})
const dataRoth = ref({})
const assets = ref({})
const showData = ref([4])
const aGL = ref(0)
const ymon = ref(null)
const year = ref(null)
const month = ref(null)
const bank = ref(null)
const statement = ref({})

console.log('-ST-MonthlyStatementsFidelity')
showData.value.fill(false)
emitter.on('open-MonthlyStatementsFidelity', (statement) => { loadData(statement) })
emitter.on('close-MonthlyStatementsFidelity', () => opened.value = 'none')

function addAnnuityHoldings () {
  // console.log('-fn-addAnnuityHoldings')
  const inData = {
    idx: 0, bank:bank.value, year:year.value, month:month.value, account_num:dataAnn.value.cnum, account_name:'Retirement Annuity', symbol:'FMPCC',
    start_balance: dataAnn.value.sbal , price:dataAnn.value.epric, quantity:dataAnn.value.eunit, end_balance:dataAnn.value.ebal, cost:50000
  }
  const path = process.env.API + '/bankstatementloader/addHoldings'
  paxios(path, inData)
}
function getActivityData (acctNum, acctName, activity) {
  // console.log('-fn-getActivityData', activity)
  const inData = []
  let idx = 0
  activity.forEach(p => {
    idx++
    const [mon, dat] = p[0]. split('/')
    const sett_date = year.value + '-' + mon + '-' + dat
    const quantity = p[3] === '-' ? null : p[3]
    const price = p[4] === '-' ? null : p[4]
    const cost = p[6] === '-' ? null : p[6]
    const x = {
      idx:idx, bank:bank.value, year:year.value, month:month.value, account_num:acctNum, account_name:acctName,
      sett_date:sett_date,security:p[1], description:p[2], quantity:quantity, price:price, amount:p[5], cost: cost
    }
    inData.push(x)
  })
  return inData
}
function addActivity (acctNum, acctName, activity) {
  const inData = getActivityData(acctNum, acctName, Object.values(activity))
  console.log('-fn-Activity Data', inData)
  const path = process.env.API + '/bankstatementloader/addActivity'
  paxios(path, inData)
}
function getInData (acctNum, acctName, holdings) {
  // console.log('-fn-getInData', holdings)
  const inData = []
  holdings.forEach(p => {
    const x = {
      bank:bank.value, year:year.value, month:month.value, account_num:acctNum, account_name:acctName, symbol:p[0], start_balance:p[1],
      quantity:p[2], price:p[3], end_balance:p[4], cost:p[5], unrealized_gl:p[6], eai:p[7], ey:p[8]
    }
    inData.push(x)
  })
  return inData
}
function addHoldings(acctNum, acctName, holdings) {
  const inData = getInData(acctNum, acctName, Object.values(holdings))
  // console.log('-fn-addHoldings inData', inData)
  const path = process.env.API + '/bankstatementloader/addHoldings'
  paxios(path, inData)
}
function addAssets () {
  console.log('-fn-addAssets')
  const path = process.env.API + '/bankstatementloader/addAssets'
  const inData = assets.value
  paxios(path, inData)
}
function getAssets () {
  // console.log('-CK-fn-getAssets')
  const asts = { bank:'Fidelity', tran_cnt: 0 }
  asts.begin_balance = parseFloat(dataAnn.value.sbal) + parseFloat(dataIra.value.bpval) + parseFloat(dataRoth.value.bpval)
  asts.end_balance = parseFloat(dataAnn.value.ebal) + parseFloat(dataIra.value.cend) + parseFloat(dataRoth.value.cend)
  asts.primary_account = 'INDIVIDUAL ' + dataIra.value.indAcct

  asts.begin_balanceX = asts.begin_balance.toFixed(2) + ' == ' + dataAnn.value.sbal + ' + ' + dataIra.value.bpval + ' + ' + dataRoth.value.bpval
  asts.end_balanceX = asts.end_balance.toFixed(2) + ' == ' + dataAnn.value.ebal + ' + ' + dataIra.value.cend + ' + ' + dataRoth.value.cend
  asts.gl = (asts.end_balance - asts.begin_balance).toFixed(2)

  year.value = ymon.value.substring(0, 4)
  month.value =  ymon.value.substring(4, 6)
  asts.year = year.value
  asts.month = month.value
  asts.begin_date = year.value + '-' + month.value + '-01'
  asts.end_date = new Date(year.value, month.value, 0).yyyymmdd()
  aGL.value = asts.gl
  assets.value = asts
  // console.log('-CK-getAssets', assets.value)
}
function showSection (i) {
  showData.value.fill(false)
  showData.value[i] = true
}
function getList () {} // fool the parent's createApp
function loadData (stmt) {
  if (stmt.bank !== 'Fidelity') {
    opened.value = 'none'
    return
  }
  opened.value = ''
  // console.log('-CK-fn-loadData for Fidelity stmt=', stmt)
  showData.value[0] = true
  statement.value = stmt
  bank.value = stmt.bank
  const x = stmt.date.split('-')
  year.value = x[0]
  month.value = x[1]
  ymon.value = x[0] + x[1]
  // console.log(`-CK-loadData month=${month.value} ymon=${ymon.value}}`)
  loadFidelityMonthlyStatements();
}
function loadFidelityMonthlyStatements () {
  const path = process.env.API + '/bankstatementloader/loadFidelityMonthlyStatements/' + ymon.value
  gaxios(path)
}
emitter.on('bankstatementloader-loadFidelityMonthlyStatements', (x) => setStatement(x))
function setStatement (da) {
  if (da.status === 'NO_FILE') {
    $q.dialog({
      title: 'The PDF File Not Exists, Get File in First',
      message: da.info
    })
    return
  }
  dataIra.value = da.dataIra
  dataRoth.value = da.dataRoth
  dataAnn.value = da.dataAnn
  getAssets()
  // console.log('-CK-setStatement', dataIra.value.holdingsInd)
}
</script>

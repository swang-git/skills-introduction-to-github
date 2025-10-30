<template>
<q-dialog v-model="opened">
<div style="border:2px solid cyan;max-width:800px">
  <q-table :title="title" title-class="text-h5 q-pl-xl" title-style="margin:0 0 0 66px" class="sh-sticky-header-table"
    v-model:rows="selist" :columns="columns" dark dense bordered square
    :visible-columns="isDesk ? visibleColumnsDesk : visibleColumnsFone" :style="{ minHeight:isIM ? '565px':''}"
    row-key="id" :separator="separator" wrap-cells hide-pagination :pagination="isDesk ? { rowsPerPage: 23 } : { rowsPerPage: 13 }"
  >
    <template v-slot:header="props">
      <q-tr :props="props">
        <q-th v-for="col in props.cols" :key="col.name" :props="props" class="text-yellow text-center">{{ col.label }}</q-th>
      </q-tr>
    </template>

    <template v-slot:top-right>
      <!-- <div class="row">
        <div class="text-h6" style="margin-left:54px">{{ checker }}</div>
        <div class="text-h6" style="margin-left:20px">{{ todayGL }}</div>
        <div class="text-h6" style="margin-left:30px">{{ totalGL }}</div>
      </div> -->
      <table style="margin-left:82px">
        <!-- <q-tr><td>总值：</td><td class="text-h6 text-right">{{ todayGL }}</td></tr>
        <q-tr><td>总值：</td><td class="text-h6 text-right">{{ totalGL }}</td></tr>
        <tr><td>总值：</td><td class="text-h6 text-right">{{ totalVL }}</td></tr> -->
        <q-tr><td>日获：</td><td class="text-h6 text-right">{{ todayGL }}</td><td class="text-right" style="width:100px">共计：</td><td>{{ numSecs }}</td></q-tr>
        <q-tr><td>总获：</td><td class="text-h6 text-right">{{ totalGL }}</td><td class="text-right" style="width:100px">误差：</td><td>{{ ppdiff }}</td></q-tr>
        <q-tr><td>总值：</td><td class="text-h6 text-right">{{ totalVL }}</td>
          <td class="text-right" style="width:100px">信用卡：</td>
          <td v-if="ccDueDate==null" class="text-right">{{ ccBalance }}</td>
          <td v-else class="text-right cursor-pointer" @click="ccPaymentInfo">{{ ccBalance }} Payment Due on: {{ ccDueDate }}</td>
        </q-tr>
      </table>
    </template>

    <template v-slot:body="p">
      <q-tr :props="p" @click="expandRow(p)" class="cursor-pointer">
        <q-td v-for="col in p.cols" :key="col" :style="getStyle(col.name)" :class="getClass4Val(col.value)">{{ showVal(col) }}</q-td>
      </q-tr>

      <q-tr v-show="p.expand" :props="p">
        <!-- <q-td class="bg-teal-10" :colspan="isDesk ? 8 : 3"> -->
        <q-td class="bg-teal-10" colspan="100%">
          <table style="width:99.7%;border:2px solid cyan">
            <q-tr>
              <td colspan="4">
                <q-card-actions align="between">
                  <div style="width:96%">
                    <div class="text-center">Account: {{ p.row.acct_name }} ~ {{ p.row.acct_num }} ~ {{ p.row.symbol }} ~ {{ p.row.date }}</div>
                    <!-- <div class="text-center">Security Name: {{ p.row.company }}</div> -->
                  </div>
                  <div style="width:4%;margin:-2px 0 0 0" class="float-right">
                    <q-btn round glossy color="indigo-10" @click="closePositionShow(p)">
                      <q-icon name="关" style="margin:-10px 0 0 0" />
                    </q-btn>
                  </div>
                </q-card-actions>
              </td>
            </q-tr>
            <q-tr>
              <td class="bg-teal-9 text-green-1">Price</td>       <td :class="getClass4Exp(p.row.price)">{{ showExpVal(p.row.price) }}</td>
              <td class="bg-teal-9 text-green-1">Price Change</td><td :class="getClass4Exp(p.row.pchange)">{{ showExpVal(p.row.pchange) }}</td>
            </q-tr>
            <q-tr v-if="p.row.today_gl!=0 && p.row.today_gl!='n/a'">
              <td class="bg-teal-9 text-green-1">Today's Gain/Loss</td>   <td :class="getClass4Exp(p.row.today_gl)">{{ showExpVal(p.row.today_gl) }}</td>
              <td class="bg-teal-9 text-green-1">Today's Gain/Loss(%)</td><td :class="getClass4Exp(p.row.today_gl)">{{ showExpVal(p.row.today_gl_p) }} %</td>
            </q-tr>
            <tr v-if="p.row.total_gl!=0 && p.row.total_gl!='n/a'">
              <td class="bg-teal-9 text-green-1">Total Gain/Loss</td>   <td :class="getClass4Exp(p.row.total_gl)">{{ showExpVal(p.row.total_gl) }}</td>
              <td class="bg-teal-9 text-green-1">Total Gain/Loss(%)</td><td :class="getClass4Exp(p.row.total_gl_p)">{{ showExpVal(p.row.total_gl_p) }} %</td>
            </tr>
            <q-tr>
              <td class="bg-teal-9 text-green-1">Number of Shares</td>     <td :class="getClass4Exp(p.row.quantity)">{{ showExpVal(p.row.quantity) }}</td>
              <td class="bg-teal-9 text-green-1">Percentage in Account</td><td :class="getClass4Exp(p.row.pct_of_acct)">{{ showExpVal(p.row.pct_of_acct) }}</td>
            </q-tr>
            <q-tr>
              <td class="bg-teal-9 text-green-1">Cost Basis Per Share</td><td :class="getClass4Exp(p.row.cost_basis_per_share)">{{ showExpVal(p.row.cost_basis_per_share) }}</td>
              <td class="bg-teal-9 text-green-1">Cost Basis</td>          <td :class="getClass4Exp(p.row.cost_basis_per_share)">{{ showExpVal(p.row.cost_basis) }}</td>
            </q-tr>
            <q-tr v-if="p.row.day_low>0">
              <td class="bg-teal-9 text-green-1">Day Low </td><td :class="getClass4Exp(p.row.day_low)">{{ showExpVal(p.row.day_low) }}</td>
              <td class="bg-teal-9 text-green-1">Day High</td><td :class="getClass4Exp(p.row.day_high)">{{ showExpVal(p.row.day_high) }}</td>
            </q-tr>
            <q-tr v-if="p.row.w52_low>0">
              <td class="bg-teal-9 text-green-1">52 Week Low </td><td :class="getClass4Exp(p.row.w52_low)">{{ showExpVal(p.row.w52_low) }}</td>
              <td class="bg-teal-9 text-green-1">52 Week High</td><td :class="getClass4Exp(p.row.w52_high)">{{ showExpVal(p.row.w52_high) }}</td>
            </q-tr>
          </table>
        </q-td>
      </q-tr>
    </template>
  </q-table>
</div>
</q-dialog>
</template>
<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'
import { libFunctions } from 'src/composables/libFunctions';
const { isDesk, isIM, fmtcy } = libFunctions()
const ccBalance = ref(null)
const ccDueDate = ref(null)
const checker = ref(null)
const todayGL = ref(null)
const totalGL = ref(null)
const totalVL = ref(null)
const numSecs = ref(null)
const ppdiff = ref(null)
const portfolio = ref(null)
const opened = ref(false)
const title = ref('Portfolio Positions')
const selist = ref([])
const separator = ref('cell')
const prevExp = ref({})
const visibleColumnsDesk = ref([col(0).name,col(2).name,col(3).name,col(4).name,col(5).name,col(6).name,col(7).name,col(8).name])
const visibleColumnsFone = ref([col(1).name,col(3).name])
const columns = ref([ col(0), col(1), col(2), col(3), col(4), col(5), col(6), col(7), col(8)])

//== main
console.log(`-ST-PortfolioPositions`)
emitter.on('open-PortfolioPositions',(x, y) => showPositions(x, y))


function col (idx) {
  const cols = [
    { required: false, label: '账号', align: 'right', name: 'acct_num', field: 'acct_num', headerClasses:'text-no-wrap', sortable: true },
    { required: true,  label: '账名', align: 'center', name: 'acct_name', field: 'acct_name', sortable: false, headerClasses:'text-no-wrap text-center' },
    { required: false, label: '股票', align: 'center', name: 'symbol', field: 'symbol', sortable: false, headerClasses:'text-no-wrap' },
    { required: false, label: '股价', align: 'center', name: 'price', field: 'price', sortable: false, headerClasses:'text-no-wrap' },
    { required: false, label: '日变', align: 'right', name: 'pchange', field: 'pchange', sortable: false, headerClasses:'text-no-wrap'  },
    { required: false, label: '日获', align: 'right', name: 'today_gl', field: 'today_gl', sortable: true, headerClasses:'text-no-wrap'  },
    { required: false, label: '总获', align: 'right', name: 'total_gl',  field: 'total_gl', sortable: true, headerClasses:'text-no-wrap'  },
    { required: false, label: '总价', align: 'right', name: 'current_val', field: 'current_val', sortable: true, headerClasses:'text-no-wrap'  },
    { required: false, label: '持时', align: 'right', name: 'holding_time', field: 'holding_time', sortable: false, headerClasses:'text-no-wrap' },
    { required: false, label: '股数', align: 'right', name: 'quantity', field: 'quantity', sortable: true, headerClasses:'text-no-wrap'  },
    { required: false, label: '进价', align: 'right', name: 'cost_basis_per_share', field: 'cost_basis_per_share', sortable: true, headerClasses:'text-no-wrap'  }
  ]
  return cols[idx]
}
function closePositionShow (p) {
  prevExp.value = {}
  p.expand = false
  opened.value = false
}
function expandRow (p) {
  console.log(`-fn-expendRow`, prevExp.value == p, prevExp.value, p)
  if (prevExp.value.key == p.key) {
    p.expand = false
    return
  }
  prevExp.value.expand = false
  p.expand = true
  prevExp.value = p
}
function showExpVal (val) {
  if (val == 'n/a' || val == '--') return val
  return fmtcy(val>0 ? val : -val)
}
function showVal (col) {
  // console.log(`-fn-showVal col.name=${col.name} col.value=${col.value}}`)
  // console.table(col)
  if (col.name == 'acct_num') return col.value.substring(0,4)
  else if (col.name == 'acct_name') return col.value.substring(0, 5).toUpperCase()
  else if (col.name == 'symbol') return col.value.replace('**', '')
  else if (col.name == 'holding_time') return isNaN(parseFloat(col.value)) ? 'n/a' : parseFloat(col.value).toFixed(1) + '年'
  else if (col.value == 'n/a' || col.value == '--') return col.value
  const val = col.value > 0 ? col.value : -col.value
  if (/^[-]?\d+/.test(val)) return fmtcy(val)
  return val
}
function getStyle (colname) {
  if (colname == col(0).name || colname == col(1).name || colname == col(2).name) return 'text-align:center;font-size:19px'
  // else if (colname == 'current_val') return 'text-align:right;font-size:19px;font-family:dejavu sans mono'
  else return 'text-align:right;font-size:18px'
}
function getClass4Exp (val) {
  const comClr = 'bg-teal-10 text-right '
  if (val == 'n/a' || val == '--') return comClr
  const txtClr = val >= 0 ? '' : 'text-red'
  return comClr + txtClr
}
function getClass4Val (val) {
  return val < 0 ? 'text-red bg-teal-10' : 'text-white'
}
function ccPaymentInfo () {
  let zFund = selist.value.filter(a => a.acct_num == 'Z71367818').map(p => p.current_val).reduce((x, y) => x + y, 0)
  console.log(`-CK-ccPaymentInfo ccBalance=${ccBalance.value} zFund=${zFund} current_val=`, selist.value.filter(a => a.acct_num == 'Z71367818'))
  let tit = 'Fund in Z7137818'
  let msg = '<div>Fund - ccDue: ' + zFund.toFixed(2) + ' - ' + ccBalance.value + '</div>'
  msg += '<div class="text-center">Credit Card Payment Due on: </div><div class="text-red text-center text-bold text-h3">' + ccDueDate.value + '</div>'
  emitter.emit('open-InfoDisplay', tit, msg)
}
function showPositions (da, portf) {
  console.log(`-fn-showPositions status=${da.status} portfolio=${portf}`, da)
  if (da.ccDueDate == null) {
    ccBalance.value = 'No Credit Card Payment Yet'
    ccDueDate.value = null
  } else {
    ccBalance.value = parseFloat(da.ccBalance).toFixed(2)
    ccDueDate.value = da.ccDueDate.substring(0, 10)
  }

  selist.value = da.positions
  numSecs.value = selist.value.length
  // selist.value = da.positions.sort((a, b) => b.today_gl - a.today_gl)
  // selist.value = da.positions.sort((a, b) => b.acct_num - a.acct_num)
  const date = selist.value[0].date

  const total_value = selist.value.reduce((a, b) => a + b.current_val, 0)

  opened.value = true
  // emit('date-total-value', date, total_value)
  portfolio.value = portf
  // ppdiff.value = fmtcy(total_value - portf)
  const fidelTotal = selist.value.filter(x => x.odx == 0).reduce((a, b) => a + b.current_val, 0)
  ppdiff.value = fmtcy(fidelTotal - portf)
  // console.log(`-CK- portf=[${total_value}] ${portf} - ${fidelTotal} == ${ppdiff.value}`, fidelTotal)
  // console.log(`-CK- portf-totalval=${portf} - ${total_value} == ${ppdiff}`)
  // checker.value = `portf-totalval=${portf} - ${fmtcy(total_value)} == ${fmtcy(ppdiff)}`
  // checker.value = `${portf} - ${total_value.toFixed(2)} == ${fmtcy(ppdiff.value)}`
  // console.log(`-CK- footer=${footer.value}`)
  title.value = date + ' Portfolio Positions $' + fmtcy(portfolio.value) // + '    (' + total_items + ' Securities)'
  const todayGLarray = selist.value.map(a => a.today_gl).filter(a => /^[-]*\d*\.\d*$/.test(a))
  const totalGLarray = selist.value.map(a => a.total_gl).filter(a => /^[-]*\d*\.\d*$/.test(a))
  // const totalVLarray = selist.value.map(a => a.current_val).filter(a => /^\d*[\.]*\d*$/.test(a))
  console.log('today gl:', todayGLarray.join(' + '))
  console.log('total gl:', totalGLarray.join(' + '))
  // console.log('total val:', totalVLarray.join(' + '))
  todayGL.value = fmtcy(todayGLarray.reduce((a, b) => parseFloat(a) + parseFloat(b), 0))
  totalGL.value = fmtcy(totalGLarray.reduce((a, b) => parseFloat(a) + parseFloat(b), 0))
  // totalVL.value = fmtcy(totalVLarray.reduce((a, b) => parseFloat(a) + parseFloat(b), 0))
  totalVL.value = fmtcy(total_value)
  // todayGL.value = '日获：' + fmtcy(todayGLarray.reduce((a, b) => parseFloat(a) + parseFloat(b), 0))
  // totalGL.value = '总获：' + fmtcy(totalGLarray.reduce((a, b) => parseFloat(a) + parseFloat(b), 0))
  // totalVL.value = '总值：' + fmtcy(totalVLarray.reduce((a, b) => parseFloat(a) + parseFloat(b), 0))
  return
}
</script>

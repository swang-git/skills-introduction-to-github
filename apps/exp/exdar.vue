<template>
<div>
  <q-dialog v-model="opened" transition-show="slide-right" persistent :maximized="isIM">
    <q-layout container class="bg-teal-10" :style="{ height:compHeight }">
      <LayoutHeader :tit="getTitle()" @do-action="doAction" :rbtn="iicon" />
      <LayoutFooter :tit="getFoote()" icon="link" :act="action" @do-action="doAction" />
      <q-page-container>
        <q-page class="q-pa-sm">
          <div v-if="isDesk">
            <DateTimePicker label="Purchased at Date and Time" :dateTime="row.purchasedon" @upd-dt="updDateTime" txsz="text-h6" />
          </div>
          <div v-else>
            <DateTimeIMPicker class="q-pa-xs" label="Match Starting Date Time" txsz="text-h6" :dateTime="row.purchasedon" @upd-dt="setDateTime" />
          </div>
          <SelInput :obj="row" label="Select Paid with"   icon="money"    iColor="amber"   :optList="paymOptions" @add-new-csp="handleUserSelection" />
          <SelInput :obj="row" label="Select Category"    icon="category" iColor="pink"    :optList="catsOptions" @get-subc-opt="getSubcOpt" />
          <SelInput :obj="row" label="Select Subcategory" icon="category" iColor="green-6" :optList="subcOptions" @get-paye-opt="getPayeOpt" />
          <SelInput :obj="row" label="Select Payee"       icon="store"    iColor="cyan-3"  :optList="payeOptions" @add-new-paye="addNewPayee" />

          <div v-if="isGolfPlayRelated() || isGolfMembership()">
            <div class="row">
              <NumInput class="col-6" v-if="isCCAutopay(row)" :obj="row" :showRight="true" :rightIcon="true" label="Fidelity CCard Payment" mask="#.##" icon="monetization_on" iColor="orange" @disable-gc="setGcard" />
              <NumInput class="col-6" v-else :obj="row" label="Total Cost" :rightIcon="true" mask="#.##" icon="monetization_on" iColor="orange" @disable-gc="setGcard" />
              <NumInput v-if="isGolfPlay()" class="col-6" :obj="row" :label="isIM ? 'W or L' : 'Won or Lost'" :showRight="true" :rightIcon="true" mask="" icon="paid" iColor="yellow" />
            </div>
            <!-- <div v-if="['Mercer County Golf Gift Card','Somerset County Golf Gift Card','Spooky Brook Golf Course'].includes(row.paym)" class="row"> -->
            <div v-if="isGiftCard()" class="row">
              <NumInput class="col" :obj="row" label="Gift Card Balance" mask="#.##" :rightIcon="true" icon="balance" iColor="cyan-5" :disable="gcDisable" />
              <NumInput class="col" :obj="row" label="Gift Card Number"  mask="#"    :rightIcon="true" icon="tag"     iColor="cyan-1" :disable="gcDisable" prefix="" />
            </div>
          </div>
          <div v-else-if="isAutoGaso(row)" class="row">
            <div class="row">
              <NumInput class="col-6" :obj="row" label="Total Cost" iconSize="lg" :rightIcon="true" mask="#.##"  icon="monetization_on" iColor="orange" @disable-gc="setGcard" />
              <NumInput class="col-6" :obj="row" label="Unit Price" iconSize="lg" :rightIcon="true" mask="#.###" icon="money" iColor="orange" />
            </div>
            <div class="row">
              <NumInput class="col" :obj="row" label="Quantities" iconSize="sm" :rightIcon="true" prefix='' mask="#.##" icon="numbers" iColor="yellow" @calc-quan="calcQuan"/>
              <NumInput class="col" :obj="row" label="Miles Run"  iconSize="lg" :rightIcon="true" prefix='' mask="#.#"  icon="directions_car" iColor="grey-4" @calc-mileage="calcGasMileage" />
            </div>
          </div>
          <div v-else>
            <NumInput v-if="!isCCAutopay(row)" :obj="row" label="Total Cost" iconSize="lg" :showRight="true" :rightIcon="true" mask="#.##" icon="paid" iColor="amber" @disable-gc="setGcard" />
            <NumInput v-else :obj="row" label="Fidelity CCard Payment" iconSize="lg" :showRight="true" :rightIcon="true" mask="#.##" icon="paid" iColor="teal-2" @disable-gc="setGcard" />
          </div>
          <div v-if="row.paym==='Fidelity Credit Card' && showPostDate">
            <datepicker label="Set Post Date for Payment or Refund" :date="row.post_date" txsz="text-h6" @upd-date="setPostDate" />
          </div>
        </q-page>
      </q-page-container>
    </q-layout>
  </q-dialog>
  <ConfirmDialog @user-confirmed="userConfirmed" />
  <AddNewCSPDialog @add-new-csp="addNewCSP" />
  <NotesDialog @set-link-notes="setNotes" />
  <LnkInput @upd-link="updLink" />
  <AddNewGiftCardDialog @new-gift-card="updGCardInfo" />
  <NotePad @save-details="saveNote" />
  <FloatPad />
  <SelOptionsWithSearch @selected-option="selectedOption" />
</div>
</template>
<script setup>
import { ref, reactive, computed } from 'vue'
import { libFunctions } from 'src/composables/libFunctions'
import { axiosFunctions } from 'src/composables/axiosFunctions'
import { dayFunctions } from 'src/composables/dayFunctions'
import emitter from 'tiny-emitter/instance'
import SelInput from '../src/components/SelInput'
import LnkInput from '../src/components/LnkInput'
import NumInput from '../src/components/NumInput'
// import TxtInput from '../src/components/TxtInput'
import NotePad from '../src/components/NotePad'
import LayoutHeader from '../src/components/LayoutHeader'
import LayoutFooter from '../src/components/LayoutFooter'
import DateTimePicker from '../src/components/DateTimePicker'
import DateTimeIMPicker from '../src/components/DateTimeIMPicker'
import Datepicker from '../src/components/DatePicker'
import ConfirmDialog from '../src/components/ConfirmDialog'
import NotesDialog from './NotesDialog'
import AddNewCSPDialog from './AddNewCSPDialog'
import AddNewGiftCardDialog from './AddNewGiftCardDialog'
import FloatPad from '../src/components/FloatPad'
import SelOptionsWithSearch from '../src/components/SelOptionsWithSearch'
import { Calendar } from '../holiday/Calendar'
// import { falseFunc } from 'app/node_modules_from_linux/boolbase'

const { isIM, isDesk } = libFunctions()
const { gaxios, paxios } = axiosFunctions()
const { addDays, addMonthsKeepDay } = dayFunctions()

//== data
const showPostDate = ref(false)
const originalCost = ref(0)
const opened = ref(false)
const gcDisable = ref(true)
var catsOptions = ref([])
var subcOptions = ref([])
var payeOptions = ref([])
var paymOptions = ref([])
const isaDeleted = ref(false)
const isaUpdated = ref(false)
const isaCreated = ref(false)
const newCSP = reactive({})
const mileage = ref(null)
const action = ref(null)
const dtTimeDone = ref(false)
var iicon = null
const tit = ref(null)
const giftCardBalance = ref(null)
// var row = ref({ cats:null, subc:null, subcId: 0, purchasedon:null, paym:null, paymId:0, payeId:0 })
var row = ref({})

const emit = defineEmits(['user-confirmed', 'add-new-csp'])
defineExpose({ openIt })
console.log('-ST-exdar')

//== emitter.on
// emitter.on('open-exdar', (rw) => { console.table([rw.id, rw.payeId, rw.paye]); openIt(rw) })
// emitter.on('del-row', (rowId) => { console.log(`-CK-del_row id=${rowId}`) })
emitter.on('del-row', (rw) => { row.value = rw; del() })
emitter.on('open-exdar', (rw, act) => { openIt(rw, act) })
emitter.on('exp-getGiftCardBalance', (x) => setGiftCardBalance(x))
emitter.on('exp-getCatsCombo', (x) => setCatsCombo(x))
emitter.on('num-input', (x) => openFloatPad(x))
emitter.on('exp-getPurchasedList4Exdar', (x) => { setPurchasedList(x.lst) })
emitter.on('expense-addNewCSP', (x) => { setNewCSP(x) })

//== computed
const compHeight = computed(() => {
  const r = row.value
  // console.log(`-CK-cp-compHeight cats=${r.cats} subc=${r.subc} paym=${r.paym} ${showPostDate.value}`)
  const baseh = 500
  let golfplay = 0
  let autogaso = 0
  // if (r.cats === 'Golf' && r.subc === 'Play' ) golfplay += 63
  // if ((isGolfPlayRelated() || isGolfMembership()) && (r.paym === 'Mercer County Golf Gift Card' || r.paym === 'Somerset County Golf Gift Card' )) golfplay += 63
  if ((isGolfPlayRelated() || isGolfMembership()) && isGiftCard()) golfplay += 63
  if (isAutoGaso()) autogaso += 63
  if (r.paym === 'Fidelity Credit Card' && showPostDate.value) autogaso += 63
  console.log('-cp-compHeight-input', baseh, '+', golfplay, '+', autogaso)
  return parseInt(baseh + golfplay + autogaso) + 'px'
})
const compDate = computed(() => { return row.value.date === undefined ? null : getDay(row.value.date.substring(0, 10)) })
// const compGcDisable = computed(() => { return gcDisable })
const defaultYM = computed(() => { return datetime.value.substring(0, 7).replace('-', '/') })
const compSubcOptions = computed(() => { return subcOptions })
const compPayeOptions = computed(() => { return payeOptions })
const showDLink = computed(() => {
  const ex = ['Play', 'Gasoline', 'iPhone', 'Comcast Internet']
  return !ex.includes(row.value.subc)
})

//== functions
function isGiftCard () { return [9, 15].includes(row.value.paymId) }
function setDateTime (dt) {
  row.value.purchasedon = dt
  dtTimeDone.value = true
  // if (isLocal()) return
  const date = dt.substring(0, 10)
  console.log(`dt time=${row.value.purchasedon}, check if there are other appointments on the date=${date}`)
  // const path = process.env.API + '/golf/checkReminder/' + date
  // gaxios(path)
}
function selectedOption (cspId, model, opt) {
  console.log(`-CK-fn-selectedOption cspId=${cspId} model=${model}`, opt)
  if (cspId === -1) {
    var cspModel = null
    if (model === 'Select Paid with') cspModel = 'PayMethod'
    else if (model === 'Select Category') cspModel = 'Category'
    else if (model === 'Select Subcategory') cspModel = 'Subcategory'
    else if (model === 'Select Payee') cspModel = 'Payee'
    return emitter.emit('open-AddNewCSPDialog', cspModel)
  }
  if (model === 'Select Paid with') {
    row.value.paymId = opt.value
    row.value.paym = opt.label
    // console.log('select Paid with label, opt.value, opt.label, compObj.value)
    // if (opt.label === 'Mercer County Golf Gift Card' || opt.label === 'Somerset County Golf Gift Card') getGiftCardBalance()
    if (isaUsingGiftCardCourse() || isGolfMembership() || isGiftCard()) getGiftCardNumAndBalance()
  } else if (model === 'Select Category') {
    row.value.catsId = opt.value
    row.value.cats = opt.label
    // cspModel = 'Category'
    // console.log('-CK-get-subc-opt', model, opt.value)
    // emit('get-subc-opt', opt.value)
    getSubcOpt(opt.value)
    // emitter.emit('open-AddNewCSPDialog', opt.value)
  } else if (model === 'Select Subcategory') {
    row.value.subcId = opt.value
    row.value.subc = opt.label
    // cspModel = 'Subcategory'
    // emitter.emit('get-paye-opt', opt.value)
    getPayeOpt(opt.value)
    // emit('get-paye-opt', opt.value)
  } else if (model === 'Select Payee') {
    row.value.payeId = opt.value
    row.value.paye = opt.cname == null ? opt.label : opt.cname
    // cspModel = 'Payee'
    if (isaUsingGiftCardCourse() || isGolfMembership()) getGiftCardBalance()
    // emit('add-new-paye', opt.value)
  // } else if (model === 'Select Course') {
    //   row.value.course_id = opt.value
  //   row.value.courseName = opt.label
  }
}
function calcQuan () {
  row.value.quan = (row.value.cost / row.value.unip).toFixed(2)
}
function openFloatPad(label) {
  // console.log(`-fn-openFloatPad-for ${label}`, row)
  emitter.emit('open-FloatPad', label, row)
}
function getFoote () {
  if (isAutoGaso(row)) {
    calcGasMileage()
    console.log(`-fn-getFoote tit=${tit.value}`)
    return tit.value
  } else if (row.value.paym === 'Fidelity Credit Card') {
    return 'NOTE_LINK_POST'
  }
  return 'NOTE_LINK'
}
function setPostDate (date) {
  row.value.post_date = date
  console.log(`-fn-setPostDate post_date=${date} row.value.post_date=${row.value.post_date}`)
}
function isGolfPlay () {
  return row.value.cats === 'Golf' && row.value.subc === 'Play'
}
function isGolfPlayRelated () {
  const cats = row.value.cats
  const subc = row.value.subc
  const regex = new RegExp('Play|Tournament|Outing|Playof|Golf Balls|Range Balls|Membership|Driving Range', 'gi')
  // const regex = new RegExp('Playof', 'i')
  const retval = row.value.cats === 'Golf' && regex.test(subc)
  // console.log(`-fn-isGolfPlayRelated()=${retval} cats=${cats} subc=${subc}`, regex, /Club Playoff/ig.test(subc))
  return retval
  // return row.value.cats === 'Golf' && (subc === 'Play' || subc.indexOf('Tournament') >= 0 || subc.indexOf('Outing') >= 0 || subc.indexOf('Playoff') >= 0)
  // return row.value.cats === 'Golf' && (['Play', 'Tournament', 'Outing', 'Playoff'].includes(subc))
}
function isGolfMembership () {
  const subc = row.value.subc
  return row.value.cats === 'Golf' && 'Membership' === subc
}
function isCCAutopay () { return row.value.subc === 'Monthly Autopay' && row.value.paye === 'Fidelity Credit Card' }
function isAutoGaso () { return row.value.cats==='Auto' && row.value.subc==='Gasoline' }
function saveNote(val) {
  row.value.note = val
}
function updLink(lnks) {
  row.value.link = lnks.join('@')
  console.log('-fn-updLink', row.value.link)
}
function getColWidth(amnt, col) {
  console.log('-fn-getColWidth', amnt, col)
  var twid = 50
  var pwid = 50
  const aamnt = Math.abs(amnt)
  if (aamnt >= 1000.0) {
    twid = 35.9
    pwid = 100.0 - twid
  } else if (aamnt >= 100.0 && aamnt < 1000.0 ) {
    twid = 33.9
    pwid = 100.0 - twid
  } else if (aamnt >= 10.0 && aamnt < 100.0 ) {
    twid = 31.5
    pwid = 100.0 - twid
  } else if (aamnt >= 0.0 && aamnt < 10.0 ) {
    twid = 29.5
    pwid = 100.0 - twid
  }
  if (col === 'payee') {
    return pwid + '%'
  } else if (col === 'tpaid') {
    return twid + '%'
  }
}
function getTitle () { return 'Spending Details' }
function setGcard (b) {
  gcDisable.value = b
}
function calcGasMileage () {
  if (isDesk) mileage.value = 'Gas Mileage: ' + (row.value.mile / row.value.quan).toFixed(2) + ' Miles/Gallon'
  else mileage.value = (row.value.mile / row.value.quan).toFixed(2) + ' Miles/Gallon'
  tit.value = mileage.value
}
function setNotes (data) {
  row.value.link = data.link
  row.value.note = data.note
  console.log('-fn-setNotes', row.value.link, row.value.note)
}
function openNotesDialog () {
  const dats = { link: row.value.link, note: row.value.note }
  emitter.emit('open-notesDialog', dats)
}
function updDateTime (val) {
  // console.log('-fn-updDateTime', val)
  row.value.purchasedon = val
  // this.setCost()
}
// function setTotalCost () {
//   console.log('setTotalCost')
// }
// function showPurchasedList () {
//   emitter.emit('open-PurchasedList', row.value.date.substring(0, 10), row.value.payeId, row.value.paye)
// }
function addNewPaym () {
  console.log('-fn-addNewPaym')
  newCSP.model = 'PayMethod'
  // emitter.emit('open-AddNewCSPDialog', 'Add New Payment Type')
  emitter.emit('open-AddNewCSPDialog', 'Payment Type')
}
function addNewCat () {
  newCSP.model = 'Category'
  // emitter.emit('open-AddNewCSPDialog', 'Add New Category')
  emitter.emit('open-AddNewCSPDialog', 'Category')
}
function addNewSubcat () {
  newCSP.model = 'Subcategory'
  // emitter.emit('open-AddNewCSPDialog', 'Add New Subcategory')
  emitter.emit('open-AddNewCSPDialog', 'Subcategory')
}
function setCost () {
  const cal = new Calendar(new Date(row.value.purchasedon))
  cal.getHolidays()
  const paye = row.value.paye
  let cost = undefined
  if (isGolfPlayRelated()) {
    if (cal.isHoliday() || cal.isWeekend()) {
      if (isaSomersetCountyCourse(paye)) cost = 58
      else if (isaMercerCountyCourse(paye)) cost = 32.00
      else cost = 80.00
    } else {
      if (isaSomersetCountyCourse(paye)) cost = 38
      else if (isaMercerCountyCourse(paye)) cost = 22.00
      else cost = 50.00
    }
    row.value.cost = cost
    console.log(`-fn-setCost row.value.cost=${row.value.cost}`)
  }
}
function updGCardInfo (newCardNum, newBlance, paymId) {
  row.value.gcardNum = newCardNum
  row.value.gcardVal = newBlance
  row.value.paymId = paymId
  console.log('-fn-updGCardInfo new card info', row.value)
}
function addNewPayee () {
  console.log('-fn-addNewPayee', row, isaUsingGiftCardCourse())
  if (isaUsingGiftCardCourse() || isGolfMembership()) getGiftCardBalance()
  if (row.value.payeId > 0) {
    setCost()
    return
  }
  if (isGolfPlayRelated()) {
    const tit = 'Please add new golf course'
    const msg = 'Please add new golf course on the golf application then come back here to select the newly added golf course for the expense'
    emitter.emit('open-InfoDisplay', tit, msg)
    return
  }
  newCSP.model = 'Payee'
  // emitter.emit('open-AddNewCSPDialog', 'Add New Payee')
  emitter.emit('open-AddNewCSPDialog', 'Payee')
}
function getSubcOpt (catId) {
  console.log(`-fn-getSubcOpt catId=${catId}`)
  if (catId == -1) addNewCat()
  emitter.on('exp-getSubcat', (x) => setSubcat(x))
  const path = process.env.API + '/exp/getSubcat/' + catId
  gaxios(path)
}
function setSubcat (da) {
  console.log('-CK-fn-setSubcat', da)
  subcOptions.value = da.subcOpt
  row.value.subc = da.subcOpt[0].label
  row.value.subcId = da.subcOpt[0].value
  if (row.value.subcId === -1) return emitter.emit('open-AddNewCSPDialog', 'Subcategory')
  getPayeOpt(row.value.subcId)
}
emitter.on('exp-getPayee', (x) => setPayee(x))
emitter.on('exp-getCourseList', (x) => setPayee(x))
function getPayeOpt (subcId) {
  console.log(`-CK-fn-getPayeOpt subcId=${subcId}`, row.value)
  if (subcId === -1) return addNewSubcat()
  if (isGolfPlayRelated()) {
    const path = process.env.API + '/exp/getCourseList'
    gaxios(path)
    return
  }
  const path = process.env.API + '/exp/getPayee/' + subcId
  gaxios(path)
}
function setPayee (da) {
  console.log('-fn-setPayee', da)
  if (da.lst[0].value === -1) return emitter.emit('open-AddNewCSPDialog', 'Payee')
  payeOptions.value = da.lst
  row.value.paye = row.value.paye
  row.value.payeId = row.value.payeId
  // row.value.paye = da.lst[0].label
  // row.value.payeId = da.lst[0].value
}
function addNewCSP (newCSP) {
  // newCSP.name = cspName
  console.log(`-CK-fn-addNewCSP cspName=${newCSP.name} row.cspId=r${row.value.catsId}`)
  if (newCSP.model === 'Payee') {
    const subcId = row.value.subcId
    newCSP.parentId = subcId
  } else if (newCSP.model === 'Subcategory') {
    const catsId = row.value.catsId
    newCSP.parentId = catsId
  } else if (newCSP.model === 'Category') {
    newCSP.parentId = 0
  } else if (newCSP.model === 'PayMethod') {
    newCSP.parentId = 0
  }
  const inData = newCSP
  const path = process.env.API + '/expense/addNewCSP'
  paxios(path, inData)
}
function userConfirmed (act) {
  if (act === 'del-spend') {
    console.log(`user confirmed to delete row action=${act}`, row.value)
    opened.value = false
    delFromDB()
    emit('user-confirmed')
  }
}
function handleUserSelection (model, opt) {
  console.log('-fn-handleUserSelection', model, opt)
  if (opt.label === 'Mercer County Golf Gift Card' || opt.label === 'Somerset County Golf Gift Card') {
    row.value.paymId = opt.value
    getGiftCardBalance()
  } else if (opt.value === -1) {
    if (opt.label === 'Add New Payment Type') {
      addNewPaym()
    } else if (opt.label === 'Add New Catetory') {
      addNewCat()
    } else if (opt.label === 'Add New Subcatetory') {
      addNewSubcat()
    } else if (opt.label === 'Add New New Payee') {
      addNewPayee()
    }
  } else {
    if (model === 'Select Paid with') {
      console.log('-sel-', model, opt.value)
      row.value.paymId = opt.value
      row.value.paym = opt.label
    } else if (model === 'Select Category') {
      row.value.catsId = opt.value
      row.value.cats = opt.label
      emit('get-subc-opt', opt.value)
    } else if (model === 'Select Subcategory') {
      row.value.subcId = opt.value
      row.value.subc = opt.label
      emit('get-paye-opt', opt.value)
    } else if (model === 'Select Payee') {
      row.value.payeId = opt.value
      row.value.paye = opt.label
      emit('add-new-paye', opt.value)
    }
  }
}
function getGiftCardNumAndBalance() {
  console.log('-fn-getGiftCardNumAndBalance')
  const path = process.env.API + '/exp/getGiftCardBalance/' + row.value.paymId + '/0'
  gaxios(path)
}
function getGiftCardBalance() {
  console.log('-fn-getGiftCardBalance')
  if (!isaUsingGiftCardCourse() && !isGolfMembership()) return
  // setCost()
  if (row.value.cost == undefined) {
    let tit = 'Cost Missing'
    let msg = 'Please provide cost'
    // emitter.emit('open-InfoDialog', tit, msg)
    emitter.emit('open-InfoDisplay', tit, msg)
  }
  const path = process.env.API + '/exp/getGiftCardBalance/' + row.value.paymId + '/' + row.value.cost
  gaxios(path)
}
function setGiftCardBalance(da) {
  // console.log(`-CK-fn-setGiftCardBalance, balance=${da.curBalance} gcardNum=${da.curCardNum}`, da)
  console.log(`-CK-fn-setGiftCardBalance, balance=${da.gcardBal} gcardNum=${da.gcardNum}`, da)
  if (da.status === 'Need to Add New Golf Gift Card') {
    console.log('-fn-setGiftCardBalance', da)
    row.value.curBalance = da.gcardBal
    row.value.curCardNum = da.gardNum
    emitter.emit('open-AddNewGiftCardDialog', row.value)
    return
  }
  // giftCardBalance.value = da
  // console.log(`-fn-getGiftCardBalance, ${row.value.gcardVal}=${da.gcardBal} ${row.value.gcardNum}=${da.gcardNum}`)
  console.log(`-fn-getGiftCardBalance, balance=${da.gcardBal} gcardNum=${da.gcardNum}`)
  // if (row.value.gcardNum !== da.gcardNum) {
  //   let tit = "NOTICE: Using New Golf Gift Card"
  //   let msg = "Please check the new card balance and add/upd again"
  //   emitter.emit('open-InfoDisplay', tit, msg)
  //   emitter.emit('open-AddNewGiftCardDialog', row.value)
  // }
  row.value.gcardNum = da.gcardNum
  row.value.gcardVal = da.gcardBal
  return
}
function setCatsCombo(da) {
  // console.log('-CK-on-getCatsCombo', da.catOptions, row.value)
  catsOptions.value = da.catOptions
  subcOptions.value = da.subOptions
  paymOptions.value = da.pymOptions
  if (isGolfPlayRelated()) {
    getCourseList() // this will assgin CourseList to payeOptions if golf play or tournament
    // console.log('-ck-setCatsCombo', row.value.cats, row.value.subc, this.payeOptions)
  } else {
    payeOptions.value = da.pyeOptions
  }
}
function setNewCSP (da) {
  console.log('-fn-setNewCsp', da)
  const csp = da.csp
  if (csp.model === 'Payee') {
    row.value.paye = csp.name
    row.value.payeId = csp.id
  } else if (csp.model === 'Category') {
    row.value.cats = csp.name
    row.value.catsId = csp.id
    row.value.subcId = -1
    addNewSubcat()
  } else if (csp.model === 'Subcategory') {
    row.value.subc = csp.name
    row.value.subcId = csp.id
    row.value.payeId = -1
    addNewPayee()
  } else if (csp.model === 'PayMethod') {
    row.value.paym = csp.name
    row.value.paymId = csp.id
  }
}
function openIt (rw, act) {
  action.value = act
  row.value = rw
  iicon = null
  originalCost.value = row.value.cost
  // console.log(`-CK-fn-openIt id=${rw.id} payeId=${rw.payeId} paye=${rw.paye}`)
  if (row.value.cats === 'Shopping' && row.value.subc === 'Grocery' && row.value.hasPlst) iicon = 'shopping_cart'
  else if (row.value.cats === 'Golf' && row.value.subc === 'Play' && row.value.hasScore) iicon = 'golf_course'
  else if (act === 'add' && row.value.cats === 'Banking' && row.value.subc === 'Monthly Autopay') {
    row.value.unip = row.value.unip.replace(/0$/, '')
    row.value.date=row.value.date.addMonthsKeepDay(1)
    setNoteAndLink()
  }
  // console.table(row)
  // console.log(`-fn-openIt-exdar-trimedUnip=${row.value.unip}`)
  showPostDate.value = false
  // row = row
  const mage = (row.value.mile / row.value.quan).toFixed(2)
  mileage.value = mage > 0 ? mage : null
  if (row.value.cats === 'Auto' && row.value.subc === 'Gasoline') tit.value = 'Gas Mileage: ' + mileage.value
  else if (row.value.paym === 'Fidel CCard') tit.value = 'NOTE_LINK_POST'
  else tit.value = 'NOTE_LINK'
  // if (row.value.paym === 'Fidel CCard') this.tit = 'NOTE_LINK_POST'
  if (isGolfPlayRelated()) originalCost.value = row.value.cost
  // row = JSON.parse(JSON.stringify(row))
  row.value.note = row.value.note === null ? '' : row.value.note
  row.value.mile = row.value.mile === 0 ? null : row.value.mile
  row.value.purchasedon = row.value.date
  row.value.disableGC = true
  isaDeleted.value = row.value.del
  isaUpdated.value = row.value.upd
  isaCreated.value = row.value.add
  // user_id.value = row.value.user_id
  // this.$parent.getPurchasedList(row.value.purchasedon.substring(0, 10), row.value.payeId)
  opened.value = true
  if (catsOptions.value.length > 0) {
    return
  }
  // console.log(`-CK--fn-openIt open=${opened.value} payeId=${row.value.payeId}`, row.value)
  const path = process.env.API + '/exp/getCatsCombo/' + row.value.catsId + '/' + row.value.subcId
  gaxios(path)
}
function getCourseList () {
  const path = process.env.API + '/exp/getCourseList'
  gaxios(path)
}
function openNotePad() {
  console.log('-fn-open-NotePad')
  emitter.emit('open-NotePad', row.value.note)
  // refs.NotePad.openIt(row.value.note)
}
function doAction(act) {
  // if (isaDeleted.value && (act === 'upd' || act === 'del')) {
    if ((act === 'upd' || act === 'del')) {
      const tit = "A Deleted Purchase"
      const msg = "A Deleted Purchase can not be updated or delete, you can use it as template - revise it and add a new purchase."
    // emitter.emit('open-InfoDisplay', tit, msg)
    if (act === 'del') emitter.emit('open-ConfirmDialog',  tit, msg, 'del-spend')
    else if (act === 'upd') upd()
    console.log('-fn-doAction', act)
    return
  } else if (act === 'psd') {
    return psd()
  } else if (act === 'plt') {
    console.log('-fn-openPurchasedList', row.value)
    const date = row.value.date
    const payeId = row.value.payeId
    const path = process.env.API + '/exp/getPurchasedList4Exdar/' + date + '/' + payeId
    gaxios(path)
    return
  } else if (act === 'gsc') {
    console.log('-fn-open Golf Play Scores', row.value)
    emitter.emit('show-golf-scores', row.value)
    return
  }
  return act === 'add' ? add() : act === 'upd' ? upd() : act === 'del' ? del() : act === 'lnk' ? lnkfunc() : openNotePad()
}
function setPurchasedList (plst) {
  console.log('-fn-purchasedList from exlist', plst)
  const date = row.value.date
  const paye = row.value.paye
  const payeId = row.value.payeId
  if (plst.length == 0) {
    emitter.emit('open-InfoDisplay', 'No Purchased List', `On this shopping date: ${date} at ${paye}` )
    return
  }
  emitter.emit('open-PurchasedList', date, plst, paye, payeId)
}
function isInvalidCost () {
  return Number.isNaN(row.value.cost) || row.value.cost === null || row.value.cost === ''
}
function isRightGolfCourseForGolfPlay () {
  const paymId = row.value.paymId
  const paye = row.value.paye
  const cats = row.value.cats
  const subc = row.value.subc
  const tit = row.value.paym + '(' + paymId + ')'
  const msg = 'Please select golf course for the Above Gift Card. You have selected (' + paye + ')'
  if (cats === 'Golf' && subc === 'Play') {
    if ((paymId === 9 && isaMercerCountyCourse(paye)) || (paymId === 15 && isaSomersetCountyCourse(paye))) return true
    emitter.emit('open-InfoDisplay', tit, msg)
    return false
  }
  return true
}
function setNoteAndLink () {
  row.value.link = 'fidelity_credit_card/' + row.value.date.substring(0,10)+'.pdf'
  let x = row.value.note.split(' ~ ')
  let beginDay=x[1].addDays(1)
  let endDay=beginDay.addDays(29)
  row.value.note = beginDay + ' ~ ' + endDay
  console.log(`row.value.date=${row.value.date}`)
  console.log(`row.value.link=${row.value.link}`)
  console.log(`row.value.note=${row.value.note}`,row.value)
}
function add () {
  if (isGiftCard() && !isRightGolfCourseForGolfPlay()) return
  if (isInvalidCost()) {
    const tit = 'Please check the cost to add new expense'
    emitter.emit('open-InfoDisplay', tit, null)
    return
  }
  if (checkBalance() === 'balance low') return
  console.log('-fn-add-checkBalance OK adding')
  row.value.post_date = null
  const path = process.env.API + '/exp/addSpend'
  const data = row.value
  // if (data.subc === 'Monthly Autopay' && data.cats === 'Banking' && data.paye === 'Fidelity Credit Card') { setNoteAndLink(data) }
  data.id = -1
  opened.value = false
  paxios(path, data)
}
function upd () {
  console.log('-fn-upd row:', row.value)
  if (isGiftCard() && !isRightGolfCourseForGolfPlay()) return
  // if (Number.isNaN(row.value.cost) || row.value.cost == null || row.value.cost === '') {
  if (row.value.post_date != null && row.value.post_date < row.value.purchasedon) {
    row.value.post_date = null
  }
  if (isInvalidCost()) {
    const tit ='Please check the cost for updating'
    emitter.emit('open-InfoDisplay', tit, null)
    return
  }
  if (row.value.gcardVal === '') {
    const tit = 'Please provide gift card balance'
    emitter.emit('open-InfoDisplay', tit, null)
    return
  }
  checkBalance(originalCost) // this need to add the original cost to the balance then checkBalance
  const data = row.value
  const path = process.env.API + '/exp/updSpend'
  paxios(path, data)
  opened.value = false
}
function psd () { showPostDate.value = true }
function delFromDB () {
  const data = row.value
  const path = process.env.API + '/exp/delSpend'
  console.log('-fn-delFromDB', path)
  paxios(path, data)
  opened.value = false
}
function del () {
  const tit = "Delete the Expense"
  const msg = 'Delete the expense "' + row.value.cats + '" on ' + row.value.date +' Permanently? (id=' + row.value.id + ')'
  emitter.emit('open-ConfirmDialog', tit, msg, 'del-spend')
}
function lnkfunc () {
  console.log('-fn-lnk', row.value.link)
  let lnks = row.value.link
  if (lnks == null) {
    lnks = []
  } else if (lnks.indexOf('@') >=0 ) {
    lnks = lnks.split('@')
  } else {
    lnks = [lnks]
  }
  console.log('-fn-lnk.openIt, lnks[]', lnks)
  emitter.emit('open-LnkInput', lnks)
}
function isaUsingGiftCardCourse () {
  let paye = row.value.paye
  let paym = row.value.paym
  if (paym === 'Somerset County Golf Gift Card') return isaSomersetCountyCourse(paye)
  else if (paym === 'Mercer County Golf Gift Card') return isaMercerCountyCourse(paye)
  return false
}
function isaMercerCountyCourse (paye) {
  // return /Mercer Oaks (East||West||GC)/.test(paye) || paye.indexOf('Mountain View Golf Club')>=0 || paye.indexOf('Hopewell Valley Golf Club')>=0 || /Princeton Country Club/.test(paye)
  // const regex = new RegExp('"Mercer Oaks*" || "Mountain View Golf*" || "Hopewell Valley Golf*" || "Princeton Country Club*"', 'gi')
  // const regex = new RegExp('"Mercer Oaks*"||"Mountain View Golf*"||"Hopewell Valley Golf*"||"Princeton Country Club*"', 'gi')
  const regex = new RegExp("Mercer Oaks*|Mountain View Golf*|Hopewell Valley Golf*|Princeton Country Club*", 'gi')
  return regex.test(paye)
}
function isaSomersetCountyCourse (paye) {
  // const regex = new RegExp("Quail Brook*" || "Neshanic Valley*" || "Spooky Brook*" || "Warrenbrook*" || "Green Knoll*")
  const regex = new RegExp("Quail Brook*|Neshanic Valley*|Spooky Brook*|Warrenbrook*|Green Knoll*", 'gi')
  // console.log(`-CK-isaSomersetCountyCourse=${regex.test(paye)} paye=${paye}`, regex)  // this is going to affect the return (always return false)
  return regex.test(paye)
}
function checkBalance (originalCost=0) { // originalCost may not be needed
  // console.log(`-fn-checkBalance, Gift Card Balance=${row.value.gcardVal} Gift Card #=${row.value.gcardNum}`)
  const paye = row.value.paye
  const paymId = row.value.paymId
  if ((paymId == 9 && isaMercerCountyCourse(paye)) || (paymId == 15 && isaSomersetCountyCourse(paye))) {
    if (parseFloat(row.value.cost) > parseFloat(row.value.gcardVal)) {
      gcDisable.value = false
      getGiftCardBalance() // get New Golf Gift Card from GiftCard
      return 'balance low'
    }
  }
  return 'OK'
}
</script>

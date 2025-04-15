<template>
<q-dialog v-model="opened" transition-show="slide-right">
  <q-layout container class="bg-teal-10" style="height:440px;width:400px" :style="action=='upd' ? 'height:490px' :'height:440px'">
    <layoutHeader>
      <template #lbtn><q-btn v-if="action=='add' || action=='upd'" icon="chevron_left" round glossy color="amber-10" v-close-popup /></template>
      <template #ctit><q-toolbar-title class="text-center text-h5">{{ title }}</q-toolbar-title></template>
      <template #rbtn><q-btn v-if="action=='upd'" icon="delete" round glossy color="red" @click="action=doAction('del')" /></template>
    </layoutHeader>
    <q-page-container class="q-pa-xs">
      <div v-if="isDesk" class="row">
        <dtm class="q-pa-xs" label="Match Starting Date Time" :obj="tmnt" txsz="text-h6" :dateTime="teeTime" @upd-dt="setDateTime" />
      </div>
      <div v-else>
        <dtmIM class="q-pa-xs" label="Match Starting Date Time" :obj="tmnt" txsz="text-h6" :dateTime="teeTime" @upd-dt="setDateTime" />
      </div>
      <sel :obj="tmnt" iColor="green" icon="golf_course" label="Select Course"   :optList="courseList" @get-TeeboxList="getTeeboxList" />
      <sel :obj="tmnt" iColor="cyan-2" icon="person_pin" label="Select Mens Tee" :optList="teeboxList" :disable="!tmnt.courseName" @do-action="mat" />
      <sel :obj="tmnt" iColor="pink-4" icon="person_pin" label="Select Lady Tee" :optList="teeboxList" :disable="!tmnt.courseName" />
      <num class="q-pa-xs" :obj="tmnt" icon="monetization_on" label="Green Fee" mask="#.##" iColor="amber" />
      <txt :obj="tmnt" iColor="cyan-3" icon="note" label="Notes" />
    </q-page-container>
    <layoutFooter class="inset-shadow-down" v-if="tmnt.mtee!=null && action=='upd'">
      <template #lbtn><q-btn rounded glossy icon="chevron_left" color="amber-10" label="cancel" v-close-popup /></template>
      <template #rbtn><q-btn rounded glossy icon-right="update" color="pink-10"   label="update" @click="upd()" /></template>
    </layoutFooter>
  </q-layout>
  <ConfirmDialog @user-confirmed="delFromDB" @user-cancelled="opened=false" />
  <NumPad ref="refNumPad" @num-teetimes="setNumTeetimes" @teetime-gap="setTeetimeGap" />
</q-dialog>
</template>
<script setup>
import emitter from 'tiny-emitter/instance'
import dtm from '../components/DateTimePicker'
import dtmIM from '../components/DateTimeIMPicker'
import sel from '../components/MySelection'
import layoutFooter from '../components/LayoutFooter'
import layoutHeader from '../components/LayoutHeader'
import num from '../components/NumInput'
import txt from '../components/TxtInput'
import compDialog from '../components/DialogComponent'
import ConfirmDialog from '../components/ConfirmDialog'
import NumPad from '../components/NumPad'
import { ref, reactive, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { dayFunctions } from 'src/composables/dayFunctions'
import { libFunctions } from 'src/composables/libFunctions'
import { axiosFunctions } from 'src/composables/axiosFunctions'

const $q = useQuasar()
const {yyyymmddHHMM, getDay2 } = dayFunctions()
const {isDesk, isIM } = libFunctions()
const {gaxios, paxios} = axiosFunctions()
const gameName = ref(null)
const courseName = ref(null)
// const mtee = ref(null)
// const ltee = ref(null)
const teeTime = ref(yyyymmddHHMM(new Date()))
const courseList = ref([])
const teeboxList = ref([])
var tmnt = reactive({start_at:teeTime, fees:22, course_id:0})
const action = ref(null)
const actTag = ref(null)
const opened = ref(false)
const game_id = ref(null)
const note = ref(null)
const title = ref(null)
// const footerTit = ref('Set Number of Teetimes')
const teeTimeDone = ref(false)
const tit = ref(null)
const msg = ref(null)
const refNumPad = ref(null)
onMounted(() => refNumPad)

const emit = defineEmits([
  'upd-match',
])

// function section
function getCourseList () {
  console.log('%c-fn-getCourseList', 'color:lime;font-size:medium')
  const path = process.env.API + '/golf/CourseList'
  gaxios(path)
}
function getTeeboxList () {
  console.log(`-fn-getTeeboxList() course=${tmnt.courseName}`)
  setTmntFees()
  tmnt.mtee_id = undefined
  // if (tmnt.course_id === -1) addCourse.value.openIt()
  const path = process.env.API + '/golf/TeeboxList/' + tmnt.course_id
  gaxios(path)
}
function getGameNameList () {
  console.log('-fn-getGameNameList() called')
  const path = process.env.API + '/golf/GameNameList'
  gaxios(path)
}
function openIt (inTmnt, act) {
  opened.value = true
  tmnt.id = inTmnt.id
  tmnt.game_id = inTmnt.game_id
  tmnt.fees = inTmnt.fees
  tmnt.courseName = inTmnt.courseName
  tmnt.course_id = inTmnt.course_id
  tmnt.mtee_id = inTmnt.mtee_id
  tmnt.mtee = inTmnt.mtee
  tmnt.ltee_id = inTmnt.ltee_id
  tmnt.ltee = inTmnt.ltee
  tmnt.note = inTmnt.note
  console.log(`%c-fn-openIt act=${act}`, 'color:lime;font-size:medium', inTmnt)
  action.value = act
  actTag.value = act === 'add' ? 'Create' : 'Update'
  game_id.value = inTmnt.game_id
  teeTime.value = inTmnt.start_at
  getCourseList()
  getGameNameList()

  if (act === 'add') {
    title.value = isIM ? 'Create New Game' : 'Create New Game (' + getDay2(tmnt.start_at) + ')'
    // footerTit.value = 'Set Number of Teetimes'
  } else if (act === 'upd') {
    title.value = isIM ? 'Update the Game' : 'Update the Game (' + getDay2(tmnt.start_at) + ')'
    gameName.value = tmnt.game
    courseName.value = tmnt.courseName
    // mtee.value = tmnt.mtee
    // ltee.value = tmnt.ltee
    action.value = 'upd'
    // footerTit.value = 'upd'
    tmnt.numGroup = 1
  } else if (act === 'del_upd') {
    title.value = 'Delete or Update the Game'
    gameName.value = tmnt.game
    courseName.value = tmnt.courseName
    // mens_tee_id.value = tmnt.mens_tee_id
    // ltee.value = tmnt.ltee
    // fees.value = parseFloat(tmnt.fees)
    note.value = tmnt.note
  }
}
function setDateTime (dt) {
  tmnt.start_at = dt
  teeTimeDone.value = true
  // if (isLocal()) return
  const date = dt.substring(0, 10)
  console.log(`tee time=${tmnt.start_at}, check if there are other appointments on the date=${date}`)
  const path = process.env.API + '/golf/checkReminder/' + date
  gaxios(path)
}
function isScheduled (x) {
  // console.log(`-CK-isScheduled`, x.todo)
  if (x.todo === 'NOTHING_TODO') return
  // const dateAttime = 'on ' + tmnt.value.start_at.replace(' ', ' at ')
  let msg = []
  x.todo.forEach(xr => {
    // console.log(`-fn-todo-scheduled, tag=${xr.tag}, message=${xr.message} x=${x.todo}`)
    msg.push(xr.tag + '; ' + xr.message)
  })
  const tit = 'Check below for conflits with your golf play'
  $q.dialog({
    component: compDialog,
    componentProps: {
      title: tit,
      message: msg,
    },
  }).onDismiss(() => {
    // emitter.on('dismiss-on', (x) => { if (x === 'Cancel') opened.value = false })
    console.log('Triggered on OK or Cancel')
  })
}
function add () {
  console.log('-fn-addTournament()')
  if (!isCompleted()) return
  const inData = tmnt
  inData.id = 0
  const path = process.env.API + '/golf/addTournament'
  paxios(path, inData)
  opened.value = false
  const tit = 'Following New Game Added'
  const msg = '<div class="text-center">Start at ' + tmnt.start_at + '</div><div class="text-center">on Course: ' + tmnt.courseName + '</div>'
  emitter.emit('open-InfoDisplay', tit, msg)
}
function delFromDB () {
  console.log('-fn-delFromDB', tmnt)
  const inData = {}
  inData.id = tmnt.id
  inData.gameId = tmnt.game_id
  inData.cleanupTplayers = 1
  const path = process.env.API + '/golf/delTournament'
  opened.value = false
  paxios(path, inData)
}
function del() {
  console.log('-fn-del', tmnt)
  const tit = 'Confirmation'
  const msg = 'Delete Match at ' + tmnt.courseName + ' on ' + tmnt.start_at + ' ?'
  // confirmDialog.value.openIt(tit, msg)
  emitter.emit('open-ConfirmDialog', tit, msg)
}
function upd () {
  console.log(`-fn-upd`, tmnt)
  if (!isCompleted()) return
  let inData = tmnt
  const path = process.env.API + '/golf/updTournament'
  paxios(path, inData)
  tmnt.date = tmnt.start_at
  tmnt.tmntId = tmnt.id
  emit('upd-match', tmnt)
  opened.value = false
}
function doAction (act=null) {
  console.log(`-fn-doAction act=${act}`, tmnt)
  if (act != null) action.value = act
  tmnt.game_id = game_id.value
  if (action.value === 'can') opened.value = false
  else if (action.value === 'add') add()
  else if (action.value === 'upd') upd()
  else if (action.value === 'del') del()
  else if (action.value === 'mat') mat()
}
function mat () {
  console.log(`set number of teetimes for gameId=${game_id.value} action=${action.value}`)
  if (action.value === 'upd') return
  if (game_id.value === 15) emitter.emit('open-NumPad', 'Select Number of Teetimes', [5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19])
  else refNumPad.value.openIt('Select Number of Teetimes', [1, 2, 3, 4, 5, 6, 7, 8, 9, 10])
  action.value = 'add'
}
function setTmntFees () {
  let course = tmnt.courseName
  console.log(`%c-CK-setTmntFees() teeTime=${teeTime.value} course=${course} test=${/Mercer Oak/.test(course)}`, 'color:lime;font-size:medium')
  if (/Mercer Oak|Hopewell|Mountain View/.test(course)) tmnt.fees = teeTime.value.isWeekend() ? 32 : 22
  else if (/Makefield/.test(course)) tmnt.fees = 32
  else if (/Royce Brook/.test(course)) tmnt.fees = 50
  else if (/Neshanic Valley/.test(course)) tmnt.fees = 72
  else if (/(Quail|Spooky|Warren)\s*Brook/i.test(course)) tmnt.fees = 56
  else tmnt.fees = 77
  // console.log(`%c-CK-setTmntFees() course=${course} test=${/Mercer Oak/.test(course)} fees=${tmnt.fees}`, 'color:lime;font-size:medium')
}
function isCompleted () {
  console.log(`%c-CK-isCompleted course=${tmnt.courseName}`, 'color:lime;font-size:medium')
  msg.value = 'setup completed'
  // setTmntFees()
  if (tmnt.fees === undefined) {
    msg.value = 'Please set green fee'
    console.log(`isCompleted`, tmnt.fees, msg)
  } else if (tmnt.course_id === undefined) {
    msg.value = 'Please select Game Course'
  } else if (tmnt.mtee_id === undefined) {
    msg.value = 'Please select Man\'s Teebox'
  } else if (tmnt.fees === undefined || tmnt.fees == null) {
    msg.value = 'Please set Green Fee'
  } else if (tmnt.start_at == undefined) {
    msg.value = 'Please set up teetime'
  // } else if (tmnt.numGroup === undefined) {
  //   msg.value = 'Please set the Number of Teetimes'
  }
  if (msg.value !== 'setup completed' ) {
    tit.value = 'Incomplete Game Setup'
    // dialogName.value = 'InfoPopup'
    // emitter.emit('open-DialogProxy')
    emitter.emit('open-InfoDisplay', tit.value, msg.value)
    return false
  }
  return true
  // mat()
}
function setNumTeetimes (i) {
  console.log('-fn-setNumTeetimes', i)
  tmnt.numGroup = i
  if (i === 1) {
    // footerTit.value = i + '  Teetime'
    emitter.emit('close-NumPad')
    add()
  } else {
    // footerTit.value = i + '  Teetimes'
    emitter.emit('open-NumPad', 'Select Teetime Gap in Minutes', [8, 9, 10, 11, 12, 20, 30, 40, 50, 60], 'teetime-gap')
  }
}
function setTeetimeGap (i) {
  console.log('-fn-setTeetimeGap', i)
  tmnt.teetime_gap = i
  emitter.emit('close-NumPad')
  add()
}
// === main ===
console.log('-ST-TeamMathCreator')
emitter.on('golf-checkReminder', (x) => { isScheduled(x) })
emitter.on('golf-CourseList', (x) => { courseList.value = x.lst })
emitter.on('golf-TeeboxList', (x) => { teeboxList.value = x.lst })
emitter.on('open-TeamMatchCreator', (x, y) => openIt(x, y))
</script>

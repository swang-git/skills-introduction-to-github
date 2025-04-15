<template>
<div class="bg-teal-10">
  <!-- <q-input v-model="course.name" dark standout dense style="font-size:18px" class="q-py-xs" @focus="newCourse()"> -->
  <q-input ref="refCourseName" v-model="course.name" dark label="Golf Coruse Name" class="text-h6 q-px-sm q-mx-sm">
    <div class="q-pl-md">
      <q-btn v-if="createNewCourse" glossy no-wrap label="Create Course" @click="addCourseTemplate()" class="q-mt-sm q-ma-sm text-cyan-2 bg-red"></q-btn>
      <q-btn v-else rounded glossy no-wrap label="Select Course" @click="showCourseOptions" class="q-ma-sm text-white bg-cyan-9"></q-btn>
    </div>
  </q-input>
</div>
<div v-if="!createNewCourse">
<div v-if="isDesk" class="q-pa-xs bg-teal-9">
  <div class="bg-teal-9" dense style="margin:-2px 4px 2px 4px">
    <div class="row text-h6 text-white">
      <div class="text-center" style="width:20%">Teebox</div>
      <div class="text-center" style="width:20%">Yardage</div>
      <div class="text-center" style="width:20%">Par</div>
      <div class="text-center" style="width:20%">Rating</div>
      <div class="text-center" style="width:20%">Slope</div>
    </div>
    <div v-for="(t, i) in course.trys" :key=t.id class="row" :class="t.teebox===teebox ? getBFColor() : 'bg-teal'">
      <div class="text-center" style="width:20%"><q-btn size="18px" no-caps flat dense @click="setTeebox(i)"           :disable="!SysAdmin">{{ t.teebox  }}</q-btn></div>
      <div class="text-center" style="width:20%"><q-btn size="18px" no-caps flat dense @click="setNumber(i,'yardage')" :disable="!SysAdmin">{{ t.yardage }}</q-btn></div>
      <div class="text-center" style="width:20%"><q-btn size="18px" no-caps flat dense @click="setNumber(i,'par')"     :disable="!SysAdmin">{{ t.par }}</q-btn></div>
      <div class="text-center" style="width:20%"><q-btn size="18px" no-caps flat dense @click="setNumber(i,'rating')"  :disable="!SysAdmin">{{ t.rating  }}</q-btn></div>
      <div class="text-center" style="width:20%"><q-btn size="18px" no-caps flat dense @click="setNumber(i,'slope')"   :disable="!SysAdmin">{{ t.slope   }}</q-btn></div>
    </div>
    <q-card-actions v-if="SysAdmin" align="between">
      <q-btn @click="delTee" glossy color="red-9"  no-caps icon="title">Delete Teebox</q-btn>
      <q-btn @click="addTee" glossy color="blue-9" no-caps icon-right="text_fields">Add Teebox</q-btn>
    </q-card-actions>
  </div>
  <table class="q-pa-xs" style="margin:0 auto">
    <q-tr>
      <q-th colspan="9">
        <q-btn class="q-px-lg" style="font-size:19px;font-weight:400" rounded outline dense no-caps v-if="courseSelected" :class="getBFColor()" @click="getCourseYardage()">
          Yardage for {{ teebox }} Tee:
          <span v-if="fyard>0">&nbsp;{{ fyard }} + {{ byard }} = {{ fyard + byard }} </span><span v-else>~ No Yardage yet</span>
          <Tooltip :clx="'bg-cyan-9 text-yellow text-h6'" :txt="'Click To Change Tee'" />
        </q-btn>
      </q-th>
    </q-tr>
    <q-tr v-if="courseSelected">
      <q-td v-for="i in f9" :key=i>
        <q-btn round size="20px" style="font-weight:400" :label="yard(i)" :class="i==setYardi || setYardi>0 ? yardColor[i] : getBFColor()" outline @click="setYard(i)">
          <Tooltip :txt="Hole(i)" />
        </q-btn>
      </q-td>
    </q-tr>
    <q-tr v-if="courseSelected">
      <q-td v-for="i in b9" :key=i>
        <q-btn round size="20px" style="font-weight:400" :label="yard(i)" :class="i==setYardi || setYardi>0 ? yardColor[i] : getBFColor()" outline @click="setYard(i)">
          <Tooltip :txt="Hole(i)" />
        </q-btn>
      </q-td>
    </q-tr>
    <q-tr><q-th colspan="9" class="th-title text-h6 text-white text-center">Pars of Hole 1 to 18 <span v-if="fpars>0">({{ fpars }} + {{ bpars }})</span></q-th></q-tr>
    <q-tr v-if="courseSelected">
      <q-td v-for="i in f9" :key=i.x>
        <q-btn round size="20px" :label="hole(i)" color="cyan-2" outline :class="selectedParsColor[i]" @click="setPar(i)">
          <q-chip class="text-black text-body2" style="width:25px;height:25px;padding-left:9px" color="grey"> {{ i }} </q-chip>
        </q-btn>
        <Tooltip :clx="'bg-amber text-black text-h6'" :txt="Hole(i)" />
      </q-td>
    </q-tr>
    <q-tr v-if="courseSelected">
      <q-td v-for="i in b9" :key=i.x>
        <q-btn round size="20px" :label="hole(i)" color="cyan-2" outline :class="selectedParsColor[i]" @click="setPar(i)">
          <q-chip class="text-black text-body2" style="width:25px;height:25px;padding-left:5px" color="grey"> {{ i }} </q-chip>
        </q-btn>
        <Tooltip :clx="'bg-amber text-black text-h6'" :txt="Hole(i)" />
      </q-td>
    </q-tr>
    <q-tr><q-th colspan="9" class="th-title text-h6 text-white text-center">Handicaps of Hole 1 to 18 </q-th></q-tr>
    <q-tr v-if="courseSelected">
      <q-td v-for="i in f9" :key=i>
        <q-btn round size="20px" :label="hcap(i)" color="lime-2" outline :class="selectedHcapColor[i]" @click="setHcap(i)" />
        <Tooltip :txt="Hole(i)" />
      </q-td>
    </q-tr>
    <q-tr v-if="courseSelected">
      <q-td v-for="i in b9" :key=i>
        <q-btn round size="20px" :label="hcap(i)" color="lime-2" outline :class="selectedHcapColor[i]" @click="setHcap(i)" />
        <Tooltip :txt="Hole(i)" />
      </q-td>
    </q-tr>
  </table>
  <q-btn-group v-if="SysAdmin && courseSelected" glossy spread class="q-px-xs q-gutter-md">
    <q-btn align="left"   label="delete" @click="delCourse" icon="delete" color="red-9" rounded :disable=disableDelButton />
    <q-btn align="center" label="update" @click="checkInput('upd')" icon="update" color="green-9" :disable=disableUpdButton />
    <!-- <q-btn align="right"  label="create" @click="checkInput('add')" icon-right="create" color="primary" rounded :disable=disableAddButton /> -->
  </q-btn-group>
  <a v-if="course.name=='Bedminster NEW, Trump National GC'" href="/docs/golf/scorecard/Bedminster_new_scorecard.png" target="_blank">Scorecard</a>
  <a v-else-if="course.name=='Bedminster OLD, Trump National GC'" href="/docs/golf/scorecard/Bedminster_old_scorecard.png" target="_blank">Scorecard</a>
  <a v-else-if="course.website!=null" :href="course.website" target="_blank" class="text-h6 text-cyan q-pl-md">Course Website</a>
  <div v-if="course.address!=null" class="text-h6 text-white q-pl-md">Address: {{ course.address }}</div>
  <div v-if="course.phone!=null" class="text-h6 text-yellow q-pl-md">Phone: {{ course.phone }}</div>
</div>
<div v-else class="q-pa-xs bg-teal-10">
  <div class="bg-teal-9" style="margin:-2px 4px 2px 4px">
    <div class="row text-h6 text-white">
      <div class="col-3 text-center">Teebox</div>
      <div class="col-3 text-center">Yardage</div>
      <div class="col-3 text-center">Rating</div>
      <div class="col-3 text-center">Slope</div>
    </div>
    <div v-for="(t, i) in course.trys" :key=t.id class="row" :class="t.teebox===teebox ? getBFColor() : 'bg-teal'">
      <div class="col-3 text-center"><q-btn size="19px" no-caps flat dense @click="setTeebox(i)"            :disable="!SysAdmin">{{ t.teebox  }}</q-btn></div>
      <div class="col-3 text-center"><q-btn size="19px" no-caps flat dense @click="setNumber(i, 'yardage')" :disable="!SysAdmin">{{ t.yardage }}</q-btn></div>
      <div class="col-3 text-center"><q-btn size="19px" no-caps flat dense @click="setNumber(i, 'rating')"  :disable="!SysAdmin">{{ t.rating  }}</q-btn></div>
      <div class="col-3 text-center"><q-btn size="19px" no-caps flat dense @click="setNumber(i, 'slope')"   :disable="!SysAdmin">{{ t.slope   }}</q-btn></div>
    </div>
    <q-btn-group v-if="SysAdmin" spread class="q-pa-sm bg-teal">
      <q-btn @click="delTee" glossy color="red-9"  icon="title">Delete Teebox</q-btn>
      <q-btn @click="addTee" glossy color="blue-9" icon="text_fields">Add Teebox</q-btn>
    </q-btn-group>
  </div>
  <table class="q-pa-xs" style="margin:0 auto">
    <q-tr v-if="courseSelected">
      <q-th colspan="6" class="th-title text-h6 text-center">
        <q-btn class="q-px-lg" style="font-size:19px;font-weight:400" rounded outline dense no-caps v-if="courseSelected" :class="getBFColor()" @click="getCourseYardage()">
          {{ teebox }} Tee : <span v-if="fyard>0">&nbsp;{{ fyard }} + {{ byard }} = {{ fyard + byard }} </span><span v-else>~ No Yardage yet</span>
          <Tooltip :clx="'bg-cyan-9 text-yellow text-h6'" :txt="'Click To Change Tee'" />
        </q-btn>
      </q-th>
    </q-tr>
    <q-tr v-if="courseSelected">
      <q-td v-for="i in f9.slice(0, 6)" :key=i>
        <q-btn round size="18.5px" style="font-weight:400" :label="yard(i)" :class="i==setYardi || setYardi>0 ? yardColor[i] : getBFColor()" outline @click="setYard(i)">
          <q-tooltip>Hole {{i}}</q-tooltip>
        </q-btn>
      </q-td>
    </q-tr>
    <q-tr v-if="courseSelected">
      <q-td v-for="i in f9.slice(6, 9).concat(b9.slice(0, 3))" :key=i>
          <q-btn round size="18.5px" style="font-weight:400" :label="yard(i)" :class="i==setYardi || setYardi>0 ? yardColor[i] : getBFColor()" outline @click="setYard(i)">
          <q-tooltip>Hole {{i}}</q-tooltip>
        </q-btn>
      </q-td>
    </q-tr>
    <q-tr v-if="courseSelected">
      <q-td v-for="i in b9.slice(3, 9)" :key=i>
        <q-btn round size="18.5px" style="font-weight:400" :label="yard(i)" :class="i==setYardi || setYardi>0 ? yardColor[i] : getBFColor()" outline @click="setYard(i)">
          <q-tooltip>Hole {{i}}</q-tooltip>
        </q-btn>
      </q-td>
    </q-tr>
    <q-tr><q-th colspan="9" class="th-title text-h6 text-white text-center">Pars of Hole 1 to 18 <span v-if="fpars>0">({{ fpars }} + {{ bpars }})</span></q-th></q-tr>
    <q-tr v-if="courseSelected">
      <q-td v-for="i in f9.slice(0, 6)" :key=i>
         <q-btn round size="18.5px" :label="hole(i)" color="cyan-2" outline :class="selectedParsColor[i]" @click="setPar(i)">
          <q-chip class="text-black text-body2" style="width:25px;height:25px;padding-left:9px" color="grey"> {{ i }} </q-chip>
        </q-btn>
        <q-tooltip>Hole {{i}}</q-tooltip>
      </q-td>
    </q-tr>
    <q-tr v-if="courseSelected">
      <q-td v-for="i in f9.slice(6, 9).concat(b9.slice(0, 3))" :key=i>
        <q-btn round size="18.5px" :label="hole(i)" color="cyan-2" outline :class="selectedParsColor[i]" @click="setPar(i)">
          <q-chip class="text-black text-body2" style="width:25px;height:25px" :style="getStyle(i)" color="grey"> {{ i }} </q-chip>
        </q-btn>
        <q-tooltip>Hole {{i}}</q-tooltip>
      </q-td>
    </q-tr>
    <q-tr v-if="courseSelected">
      <q-td v-for="i in b9.slice(3, 9)" :key=i>
        <q-btn round size="18.5px" :label="hole(i)" color="cyan-2" outline :class="selectedParsColor[i]" @click="setPar(i)">
          <q-chip class="text-black text-body2" style="width:25px;height:25px;padding-left:5px" color="grey"> {{ i }} </q-chip>
        </q-btn><q-tooltip>Hole {{i}}</q-tooltip>
      </q-td>
    </q-tr>
    <q-tr><q-th colspan="6"  class="th-title text-h6 text-white text-center">Handicaps </q-th></q-tr>
    <q-tr v-if="courseSelected">
      <q-td v-for="i in f9.slice(0, 6)" :key=i>
        <q-btn round size="18.5px" :label="hcap(i)" color="lime-2" outline :class="selectedHcapColor[i]" @click="setHcap(i)" />
        <q-tooltip>Hole {{i}}</q-tooltip>
      </q-td>
    </q-tr>
    <q-tr v-if="courseSelected">
      <q-td v-for="i in f9.slice(6, 9).concat(b9.slice(0, 3))" :key=i.x>
        <q-btn round size="18.5px" :label="hcap(i)" color="lime-2" outline :class="selectedHcapColor[i]" @click="setHcap(i)" />
        <q-tooltip>Hole {{i}}</q-tooltip>
      </q-td>
    </q-tr>
    <q-tr v-if="courseSelected">
      <q-td v-for="i in b9.slice(3, 9)" :key=i>
        <q-btn round size="18.5px" :label="hcap(i)" color="lime-2" outline :class="selectedHcapColor[i]" @click="setHcap(i)" />
      <q-tooltip>Hole {{i}}</q-tooltip>
    </q-td>
  </q-tr>
  </table>
  <q-btn-group v-if="SysAdmin && isCourseSelected" glossy spread class="q-px-xs q-gutter-md">
    <q-btn align="left"   label="delete" @click="delCourse" icon="delete" color="red-9" rounded :disable=disableDelButton />
    <q-btn align="center" label="update" @click="checkInput('upd')" icon="update" color="green-9" :disable=disableUpdButton />
    <!-- <q-btn align="right"  label="create" @click="checkInput('add')" icon="create" color="primary" rounded :disable=disableAddButton /> -->
  </q-btn-group>
  <a v-if="course.name=='Bedminster NEW, Trump National GC'" href="/docs/golf/scorecard/Bedminster_new_scorecard.png" target="_blank">Scorecard</a>
  <a v-else-if="course.name=='Bedminster OLD, Trump National GC'" href="/docs/golf/scorecard/Bedminster_old_scorecard.png" target="_blank">Scorecard</a>
</div>
</div>

<CourseHolePad ref="refCourseHolePad" @set-pars="setParsBGColor" />
<CourseHcapPad ref="refCourseHcapPad" @set-hcap="setHcapBGColor" />
<CourseYardPad ref="refCourseYardPad" @set-yard="setYardBGColor" />
<NumberPadDecimal ref="refNumberPadDecimal" />
<TeeboxPad ref="refTeeboxPad" />
<SelOptionsWithSearch ref="selOptions" @selected-option="selectedCourse" />
</template>
<script setup>
import { ref, onMounted } from 'vue'
// import { useQuasar } from 'quasar'
import emitter from 'tiny-emitter/instance'
import { libFunctions } from '../composables/libFunctions'
const { $q, store, SysAdmin, isDesk } = libFunctions()
import { axiosFunctions } from '../composables/axiosFunctions'
const { gaxios, paxios } = axiosFunctions()
import { storeFunctions } from '../composables/storeFunctions'
const { hole, yard, hcap } = storeFunctions()

import TeeboxPad from './TeeboxPad'
import NumberPadDecimal from './NumberPadDecimal'
import CourseHolePad from './CourseHolePad'
import CourseYardPad from './CourseYardPad'
import CourseHcapPad from './CourseHcapPad'
import SelOptionsWithSearch from 'src/components/SelOptionsWithSearch'
import Tooltip from 'src/components/ToolTip'

const refCourseName = ref(null)
const refCourseHolePad = ref(null)
const refCourseHcapPad = ref(null)
const refCourseYardPad = ref(null)
const refNumberPadDecimal = ref(null)
const refTeeboxPad = ref(null)
onMounted(() => {
  refCourseName,
  refCourseHolePad,
  refCourseHcapPad,
  refCourseYardPad,
  refNumberPadDecimal,
  refTeeboxPad,
  selectedHcapColor.value.fill('bg-teal-10')
  selectedParsColor.value.fill('bg-teal-10')
  yardColor.value.fill(getBFColor())
})

const f9 = [1, 2, 3, 4, 5, 6, 7, 8, 9]
const b9 = [10, 11, 12, 13, 14, 15, 16, 17, 18]
// const columns = [
//   { name: 'teebox', label: 'TEEBOX', field: 'teebox', required: true, style: 'text-align:center' },
//   { name: 'yardage', label: 'YARDGE', field: 'yardage', required: true, style: 'text-align:center' },
//   { name: 'rating', label: 'RATING', field: 'rating', required: true, style: 'text-align:center' },
//   { name: 'slope', label: 'SLOPE', field: 'slope', required: true, style: 'text-align:center;color' }
// ]
var course = ref({})
const courseSelected = ref(false)
const teebox = ref('Black')
var teeboxId = 0
var teeIdx = 0
var courseId = $q.localStorage.getItem('courseId') == null ? 4 : $q.localStorage.getItem('courseId')
// var courseId = 4
const setYardi = ref(0)
// const courseName = ref(null)
// const opened = ref(true)
const disableDelButton = ref(false)
const disableUpdButton = ref(false)
const disableAddButton = ref(false)
// var teeColor = 'Blue'
var fpars = ref(0)
var bpars = ref(0)
var fyard = ref(0)
var byard = ref(0)
var courseList = []
const yardColor = ref(new Array(19))
const selectedParsColor = ref(new Array(19))
const selectedHcapColor = ref(new Array(19))
const createNewCourse = ref(false)

const emit = defineEmits(['act-course'])

emitter.on('add-new-course', () => addNewCourse())

//== main function
console.log('-ST-CourseDetails')
disableAddButton.value = true
getCourseDetails()
getCourseList()

function addNewCourse () {
  console.log('-fn-addNewCourse')
  createNewCourse.value = true
  course.value.name = ''
  // newCourseTemplate()
  // refCourseName.value.focus()
}
// function newCourseTemplate () {
//   course.value.name = 'Type in New Course Name Here!'
//   console.log(`-CK-fn-newCourse courseName=${course.value.name}`, course.value.trys)
//   // disableDelButton.value = true
//   // disableUpdButton.value = true
//   // disableAddButton.value = false
//   // course.value.trys = []
//   // course.value.trys[0] = {couse_id:-1, id:-1, par:72,rating:70.0,slope:111,teebox:'Blue',yardage:6000}
// }
function setParsBGColor (i) {
  selectedParsColor.value[i] = 'bg-red'
  selectedParsColor.value[i + 1] = 'bg-cyan-3'
  for (let i = 1; i <= 18; i++) if (isNaN(hole(i))) return
  if (i == 9) {
    fpars.value = 0
    for (let a = 1; a <= 9; a++) fpars.value += hole(a)
  } else if (i == 18) {
    bpars.value = 0
    for (let a = 10; a <= 18; a++) bpars.value += hole(a)
  }
  let totalPars = fpars.value + bpars.value
  let coursePar = course.value.trys[0].par
  if (totalPars != coursePar) {
    let tit = 'Your Input Pars Not Match with Course Pars'
    let msg = `<div class="text-center">(${fpars.value}` + ' + ' + `${bpars.value}) = ` +  `${totalPars}` + ' != ' + `${coursePar}</div>`
    emitter.emit('open-InfoDisplay', tit, msg)
  }
  // console.log(`-CK-setParsBGColor i=${i} par=${par} totalPars=${totalPars} coursePars=${course.value.trys[0].par}`, holes.value)
}
function setHcapBGColor (i) {
  selectedHcapColor.value[i] = 'bg-red'
  selectedHcapColor.value[i + 1] = 'bg-cyan-3'
  // console.log(`-CK-setHcaps i=${i} hcap=${hcap}`, selectedHcap)
}
function Hole (i) {
  return 'Hole ' + i
}
function getStyle (i) {
  if (i < 10) return 'padding-left:9px'
  else return 'padding-left:5px'
}
function getCourseList () {
  console.log('-CK-fn-getCourseList')
  const path = process.env.API + '/golf/CourseList'
  gaxios(path)
}
function selectedCourse (model, selectedOpt) {
  // console.log('-CK-fn-selectedCourse', selectedOpt)
  courseId = selectedOpt.value
  course.value.name = selectedOpt.cname
  // console.log(`-CK-fn-selectedCourse course.name=${course.value.name} courseId=${courseId}`)
  $q.localStorage.set('courseId', courseId)
  getCourseDetails()
}
function showCourseOptions () {
  emitter.emit('open-SelOptionsWithSearch', 'golf_course', 'Golf Course', courseList)
}
emitter.on('golf-CourseList', (x) => courseList = x.lst)
emitter.on('golf-CourseDetails', (x) => setCourseDetails(x))
function setCourseDetails (da) {
  course.value = da.course
  store.holes = JSON.parse(JSON.stringify(course.value.holes))
  store.yards = JSON.parse(JSON.stringify(course.value.yards))
  store.hcaps = JSON.parse(JSON.stringify(course.value.hcaps))
  teebox.value = course.value.trys[0].teebox
  // console.log(`-CK-fn-setCourseDetails course.yards`, course.value.yards)
  fyard.value = 0
  byard.value = 0
  fpars.value = 0
  bpars.value = 0
  calcFByards()
  calcFBpars()
    // console.log(`-CK-fn-setCourseDetails i=${i} fyard=${fyard}`, yard(i))
  courseSelected.value = true
}
function getCourseDetails () {
  store.pageTitle = 'Course Details'
  store.page = 'course_details'
  console.log(`-CK-fn-getCourseDetails courseId=${courseId}`)
  const path = process.env.API + '/golf/CourseDetails/' + courseId
  gaxios(path)
}
// function setCourse (teeIdx, crs) {
//   console.log('-fn-seCourse', teeIdx, course)
//   teeIdx = teeIdx
//   course.value = crs
// }
// function newCourse () {
//   console.log(`-CK-fn-newCourse courseName=${course.value.name}`)
//   disableAddButton.value = false
//   disableUpdButton.value = false
//   disableDelButton.value = false
//   calcFByards()
// }
// function holeColor (i) {
//   return selectedPars.includes(i) ? 'red' : 'purple'
// }
function setTeebox (i) {
  console.log('-CK-fn-setTeebox', i, course.value)
  refTeeboxPad.value.openIt(course.value.trys[i], course.value.tees)
}
function setNumber (i, tag) {
  console.log(`-CK-fn-setNumber i=${i} tag=${tag}`, course.value)
  let teebox = course.value.trys[i]
  refNumberPadDecimal.value.openIt('standard', tag, course.value.name, teebox)
}
// function getTooltip (i) {
//   return '<q-tooltip>Hole ' + i + '</q-tooltip>'
// }
function getBFColor (i=-1) {
  if (setYardi.value === i) return 'bg-cyan-3 text-red-9'
  let retval = null
  let txtclr = null
  let teeColor = teebox.value.toLowerCase()
  if (teeColor === 'gold') { retval = 'yellow-8'; txtclr = 'grey-9' }
  else if (teeColor === 'black') { retval = 'grey-7'; txtclr = 'lime-1' }
  else if (teeColor === 'forward') { retval = 'red'; txtclr = 'yellow-2' }
  else if (teeColor === 'scarlet') { retval = 'purple-4'; txtclr = 'white' }
  else if (teeColor === 'blended') { retval = 'white'; txtclr = 'blue-8' }
  else if (teeColor === 'player') { retval = 'blue'; txtclr = 'yellow-2' }
  else if (teeColor === 'silver') { retval = 'grey'; txtclr = 'cyan-2' }
  else if (teeColor === 'blue') { retval = 'blue-8'; txtclr = 'lime-1' }
  else if (teeColor === 'white') { retval = 'white'; txtclr = 'black' }
  else if (teeColor === 'red') { retval = 'red'; txtclr = 'yellow-2' }
  else if (teeColor === 'green') { retval = 'green'; txtclr = 'black' }
  else if (teeColor === 'combo') { retval = 'grey-3'; txtclr = 'blue-8' }
  return 'bg-' + retval + ' text-' + txtclr
}
function calcFByards () {
  fyard.value = 0
  byard.value = 0
  // console.log(`-CK-fn-calFBYards teebox=${teebox.value} courseId=${courseId} teeboxId=${teeboxId}`, yards.value)
  for (let i = 1;  i <= 9;  ++i) fyard.value += parseInt(yard(i)) >  9 ? parseInt(yard(i)) : 0
  for (let i = 10; i <= 18; ++i) byard.value += parseInt(yard(i)) > 18 ? parseInt(yard(i)) : 0
  // console.log(`-CK-fn-calFBYards teebox=${teebox.value} courseId=${courseId} teeboxId=${teeboxId} fyard=${fyard.value}`)
}
function calcFBpars () {
  fpars.value = 0
  bpars.value = 0
  for (let i = 1; i <= 9; ++i) fpars.value += hole(i)
  for (let i = 10; i <= 18; ++i)  bpars.value += hole(i)
}
emitter.on('golf-CourseYardage', (x) => setCourseYardage(x))
function setCourseYardage (da) {
  // console.log(`-CK-fn-checkYardage Matched for Teebox=${teebox.value}`, da)
  // courseId = da.course_id
  // teeboxId = da.teebox_id
  // teebox.value = da.teebox // use teebox in course.value.trys which from the course_tees table -- updated if updated in front
  course.value['yards'] = da
  store.yards = JSON.parse(JSON.stringify(da))
  calcFByards()
  if (checkYardage()) console.log(`-OK-checkYardage Matched for Teebox=${teebox.value}`)
  // console.log(`-CK-fn-setCourseYard teebox=${teebox.value} courseId=${courseId} teeboxId=${teeboxId}`, course.value)
  yardColor.value.fill(getBFColor())
}
function getCourseYardage () {
  if (course.value.trys === undefined) {
    teeIdx = 0
    teebox.value = 'Black'
  } else {
    teeIdx++
    teeIdx %= course.value.trys.length
    teebox.value = course.value.trys[teeIdx].teebox
    teeboxId = course.value.trys[teeIdx].id
  }
  // let pars = course.value.trys[teeIdx].par
  // let yardage = course.value.trys[teeIdx].yardage
  // console.log(`%c-fn-getCourseYardage teeIdx=${teeIdx} teebox=${teebox.value} pars=${pars} yardage=${yardage}`, 'color:red')
  // console.table(course.value.trys)
  const path = process.env.API + '/golf/CourseYardage/' + courseId + '/' + teeboxId + '/' + teebox.value
  gaxios(path)
}
function checkYardage () {
  // console.log(`-CK-fn-checkYardage teeIdx=${teeIdx} teebox=${teebox.value}`)
  const fbytotal = fyard.value + byard.value, ydg = course.value.trys[teeIdx].yardage
  if (fbytotal > 0 && fbytotal !== ydg) {
    console.log(`-CK-fn-checkYardage totalYardage=${fbytotal} courseYardage=${ydg} teeIdx=${teeIdx} teebox=${teebox.value}`)
    $q.dialog({ title: 'Please check yardage: ' + fbytotal + ' != ' + ydg + ' for ' + teebox.value + ' Tee'})
    return false
  }
  return true
}
function setPar (i) {
  if (!SysAdmin.value) return
  selectedParsColor.value[i] = 'bg-cyan-3'
  // console.log(`-CK-fn-setPar i=${i}`, selectedPars)
  refCourseHolePad.value.openIt(i, course.value.holes)
}
function setHcap (i) {
  if (!SysAdmin.value) return
  selectedHcapColor.value[i] = 'bg-cyan-3'
  refCourseHcapPad.value.openIt(i, course.value.hcaps)
}
function setYard (i) {
  if (!SysAdmin.value) return
  yardColor.value[i] = 'bg-cyan text-grey-9'
  setYardi.value = i
  // console.log(`-CK-fn-setYard for hole i=${i}`, course.value)
  refCourseYardPad.value.openIt(i, course.value.yards)
}
function setYardBGColor(holeIdx, hyard) {
  console.log(`-CK-fn-setYardBGColor fyard=${fyard.value} holeIdx=${holeIdx} hyard=${hyard} course=${course.value.name}`)
  if (holeIdx > 18) return
  yardColor.value[holeIdx] = 'bg-cyan text-grey-9'
  // if (holeIdx <= 9) fyard.value += hyard
  // else byard.value += hyard
  course.value.yards['y' + holeIdx] = hyard
  store.yards = JSON.parse(JSON.stringify(course.value.yards))
  calcFByards()
  console.log(`-CK-fn-setYardBGColor fyard=${fyard.value} byard=${byard.value} holeIdx=${holeIdx} hyard=${hyard}`, yard(9))
}
function addTee () {
  // console.log(`-CK-fn-addTee courseId=${courseId} courseName=${course.value.name}`)
  disableDelButton.value = true
  disableAddButton.value = true
  disableUpdButton.value = false
  const newTee = {}
  newTee.teebox = 'Color'
  newTee.yardage = 6666
  newTee.rating = 66.6
  newTee.slope = 166
  newTee.par = 72
  newTee.courseId = courseId
  course.value.trys.push(newTee)
}
function delTee () { // delete the last row in the teeboxes, need to click update button
  let del = course.value.trys[teeIdx]
  console.log('detTeebox', del)
  $q.dialog({
    title: '<center style="font-size:30px;font-weight:800">Please Comfirm</center>',
    message: '<center style="font-size:20px">Delete <b><em>' + del.teebox + '</em></b> teebox from <b><em><p>' + course.value.name + '?</em></b></center>',
    html: true,
    ok: 'OK',
    cancel: 'Cancel'
  }).onOk(() => {
    $q.notify('Agreed!')
    del = course.value.trys.splice(teeIdx, 1)[0]
    // console.log('delTee', del)
    const inData = { courseId: del.course_id, teeboxId: del.id }
    const path = process.env.API + '/golf/delTeebox'
    paxios(path, inData)
    getCourseYardage()
  }).onCancel(() => {
    console.log('q.dialog', $q.dialog)
    $q.notify('Disagreed...')
  })
}
function delCourse () {
  console.log('delCourse', course.value)
  $q.dialog({
    title: 'Confirm',
    message: 'Delete the course " ' + course.value.name + ' " ?',
    ok: 'Agree',
    cancel: 'Disagree'
  }).onOk(() => {
    $q.notify('Agreed!')
    const path = process.env.API + '/golf/delCourse'
    // console.log('inData', args.inData)
    emit('act-course', 'del', null)
    paxios(path, course.value)
  }).onCancel(() => {
    $q.notify('Disagreed...')
  })
}
function checkInput (act) {
  if (course.value.name == null || course.value.name == '') {
    console.log('Plesse provide course name')
    $q.dialog({
      title: 'Please type in the new course name'
    })
    return
  }
  let alreadyIn = []
  for (let i = 1; i <= 9; i++) {
    const hcap = store.hcaps['p' + i]
    // console.log('hcap', hcap)
    if (alreadyIn[hcap]) {
      alert('there are dupllcates for ' + hcap + ' please check it again')
      return
    } else alreadyIn[hcap] = true
  }
  alreadyIn = []
  for (let i = 10; i <= 18; i++) {
    const hcap = store.hcaps['p' + i]
    // console.log('hcap', hcap)
    if (alreadyIn[hcap]) {
      alert('there are dupllcates for ' + hcap + ' please check it again')
      return
    } else alreadyIn[hcap] = true
  }
  const pars = []
  fpars.value = 0
  bpars.value = 0
  for (let i = 1; i <= 18; i++) {
    if (i <= 9) fpars.value += hole(i)
    else bpars.value += hole(i)
    pars.push(hole(i))
  }
  const totalPar = pars.reduce((a, b) => a + b, 0)
  // const totalPar = fpars.value + bpars.value
  const par =  course.value.trys[teeIdx].par
  if (totalPar != par) {
    const tit = 'Par is not equal to course par ' + par
    const msg = 'Please check Hole Par settings and Course Par setting ' + totalPar
    emitter.emit('open-InfoDisplay', tit, msg)
  }
  if (act === 'upd') updCourse()
  else if (act === 'add') addCourse()
}
function updCourse () {
  console.log('-CK-fn-updCourse', course.value)
  const path = process.env.API + '/golf/updCourse'
  let inData = course.value
  // console.log('inData', args.inData)
  paxios(path, inData)
}
function addCourse () {
  console.log(`-CK-fn-addCourse`, course.value)
  const path = process.env.API + '/golf/addCourse'
  let inData = course.value
  emit('act-course', 'add', course.value)
  paxios(path, inData)
}
function addCourseTemplate () {
  if (course.value.name == null || course.value.name == '') {
    console.log(`please provide new couse name=${course.value.name}`)
    $q.dialog({
      title: 'Please provide new couse name'
    })
    return
  }
  console.log(`-CK-fn-addCourseTemplate`, course.value)
  course.value.trys = []
  course.value.holes = {}
  course.value.hcaps = {}
  //   h1:'h1',
  //   h2:'h2',
  //   h3:'h3',
  //   h4:'h4',
  //   h5:'h5',
  //   h6:'h6',
  //   h7:'h7',
  //   h8:'h8',
  //   h9:'h9',
  //   h10:'h10',
  //   h11:'h11',
  //   h12:'h12',
  //   h13:'h13',
  //   h14:'h14',
  //   h15:'h15',
  //   h16:'h16',
  //   h17:'h17',
  //   h18:'h18',
  // }
  // course.value.hcaps = {}
  const path = process.env.API + '/golf/addCourse'
  let inData = course.value
  emit('act-course', 'add', course.value)
  paxios(path, inData)
}
</script>

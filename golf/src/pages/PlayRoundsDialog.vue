<template>
<div>
  <q-dialog v-model="opened" persistent transition-show="slide-left">
    <q-layout container class="bg-teal-10" style="width:440px;height:470px;border:2px solid cyan">
      <q-toolbar class="inset-shadow-down">
        <q-btn glossy dense round color="orange" icon='keyboard_arrow_left' v-close-popup />
        <q-toolbar-title class="text-white text-h6"> Recording Scores for {{ compPlayerFirstname }} </q-toolbar-title>
      </q-toolbar>
      <div class="row q-pl-sm">
        <div class="col-7 q-gutter-xs">
          <q-input class="bg-teal-9" :input-style="{ fontSize:'20px', color:'white' }" borderless v-model="gameDate" label="Game Date" color="white">
            <template v-slot:prepend>
              <q-icon name="event" color="amber-10" class="cursor-pointer" size="40px">
                <q-popup-proxy>
                  <q-date v-model="gameDate" dark color="orange" text-color="black" mask="YYYY-MM-DD" today-btn v-if="isIM" />
                  <q-date v-model="gameDate" dark color="orange" text-color="white" mask="YYYY-MM-DD" today-btn v-else landscape >
                    <div class="row items-center justify-end">
                      <q-btn v-close-popup label="Close" color="primary" flat />
                    </div>
                  </q-date>
                </q-popup-proxy>
              </q-icon>
            </template>
          </q-input>
        </div>
        <div class="col-5 q-gutter-xs">
          <q-input class="bg-teal-9" :input-style="{ fontSize:'20px', color:'white' }" borderless v-model="teeTime" now-btn label="Tee Time" color="white">
            <template v-slot:prepend>
              <q-icon name="access_time" color="yellow" class="cursor-pointer" size="40px">
                <q-popup-proxy>
                  <q-time v-model="teeTime" dark color="orange" text-color="black" today-btn v-if="isIM" />
                  <q-time v-model="teeTime" dark color="orange" text-color="black" mask="HH:mm" now-btn v-else landscape>
                    <div class="row items-center justify-end">
                      <q-btn v-close-popup label="Close" color="primary" flat />
                    </div>
                  </q-time>
                </q-popup-proxy>
              </q-icon>
            </template>
          </q-input>
        </div>
      </div>
      <div class="q-pa-md">
        <div class="col-12 q-pa-xs">
          <q-input class="text-body1" dark rounded outlined v-model="course.label" label="Golf Course" @click="showCourseOptions('golf_course', 'Course')" >
              <template v-slot:prepend>
                <q-icon name="golf_course" color="green" />
              </template>
            </q-input>
        </div>
        <div class="col-12 q-pa-xs">
          <q-input class="text-body1" dark rounded outlined v-model="teebox.label" label="From Teebox" @click="showTeeboxOptions('T', 'Teebox')" >
              <template v-slot:prepend>
                <q-icon name="text_fields" color="pink" />
              </template>
            </q-input>
        </div>
      </div>
      <q-input class="q-pa-md text-body1" borderless label="notes" type="textarea" rows="5" v-model="note" dark placeholder="place note here">
        <template v-slot:prepend>
          <q-icon name="note" color="white" />
        </template>
      </q-input>
      <div class="q-px-sm inset-shadow">
        <q-btn-group glossy spread class="q-gutter-xs">
          <q-btn icon="check" label="create" @click="createRound" color="cyan-9"  :disable="compScoreId>0">
          <!-- <q-tooltip class="bg-amber-9 text-body1">add a round</q-tooltip> -->
          </q-btn>
          <q-btn icon="arrow_right" :label="getRoundLabel()" @click="nextRound" color="blue-9" v-if="roundList.length>1" />
          <!-- <q-btn icon="play_circle_filled" label="play" @click="addCheckScore" color="teal" :disable="compScoreId<0"><q-tooltip class="bg-cyan-9 text-body1">add a round</q-tooltip></q-btn> -->
          <q-btn icon="play_circle_filled" label="play" @click="openEnterPScoresDialog" color="teal" :disable="compScoreId<0"><q-tooltip class="bg-cyan-9 text-body1">add a round</q-tooltip></q-btn>
        </q-btn-group>
      </div>
    </q-layout>
  </q-dialog>
</div>
<SelOption ref="refSelOption" @selected-option="selectedOption" />
<EnterPScoresDialog ref="refEnterPScoresDialog" />
</template>

<script setup>
// /* eslint-disable */
import { ref, computed, onMounted } from 'vue'
import emitter from 'tiny-emitter/instance'

import { dayFunctions } from '../composables/dayFunctions'
const { today } = dayFunctions()
import { libFunctions } from '../composables/libFunctions'
const { $q, store } = libFunctions()
import { axiosFunctions } from '../composables/axiosFunctions'
const { gaxios } = axiosFunctions()

import EnterPScoresDialog from 'pages/EnterPScoresDialog'
import SelOption from 'src/components/SelOptionsWithSearch'

// const state = 'none'
var gameDate = today()
var teeTime = ''
const scoreId = ref(-1)
const teebox = ref({})
const teeboxId = ref(0)
const course = ref({})
const courseId = ref(0)
const note = ref(null)
const courseList = []
const teeboxList = []
const roundList = []
var roundIdx = 0
var player = {}
var member = {}
var playerId = 0
var tag = 'play'
const opened = ref(false)
const refEnterPScoresDialog = ref(false)

onMounted(() => {
  refEnterPScoresDialog
})
console.log('-ST-PlayRoundsDialog and getCourseList')
getCourseList ()
emitter.on('golf-getCourseInfo', (x) => setCourseInfo(x))
// emitter.on('golf-getScoreId', (x) => setScoreId(x))

const compPlayerFirstname = computed(() => { return player.firstname })
const compScoreId = computed(() => { return scoreId })

function setMember () {
  const teetime = gameDate + ' ' + teeTime
  member = {
    playerId:playerId,
    courseId:courseId,
    teetime:teetime,
    name:player.name,
    course:player.course,
    teebox:player.teebox,
    teeboxId:player.teeboxId,
    note:note
  }
  console.log(`-fn-setMember playerId=${playerId}`, member)
}
function openEnterPScoresDialog () {
  setMember()
  console.log(`getScoreId playerId=${playerId}`, member)
  refEnterPScoresDialog.value.openIt(member, null, 'hole-mark1', 'hole-mark2')
}
function getRoundLabel () {
  return 'round ' + parseInt(roundIdx + 1)
}
const refSelOption = ref(null)
function showCourseOptions (iconName, model) {
  refSelOption.value.openIt(iconName, model, courseList)
}
function showTeeboxOptions (iconName, model) {
  refSelOption.value.openIt(iconName, model, teeboxList)
}
function selectedOption (model, opt) {
  if (model === 'Course') {
    course.value = opt
    teebox.value = {}
    console.log(`-fn-selectedOption-${model} ${opt.value}`)
    courseId.value = opt.value
    getTeeboxList()
  } else if (model === 'Teebox') {
    console.log(`-fn-selectedOption-${model} ${opt.value}`)
    teebox.value = opt
    teeboxId.value = opt.value
    getCourseInfo()
  }
}
function getCourseList () {
  const path = process.env.API + '/golf/CourseList'
  gaxios(path)
}
function nextRound () {
  roundIdx += 1
  if (roundIdx === roundList.length) roundIdx = 0
  scoreId.value = roundList[roundIdx].id
  note.value = roundList[roundIdx].note
  course.value = roundList[roundIdx].courseId
  course.value.label = roundList[roundIdx].course
  getTeeboxList()
  teebox.value = roundList[roundIdx].teeboxId
  teebox.value.label = roundList[roundIdx].teebox
  const teetime = roundList[roundIdx].teetime.split(' ')
  gameDate = teetime[0]
  teeTime = teetime[1]
  console.info('-fn-nextRound roundIdx, scoreId, gameDate, teeTime', roundIdx, scoreId, gameDate, teeTime)
  if (scoreId.value === null || scoreId.value < 0) {
    $q.dialog({ title: 'Please create the play first then click on Play button' })
  }
}
function createRound () {
  console.info('-CK-fn-createRound', course)
  if (course.value === null || course.value === undefined) {
    $q.dialog({ title: 'Please select course played' })
    return
  } else if (teebox.value.label === undefined || teebox.value.label === null) {
    $q.dialog({ title: 'Please select teebox' })
    return
  }
  player.scoreId = null
  // const args = getData()
  setMember()
  console.log(`-CK-fn-createRound scoreId=${scoreId.value}`)
  // args.enterScoresDialog = $refs.enterScoresDialog
  // state = 'create_round'
  roundList.push({})
  // axiosPost(args)
  // $q.dialog({ title: 'Play has been created, Click on Play button to enter your scores' })
  refEnterPScoresDialog.value.openIt(member, null, 'hole-mark1', 'hole-mark2')
}
// function updateRoundList () {
//   const round = roundList[roundIdx]
//   round.courseId = course.value
//   round.course = course.label
//   round.id = scoreId
//   round.note = note
//   round.teebox = teebox.label
//   round.teeboxId = teebox.value
//   round.teetime = gameDate + ' ' + teeTime
// }
defineExpose({openIt})
function openIt (m, tg) {
  console.log(`-CK-fn-PlayRoundsDialog.openIt -- player, tag=${tag} player`, m)
  roundIdx = 0
  tag = tg
  player = m
  player.name = m.lastname + ', ' + m.firstname
  // player.course = course
  // const playerId = m.id
  playerId = m.id
  let path = null
  if (tag === 'play') {
    path = process.env.API + '/golf/RoundList/' + playerId
  } else {
    path = process.env.API + '/golf/PlayedRoundList/' + playerId
  }
  gaxios(path)
  opened.value = true
}
function getTeeboxList () {
  // if (course.value === undefined) return
  if (courseId.value === undefined) return
  teebox.value = {}
  console.info('-CK- getTeeboxList for course', course.value)
  player.course = course.value.label
  player.courseId = course.value
  // const args = { vm: this }
  let path = process.env.API + '/golf/TeeboxList/' + player.courseId
  // args.target = 'golf.TeeboxList'
  gaxios(path)
}
function getCourseInfo () {
  console.log(`-fn-getCourseInfo course=${course.value.label} teebox.label=${teebox.value.label} courseId=${courseId.value} teeboxId=${teeboxId.value}`)
  player.teeboxId = teebox.value
  player.teebox = teebox.value.label.split(' ~ ')[0]
  const path = process.env.API + '/golf/getCourseInfo/' + courseId.value + '/' + teeboxId.value
  gaxios(path)
}
function setCourseInfo (da) {
  console.log(`-fn-setCourseInfo-`, da)
  store.holes = da.pars
  store.yards = da.yards
  store.hcaps = da.hcaps
  store.slope = da.slope
  store.rating = da.rating
  store.yardage = da.yardage
}
</script>

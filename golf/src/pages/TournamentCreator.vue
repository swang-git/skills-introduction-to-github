<template>
<div class="q-pa-md q-gutter-sm">
  <q-dialog v-model="opened" transition-show="scale" persistent :maximized='isIM'>
    <q-layout container class="bg-teal-9" style="max-height:560px;max-width:400px">
      <q-header elevated class="bg-teal-9 inset-shadow-down text-center">
        <q-toolbar>
          <q-toolbar-title v-if="action==='Create'"> Create Tournament </q-toolbar-title>
          <q-toolbar-title v-if="action==='Update'"> Update Tournament </q-toolbar-title>
          <!-- <q-btn round color="yellow-10" glossy icon='keyboard_arrow_right' v-close-popup /> -->
        </q-toolbar>
      </q-header>
      <div class="q-pa-xs">
        <div class="q-gutter-y-xs q-pt-xl">
          <DateTimePicker :obj="tmnt" :dateTime="tmnt.start_at" txsz="text-h6" label="tournament start at" @upd-dt="setDateTime"/>
          <MySelection :obj="tmnt" icon="filter_4"    iColor="amber" label="Select Game" :optList="gameNameList" />
          <!-- <MySelection :obj="tmnt" icon="golf_course" iColor="red" label="Select Course" :optList="courseList" @set-opt="setOpt" /> -->
          <MySelection :obj="tmnt" icon="golf_course" iColor="red" label="Select Course" :optList="courseList" @get-TeeboxList="getTeeboxList" />
          <MySelection :obj="tmnt" icon="person_pin"  iColor="blue" label="Select Mens Tee" :optList="teeboxList" />
          <MySelection :obj="tmnt" icon="person_pin_circle" iColor="pink" label="Select Lady Tee" :optList="teeboxList" />

          <div class="row bg-teal-9">
            <numInput class="col-6" :obj="tmnt" icon="monetization_on" label="Green Fees" iColor="amber" />
            <numInput class="col-6" :obj="tmnt" icon="av_timer" label="Tee Time Gap" iColor="red" />
          </div>
          <txtInput :obj="tmnt" icon="note" label="Notes" iColor="white" style="height:60px" />
          <div style="margin-top:0px">
            <LayoutFooter tit="NOTE_LINK" @do-action="doAction" >
              <template v-slot:rbtn>
                <q-btn v-if="action==='Create'" glossy color="primary" :label="action" icon="add_circle" @click="addTournament" />
                <q-btn v-else-if="action==='Update'" glossy color="primary" :label="action" icon="update" @click="updTournament" />
              </template>
              <template v-slot:lbtn>
                <q-btn color="amber-9" glossy round icon="chevron_left" v-close-popup />
              </template>
            </LayoutFooter>
          </div>
        </div>
      </div>
    </q-layout>
  </q-dialog>
  <LnkInput ref="refLnkInput" @upd-link="updLinks" />
  <NotePad ref="refNotePad" @save-details="saveNotes" />
  <SelOptionsWithSearch />
</div>
</template>
<script setup>
import emitter from 'tiny-emitter/instance'
import { ref, onMounted } from 'vue'
import { axiosFunctions } from '../composables/axiosFunctions'
import { libFunctions } from '../composables/libFunctions'
import DateTimePicker from '../components/DateTimePicker.vue'
import LayoutFooter from '../components/LayoutFooter.vue'
import MySelection from '../components/MySelection.vue'
import numInput from '../components/NumInput.vue'
import txtInput from '../components/TxtInput.vue'
import LnkInput from '../components/LnkInput.vue'
import NotePad from '../components/NotePad.vue'
import SelOptionsWithSearch from '../components/SelOptionsWithSearch.vue'

const { gaxios, paxios } = axiosFunctions()
// const { getNNextSunday } = dayFunctions()
const { isIM, ENV_API } = libFunctions()
const tit = ref(null)
const courseList = ref([])
const gameNameList = ref([])
const teeboxList = ref([])
const tmnt = ref({fees: 100, teetime_gap: 20 })
const action = ref(null)
const opened = ref(false)
const refNotePad = ref(null)
const refLnkInput = ref(null)

onMounted(() => { refNotePad })
console.log('-ST-TournamentCreator')
emitter.on('open-TournamentCreator', (tmnt, bol, act) => openIt(tmnt, bol, act))
emitter.on('golf-GameNameList', (x) => gameNameList.value = x.lst)
emitter.on('golf-CourseList', (x) => courseList.value = x.lst)
emitter.on('golf-TeeboxList', (x) => teeboxList.value = x.lst)

// const compTeeboxList = computed(() => { return teeboxList.value })

function saveNotes (x) {
  console.log(`-CK-fn-saveNotes ${x}`)
  tmnt.value.note = x
}
function updLinks(lnks) {
  console.log('-CK-fn-updLinks', tmnt.value.links)
  tmnt.value.links = lnks.join('@')
}
function openIt (tt, bol, act) {
  console.log(`-fn-TmntCreator-openIt act=${act} courseId=${tt.course_id}`, tt)
  opened.value = bol
  action.value = act
  tit.value = 'Tournament'
  tmnt.value = tt
  tmnt.value.start_at = tt.start_at
  // let links = tmnt.value.links.split('@')
  // tmnt.value.links = links
  getCourseList()
  getGameNameList()
  if (tmnt.value.course_id > 0) getTeeboxList()
}
function setDateTime (dt) {
  tmnt.value.start_at = dt
  tmnt.value.disptm = dt.substring(5, 10) + ' ' + dt.substring(9)
}
function setOpt (label, opt) {
  console.log(`-CK-fn-setOpt label=${label}`, opt)
  if (label === 'Select Course') {
    tmnt.value.course_id = opt.value
    getTeeboxList()
  }
  console.log('tmnt', label, tmnt.value)
}
// function createNewGameName () {
//   if (game_id.value === -1) {
//     newOption.model = 'GameName'
//     emitter.emit('open-addNewOptionDialog', 'Add New GameName')
//   }
// }
function doAction (act) {
  console.log(`-CK-fn-doAction act=${act}`)
  // act === 'upd' ? updTournament() : act === 'add' ? addTournament() : addNotes()
  act === 'msg' ? showNotePad() : act === 'lnk' ? showLnkInput() : null
  // if (act === 'save') this.saveNotes(x)
}
function showNotePad () {
  // emitter.emit('open-NotePad', tmnt.value.note)
  refNotePad.value.openIt(tmnt.value.note)
}
function showLnkInput () {
  // emitter.emit('open-LnkInput', tmnt.value.link)
  refLnkInput.value.openIt(tmnt.value.links)
}
function addTournament () {
  console.log(`-fn-addTournament`)
  const path = ENV_API + '/golf/addTournament'
  console.log('addTournament inData', tmnt.value)
  paxios(path, tmnt.value)
  opened.value = false
}
function updTournament () {
  const path = ENV_API + '/golf/updTournament'
  paxios(path, tmnt.value)
  opened.value = false
}
function getCourseList () {
  console.log('getCourseList() called')
  const path = ENV_API + '/golf/CourseList'
  gaxios(path)
}
function getTeeboxList () {
  console.log(`-fn-getTeeboxList course_id=${tmnt.value.course_id}`)
  if (tmnt.value.course_id === -1) this.$refs.addCourse.openIt()
  const path = ENV_API + '/golf/TeeboxList/' + tmnt.value.course_id
  gaxios(path)
}
function getGameNameList () {
  console.log('-fn-getGameNameList')
  const path = ENV_API + '/golf/GameNameList'
  gaxios(path)
}
</script>

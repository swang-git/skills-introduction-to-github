<template>
<div class="q-pa-md q-gutter-sm">
  <q-dialog v-model="opened" transition-show="scale" persistent :maximized='isIM'>
    <q-layout container class="bg-teal-9" style="maxHeight:560px;maxWidth:400px">
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
          <selection :obj="tmnt" icon="filter_4"    iColor="amber" label="Select Game" :optList="gameNameList" />
          <selection :obj="tmnt" icon="golf_course" iColor="red" label="Select Course" :optList="courseList" @set-opt="setOpt" />
          <selection :obj="tmnt" icon="person_pin"  iColor="blue" label="Select Mens Tee" :optList="teeboxList" />
          <selection :obj="tmnt" icon="person_pin_circle" iColor="pink" label="Select Lady Tee" :optList="teeboxList" />

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
import { axiosFunctions } from 'src/composables/axiosFunctions'
// import { dayFunctions } from 'src/composables/dayFunctions'
import { libFunctions } from 'src/composables/libFunctions'
import DateTimePicker from 'src/components/DateTimePicker'
import LayoutFooter from 'src/components/LayoutFooter'
import selection from 'src/components/MySelection'
import numInput from 'src/components/NumInput.vue'
import txtInput from 'src/components/TxtInput.vue'
import LnkInput from 'src/components/LnkInput'
import NotePad from 'src/components/NotePad'
import SelOptionsWithSearch from 'src/components/SelOptionsWithSearch'

const { gaxios, paxios } = axiosFunctions()
// const { getNNextSunday } = dayFunctions()
const { isIM } = libFunctions()
const tit = ref(null)
// const gameName = ref(null)
// const courseName = ref(null)
// const mtee = ref(null)
// const ltee = ref(null)
// const gameDate = getNNextSunday().yyyymmdd()
// const dialogResponse = ref(undefined)
const courseList = ref([])
const gameNameList = ref([])
const teeboxList = ref([])
// const tmnt = ref({ start_at:getNNextSunday() })
// const tmnt = ref({note:'Tournament Notes'})
const tmnt = ref({})
const action = ref(null)
const opened = ref(false)
// const start_at = ref(getNNextSunday())
// const game_id = ref(null)
const refNotePad = ref(null)
const refLnkInput = ref(null)
// const note = ref('')

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
  console.log('-fn-openIt act', act, tt)
  opened.value = bol
  action.value = act
  tit.value = 'Tournament'
  tmnt.value = tt
  tmnt.value.start_at = tt.start_at
  let links = tmnt.value.links.split('@')
  tmnt.value.links = links
  getCourseList()
  getGameNameList()
  if (tmnt.value.course_id > 0) getTeeboxList()
}
function setDateTime (dt) {
  tmnt.value.start_at = dt
  tmnt.value.disptm = dt.substring(5, 10) + ' ' + dt.substring(9)
}
function setOpt (label, opt) {
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
  console.log('addTournament() called')
  const path = process.env.API + '/golf/addTournament'
  console.log('addTournament inData', tmnt.value)
  paxios(path, tmnt.value)
  opened.value = false
}
function updTournament () {
  const path = process.env.API + '/golf/updTournament'
  paxios(path, tmnt.value)
  opened.value = false
}
function getCourseList () {
  console.log('getCourseList() called')
  const path = process.env.API + '/golf/CourseList'
  gaxios(path)
}
function getTeeboxList () {
  console.log('getTeeboxList() called course_id', tmnt.value.course_id)
  if (tmnt.value.course_id === -1) this.$refs.addCourse.openIt()
  const path = process.env.API + '/golf/TeeboxList/' + tmnt.value.course_id
  gaxios(path)
}
function getGameNameList () {
  // console.log('getGameNameList() called')
  const path = process.env.API + '/golf/GameNameList'
  gaxios(path)
}
</script>

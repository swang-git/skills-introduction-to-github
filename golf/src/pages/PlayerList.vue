<template>
<div class="bg-teal-10" :style="{ 'margin-left': isDesk ? '10px' : '-10px' }">
  <div class="q-pl-md text-body1 text-white" icon="people" color="teal"> Total {{ memberCount }} Players in Club</div>
  <q-toolbar-title>
    <q-btn v-show="SysAdmin" glossy rounded color="teal-9" label="add new player" icon="person_add" @click="openMemberDialog('Create', {}) " style="float:right;margin:0 0 0 0" />
    <div class="q-pl-xs q-gutter-sm">
      <q-chip class="q-pl-sm" icon="sort" color="teal"> Sort by
      <q-radio keep-color inverted dark v-model="sortby" val="club" label="Club" color="pink"   @input="doSorting()" />
      <q-radio keep-color inverted dark v-model="sortby" val="net"  label="Net"  color="orange" @input="doSorting()" />
      <q-radio keep-color inverted dark v-model="sortby" val="none" label="None" color="white"  @input="doSorting()" />
      </q-chip>
    </div>
  </q-toolbar-title>
  <q-toolbar-title flat v-for="(m, i) in dalist" :key=m>
    <q-btn style="margin-left:-20px" @click="playARound(m)" no-caps>
    <q-chip color="teal-9" text-color="white" style="width:210px" size="18px">
      <q-avatar> <img :src="getAvatar(m)"> </q-avatar> {{m.lastname}} {{m.firstname}}
    </q-chip>
    </q-btn>
    <span v-if="SysAdmin" style="margin-left:-40px">
      <q-btn @click="delMember(m, i)" round icon="cancel" color="teal-9" text-color="yellow" />
      <q-btn @click="openMemberDialog('Update', m, i)" icon="edit" color="amber" text-color="pink" size="12px" round glossy />
      <q-btn @click="addMemberDialog('Add Member', m, i)" icon="add_circle" color="green" text-color="amber" size="12px" round glossy />
    </span>
    <span v-else style="margin-left:-20px">
      <q-btn @click="getScores(m)"    round color="green" icon="golf_course" class="text-red" style="margin-left:-10px" ><q-tooltip class="bg-cyan text-body1">Get Your Scores</q-tooltip></q-btn>
      <q-btn size="md" color="white"  round outline style="margin-left:-0px"><q-tooltip class="bg-blue text-body1">Club Index for player with player_id: {{ m.id }}</q-tooltip>{{ m.cidx }}</q-btn>
      <q-btn size="md" color="yellow" round outline style="margin-left:-0px"><q-tooltip class="bg-amber-9 text-body1">Net Progress</q-tooltip>{{ m.nidx }}</q-btn>
      <q-btn @click="getScores(m)"    round outline color="white">
        <q-icon :name="m.type" style="padding:0 1.6px 10px 0" size="25px" color="yellow" />
        <q-tooltip class="bg-red text-body1">Member Status</q-tooltip>
      </q-btn>
    </span>
  </q-toolbar-title>
  <MemberDialog ref="refMemberDialog" @updMemberList="updMemberList($event)" />
  <PlayRoundsDialog ref="refPlayRoundsDialog" />
  <ScoreList ref="refScoreList" />
  <!-- <EnterPScoresDialog ref="refEnterPScoresDialog" /> -->
</div>
</template>
<script setup>
import { ref, computed, onMounted } from 'vue'
// import { useQuasar } from 'quasar'
// const q = useQuasar()
import emitter from 'tiny-emitter/instance'

import { libFunctions } from '../composables/libFunctions'
const { $q, store, dalist, isDesk, SysAdmin, dats, searchQuery, buildApp } = libFunctions()
import { axiosFunctions } from '../composables/axiosFunctions'
const { gaxios } = axiosFunctions()
// import { dayFunctions } from '../composables/dayFunctions'
// const { yyyymmdd } = dayFunctions()

import MemberDialog from '../components/MemberDialog'
import ScoreList from 'pages/ScoreList'
import PlayRoundsDialog from 'pages/PlayRoundsDialog'
// import EnterPScoresDialog from 'pages/EnterPScoresDialog'
// var golf_usertype = null
const sortby = 'none'
// var removeId = -1
// const clickedMember = {}
// const scores = []
// const mtype = 'edit'
const membersBak = []
const members = ref([])
const refMemberDialog = ref(null)
const refPlayRoundsDialog = ref(null)
const refScoreList = ref(null)

onMounted(() => {
  refMemberDialog
  refPlayRoundsDialog
  refScoreList
})

console.log('-ST-PlayerList with pageTitle:"' + store.pageTitle + '"', store.page)
emitter.on('search', (x) => { searchQuery.value = x })
// emitter.on('golf-usertype', (x) => { golf_usertype = x })
emitter.on('golf-getMemberList', (x) => { setMemberList(x) })
emitter.on('golf-Scores', (x) => { setScores(x) })
store.pageTitle = 'List of Players'
store.page = 'PlayerList'

buildApp('Players', 'Golf')
getMemberList()

const memberCount = computed(() => { return dalist.value.length })

function setScores (da) {
  console.log('-CK-fn-setScores', da)
  refScoreList.value.openIt(da.scores, da.playerId)
  // dats.value = da.lst
  // emitter.emit('dats', dats)
}
function setMemberList (da) {
  // console.log('-CK-fn-setMemberList', da)
  dats.value = da.lst
  // emitter.emit('dats', dats)
}
function doSorting () {
  if (sortby === 'club') {
    console.log('-Ck-fn-doSoring', sortby, membersBak.length)
    var members = members.filter((p) => p.cidx > 0)
    members = members.slice().sort((a, b) => {
      a = parseFloat(a.cidx)
      b = parseFloat(b.cidx)
      return (a === b ? 0 : a > b ? 1 : -1)
    })
  } else if (sortby === 'net') {
    members = members.filter((p) => p.nidx > 0)
    members = members.slice().sort((a, b) => {
      a = parseFloat(a.nidx)
      b = parseFloat(b.nidx)
      return (a === b ? 0 : a < b ? 1 : -1)
    })
  } else {
    members = membersBak
  }
  // console.log('-Ck-doSoring', sortby, members.length)
}
function getScores (member) {
  const path = process.env.API + '/golf/Scores/' + member.id
  gaxios(path)
}
function playARound (member) {
  refPlayRoundsDialog.value.openIt(member, 'play')
}
// function getBackground (i) {
//   return i % 2 === 0 ? 'background:teal' : 'background:teal-9'
// }
// function getIdx (m) {
//   for (let i = 0; i < members.length; i++) {
//     if (members[i].id === m.id) {
//       // console.log('m.id, p.id', m.id, i)
//       return i
//     }
//   }
//   return -1
// }
function openMemberDialog (act, member, idx = 0) {
  member.act = act
  // console.table(member)
  refMemberDialog.value.openIt(act, member, idx)
}
function addMemberDialog (act, member) {
  member.act = act
  // console.table(member)
  refMemberDialog.value.openIt(act, member, true, 15)
}
function delMember (m) {
  console.log('delMemer', m)
  // removeId = idx
  $q.dialog({
    title: `Confirm: Remove ${m.lastname} ${m.firstname}?`,
    ok: 'Yes',
    cancel: 'Cancel'
  }).onOk(() => {
    console.log('-CK-fn-delMember YES')
    const path = process.env.API + '/golf/delMember/' + m.id + '/' + m.mid
    gaxios(path)
  }).onCancel(() => { console.log('-CK-fn-delMember Cancelled') })
}
function updMemberList (newMember) {
  console.log('-CK-fn-updMemberlist-newMember', newMember)
  // mtype.value = newMember.type
  if (newMember.act === 'Create') members.value.unshift(newMember)
}
// function memPerformance () {
//   console.log('-CK-memPerformance show member performance curve')
// }
function getMemberList () {
  const path = process.env.API + '/golf/getMemberList'
  gaxios(path)
}
function getAvatar (m) {
  return m.gender === 'F' ? 'icons/girl.png' : 'icons/boy.png'
}
</script>

<template>
<q-dialog v-model="opened" :maximized="isIM">
  <div class="bg-teal-9 q-pa-sm" style="width:400px">
    <q-toolbar glossy>
      <!-- <q-btn glossy round v-close-popup icon="keyboard_arrow_left" color="amber-9" /> -->
      <q-toolbar-title v-if="isClubMember" class="text-white text-h6 text-center"> {{ action }} for {{ year }} Membership</q-toolbar-title>
      <q-toolbar-title v-else class="text-white text-h6 text-center">{{ action }} Player for Team Match</q-toolbar-title>
    </q-toolbar>
    <q-card class="bg-teal-10">
      <q-card-section>
        <div class="row">
          <q-input outlined rounded class="col-6 q-px-xs" v-model='firstname' label='First Name' dark />
          <q-input outlined rounded class="col-6" v-model='lastname' label='Last Name' dark />
        </div>
      </q-card-section>
      <q-card-section style="margin-top:-30px">
        <q-radio keep-color dark v-model="gender" val="F" label="Female" color="red-6" class="text-white text-body1" />
        <q-radio keep-color dark v-model="gender" val="M" label="Male"   color="green" class="text-white text-body1" />
      </q-card-section>
      <q-card-section v-if="isClubMember" style="margin-top:-24px">
        <q-radio keep-color dark v-model="mtype"  val="H" @click="setFees()" label="Honor"      color="yellow-9" class="text-white text-body1" />
        <q-radio keep-color dark v-model="mtype"  val="R" @click="setFees()" label="Member"     color="yellow-8" class="text-white text-body1" />
        <q-radio keep-color dark v-model="mtype"  val="C" @click="setFees()" label="Couple"     color="yellow-6" class="text-white text-body1" />
        <q-radio keep-color dark v-model="mtype"  val="D" @click="setFees()" label="Developer"  color="yellow-4" class="text-white text-body1" />
        <q-radio keep-color dark v-model="mtype"  val="N" @click="setFees()" label="Non-Member" color="yellow-2" class="text-white text-body1" />
        <q-radio keep-color dark v-model="mtype"  val="G" @click="setFees()" label="Guest"      color="amber-10" class="text-white text-body1" />
      </q-card-section>
      <q-card-section style="margin-top:-24px">
        <div class="row">
          <q-input v-if="isClubMember" outlined rounded class="col-6" v-model='fees' label='Membership Fees' type="number" deciamls=2 numeric-keyboard-toggle prefix="$" dark />
          <q-input outlined rounded :class="{ 'col-6':isClubMember, 'col-12':!isClubMember }" v-model='phone' dark label='Phone' type="tel" color="teal-10" />
        </div>
        <div class="row">
          <q-input outlined rounded class="col-6 q-pt-xs" v-model='chname' dark label='中文姓名' type="text" color="teal-10" />
          <q-input outlined rounded class="col-6 q-pt-xs" v-model='nkname' dark label='别名(微信名)' type="text" color="teal-10" />
        </div>
        <q-input outlined class="q-pt-xs" rounded v-model='email' dark label='Email' type="email" color="teal-10" />
      </q-card-section>
      <q-card-actions align="between">
        <q-btn label="Cancel" @click="opened=false" icon="cancel" glossy color="amber-9" />
        <q-btn :label="action" @click="checkInput()"  icon="add_circle" glossy color="teal-9" />
      </q-card-actions>
    </q-card>
  </div>
</q-dialog>
</template>
<script setup>
/* eslint-disable */
import { ref } from 'vue'
import { useQuasar } from 'quasar'
const $q = useQuasar()
import { axiosFunctions } from '../composables/axiosFunctions'
const { paxios } = axiosFunctions()
import { libFunctions } from '../composables/libFunctions'
const { isIM, $store, dalist, isDesk, SysAdmin } = libFunctions()
var year = (new Date()).getFullYear()
const fees = ref(0)
const mtype = ref('G')
var action = undefined
const lastname = ref(null)
const firstname = ref(null)
const chname = ref(null)
const nkname = ref(null)
const email = ref(null)
const phone = ref(null)
const gender = ref(null)
const playerId = ref(null)
var member = {}
var opened = ref(false)
var gameId = null
var isClubMember = false

console.log('-ST-MemberDialog')

function setFees () {
  console.log(`-CK-mtype=${mtype.value}`)
  switch (mtype.value) {
    case 'H':
      fees.value = 1000
      break
    case 'R':
      fees.value = 150
      break
    case 'C':
      fees.value = 100
      break
    case 'D':
      fees.value = 50
      break
    case 'T':
      fees.value = 30
      break
    default:
      fees.value = 0
  }
  // console.log(`-CK-mtype=${mtype.value} fees=${fees}`)
}
defineExpose({openIt})
function openIt (act, m, isCMember=true, gameId) {
  console.log(`-CK-fn-openIt-act=${act} isCMember=${isCMember} gameId=${gameId}`, m)
  action = act
  isClubMember = isCMember
  gameId = gameId
  member = m
  lastname.value = m.lastname == 'Add Member' ? null : m.lastname
  firstname.value = m.firstname
  email.value = m.email
  phone.value = m.phone
  chname.value = m.chname
  nkname.value = m.nkname
  gender.value = m.gender
  fees.value = m.fees
  playerId.value = m.id
  mtype.value = isClubMember ? 'R' : undefined
  setFees()
  // console.table(m)
  opened.value = true // must be the last
}
// function XXXaxiosBack (target, da) {
//   if (target === 'golf.addMember') {
//     $emit('added-new-player', da.newPlayer)
//   } else if (target === 'golf.updMember') {
//     // $emit('added-new-player', da.newPlayer)
//     console.log('-ab-updMember', da.updatedMember)
//   }
// }
function addMember () {
  console.log('-fn-addMember')
  console.table(member)
  const path = process.env.API + '/golf/addMember'
  // emit('add-membership', mtype)
  member.type = mtype.value
  paxios(path, member)
  opened.value = false
}
function updMember () {
  console.log('-fn-updMember')
  console.table(member)
  const path = process.env.API + '/golf/updMember'
  // emit('add-membership', mtype)
  member.type = mtype
  paxios(path, member)
  opened.value = false
}
function saveMembership () {
  console.log('-fn-save membership')
  console.table(member)
  const path = process.env.API + '/golf/addPGCMembership'
  // emit('add-membership', mtype)
  member.type = mtype
  paxios(path, member)
  opened.value = false
}
function saveMember () {
  if (gameId < 10) {
    return saveMembership()
  }
  console.log('-fn-save member ')
  console.table(member)
  // const args = { vm: this }
  let path = process.env.API + '/golf/updMember'
  // args.target = 'golf.getMemberList'
  if (action === 'Create') {
    path = process.env.API + '/golf/addMember'
    // args.target = 'golf.getMemberList'
    // args.target = 'new-pgc-member'
  }
  member.year = year
  member.act = action
  if (gameId > 0) member.team = gameId
  // args.inData = member
  axiosPost(path, member)
  opened.value = false
}
// function delMember () {
//   console.log(`-fn-delMember to be construct action=${action} player_id=${member.player_id}`)
// }
function checkInput () {
  member.player_id = playerId.value
  member.lastname = lastname.value
  member.firstname = firstname.value
  member.gender = gender.value
  member.email = email.value
  member.phone = phone.value
  member.chname = chname.value
  member.nkname = nkname.value
  member.fees = fees.value
  member.type = mtype.value
  member.year = year
  const m = member
  let isMemberInfoOK = true
  let tit = 'OK'
  let msg = 'OK'
  if (m.chname !== null && m.chname != undefined && m.chname.length > 8) {
    tit = 'chinese name cannot be more than 4 chinese words!'
    msg = 'please revise your input.'
    isMemberInfoOK = false
  } else if (m.lastname.length < 1 || m.lastname.length > 16) {
    tit = 'lastname is required and cannot be more than 16 letter and at least 1 letters'
    msg = 'please revise your input.'
    isMemberInfoOK = false
  } else if (m.firstname.length < 1 || m.firstname.length > 16) {
    tit = 'firstname are required and cannot be more than 16 letter and at least 1 letters'
    msg = 'please revise your input.'
    isMemberInfoOK = false
  } else if (m.gender === undefined || m.gender === '' || m.gender === null) {
    tit = 'member gener is required'
    msg = 'please the member\'s gender.'
    isMemberInfoOK = false
  }
  if (!isMemberInfoOK) {
    $q.dialog({ title: tit, message: msg })
  } else {
    if (isClubMember) saveMember()
    else {
      action == 'Update' ? updMember() : action == 'Create' ? addMember() : delMember()
    }
  }
}
</script>

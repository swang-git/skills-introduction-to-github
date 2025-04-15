<template>
<q-dialog v-model="opened" seamless :maximized="isIM">
  <div class="bg-teal-9 q-pa-sm" style="width:400px">
    <q-toolbar glossy>
      <!-- <q-btn glossy round v-close-popup icon="keyboard_arrow_left" color="amber-9" /> -->
      <q-toolbar-title v-if="isClubMember" class="text-white text-h6"> {{ year }} Membership ({{ action }}) </q-toolbar-title>
      <q-toolbar-title v-else class="text-white text-h6 text-center">Add New Player for Team Match</q-toolbar-title>
    </q-toolbar>
    <q-card class="bg-teal-10">
      <q-card-section>
        <div class="row">
          <q-input outlined rounded class="col-6 q-px-xs" v-model='firstname' label='First  Name' dark />
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
import { ref } from 'vue'
import { useQuasar } from 'quasar'
import emitter from 'tiny-emitter/instance'
import { axiosFunctions } from 'src/composables/axiosFunctions'
import { libFunctions } from 'src/composables/libFunctions'
const { paxios } = axiosFunctions()
const { isIM } = libFunctions()
const emit = defineEmits(['added-new-player'])
const $q = useQuasar()
const year = ref((new Date()).getFullYear())
const fees = ref(0.0)
const isClubMember = ref(null)
const mtype = ref(isClubMember.value ? 'G' : undefined)
const action = ref(undefined)
const lastname = ref(undefined)
const firstname = ref(undefined)
const chname = ref(undefined)
const nkname = ref(undefined)
const email = ref(undefined)
const phone = ref(undefined)
const gender = ref(undefined)
const member = ref({})
const opened = ref(false)
const gameId = ref(null)

function setFees () {
  console.info(`mtype=${mtype.value}`)
  switch (mtype.value) {
    case 'H':
      fees.value = 1000
      break
    case 'R':
      fees.value = 100
      break
    case 'C':
      fees.value = 80
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
  console.info('mtype', mtype.value, fees.value)
}
function openIt (act, m, isClubMem=true, gId) {
  console.log(`-fn-openIt act=${act} isClubMember=${isClubMem} gameId=${gId}`, m)
  action.value = act
  isClubMember.value = isClubMem
  gameId.value = gId
  member.value = m
  lastname.value = m.lastname
  firstname.value = m.firstname
  email.value = m.email
  phone.value = m.phone
  chname.value = m.chname
  nkname.value = m.nkname
  gender.value = m.gender
  fees.value = m.fees
  mtype.value = m.type
  opened.value = true // must be the last
}
console.log('-ST-NewPlayerDialog')
emitter.on('open-NewPlayerDialog', (c,x,y,z) => openIt(c,x,y,z))
emitter.on('golf-addMember', (da) => emit('added-new-player', da.newPlayers))
emitter.on('golf-updMember', (da) => emit('added-new-player', da.newPlayers))
function saveMember () {
  console.log('-fn-save Create/Updated member ', member.value)
  var path = process.env.API + '/golf/updMember'
  if (action.value === 'Create') {
    path = process.env.API + '/golf/addMember'
  }
  member.value.year = year.value
  member.value.act = action.value
  if (gameId.value > 0) member.value.team = gameId.value
  paxios(path, member.value)
  opened.value = false
}
function checkInput () {
  member.value.lastname = lastname.value
  member.value.firstname = firstname.value
  member.value.gender = gender.value
  member.value.email = email.value
  member.value.phone = phone.value
  member.value.chname = chname.value
  member.value.nkname = nkname.value
  member.value.fees = fees.value
  member.value.type = mtype.value
  const m = member.value
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
    saveMember()
  }
}
</script>

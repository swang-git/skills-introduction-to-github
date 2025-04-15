<template>
<div style="width:400px">
  <q-dialog v-model="opened">
    <div class="bg-teal-10 q-pa-md">
      <q-toolbar glossy>
        <q-btn glossy round v-close-popup icon="keyboard_arrow_left" color="yellow-10" />
        <q-toolbar-title> {{ year }} Membership ({{ action }}) </q-toolbar-title>
      </q-toolbar>
      <q-input v-model.trim='firstname' label='First  Name' color="teal-10" inverted dark align="left" />
      <q-input v-model.trim='lastname' label='Last Name' color="teal-10" inverted dark />
      <q-radio keep-color dark v-model="gender" val="M" label="Male" color="white" class="text-white text-body1" />
      <q-radio keep-color dark v-model="gender" val="F" label="Female" color="yellow-8" class="text-white text-body1" />
      <q-radio keep-color dark v-model="mtype"  val="H" @click="setFees()" label="Honor"      color="yellow-9" class="text-white text-body1" />
      <q-radio keep-color dark v-model="mtype"  val="R" @click="setFees()" label="Member"     color="yellow-8" class="text-white text-body1" />
      <q-radio keep-color dark v-model="mtype"  val="C" @click="setFees()" label="Couple"     color="yellow-6" class="text-white text-body1" />
      <q-radio keep-color dark v-model="mtype"  val="D" @click="setFees()" label="Developer"  color="yellow-4" class="text-white text-body1" />
      <q-radio keep-color dark v-model="mtype"  val="N" @click="setFees()" label="Non-Member" color="yellow-2" class="text-white text-body1" />
      <q-input v-model='fees' label='Membership Fees' type="number" deciamls=2 numeric-keyboard-toggle prefix="$" color="teal-10" dark />
      <q-input v-model='email' dark label='Email' type="email" color="teal-10" />
      <q-input v-model='phone' dark label='Phone' type="tel" color="teal-10" />
      <q-input v-model='chname' dark label='中文姓名' type="text" color="teal-10" />
      <q-input v-model='nkname' dark label='别名(微信名)' type="text" color="teal-10" />
      <q-btn label="Cancel" @click="opened=false" icon="cancel" glossy color="yellow-10" />
      <q-btn :label="action" @click="checkInput()"  icon="create" glossy color="teal-10" style="float:right" />
    </div>
  </q-dialog>
</div>
</template>
<script setup>
import { ref } from 'vue'
import { libFunctions  } from 'src/composables/libFunctions'
import { axiosFunctions  } from 'src/composables/axiosFunctions'
const { $q } = libFunctions()
const { paxios } = axiosFunctions()
const year = (new Date()).getFullYear()
const fees = ref(0.0)
const mtype = null
const action = null
const lastname = ''
const firstname = ''
const chname = ''
const nkname = ''
const email = ''
const phone = ''
const gender = ''
const member = {}
const opened = ref(false)

function setFees () {
  console.info('mtype', mtype)
  switch (mtype) {
    case 'H':
      fees.value = 1000
      break
    case 'M':
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
      fees.value = null
  }
}
// function openIt (act, m) {
//   opened.value = true
//   action = act
//   console.log('upd member', m)
//   if (act === 'update' || act === 'delete') {
//     member = m
//     lastname = m.lastname
//     firstname = m.firstname
//     email = m.email
//     phone = m.phone
//     chname = m.chname
//     gender = m.gender
//     fees = m.fees
//     mtype = m.type
//   }
// }
function saveMember () {
  console.log('-Ck-fn-save new / updated member ', lastname, member)
  var path = process.env.API + '/golf/updMember'
  if (action === 'create') {
    path = process.env.API + '/golf/addMember'
  }
  member.year = year
  member.act = action
  paxios(path, member)
  opened.value = false
}
function checkInput () {
  member.lastname = lastname
  member.firstname = firstname
  member.gender = gender
  member.email = email
  member.phone = phone
  member.chname = chname
  member.fees = fees
  member.type = mtype
  const m = member
  let isMemberInfoOK = true
  let tit = 'OK'
  let msg = 'OK'
  console.log('chname.length', m)
  if (m.chname !== undefined && m.chname.length > 8) {
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
  } else if (m.gender === undefined || m.gender === '') {
    tit = 'member gener is required'
    msg = 'please revise your input.'
    isMemberInfoOK = false
  }
  if (!isMemberInfoOK) {
    $q.dialog({ title: tit, message: msg })
  } else {
    saveMember()
  }
}
</script>

<template>
<q-dialog v-model="opened" seamless :maximized="isIM">
  <div class="bg-teal-9 q-pa-sm" style="width:400px">
    <q-toolbar glossy>
      <!-- <q-btn glossy round v-close-popup icon="keyboard_arrow_left" color="amber-9" /> -->
      <q-toolbar-title v-if="isClubMember" class="text-white text-h6"> {{ year }} Membership ({{ action }}) </q-toolbar-title>
      <q-toolbar-title v-else class="text-white text-h6 text-center">Create Alias for the Player Below</q-toolbar-title>
    </q-toolbar>
    <q-card class="bg-teal-10">
      <q-card-section>
        <div class="row">
          <q-input outlined rounded class="col-6 q-px-xs text-h6" v-model='firstname' label='First  Name' dark disable />
          <q-input outlined rounded class="col-6 text-h6" v-model='lastname' label='Last Name' dark disable />
        </div>
      </q-card-section>
      <q-card-section style="margin-top:-30px">
        <q-radio keep-color dark v-model="gender" val="F" label="Female" color="red-6" class="text-white text-h6" />
        <q-radio keep-color dark v-model="gender" val="M" label="Male"   color="green" class="text-white text-h6" />
      </q-card-section>
      <q-card-section style="margin-top:-40px">
        <div class="row q-pb-xs">
          <transition appear enter-active-class="animated flip" style="animation-duration:3s;animation-delay:0.3s">
          <!-- <transition appear enter-active-class="animated bounceIn" style="animation-duration:3s;animation-delay:0.3s"> -->
          <!-- <transition appear enter-active-class="animated bounceOut" style="animation-duration:2s;animation-delay:0.3s"> -->
            <q-input outlined rounded class="col-6 q-pt-xs bg-green-10 text-h6" v-model='nkname' dark label='别名(alias)' type="text" @update:model-value="checkDupAlias" />
          </transition>
          <q-input outlined rounded class="col-6 q-pt-xs text-h6" v-model='chname' dark label='中文姓名' type="text" color="teal-10" />
        </div>
        <div class="row">
          <q-input v-if="isClubMember" outlined rounded class="col-6" v-model='fees' label='Membership Fees' type="number" deciamls=2 numeric-keyboard-toggle prefix="$" dark />
          <q-input outlined rounded :class="{ 'col-6':isClubMember, 'col-12':!isClubMember }" v-model='phone' dark label='Phone' type="tel" color="teal-10" />
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
import { ref, reactive } from 'vue'
import emitter from 'tiny-emitter/instance'
import { libFunctions } from '../composables/libFunctions'
const { isIM, $q } = libFunctions()
import { axiosFunctions } from '../composables/axiosFunctions'
const { paxios } = axiosFunctions()

const year = (new Date()).getFullYear()
const fees = 0.0
var isClubMember = false
const mtype = isClubMember ? 'G' : undefined
var action = undefined
// var matchPlayerId = -1
var lastname = undefined
var firstname = undefined
const chname = undefined
const nkname = ref(null)
const email = undefined
const phone = undefined
const gender = 'M'
var member = reactive({})
const opened = ref(false)
var gameId = null
var aliases = null

emitter.on('open-KJNewPlayerDialog', (act, m, gId, paliases) => openIt (act, m, gId, paliases))

console.log(`%c-ST-KJNewPlayerDialog`, 'color:blue;font-size:11px')
// const emit = defineEmits([ 'get-aliases', ])
// defineExpose ({openIt})
function openIt (act, m, gId, paliases, isMember=false) {
  console.log(`%c-CK-fn-openIt gameId=${gId}`, 'color:red', paliases, m)
  action = act
  isClubMember = isMember
  gameId = gId
  member = m
  lastname = m.lastname
  firstname = m.firstname
  // aliases = paliases.map(p => p.alias.toUpperCase())
  aliases = paliases
  opened.value = true // must be the last
}
function saveMember () {
  console.log('-fn-save Create player ', member)
  const path = process.env.API + '/golf/addNewSimPlayer'
  member.year = year
  member.act = action
  if (gameId > 0) member.team = gameId
  paxios(path, member)
  opened.value = false
  // emit('get-aliases')
}
function checkDupAlias () {
  let aliasesm = aliases.map(p => p.alias)
  console.log(`-fn-checkDupAlias alias=${nkname.value.toUpperCase()}`, aliasesm.includes(nkname.value.toUpperCase()), aliases)
  if (aliasesm.includes(nkname.value.toUpperCase())) {
    const tit = 'Alias "' + nkname.value + '" Exists (case insensitive)'
    const msg = 'Please Choose something else as alias'
    $q.dialog({ title: tit, message: msg })
    nkname.value = null
  }      
}
function checkInput () {
  // console.log(nkname, aliases)
  member.lastname = lastname
  member.firstname = firstname
  member.gender = gender
  member.gameId = gameId
  member.email = email
  member.phone = phone
  member.chname = chname
  member.alias = nkname.value
  member.fees = fees
  member.type = mtype
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
    msg = 'please add member\'s gender.'
    isMemberInfoOK = false
  } else if (m.alias === undefined || m.alias === '' || m.alias === null) {
    tit = 'alias is required'
    msg = 'please add player\'s alias, something like KJ, JH, etc.'
    isMemberInfoOK = false
  } else if (aliases.includes(m.alias.toUpperCase())) {
    tit = 'Alias "' + m.alias + '" Exists'
    msg = 'Please Choose something else as alias'
    isMemberInfoOK = false
  }      
  if (!isMemberInfoOK) {
    $q.dialog({ title: tit, message: msg })
    // alias = null
  } else {
    saveMember()
  }
}
</script>

<template>
<div class="bg-teal-10 q-pa-xs">
  <CardSelection v-if="tmntId==0" :selections="activeGames" todo="Grouping" @user-selected="userSelected" />
  <PGCGroupListTempl v-if="tmntId>0" :tmnt="tmnt" />
</div>
</template>
<script setup>
import { ref, reactive } from 'vue'
import emitter from 'tiny-emitter/instance'
import { libFunctions } from '../composables/libFunctions'
const { store } = libFunctions() 
import { axiosFunctions } from '../composables/axiosFunctions'
const { gaxios } = axiosFunctions() 
import CardSelection from 'src/components/CardSelection'
import PGCGroupListTempl from './PGCGroupListTempl'
  
var tmnt = reactive({})
const activeGames = ref([])
const tmntId = ref(0)

console.log('-ST-PGCGroupList')
// console.log('-ST-PGCGroupList', activeGames.value) 

store.pageTitle = 'Game Groups'
store.page = 'grouping'
getUnexpiredTournaments('ALL')
// emitter.on('golf-UnexpiredTournaments', (x) => console.log('unexpired games', x))
emitter.on('golf-UnexpiredTournaments', (x) => { activeGames.value = x.games; /* console.log('active games', activeGames.value) */ })

function getUnexpiredTournaments (gameName = 'ALL') {
  // console.log(`-CK-fn-getUnexpiredTournaments gameName=${gameName}`)
  const path = process.env.API + '/golf/UnexpiredTournaments/' + gameName
  gaxios(path)
}
function userSelected (tmt) {
  tmntId.value = tmt.id
  tmnt = tmt
  console.log(`-CK-fn-userSelected tmntId=${tmntId.value}`, tmnt)
  store.pageTitle = 'Group List for ' + tmt.game
}
</script>

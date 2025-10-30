<template>
<q-dialog v-model="opened" position="right">
<q-layout view="lhh LpR lff" container class="shadow-2 rounded-borders bg-teal-10" style="width:305px;height:600px">
  <layoutHeader tit="To Be Grouped" ricon="none" />
  <div class="q-pt-xl">
    <div v-for="(p) in compNotGrouped.sort((a, b) => { return a.name < b.name ? -1 : 1})" :key=p class="q-pl-md">
      <div class="row">
        <q-chip class="bg-teal-9 text-white" size="18px" style="width:243px">
          <q-btn round outline icon="cancel" color="amber" style="margin:0 20px 0 -25px" @click="delPGCTPlayer(p)"/>
          <q-avatar><img :src="getAvatar(p)" /></q-avatar>
          <b class="cursor-pointer" @click="getPlayersGameScores(p)">{{ p.name }}</b>
        </q-chip>
        <q-btn round glossy icon="add_circle" color="amber-9" style="margin:0 0px 0 -17px" @click="addToGroup(p)"/>
      </div>
  </div>
  </div>
  <div class="q-ma-xs q-pt-none" style="z-index:2">
    <q-input class="bg-teal-10 text-h6" standout bottom-slots v-model="searchQuery" label="Search Players by Name" dense dark>
      <template v-slot:prepend>
        <q-icon name="search" color="white" />
      </template>
      <template v-slot:append>
        <q-icon name="close" @click="searchQuery=''" class="cursor-pointer" />
      </template>
    </q-input>
  </div>
  <div style="margin:-20px 0 0 0">
    <div v-for="(p) in compPlayerList" :key=p class="q-pl-md">
      <div class="row">
        <q-chip class="bg-teal-9 text-white" size="18px" style="width:233px">
          <q-btn v-if="p.type==null" round glossy icon="X" color="green-8" style="margin: 0 0 0 -20px" class="q-mb-xs q-pb-sm" @click="addMembership(p)">
            <q-tooltip class="bg-green-8 text-h6">add to membership</q-tooltip>
          </q-btn>
          <q-btn v-else round glossy disable :icon="p.type" color="grey" style="margin: 0 0 0 -20px" class="q-mb-xs q-pb-sm" @click="delPGCTPlayer(p)"/>
          <q-avatar class="q-pl-md"><img :src="getAvatar(p)" /></q-avatar>
          <b class="cursor-pointer q-pl-md" @click="getPlayersGameScores(p)">{{ p.name }}</b>
        </q-chip>
        <q-btn v-if="p.type==null" round glossy disable icon="add_circle" color="grey" style="margin:0 0 0 -10px"/>
        <q-btn v-else round glossy icon="add_circle" color="green-8" @click="addToGroup(p)" style="margin:0 0 0 -10px"/>
      </div>
    </div>
  </div>
  <MemberDialog ref="MemberDialog" @add-membership="setMemberType" />
</q-layout>
</q-dialog>
</template>
<script setup>
import { ref, computed } from 'vue'
import emitter from 'tiny-emitter/instance'
import layoutHeader from 'src/components/LayoutHeader'
import MemberDialog from '../components/MemberDialog'
const opened = ref(false)
const notGrouped = []
// const playerList = []
const searchQuery = ''
// const player = null
// const tmntId = null
// const gameId = null
// const year = null
// const gameFee = null

console.log('-ST-PGCPlayerList')
emitter.on('add-to-not-grouped', (p) => this.addToNotGrouped(p))
emitter.on('golf-getPGCNotGroupedPlayers', (x) => this.setPGCNotGroupedPlayers(x))
emitter.on('new-pgc-member', (x) => this.addToList(x))


// function addToList (da) {
//   const newMember = { tournament_id:this.tmntId, game_id:this.gameId, gamefee:this.gameFee, year:this.year }
//   newMember.gender = da.member.gender
//   newMember.name = da.member.name
//   newMember.player_id = da.member.player_id
//   newMember.type = da.member.type
//   console.log('-fn-addToList-', da, newMember)
//   this.playerList.unshift(newMember)
// }
function setMemberType (type) {
  this.player.type = type
}
function addMembership (p) {
  // if (this.isNotPGCsAdmin()) return
  this.player = p
  const x = p.name.split(', ')
  const newMember = { firstname:x[1], lastname:x[0], gender:p.gender, fees:this.gameFee, id:p.player_id }
  console.log(`-fn-addMembership ${p.name}`)
  this.$refs.MemberDialog.openIt('Create', newMember, true, p.game_id)
}
// function openIt (tmntId, gameId, year, fees) {
//   emitter.emit('open-list')
//   console.log(`-fn-openIt-${tmntId} ${gameId} ${year} ${fees}`)
//   this.tmntId = tmntId
//   this.gameId = gameId
//   this.year = year
//   this.gameFee = fees
//   if (this.playerList.length === 0) this.getPGCNotGroupedPlayers()
//   this.opened = true
// }
// function getPGCNotGroupedPlayers () {
//   console.log(`-fn-getPGCNotGroupedPlayers`)
//   const params = this.tmntId + '/' + this.gameId + '/' + this.year
//   const path = process.env.API + '/golf/getPGCNotGroupedPlayers/' + params
//   this.gaxios(path)
// }
// function setPGCNotGroupedPlayers (da) {
//   // emitter.emit('open-list')
//   console.log(`-fn-setPGCNotGroupedPlayers`, da)
//   this.playerList = []
//   this.playerList = da.players
//   const newPlayerTempl = { player_id:-1, name:'Add Member'}
//   this.playerList.unshift(newPlayerTempl)
// }
// function addToNotGrouped (p) {
//   this.notGrouped.push(p)
//   console.log('-fn-addToNotGrouped', p, this.notGrouped)
// }
function addToGroup (p) {
  // if (this.isNotPGCsAdmin()) return
  this.notGrouped = this.notGrouped.filter(x => x.player_id !== p.player_id)
  this.playerList = this.playerList.filter(x => x.player_id !== p.player_id)
  console.log('-fn-addToGroup', p, this.notGrouped)
  this.$emit('add-to-group', p)
  // emitter.emit('add-to-list', p)
  // emitter.emit('open-list')
}
function delPGCTPlayer (p) {
  // if (this.isNotPGCsAdmin()) return
  console.log(`-fn-delPGCTplayer ${p.id} ${this.playerList.length}`, p) // delete it from tplayers table
  const path = process.env.API + '/golf/delPGCTplayer/' + p.id
  this.notGrouped = this.notGrouped.filter(x => x.player_id !== p.player_id)
  this.playerList.push(p)
  console.log(`-fn-delPGCTplayer ${p.id} ${this.playerList.length}`, p) // delete it from tplayers table
  this.gaxios(path)
}

const compNotGrouped = computed(() => { return notGrouped })
const compPlayerList = computed(() => {
    var filterKey = searchQuery.length > 0 && searchQuery.toLowerCase()
  // console.log(`-fn-search-${this.searchQuery} ${this.gameId} ${filterKey}`, Object.keys(this.playerList[0]))
  var data = this.playerList
  if (filterKey.length > 0) {
    var words = filterKey.split(' ')
    words.forEach(word => {
      data = data.filter(row => {
        return Object.keys(row).filter(key => { return !['game_id', 'tournament_id'].includes(key) }).some(key => {
          return String(row[key]).toLowerCase().indexOf(word) >= 0
        })
      })
    })
  }
  // console.log('searched results', data)
  return data.sort((a, b) => { return a.name < b.name ? -1 : 1 })
})
</script>

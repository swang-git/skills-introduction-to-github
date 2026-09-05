<template>
<div style="position:relative;margin:auto" class="bg-cyan-10">
  <div class="text-h5 text-cyan-1 q-pl-lg q-pt-sm">Princeton Golf Club {{ year }} Tournaments</div>
  <div v-for="(tmnt, idx) in selections" :key=tmnt>
    <div class="q-pa-sm q-pl-sm row items-start q-gutter-md">
      <!-- <q-card class="my-card text-white" :class="{ 'bg-cyan-9':todo=='Signup', 'bg-teal-9' :todo=='Grouping'}" style="width:450px"> -->
      <q-card class="text-white bg-teal-10" style="width:450px">
        <q-card-section>
          <div class="text-h5 q-pl-md">Club {{ tmnt.game }} Group {{ idx+1 }}</div>
        </q-card-section>
        <q-separator dark />
        <q-card-section class="bg-teal-10">
          <ul style="font-size:20px;line-height:1.4">
            <li>Game Time: <span>{{ tmnt.start_at }}</span> </li>
            <li>Men's tee: {{ tmnt.mtee }} </li>
            <li>Ladies tee: {{ tmnt.ltee }} </li>
            <li>Fees: ${{ tmnt.fees }} </li>
            <li v-if="tmnt.teetime_gap>0">Groups Tee Off: Every {{ tmnt.teetime_gap }} Minutes</li>
            <li v-else>Shutgun start at: {{ tmnt.start_at.substring(11, 16) }} </li>
            <li v-if="tmnt.note!=null">Notes: <div v-html="tmnt.note" /></li>
            <li>Course: <span>{{ tmnt.courseName }}</span> </li>
            <!-- <li>id: <span>{{ tmnt.id }}</span> </li> -->
            <div v-for="ar in getPlayer(tmnt.id)" :key="ar">
              <li>
                <span v-if="ar.captain==1" class="text-red-3 text-bold text-h6 cursor-pointer" @click="showModify(ar, tmnt.id, idx+1)">
                  {{ ar.name }} ({{ ar.activity=='both' ? 'Golf & Dinner': ar.activity=='dinn' ? 'Dinner Only' : ar.activity[0].toUpperCase() + ar.activity.slice(1) + ' Only' }}) (C)
                </span>
                <span v-else class="text-lime text-h6 cursor-pointer" @click="showModify(ar, tmnt.id, idx+1)">
                  {{ ar.name }} ({{ ar.activity=='both' ? 'Golf & Dinner': ar.activity=='dinn' ? 'Dinner Only' : ar.activity[0].toUpperCase() + ar.activity.slice(1) + ' Only' }})
                </span>
              </li>
            </div>
          </ul>
        </q-card-section>
        <q-separator dark />
        <q-card-actions align="evenly">
          <q-btn v-if="getPlayerCount(tmnt.id)==4" flat class="text-h6" no-caps :label="'Group Filled Up (' + tmnt.id + ')'" disable />
          <q-btn v-else flat class="text-h6" :label="'Sign up (' + tmnt.id + ')'" @click="openSelectionDialog(tmnt, idx+1)" />
        </q-card-actions>
      </q-card>
    </div>
  </div>
</div>
</template>
<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'
const emit = defineEmits(['user-selected', 'open-signup-dialog'])
const props = defineProps({
  selections: { type: Array },
  todo: { type: String },
  tplayers: { type: Array },
})
var year = null
const selectedId = ref(-1)

console.log('-ST-CardSelection')
year = new Date().getFullYear()

function showModify (player, tmntId, grp) {
  console.log(`-fn-showModify tmntId=${tmntId} grp=${grp}`, player)
  emitter.emit('open-ModifyDialog', player, tmntId, grp)
}
function getPlayerCount (tmntId) {
  console.log(`-fn-getPlayerCount tmntId=${tmntId}`, props.tplayers)
  const players = props.tplayers.filter(x => x.tournament_id == tmntId && x.activity != 'dinn')
  return players.length
}
function getPlayer (tmntId) {
  console.log(`-fn-getPlayer tmntId=${tmntId}`, props.tplayers)
  // const players = props.tplayers.filter(x => x.tournament_id == tmntId && x.activity != 'dinn')
  const players = props.tplayers.filter(x => x.tournament_id == tmntId).sort((a, b) => { return b.captain - a.captain })
  if (players.length>0) {
    console.log(`-ck-player name=${players[0].name} activity=${players[0].activity}`, players)
    return players
  }
}

function openSelectionDialog (tmnt, grp) {
  console.log('-fn-openSelectionDialog grp=${grp}', tmnt)
  emit('open-signup-dialog', tmnt, grp)
}
</script>
<!-- <style lang="sass" scoped>
.my-card
  width: 100%
  max-width: 370px
</style> -->

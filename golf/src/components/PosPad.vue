<template>
<q-dialog v-model="opened" transition-show="rotate" transition-hide="rotate">
  <q-card style="min-height:136px">
    <q-card-section class="bg-teal-10 q-pa-sm text-h6 text-cyan-3 text-center">
      <span class="q-px-sm">Reward for "{{ player.name }}"</span>
    </q-card-section>
    <q-card-actions class="bg-teal">
      <q-input rounded outline v-model="player.note" input-style="width:280px" input-class="bg-teal-10 text-h6 text-white q-px-xs" placeholder="Reward Notes"/>
      <!-- <q-btn glossy round icon="chevron_left" color="amber" v-close-popup /> 
        <q-btn glossy round icon="save" color="primary" v-close-popup />  -->
      </q-card-actions>
      <q-card-section class="bg-teal-9 q-pa-sm">
        <table v-if="gameId<6" style="y-overflow:auto;margin:auto" class="bg-teal-7">
          <q-tr><td v-for="i in ['G1', 'G2', 'G3', 'G4', 'G5']"  :key=i.x><q-btn size="17px" color="teal-10" round :label="i" @click="saveReward(i)" /></td></q-tr>
          <q-tr><td v-for="i in ['N1', 'N2', 'N3', 'LD', 'CP']" :key=i.x><q-btn size="17px" color="teal-10" round :label="i" @click="saveReward(i)" /></td></q-tr>
        </table>
        <table v-else-if="gameId===6" style="y-overflow:auto;margin:auto" class="bg-teal-7">
          <q-tr><td v-for="i in ['P1', 'P2', 'P3', 'P4', 'P5']"  :key=i.x><q-btn size="17px" color="teal-10" round :label="i" @click="saveReward(i)" /></td></q-tr>
          <q-tr><td v-for="i in ['NP1', 'NP2', 'NP3', 'HIO']" :key=i.x><q-btn size="17px" color="teal-10" round :label="i" @click="saveReward(i)" /></td></q-tr>
        </table>
      </q-card-section>
      <q-card-actions class="bg-teal-10 justify-between">
        <q-btn round icon="chevron_left" color="amber-9" v-close-popup /><q-btn round icon="delete" color="red" @click="saveReward(null)" />
      </q-card-actions>
  </q-card>
</q-dialog>
</template>
<script setup>
/* eslint-disable */
import { ref } from 'vue'
// import emitter from 'tiny-emitter/instance'
const opened = ref(false)
const player = {}

console.log('-ST-PosPad')

function saveReward (w) {
  console.log(`-CK-fn-saveReward ${w}, ${this.player.name} ${this.player.note}`)
  player.pos = w
  const path = process.env.API + '/golf/addPGCTplayer'  // update player's pos
  paxios(path, player)
  opened.value = false
}
function openIt (p, gId) {
  console.log(`-CK-fn-openIt-${p.name} ${player.id} gameId=${gId}`)
  this.gameId = gId
  this.player = p
  opened.value = true
}
</script>

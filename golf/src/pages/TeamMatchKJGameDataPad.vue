<template>
<q-dialog v-model="opened">
  <q-card>
    <q-card-section class="bg-teal-10 elevated inset-shadow-down">
      <div class="text-center text-h6 text-cyan-3">{{ player.firstname }}'s Game Data upon {{ player.execl_date }}</div>
    </q-card-section>
    <q-card-section class="bg-teal-9 text-h6 text-cyan-2">
      <tr v-if="player.handicap>0">
        <td class="col-6 text-right">Handicap (last 10 games) : </td>
        <td class="col-6 text-left q-pl-xs">{{ player.handicap }}</td>
        <!-- <td><q-btn glossy round color="amber-10" icon="close" v-close-popup /></td> -->
      </tr>
      <tr><td class="col-6 text-right">Games Played (current year) : </td><td class="col-6 text-left q-pl-xs">{{ player.games_played }}</td></tr>
      <tr><td class="col-6 text-right">All Games Played : </td><td class="col-6 text-left q-pl-xs">{{ player.all_games_played }}</td></tr>
      <tr v-if="player.gross_score>0"><td class="col-6 text-right">Gross Score ({{ player.execl_date }} game) : </td><td class="col-6 text-left q-pl-xs">{{ player.gross_score }}</td></tr>
      <tr><td class="col-6 text-right">Avg Score (all games) : </td><td class="col-6 text-left q-pl-xs">{{ player.avg_score }}</td></tr>
      <tr><td class="col-6 text-right">Avg Index (all games) : </td><td class="col-6 text-left q-pl-xs">{{ player.avg_idx_all_games }}</td></tr>
      <tr v-if="player.idx_diff>0"><td class="col-6 text-right">Index Differential ({{ player.execl_date }} game) : </td><td class="col-6 text-left q-pl-xs">{{ player.idx_diff }}</td></tr>
      <tr v-if="player.pair!=null"><td class="col-6 text-right">Pair ({{ player.execl_date }} game) : </td><td class="col-6 text-left q-pl-xs">{{ player.pair }}</td></tr>
      <!-- <tr v-if="player.net_win_loss!=null"><td class="col-6 text-right">Net Win/Loss : </td><td class="col-6 text-left q-pl-xs">{{ player.net_win_loss }}</td></tr> -->
      <!-- <tr v-if="player.team!=null"><td class="col-6 text-right">Team : </td><td class="col-6 text-left q-pl-xs">{{ player.team }}</td></tr> -->
      <!-- <tr v-if="player.pair_win_loss!=null"><td class="col-6 text-right">Pair Win Percentage : </td><td class="col-6 text-left q-pl-xs">{{ player.pair_win_loss }}%</td></tr> -->
      <!-- <tr v-if="player.pair_win_pct!=null"><td class="col-6 text-right">Pair Win Percentage : </td><td class="col-6 text-left q-pl-xs">{{ player.pair_win_pct }}%</td></tr>
      <tr v-if="player.pair_win_pnt!=null"><td class="col-6 text-right">Pair Win Point : </td><td class="col-6 text-left q-pl-xs">{{ player.pair_win_pnt }}</td></tr>
      <tr v-if="player.team_win_pct!=null"><td class="col-6 text-right">Team Win Percentage : </td><td class="col-6 text-left q-pl-xs">{{ player.team_win_pct }}%</td></tr>
      <tr v-if="player.team_win_pnt!=null"><td class="col-6 text-right">Team Win Point : </td><td class="col-6 text-left q-pl-xs">{{ player.team_win_pnt }}</td></tr> -->
      <!-- <q-card-actions align="right"><q-btn glossy round color="amber-10" icon="close" v-close-popup /></q-card-actions> -->
    </q-card-section>
    <q-card-actions align="right" class="bg-teal-10">
      <q-btn outline color="cyan-1" label="close" v-close-popup />
    </q-card-actions>
  </q-card>
</q-dialog>
</template>
<script setup>
import { ref, reactive } from 'vue'
import emitter from 'tiny-emitter/instance'

//== data section
let player = reactive({})
const opened = ref(false)

console.log(`-ST-TeamMatchKJGameDataPad`)
emitter.on('open-TeamMatchKJGameDataPad', (player) => openIt(player))
//== function section
function openIt(p) {
  console.log(`-fn-openIt`, p)
  player = p
  player.firstname = p.name.split(', ')[1].trim()
  opened.value = true
}
</script>

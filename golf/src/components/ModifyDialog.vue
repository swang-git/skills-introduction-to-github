<template>
<div class="q-pa-md q-gutter-sm" >
  <q-dialog v-model="opened" persistent transition-show="rotate" transition-hide="flip-up">
    <q-layout container class="bg-teal-10" style="height:414px; width:330px;border:3px cyan solid">
      <LayoutHeader :tit="title" />
      <q-card class="bg-teal-10 text-cyan-1">
        <q-card-section style="max-height:13vh;margin:40px 30px 0 30px;border:1px cyan solid" class="bg-cyan-10">
          <div><q-radio v-model="activity" val="both" label="Golf & Dinner" keep-color color="red" class="text-h6" /></div>
          <div><q-radio v-model="activity" val="golf" label="Golf Only" keep-color color="cyan" class="text-h6" /></div>
          <div><q-radio v-model="activity" val="dinn" label="Dinner Only" keep-color color="lime" class="text-h6" /></div>
        </q-card-section>
        <q-card-section style="max-height:13vh;margin:5px 30px 0 30px;border:1px cyan solid" class="bg-teal-9">
          <div><q-radio v-model="act" val="cap" label="Captain" keep-color color="green" class="text-h6" /></div>
          <div><q-radio v-model="act" val="del" label="Delete" keep-color color="pink" class="text-h6" /></div>
          <div><q-radio v-model="act" val=null  label="None" keep-color color="yellow" class="text-h6" /></div>
        </q-card-section>
        <q-separator />
      </q-card>
      <LayoutFooter act="submit" @do-action="modifyPlayer"/>
    </q-layout>
  </q-dialog>
  <ConfirmDialog @user-confirmed="doConfirmedModify" />
</div>
</template>
<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'
import { libFunctions } from '../composables/libFunctions'
import { axiosFunctions } from '../composables/axiosFunctions'
const { opened } = libFunctions()
const { paxios } = axiosFunctions()
import LayoutHeader from './LayoutHeader.vue'
import LayoutFooter from './LayoutFooter.vue'
import ConfirmDialog from './ConfirmDialog.vue'
// const emit = defineEmits(['selected-option'])
const title = ref(null)
const act = ref(null)
const activity = ref('both')
const tmntId = ref(-1)
const grp = ref(-1)
const player = ref({})

const emit = defineEmits(['del-tplayer', 'upd-tplayer'])

console.log('-ST-ModifyDialog')
emitter.on('open-ModifyDialog', (x,y,z) => openIt(x,y,z))

function doConfirmedModify () {
  console.log(`-fn-doConfirmedModify act=${act.value} player=${player.value.name}`)
  const p = player.value
  const isCaptain = act.value == 'cap' ? 1 : null
  const modifiedTplayer = { tournament_id:tmntId.value, player_id:p.player_id, year:p.year, game_id:p.game_id, name:p.name, activity:activity.value, captain:isCaptain, grp:grp.value }
  console.log("modifiedTplayer", modifiedTplayer)
  if (act.value == 'del') emit('del-tplayer', p.id)
  else emit('upd-tplayer', modifiedTplayer)
}
function modifyPlayer () {
  console.log(`-fn-modifyPlayer playerId=${player.value.player_id}, act=${act.value} activity=${activity.value} tmntId=${tmntId.value}`)
  const fname = player.value.name
  // const modTplayer = {tournament_id:tmntId.value, grp:grp.value, player_id:player.value.player_id, activity:player.value.activity, name:fname, captain:player.value.captain }
  const tit = "Confirm Changes for " + fname
  const ac = activity.value
  const actv = ac == 'both' ? 'Golf & Dinner' : ac == 'dinn' ? 'Dinner Only' : ac[0].toUpperCase() + ac.slice(1) + ' Only'
  const av = act.value
  const capdel = av == 'cap' ? 'Set Captain' : av == 'del' ? 'Delete This Player' : 'Unset Captain'
  const msg = av == 'del' ? "<ul><li>" + capdel + "</li></ul>" : "<ul><li>" + actv + "</li><li>" + capdel + "</li></ul>"

  emitter.emit('open-ConfirmDialog', tit, msg)
  opened.value = false
}
function openIt (ply, tid, g) {
  console.log(`-CK-fn-openIt player=${ply.name}, tmntId=${tid} grp=${g} activitiy=${ply.activity}`)
  title.value = "Modify for " + ply.name
  player.value = ply
  tmntId.value = tid
  grp.value = g
  activity.value = ply.activity
  act.value = ply.captain == 1 ? 'cap' : null
  opened.value = true
}
</script>

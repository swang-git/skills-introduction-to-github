<template>
  <q-dialog v-model="opened" persistent :maximized="isIM" :full-height="isIM">
    <q-layout container :style="{ height:isDesk ? '590px' : '500px', width:'350px' }" class="bg-teal">
      <q-header class="q-pa-xs">
        <q-toolbar class="fixed bg-primary z-top">
          <q-btn round glossy icon='chevron_left' v-close-popup color="amber-10" style="margin-left:-9px" />
          <transition appear enter-active-class="animated flip" style="animation-duration:5s;animation-delay:0.0s" v-if="flipNum">
            <q-btn round outline :label="tplayers.length + selected.length" class="q-ml-xs">
              <q-tooltip class="text-h6 text-grey-7 bg-yellow-4">Number of Players for This Match</q-tooltip>
            </q-btn>
          </transition>
          <transition appear enter-active-class="animated flip" style="animation-duration:5s;animation-delay:0.0s" v-else>
            <q-btn round outline :label="tplayers.length + selected.length" class="q-ml-xs">
              <q-tooltip class="text-h6 text-grey-7 bg-yellow-4">Number of Players for This Match</q-tooltip>
            </q-btn>
          </transition>
          <div class="col-7 q-pl-xl">
            <q-input dark borderless v-model="searchTxt" input-class="text-right text-h6" dense :label="compAllNoneMatchPlayers.length + ' Players'">
              <template v-slot:append>
                <q-icon v-if="searchTxt === ''" name="search" size="md" />
                <q-icon v-else name="clear" size="md" class="cursor-pointer" @click="searchTxt=''" />
              </template>
            </q-input>
          </div>
          <transition appear enter-active-class="animated flip" style="animation-duration:5s;animation-delay:0.0s" v-if="flipNum">
            <!-- <q-btn round glossy color="teal-9" icon="add_circle" @click="addUnsignedTeamMatchPlayers" class="q-ml-md" v-if="selected.length>0" /> -->
            <q-btn round glossy color="teal-9" icon="add_circle" @click="$emit('move-to-grouping', selected[0])" class="q-ml-md" v-if="selected.length>0" />
            </transition>
            <transition appear enter-active-class="animated flip" style="animation-duration:5s;animation-delay:0.0s" v-else>
            <q-btn round glossy color="teal-9" icon="add_circle" @click="$emit('move-to-grouping', selected[0])" class="q-ml-md" v-if="selected.length>0" />
            <!-- <q-btn round glossy color="teal-9" icon="add_circle" @click="addUnsignedTeamMatchPlayers" class="q-ml-md" v-if="selected.length>0" /> -->
          </transition>
        </q-toolbar>
      </q-header>
      <div class="bg-teal-9" style="padding:24px 0 0 0">
        <div v-for="(p, i) in compAllNoneMatchPlayers" :key=p class="q-gutter-sm q-pa-sm" :class="{'q-pt-xl':i===0}" @click="selectedPlayer(p)">
          <transition appear enter-active-class="animated flip" style="animation-duration:5s;animation-delay:0.0s" v-if="compSelectedPlayerIds.includes(p.player_id)">
            <q-avatar style="margin:-4px 0 0 6px" :square="compSelectedPlayerIds.includes(p.player_id)" size="34px"><img :src="getAvatar(p)"></q-avatar>
          </transition>
          <transition appear enter-active-class="animated bounceIn" style="animation-duration:5s;animation-delay:0.0s" v-else>
            <q-avatar style="margin:-4px 0 0 6px" :square="compSelectedPlayerIds.includes(p.player_id)" size="34px"><img :src="getAvatar(p)"></q-avatar>
          </transition>
            <q-chip class="text-h6 cursor-pointer bg-teal-8 text-white" :class="{ 'text-teal-3':compSelectedPlayerIds.includes(p.player_id)}" style="margin:-5px 0 0 0;width:276px" square>
              <span class="q-px-xs text-body1" style="margin:0 0 0 -14px">{{ (i + 1).toFixed(0).padStart(2, '0') }}</span>
              <span style="margin:0 0 0 0px">{{ p.name }}</span>
            </q-chip>
          <q-btn round icon="add_circle" color="green-9" style="margin:-8px 0 0 -16px" />
        </div>
      </div>
    </q-layout>
    <!-- <MemberDialog ref="memberDialog" @added-new-player="addedNewPlayer" /> -->
    <!-- <component :is="tabs[currentTab]" :tit="tit" :msg="msg" @add-selected-players="addSelectedPlayers" /> -->
    <!-- <ConfirmDialog ref="confirmDialog" @add-new-player="addNewPlayer" /> -->
  </q-dialog>
</template>
<script setup>
import { ref, computed } from 'vue'
import emitter from 'tiny-emitter/instance'
// import InfoDialog from 'src/components/InfoDialog'
// import MemberDialog from '../src/components/MemberDialog'
import { libFunctions } from 'src/composables/libFunctions';
import { axiosFunctions } from 'src/composables/axiosFunctions';
import { cssFunctions } from 'src/composables/cssFunctions';
const props = defineProps({
  tplayers: { type: Array },
  paliases: { type: Array },
})
const emit = defineEmits([
  'move-to-grouping',
])
const { isIM, isDesk } = libFunctions()
const { gaxios } = axiosFunctions()
const { getAvatar } = cssFunctions()
const openSlots = ref(0)
const flipNum = ref(false)
const selected = ref([])
const allPlayers = ref([])
const searchTxt = ref('')
const opened = ref(false)
// const tit = ref('')
// const msg = ref('')
// const currentTab = ref('InfoDialog')
// const tabs = { InfoDialog }
// const tmntId = ref(0)
// const gameId = ref(0)
// const getActive = ref(0)
console.log('-ST-MatchGroupingPlayers')
// emitter.on('match-grouping-players', (x) => { console.log('XXXX', x);openSlots.value = x; opened.value = true })
emitter.on('match-grouping-players', (x) => openIt(x))
emitter.on('golf-getMatchGroupingPlayers', (x) => allPlayers.value = x.lst)
// emitter.on('move-to-grouping', (x) => { this.addToGrouping(x) })
function openIt (osls) {
  console.log(`-fn-openIt openSlots=${osls}`)
  openSlots.value = osls
  selected.value = []
  opened.value = true
  const path = process.env.API + '/golf/getMatchGroupingPlayers'
  gaxios(path)
}
function selectedPlayer(p) {
  flipNum.value = !flipNum.value
  emit('move-to-grouping', p)
  openSlots.value--
  console.log(`-fn-selectedPlayer openSlots=${openSlots.value} selected.len=${selected.value.length}`, selected.value.map(p => p.player_id))
  if (openSlots.value == 0) opened.value = false
  // if (compSelectedPlayerIds.value.includes(p.player_id)) {
  //   selected.value = selected.value.filter(x => x.player_id != p.player_id)
  // } else {
  //   selected.value.push(p)
  //   console.log(`-fn-selectedPlayer openSlots=${openSlots.value} selected.len=${selected.value.length}`, selected.value.map(p => p.player_id))
  //   if (openSlots.value === selected.value.length) {
  //     tit.value = 'All Slots Are Filled'
  //     msg.value = 'AllSlotsAreFilled'
  //     currentTab.value = 'InfoDialog'
  //     emitter.emit('open-info-dialog', true)
  //   }
  // }
}
// function XXselectedPlayer(p) { // batch adding -- will not be used
//   flipNum.value = !flipNum.value
//   if (compSelectedPlayerIds.value.includes(p.player_id)) {
//     selected.value = selected.value.filter(x => x.player_id != p.player_id)
//   } else {
//     selected.value.push(p)
//     console.log(`-fn-selectedPlayer openSlots=${openSlots.value} selected.len=${selected.value.length}`, selected.value.map(p => p.player_id))
//     if (openSlots.value === selected.value.length) {
//       tit.value = 'All Slots Are Filled'
//       msg.value = 'AllSlotsAreFilled'
//       // currentTab.value = 'InfoDialog'
//       emitter.emit('open-info-dialog', true)
//     }
//   }
// }
// function XXaddSelectedPlayers(i, p) {
//   console.log(`-fn-addSelectedPlayers openSlots=${openSlots.value}`, selected.value)
//   selected.value.forEach(p => { emit('move-to-grouping', p) })
//   opened.value = false
//   // currentTab.value = null
//   emitter.emit('open-info-dialog', false) // cleanup InfoDialog
// }
const compSelectedPlayerIds = computed(() => { return selected.value.map(p => p.player_id) })
const compAllNoneMatchPlayers = computed(() => { // all non tplayer, that is all none match plaerys, that is players to be selected to the match
  let filterKey = searchTxt.value.length>0 && searchTxt.value.toLowerCase()
  const noneMatchPlayers = allPlayers.value.filter(p => !props.tplayers.map(x => x.player_id).includes(p.player_id))
    .filter(p => !props.paliases.map(x => x.player_id).includes(p.player_id))
  let data = noneMatchPlayers
  if (filterKey.length>0) {
    let words = filterKey.split(' ')
    words.forEach(word => {
      data = data.filter(row => {
        return Object.keys(row).filter(key => { return !['player_id', 'gender'].includes(key) }).some(key => {
          return String(row[key]).toLowerCase().indexOf(word) >= 0
        })
      })
    })
  }
  return data
})
</script>

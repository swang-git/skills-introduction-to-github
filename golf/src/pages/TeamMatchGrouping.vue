<template>
<div v-if="showMatchGrouping" class="bg-teal-10">
  <q-card-actions align="evenly" class="row q-py-xs text-cyan-1 text-h5 inset-shadow-down">
    <div class="text-center">{{ matchDate }} ({{ matchDate.chwk3() }})</div>
    <q-btn glossy round icon="play_arrow" @click="showMatchGrouped()" />
    <q-btn v-if="!groupingDone" outline round @click="emit('switch-handicap')"><q-icon :name="compHandicapFlag==14 ? 'M' : 'Z'" class="q-mb-sm" /></q-btn>
  </q-card-actions>

  <div class="row">
    <div style="border:1px solid teal">
      <div v-for="(gs, gi) in grouped" :key="gs">
        <div :class="getTeeColor(gs.tee)" class="inset-shadow-down q-px-sm row"
          :style="{ 'font-size':'16.5px', 'width':screenwidth-10 + 'px' }" style="margin-left:3px">
          <div class="row" @click="emit('change-tmnt', gs.tmntId, 'upd')">
            <b class="q-pr-xs text-body1">{{ gs.ttm }}</b>
            <span class="cursor-pointer text-body1">Play</span>
            <span class="q-px-xs inset-shadow-down" :class="getTeeColor(gs.tee)">{{ gs.teename }}</span>
          </div>
          <b class="q-pl-xs text-body1 cursor-pointer ellipsis" @click="emit('course-info', gs.tmntId, gs.teename)">{{ gs.course }}</b>
        </div>

        <table v-if="gsx>=0" class="q-ml-xs">
          <tr v-for="(p, pi) in gs.players.filter(p => /[A|B]\d/.test(p.team)).sort((a,b) => { return a.team < b.team ? -1 : 1})" :key=p.id class="text-h6 text-white">
            <td colspan="3" :width="screenwidth/2 + 'px'" no-wrap :class="teamColor(p)" class="inset-shadow-down">
              <q-btn dense round glossy class="q-ml-xs" @click="moveOutGrouped(gs, p)">
                <q-icon :name="p.name[0]" class="text-h6" style="margin:-6px 0 0 0"/>
              </q-btn>
              <span>{{ p.name }}<Tooltip :txt="`tpId: ${p.id} tmntId=${p.tmntId}`" /></span>
            </td>
            <td v-if="pi===0" :class="shadow('navy-box-16')">{{ p.hcap>0 ? p.hcap.toFixed(1) : null }}</td>
            <td v-if="pi===1" rowspan="2">
              <div style="padding:6px 0 0 0" :class="condShadow(p.hcap, 'redx-box-16', 'navy-box-16', 'yell-box-16')"> {{ getABS(p.hcap) }}</div>
            </td>
            <td v-if="pi===3" :class="shadow('redx-box-16')">{{ p.hcap>0 ? p.hcap.toFixed(1) : null }}</td>
            <td v-if="(pi<2)" :class="shadow('navy-box-16')">{{ p.handicap }}</td>
            <td v-if="(pi>1)" :class="shadow('redx-box-16')">{{ p.handicap }}</td>
          </tr>
        </table>
        <!-- grouping scenario bias or group add or favorite team - team handicaps comparation -->
        <q-card-actions v-if="gi==grouped.length-1 && Ahcap>0 && Bhcap>0" align="between" style="width:303px">
          <td :class="shadow('navy-box-20')">{{ Ahcap.toFixed(1) }}
            <q-tooltip v-model="showtip.ah" class="text-h6 bg-blue-10 text-cyan-1">Team A Average Handicaps</q-tooltip>
          </td>
          <td><q-icon name="remove" color="cyan-1" size="30px" /></td>
          <td :class="shadow('redx-box-20')">{{ Bhcap.toFixed(1) }}
            <q-tooltip v-model="showtip.bh" class="text-h6 bg-red-10 text-cyan-1">Team B Average Handicaps</q-tooltip>
          </td>
          <td><q-icon name="drag_handle" color="cyan-1" size="30px" /></td>
          <td :class="condShadow(ABdif, 'redx-box-20', 'navy-box-20', 'yell-box-20')">{{ getABS(ABdif) }}
            <q-tooltip v-model="showtip.ab" class="text-h6 bg-amber-10 text-white">Team Differential</q-tooltip>
          </td>
        </q-card-actions>
        <div> <!--show tobGrouped -->
          <table>
            <tr v-for="p in gs.players.filter(p => p.team=='X').sort((a, b) => a.alias - b.alias)" :key=p>
              <td><q-btn round outline icon="cancel" color="yellow" @click="moveOutGrouping(gs, p)" /></td>
              <td>
                <q-chip :avatar="getAvatar(p)" class="bg-teal-9 text-white" size="18px" style="width:252px;margin:0 0 0 -4px">
                  <q-avatar> <img :src="getAvatar(p)" /></q-avatar>
                  <b>{{ p.handicap }} {{ p.name }}</b>
                </q-chip>
              </td>
              <td><q-btn round icon="add_circle" @click="moveToGrouped(p)" color="green-8" style="margin:0 0 0 -42px" /></td>
            </tr>
          </table>
        </div>
      </div>
    </div>
  </div>
  <!-- grouping scenarios -->
  <div v-if="openSlots==0" class="relative-position">
    <div class="absolute-right">
      <div v-if="(tpids.length==4)" class="absolute-bottom-right">
        <div v-if="gsx>0 || (Ahcap>0 && Bhcap>0)">
          <q-btn round glossy color="indigo-9" size="18px" @click="unGrouping()"><q-icon name="cancel" color="amber" /></q-btn>
          <q-btn round glossy color="accent" size="18px" @click="showUserGuide()"><q-icon name="help" color="white" /></q-btn>
        </div>
        <q-btn round :disable="gsx==1" :glossy="gsx!=1" color="purple-10" size="18px" @click="groupScenario(1)"><q-icon name="S" color="cyan-1" /><b class="sup-60-24">1</b></q-btn>
        <q-btn round :disable="gsx==5" :glossy="gsx!=5" color="purple-10" size="18px" @click="groupScenario(5)"><q-icon name="S" color="cyan-1" /><b class="sup-60-24">5</b></q-btn>
        <q-btn round :disable="gsx==6" :glossy="gsx!=6" color="purple-10" size="18px" @click="groupScenario(6)"><q-icon name="S" color="cyan-1" /><b class="sup-60-24">6</b></q-btn>
        <q-btn round :disable="gsx==9" :glossy="gsx!=9" color="purple-10" size="18px" @click="groupScenario(9)"><q-icon name="S" color="cyan-1" /><b class="sup-60-24">9</b></q-btn>
      </div>
      <div v-if="tpidsLen>=8 && tpidsLen%4==0" class="absolute-bottom-right">
        <q-btn v-if="gsx>0" round glossy color="indigo-9" size="18px" @click="unGrouping()"><q-icon name="cancel" color="amber" /></q-btn>
        <q-btn round glossy color="accent" size="18px" @click="showUserGuide()"><q-icon name="help" color="white" /></q-btn>
        <q-btn round :disable="gsx==8" :glossy="gsx!=8" color="purple-10" size="18px" @click="groupScenario(8)"><q-icon name="S" color="cyan-1" /><b class="sup-60-24">8</b></q-btn>
        <q-btn round :disable="gsx==1" :glossy="gsx!=1" color="purple-10" size="18px" @click="groupScenario(1)"><q-icon name="S" color="cyan-1" /><b class="sup-60-24">1</b></q-btn>
        <q-btn round :disable="gsx==2" :glossy="gsx!=2" color="purple-10" size="18px" @click="groupScenario(2)"><q-icon name="S" color="cyan-1" /><b class="sup-60-24">2</b></q-btn>
        <q-btn round :disable="gsx==3" :glossy="gsx!=3" color="purple-10" size="18px" @click="groupScenario(3)"><q-icon name="S" color="cyan-1" /><b class="sup-60-24">3</b></q-btn>
        <q-btn round :disable="gsx==4" :glossy="gsx!=4" color="purple-10" size="18px" @click="groupScenario(4)"><q-icon name="S" color="cyan-1" /><b class="sup-60-24">4</b></q-btn>
        <q-btn round :disable="gsx==9" :glossy="gsx!=9" color="purple-10" size="18px" @click="groupScenario(9)"><q-icon name="S" color="cyan-1" /><b class="sup-60-24">9</b></q-btn>
      </div>
    </div>
  </div>

  <q-card-actions v-if="gsx==currentGsx && gsx!=7 && groupingDone" align="left">
    <q-btn glossy rounded class="bg-teal-9" @click="cancelGrouping">
      <q-icon left name="cancel" size="sm" color="yellow" />
      <span class="text-cyan-2 text-body1" style="margin: 0 4px 0 -10px">Cancel</span>
    </q-btn>
    <q-btn glossy rounded class="bg-primary-10" @click="saveGrouping">
      <span class="text-cyan-2 text-body1" style="margin: 0 4px 0 4px">Save Grouping</span>
      <q-icon name="add_circle" size="sm" color="green" />
    </q-btn>
  </q-card-actions>

  <!--show aliases -->
  <q-card v-if="openSlots>0" class="bg-teal-10">
    <q-card-section v-if="gameId==14 && lastHandicapDate" class="q-pa-none">
      <div class="text-h6 text-center text-cyan-2">Handicaps Updated on {{ lastHandicapDate }}</div>
    </q-card-section>
    <q-card-actions class="row" align="between">
      <div :class="shadow('round-66')" style="padding:15px 0 0 9px;font-size:24px;color:red" @click="switchAliases">{{ aliName }}</div>
      <!-- <div v-for="p in paliases[aliName].filter(p => !tpids.includes(p.player_id))" :key="p"> -->
      <!-- <div :class="shadow('round-66')" style="padding:15px 0 0 9px;font-size:24px" @click="switchAliases">JZs</div> -->
      <div v-for="p in paliases.filter(p => !tpids.includes(p.player_id))" :key="p">
        <div v-if="(zhcharRegExp.test(p.alias) && p.alias.length==1)" :class="shadow('round-66')" style="font-size:36px" @click="moveToGrouped(p)">
          <div style="margin:-4px 0 0 0px">{{ p.alias }}</div>
          <q-tooltip class="text-white text-h6 bg-indigo-9">{{ p.handicap }} {{ p.name }}</q-tooltip>
        </div>
        <div v-else-if="zhcharRegExp.test(p.alias)" :class="shadow('round-66')" style="font-size:23px" @click="moveToGrouped(p)">
          <div style="margin:4px 0 0 -4px">{{ p.alias }}</div>
          <q-tooltip class="text-white text-h6 bg-indigo-9">{{ p.handicap }} {{ p.name }}</q-tooltip>
        </div>
        <div v-else-if="p.alias!=null && p.alias.length==2" :class="shadow('round-66')" @click="moveToGrouped(p)">
          <div>{{ p.alias }}</div>
          <q-tooltip class="text-white text-h6 bg-indigo-9">{{ p.handicap }} {{ p.name }}</q-tooltip>
        </div>
        <div v-else-if="p.alias!=null && p.alias.length==3" :class="shadow('round-66')" style="font-size:22px" @click="moveToGrouped(p)">
          <div style="margin:5px 0 0 2px">{{ p.alias }}</div>
          <q-tooltip class="text-white text-h6 bg-indigo-9">{{ p.handicap }} {{ p.name }}</q-tooltip>
        </div>
        <div v-else-if="p.alias!=null && p.alias.length==4" :class="shadow('round-66')" style="font-size:19px" @click="moveToGrouped(p)">
          <div style="margin:8px 0 0 1px">{{ p.alias }}</div>
          <q-tooltip class="text-white text-h6 bg-indigo-9">{{ p.handicap }} {{ p.name }}</q-tooltip>
        </div>
        <div v-else-if="p.alias!=null && p.alias.length==5" :class="shadow('round-66')" style="font-size:18px" @click="moveToGrouped(p)">
          <div style="margin:8px 0 0 -3px">{{ p.alias }}</div>
          <q-tooltip class="text-white text-h6 bg-indigo-9">{{ p.handicap }} {{ p.name }}</q-tooltip>
        </div>
        <div v-else-if="(p.alias!=null && p.alias.length>=6)" :class="shadow('round-66')" style="font-size:16px" @click="moveToGrouped(p)">
          <div style="margin:10px 0 0 -3px">{{ p.alias }}</div>
          <!-- <q-tooltip class="text-white text-h6 bg-indigo-9">{{ p.handicap }} {{ p.name }}</q-tooltip> -->
        </div>
        <div v-else :class="shadow('round-66')" style="font-size:15px">{{ p.alias }}</div>
      </div>
    </q-card-actions>
  </q-card>
  <q-btn-group glossy spread>
    <q-btn v-if="openSlots===1" color="cyan-10" no-caps :label="'Show Player List (' + openSlots + ' open)'" @click="showPlayerList" />
    <q-btn v-else-if="openSlots>1" color="cyan-10" no-caps :label="'Show Player List (' + openSlots + ' opens)'" @click="showPlayerList" />
  </q-btn-group>
  <TeamMatchPlayers :tplayers="getTplayers()" :paliases="aliases" @move-to-grouping="moveToGrouping" />
  <!-- <KJNewPlayerDialog ref="refKJNewPlayerDialog" /> -->
  <!-- <KJNewPlayerDialog /> -->
</div>
</template>
<script setup>
import { ref, computed } from 'vue'
// import { useQuasar } from 'quasar'
// import { useStore } from 'vuex'
import { axiosFunctions } from '../composables/axiosFunctions'
import { cssFunctions } from '../composables/cssFunctions'
import { groupFunctions } from '../composables/groupFunctions'
import { libFunctions } from '../composables/libFunctions'
import { UserGuideTitles } from '../composables/UserGuideTitles'
import emitter from 'tiny-emitter/instance'

// import InfoDisplay from 'src/components/InfoDisplay'
import TeamMatchPlayers from './TeamMatchPlayers'
// import KJNewPlayerDialog from '../components/KJNewPlayerDialog'
import Tooltip from 'src/components/ToolTip'

//== data section
const dev = false
const props = defineProps({
  gameId: { type: Number },
  handicapFlag: { type: Number },
  games: { type: Array },
  aliases: { type: Array },
  handicaps: { type: Object },
  matchDate: { type: String },
  // kjNewPlayer: { type: Array },
})
const emit = defineEmits([
  'info-display',
  'course-info',
  'change-tmnt',
  'switch-aliases',
  'switch-handicap',
])
// const tabs = { MatchGroupingPlayers, InfoDisplay }
// const currentTab = ref('MatchGroupingPlayers')
const { shadow, condShadow, getTeeColor, teamColor, getAvatar, zhcharRegExp } = cssFunctions()
const { grpScenario8, grpScenario, getABS } = groupFunctions()
const { paxios } = axiosFunctions()
var { pagename, year, screenwidth, store } = libFunctions()
pagename.value = 'TeamMatchGrouping'
// const store = useStore()
// const q = useQuasar()
const showtip = ref({rp:[], mg:false, ap:[], gp:false})
// var year = new Date().getFullYear()
const grouped = ref([])
const tmntId = ref([]) // default tmntId for storeMatchPlayers
// const paliases = ref({ JZs:[], MMs:[] })
// const paliases = ref(props.aliases)
const aliName = props.gameId == 14 ? ref('MMs') : props.gameId == 13 ? ref('JZs') : ref('JZs')
const tpids = ref([])
const gsx = ref(0)
const prevGsx = ref(-1)
const currentGsx = ref(-1)
const Ahcap = ref(0)
const Bhcap = ref(0)
const ABdif = ref(0)
const lastHandicapDate = ref(null)
const showMatchGrouping = ref(true)
// const refKJNewPlayerDialog = ref(null)
var groupedPlayer = {}

//== computed section
const paliases = computed(() => { console.log(`%cwxh's ALIAS alias=${props.aliases[14].alias}`,'color:lime;font-size:30px'); return props.aliases })
const compHandicapFlag = computed(() => { return props.handicapFlag })
const tpidsLen = computed(() => { return tpids.value.length })
const openSlots = computed(() => { return grouped.value.length*4 - tpids.value.length })
// const tplayers = computed(() => {
//   let players = []
//   grouped.value.forEach(gx => {
//     players = players.concat(gx.players)
//   })
//   return players
// })
// console.log(`-CP groupingDone openSlots=${openSlots.value}`, !getTplayers().map(p => p.team).includes('X'))
const groupingDone = computed({
  get: () => { return openSlots.value === 0 && !getTplayers().map(p => p.team).includes('X') },
  set: val => openSlots.value = val
})
const readyToFinalize = computed(() => {
  const players = getTplayers()
  // console.table(players)
  const x = players.filter(p => p.team == 'X')
  return openSlots.value == 0 && x.length == 0
})
const manuallyGrouping = computed(() => { return tpidsLen.value>0 && tpidsLen.value%4==0 && gsx.value>0 })
const groupingMatchPlayers = computed(() => { return tpidsLen.value>0 && tpidsLen.value%4==0 })
const removeMatchPlayer = computed(() => { return openSlots.value != 0 })
const userGuide = computed(() => {
  const x = store.userGuide.split('<hr>')
  if (tpidsLen.value>0 && tpidsLen.value%4==0 && gsx.value>0) return x[3]
  else if (tpidsLen.value>0 && tpidsLen.value%4==0) return x[1]
  else if (openSlots.value != 0) return x[2]
  return x[0]
})
const { manualGrouping, groupingPlayers, removePlayer, defaultTitle } = UserGuideTitles()
const userGuideTit = computed(() => {
  let tit = null
  if (manuallyGrouping.value) tit = manualGrouping()
  else if (groupingMatchPlayers.value) tit = groupingPlayers()
  else if (removeMatchPlayer.value) tit = removePlayer()
  else tit = defaultTitle()
  return tit
})

// onMounted(() => {
//   refKJNewPlayerDialog
//   // console.log(`-MT-refKJNewPlayerDialog`, refKJNewPlayerDialog.value)
// })

//== main starting
// getAliases()
// getKJAliases()

console.log(`-ST-TeamMatchGrouping gameId=${props.gameId} matchDate=${props.matchDate}`, grouped.value, 'props.games=', props.games)

//== emitter.on section
emitter.on('show-match-grouping', (x, y) => { showMatchGrouping.value = x; gsx.value = y } )
// emitter.on('golf-getAliases', (x) => setAliases(x))
emitter.on('do-grouping', (gsx, year, groups) => doGrouping(gsx, year, groups))
emitter.on('open-slots', (x) => openSlots.value = x)
emitter.on('user-guide-JZsMatch', () => showUserGuide())
// emitter.on('add-kj-new-player', (x) => addKjNewPlayer(x))

//== function section
// function addKjNewPlayer (da) {
//   console.log(`-%cCK-fn-addKjNewPlayer`, 'color:red;font-size:19px', da)
// }
function switchAliases () {
  // console.log(`-%cCK-fn-aliName='switchAliases' aliName=${aliName.value}`, 'color:red;font-size:19px')
  aliName.value = aliName.value == 'JZs' ?  'MMs' : 'JZs'
  emit('switch-aliases', aliName.value)
}
function showMatchGrouped () {
  console.log(`-%cCK-fn-groupScenario gsx=${gsx.value}`, 'color:red;font-size:19px')
  showMatchGrouping.value = false
  emitter.emit('show-match-grouped', true, gsx.value)
}
function groupScenario8 () {
  gsx.value = 8
  currentGsx.value = 8
  const groupedPlayers = grpScenario8(getTplayers())
  // console.log(`-CK-fn-groupScenario gsx=${gsx.value}`, getTplayers(), groupedPlayers)
  groupedPlayers.forEach((players, i) => {
    grouped.value[i].grp = i + 1
    grouped.value[i].players = players // tmntId will reasigned in saveGrouping()
  })
  calcTeamHandicapDiffs()
}
function groupScenario (gx) {
  if (gx == 8) return groupScenario8()
  // console.log(`%c-fn-groupScenario gsx=${gsx.value}`, 'color:red;font-size:20px')
  prevGsx.value = gsx.value
  gsx.value = gx
  currentGsx.value = gx
  const groupedPlayers = grpScenario(gsx.value, getTplayers())
  // console.log(`-CK-fn-groupScenario gsx=${gsx.value}`, getTplayers(), groupedPlayers)
  groupedPlayers.forEach((players, i) => {
    grouped.value[i].grp = i + 1
    grouped.value[i].players = players // tmntId will reasigned in saveGrouping()
  })
  calcTeamHandicapDiffs()
}
function cancelGrouping () {
  console.log(`%c-fn-cancelGrouping gsx=${prevGsx.value}`, 'color:red;font-size:20px')
  if (groupingDone.value) {
    showMatchGrouping.value = false
    // emit('show-team-match')
    emitter.emit('show-match-grouped', true, prevGsx.value)
  }
}
function saveGrouping () {
  showMatchGrouping.value = false
  currentGsx.value = -1
  console.log(`-CK-fn-saveGrouping gsx=${gsx.value} currentGsx=${currentGsx.value}`)
  const players = getTplayers()
  const tplayers = []
  players.forEach(p => {
    p.tmntId = grouped.value[p.grp - 1].tmntId
    const x = { id:p.id, name:p.name, tmntId:p.tmntId, gameId:props.gameId, year:year, grp:p.grp, tnum:p.team, player_id:p.player_id }
    tplayers.push(x)
  })
  const inData = { gsx:gsx.value, tmntIds:grouped.value.map(p => p.tmntId), tplayers:tplayers }
  // console.table(tplayers.sort((a, b) => a.grp - b.grp))
  const path = process.env.API + '/golf/saveGrouping'
  paxios(path, inData)
  if (groupingDone.value) {
    showMatchGrouping.value = false
   emitter.emit('show-match-grouped', true, gsx.value)
  }
  // emitter.emit('show-match-grouped', true, gsx.value)
}
// function XX_getKJAliases() {
//   // console.log(`-CK-fn-getKJAliases gameId=${gameId} matchDate=${props.matchDate}`)
//   if (paliases.value.MMs.length > 0) return
//   const path = process.env.API + '/golf/getAliases/14/0'
//   gaxios(path)
// }
// function XX_getAliases() {
//   console.log(`-CK-fn-getAliases gameId=${props.gameId} matchDate=${props.matchDate}`)
//   if (paliases.value.JZs.length > 0) return
//   const path = process.env.API + '/golf/getAliases/' + props.gameId + '/0'
//   gaxios(path)
// }
// function XX_setAliases(da) {
//   // console.log(`%c-CK-fn-setAliases gameId=${props.gameId}`, 'color:pink;font-size:11px', da)
//   lastHandicapDate.value = da.lastHandicapDate
//   // paliases.value.JZs = da.aliases.sort((a, b) => a.alias < b.alias ? -1 : 1)
//   // paliases.value.MMs = da.aliases.sort((a, b) => a.alias < b.alias ? -1 : 1)
//   if (da.gameId == 13) paliases.value.JZs = da.aliases.sort((a, b) => a.alias < b.alias ? -1 : 1)
//   else if (da.gameId == 14) paliases.value.MMs = da.aliases.sort((a, b) => a.alias < b.alias ? -1 : 1)

//   console.log(`-CK-fn-setAliases`, paliases.value.MMs)

//   // let kjnp = props.kjNewPlayer[0]
//   // if (da.status == 'ADD_NEW_SIM_PLAYER') {
//   // if (props.kjNewPlayer.length > 0) {
//   //   // refKJNewPlayerDialog.value.openIt('create', { id:kjnp.id, firstname:kjnp.firstname, lastname:kjnp.lastname }, props.gameId, paliases.value.MMs)
//   //   emitter.emit('open-KJNewPlayerDialog', 'create', { id:kjnp.id, firstname:kjnp.firstname, lastname:kjnp.lastname }, props.gameId, paliases.value.MMs)
//   // }
//   store.commit('golf/setUserGuide', da.userGuide == null ? '' : da.userGuide[0].user_guide)
//   store.commit('golf/setUserGuideId', da.userGuide == null ? 0 : da.userGuide[0].id)
//   emitter.emit('upd-user-guide', da.userGuide == null ? false : true)
// }
function doGrouping(gx, yr, groups) {
  showMatchGrouping.value = true
  tpids.value = []
  gsx.value = gx
  year = yr
  tmntId.value = groups[0].tmntId
  grouped.value = groups
  console.log(`-fn-doGrouping A gsx=${gsx.value} year=${year} openSlots=${openSlots.value} showMatchGrouping=${showMatchGrouping.value}`, grouped.value)
  const players = getTplayers()
  tpids.value = players.map(p => p.player_id)
  // console.log(`-CK-fn-doGrouping- pagename=${pagename.value} openSlots=${openSlots.value} tpids.length=${tpids.value.length}`, tpids.value, paliases.value)
  // console.log(`-fn-doGrouping B gsx=${gsx.value} tpidsLen=${tpids.value.length} openSlots=${openSlots.value} groupingDone=${groupingDone.value}`, paliases.value, tpids.value, grouped.value[0])
  pagename.value = pagename.value.replace(/(^.*)_(.*)$/, "$1" + '_' + 'group')
  // console.log(`-fn-doGrouping- pagename=${pagename.value} openSlots=${openSlots.value}`, paliases.value)
  calcTeamHandicapDiffs()
  if (dev) showGroupedPlaysers()
}
function getTplayers() {
  let players = []
  grouped.value.forEach(gx => players = players.concat(gx.players))
  return players
}
function calcTeamHandicapDiffs() {
  const players = getTplayers()
  // console.log(`-CK-fn-calcTeamHandicapDiffs grouped.length=${grouped.value.length} tpids.length=${tpids.value.length}`, players, grouped.value)
  if (players.find(p => p.team === 'X')) return
  if (tpids.value.length == 0 || tpids.value.length%4 != 0 || tpids.value.length < grouped.value.length*4) return
  console.log(`-CK-fn-calcTeamHandicapDiffs grouped.length=${grouped.value.length}`)
  grouped.value.forEach((g) => {
    // console.log('AAAA', i, grouped.value.length)
    const players = g.players.sort((a, b) => a.team < b.team ? -1 : 1)
    const ahcap = players.filter(p => /A\d/.test(p.team)).map(x => x.handicap).reduce((a, b) => a + b, 0) / 2
    const p1 = players[0]
    if (p1 == undefined) return
    // console.table(players)
    // console.log(`gsx=${gsx.value}`, p1)
    p1.hcap = ahcap
    const bhcap = players.filter(p => /B\d/.test(p.team)).map(x => x.handicap).reduce((a, b) => a + b, 0) / 2
    const p4 = players[3]
    p4['hcap'] = bhcap

    const p2 = players[1]
    p2['hcap'] = ahcap - bhcap
    const p3 = players[2]
    p3['hcap'] = 'X'
    // console.table(players.map(p => [p.hcap]))
    // console.log('BBBB', i, grouped.length)
  })
  const Acaps = getTplayers().filter(p => /A\d/.test(p.team)).map(x => x.handicap)
  const Bcaps = getTplayers().filter(p => /B\d/.test(p.team)).map(x => x.handicap)
  Ahcap.value = Acaps.reduce((a, next) => a + next, 0) / Acaps.length
  Bhcap.value = Bcaps.reduce((a, next) => a + parseFloat(next), 0) / Bcaps.length
  ABdif.value = Ahcap.value - Bhcap.value
  if (gsx.value == null) gsx.value = 7
}
function moveToGrouped (p) {
  console.log(`-CK-fn-moveToGrouped groupingDone=${groupingDone.value} openSlots=${openSlots.value} p.grp=${p.grp}`, p)
  // console.log(`-fn-moveToGrouped groupingDone=${groupingDone.value} openSlots=${openSlots.value} p.grp=${p.grp}`, !getTplayers().map(p => p.team).includes('X'))
  groupedPlayer = p
  addToNextSlot(p)
  showGroupedPlaysers()
  if (tpids.value.find(x => x === p.player_id) == undefined) tpids.value.push(p.player_id)

  p.year = year
  const path = process.env.API + '/golf/moveToGrouped'
  paxios(path, p)
  if (groupingDone.value) {
    const path = process.env.API + '/golf/addupdMatchGroups'
    const inData = { gsx:7, tmntIds:grouped.value.map(p => p.tmntId) }
    paxios(path, inData)
  }
  // console.log(`-fn-moveToGrouped groupingDone=${groupingDone.value} openSlots=${openSlots.value} p.grp=${p.grp}`)
  showGroupedPlaysers()
  if (groupingDone.value) {
    showMatchGrouping.value = false
    // emit('show-team-match', getTplayers())
    // emit('set-gscenario', 7) // manual grouping
    gsx.value = 7
    emitter.emit('show-match-grouped', true, gsx.value)
  }
}
function showGroupedPlaysers () {
  if (!dev) return
  for (let i=0; i<grouped.value.length; i++) console.table(grouped.value[i].players.map(p => [p.id, p.tmntId, p.name, p.player_id, p.grp, p.team]))
}
function moveOutGrouped (g, p) {
  console.log(`-CK-fn-moveOutGrouped pid=${p.id} p.name=${p.name}`)
  if (p.team == 'X' && p.grp == -1)  {
    console.log(`${p.name} with id=${p.id} already out grouped, do nothing return ...`)
    return
  }
  Ahcap.value = 0
  Bhcap.value = 0
  p.team = 'X'
  p.grp = -1
  g.players = g.players.filter(x => x.player_id != p.player_id)
  getLastGroup().players.push(p)
  const path = process.env.API + '/golf/moveOutGrouped'
  p.tmntIds = grouped.value.map(x => x.tmntId)
  paxios(path, p)
  showGroupedPlaysers()
}
emitter.on('golf-moveToGrouping', (x) => setTplayerId(x))
emitter.on('golf-moveToGrouped', (x) => groupedPlayer.id = x.id)
// emitter.on('golf-moveToGrouped', (x) => setTplayerId(x))
function setTplayerId (da) {
  const tpid = da.id
  const playerId = da.playerId
  // activeP.id = tpid
  const activeP = getLastGroup().players.find(p => p.player_id == playerId)
  activeP.id = tpid
  console.log(`-fn-setTplayerId: playerId=${playerId} tpid=${tpid}`, getLastGroup().players)
}
// function getPlayerHandicap (p) {
//   let ret = paliases.value.filter(x => x.player_id == p.player_id)
//   console.log(`-fn-getPlayerHandicap:`, ret)
//   if (ret.length == 0) return 28.3
//   return paliases.value.filter(x => x.player_id == p.player_id)[0].handicap
// }
function moveToGrouping (p) {
  console.log(`-CK-fn-moveToGrouping p.name=${p.name}`, getLastGroup().tmntId, props.handicaps)
  tpids.value.push(p.player_id)
  p.team = 'X'
  p.tmntId = getLastGroup().tmntId
  p.year = year
  p.grp = -1
  p.game_id = props.gameId
  if (p.handicap == null) p.handicap = props.handicaps[p.player_id]
  getLastGroup().players.push(p)
  // activeP = p
  console.log(`-CK-fn-moveToGrouping openSlots=${openSlots.value}`)
  // if (props.gameId != 14) {
  const path = process.env.API + '/golf/moveToGrouping'
  paxios(path, p)
  // }
}
function moveOutGrouping (g, p) {
  let pi = tpids.value.findIndex(x => x == p.player_id)
  if (pi > -1) tpids.value.splice(pi, 1)
  g.players.forEach((x, i) => { if (x.player_id == p.player_id) g.players.splice(i, 1) })
  console.log(`-fn-moveOutGrouping pid=${p.id} openSlots=${openSlots.value} tpidsLen=${tpidsLen.value} grouped.length=${grouped.value.length}`, tpids.value, paliases.value)
  const path = process.env.API + '/golf/moveOutGrouping'
  paxios(path, p)
  // if (props.gameId != 14) {
  //   const path = process.env.API + '/golf/moveOutGrouping'
  //   paxios(path, p)
  // }
  // groupingDone.value = !getTplayers().map(x => x.team).includes('X')
}
function getLastGroup () {
  return grouped.value[grouped.value.length - 1]
}
function showPlayerList () {
  // if (props.gameId === 15) return showMemberList()
  emitter.emit('match-grouping-players', openSlots.value)
  console.log('-fn-showPlayerList')
}
// function groupScenario (gx) {
//   prevGsx.value = gsx.value
//   gsx.value = gx
//   currentGsx.value = gx
//   const groupedPlayers = grpScenario(gsx.value, getTplayers())
//   console.log(`-CK-fn-groupScenario gsx=${gsx.value}`, getTplayers(), groupedPlayers)
//   groupedPlayers.forEach((players, i) => {
//     grouped.value[i].grp = i + 1
//     grouped.value[i].players = players // tmntId will reasigned in saveGrouping()
//   })
//   calcTeamHandicapDiffs()
// }
function getNextGroup (p) {
  console.log(`-CK-fn-getNextGroup grouped=`, grouped.value)
  console.log('-CK-games:', props.games)
  let gi = -1
  let gmi = {}
  for (let i=0; i<grouped.value.length; ++i) {
    gmi= props.games[i]
    const plen = grouped.value[i].players.filter(p => p.grp > 0).length
    if (plen < 4) {
      gi = i
      break
    // } else if (gdi.players.find(p => p.team == 'X')) {
    //   // console.table(gpi)
    //   gi = i
    }
  }
  if (gi === grouped.value.length - 1) console.log(`grouping done`)
  p.tmntI = gmi.tournament_id
  p.teetime = gmi.start_at
  p.courseId = gmi.course_id
  p.teeboxId = gmi.mens_tee_id > 0 ? gmi.mens_tee_id : gmi.lady_tee_id
  return gi
}
function addToNextSlot (p) {
  const gi = getNextGroup(p)
  const pi = getLastGroup().players.findIndex(x => x.player_id == p.player_id)
  console.log(`pi=${pi} p.name=${p.name} nextGroup=${gi}`, grouped.value)
  if (pi>=0) getLastGroup().players.splice(pi, 1)
  p.grp = gi + 1
  let g = grouped.value[gi]
  const team = g.players.map(x => x.team)
  if (!team.includes('A1')) p.team = 'A1'
  else if (!team.includes('A2')) p.team = 'A2'
  else if (!team.includes('B1')) p.team = 'B1'
  else if (!team.includes('B2')) p.team = 'B2'
  p.tmntId = g.tmntId
  p.name = p.name.replace('(DBA)', '')
  g.players.push(p)
  console.log(`-fn-addToNextSlot-${p.name}-gi=${gi} team=${p.team} openSlots=${openSlots.value} readyToFinalize=${readyToFinalize.value}`, team)
  if (readyToFinalize.value) {
    gsx.value = 7
    calcTeamHandicapDiffs()
  }
  // storeMatchPlayers()
}
function closeTips () {
  Object.keys(showtip.value).forEach(key => {
    if (!['rp', 'ap'].includes(key)) showtip.value[key] = false
  })
  showtip.value.rp[0] = false
  showtip.value.ap[0] = false
}
function showUserGuide () {
  console.log(`-fn-ShowUserGuide gameId=${props.gameId}`, userGuide.value)
  closeTips()
  if (manuallyGrouping.value) showtip.value.mp = true
  else if (groupingMatchPlayers.value) showtip.value.gp = true
  else if (removeMatchPlayer.value) { showtip.value.rp[0] = true; showtip.value.ap[0] = true }
  else showtip.value.st = true
  emitter.emit('open-InfoDisplay', userGuideTit.value, userGuide.value)
}
function unGrouping () {
  console.log(`-CK-fn-unGrouping`)
  const players = getTplayers()
  // players.forEach(p => { p.team = 'X'; moveOutGrouped(grouped.value[p.grp-1], p) })
  players.forEach(p => moveOutGrouped(grouped.value[p.grp-1], p))
  grouped.value.forEach(g => g.players = [])
  getLastGroup().players = players
  gsx.value = null
  Ahcap.value = 0
  Bhcap.value = 0
  ABdif.value = 0
}
</script>

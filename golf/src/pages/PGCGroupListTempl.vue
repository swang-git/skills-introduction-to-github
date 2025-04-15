<template>
<div class="bg-teal-10 q-pa-xs">
  <div v-if="tmnt.id>0">
    <div class="text-center text-h6 text-cyan-2">{{ gameInfo }}</div>
    <div class="flex-center text-h6 text-cyan-1 row">
      <div>At {{ tmnt.courseName }}</div>
      <div v-if="shotgun">(shotgun)</div>
    </div>
    <div v-for="(g, i) in groups" :key=g :class="getBGcolor(i)">
      <div class="ellipsis" :class="{ textCenter:isIM }">
        <q-chip outline no-wrap :class="getTeeColor(g.teename)" style="margin:0"> <!-- move left a bit -->
          <span>Group Plays
            <b class="bg-cyan-1 text-bold text-body1 q-ma-xs q-px-xs">{{ g.teename }} Tee</b><span class="q-pl-xs">Start At : </span>
            <b class="bg-amber-2 text-bold q-ma-sm q-px-xs text-body1 text-red">{{ g.startAt }}</b>
          </span>
        </q-chip>
      </div>
      <table>
        <tr v-for="(p, pi) in g.players.sort((a,b) => { return a.captain - b.captain })" :key=p class="text-h6 text-white">
          <td width="200px" no-wrap :class="{'bg-red':p.captain==1, 'bg-blue-9':p.captain!=1}">
            <q-btn round dense :icon="getNature(p)" @click="moveOutGrouping(p, g)" :disable="!PGCsAdmin" />{{ p.name }}</td>
          <td v-if="pi==0"><q-btn class="text-body1 text-black" round outline :label="parseInt(p.gamefee)" /></td>
          <td v-if="pi==1"><q-btn class="text-body1 text-black" round outline :label="parseInt(p.gamefee)" /></td>
          <td v-if="pi==2"><q-btn class="text-body1 text-black" round outline :label="parseInt(p.gamefee)" /></td>
          <td v-if="pi==3"><q-btn class="text-body1 text-black" round outline :label="parseInt(p.gamefee)" /></td>
          <td v-if="pi==0"><q-btn :disable="!PGCsAdmin" :class="getAvgColor(p.pscore)" @click="setGameScore(p)" round>{{ p.pscore}}</q-btn></td>
          <td v-if="pi==1"><q-btn :disable="!PGCsAdmin" :class="getAvgColor(p.pscore)" @click="setGameScore(p)" round>{{ p.pscore}}</q-btn></td>
          <td v-if="pi==2"><q-btn :disable="!PGCsAdmin" :class="getAvgColor(p.pscore)" @click="setGameScore(p)" round>{{ p.pscore}}</q-btn></td>
          <td v-if="pi==3"><q-btn :disable="!PGCsAdmin" :class="getAvgColor(p.pscore)" @click="setGameScore(p)" round>{{ p.pscore}}</q-btn></td>
        </tr>
      </table>
    </div>
    <div v-for="p in compPlayers" :key=p>
      <q-chip color="teal-9" text-color="white" style="width:210px" size="18px">
        <q-avatar><img :src="getAvatar(p)"> </q-avatar> {{p.name}}
      </q-chip>
      <q-btn round glossy :color="p.type==null ? 'green-10' : 'grey'" @click="addToMembership(p)" :disable="p.type!=null">
        <q-icon :name="p.type" class="q-pb-sm"/>
        <q-tooltip class="bg-cyan-10 text-cyan-1 text-h6">Add {{ p.name }} to Membership</q-tooltip>
      </q-btn>
      <q-btn round glossy color="amber-10" icon="add_circle" @click="moveToGrouped(p)" :disable="p.type==null">
        <q-tooltip class="bg-cyan-10 text-cyan-1 text-h6">Add to Group{{ p.name }}</q-tooltip>
      </q-btn>
    </div>
  </div>
  <MemberDialog ref=refMemberDialog @add-membership="setMembershipType" />
  <HolePad ref=refHolePad @set-start-at="setStartAt" />
</div>
</template>
<script setup>
import { ref, computed, onMounted } from 'vue'
import emitter from 'tiny-emitter/instance'
import { useStore } from 'vuex'
import { axiosFunctions } from '../composables/axiosFunctions'
// import { dayFunctions } from '../composables/dayFunctions'
import { cssFunctions } from '../composables/cssFunctions'
import { libFunctions } from '../composables/libFunctions'
const { getAvgColor } = cssFunctions()
const { getAvatar, PGCsAdmin, isIM } = libFunctions()
// import SelOptionsWithSearch from 'src/components/SelOptionsWithSearch'
import MemberDialog from '../components/MemberDialog'
import HolePad from 'src/components/HolePad'
const store = useStore()
const { paxios, gaxios } = axiosFunctions()

//== data section
const currentCaptain = ref({})
const currentGroup = ref({})
const tplayers = ref([])
const players = ref([])
const grouped = ref([])
const groups = ref([])
const searchQuery = ref('')
const refMemberDialog = ref(null)
const refHolePad = ref(null)
const year = (new Date()).getFullYear()
const mtype = ref('G')
var moveToGroupedPlayer = {}
// const tmntId = props.tmnt.id
const gameId = props.tmnt.game_id
const shotgun = props.tmnt.teetime_gap == 0 ? true : false

const props = defineProps({ tmnt: Object })
onMounted(() => { refMemberDialog, refHolePad})

//== main == section
console.log(`-ST-PGCGroupListTempl shotgun=${shotgun} teetime_gap=${props.tmnt.teetime_gap}`, props.tmnt)
emitter.on('golf-getPGCGamePlayers', (x) => setPGCGamePlayers(x))
emitter.on('golf-getPlayers', (x) => { players.value = x.players; /* console.log('players', players.value) */ })
emitter.on('search', (x) => { searchQuery.value = x; console.log('searching for', searchQuery.value) })
emitter.on('golf-moveToGrouped', (x) => { moveToGroupedPlayer.id = x.id; /* console.table(moveToGroupedPlayer) */})

showGroups()

function setStartAt (startHole) {
  currentCaptain.value.tnum = startHole
  currentGroup.value.startAt = startHole
  console.log(`-CK-fn-setStartAt startHole=${startHole}`)
  // currentGroup.value.players.push(currentCaptain.value)
  sendToDB(currentCaptain.value)
}
function setMembershipType (mtyp) {
  mtype.value = mtyp
  console.log(`-CK-fn-setMembershipType gameId=${props.tmnt.game_id} year=${year} mtype=${mtype.value}`)
}
function addToMembership (p) {
  console.log(`-CK-fn-addToMembership gameId=${props.tmnt.game_id} year=${year}`, p)
  refMemberDialog.value.openIt('add', p, true, gameId)
}
function moveOutGrouping (p, g) {
  console.log('-CK-moveOutGrouping')
  // console.table(p)
  // console.table(g)
  g.players = g.players.filter(x => x.player_id != p.player_id)
  p.grp = 0
  p.captain = 0
  players.value.unshift(p)
  tplayers.value = tplayers.value.filter(x => x.player_id != p.player_id)
  const path = process.env.API + '/golf/moveOutGrouping'
  paxios(path, p)
}
function addToOpenSlot (p) {
  const glen = groups.value.length
  for (let i = 0; i < glen; i++) {
    console.log(`-CK-fn-addToOpenSlot groupslength=${glen}`)
    let g = groups.value[i]
    if (g.players.length < 4) {
      p.grp = i + 1
      for (let j=1; j<=4; j++) {
        if (g.players.find(x => x.captain === j) == undefined) {
          p.captain = j
          if (j === 1) {
            setGroupStartAt(i, p, g)
            g.players.push(p)
            return true
          }
        }
      }
      g.players.push(p)
      sendToDB(p)
      return true
    }
  }
  return false
}
function moveToGrouped (p) {
  let glen = groups.value.length
  console.log(`-CK-fn-moveToGrouped gameId=${props.tmnt.game_id} glen=${glen}`, p, groups.value)
  p.gameId = props.tmnt.game_id
  p.tmntId = props.tmnt.id
  p.gamefee = props.tmnt.fees
  p.year = props.tmnt.start_at.substring(0, 4)
  if (addToOpenSlot(p)) return
  if (glen === 0) { // the 1st added player come to here
    console.log(`-CK-fn-moveToGrouped the 1st added player come to here groupslength=${glen}`)
    p.grp = 1
    p.captain = 1
    // let g0 = { teeOffTime: getTeeOffTime(0, props.tmnt.start_at.substring(11, 16), props.tmnt.teetime_gap), players:[p],teename:p.gender=='M' ? props.tmnt.mtee : props.tmnt.ltee }
    let g = { players:[p],teename:p.gender=='M' ? props.tmnt.mtee : props.tmnt.ltee }
    setGroupStartAt(glen, p, g)
  } else {
    let gi = glen - 1
    if (groups.value[gi].players.length < 4) {
      p.grp = glen
      p.captain = groups.value[gi].players.length + 1
      groups.value[gi].players.push(p)
    } else { // already 4 create new group
      p.grp = glen + 1
      p.captain = 1
      let g = { players:[p],teename:p.gender=='M' ? props.tmnt.mtee : props.tmnt.ltee }
      groups.value.push(g)
      setGroupStartAt(glen, p, g)
    }
  }
}
function setGroupStartAt(lastgIdx, p, g) {
  console.log(`-CK-fn-setGroupStartAt groupLength=${groups.value.length} lastgIdx=${lastgIdx}`)
  if (shotgun) {
    currentCaptain.value = p
    currentGroup.value = g
    const lastStartAt = groups.value[lastgIdx] === undefined ? 0 : groups.value[lastgIdx].startAt
    refHolePad.value.openIt(lastStartAt)
  } else {
    g.startAt = getTeeOffTime(lastgIdx, props.tmnt.start_at.substring(11, 16), props.tmnt.teetime_gap)
    sendToDB(p)
  }
  if (groups.value.find(x => x.startAt == g.startAt)) return
  groups.value.push(g)
  // console.table(p)
  // sendToDB(p)
}
function sendToDB (p) {
  moveToGroupedPlayer = p
  console.table(p)
  grouped.value.push(p)
  tplayers.value.push(p)
  players.value = players.value.filter(x => x.player_id != p.player_id)
  const path = process.env.API + '/golf/moveToGrouped'
  paxios(path, p)
}
const compPlayers = computed(() => {
   var filterKey = searchQuery.value.length > 0 && searchQuery.value.toLowerCase()
    var data = players.value
    if (filterKey.length > 0) {
      var words = filterKey.split(' ')
      words.forEach(word => {
        data = data.filter(row => {
          return Object.keys(row).filter(key => { return !['id', 'catsId', 'subcId', 'payeId', 'paymId'].includes(key) }).some(key => {
            return String(row[key]).toLowerCase().indexOf(word) >= 0
          })
        })
      })
    }
  return data
})
const gameInfo = computed(() => {
  console.log('-CK-cp-gameInfo', props.tmnt)
  const dd = props.tmnt.start_at.substring(0, 10)
  // const tit = dd + ' (' + dd.chwk2() + ') ' + (shotgun ? props.tmnt.start_at.substring(10,16) : '') + ' ' + props.tmnt.game + ' at ' + props.tmnt.courseName
  // const tit = dd + ' (' + dd.chwk2() + ') ' + (shotgun ? props.tmnt.start_at.substring(10,16) : '') + ' ' + props.tmnt.game
  const tit = props.tmnt.game + ' ' + dd + ' (' + dd.chwk2() + ') ' + (shotgun ? props.tmnt.start_at.substring(10,16) : '')
  return tit
})
function getBGcolor (i) {
  const numPlayersInGroup = groups.value[i].players.length
  if (numPlayersInGroup > 4) {
    groups.value[i].note = 'There are more than 4 players in the group'
    return 'bg-red'
  } else if (i % 2 === 0 && numPlayersInGroup === 4) {
    return 'bg-green-4'
  } else if (numPlayersInGroup < 4) {
    return 'bg-yellow-5'
  } else {
    return 'bg-green-6'
  }
}
function getNature (p) { return p.gender === 'M' ? 'nature_people' : 'nature' }
function getTeeColor (tee) {
  if (tee === 'Player') tee = 'purple-2'
  else if (tee === 'Blue') tee = 'blue-4'
  else if (tee === 'Green') tee = 'green-8'
  else if (tee === 'Gold') tee = 'yellow-10'
  else if (tee === 'White') tee = 'white'
  else if (tee === 'Black') tee = 'grey-4'
  return 'bg-' + tee
}
function getGamefee (p) {
  return p.type === 'H' ? '0' : p.course_member == 'FG' ? 25 : parseInt(props.tmnt.fees)
}
function getGroupStartAt(grp, gameStartAt, teeTimeGap) {
  return getTeeOffTime(grp, gameStartAt, teeTimeGap) // will be overriden if shotgun
}
function getTeeOffTime (grp, hhmm, tgap) {
  const x = hhmm.split(':')
  const hh = x[0]
  const mm = x[1]
  let d = new Date(2022, 6, 16, hh, mm, 0)
  const min = parseInt(d.getMinutes() + grp * tgap)
  d.setMinutes(min)
  var ttm = d.toLocaleString().substring(11, 16)
  const txm = ttm.replace(/:$/, '')
  // console.log(`-fn-from libs getTeeOffTime = ${hhmm}, ${tgap}, ${txm}`)
  return txm
}
function setGroups (tpls) {
  tplayers.value = tpls.filter(p => p.grp > 0)
  players.value = tpls.filter(p => p.grp == 0 || p.grp == null)
  console.log('-CK-fn-setGroups for tmnt', props.tmnt, tpls)
  const numGroups = Math.ceil(tplayers.value.length / 4)
  groups.value = Array(numGroups)
  // openSlots.value = numGroups * 4 - tplayers.value.length
  console.log(`-CK-fn-setGroups numGroups=${numGroups} `, tplayers.value, groups.value)

  for (let i=0; i<numGroups; ++i) {
    const groupStartAt = getGroupStartAt(i, props.tmnt.start_at.substring(11, 16), props.tmnt.teetime_gap)
    console.log(`-CK-fn-groupStartAt=${groupStartAt}`)
    const g = { startAt:groupStartAt, players:[] }
    groups.value[i] = g
  }
  // console.table(tplayers.value.map(p => p.tmntId + ' ~ ' + p.grp + ' ~ ' + p.team + ' ~ ' + p.type + ' ~ ' + p.name))
  groups.value.forEach((g, i) => {
    const ttm = props.tmnt.start_at.substring(11, 16)
    g.ttm = ttm
    // console.table(`games(${i}) ${ttm}`)
    const groupStartAt = getGroupStartAt(i, props.tmnt.start_at.substring(11, 16), props.tmnt.teetime_gap)
    const x = {
      idx: i+1, // this will be used for team grouping setup
      tmntId: props.tmntId,
      players: [],
      teename: props.tmnt.mtee,
      startAt: groupStartAt,
    }
    tplayers.value.forEach(p => {
      p.gamefee = getGamefee(p)
      if (p.grp > 0) { // use player's grp to identify -- no tmnId in case to add a new tournament
        if (p.grp === x.idx) {
          x.players.push(p)
          p.grp = x.idx
          x.grp = p.grp
          if (shotgun && p.captain == 1) x.startAt = p.tnum
        }
      }
    })
    // groups.value[i] = Objssign(groups.value[i], x)
    groups.value[i] = x
    // console.log('-ST-tmntId', game.tmn== p.tmntId, p.player, p.tmntId, p.grp)
    // console.log('-ST-d', x.tmntIdtm, groups.value, comps)
  })
  // notGrouped = tplayers.value.filter(p => p.grp === null || p.grp === 0)
  // console.log('-fn-setGroups')
}
function setPGCGamePlayers (da) {
  const tmntId = props.tmnt.id
  const gameId = props.tmnt.game_id
  const year = props.tmnt.year
  console.log(`-fn-setPGCGamePlayers tmntId=${tmntId} gameId=${gameId} year=${year}`, da)
  setGroups(da.PGCPlayers)
}
function getPGCGamePlayers () {
  const tmntId = props.tmnt.id
  const gameId = props.tmnt.game_id
  const year = props.tmnt.year
  console.log(`-fn-getPGCGamePlayers tmntId=${tmntId} gameId=${gameId} year=${year}`)
  const path = process.env.API + '/golf/getPGCGamePlayers/' + tmntId + '/' + gameId + '/' + year
  gaxios(path)
}
// function getPlayers () {
//   console.log(`-CK-fn-getPlayers`)
//   const path = process.env.API + '/golf/getPlayers'
//   gaxios(path)
// }
function showGroups () {
  // const tmntId = props.tmnt.id
  // console.log('-fn-userSelected(tid) for tmnt Id', tmnt.id, activeGames)
  console.log('-CK-fn-userSelected(tid) for tmnt Id', props.tmnt)
  store.commit('golf/setPageTitle', 'Grouping ' + props.tmnt.game)
  groups.value = []
  getPGCGamePlayers()
}
</script>

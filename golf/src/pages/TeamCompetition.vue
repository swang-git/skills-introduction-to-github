<template>
<div style="background:teal;padding:1px">
  <div style="text-align:center;font-size:20px;color:white" v-show="activeGames.length===1">
    <q-field> {{ tournament.start_at }} {{ tournament.game }} </q-field>
    <q-field> at {{ tournament.courseName }} </q-field>
  </div>
  <dialog-for-select v-if="activeGames.length > 1" :selections="activeGames" @user-selected="userSelected($event)" style="text-align:left;font-size:18px;color:white"/>
  <q-btn label="Blue Team Score: " size="lg" dense no-caps no-wrap align="left" color="blue" style="width:180px;padding-left:5px" > {{ blueTeamScore }} </q-btn>
  <q-btn label="Red Team Score: "  size="lg" dense no-caps no-wrap align="left" color="red"  style="width:180px;padding-left:5px" > {{ redTeamScore }} </q-btn>
  <div v-for="(og, i) in groups" :key=og.x :style="{ background: getBackground(i, og.players.length) }">
    <div>Group {{ i+1 }} start at {{ og.teetime }} </div>
    <table>
    <tr v-for="(p, oidx) in og.players" :key=p.x>
      <td><q-btn :icon="getGenderIcon(p.gender)" :label="p.fullname" size="lg" dense no-caps no-wrap align="left" :color="teamColor(oidx)" style="width:180px;padding-left:2px" />
      </td>
      <td>
        <q-btn-dropdown v-if="doGroup||isAdmin" :color="teamColor(oidx)" dense size="lg">
          <div style="background: teal">
            <table style="padding:4px">
              <tr v-for="mx in matrix" :key=mx.id>
                <td v-for="i in mx" :key=i.id>
                  <q-btn v-if="i >= 0" round class="pos-sel" :color="getColor(i)" text-color="black" @click="regroup(p, oidx, og, groups[i])" :icon="getIcon(i)">{{i +1}}</q-btn>
                </td>
              </tr>
            </table>
          </div>
        </q-btn-dropdown>
      </td>
      <td> <q-btn v-if="oidx==0||oidx==3" round :color="oidx==0?'blue':'red'" size="md" @click="openNumPad(og.idx, oidx)">{{ score[teamName(og.idx, oidx)] }}</q-btn> </td>
      <td v-if="oidx===0" rowspan="3" style="padding-left:5px;font-size:24px" v-html="og.note===null ? og.name : og.note" />
    </tr>
    </table>
  </div>
  <div v-for="(p, i) in tplayers" :key=p.x>
    <q-tr>
      <q-td>
        <q-chip :avatar="getAvatar(p.gender)" square style="width:20px" />
        <q-btn color="teal-10" size="lg" dense align="left" :label="p.fullname" style="width:220px" />
        <q-btn v-if="doGroup||isAdmin" @click="pickIt(p, i)" color="teal-10" round icon="add_circle" style="margin-left:-15px" />
      </q-td>
    </q-tr>
  </div>
  <q-toolbar-title v-if="(doGroup || isAdmin) && (activeGames.length===1 || tmntId>0)">
    <q-btn label="reset" style="float:left"  icon="repeat" size="md" push color="primary" @click="reset" />
    <q-btn label="save"  style="float:right" icon="save"   size="md" push color="primary" @click="checkAndSave" />
  </q-toolbar-title>
  <num-pad ref="numPad" @set-score="setTeamScore" />
</div>
</template>
<script setup>
import dialogForSelect from 'src/components/CardSelection'
import { libFunctions } from 'src/composables/libFunctions'
const { $q } = libFunctions()
import numPad from 'pages/OrderedNumPad'
const score = {}
const tplayers = []
const tournament = {}
// const numPlayers = -1 // calc in myLibs.js
// const numGroups = -1 // calc in myLibs.js
const groups = [] // calc in myLibs.js
const matrix = [] // calc in myLibs.js
const activeGames = []
const tmntId = -1


// function getIdx (gidx, oidx) {
//   if (gidx === 1 && oidx === 0) return 1
//   else if (gidx === 1 && oidx === 1) return 2
//   else if (gidx === 1 && oidx === 2) return 3
//   else if (gidx === 1 && oidx === 3) return 4
//   else if (gidx === 2 && oidx === 0) return 5
//   else if (gidx === 2 && oidx === 1) return 6
//   else if (gidx === 2 && oidx === 2) return 7
//   else if (gidx === 2 && oidx === 3) return 8
//   else if (gidx === 3 && oidx === 0) return 9
//   else if (gidx === 3 && oidx === 1) return 10
//   else if (gidx === 3 && oidx === 2) return 11
//   else if (gidx === 3 && oidx === 3) return 12
//   else if (gidx === 4 && oidx === 0) return 13
//   else if (gidx === 4 && oidx === 1) return 14
//   else if (gidx === 4 && oidx === 2) return 15
//   else if (gidx === 4 && oidx === 3) return 16
// }
function teamName (gidx, oidx) { return 'h' + this.getIdx(gidx, oidx) }
function openNumPad (gidx, oidx) {
  const tname = this.teamName(gidx, oidx)
  console.log('team name', tname)
  this.score[tname] = 0
  this.$refs.numPad.openIt(this.getIdx(gidx, oidx), this.score)
  console.log('the score:', this.score)
}
function setTeamScore (idx, score) {
  console.log('team score', idx, score)
  // score.h1 = score
  // score['h' + idx] = score
  // return score
}
function teamColor (oidx) {
  return oidx % 2 === 0 ? 'blue' : 'red'
}
function reset () {
  this.groups.forEach(g => {
    g.players.forEach(p => { p.captain = null; this.tplayers.push(p) })
    g.players = []
  })
}
// function loadTplayers () {
//   this.tplayers = []
//   this.groups = []
//   const args = { vm: this }
//   args.path = process.env.API + '/golf/getTplayers/' + this.tmntId
//   args.target = 'golf.getTplayers'
//   axiosLoad(args)
// }
function userSelected (tmnt) {
  console.log(' === userSelected(tid) for tmnt Id', tmnt.id, this.activeGames)
  this.tmntId = tmnt.id
  this.loadTplayers()
}
// function isAllCaptainAssignedXXX () {
//   for (let i = 0; i < this.numGroups; i++) {
//     console.log('g captain', this.groups[i].players[0].captain)
//     if (this.groups[i].players[0].captain !== 'Y') {
//       this.groups[i].note = 'there is no captain for this group'
//       const p = this.groups[i].players.pop() // to trigger to re-rendering to show the note
//       this.groups[i].players.push(p)
//       // console.log('g note', this.groups[i])
//       this.getBackground(i, this.groups[i].players.length)
//       return false
//     }
//   }
//   return true
// }
// function saveToDB () {
//   console.log(' == tplayers not saved yet', this.tplayers)
//   let gplayers = []
//   for (let i = 0; i < this.numGroups; i++) {
//     if (this.groups[i].players[0] !== undefined) this.groups[i].players[0].captain = 'A1'
//     if (this.groups[i].players[1] !== undefined) this.groups[i].players[1].captain = 'A2'
//     if (this.groups[i].players[2] !== undefined) this.groups[i].players[2].captain = 'B1'
//     if (this.groups[i].players[3] !== undefined) this.groups[i].players[3].captain = 'B2'
//     if (this.groups[i].players[0] !== undefined) this.groups[i].players[0].grp = i + 1
//     if (this.groups[i].players[1] !== undefined) this.groups[i].players[1].grp = i + 1
//     if (this.groups[i].players[2] !== undefined) this.groups[i].players[2].grp = i + 1
//     if (this.groups[i].players[3] !== undefined) this.groups[i].players[3].grp = i + 1
//     gplayers = gplayers.concat(this.groups[i].players)
//   }
//   console.log(' == saving grouped players', gplayers, 'G1 player', this.groups[0].players, 'gplayers:', gplayers)
//   const args = { vm: this }
//   args.path = process.env.API + '/golf/saveGrouping'
//   args.target = 'golf.saveGrouping'
//   args.inData = gplayers
//   axiosPost(args)
// }
function checkAndSave () {
  if (this.tplayers.length > 0) {
    console.log('There are some players not been grouped yet', this.tplayers)
    this.$q.dialog({
      title: 'Confirm',
      message: 'there are players not being grouped yet, you can save the current and do the rest later',
      preventClose: true,
      ok: 'Agree',
      cancel: 'Disagree'
    }).onOk(() => {
      this.$q.notify('Agreed!')
      this.saveToDB()
    }).onCancel(() => {
      this.$q.notify('Disagreed and continue group ...')
      this.saveToDB()
    })
  } else {
    this.saveToDB()
  }
}
// function getLabel (p) {
//   return p.captain === undefined || p.captain === null ? p.fullname : p.fullname + '(C)'
// }
function getGenderIcon (gender) {
  return gender === 'M' ? 'nature_people' : 'nature'
}
function getIcon (groupIdx) {
  // return groupIdx <= this.groups.length ? 'G' : 'C'
  return groupIdx >= 0 ? 'G' : 'C'
}
function getColor (groupIdx) {
  // return groupIdx <= this.groups.length ? 'yellow-8' : 'red'
  return groupIdx >= 0 ? 'yellow-8' : 'red'
}
function getBackground (i, playersInGroup) {
  // let parr = this.groups[i].players.filter(p => p.captain === 'Y')
  // let hasNoCaptain = false
  // if (parr.length === 0) hasNoCaptain = true
  if (playersInGroup > 4) {
    this.groups[i].note = 'There are more than 4 players in the group'
    return 'red'
  } else if (i % 2 === 0 && playersInGroup === 4) {
    return 'lightgrey'
  } else if (playersInGroup < 4) {
    return 'yellow'
  // } else if (playersInGroup === 4 && hasNoCaptain) {
  //   this.groups[i].note = 'there is no captain for this group'
  //   return 'brown'
  // } else if (this.groups[i].note !== null) {
  //   return 'brown'
  } else {
    return 'lightblue'
  }
}
function regroup (p, oIdx, og, ng) {
  console.log(' == regroup', p.fullname, og.idx, oIdx, ng.idx)
  // console.log(' == regroup', p)
  // if (ng === undefined) {
  //   og.note = null
  //   og.players[0].captain = 'A1'
  //   og.players[1].captain = 'A2'
  //   og.players[2].captain = 'B1'
  //   og.players[3].captain = 'B2'
  //   p.players[0].captain = 'A1'
  //   p.players[1].captain = 'A2'
  //   p.players[2].captain = 'B1'
  //   p.players[3].captain = 'B2'
  //   console.log(' -x- p.captain', p.captain, og.captain, p.fullname)
  //   og.players.splice(oIdx, 1)
  //   og.players.unshift(p)
  //   console.log(' --- regroup, players', og.players)
  //   return
  // }
  // og.players[0].captain = 'A1'
  // og.players[1].captain = 'A2'
  // og.players[2].captain = 'B1'
  // og.players[3].captain = 'B2'
  p.grp = ng.idx
  ng.players.push(p)
  og.players.splice(oIdx, 1)
  if (og.players.length <= 4) og.note = null
}
function pickIt (p, i) {
  this.tplayers.splice(i, 1)
  for (let k = 0; k < this.numGroups; k++) {
    if (this.groups[k].players.length < 4) {
      if (this.groups[k].players.length === 0) p.captain = 'Y'
      this.groups[k].players.push(p)
      // p.group = k + 1
      p.grp = k + 1
      return
    }
  }
}
function getAvatar (gender) {
  return gender === 'M' ? 'statics/boy2.png' : 'statics/girl.png'
}
// score = { h1: 0, h2: 0, h3: 0, h4: 0, h5: 0, h6: 0, h7: 0, h8: 0, h9: 0, h10: 0, h11: 0, h12: 0, h13: 0, h14: 0, h15: 0, h16: 0, h17: 0, h18: 0 }
// getUnexpiredTournaments (this, 'ForTeamCompetition')

function blueTeamScore () {
  let score = 0
  for (let i = 1; i < 18; i++) {
    if (i % 2 === 1) score += this.score['h' + i]
  }
  return score
}
function redTeamScore () {
  let score = 0
  for (let i = 1; i < 18; i++) {
    if (i % 2 === 0) score += this.score['h' + i]
  }
  return score
}
// function tlist () { return this.tplayers }
function isAdmin () { return $q.sessionStorage.getItem('user') === 'gadmin' }
function doGroup () { return $q.sessionStorage.getItem('user') === 'doGroup' }
// function ggroups () { return this.groups }
</script>
<style>
q-icon.transition-generic.material-icons {
  float:right;
}
</style>

<template>
  <q-dialog v-model="opened">
    <div style="height:100x" class="bg-cyan-10 q-pa-sm">
      <tr><td colspan="5" class="text-white text-h6 text-center">Add ({{ player.name }}) to </td></tr>
      <tr><q-chip class="text-h6 text-black bg-amber q-pa-lg">GROUP</q-chip>
        <td v-for="(grp) in unfinishedGroup" :key="grp">
          <q-btn round class="text-h6" :color="getGrpColor(grp)" text-color="black" @click="setGroup(grp-1)">{{grp}}</q-btn>
        </td>
      </tr>
      <tr v-if="teamA.length>0"><q-chip class="text-h6 text-white bg-blue q-pa-lg">TeamA </q-chip>
        <td v-for="t in teamA" :key=t.x>
          <q-btn v-if="t>0" round class="text-h6" color="blue" text-color="black" @click="setPlayerA(t)">{{ t }}</q-btn>
          <q-btn v-else disable round class="text-h6" color="blue" text-color="black" />
        </td>
      </tr>
      <tr v-if="teamB.length>0"><q-chip class="text-h6 text-white bg-red q-pa-lg">TeamB </q-chip>
        <td v-for="t in teamB" :key=t.x>
          <q-btn v-if="t>0" round class="text-h6" color="red" text-color="black" @click="setPlayerB(t)">{{ t }}</q-btn>
          <q-btn v-else disable round class="text-h6" color="red" text-color="black" />
        </td>
      </tr>
    </div>
  </q-dialog>
</template>
<script>
export default {
  data () {
    return {
      teamA: [],
      teamB: [],
      tmnts: null,
      grp: null,
      groups: [],
      grouped: [],
      notGrouped: [],
      player: null,
      opened: false
    }
  },
  created() { console.info('-cr-TeamMatchSetup') },
  computed: {
    unfinishedGroup() {
      // console.info('-cp-Groups', this.groups)
      return this.groups.filter(g => g.players.length < 4).map(g => g.idx)
    }
  },
  methods: {
    openIt(idx, groups, player, notGrouped) {
      console.info('-cr-TeamMatchSetup.openIt', groups, player)
      this.idx = idx
      this.notGrouped = notGrouped
      this.groups = groups
      this.player = player
      this.opened = true
    },
    getGrpColor(groupi) { return groupi - 1 === this.grp ? 'yellow-1' : 'yellow-9' },
    getIcon(i) { return 'G' },
    setPlayerA(i) {
      const thegrp = this.groups[this.grp]
      console.info('-fn-setPlayerA thegrp', thegrp)
      this.player.grp = this.grp + 1
      this.player.team = 'A' + i
      this.player.tmntId = thegrp.tmntId
      thegrp.players.push(this.player)
      this.notGrouped.splice(this.idx, 1)
      console.info('-fn-setPlayerA groups', this.groups)
      this.$emit('upd-team-match-tplayer', this.player)
      this.teamA = []
      this.teamB = []
      this.opened = false
    },
    setPlayerB(i) {
      const thegrp = this.groups[this.grp]
      this.player.grp = this.grp + 1
      this.player.team = 'B' + i
      this.player.tmntId = thegrp.tmntId
      thegrp.players.push(this.player)
      this.notGrouped.splice(this.idx, 1)
      this.grouped.push(this.player)
      this.$emit('upd-team-match-tplayer', this.player)
      this.teamA = []
      this.teamB = []
      console.info('-fn-setPlayerB groups', this.groups)
      this.opened = false
    },
    setGroup(i) {
      console.info(`-setGroup(${i})`)
      this.grp = i
      if (this.groups[i].players.length === 0) {
        this.teamA = [1, 2]
        this.teamB = [1, 2]
      } else {
        const teams = this.groups[i].players.map(p => p.team).join('')
        console.info(`-setGroup(${i})-teams=${teams}`)
        console.info(`-setGroup-teamA=${this.teamA}, teamB=${this.teamB}`)
        this.teamA = /A1/.test(teams) && /A2/.test(teams) ? [0, 0] : /A1/.test(teams) ? [0, 2] : /A2/.test(teams) ? [1, 0] : [1, 2]
        this.teamB = /B1/.test(teams) && /B2/.test(teams) ? [0, 0] : /B1/.test(teams) ? [0, 2] : /B2/.test(teams) ? [1, 0] : [1, 2]
      }
    },
  }
}
</script>

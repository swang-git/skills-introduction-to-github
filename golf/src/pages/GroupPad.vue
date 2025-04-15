<template>
  <div>
    <q-dialog v-model="opened" minimized :style="{ position:'absolute', top: topPos }">
      <div class="bg-teal">
        <q-toolbar class="bg-teal-9">
          <div style="margin-left:-10px"><q-btn color="amber-10" glossy size="lg" dense icon="chevron_left" round v-close-popup /></div>
          <q-toolbar-title class="text-h6 text-white">Add Players to Group</q-toolbar-title>
        </q-toolbar>
        <div class="row">
          <div v-for="(g, i) in groups" :key=g.id>
            <q-btn v-if="(players.length==4 && g.players.length==0) || (players.length==1 && g.players.length<4)" color="red" round glossy size="lg" @click="doAction(i)">{{i+1}}</q-btn>
            <q-btn v-else-if="players.length==4 && g.players.length<4" color="grey" disable icon="有" class="q-pb-sm" round glossy size="lg">{{g.players.length}}</q-btn>
            <q-btn v-else color="purple" disable icon="满" class="q-pb-sm" round glossy size="lg">{{i+1}}</q-btn>
          </div>
        </div>
      </div>
    </q-dialog>
  </div>
</template>
<script>
// import libs from '../mixins/libs'
export default {
  // mixins: [libs],
  data () {
    return {
      groups: [],
      players: [],
      // tmntId: -1,
      topPos: '10px',
      opened: false
    }
  },
  created() {
    console.info('-cr-GroupPad')
  },
  computed: {
    // compPadNum () { return this.padNum }
  },
  methods: {
    doAction(gi) {
      this.$emit('add-grouped-tplayers', gi)
      this.opened = false
    },
    openIt(groups, players) {
      console.info('-fn-groups.openIt', players, groups)
      this.groups = groups
      // this.tmntId = tmntId
      this.players = players
      this.opened = true
    }
  }
}
</script>
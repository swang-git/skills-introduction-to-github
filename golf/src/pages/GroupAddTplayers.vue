<template>
  <q-dialog v-model="opened" persistent :maximized="isIM" :full-height="isIM">
    <q-layout container :style="{ height:isDesk ? '590px' : '500px', width:'344px' }" class="bg-teal-10">
      <q-header class="bg-teal-10 q-pa-xs">
        <q-toolbar>
          <q-btn round glossy icon='keyboard_arrow_left' v-close-popup color="orange" style="margin-left:-10px" />
          <q-btn round outline :label="compPlayers.length-selected.length" class="q-ml-sm">
            <q-tooltip class="text-h6 text-grey-7 bg-yellow-4">Number of players to be added</q-tooltip>
          </q-btn>
          <q-btn round outline :label="selected.length" class="q-ml-sm">
            <q-tooltip class="text-h6 text-grey-7 bg-yellow-4">Number of Players Selected</q-tooltip>
          </q-btn>
          <div class="q-pl-xl">
            <q-input dark borderless v-model="searchTxt" input-class="text-right text-h6" dense :label="players.length + ' Players'">
              <template v-slot:append>
                <q-icon v-if="searchTxt === ''" name="search" size="md" />
                <q-icon v-else name="clear" size="md" class="cursor-pointer" @click="searchTxt=''" />
              </template>
            </q-input>
          </div>
        </q-toolbar>
      </q-header>
      <q-footer class="bg-teal-10">
        <q-toolbar no-shadow>
          <q-toolbar-title align="center">
            <q-btn glossy color="amber-10" icon="add_circle" icon-right="add_circle" @click="addTplayers()" label="Add Selected to Tournament" />
          </q-toolbar-title>
        </q-toolbar>
      </q-footer>
      <div class="q-pa-sm bg-teal-9 text-body1 text-white" style="margin:50px 0 0 0">
        <div v-for="(p) in compPlayers" :key=p.x class="q-gutter-sm">
          <q-avatar size="33px"><img :src="getAvatar(p)"></q-avatar>
          <q-checkbox v-model="selected" :val="p" :label="p.fullname" color="orange" style="margin:8px 0 0 -6px" />
        </div>
      </div>
    </q-layout>
  </q-dialog>
</template>
<script>
import { ref } from 'vue'
import libs from '../mixins/libs'
export default {
  mixins: [libs],
  setup () {
    return {
      // players: ref([ { player_id:1, gender:'F', fullname:'Wang, Shengli' }, { player_id:2, gender:'M', fullname:'Wang, Henry' } ]),
      selected: ref([]),
      title: ref('Add Tournament Players'),
      searchTxt: ref(''),
      gameId: ref(0),
      tmntId: ref(0),
      year: ref(0),
      tmnt: ref({}),
      players: ref([]),
      tplayers: ref([]),
      opened: ref(false)
    }
  },
  created() {
    console.info('-cr-GroupAddTplayer')
    // this.getPlayers()
  },
  methods: {
    addTplayers() {
      console.info('-fn-addTplayers', this.tmnt)
      const args = { vm: this.$parent }
      let inData = { tmntId: this.tmntId, players:[] }
      this.selected.forEach(p => {
        let x = {}
        x.tournament_id = this.tmnt.id
        x.game_id = this.tmnt.game_id
        x.player = p.fullname
        x.player_id = p.player_id
        // x.year = this.gameDate.substring(0, 4)
        x.year = this.tmnt.year
        inData.players.push(x)
      })
      args.inData = inData
      args.path = process.env.API + '/golf/addTplayers'
      args.target = 'golf.getTplayers'
      this.axiosPost(args)
      this.opened = false
    },
    setNonTplayers() {
      const rmlist = this.tplayers.map(p => { return p.playerId})
      this.players = this.players.filter(p => !rmlist.includes(p.player_id)).sort((a, b) => { return a.fullname < b.fullname ? -1 : 1 })
      console.info('-fn-setNonTplayers', this.players[0])
    },
    openIt(tmnt, tplayers) {
      this.tplayers = tplayers
      this.getPlayers()
      this.tmnt = tmnt
      this.tmntId = tmnt.id
      this.gameId = tmnt.game_id
      this.gameDate = tmnt.start_at
      this.opened = true
      console.info('-fn-GroupAddTplayers.openIt', tmnt, tplayers)
    }
  },
  computed: {
    compPlayers () {
      var filterKey = this.searchTxt.length>0 && this.searchTxt.toLowerCase()
      // console.info('-cp-players', this.players)
      var data = this.players
      if (filterKey.length>0) {
        var words = filterKey.split(' ')
        words.forEach(word => {
          data = data.filter(row => {
            return Object.keys(row).filter(key => { return !['player_id', 'gender'].includes(key) }).some(key => {
              return String(row[key]).toLowerCase().indexOf(word) >= 0
            })
          })
        })
      }
      return data
    }
  }
}
</script>

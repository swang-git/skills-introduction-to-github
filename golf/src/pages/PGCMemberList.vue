<template>
  <q-dialog v-model="opened" persistent :maximized="isIM" :full-height="isIM">
    <q-layout container :style="{ height:isDesk ? '590px' : '500px', width:'350px' }" class="bg-teal-10">
      <q-header style="margin-top:-5px">
        <q-toolbar class="fixed bg-primary z-top">
          <q-btn round glossy icon='chevron_left' v-close-popup color="amber-10" style="margin-left:-3px" />
          <!-- <q-btn round outline @click="toggleSelected" :label="compTplayers.length + selected.length"> -->
          <q-btn round outline :label="tplayers.length + selected.length">
            <q-tooltip class="text-h6 text-grey-7 bg-yellow-4">Number of Players for This Match</q-tooltip>
          </q-btn>
          <div class="col-7 q-pl-xl">
            <q-input dark borderless v-model="searchTxt" input-class="text-right text-h6" dense :label="compAllPlayers.length + ' Players'">
              <template v-slot:append>
                <q-icon v-if="searchTxt === ''" name="search" size="md" />
                <q-icon v-else name="clear" size="md" class="cursor-pointer" @click="searchTxt=''" />
              </template>
            </q-input>
          </div>
          <q-btn round glossy color="teal-9" icon="add_circle" @click="addUnsignedTeamMatchPlayers" class="q-ml-md" />
        </q-toolbar>
      </q-header>
      <div class="q-pa-sm bg-teal-10 text-body1 text-white" style="margin:5px 0 0 0">
        <div v-for="(p, i) in compAllNoneMatchPlayers" :key=p class="q-gutter-sm" :class="{'q-pt-xl':i===0}">
          <q-avatar size="33px"><img :src="getAvatar(p)"></q-avatar>
          <q-checkbox v-model="selected" :val="p" color="orange" style="margin:8px 0 0 -6px" @click="selectedPlayer()">{{ p.name }}</q-checkbox>
        </div>
      </div>
      <memberDialog ref="memberDialog" @added-new-player="addedNewPlayer" />
    </q-layout>
    <infoDialog ref="infoDialog" @add-selected-players="addUnsignedTeamMatchPlayers" />
    <confirmDialog ref="confirmDialog" @add-new-player="addNewPlayer" />
  </q-dialog>
</template>
<script>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'
import libs from '../mixins/libs'
import infoDialog from '../src/components/InfoDialog'
import confirmDialog from '../src/components/ConfirmDialog'
import memberDialog from '../components/MemberDialog'
export default {
  props: {
    tplayers: { type: Array },
    openSlots:{ type: Number },
  },
  mixins: [libs],
  components: { 
    infoDialog,
    confirmDialog,
    memberDialog
  },
  setup () {
    return {
      selected: ref([]),
      // tplayers: ref([]),
      // toBeSelectedPlayers: ref([]),
      allPlayers: ref([]),
      // slotFilled: ref(false),
      // newPlayer: ref({}),
      // title: ref(''),
      // toggle: ref(true),
      searchTxt: ref(''),
      gameId: ref(0),
      tmntId: ref(0),
      // openSlots: ref(0),
      opened: ref(false),
      caller: ref(null),
      gamefee: ref(0),
      // isSearch: ref(false)
    }
  },
  methods: {
    addedNewPlayer(p) {
      console.info('-fn-added NewPlayer', p)
      const x = {}
      x.name = p.lastname + ', ' + p.firstname
      x.player_id = p.id
      this.selected.push(x)
      this.addUnsignedTeamMatchPlayers()
      
    },
    selectedPlayer() {
      console.info('-fn-selectedPlayer', this.selected)
      // console.table(this.selected[0])
      if (this.openSlots === this.selected.length) {
        // this.slotFilled = true
        const tit = 'All Slots Are Filled'
        const msg = 'AllSlotsAreFilled'
        this.$refs.infoDialog.openIt(tit, msg)
      } else if (this.openSlots > this.selected.length) {
        // this.slotFilled = false
      }
    },
    XXsearchPlayer() {
      console.info('-fn-searchPlayer', this.searchTxt, this.toBeSelectedPlayers)
      const found = this.toBeSelectedPlayers.filter(p => p.name === this.searchTxt)
      console.info('-fn-searchPlayer', found)
      this.players = found.concat(this.players)
      this.searchTxt = ''
    },
    addSelectedPlayers(i, p) {
      console.info('-fn-addSelectedPlayers', i, p)
    },
    XXtoggleSelected() {
      if (this.selected.length === 0) return
      if (this.toggle) this.players = this.selected
      else this.players = this.dats
      this.toggle = !this.toggle
    },
    addNewPlayer() {
      console.info('-fn-addNewPlayer')
      const newPlayer = {
        firstname: this.newPlayer.firstname,
        lastname: 'Guest',
        gender: 'M',
        email: null,
        phone: null,
        chname: null,
        nkname: null,
        fees: 0.00,
        type: 'G' // guest
      }
      this.$refs.memberDialog.openIt('create', newPlayer)
      // this.opened = false
    },
    addUnsignedTeamMatchPlayers() {
      console.info('-fn-addUnsignedTeamMatchPlayer', this.searchTxt, this.selected.length)
      if (this.selected.length === 0) {
        this.newPlayer.firstname = this.searchTxt[0].toUpperCase() + this.searchTxt.slice(1)
        if (this.isAdmin) {
          const tit = 'Add New Player'
          const msg = 'Add "Guest, ' + this.newPlayer.firstname + '" as new player ?'
          this.$refs.confirmDialog.openIt(tit, msg)
        } else {
          this.$refs.infoDialog.openIt('No Such Player named "' + this.newPlayer.firstname + '"', 'To add New Player, please contact 胜利')
        }
        return
      }
      const args = { vm: this.caller }
      const inData = []
      this.selected.forEach(p => {
        let x = {}
        x.tournament_id = this.tmntId
        x.game_id = this.gameId
        x.name = p.name
        x.matchDate = this.matchDate
        x.player_id = p.player_id
        x.year = this.year
        inData.push(x)
      })
      args.inData = inData
      args.path = process.env.API + '/golf/addUnsignedTeamMatchPlayers'
      args.target = 'golf.getTeamMatchPlayers'
      if (this.gameId < 10)  args.target = 'golf.getPGCGamePlayers'
      this.axiosPost(args)
      this.opened = false
      // this.toBeSelectedPlayers = this.toBeSelectedPlayers.filter(p => this.selected.map(x => x.player_id).indexOf(p.player_id) < 0) // remove added players from playersList
      // this.openSlots -= this.selected.length
      this.selected = []
      // this.$emit('upd-open-slots', this.openSlots)
    },
    openIt(caller, matchDate, gameId, tmntId, gamefee) {
      this.matchDate = matchDate
      this.year = matchDate.substring(0, 4)
      this.gameId = gameId
      this.tmntId = tmntId
      this.gamefee = gamefee
      console.info(`-fn-MemberList.openIt year: ${matchDate} ${gameId} ${tmntId}`)
      this.selected = []
      // if (this.toBeSelectedPlayers.length>0) {
      if (this.allPlayers.length > 0) {
        this.opened = true
        return
      }
      this.caller = caller
      // this.openSlots = caller.openSlots
      this.getPGCMemberList()
      this.opened = true
    },
    getPGCMemberList () {
      const path = process.env.API + '/golf/getPGCMemberList/' + this.year
      this.gaxios(path)
    },
    setPGCMemberList (da) {
      console.log('setPGCMemberList', da.lst)
      da.lst.forEach(p => {
        const gamefee = p.type === 'H' ? 0 : this.gamefee
        // const x = {name:p.lastname + ', ' + p.firstname + ' (' + p.type + ')', gender:p.gender, player_id:p.player_id, type:p.type, gamefee:gamefee}
        const x = {name:p.lastname + ', ' + p.firstname, gender:p.gender, player_id:p.player_id, type:p.type, gamefee:gamefee}
        this.allPlayers.push(x)
      })
      // const lst = this.allPlayers.map(p => { p.lastname + ', ' + p.firstname })
      // console.log('lst', lst)

    },
    axiosBack(target, da) {
      if (target === 'golf.getCandidatePlayers') {  // this is getting all players from players table with status = 'A'
        console.info('-ab-getCandidatePlayers', da.lst)
        const playersH = da.lst.filter(p => p.tgameId != null && p.tgameId.indexOf(this.gameId) >= 0) // top of the list
        const playersL = da.lst.filter(p => p.tgameId == null || p.tgameId == 'Money' || p.tgameId == 'Drug' || p.tgameId.indexOf(this.gameId) < 0)
        this.allPlayers = playersH.concat(playersL)
        // this.setToBeSelectedPlayers()
      }
    },
    XXsetToBeSelectedPlayers() {
      const tp = this.tplayers.map(p => p.player_id)
      this.toBeSelectedPlayers = this.allPlayers.filter(p => tp.indexOf(p.player_id)<0)
    },
    // XXupdTplayers(openSlots, tplayers) {
    //   this.tplayers = tplayers
    //   this.openSlots = openSlots
    //   this.setToBeSelectedPlayers()
    //   // console.log('-fn-updTplayers # of tplayers', this.tplayers.length)
    //   console.table(this.tplayers.map(p => p.game_id + ' ~ ' + p.tmntId + '~' + p.player_id + ' ~ ' + p.name))
    // }
  },
  created() {
    console.log('-cr-PGCMemberList')
    // emitter.on('slot-filled', (x, y) => { this.slotFilled = x; this.openSlots = y; console.log('-ck-openSlots', this.openSlots) })
    // emitter.on('upd-open-slots', (x) => { this.openSlots = x; console.log('-ck-openSlots', this.openSlots) })
    // emitter.on('upd-tplayers', (o, t) => { this.updTplayers(o, t) })
    // emitter.on('upd-tplayers', (o, t) => { this.updTplayers(o, t) })
    emitter.on('golf-getPGCMemberList', (x) => { this.setPGCMemberList(x) })
  },
  computed: {
    compAllPlayers() { return this.allPlayers },
    compAllNoneMatchPlayers() { // all non tplayer, that is all none match plaerys, that is players to be selected to the match
      let filterKey = this.searchTxt.length>0 && this.searchTxt.toLowerCase()
      // let data = this.toBeSelectedPlayers
      // const noneMatchPlayers = this.allPlayers.filter(p => !this.tplayers.map(x => x.player_id).includes(p.player_id))
      const noneMatchPlayers = this.allPlayers.filter(p => this.tplayers.map(x => x.player_id).indexOf(p.player_id)<0)
      console.info('-cp-noneMatchPlayers', noneMatchPlayers)
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
      // if (data.length === 0) {
      //   console.info('-cm-compAllNoneMatchPlayers', data.length, data)
      //   data = this.toBeSelectedPlayers.filter(p => !this.compTplayers.map(x => x.player_id).includes(p.player_id))
      //   if (filterKey.length>0) {
      //     let words = filterKey.split(' ')
      //     words.forEach(word => {
      //       data = data.filter(row => {
      //         return Object.keys(row).filter(key => { return !['player_id', 'gender'].includes(key) }).some(key => {
      //           return String(row[key]).toLowerCase().indexOf(word) >= 0
      //         })
      //       })
      //     })
      //   }
      // }
      return data
    }
  }
}
</script>

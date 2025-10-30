<template>
  <q-dialog v-model="opened" persistent :maximized="isIM" :full-height="isIM">
    <q-layout container :style="{ height:isDesk ? '590px' : '500px', width:'350px' }" class="bg-teal-10">
      <q-header class="bg-teal-10 q-pa-xs">
        <q-toolbar>
          <q-btn round glossy icon='keyboard_arrow_left' v-close-popup color="orange" style="margin-left:-10px" />
          <q-btn round outline :label="selected.length" class="q-ml-sm">
            <q-tooltip class="text-h6 text-grey-7 bg-yellow-4">Number of Players Selected</q-tooltip>
          </q-btn>
          <div class="col-7 q-pl-xl">
            <!-- <q-input dark borderless v-model="searchTxt" input-class="text-right text-h6" dense :label="tobeGrouped.length + ' Players'"> -->
            <q-input dark borderless v-model="searchTxt" input-class="text-right text-h6" dense label="Add 4 Players to Tmnt">
              <template v-slot:append>
                <q-icon v-if="searchTxt === ''" name="search" size="md" />
                <q-icon v-else name="clear" size="md" class="cursor-pointer" @click="searchTxt=''" />
              </template>
            </q-input>
          </div>
          <!-- <q-btn round glossy color="teal-9" icon="add_circle" @click="addTplayers" class="q-ml-md" /> -->
        </q-toolbar>
      </q-header>
      <q-footer class="bg-teal-10 q-pa-xs" v-show="comPlayers.length===0">
        <q-toolbar>
          <div class="col-10 q-pl-xs">
            <q-input dark borderless v-model="searchTxt" input-class="text-right text-h6" dense label="Search by Firstname+Lastname">
              <template v-slot:append>
                <q-icon v-if="searchTxt != ''" name="clear" size="md" class="cursor-pointer" @click="searchTxt=''" />
              </template>
            </q-input>
          </div>
          <q-btn round glossy color="teal-9" icon="search" @click="searchPlayer()" class="q-ml-md" />
        </q-toolbar>
      </q-footer>
      <div class="q-pa-sm bg-teal-9 text-body1 text-white" style="margin:50px 0 0 0">
        <div v-for="(p) in comPlayers" :key=p.x class="q-gutter-sm">
          <q-avatar size="33px"><img :src="getAvatar(p)"></q-avatar>
          <q-checkbox v-model="selected" :val="p" :label="p.fullname" color="orange" style="margin:8px 0 0 -6px" @click="selPlayer(p)" />
        </div>
      </div>
    </q-layout>
  </q-dialog>
</template>
<script>
import { ref } from 'vue'
import libs from '../mixins/libs'
import emitter from 'tiny-emitter/instance'
export default {
  mixins: [libs],
  setup () {
    return {
      tobeGrouped: ref([
          { player_id:1, gender:'F', player:'Wang, Shengli' },
          { player_id:2, gender:'M', player:'Wang, Henry' },
        ]),
      selected: ref([]),
      dats: ref([]),
      // selectedPlayers: ref([]),
      // title: ref(''),
      // showSearchByFirstLastName: ref(false),
      // toggle: ref(true),
      searchTxt: ref(''),
      // firstLastName: ref(''),
      gameId: ref(0),
      tmntId: ref(0),
      task: ref(null),
      // tplayers: ref([]),
      opened: ref(false)
    }
  },
  created() {
    console.info('-cr-GroupAdd')
  },
  methods: {
    selPlayer(p) {
      console.info('-selPlayer-', this.selected)
      if (this.selected.length === 4) {
        this.selected[0].captain = 1
        this.$emit('set-players-to-group', this.selected)
        this.selected = []
        this.opened = false
      }
    },
    openIt(tmnt, tobeGrouped) {
      this.tmnt = tmnt
      this.tmntId = tmnt.id
      this.gameId = tmnt.game_id
      this.dats = tobeGrouped
      this.gameDate = tmnt.start_at
      this.tobeGrouped = tobeGrouped.sort((a,b) => a.fullname < b.fullname ? -1:1)
      this.selected = []
      this.opened = true
      console.info('-fn-GroupAdd.openIt', tmnt, tobeGrouped, this.comPlayers)
    }
  },
  computed: {
    comPlayers () {
      var filterKey = this.searchTxt.length>0 && this.searchTxt.toLowerCase()
      var data = this.tobeGrouped
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

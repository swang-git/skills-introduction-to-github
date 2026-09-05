<template>
<div class="q-pa-md q-gutter-sm" >
  <q-dialog v-model="opened" persistent transition-show="rotate" transition-hide="flip-up">
    <q-layout container class="bg-teal-10" style="height: 600px; width:300px">
      <LayoutHeader act="signup" @do-action="signup"/>
    <q-card class="bg-teal-10 text-cyan-1">
      <!-- <q-separator /> -->
      <div class="q-pa-xs">
        <q-input class="bg-teal-10" standout bottom-slots v-model="searchQuery" label="Search Players by Name" dense dark>
          <template v-slot:prepend>
            <q-icon name="search" color="white" />
          </template>
          <template v-slot:append>
            <q-icon name="close" @click="searchQuery=null" class="cursor-pointer" />
          </template>
        </q-input>
      </div>

      <q-card-section style="max-height:13vh;margin:-22px 65px 0 30px;border:1px cyan solid" class="bg-cyan-10">
        <div><q-radio v-model="activity" val="both" label="Golf & Dinner" keep-color color="red" class="text-h6" /></div>
        <div><q-radio v-model="activity" val="golf" label="Golf Only" keep-color color="cyan" class="text-h6" /></div>
        <div><q-radio v-model="activity" val="dinn" label="Dinner Only" keep-color color="lime" class="text-h6" /></div>
      </q-card-section>
     
      <q-card-section style="max-height:50vh" class="scroll">
        <div class="scroll" v-for="o in options" :key=o.value >
          <!-- <q-radio v-model="cspId" :val="o.value" :label="o.label" color="red" class="text-h6" @click="selectedOpt(o)" /> -->
          <q-radio v-model="cspId" :val="o.value" :label="o.label" keep-color color="cyan" class="text-h6" />
        </div>
      </q-card-section>
      <q-separator />
    </q-card>
  </q-layout>
  </q-dialog>
</div>
</template>
<script setup>
import { ref, computed } from 'vue'
import emitter from 'tiny-emitter/instance'
import { libFunctions } from '../composables/libFunctions'
import { axiosFunctions } from '../composables/axiosFunctions'
const { opened, ENV_API } = libFunctions()
const { paxios } = axiosFunctions()
import LayoutHeader from './LayoutHeader.vue'
const emit = defineEmits(['selected-option'])
const searchQuery = ref(null)
const cspId = ref(0)
const activity = ref('both')
const cspOptions = ref([])
const model = ref(null)
const iconName = ref(null)
const tmnt = ref({})
const grp = ref(-1)

console.log('-ST-SelectionDialog')
emitter.on('open-SelectionDialog', (x,y,z,t,g) => openIt(x,y,z,t,g))
defineExpose({ openIt })
const options = computed(() => {
  var filterKey = searchQuery.value && searchQuery.value.toLowerCase()
  var data = cspOptions.value
  if (filterKey) {
    var words = filterKey.split(' ')
    words.forEach(word => {
      data = data.filter(row => {
        return Object.keys(row).filter(key => { return !['id', 'catsId'].includes(key) }).some(key => {
          return String(row[key]).toLowerCase().indexOf(word) >= 0
        })
      })
    })
  }
  return data
})
function signup () {
  console.log(`-fn-signup cspId=${cspId.value}, activity=${activity.value} tmntId=${tmnt.value.id}`, tmnt.value)
  if (cspId.value <= 0) {
    const tit = 'Player Not Selected'
    const msg = 'Please Select Player'
    emitter.emit('open-InfoDisplay', tit, msg)
    return
  }
  const t = tmnt.value
  const opts = options.value
  const fname = opts.filter(p => p.value == cspId.value)[0].label
  console.log(`-CK-fname=`, fname)
  const newTplayer = {tournament_id:t.id, player_id:cspId.value, year:t.year, grp:grp.value, game_id:t.game_id, captain:null, name:fname, activity:activity.value }
  emit('new-tplayer', newTplayer)
  const path = ENV_API + '/golf/addTournamentPlayer'
  paxios(path, [newTplayer])
}
// function selectedOpt (opt) {
//   // console.log(`-CK-fn-selectedOpt for user selectedOpt model=${model.value}`, opt)
//   if (opt.value === -1) {
//     $q.notify({ message: model.value + '(Add New)' })
//   }
//   emitter.emit('selected-option', model.value, opt)
//   emit('selected-option', model.value, opt)
//   // if (model.value == 'Select Course') emitter.emit('selected-option', model.value, opt)
//   // else emit('selected-option', model.value, opt)
//   // let selectedModel = 'selected-' + model.value.replace(/ /g, '-')
//   // if (model.value == 'Select Course') emit('selected-course', model.value, opt)
//   opened.value = false
// }
function openIt (inm, md, opts, t, g) {
  console.log(`-CK-fn-openIt iname=${inm}, model=${md}`, opts)
  searchQuery.value = null
  cspOptions.value = opts
  cspOptions.value = opts
  model.value = md
  iconName.value = inm
  cspId.value = 0
  tmnt.value = t
  grp.value = g
  opened.value = true
}
</script>

<template>
<q-dialog v-model="opened" maximized>
<div style="border:1px solid cyan">
  <q-table :title="title" title-class="text-h5 q-pl-lg" class="sh-sticky-header-table"
    v-model:rows="selist" :columns="columns" dark dense bordered square
    row-key="index" :separator="separator" wrap-cells hide-pagination :rows-per-page-options="[0]"
    :visible-columns="isIM ? visibleColumnsFone : visibleColumnsDesk" hide-header hide-bottom
  >
    <template v-slot:header="props">
      <q-tr :props="props">
        <q-th v-for="(col, i) in props.cols" :key="col.name" :props="props" class="text-yellow">{{ col.label }}
        <q-tooltip v-if="i==0" class="bg-teal-10 text-h6 text-cyan-1" anchor="top middle" self="bottom middle" :offset="[10, 10]">game index</q-tooltip>
        <q-tooltip v-else-if="i==1" class="bg-teal-10 text-h6 text-cyan-1" anchor="top middle" self="bottom middle" :offset="[10, 10]">all games played</q-tooltip>
        <q-tooltip v-else-if="i==2" class="bg-teal-10 text-h6 text-cyan-1" anchor="top middle" self="bottom middle" :offset="[10, 10]">games played (current year)</q-tooltip>
        <q-tooltip v-else-if="i==3" class="bg-teal-10 text-h6 text-cyan-1" anchor="top middle" self="bottom middle" :offset="[10, 10]">average index ranking</q-tooltip>
        <q-tooltip v-else-if="i==4" class="bg-teal-10 text-h6 text-cyan-1" anchor="top middle" self="bottom middle" :offset="[10, 10]">weighted average index (current year)</q-tooltip>
        <q-tooltip v-else-if="i==5" class="bg-teal-10 text-h6 text-cyan-1" anchor="top middle" self="bottom middle" :offset="[10, 10]">average index (all games played)</q-tooltip>
        <q-tooltip v-else-if="i==6" class="bg-teal-10 text-h6 text-cyan-1" anchor="top middle" self="bottom middle" :offset="[10, 10]">gross score</q-tooltip>
        <q-tooltip v-else-if="i==7" class="bg-teal-10 text-h6 text-cyan-1" anchor="top middle" self="bottom middle" :offset="[10, 10]">average score (all games played)</q-tooltip>
        <q-tooltip v-else-if="i==8" class="bg-teal-10 text-h6 text-cyan-1" anchor="top middle" self="bottom middle" :offset="[10, 10]">net team win/loss(current year)</q-tooltip>
        <q-tooltip v-else-if="i==9" class="bg-teal-10 text-h6 text-cyan-1" anchor="top middle" self="bottom middle" :offset="[10, 10]">net pair win/loss(current year)</q-tooltip>
        </q-th>
      </q-tr>
    </template>

    <template v-slot:body="p">
      <q-tr :props="p" @click="expandRow(p)" class="cursor-pointer">
        <q-td v-for="col in p.cols" :key="col" :style="getStyle(col.name)" :class="getClass4Val(col.name, col.value)">{{ showVal(col) }}</q-td>
      </q-tr>

      <q-tr v-show="p.expand" :props="p">
        <q-td class="bg-teal-9" colspan="99%">
          <table style="width:99%;border:1px solid cyan">
            <q-tr>
              <q-td colspan="2">
                <q-card-actions align="evenly">
                  <div style="width:90%">
                    <div class="text-left">Game Data for {{ p.row.game_date }}</div>
                  </div>
                  <div style="width:10%">
                    <q-btn round glossy color="indigo-10" @click="closeDisplay(p)">
                      <q-icon name="关" style="margin:-10px 0 0 0" />
                    </q-btn>
                  </div>
                </q-card-actions>
              </q-td>
            </q-tr>
            <q-tr><q-td class="bg-teal-9 text-green-1 text-right text-no-wrap">Index Ranking</q-td><q-td :class="getClass4Exp(p.row.index)">{{ showExpVal(p.row.index) }}</q-td></q-tr>
            <q-tr><q-td class="bg-teal-9 text-green-1 text-right text-no-wrap">Average Index (This Year)</q-td><q-td :class="getClass4Exp(p.row.avg_idx)">{{ showExpVal(p.row.avg_idx) }}</q-td></q-tr>
            <q-tr><q-td class="bg-teal-9 text-green-1 text-right text-no-wrap">Average Index (All Games)</q-td><q-td :class="getClass4Exp(p.row.avg_idx_all_games)">{{ showExpVal(p.row.avg_idx_all_games) }}</q-td></q-tr>
            <q-tr><q-td class="bg-teal-9 text-green-1 text-right text-no-wrap">Gross Score (This Day)</q-td><q-td :class="getClass4Exp(p.row.gross_score)">{{ showExpVal(p.row.gross_score) }}</q-td></q-tr>
            <q-tr><q-td class="bg-teal-9 text-green-1 text-right text-no-wrap">Avgerage Score (All Games)</q-td><q-td :class="getClass4Exp(p.row.avg_score)">{{ showExpVal(p.row.avg_score) }}</q-td></q-tr>
            <q-tr><q-td class="bg-teal-9 text-green-1 text-right text-no-wrap">Games Played (This Year)</q-td><q-td :class="getClass4Exp(p.row.games_played)">{{ showExpVal(p.row.games_played) }}</q-td></q-tr>
            <q-tr><q-td class="bg-teal-9 text-green-1 text-right text-no-wrap">All Games Played</q-td><q-td :class="getClass4Exp(p.row.all_games_played)">{{ showExpVal(p.row.all_games_played) }}</q-td></q-tr>
            <q-tr><q-td class="bg-teal-9 text-green-1 text-right text-no-wrap">Net Tean Win/Loss</q-td><q-td :class="getClass4Exp(p.row.net_team_win_loss)">{{ showExpVal(p.row.net_team_win_loss) }}</q-td></q-tr>
            <q-tr><q-td class="bg-teal-9 text-green-1 text-right text-no-wrap">Net Pair Win/Loss</q-td><q-td :class="getClass4Exp(p.row.net_pair_win_loss)">{{ showExpVal(p.row.net_pair_win_loss) }}</q-td></q-tr>
          </table>
        </q-td>
      </q-tr>
    </template>
  </q-table>
</div>
</q-dialog>
</template>
<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'
import { libFunctions } from 'src/composables/libFunctions';
const { isDesk, isIM } = libFunctions()
// const checker = ref(null)
// const todayGL = ref(null)
// const totalGL = ref(null)
// const totalVL = ref(null)
// const numSecs = ref(null)
// const ppdiff = ref(null)
// const portfolio = ref(null)
const opened = ref(false)
const title = ref('')
const selist = ref([])
const separator = ref('cell')
const prevExp = ref({})
const visibleColumnsDesk = [col(0).name,col(1).name,col(2).name,col(3).name,col(4).name,col(5).name,col(6).name,col(7).name,col(8).name,col(9).name]
const visibleColumnsFone = [col(0).name,col(3).name,col(4).name,col(7).name]
const columns = [col(0), col(1), col(2), col(3), col(4), col(5), col(6), col(7), col(8), col(9)]
// const xcolumns = ['cat', 'num']
// var xtitle = ''
// const xrow = ref([
//   { cat: 'Index Ranking', num: null },
//   { cat: 'Average Index (This Year)', num: null },
//   { cat: 'Average Index (All Games)', num: null },
//   { cat: 'Gross Score (This Day)', num: null },
//   { cat: 'Avgerage Score (All Games)', num: null },
//   { cat: 'Games Played (This Year)', num: null },
//   { cat: 'All Games Played', num: null },
//   { cat: 'Net Tean Win/Loss', num: null },
//   { cat: 'Net Pair Win/Loss', num: null }
// ])

//== main
console.log(`-ST-KjGameDataDisplay`)
emitter.on('open-KjGameDataDisplay',(kjPlayer, gdata) => showKjGameData(kjPlayer, gdata))

function col (idx) {
  const cols = [
    { required: false, label: null, align: 'center', name: 'game_date', field: 'game_date', sortable: true, headerClasses:'text-no-wrap text-right' },
    { required: false, label: '赛', align: 'center', name: 'all_games_played', field: 'all_games_played', headerClasses:'text-center' },
    { required: false, label: 'C', align: 'center', name: 'games_played', field: 'games_played',  headerClasses:'text-no-wrap text-center' },
    { required: false, label: '序', align: 'center', name: 'index', field: 'index', sortable: false, headerClasses:'text-no-wrap text-center' },
    { required: false, label: '', align: 'center', name: 'avg_idx', field: 'avg_idx', sortable: true, headerClasses:'text-no-wrap text-center' },
    { required: false, label: '', align: 'center', name: 'avg_idx_all_games', field: 'avg_idx_all_games', sortable: true, headerClasses:'text-no-wrap' },
    { required: false, label: '', align: 'center', name: 'gross_score', field: 'gross_score', sortable: true, headerClasses:'text-no-wrap'  },
    { required: false, label: '', align: 'center', name: 'avg_score',  field: 'avg_score', sortable: true, headerClasses:'text-no-wrap'  },
    { required: false, label: 'T', align: 'center', name: 'net_team_win_loss', field: 'net_team_win_loss', headerClasses:'text-no-wrap'  },
    { required: false, label: 'P', align: 'center', name: 'net_pair_win_loss', field: 'net_pair_win_loss', headerlefts:'text-no-wrap' },
  ]
  return cols[idx]
}
function closeDisplay (p) {
  prevExp.value = {}
  p.expand = false
  opened.value = false
}
function expandRow (p) {
  // console.log(`-fn-expendRow`, prevExp.value == p, prevExp.value, p)
  console.log(`-fn-expendRow`, p.row.index)
  if (prevExp.value.key == p.key) {
    p.expand = false
    return
  }
  prevExp.value.expand = false
  p.expand = true
  prevExp.value = p
  // xtitle = 'Game Data for ' + p.row.game_date
  // xrow.value[0].num = p.row.index
  // xrow.value[1].num = p.row.avg_idx
  // xrow.value[2].num = p.row.avg_idx_all_games
  // xrow.value[3].num = p.row.gross_score
  // xrow.value[4].num = p.row.avg_score
  // xrow.value[5].num = p.row.games_played
  // xrow.value[6].num = p.row.all_games_played
  // xrow.value[7].num = p.row.net_team_win_loss
  // xrow.value[8].num = p.row.net_pair_win_loss
  // console.log(xrow.value)
}
function showExpVal (val) {
  // if (val == 'n/a' || val == '--') return val
  // return fmtcy(val>0 ? val : -val)
  return val
}
function showVal (col) {
  // console.log(`-fn-showVal col.name=${col.name} col.value=${col.value}}`)
  // console.table(col)
  return col.value < 0 ? -col.value : col.value
  // if (col.name == 'acct_num') return col.value.substring(0,4)
  // else if (col.name == 'acct_name') return col.value.substring(0, 5).toUpperCase()
  // else if (col.name == 'symbol') return col.value.replace('**', '')
  // else if (col.name == 'holding_time') return isNaN(parseFloat(col.value)) ? 'n/a' : parseFloat(col.value).toFixed(1) + '年'
  // else if (col.value == 'n/a' || col.value == '--') return col.value
  // const val = col.value > 0 ? col.value : -col.value
  // if (/^[-]?\d+/.test(val)) return fmtcy(val)
  // return val
}
function getStyle (colname) {
  // if (colname == col(0).name || colname == col(1).name || colname == col(2).name) return 'text-align:center;font-size:19px'
  // else if (colname == 'current_val') return 'text-align:right;font-size:19px;font-family:dejavu sans mono'
  const x = 'text-align:center;font-size:18px; '
  if (colname == col(0).name) return 'text-align:left;font-size:18px;min-width:100px;max-width:100px'
  else if (colname == col(3).name) return x + 'min-width:24px;max-width:24px'
  // else if (colname == col(2).name) return x + 'min-width:40px'
  // else if (colname == col(2).name) return x + 'max-width:0px;min-width:0px'
  return x + 'max-width:40px;min-width:40px'

}
function getClass4Exp (val) {
  const comClr = 'bg-teal-10 text-right '
  if (val == 'n/a' || val == '--') return comClr
  const txtClr = val >= 0 ? '' : 'text-red'
  return comClr + txtClr
}
function getClass4Val (colname, colval) {
  if (colname == col(3).name) return 'text-cyan-4'
  else return colval < 0 ? 'text-red bg-teal-10 text-bold' : 'text-white'
}
function showKjGameData (kjPlayer, gdata) {
  gdata.forEach((p, i) => { p.index = i + 1;
    if (Number.isInteger(p.avg_idx)) p.avg_idx = p.avg_idx + '.0';
    else if (p.avg_idx_all_games != null && Number.isInteger(p.avg_idx_all_games)) p.avg_idx_all_games = p.avg_idx_all_games + '.0';
    else if (p.avg_score != null && Number.isInteger(p.avg_score)) p.avg_score = p.avg_score + '.0' })
  // console.log(`-fn-showKjGameData kjPlayer=${kjPlayer}`, gdata)
  opened.value = true
  title.value = isDesk ? 'MMs Game Play Data for "' + kjPlayer + '"' : 'Game Data (' + kjPlayer + ')'
  selist.value = gdata
  // selist.value = gdata.forEach((p, i) => p['index'] = i + 1)
  // console.log(selist.value)
}
</script>
<!-- <style>
tr {
  border-bottom: 0.1px solid #ddd;
}
tr:hover {
  background-color: #D6EEEE;
}
</style> -->

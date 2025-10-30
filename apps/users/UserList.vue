<template>
<q-dialog v-model="opened" full-width>
  <div class="q-py-xsbg-cyan-10">
    <q-btn round icon="close" color="amber" v-close-popup style="z-index:10;float:right;margin:5px 0 0 -88px" />
    <q-table flat bordered
      title="User List"
      :rows="rows"
      :columns="columns"
      row-key="id" 
      :rows-per-page-options="[10, 20, 30]"
      hide-pagination
      class="bg-teal-10 text-white"
    >
      <template v-slot:body="props">
        <q-tr key="id" :props="props" @click="cloneIt(props.row)" class="cursor-pointer">
          <q-td><q-radio size="70px" keep-color v-model="rowId" :val="props.row.id" checked-icon="task_alt" unchecked-icon="panorama_fish_eye" 
            :color="props.row.id % 3 == 0 ? 'yellow': props.row.id % 3 == 1 ? 'green' : 'pink'" @click="cloneIt(props.row)"/></q-td>
          <q-td key="name"     :props="props">{{ props.row.name }}</q-td>
          <q-td key="username" :props="props">{{ props.row.username }}</q-td>
          <q-td key="usertype" :props="props">{{ props.row.usertype }}</q-td>
          <q-td key="email"    :props="props">{{ props.row.email }}</q-td>
        </q-tr>
      </template>
    </q-table>
    <div v-if="rowId>0" class="q-pa-xs"> 
      <div class="row">
        <TxtInput class="col-6" :obj="selectedRow" label="Full Name" icon="person" iColor="cyan-2" :rightIcon="true" />
        <TxtInput class="col-6" :obj="selectedRow" label="username" icon="account_box" iColor="cyan-2" :rightIcon="true" />
      </div>
      <div class="row">
        <TxtInput class="col-6" :obj="selectedRow" label="usertype" icon="contacts" iColor="cyan-2" :rightIcon="true" />
        <TxtInput class="col-6" :obj="selectedRow" label="password" icon="password" iColor="cyan-2" :rightIcon="true" />
      </div>
      <TxtInput class="col-12" :obj="selectedRow" label="email" icon="email" iColor="cyan-2" :rightIcon="true" />
    </div>
    <div v-if="rowId>0" class="row justify-between q-px-md q-py-xs">
      <q-btn round icon="chevron_left" color="amber" @click="rowId=null" />
      <q-btn label="delete" icon="delete" @click="del" color="red" />
      <q-btn label="update" icon="update" @click="upd" color="amber" />
      <q-btn label="create" icon="create" @click="add" color="green" />
      <q-btn round icon="close" color="amber" v-close-popup />
    </div>
  </div>
</q-dialog>
<ConfirmDialog ref="refConfirmDialog" @user-confirmed="delFromDB" />
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import emitter from 'tiny-emitter/instance'
import { axiosFunctions } from '../src/composables/axiosFunctions'
const { gaxios, paxios } = axiosFunctions()
import TxtInput from '../src/components/TxtInput'
import ConfirmDialog from '../src/components/ConfirmDialog'
const opened = ref(false)
const rows = ref([])
const rowId = ref(null)
const selectedRow = ref([])
const refConfirmDialog = ref(false)

console.log('-ST-UserListDialog')
emitter.on('users-getUserList', (x) => openIt(x))
emitter.on('users-del', () => { rowId.value = null })
emitter.on('users-add', (x) => { rows.value.push(x.data); rowId.value = null })
emitter.on('users-upd', (x) => updatedRow(x))
onMounted(() => refConfirmDialog)
defineExpose({ getUserList })

function cloneIt (row) {
  selectedRow.value = structuredClone(row)
  rowId.value = row.id
  selectedRow.value.password = 'P@$$w0rdZ'
}
function del () {
  const tit = "Delete Following User?"
  const msg = rows.value.filter(x => x.id === rowId.value)[0].name
  refConfirmDialog.value.openIt(tit, msg, 'del')
}
function updatedRow (da) {
  const rIdx = rows.value.map(x => x.id).indexOf(da.data.id)
  // console.log(`-fn-updatedRow rowId=${rowId.value} da.id=${da.data.id} rIdx=${rIdx}`, rows.value.map(x => x.id), da.data)
  rows.value.splice(rIdx, 1, da.data)
  rowId.value = null
}
function delFromDB () {
  console.log(`-fn-delFromDB rowId=${rowId.value}`, rowId.value)
  const path = process.env.API + '/users/del/' + rowId.value
  rows.value.splice(rows.value.map(x => { return x.id }).indexOf(rowId.value), 1)
  gaxios(path)
}
function upd () {
  // const upduser = rows.value.filter(x => x.id === rowId.value)[0]
  console.log(`-fn-upd rowId=${rowId.value}`, selectedRow.value)
  const path = process.env.API + '/users/upd'
  paxios(path, selectedRow.value)
}
function add () {
  // console.log(`-fn-add rowId=${rowId.value}`, rows.value.filter(x => x.id === rowId.value)[0])
  const path = process.env.API + '/users/add'
  paxios(path, selectedRow.value)
}
function getUserList () {
  console.log('-fn-getUserList')
  const path = process.env.API + '/users/getUserList'
  gaxios(path)
}
function openIt(da) {
  console.log('-fn-openIt', da.data)
  // rows.value = JSON.parse(JSON.stringify(da.data))
  // rows.value = structuredClone(da.data)
  rows.value = da.data
  opened.value = true
}
const columns = [
  // { name: 'name', required: true, label: 'Full Name', align: 'left', field: row => row.name, format: val => `${val}`, sortable: true  },
  { name: 'name', required: true, label: 'Selected', align: 'center', headerStyle:'font-size:20px' },
  { name: 'name', required: true, label: 'Full Name', align: 'left', field: 'name', sortable: true, headerStyle:'font-size:20px', style:'font-size:18px' },
  { name: 'username', required: true, align: 'left', label: 'username', field: row=> row.username, sortable: true, headerStyle:'font-size:20px', style:'font-size:18px'  },
  { name: 'usertype', required: true, align: 'left', label: 'usertype', field: 'usertype', sortable: true, headerStyle:'font-size:20px', style:'font-size:18px'  },
  { name: 'email', required: true, align: 'left', label: 'Email Address', field: 'email', sortable: true, headerStyle:'font-size:20px', style:'font-size:18px'  },
  // { name: 'fat', label: 'Fat (g)', field: 'fat', sortable: true, style: 'width: 10px' },
  // { name: 'carbs', label: 'Carbs (g)', field: 'carbs' },
  // { name: 'protein', label: 'Protein (g)', field: 'protein' },
  // { name: 'sodium', label: 'Sodium (mg)', field: 'sodium' },
  // { name: 'calcium', label: 'Calcium (%)', field: 'calcium', sortable: true, sort: (a, b) => parseInt(a, 10) - parseInt(b, 10) },
  // { name: 'iron', label: 'Iron (%)', field: 'iron', sortable: true, sort: (a, b) => parseInt(a, 10) - parseInt(b, 10) }
]
</script>

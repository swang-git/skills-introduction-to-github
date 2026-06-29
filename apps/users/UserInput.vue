<template>
  <q-dialog v-model="opened" width="99%" :transition-show="action == 'add' ? 'slide-right' : 'rotate'" :maximized="isIM">
    <q-layout container class="bg-teal-10" style="max-width: 600px; height: 322px" >
      <LayoutHeader :tit="getTitle()" @do-action="doAction" />
      <LayoutFooter :act="action" @do-action="doAction" />
      <q-page-container>
        <div class="q-pa-sm bg-teal-10" style="border: solid red 0px">
          <div class="q-pa-xs">
            <div class="row">
              <TxtInput class="col-6 q-pa-xs" :obj="selectedRow" label="Full Name" icon="person" iColor="green" :rightIcon="true" />
              <TxtInput class="col-6 q-pa-xs" :obj="selectedRow" label="username" icon="account_box" iColor="cyan" :rightIcon="true" />
            </div>
            <div class="row">
              <TxtInput class="col-6 q-pa-xs" :obj="selectedRow" label="usertype" icon="contacts" iColor="lime" :rightIcon="true" />
              <TxtInput class="col-6 q-pa-xs" :obj="selectedRow" label="password" icon="password" iColor="amber" :rightIcon="true" />
            </div>
            <TxtInput class="col-12" :obj="selectedRow" label="email" icon="email" iColor="secondary" :rightIcon="true" />
          </div>
        </div>
      </q-page-container>
    </q-layout>
  </q-dialog>
  <ConfirmDialog ref="refConfirmDialog" @user-confirmed="delFromDB" />
</template>

<script setup>
import { ref, onMounted } from 'vue'
import emitter from 'tiny-emitter/instance'
import { libFunctions } from '../src/composables/libFunctions'
import { axiosFunctions } from '../src/composables/axiosFunctions'
const { gaxios, paxios } = axiosFunctions()
const { isIM } = libFunctions()
import TxtInput from '../src/components/TxtInput.vue'
import ConfirmDialog from '../src/components/ConfirmDialog.vue'
import LayoutHeader from '../src/components/LayoutHeader.vue'
import LayoutFooter from '../src/components/LayoutFooter.vue'
const opened = ref(false)
const action = ref(null)
const rowId = ref(null)
const tit = ref(null)
const selectedRow = ref([])
const refConfirmDialog = ref(false)
const ENV_DEV = import.meta.env.DEV ? '/api' : ''

console.log('-ST-UserInput')
emitter.on('users-getUserList', x => openIt(x))
emitter.on('open-UserInput', rw => {
  console.log('open-UserInput', rw)
  openIt(rw)
})
// emitter.on('users-del', () => { rowId.value = null })
// emitter.on('users-add', x => { rows.value.push(x.data); rowId.value = null })
// emitter.on('users-upd', x => updatedRow(x))
onMounted(() => refConfirmDialog)
// defineExpose({ getUserList })

function doAction(act) {
  console.log(`-fn-doAction act=${act}`)
  if (act == 'upd') upd()
  else if (act == 'add') add()
  else del()
}
function getTitle() {
  return 'Add/Upd/Del User'
}
function getFoote() {
  console.log(`-fn-getFoote tit=${tit.value}`)
  return 'NOTE_LINK'
}
function del() {
  const tit = 'Delete Following User?'
  const msg = selectedRow.value.name
  refConfirmDialog.value.openIt(tit, msg, 'del')
}
function delFromDB() {
  console.log(`-fn-delFromDB rowId=${selectedRow.value.id}`, rowId.value)
  const path = ENV_DEV + '/users/del/' + rowId.value
  // rows.value.splice(
  //   rows.value
  //     .map(x => {
  //       return x.id
  //     })
  //     .indexOf(rowId.value),
  //   1
  // )
  gaxios(path)
  opened.value = false
}
function upd() {
  // const upduser = rows.value.filter(x => x.id === rowId.value)[0]
  console.log(`-fn-upd rowId=${rowId.value}`, selectedRow.value)
  const path = ENV_DEV + '/users/upd'
  paxios(path, selectedRow.value)
  opened.value = false
}
function add() {
  // console.log(`-fn-add rowId=${rowId.value}`, rows.value.filter(x => x.id === rowId.value)[0])
  const path = ENV_DEV + '/users/add'
  paxios(path, selectedRow.value)
  opened.value = false
}
function openIt(rw) {
  console.log('-fn-openIt', rw)
  selectedRow.value = rw
  rowId.value = rw.id
  opened.value = true
}
const columns = [
  // { name: 'name', required: true, label: 'Full Name', align: 'left', field: row => row.name, format: val => `${val}`, sortable: true  },
  {
    name: 'name',
    required: true,
    label: 'Selected',
    align: 'center',
    headerStyle: 'font-size:20px'
  },
  {
    name: 'name',
    required: true,
    label: 'Full Name',
    align: 'left',
    field: 'name',
    sortable: true,
    headerStyle: 'font-size:20px',
    style: 'font-size:18px'
  },
  {
    name: 'username',
    required: true,
    align: 'left',
    label: 'username',
    field: row => row.username,
    sortable: true,
    headerStyle: 'font-size:20px',
    style: 'font-size:18px'
  },
  {
    name: 'usertype',
    required: true,
    align: 'left',
    label: 'usertype',
    field: 'usertype',
    sortable: true,
    headerStyle: 'font-size:20px',
    style: 'font-size:18px'
  },
  {
    name: 'email',
    required: true,
    align: 'left',
    label: 'Email Address',
    field: 'email',
    sortable: true,
    headerStyle: 'font-size:20px',
    style: 'font-size:18px'
  }
  // { name: 'fat', label: 'Fat (g)', field: 'fat', sortable: true, style: 'width: 10px' },
  // { name: 'carbs', label: 'Carbs (g)', field: 'carbs' },
  // { name: 'protein', label: 'Protein (g)', field: 'protein' },
  // { name: 'sodium', label: 'Sodium (mg)', field: 'sodium' },
  // { name: 'calcium', label: 'Calcium (%)', field: 'calcium', sortable: true, sort: (a, b) => parseInt(a, 10) - parseInt(b, 10) },
  // { name: 'iron', label: 'Iron (%)', field: 'iron', sortable: true, sort: (a, b) => parseInt(a, 10) - parseInt(b, 10) }
]
</script>

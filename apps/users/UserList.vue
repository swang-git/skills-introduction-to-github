<template>
  <q-dialog v-model="opened" full-width>
    <!-- <div class="q-pa-sm bg-cyan-10" style="border: solid cyan 4px" :style="rowId<=0 ? { 'height':'830px' } : { 'height':'1060px' }"> -->
    <div
      class="q-pa-sm bg-cyan-10"
      style="border: solid cyan 4px;height:'830px"
    >
      <q-btn
        round
        icon="close"
        glossy
        color="amber-10"
        v-close-popup
        class="float-right"
        style="z-index: 10; margin: -1px 0 0 -100px"
      />
      <q-table
        flat
        bordered
        dense
        title="User List"
        :rows="rows"
        :columns="columns"
        row-key="id"
        :rows-per-page-options="[10, 20, 30]"
        hide-pagination
        class="bg-teal-10 text-white text-h6"
      >
        <template v-slot:body="props">
          <q-tr
            key="id"
            :props="props"
            @click="openUserInput(props.row)"
            class="cursor-pointer"
          >
            <q-td
              ><q-radio
                size="70px"
                keep-color
                v-model="rowId"
                :val="props.row.id"
                checked-icon="task_alt"
                unchecked-icon="panorama_fish_eye"
                :color="
                  props.row.id % 3 == 0
                    ? 'yellow'
                    : props.row.id % 3 == 1
                      ? 'green'
                      : 'pink'
                "
                @click="cloneIt(props.row)"
            /></q-td>
            <q-td key="name" :props="props">{{ props.row.name }}</q-td>
            <q-td key="username" :props="props">{{ props.row.username }}</q-td>
            <q-td key="usertype" :props="props">{{ props.row.usertype }}</q-td>
            <q-td key="email" :props="props">{{ props.row.email }}</q-td>
          </q-tr>
        </template>
      </q-table>
    </div>
    <UserInput />
  </q-dialog>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import emitter from 'tiny-emitter/instance'
import { axiosFunctions } from '../src/composables/axiosFunctions'
const { gaxios, paxios } = axiosFunctions()
import TxtInput from '../src/components/TxtInput.vue'
import ConfirmDialog from '../src/components/ConfirmDialog.vue'
const opened = ref(false)
const rows = ref([])
const rowId = ref(null)
const refConfirmDialog = ref(false)
const ENV_DEV = import.meta.env.DEV ? '/api' : ''

console.log('-ST-UserList')
emitter.on('users-getUserList', x => openIt(x))
emitter.on('users-del', da => {
  deletedRow(da)
})
emitter.on('users-add', x => {
  rows.value.push(x.data)
  rowId.value = null
})
emitter.on('users-upd', x => updatedRow(x))
onMounted(() => refConfirmDialog)
defineExpose({ getUserList })

function openUserInput(rw) {
  console.log(`-fn-openUserInput`, rw)
  // selectedRow.value = JSON.parse(JSON.stringify(row))
  const row = JSON.parse(JSON.stringify(rw))
  rowId.value = rw.id
  // selectedRow.value.password = 'P@$$w00rdZ'
  row.password = 'P@$$w00rdZ'
  emitter.emit('open-UserInput', row)
}
function deletedRow(da) {
  // const rIdx = rows.value.map(x => x.id).indexOf(da.data.id)
  console.log(
    `-fn-deletedRow rowId=${rowId.value} da.id=${da.id}`,
    rows.value.map(x => x.id),
    da
  )
  rows.value = rows.value.filter(x => x.id != da.id)
  rowId.value = null
}
function updatedRow(da) {
  const rIdx = rows.value.map(x => x.id).indexOf(da.data.id)
  console.log(
    `-fn-updatedRow rowId=${rowId.value} da.id=${da.data.id} rIdx=${rIdx}`,
    rows.value.map(x => x.id),
    da.data
  )
  rows.value.splice(rIdx, 1, da.data)
  rowId.value = null
}
function getUserList() {
  console.log('-fn-getUserList')
  const path = ENV_DEV + '/users/getUserList'
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

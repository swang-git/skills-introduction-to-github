<template>
<q-dialog v-model="opened" transition-show="rotate" transition-hide="slide-left" persistent>
  <div class="bg-teal-10 row q-pa-xs">
    <div><q-btn round flat icon="cancel" color="amber" size="lg" @click="opened=false" glossy /></div>
    <div class="q-pa-sm">
      <q-input class="text-h6 q-px-xs" label-color="cyan-2" autofocus maxlength=24 clearable dark borderless dense input-class="text-center" v-model="newCIname" :label="getLabel()" />
    </div>
    <div><q-btn round flat icon="add_circle" color="cyan-2" glossy size="lg" @click="addNewCIdialog" /></div>
  </div>
</q-dialog>
</template>
<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'
import { axiosFunctions } from '../src/composables/axiosFunctions'
const { paxios } = axiosFunctions()
import { libFunctions } from '../src/composables/libFunctions'
const { $q } = libFunctions()
const className = ref(null)
const newCIname = ref(null)
const classId = ref(0)
const opened = ref(false)

console.log('-ST-ItemPad')
emitter.on('open-ItemPad', (x, y) => openIt(x, y))

function openIt (cId, cName) {
  console.log(`-fn-openIt - classId=${cId} className=${cName}`)
  classId.value = cId
  className.value = cName
  opened.value = true
}
function getLabel () {
  let label = 'Enter New CLASS name here'
  if (classId.value > 0) label = 'Enter New ITEM for ' + className.value
  return label
}
function genDialog (tit) {
  $q.dialog({
    color: 'amber-10',
    position: 'right',
    title: tit,
    ok: { label: 'OK' }
  })
}
function addNewCIdialog () {
  if (classId.value === 0 && newCIname.value === null) {
    console.log('-WN-please provide new class name', classId.value, newCIname.value)
    const tit = 'please provide new class name'
    genDialog(tit)
    return
  } else if (classId.value > 0 && (className.value === null || newCIname.value === null)) {
    const tit = 'please provide new item name for ' + className.value
    console.log('-WN-please provide new item name for', className.value)
    genDialog(tit)
    return
  }
  console.log(`-CK-New ClassName=${newCIname.value} classId=${classId.value}`)
  const newClass = '<center>Add New Class「 <b>' + newCIname.value + '</b> 」'
  const newItem = '<center>Add New Item「 <b>' + newCIname.value + '</b> 」' + className.value + '类?</center>'
  $q.dialog({
    html: true,
    color: 'primary',
    position: 'right',
    title: classId.value === 0 ? newClass : newItem,
    cancel: { label: '唔，我再想想' },
    ok: { label: 'OK' }
  }).onOk(() => {
    console.log('OK Clicked')
    addNewCIname()
  }).onCancel(() => {
    console.log('Cancelled')
  })
}
function addNewCIname () {
  console.log('-fn-addNewCIname')
  var path = null
  var inData = {}
  if (classId.value > 0) {
    inData = { name: newCIname.value, class_id: classId.value, class: className.value }
    // this.inData = inData
    console.log('-CK-add new item', inData)
    path = process.env.API + '/shopping/addNewItem'
  } else if (classId.value === 0) {
    inData = { class: newCIname.value }
    console.log('-CK-add new class', inData)
    path = process.env.API + '/shopping/addNewClass'
  }
  paxios(path, inData)
}
const emit = defineEmits(['add-item', 'add-class'])
emitter.on('shopping-addNewClass', (da) => {
  if (da.status === 'classExists') {
    $q.dialog({
      color: 'red',
      title: `class 『${da.className}』 already exists`,
      ok: { label: 'OK' }
    })
    return
  }
  emit('add-class', da.newClass)
  // this.$parent.itemClasses = da.newClass
  // this.$parent.itemClasses.push({ value: 0, label: 'NewClass' })
})
emitter.on('shopping-addNewItem', (da) => {
  console.log(`-CK-addNewItem`, da)
  if (da.status === 'itemExists') {
    $q.dialog({
      color: 'red',
      title: 'Item ' + `${da.itemName}` + ' already exists in ' + `${da.class}`,
      ok: { label: 'OK' }
    })
    return
  }
  emit('add-item', da.newItem)
  // emit('add-item', da.id, da.item_id, da.class_id, da.itemList, da.itemClasses)
  // emit('add-item', da.id, da.item_id, da.class_id, da.itemList, da.itemClasses)
  // this.$parent.itemList = da.itemList
  // this.$parent.itemClasses = da.itemClasses
  // this.$parent.itemClasses.push({ value: 0, label: 'NewClass' })
  // const newItem = this.inData
  // newItem.id = da.id
  // newItem.item_id = da.item_id
  // this.$parent.candidateItemsInClass.unshift(newItem)
  // classId.value = -1
  // console.log(`-CK-addNewItem id=${da.id} itemId=${da.item_id}, classId=${da.class_id}`, da.itemList, da.itemClasses)
})
</script>

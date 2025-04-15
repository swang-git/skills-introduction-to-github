<template>
<q-dialog v-model="opened" :maximized="isIM">
  <div class="absolute" :style="getPos()">
    <table class="bg-teal-10" style="border:5px solid #aaa;border-radius:40px">
      <q-tr>
        <th colspan="4" style="border-radius:30px" class="inset-shadow-down q-py-sm text-h6 text-white bg-teal-10">{{ item.name }} {{ tagName }}: {{ num }}</th>
      </q-tr>
      <q-tr class="text-white"><td v-for="x in ['3', '9', '6', 'N']" :key=x @click="setNumber(x)"><q-btn round color="cyan-9" size="xl">{{x}}</q-btn></td></q-tr>
      <q-tr class="text-white"><td v-for="x in ['2', '9', '5', 'P']" :key=x @click="setNumber(x)"><q-btn round color="cyan-9" size="xl">{{x}}</q-btn></td></q-tr>
      <q-tr class="text-white"><td v-for="x in ['1', '8', '4', 'U']" :key=x @click="setNumber(x)"><q-btn round color="cyan-9" size="xl">{{x}}</q-btn></td></q-tr>
      <q-tr class="text-white"><td v-for="x in ['0', '7', 'X', 'C']" :key=x @click="setNumber(x)"><q-btn round color="cyan-9" size="xl">{{x}}</q-btn></td></q-tr>
    </table>
  </div>
</q-dialog>
</template>
<script setup>
import { ref, computed } from 'vue'
import emitter from 'tiny-emitter/instance'
import { libFunctions } from '../src/composables/libFunctions'
const { isIM, deepClone, decimal2 } = libFunctions()

//== data sections
const posX = ref(0)
const posY = ref(0)
const oInput = ref('')
const num = ref('')
const tag = ref('')
const item = ref({})
const oItem = ref({})
const opened = ref(false)

console.log('-ST-PUCPad')
emitter.on('open-PUCPad', (tg, itm) => openIt(tg, itm))

//== computed sections
const padpos = computed(() => { return 'top:' + posY.value + 'px;left:' + posX.value + 'px' })
const padcos = computed(() => { return 'top:' + posY.value + 'px;right:18px' })
const tagName = computed(() => { return tag.value === 'costs' ? 'COST' : tag.value.toUpperCase() })

function getPos () { return tag.value === 'costs' ? padcos.value : padpos.value }
const emit = defineEmits(['upd-item', 'restore-num'])
function setNumber (np) {
  console.log(`-fn-setNumber np=${np} tag=${tag.value}`, oItem.value, item.value.disct, item.value.price, item.value.units, item.value.costs)
  if (np === 'X') {
    console.log(`-CK-setNumber oprice=${oItem.value.price}`, oItem.value, item.value)
    emit('restore-num', oItem.value)
    opened.value = false
  } else if (np === 'D') {
    oInput.value = ''
    num.value = ''
    emit('upd-item', item.value)
    opened.value = false
    return
  } else if (np === 'N') {
    oInput.value = ''
    num.value = ''
    if (tag.value === 'price') tag.value = 'units'
    else if (tag.value === 'units') tag.value = 'costs'
    return
  } else if (np === 'P') {
    if (item.value.units === 0 || item.value.costs === 0) return
    const discount = item.value.disct === null ? 0 : parseFloat(item.value.disct)
    item.value.price = ((parseFloat(item.value.costs) - discount) / item.value.units).toFixed(2)
    emit('upd-item', item.value)
    opened.value = false
    return
  } else if (np === 'U') {
    if (item.value.price === 0 || item.value.costs === 0) return
    item.value.units = (item.value.costs / item.value.price).toFixed(2)
    emit('upd-item', item.value)
    opened.value = false
    return
  } else if (np === 'C') {
    if (item.value.price === 0 || item.value.units === 0) return
    item.value.costs = (item.value.units * item.value.price).toFixed(2)
    emit('upd-item', item.value)
    opened.value = false
  }
  oInput.value += np
  const nm = decimal2(oInput.value)
  num.value = parseFloat(nm).toFixed(2)
  if (tag.value === 'price') { item.value.price = parseFloat(num.value).toFixed(3) }
  if (tag.value === 'units') { item.value.units = num.value }
  if (tag.value === 'disct') { item.value.disct = num.value }
  if (tag.value === 'costs') { item.value.costs = num.value }
}
function openIt (tg, itm) {
  console.log('-fn-openIt', tg, itm)
  posX.value = event.pageX - 150
  posY.value = event.pageY + 12
  if (tg === 'disct') {
    posX.value = event.pageX - 170
    posY.value = event.pageY + 15
  }
  const winHeight = event.view.outerHeight
  const padHeight = 423
  if (posY.value + padHeight > winHeight) posY.value -= (padHeight - 3)
  tag.value = tg
  item.value = itm
  oItem.value = deepClone(item)
  num.value = ''
  oInput.value = ''
  opened.value = true
  // console.log('-x-numPad.openIt', oItem.value)
  if (isIM) {
    posX.value = 0
    posY.value = 0
  }
}
</script>

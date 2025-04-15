<template>
<q-dialog v-model="opened">
  <div class="absolute" :style="padpos">
    <table class="bg-teal-10">
      <q-tr class="text-white text-center"><td v-for="x in ['6.625', '7.376', 'X']" :key=x.i @click="setTax(x)"><q-btn round glossy size="xl">{{x}}</q-btn></td></q-tr>
      <q-tr><th colspan="3" class="text-grey text-h5 q-pa-sm text-center">{{ item.name }} 税率</th></q-tr>
    </table>
  </div>
</q-dialog>
</template>
<script setup>
import { ref, computed } from 'vue'
import emitter from 'tiny-emitter/instance'
import { libFunctions } from '../src/composables/libFunctions'
const { isIM } = libFunctions()
const posX = ref(0)
const posY = ref(0)
const item = ref({})
const opened = ref(false)
const emit = defineEmits(['upd-item'])

console.log('-ST-TaxPad')

const padpos = computed(() => { return 'top:' + posY.value + 'px;left:' + posX.value + 'px' })

function setTax (np) {
  // console.log(np, this.tag, this.oItem, item.value.tax, item.value.price, item.value.units, item.value.costs)
  if (np === 'X') item.value.tax = null
  else item.value.tax = np
  emit('upd-item', item.value)
  opened.value = false
}
emitter.on('open-TaxPad', (x) => openIt(x))
function openIt (itm) {
  console.log('taxPad.openIt item', item, event.pageX, event.pageY)
  posX.value = event.pageX - 110
  posY.value = event.pageY + 20
  item.value = itm
  opened.value = true
  if (isIM) {
    posX.value = 0
    posY.value = 0
  }
}
</script>

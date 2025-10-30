<template>
<q-dialog v-model="opened" transition-show="rotate" transition-hide="scale">
  <div class="bg-teal-10 text-white" style="border:1px solid cyan">
    <q-card-section class="inset-shadow-down">
      <div class="bg-teal-10">
        <div class="text-h4 text-bold text-center">{{ tit }}</div>
      </div>
    </q-card-section>

    <q-card-section class="text-h4">
      <div class="row">
        Width: <div class="text-red q-px-sm">{{ screen_width }}</div>
      </div>
      <div class="row">
        Height: <div class="text-red q-px-sm">{{ screen_height }}</div>
      </div>
    </q-card-section>

    <q-card-actions align="right" class="bg-cyan text-teal-10">
      <q-btn flat label="close" v-close-popup />
    </q-card-actions>
  </div>
</q-dialog>
</template>
<script setup>
import emitter from 'tiny-emitter/instance'
import { ref, computed } from 'vue' 
import { useQuasar } from 'quasar' 
import { libFunctions } from '../composables/libFunctions'
const $q = useQuasar()
const { opened } = libFunctions()
const deviceType = ref('iphoneMax')

const screen_height = $q.screen.height
const screen_width = $q.screen.width
const iPhone11ProMax = computed(() => { return screen_width == 414 && screen_height == 708 })
const iPhoneX = computed(() => { return screen_width == 375 && screen_height == 626 })
// function XXcheckDeviceType () {
//   console.log('%c-CHECKING DEVICE TYPE', "font-size:10px;font-weight:600;color:red")
//   console.log(`%cHeight=${screen_height} Width=${screen_width}`, "font-size:10px;font-weight:600;color:red")
// }
var tit = null
console.log('-ST-DeviceType')
deviceType.value = iPhone11ProMax.value ? 'iPhone11 Pro Max' : iPhoneX.value ? 'iPhoneX' : 'Unknown'
// tit = `Device Type: ${deviceType.value}`
tit = `Device Dimension`
emitter.on('show-DeviceType', () => { opened.value = true })
// checkDeviceType()
</script>

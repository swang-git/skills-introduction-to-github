<template>
<div class="q-px-xs q-pt-xs">
  <q-input rounded outlined v-model="time" dark class="bg-teal-10" :class="txsz" :label="label" style="width:320px">
    <template v-slot:append>
      <q-icon name="schedule" size="lg" color="cyan" class="cursor-pointer">
        <q-popup-proxy v-model="showTime">
          <div class="q-gutter-xs" :class="{ 'row':isDesk }" :style="isDesk ? '' : 'margin:-110px 0 0 20px'">
            <q-time v-model="time" mask="YYYY-MM-DD HH:mm" dark now-btn>
              <div class="row items-center justify-end">
                <q-btn label="OK" color="green" glossy round @click="updTime()" v-close-popup />
              </div>
            </q-time>
          </div>
        </q-popup-proxy>
      </q-icon>
    </template>
    <template v-slot:prepend>
      <q-btn class="q-pb-xs" round outline size="13px" :icon="time==null ? 'X' : time.chwk1()" color="cyan-3" @click="showManualInput=false;showTime=true" />
    </template>
    <!-- <q-popup-edit v-if="showManualInput" v-model="time" auto-save > 
      <q-input v-model="time" mask="##:##" dense autofocus counter @keyup.enter="updTime()" class="text-h6 q-ma-xs" />
      <div class="row items-center justify-end">
        <TimePicker label="OK" color="green" glossy round />
      </div>
    </q-popup-edit> -->
  </q-input>
</div>
</template>
<script setup>
// import emitter from 'tiny-emitter/instance'
import { ref } from 'vue'
import { libFunctions } from '../composables/libFunctions'
const { isDesk } = libFunctions()

const props = defineProps({
  idx: { type: Number },
  time: { type: String },
  txsz: { type: String },
  label: { type: String }
})
const emit = defineEmits(['upd-tm'])

const showManualInput = ref(true)
// const dshow = true
// const tshow = false
// const time = ref(props.time.replace('T', ' '))
const time = ref(props.time)
const showTime = ref(false)
// const datetime = ref(props.dateTime)

console.log(`-ST-TimePicker time=${time.value}`)
// emitter.on('upd-dt', (dt) => { datetime = dt; console.log(`-CK-dt=${dt}`) })
// emitter.on('show-updtm', () => showManualInput.value = true)
// function log_time () { console.log(`time=${time.value}`) }
function updTime () {
  console.log(`-CK-fn-updTime time=${time.value}  time=${props.time}`)
  // showManualInput.value = true
  emit('upd-tm', props.idx, time.value)
}
</script>

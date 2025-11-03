<template>
<div class="q-px-xs q-pt-xs">
  <q-input rounded outlined v-model="datetime" dark class="bg-teal-10" :class="txsz" :label="label">
    <template v-slot:prepend>
      <q-icon name="schedule" size="lg" color="cyan" class="cursor-pointer">
        <q-popup-proxy v-model="showDatetime">
          <div class="q-gutter-xs" :class="{ 'row':isDesk }" :style="isDesk ? '' : 'margin:-110px 0 0 20px'">
            <!-- <div><q-date v-model="datetime" mask="YYYY-MM-DD HH:mm" dark today-btn @click="log_datime" style="height:434.5px"/></div> -->
            <div><q-date v-model="datetime" mask="YYYY-MM-DD HH:mm" dark today-btn style="height:434.5px"/></div>
            <div>
              <q-time v-model="datetime" mask="YYYY-MM-DD HH:mm" dark now-btn>
                <div class="row items-center justify-end">
                  <q-btn label="OK" color="green" glossy round @click="updDatetime()" v-close-popup />
                </div>
              </q-time>
            </div>
          </div>
        </q-popup-proxy>
      </q-icon>
    </template>
    <template v-slot:append>
      <!-- <q-btn class="q-pb-xs" round outline size="13px" :icon="datetime==null ? 'X' : datetime.chwk1()" color="cyan-3" @click="showManualInput=false;showDatetime=true" /> -->
      <q-btn class="q-pb-xs" round outline size="13px" :icon="datetime.chwk1()" color="cyan-3" @click="showManualInput=false;showDatetime=true" />
    </template>
    <q-popup-edit v-if="showManualInput" v-model="datetime" auto-save > 
      <q-input v-model="datetime" mask="####-##-## ##:##" dense autofocus counter @keyup.enter="updDatetime()" class="text-h6 q-ma-xs" />
      <div class="row items-center justify-end">
        <q-btn label="OK" color="green" glossy round @click="updDatetime()" v-close-popup />
      </div>
    </q-popup-edit>
  </q-input>
</div>
</template>
<script setup>
// import emitter from 'tiny-emitter/instance'
import { ref } from 'vue'
import { libFunctions } from '../composables/libFunctions'
const { isDesk } = libFunctions()

const props = defineProps({
  dateTime: { type: String },
  txsz: { type: String },
  label: { type: String },
})
const emit = defineEmits(['upd-dt'])

const showDatetime = ref(false)
const showManualInput = ref(true)
const datetime = ref(props.dateTime.replace('T', ' '))
// const datetime = ref(props.dateTime)
//var dshow = true
//var tshow = false

console.log(`-ST-DateTimePicker datetime=${datetime.value}`)
// emitter.on('upd-dt', (dt) => { datetime = dt; console.log(`-CK-dt=${dt}`) })
// emitter.on('show-updtm', () => showManualInput.value = true)

// function showDatetimeFunc () {
//   showDatetime.value = true
//   showManualInput.value = false
//   console.log(`-fn-showDatetimeFunc showDatetime=${showDatetime.value} showManualInput=${showManualInput.value}`)
//   // console.log(`-fn-showDateTimeFunc`)
// }
// function log_datime () { console.log(`datetime=${datetime.value}`) }
function updDatetime () {
  console.log(`-CK-fn-updDatetime datetime=${datetime.value}  dateTime=${props.dateTime}`)
  showManualInput.value = true
  emit('upd-dt', datetime.value)
}
</script>

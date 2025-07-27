<template>
<q-dialog v-model="opened" persistent>
  <div style="background:teal;padding:10px;margin:-336px 0 0 200px">
    <q-card-actions align="between">
      <div style="font-size:20px"> Par for Hole {{ holeIdx }} </div>
      <q-btn glossy color="amber-10" round v-close-popup icon="close" />
    </q-card-actions>
    <table style="y-overflow:auto;margin:auto">
      <q-tr><td v-for="par in [3, 4, 5]" :key=par.x><q-btn size="xl" color="green-9" style="width:60px;height:60px" @click="setPar(par)">{{par}}</q-btn></td></q-tr>
    </table>
  </div>
</q-dialog>
</template>
<script setup>
import { ref } from 'vue'
// import { useStore } from 'vuex'
// const store = useStore()
import { libFunctions } from '../composables/libFunctions'
const { opened, store } = libFunctions()

const emit = defineEmits(['set-pars'])
var holes = ref({})
var holeIdx = ref(0)
// const opened = ref(false)

defineExpose({ openIt})
console.log(`-ST-CourseHolePad`)
function openIt (idx, hls) {
  holeIdx.value = idx
  holes.value = hls
  console.log(`-CK-fn-openIt holeIdx=${holeIdx.value}`, holes)
  opened.value = true
}
function setPar (par) {
  console.log(`-CK-fn setPar for hole ${holeIdx.value} par=${par}`)
  holes['h' + holeIdx.value] = par
  let holex = JSON.parse(JSON.stringify(holes))
  store.holes = holex
  emit('set-pars', holeIdx.value, par)
  // console.log('par for hole', holeIdx, this.holex['h' + holeIdx])
  holeIdx.value++
  if (holeIdx.value == 10 || holeIdx.value > 18) opened.value = false
}
</script>
<!-- <style>
.num-pad-score {
  width: 60px;
  height: 60px;
  font-size: 25px;
  background: red;
}
</style> -->

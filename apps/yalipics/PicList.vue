<template>
<div class="q-pa-xs text-amber text-center text-h4">娅 莉 画 展</div>
<q-card class="flex flex-center" style="background:teal">
  <q-card-actions align="between">
    <div v-for="(p, idx) in pics" :key=p class="q-px-xs">
      <img :src="getImg(p)" :height="isIM ? '171' : '150'" :width="isIM ? '171' : '150'" class="q-pa-xs cursor-pointer" @click="refPicDialog.openIt(idx, pics)" />
    </div>
  </q-card-actions>
</q-card>
<PicDialog ref="refPicDialog" />
</template>
<script setup>
import { ref, onMounted } from "vue"
import emitter from "tiny-emitter/instance"
import { axiosFunctions } from "../src/composables/axiosFunctions"
const { gaxios, paxios } = axiosFunctions();
import { libFunctions } from "../src/composables/libFunctions"
const { isIM, isDesk } = libFunctions();
import PicDialog from './PicDialog'
const pics = ref([])
const refPicDialog = ref(null)

// ---- main starts ----------
console.log("-ST-yalipics/PicList")
onMounted(() => refPicDialog)
emitter.on("yalipics-getList", (x) => setList(x))
getList();

// ---- function section -----
function getImg(p) {
  return process.env.API + '/pics/yali/' + p
}
function getList() {
  console.log("-fn-getList")
  const path = process.env.API + "/yalipics/getList"
  gaxios(path)
}
function setList(da) {
  console.log(`-fn-setList total number of pics=${da.lst.length}`)
  pics.value = da.lst
}
</script>

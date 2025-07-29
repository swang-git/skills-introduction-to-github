<template>
<div v-show="showPiclst">
<div class="q-pa-xs text-amber text-center text-h4" @click="showPicScreenFit">娅 莉 画 展 <span class="text-h5 text-cyan-3">({{ pics.length }}幅)</span></div>
<q-card class="flex flex-center" style="background:teal">
  <q-card-actions align="between">
    <div v-for="(p, idx) in pics" :key=p class="q-px-xs">
      <img :src="getImg(p)" :height="isIM ? '171' : '150'" :width="isIM ? '171' : '150'" class="q-pa-xs cursor-pointer" @click="refPicDialog.openIt(idx, pics, dates, ratios)" />
      <!-- <img :src="getImg(p)" :height="isIM ? '171' : '150'" :width="isIM ? '171' : '150'" class="q-pa-xs cursor-pointer" @click="router.push({ path:'yalipics/slide' })" /> -->
    </div>
  </q-card-actions>
</q-card>
<PicDialog ref="refPicDialog" />
</div>
<div v-show="showScreenFit">
<!-- <PicScreenFit :piclst="pics.map(p => p.replace('_thumbnail', ''))" :datlst="dates" :ratlst="ratios" /> -->
</div>
</template>
<script setup>
import { ref, onMounted } from "vue"
import { useRouter } from 'vue-router'
const router = useRouter()
import emitter from "tiny-emitter/instance"
import { axiosFunctions } from "../src/composables/axiosFunctions"
const { gaxios, paxios } = axiosFunctions();
import { libFunctions } from "../src/composables/libFunctions"
const { isIM, isDesk } = libFunctions();
// import PicScreenFit from './PicScreenFit'
import PicDialog from './PicDialog'
const showPiclst = ref(true)
const showScreenFit = ref(false)
const pics = ref([])
const dates = ref([])
const ratios = ref([])
const refPicDialog = ref(null)

// ---- main starts ----------
console.log("-ST-yalipics/PicList")
document.title = '娅莉硬笔画'
onMounted(() => refPicDialog)
emitter.on("yalipics-getList", (x) => setList(x))
getList();

// ---- function section -----
function showPicScreenFit () {
  showScreenFit.value = true
  showPiclst.value = false
  router.replace({ path: 'yalipics/slide' })
}
function getImg(p) {
  return process.env.API + '/pics/yali/thumbnails/' + p
}
function getList() {
  console.log("-fn-getList")
  const path = process.env.API + "/yalipics/getList"
  gaxios(path)
}
function setList(da) {
  console.log(`-fn-setList total number of pics=${da.lst.length}`)
  pics.value = da.lst
  dates.value = da.dates
  ratios.value = da.ratios
}
</script>

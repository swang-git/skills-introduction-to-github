<template>
  <!-- <div v-show="showPiclst" class=bg-teal-10 inset-shadow-down @click="showPicScreenFit"> -->
  <div v-show="showPiclst" class="bg-teal-10" inset-shadow-down>
    <div class="q-pa-xs text-amber text-center text-h4">
      婭 莉 画 展 <span class="text-h5 text-cyan-3">({{ pics.length }}幅)</span>
    </div>
    <q-card class="flex flex-center" style="background: teal">
      <q-card-actions align="between">
        <div v-for="(p, idx) in pics" :key="p" class="q-px-xs">
          <img v-if="isIM" :src="getImg(p)" height="178" width="178" class="q-pa-xs cursor-pointer" @click="openPic(idx, pics, fileszs, datetms, ratios)" />
          <img v-else      :src="getImg(p)" height="150" width="150" class="q-pa-xs cursor-pointer" @click="openPic(idx, pics, fileszs, datetms, ratios)" />
          <!-- <img :src="getImg(p)" :height="isIM ? '171' : '150'" :width="isIM ? '171' : '150'" class="q-pa-xs cursor-pointer" @click="router.push({ path:'yalipics/slide' })" /> -->
          <!-- <img :src="getImg(p)" :height="isIM ? '171' : '150'" :width="isIM ? '171' : '150'" class="q-pa-xs cursor-pointer" @click="refPicDialog.openIt(idx, pics, fileszs, datetms, ratios)" /> -->
          <!-- <q-img :src="getImg(p)" lazy :height="isIM ? '171' : '150'" :width="isIM ? '171' : '150'" class="q-pa-xs cursor-pointer" @click="refPicDialog.openIt(idx, pics, datetms, ratios)" /> -->
        </div>
      </q-card-actions>
    </q-card>
  </div>
  <!-- <div v-show="showScreenFit">
    <PicScreenFit :piclst="pics.map(p => p.replace('_thumbnail', ''))" :datlst="datetms" :ratlst="ratios" />
    </div> -->
  <!-- <PicDialog ref="refPicDialog" /> -->
  <PicDialog ref="refPicDialog" />
</template>
<script setup>
import { ref } from 'vue'
// import { useRouter } from 'vue-router'
// const router = useRouter()
import emitter from 'tiny-emitter/instance'
import { axiosFunctions } from '../../src/composables/axiosFunctions.js'
const { gaxios } = axiosFunctions()
import { libFunctions } from '../../src/composables/libFunctions.js'
const { isIM, buildApp } = libFunctions()
// import PicScreenFit from './PicScreenFit'
import PicDialog from '../pages/PicDialog'
const showPiclst = ref(true)
// const showScreenFit = ref(false)
const pics = ref([])
const datetms = ref([])
const fileszs = ref([])
const ratios = ref([])
// const refPicDialog = ref(null)

// ---- main starts ----------
console.log(`-ST-yali/PicList isIM=${isIM}`)
document.title = '娅莉硬笔画'
// onMounted(() => refPicDialog)
emitter.on('yali-getList', (x) => setList(x))
buildApp('娅莉硬笔画', 'yalipics')
getList()

// ---- function section -----
// function showPicScreenFit () {
//   showScreenFit.value = true
//   showPiclst.value = false
//   router.replace({ path: 'yali/slide' })
// }

function openPic(idx, pics, fileszs, datetms, ratios) {
  emitter.emit('open-PicDialog', idx, pics, fileszs, datetms, ratios)
}
function getImg(p) {
  let picdir = isIM ? '/pics/yali/' : '/pics/yali/'
  return process.env.API + picdir + 'thumbnails/' + p
}
function getList() {
  console.log(`-fn-getList isIM=${isIM}`)
  const path = process.env.API + '/yali/getList/' + (isIM ? '1' : '0')
  gaxios(path)
}
function setList(da) {
  console.log(`-fn-setList total number of pics=${da.lst.length}`, da.lst)
  pics.value = da.lst
  datetms.value = da.datetms
  fileszs.value = da.fsz
  ratios.value = da.ratios
}
</script>

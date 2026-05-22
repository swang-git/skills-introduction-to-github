˜<template>
  <div class="bg-teal-10" inset-shadow-down>
    <div class="q-pa-xs text-amber text-center text-h4"> 婭 莉 画 展 <span class="text-h5 text-cyan-3">({{ total }}幅)</span></div>
    <q-card class="bg-teal" style="margin-top:-5px;height:35px">
      <q-card-actions align="between">
        <q-btn glossy dense style="margin-top:-8px" class="text-h6" label="接下一页" color="cyan-10" @click="loadPage(0)" />
        <div class="row" style="margin-top:-10px">
          <q-btn glossy dense class="text-h6" label="跳转到第" color="cyan-10" @click="loadPage(thePage)" />
          <q-input dense v-model="thePage" input-class="text-center" class="bg-cyan-2 q-mx-xs q-mt-xs"  style="font-size: 20px; width:44px; height:30px" />
          <span class="q-pr-xs text-h6 text-indigo-9 q-mt-sm">页</span>
        </div>
        <q-btn glossy dense style="margin-top:-10px" class="text-h6" label="最后一页" color="teal-10" @click="loadPage(lastPage)" />
      </q-card-actions>
    </q-card>
    <q-card class="flex flex-center" style="background: teal">
      <q-card-actions align="between">
        <div v-for="(p, idx) in data" :key="p" class="q-px-xs icon-wrapper">
          <!-- <img :src="getThumbnailURL(p)" :height="isIM ? IMiconSZ : DKiconSZ" :width="isIM ? IMiconSZ : DKiconSZ" class="q-pa-xs cursor-pointer" @click="showFullImage(idx)" loading="lazy" /> -->
          <img v-if="isIM" :src="getThumbnailURL(p.fnm)" :height=IMiconSZ :width=IMiconSZ class="q-pa-xs cursor-pointer" @click="showFullImage(idx)" loading="lazy" />
          <img v-else      :src="getThumbnailURL(p.fnm)" :height=DKiconSZ :width=DKiconSZ class="q-pa-xs cursor-pointer" @click="showFullImage(idx)" loading="lazy" />
        </div>
      </q-card-actions>
    </q-card>
  </div>
  <q-card class="bg-teal" style="margin-top:-5px;height:55px">
    <q-card-actions align="between">
      <q-btn glossy dense style="margin-top:-8px" class="text-h6" label="接下一页" color="cyan-10" @click="loadPage(0)" />
      <div class="row" style="margin-top:-10px">
        <q-btn glossy dense class="text-h6" label="跳转到第" color="cyan-10" @click="loadPage(thePage)" />
        <q-input dense v-model="thePage" input-class="text-center" class="bg-cyan-2 q-mx-xs q-mt-xs"  style="font-size: 20px; width:44px; height:30px" />
        <span class="q-pr-xs text-h6 text-indigo-9 q-mt-sm">页</span>
      </div>
      <q-btn glossy dense style="margin-top:-10px" class="text-h6" label="最后一页" color="teal-10" @click="loadPage(lastPage)" />
    </q-card-actions>
  </q-card>
  <PicDialog ref="refPicDialog" />
</template>
<script setup>
import { ref } from 'vue'

import emitter from 'tiny-emitter/instance'
import { axiosFunctions } from '../../src/composables/axiosFunctions.js'
const { gaxios } = axiosFunctions()
import { libFunctions } from '../../src/composables/libFunctions.js'
const { isIM, buildApp } = libFunctions()
import PicDialog from '../pages/PicDialog'
const data = ref([])
const hasMore = ref(true)
const thePage = ref(null)
const currentPage = ref(1)
const perPage = ref(30)
const total = ref(0)
const lastPage = ref(total.value / perPage.value)
const loading = ref(false)
const IMiconSZ = 185
const DKiconSZ = 150

// ---- main starts ----------
console.log(`-ST-yali/getList isIM=${isIM}`)
document.title = '娅莉硬笔画'
emitter.on('yali-getList', (x) => setList(x))
buildApp('娅莉硬笔画', 'yali')
getList(currentPage.value, perPage.value)

// ---- function section -----
/**
 * Load next page and append to existing drawings
 * This is what your "Load More" button calls
 */
const loadPage = (tPage=0) => {
  console.log(`-fn-loadPage hasMore=${hasMore.value} loading=${loading.value} tPage=${tPage} lastPage=${lastPage.value} thePage=${thePage.value}`)
  // if (hasMore.value && !loading.value) {
  if (tPage == lastPage.value) {
    data.value = []
    getList(lastPage.value, perPage.value)
  } else if (tPage == 0) {
    currentPage.value++
    if (currentPage.value >= lastPage.value) {
      currentPage.value = 1
      data.value = []
    }
    getList(currentPage.value, perPage.value)
  } else {
    data.value = []
    tPage = tPage % lastPage.value
    thePage.value = tPage
    getList(tPage, perPage.value)
  }
}

function showFullImage(idx) {
  emitter.emit('open-PicDialog', idx, data.value)
  // emitter.emit('open-PicDialog', idx, thumbnails.value, fileszs.value, datetms.value, ratios.value)
}
function getThumbnailURL(p) {
  let picdir = isIM ? '/pics/yali/' : '/pics/yali/'
  let turl = process.env.API + picdir + 'thumbnails/' + p
  // console.log(`thumbnaile.url=${turl}`)
  // return process.env.API + picdir + 'thumbnails/' + p
  return turl
}
function getList(cpage, ppage) {
  console.log(`-fn-getList isIM=${isIM}`)
  const path = process.env.API + '/yali/getList/' + cpage + '/' + ppage
  gaxios(path)
}
function setList(da) {
  console.log(`-fn-setList total=${da.total} has_more=${da.has_more} current_page=${da.current_page} last_page=${da.last_page} per_page=${da.per_page}`, da.data[0])
  console.log(`-fn-setList`, da)
  data.value = data.value.concat(da.data)
  hasMore.value = da.has_more
  currentPage.value = da.current_page
  lastPage.value = da.last_page
  perPage.value = da.per_page
  total.value = da.total
}
</script>

<!-- <style scoped>
.icon-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  gap: 8px;
}
</style> -->

˜<template>
  <q-layout view="lHh Lpr LFf">
    <q-header>
      <q-toolbar class="bg-teal-10 glossy">
        <q-toolbar-title>
          <div class="row justify-evenly">
          <q-btn v-if="isAdmin || isLocal" class="q-mt-sm float-left" round glossy dense dark size="20px" :label="admin.isOn ? 'X' : 'A'" @click = "admin.isOn = !admin.isOn" />
            <div class="text-center cursor-pointer text-yellow q-pt-sm" style="font-size:27px" @click="loadRandomPage">婭 莉 画 展({{ total }}幅)</div>
          </div>
          <q-card class="bg-teal-10" style="margin-top:10px">
            <q-card-actions align="between">
              <!-- <q-btn glossy dense class="text-h6" label="接下一页"  color="cyan-10" @click="loadPage(0)" /> -->
              <q-btn glossy dense class="text-h6" label="接下一页"  color="cyan-10" @click="appendPrevPage()" />
              <q-btn glossy dense class="text-h6" :label="pageListBeginAt" round color="cyan-10" @click="openNumPad('jump-page')" />
              <q-btn glossy dense class="text-h6" label="加上一页" color="cyan-10" v-if="pageListBeginAt>1" @click="prependNextPage()" />
              <q-btn glossy dense class="text-h6" label="最后一页"  color="cyan-10" @click="loadPage(lastPage)" />
            </q-card-actions>
          </q-card>
        </q-toolbar-title>
      </q-toolbar>
    </q-header>

    <div class="q-pt-md"> <!-- prevent from moving up for first time reloading-->
      <q-card class="flex flex-center bg-cyan-10" style="margin-top:102px">
        <q-card-actions align="between">
          <div v-for="(p, idx) in data" :key="p" class="q-px-xs">
            <img v-if="isIM" :src="getThumbnailURL(p.fnm)" :height=IMiconSZ :width=IMiconSZ class="q-pa-xs cursor-pointer" @click="showFullImage(idx)" loading="lazy" />
            <img v-else      :src="getThumbnailURL(p.fnm)" :height=DKiconSZ :width=DKiconSZ class="q-pa-xs cursor-pointer" @click="showFullImage(idx)" loading="lazy" />
          </div>
        </q-card-actions>
      </q-card>
    </div>

    <q-card class="bg-teal-10">
      <q-card-actions align="between">
        <q-btn glossy dense class="text-h6" label="接下一页"  color="cyan-10" @click="loadPage(0)" />
        <q-card-actions v-show="admin.isOn">
          <q-btn glossy dense class="text-h6" label="per page" color="cyan-10" @click="openNumPad('per-page')" />
        </q-card-actions>
        <q-btn glossy dense class="text-h6" label="跳" round color="cyan-10" @click="openNumPad('jump-page')" />
        <q-btn glossy dense class="text-h6" label="最后一页"  color="teal-10" @click="loadPage(lastPage)" />
      </q-card-actions>
    </q-card>

  </q-layout>
  <PicDialog />
</template>
<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'
import { axiosFunctions } from '../../src/composables/axiosFunctions.js'
const { gaxios } = axiosFunctions()
import { libFunctions } from '../../src/composables/libFunctions.js'
const { isIM, buildApp, isAdmin, isLocal } = libFunctions()
import PicDialog from '../pages/PicDialog'

import { useNumPadStore } from '../../src/stores/numPadStore'
const numPadStore = useNumPadStore()
import { useAdminStore } from '../../src/stores/adminStore'
const admin = useAdminStore()
emitter.on('jump-to-page', (page) => { jumpTo(page) })
emitter.on('per-page', (prpg) => { perPage.value = 0; perPage.value = prpg; data.value=[]; getPages(1, perPage.value) })

const data = ref([])
const hasMore = ref(true)
// const thePage = ref(null)
const currentPage = ref(1)
const perPage = ref(30)
const total = ref(0)
const lastPage = ref(total.value / perPage.value)
// const loading = ref(false)
// const IMiconSZ = 185 // iPhone 2 columns good
const IMiconSZ = 178 // Mate60 Good for 2 columns
const DKiconSZ = 150
const append = ref(true)
const prepend = ref(false)
var pageListBeginAt = ref(1)
// ---- main starts ----------
// console.log(`-ST-yali window.location.href=${window.location.href} isIM=${isIM}`)
// console.log(`-ST-yali window.location.hostname=${window.location.hostname} isLocal=${isLocal} isIM=${isIM}`)
// console.log(`-ST-yali domainName=${/http:\/\/vicking.cn.mt\//gi.test(window.location.href)} isIM=${isIM}`)
// console.log(`-ST-yali domainName=${/http:\/\/devx:9001\//gi.test(window.location.href)} isIM=${isIM}`)
document.title = '娅莉硬笔画'
emitter.on('yali-getPages', (x) => setList(x))
buildApp('娅莉硬笔画', 'yali')
getPages(currentPage.value, perPage.value)


// ---- function section -----
function jumpTo (page) {
  pageListBeginAt.value = page
  data.value = []
  getPages(page, perPage.value)
}

function appendPrevPage () {
  [append.value, prepend.value] = [true, false]
  let nPage = data.value.length/perPage.value
  console.log(`-CK-npage=${nPage} currentPage=${currentPage.value}`)
  getPages(pageListBeginAt.value + nPage, perPage.value)
}

function prependNextPage () {
  // let currentData = data.value
  // currentPage.value++
  [append.value, prepend.value] = [false, true]
  pageListBeginAt.value--
  getPages(pageListBeginAt.value, perPage.value)
}

function openNumPad(flag=null) {
  console.log(`-fn-openNumPad isIM=${isIM}`)
  if (flag == 'per-page') numPadStore.open('YALI_PER_PAGE', '输入每页的页数', total.value)
  else if (flag == 'jump-page') numPadStore.open('YALI_PIX_PAGE', '输入要跳转的页数', lastPage.value)
}
/**
 * Load next page and append to existing drawings
 * This is what your "Load More" button calls
 */
const loadRandomPage = () => {
  const rpage = Math.floor(Math.random() * 121) + 1;
  // console.log(`-fn-loadRandomPage hasMore=${hasMore.value} loading=${loading.value} rpage=${rpage} lastPage=${lastPage.value} thePage=${thePage.value}`)
  pageListBeginAt.value = rpage
  data.value = []
  getPages(rpage, perPage.value)
}

function showFullImage(idx) {
  emitter.emit('open-PicDialog', idx, data.value)
  // emitter.emit('open-PicDialog', idx, thumbnails.value, fileszs.value, datetms.value, ratios.value)
}
function getThumbnailURL(p) {
  let picdir = isIM ? '/pics/yali/' : '/pics/yali/'
  let turl = process.env.API + picdir + 'thumbnails/' + p
  // console.log(`thumbnaile.url=${turl}`)
  return turl
}
function getPages(cpage, ppage) {
  // console.log(`-fn-getPages isIM=${isIM}`)
  const path = process.env.API + '/yali/getPages/' + cpage + '/' + ppage
  gaxios(path)
}
function setList(da) {
  // console.log(`-fn-setList total=${da.total} has_more=${da.has_more} current_page=${da.current_page} last_page=${da.last_page} per_page=${da.per_page}`, da.data[0])
  // console.log(`-fn-setList`, da)
  if (prepend.value) data.value = da.data.concat(data.value)
  else if (append.value) data.value = data.value.concat(da.data)
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

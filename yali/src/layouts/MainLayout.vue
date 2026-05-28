˜<template>
  <q-layout view="lHh Lpr LFf" class="bg-teal-10">
    <q-header>
      <q-toolbar class="bg-teal-10 glossy">
        <q-toolbar-title>
          <div class="row justify-evenly">
          <!-- <q-btn v-if="isCleanup" class="q-mt-sm float-left" round glossy dense dark size="20px" :label="admin.isOn ? 'X' : 'A'" @click = "admin.isOn = !admin.isOn" /> -->
            <div class="text-center cursor-pointer text-yellow q-pt-sm" style="font-size:27px" @click="loadRandomPage">婭 莉 画 展({{ total }}幅)</div>
          </div>
          <q-card class="bg-teal-10" style="margin-top:10px">
            <q-card-actions align="between">
              <RoundButton size="16px" icon="头" clas="q-pb-sm" :colr="pageBegin==1 && numPages==1 ? 'pink-3' : 'red-10'" iclr="yellow" ttip="just show the first page" @click="getFirstPage" />
              <RoundButton size="16px" icon="upload" :colr="pageBegin+numPages>lastPage ? 'pink-3' : 'red-10'" iclr="yellow" ttip="appending next page(on end)" @click="appnNextPage" />
              <!-- <RoundButton v-if="pageBegin==1 && numPages==1" size="16px" icon="跳" clas="q-pb-sm" colr="indigo-10" iclr="yellow" ttip="跳转到某页" @click="openNumPad('jump-page')" /> -->
              <RoundButton v-if="pageBegin==1 && numPages==1" size="16px" :icon="compVer" clas="q-pb-sm" colr="indigo-10" iclr="yellow" ttip="跳转到某页" @click="openNumPad('jump-page')" />
              <q-btn v-else flat round  dense size="22px" style="width:80px; justify-content:center" :label=getLabel() color="cyan-3" @click="openNumPad('jump-page')" />
              <RoundButton size="16px" icon="download" :colr="pageBegin>1 ? 'red-10' : 'pink-3'" iclr="yellow" ttip="preppend the prev page(on top)" @click="prepnPrevPage" />
              <RoundButton size="16px" icon="尾" clas="q-pb-sm" :colr="pageBegin==lastPage && numPages==1 ? 'pink-3' : 'red-10'" iclr="yellow" ttip="just the last page" @click="getLastPage" />
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

    <q-footer>
      <q-toolbar class="bg-teal-10">
        <q-toolbar-title>
          <q-card class="bg-teal-10" style="margin-top:10px">
            <q-card-actions align="between">
              <RoundButton size="16px" icon="头" clas="q-pb-sm" :colr="pageBegin==1 && numPages==1 ? 'pink-3' : 'red-10'" iclr="yellow" ttip="just show the first page" @click="getFirstPage" />
              <RoundButton size="16px" icon="upload" :colr="pageBegin+numPages>lastPage ? 'pink-3' : 'red-10'" iclr="yellow" ttip="appending next page(on end)" @click="appnNextPage" />
                <RoundButton v-if="pageBegin==1 && numPages==1" size="16px" icon="跳" clas="q-pb-sm" colr="indigo-10" iclr="yellow" ttip="跳转到某页" @click="openNumPad('jump-page')" />
                <q-btn v-else flat round  dense size="22px" style="width:80px; justify-content:center" :label=getLabel() color="cyan-3" @click="openNumPad('jump-page')" />
                <RoundButton size="16px" icon="download" :colr="pageBegin>1 ? 'red-10' : 'pink-3'" iclr="yellow" ttip="preppend the prev page(on top)" @click="prepnPrevPage" />
                <RoundButton size="16px" icon="尾" clas="q-pb-sm" :colr="pageBegin==lastPage && numPages==1 ? 'pink-3' : 'red-10'" iclr="yellow" ttip="just the last page" @click="getLastPage" />
                <!-- <q-btn v-if="compVer==null" round outline class="q-pb-sm text-cyan" icon="中" />
                <q-btn v-else round outline :icon="compVer" class="q-pb-sm text-cyan text-bold" /> -->
            </q-card-actions>
          </q-card>

          <!-- <q-card v-if="isDesk" class="bg-teal-10" style="margin-top:10px">
            <q-card-actions align="between">
              <q-btn glossy dense class="text-h6" label="看第一页"  color="cyan-10" :style="{ 'visibility': pageBegin==1 ? 'hidden' : 'visible' }"  @click="getFirstPage" />
              <q-btn v-if="compVer==null" round outline class="q-pb-sm text-cyan" icon="中" />
              <q-btn v-else round outline :icon="compVer" class="q-pb-sm text-cyan text-bold" />
              <q-btn glossy dense class="text-h6" label="接下一页" color="cyan-10" :style="{ 'visibility': pageBegin+numPages==lastPage ? 'hidden' : 'visible' }" @click="appnNextPage()" />
              <q-btn flat   dense class="text-h6" style="width:80px;justify-content:center" :label=getLabel() color="cyan-3" @click="openNumPad('jump-page')" />
              <q-btn glossy dense class="text-h6" label="加上一页" color="cyan-10"  :style="{ 'visibility': pageBegin<=1 ? 'hidden' : 'visible' }" @click="prepnPrevPage()" />
              <q-btn glossy dense class="text-h6" label="最后一页" color="cyan-10" :style="{ 'visibility': pageBegin>=lastPage ? 'hidden' : 'visible'}" @click="getLastPage" />
            </q-card-actions>
          </q-card>
          <q-card v-else class="bg-teal-10" style="margin-top:10px">
            <q-card-actions align="between">
              <q-btn glossy dense round class="text-h6" icon="头" color="cyan-10" :disable="pageBegin==1" @click="getFirstPage" />
              <q-btn glossy dense round class="text-h6" icon="chevron_right" color="cyan-10" :disable="pageEnd==lastPage" @click="appnNextPage()" />
              <q-btn flat   dense round class="text-h6" style="width:80px;justify-content:center" :label=getLabel() color="cyan-3" @click="openNumPad('jump-page')" />
              <q-btn glossy dense round class="text-h6" icon="chevron_left"  color="cyan-10"  :disable="pageBegin<=1" @click="prepnPrevPage()" />
              <q-btn glossy dense round class="text-h6" icon="尾" color="cyan-10" :disable="pageBegin>=lastPage" @click="getLastPage" />
            </q-card-actions>
          </q-card> -->
        </q-toolbar-title>
      </q-toolbar>
    </q-footer>
  </q-layout>
  <PicDialog />
</template>
<script setup>
import { ref, computed } from 'vue'
import emitter from 'tiny-emitter/instance'
import { axiosFunctions } from '../../src/composables/axiosFunctions.js'
const { gaxios } = axiosFunctions()
import { libFunctions } from '../../src/composables/libFunctions.js'
const { isIM, buildApp } = libFunctions()
import PicDialog from '../pages/PicDialog'
import RoundButton from '../../src/components/RoundButton'

import { useNumPadStore } from '../../src/stores/numPadStore'
const numPadStore = useNumPadStore()
import { useAdminStore } from '../../src/stores/adminStore'
const admin = useAdminStore()
emitter.on('jump-to-page', (page) => { jumpTo(page) })
emitter.on('per-page', (prpg) => { perPage.value = 0; perPage.value = prpg; data.value=[]; getPages(1, perPage.value) })

const data = ref([])
const hasMore = ref(true)
const perPage = ref(30)
const total = ref(0)
const lastPage = ref(total.value / perPage.value)
const IMiconSZ = 178 // Mate60 Good for 2 columns
const DKiconSZ = 150
const append = ref(true)
const prepend = ref(false)
const pageBegin = ref(1)
// const pageEnd = ref(-1)
const numPages = ref(1)
// ---- main starts ----------
// console.log(`-ST-yali window.location.hostname=${window.location.hostname} isLocal=${isLocal} isIM=${isIM}`)
document.title = '娅莉硬笔画'
emitter.on('yali-getPages', (x) => setPages(x))
buildApp('娅莉硬笔画', 'yali')
getPages(pageBegin.value, perPage.value)

admin.isCleanup = ref(window.location.href.substring(window.location.href.length - 2) == '//')
console.log(`-ST-yali hostname=${window.location.hostname} href=${window.location.href.substring(window.location.href.length - 2)} isCleanup=${admin.isCleanup}`)

// ---- function section -----
const compVer = computed(() => { return process.env.VER })
// const compNumPages = computed({ get() { return Math.ceil(data.value.length/perPage.value) }, set(val) { numPages.value = val } })

function getFirstPage () {
  if (pageBegin.value == 1 && numPages.value == 1) {
    console.log(`first page already showed pageBegin=${pageBegin.value} numPages=${numPages.value} return`)
    return
  }
  pageBegin.value = 1
  numPages.value = 1
  data.value = []
  getPages(1, perPage.value)
}

function getLastPage () {
  if (pageBegin.value + numPages.value == lastPage.value && numPages.value == 1) {
    console.log(`last page already showed pageBegin=${pageBegin.value} numPages=${numPages.value} return`)
    return
  }
  pageBegin.value = lastPage.value
  numPages.value = 1
  data.value = []
  getPages(lastPage.value, perPage.value)
}

function getLabel () {
  if (pageBegin.value == 1 && numPages.value == 1) return compVer.value
  let pageEnd = pageBegin.value + numPages.value - 1
  // console.log(`-fn-getLabel() compNumPages=${compNumPages.value} pageBegin=${pageBegin.value} pageEnd=${pageEnd.value}`)
  console.log(`-fn-getLabel() numPages=${numPages.value} pageBegin=${pageBegin.value}`)
  // let ret = pageBegin.value == numPages.value == 1 ? [pageBegin.value] : [pageBegin.value, pageEnd]
  let ret = numPages.value == 1 ? [pageBegin.value] : [pageBegin.value, pageEnd]
  if (ret.length == 1) return ret[0]
  else if (ret[1] == ret[0]) return ret[0]
  else return ret[0] + '~' + ret[1]
}

function jumpTo (page) {
  if (page == 0) page = 1
  console.log(`-fn-jumpTo page=${page}`)
  pageBegin.value = page % lastPage.value
  numPages.value = 1
  data.value = []
  getPages(pageBegin.value, perPage.value)
}

function appnNextPage () {
  [append.value, prepend.value] = [true, false]
  console.log('-fn-appnNextPage')
  getPages(pageBegin.value + numPages.value, perPage.value)
}

function prepnPrevPage () {
  // let currentData = data.value
  if (pageBegin.value == 1) {
    console.log(`already reached the top end do nothing: pageBegin=${pageBegin.value}, return...`)
    return
  }
  [append.value, prepend.value] = [false, true]
  pageBegin.value--
  numPages.value++
  getPages(pageBegin.value, perPage.value)
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
  pageBegin.value = rpage
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
  console.log(`-fn-getPages pageBegin=${pageBegin.value} cpage=${cpage} ppage=${ppage}`)
  if (total.value > 0 && cpage > lastPage.value) {
    console.log(`already reached the end of pages cpage=${cpage} > lastPage, return ...`)
    return
  }
  const path = process.env.API + '/yali/getPages/' + cpage + '/' + ppage
  gaxios(path)
}

function setPages(da) {
  // console.log(`-fn-setPages total=${da.total} has_more=${da.has_more} current_page=${da.current_page} last_page=${da.last_page} per_page=${da.per_page}`, da.data[0])
  console.log(`-fn-setPages total=${da.total} has_more=${da.has_more} current_page=${da.current_page} last_page=${da.last_page}`)
  if (prepend.value) data.value = da.data.concat(data.value)
  else if (append.value) data.value = data.value.concat(da.data)
  numPages.value = Math.ceil(data.value.length/perPage.value)
  console.log(`-fn-setPages numPages=${numPages.value}`)
  hasMore.value = da.has_more
  // currentPage.value = da.current_page
  lastPage.value = da.last_page
  perPage.value = da.per_page
  total.value = da.total
}
</script>

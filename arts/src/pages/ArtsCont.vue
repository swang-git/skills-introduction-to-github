<template>
  <div class="q-pa-md">
    <q-page>
      <q-item :id="'cont_' + i" v-for="(lnk, i) in data.links" :key="lnk.x" :to="{ name: 'text', params: { tag: lnk.tag, ymd: lnk.ymd, qid: lnk.qid } }" @click="setClickedArt(i)" >
        <q-item-section>
          <q-item-label :class="{ 'dim-index': highlit === i, 'lit-index': highlit !== i }">
            <span style="font-family:stzhongs" class="text-yellow text-h4">{{ i + 1 }}.</span>
            <span style="font-size:24.8px;font-family:stzhongs">{{ data.titles[i] }}</span>
          </q-item-label>
          <q-item-label class="subtits" caption>{{ data.subtits[i].replace(/\(|\W.\W.\W.\W\)/g, '') }}</q-item-label>
        </q-item-section>
        <span v-if="data.cons[i] === 'photo'" ><q-icon name="photo" color="cyan-3" size="md" /></span>
        <span v-else-if="data.cons[i] === 'videocam'" ><q-icon name="videocam" color="yellow-3" size="md" /></span>
        <q-icon :name="data.cons[i]" color="cyan-3" size="md" />
      </q-item>
      <q-footer reveal elevated bordered v-model="footerState">
        <q-toolbar class="bg-teal-10 glossy" style="height: 30px">
          <q-toolbar-title class="row" style="padding: 2px 0 0 10px">
            <div class="col-12 q-pt-xs" align="right">
              <q-btn flat round dense icon="arrow_back" @click="showPrev" :class="isPrevActive" />
              <span class="q-pl-md q-pr-md">共 {{ totalArts }} 篇</span>
              <q-btn flat round dense icon="arrow_forward" @click="showNext" :class="isNextActive" />
            </div>
          </q-toolbar-title>
        </q-toolbar>
      </q-footer>
      <q-page-scroller position="bottom-right" :scroll-offset="350" :offset="[0, -5]" >
        <q-btn fab icon="keyboard_arrow_up" color="accent" />
      </q-page-scroller>
    </q-page>
  </div>
</template>

<script setup>
import { useRoute, useRouter } from 'vue-router'
const route = useRoute()
const $router = useRouter()
import emitter from 'tiny-emitter/instance'
import { ref, computed, watch } from 'vue'
import { libFunctions } from '../composables/libFunctions'
const { DEV_API } = libFunctions()
import { axiosFunctions } from '../composables/axiosFunctions'
const { gaxios } = axiosFunctions()
import { scroll } from 'quasar'
const { getScrollTarget, setVerticalScrollPosition } = scroll

import { useArtStore } from '../stores/art.js'
const store = useArtStore()

const footerState = ref(true) // might be used some times
const prevYmd = ref(undefined)
const nextYmd = ref(undefined)
const tag = ref(undefined)
const ymd = ref(undefined)
const data = ref({})

// const compData = computed(() => { return data.value })

emitter.on('get-cont', () => getCont())
emitter.on('arts-getCont', da => setCont(da))
emitter.on('arts-searchATT', da => { console.log('call setSearched'); setSearched(da) })
// emitter.on('back-to-search', () => { console.log('back-to-search'); backToSearch() })

const isPrevActive = computed(() => { return prevYmd.value === undefined ? 'invisible' : 'visible' })
const isNextActive = computed(() => { return nextYmd.value === undefined ? 'invisible' : 'visible' })
const contKey = computed(() => { return tag.value + ymd.value })
// const contKey = computed(() => { return store.tag + store.ymd })
// const highlit = computed(() => { return store.clickedArt[tag.value + ymd.value] })
// const highlit = computed(() => { return store.clickedArt[contKey.value] })
// const highlit = computed(() => { return store.clickedArt[store.clickedCont.key] })
// const highlit = computed(() => { return store.clickedIndex })
// const highlit = computed(() => { return store.clickedArt[store.clickedCont.key] })
const highlit = computed(() => { return store.clickedIdx[contKey.value] })
const totalArts = computed(() => { return data.value.titles === undefined ? 0 : data.value.titles.length })
// const contKey = computed(() => { return '/' + tag.value + '/' + ymd.value })
// const contKey = computed({
//   get: () => tag.value + ymd.value,
//   set: (val) => contKey.value = val
// })

console.info(`-ST-ArtCont contKey=${contKey.value}`)
getCont()
setTimeout(() => { scrollToClickedCont() }, 190)

function setSearched(da) {
  // console.log(`-fn-setSearched`, compData.value.links, compData.value.titles, compData.value.subtits)
  let x = route.path.split('/')
  tag.value = x[1]
  ymd.value = x[2]
  console.log(`-fn-setSearched contKey=${contKey.value} route.path=${route.path}`)
  store.isSearch = true
  store.searchCat = tag.value
  store.searchTxt = ymd.value
  data.value = da.cont
  store.topTit = da.cont.topTitle
  document.title = store.topTit
  store.addContDict(contKey.value, da.cont)
  store.clickedCont = store.contDict[contKey.value]
  store.qids[contKey.value] = da.cont.links.map(p => p.qid)
  // data.value.ymds = da.cont.links.map(p => p.ymd)
  console.log(`-fn-setSearched`, store.contDict, store.qids)
  // setPrevNextYmds()
}
function setCont(da) {
  if (store.isSearch) return
  tag.value = route.params.tag
  ymd.value = route.params.ymd
  // data.value = da.cont
  // console.log(`-fn-%csetCont befor: contDict=`, 'color:pink', store.contDict)
  store.contDict[contKey.value] = da.cont
  data.value = store.contDict[contKey.value]
  console.log(`-fn-%csetCont contKey=${contKey.value} after: contDict=`, 'color:pink', store.contDict)
  store.qids[contKey.value] = da.cont.links.map(p => p.qid)
  store.topTit = da.cont.topTitle
  document.title = da.cont.topTitle
  setPrevNextYmds()
}
function setClickedArt(i) {
  store.clickedIdx[contKey.value] = i
  // const ckey = store.clickedCont.key
  // console.log(`-fn-setClickedArt %c idx=${store.clickedIdx[contKey.value]} store.clickedCont.key=${ckey} idx=${store.clickedArt[ckey]}`, 'color:pink')
}
function getCont () {
  tag.value = route.params.tag
  ymd.value = route.params.ymd
  let x = store.contDict[contKey.value]
  console.log(`-fn-getCont isSearch=${store.isSearch} contKey=${contKey.value}`)
  // if (store.isSearch) return
  if (store.isSearch && x != undefined) {
    data.value = x
    store.topTit = x.topTitle
    document.title = store.topTit
    return
  }
  
  let contx = store.contDict[contKey.value]
  console.log(`contDict[${contKey.value}]`, contx)
  if (contx != undefined) {
    store.clickedCont = contx
    console.log(`getCont %ccontx.key=${contx.key} contKey=${contKey.value}`, "color:red")
    if (contx.key == contKey.value) {
      data.value = contx
      store.topTit = contx.topTitle
      document.title = contx.topTitle
      setTimeout(() => { scrollToClickedCont() }, 190)
      return
    } 
  }
  const path = DEV_API + '/arts/getCont/' + tag.value + '/' + ymd.value
  gaxios(path)
}
function XXXgetCont() {
  const contx = store.clickedCont
  // console.log(`-fn-getCont store.clickedIndex=${store.clickedIndex}`, contx)
  if (contx != null) {
    data.value = contx
    store.topTit = contx.topTitle
    document.title = contx.topTitle
    setTimeout(() => { scrollToClickedCont() }, 190)
    console.log(`-fn-%cgetCont store.clickedIndex=${store.clickedIndex}`, "color:pink", contx.links)
    return
  }
  // const path = DEV_API + '/arts/getCont/' + tag.value + '/' + ymd.value
  const path = DEV_API + '/arts/getCont' + route.path
  gaxios(path)
}

watch(
  () => route.path, // Watch the `path` property of the route
  (newPath, oldPath) => {
    console.log(
      `%cwatch(in ArtsCont): route changed from ${oldPath} to ${newPath}`,
      'color:pink'
    )
    let x = newPath.split('/')
    tag.value = x[1]
    ymd.value = x[2]
    console.log( `%cwatch(in ArtsCont): tag=${tag.value} ymd=${ymd.value}`, 'color:lime')
    store.isSearch = false
    getCont()
  }
)

function showPrev() {
  $router.push({ name: 'cont', params: { tag: tag.value, ymd: prevYmd.value } })
}
function showNext() {
  $router.push({ name: 'cont', params: { tag: tag.value, ymd: nextYmd.value } })
}
function setPrevNextYmds() {
  // console.log(`-fn-setPrevNextYmds prevYmd=${prevYmd.value} ymd=${ymd.value}`, data.value.ymds)
  if (data.value.ymds.length <= 0) {
    prevYmd.value = undefined
    nextYmd.value = undefined
    return
  }
  prevYmd.value = data.value.ymds.filter(d => d < ymd.value).shift()
  nextYmd.value = data.value.ymds.filter(d => d > ymd.value).pop()
  // console.log(`prevYmd=${prevYmd.value} ymd=${ymd.value}`)
  // console.log(`nextYmd=${nextYmd.value}`)
}

function scrollToClickedCont () {
  const ele = getElement.value // You need to get your element here
  // console.log(`-fn-scrollToClickedCont`, ele)
  if (ele != null) {
    const target = getScrollTarget(ele)
    const offset = ele.offsetTop - ele.scrollHeight - 150
    const duration = 200
    setVerticalScrollPosition(target, offset, duration)
  }
}
const getElement = computed(() => {
  // const idx = store.clickedIndex
  // const tagymd = tag.value + ymd.value
  // const idx = store.clickedArt[tagymd]
  const idx = store.clickedIdx[contKey.value]
  // const idx = store.clickedArt[store.clickedCont.key]
  const elId = 'cont_' + idx
  const ele = document.getElementById(elId)
  console.log(`-cp-%cgetElement idx=${idx} elId=${elId}`, 'color:pink')
  return ele
})
</script>

<style>
.q-toolbar-title {
  font-family: simyou;
  font-size: 20px;
  font-weight: 700;
  line-height: 1.6;
  padding: 5px 20px 5px 20px;
  text-align: justify;
}
.q-item-label {
  font-family: simyou;
  font-size: 21.9px;
  font-weight: 600;
  color: white;
}
.subtits {
  font-family: stzhongso;
  font-size: 17.5px;
  font-weight: 500;
  color: yellow;
}
.lit-index {
  font-family: stfangso;
  font-size: 26px;
  font-weight: 400;
  color: lightcyan;
}
.dim-index {
  font-family: simyou;
  /* font-family: stfangso; */
  font-size: 26px;
  font-weight: 400;
  color: rgb(209, 176, 176);
}
html {
  overflow: scroll;
  overflow-x: hidden;
  -ms-overflow-style: none;
  scrollbar-width: none;
  /* -moz-overflow: hidden; */
}
::-webkit-scrollbar {
  width: 0px; /* Remove scrollbar space */
  background: transparent; /* Optional: just make scrollbar invisible */
}
/* Optional: show position indicator in red */
::-webkit-scrollbar-thumb {
  background: #ff0000;
}
::-moz-scrollbar {
  width: 0px; /* Remove scrollbar space */
  background: transparent; /* Optional: just make scrollbar invisible */
}
/* Optional: show position indicator in red */
::-moz-scrollbar-thumb {
  background: #ff0000;
}
::-ms-scrollbar {
  width: 0px; /* Remove scrollbar space */
  background: transparent; /* Optional: just make scrollbar invisible */
}
/* Optional: show position indicator in red */
::-ms-scrollbar-thumb {
  background: #ff0000;
}
</style>

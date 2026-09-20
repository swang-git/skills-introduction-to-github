<template>
  <div class="q-pa-md">
    <q-page>
      <q-item :id="'cont_' + i" v-for="(lnk, i) in compData.links" :key="lnk.x" :to="{ name: 'text', params: { tag: lnk.tag, ymd: lnk.ymd, qid: lnk.qid } }" @click="setClickedArt(i)" >
        <q-item-section>
          <q-item-label :class="{ 'dim-index': highlit === i, 'lit-index': highlit !== i }">
            <span style="color: lime">{{ i + 1 }}.</span>
            <span class="text-bold" style="font-size: 28.8px">{{ compData.titles[i] }}</span>
          </q-item-label>
          <q-item-label class="subtits" caption>{{ compData.subtits[i].replace(/\(|\W.\W.\W.\W\)/g, '') }}</q-item-label>
        </q-item-section>
        <span v-if="compData.cons[i] === 'photo'" ><q-icon name="photo" color="cyan-3" size="md" /></span>
        <span v-else-if="compData.cons[i] === 'videocam'" ><q-icon name="videocam" color="yellow-3" size="md" /></span>
        <q-icon :name="compData.cons[i]" color="cyan-3" size="md" />
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
const { store, DEV_API } = libFunctions()
import { axiosFunctions } from '../composables/axiosFunctions'
const { gaxios } = axiosFunctions()
import { scroll } from 'quasar'
const { getScrollTarget, setVerticalScrollPosition } = scroll

const footerState = ref(true) // might be used some times
const prevYmd = ref(undefined)
const nextYmd = ref(undefined)
const tag = ref(undefined)
const ymd = ref(undefined)
const data = ref({})

const compData = computed(() => { return data.value })

emitter.on('get-cont', () => getCont())
emitter.on('arts-getCont', da => setCont(da))
emitter.on('arts-searchATT', da => { console.log('call setSearched'); setSearched(da) })
// emitter.on('back-to-search', () => { console.log('back-to-search'); backToSearch() })

const isPrevActive = computed(() => { return prevYmd.value === undefined ? 'invisible' : 'visible' })
const isNextActive = computed(() => { return nextYmd.value === undefined ? 'invisible' : 'visible' })
const highlit = computed(() => { return store.clickedArt[tag.value + ymd.value] })
const totalArts = computed(() => { return data.value.titles === undefined ? 0 : data.value.titles.length })
// const contKey = computed(() => { return '/' + tag.value + '/' + ymd.value })
const contKey = computed(() => { return tag.value + ymd.value })
// const contKey = computed({
//   get: () => tag.value + ymd.value,
//   set: (val) => contKey.value = val
// })

console.info(`-ST-ArtCont contKey=${contKey.value}`)
getCont()

// function backToSearch () {
//   // $router.replace({ path: '/aut/XXX' })
//   data.value = store.clickedCont
//   store.topTit = store.clickedCont.topTitle
//   document.title = store.topTit
//   console.log(`-fn-backToSearch`, store.clickedCont, data.value.links)
// }
function setSearched(da) {
  // console.log(`-fn-setSearched`, compData.value.links, compData.value.titles, compData.value.subtits)
  let x = route.path.split('/')
  tag.value = x[1]
  ymd.value = x[2]
  console.log(`-fn-setSearched contKey=${contKey.value} route.path=${route.path}`)
  store.isSearch = true
  data.value = da.cont
  store.topTit = da.cont.topTitle
  document.title = store.topTit
  store.addClicked(contKey.value, da.cont)
  // store.qids = da.cont.links.map(p => p.qid)
  // data.value.ymds = da.cont.links.map(p => p.ymd)
  console.log(`-fn-setSearched`, store.clickedCont)
  // setPrevNextYmds()
}
function setCont(da) {
  if (store.isSearch) return
  tag.value = route.params.tag
  ymd.value = route.params.ymd
  // store.topTit = da.cont.topTitle
  // document.title = store.topTit
  data.value = da.cont
  console.log(`-fn-%csetCont befor: clickedCont=`, 'color:pink', store.clickedCont)
  store.clickedCont[contKey.value] = da.cont
  // store.addClicked(contKey.value, da.cont)
  console.log(`-fn-%csetCont after: clickedCont=`, 'color:pink', store.clickedCont)
  // console.log(`-fn-setCont`, compData.value.links, compData.value.titles, compData.value.subtits)
  // console.log(`-fn-setCont`, compData.value, store.clickedCont)
  // console.log(`-fn-setCont contKey=${contKey.value} store.clickedCont=`, store.clickedCont)
  store.qids = da.cont.links.map(p => p.qid)
  // data.value.ymds = da.cont.links.map(p => p.ymd)
  store.topTit = da.cont.topTitle
  setPrevNextYmds()
}

function setClickedArt(i) {
  store.clickedIndex = i
  const tagymd = tag.value + ymd.value
  store.clickedArt[tagymd] = i
  console.log( `-fn-%csetClickedIndex idx=${store.clickedArt[tagymd]} ty=${tagymd}`, 'color:red')
}
function getCont() {
  tag.value = route.params.tag
  ymd.value = route.params.ymd
  let x = store.clickedCont[contKey.value]
  console.log(`-fn-getCont isSearch=${store.isSearch} contKey=${contKey.value}`)
  // if (store.isSearch) return
  if (store.isSearch && x != undefined) {
    data.value = x
    store.topTit = store.clickedCont.topTitle
    document.title = store.topTit
    return
  }
  
  let contx = store.clickedCont[contKey.value]
  console.log(`clickedCont[${contKey.value}]`, contx)
  if (contx != undefined) {
    console.log(`getCont contx.key=${contx.key} contKey=${contKey.value}`)
    if (contx.key == contKey.value) {
      data.value = contx
      return
    } 
  }
  const path = DEV_API + '/arts/getCont/' + tag.value + '/' + ymd.value
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
    console.log(
      `%cwatch(in ArtsCont): tag=${tag.value} ymd=${ymd.value}`,
      'color:lime'
    )
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

// function isSearch () {
//   const re = /aut|tit|txt/gi
//   return re.test(tag.value)
// }

function scrollToClickedCont() {
  const ele = getElement.value // You need to get your element here
  console.log(`-fn-scrollToClickedCont`, ele)
  if (ele != null) {
    const target = getScrollTarget(ele)
    const offset = ele.offsetTop - ele.scrollHeight - 150
    const duration = 200
    setVerticalScrollPosition(target, offset, duration)
  }
}
// function getCont () {
//   console.log('-fn-getCont', route)
//   const tagymd = route.path
//   const path = process.env.API + '/arts/getCont' + tagymd
//   gaxios(path)
//   // if (!isSearch()) searchCat.value = ''
//   // store.pageType = 'cont'
//   // const key = '/' + tag.value + '/' + ymd.value
//   // // console.warn('=ck=key:', key)
//   // var conts = store.conts
//   // // var hasPropety = Object.prototype.hasOwnProperty.call(cont, key)
//   // if (Object.prototype.hasOwnProperty.call(conts, key)) {
//   //   // console.warn('=ck=msg passed in ' + msg + ' - getCont from STORE') // for', tag.value, ymd.value)
//   //   data.value = conts[key]
//   //   store.updClickedCont = data.value
//   //   const topTit = data.value.topTitle
//   //   store.updTopTitle = topTit
//     setPrevNextYmds()
//   // } else {
//   //   // console.warn('=ck=msg' + msg + ' - getCont from DB') // for', tag.value, ymd.value)
//   //   getContFromDB()
//   // }
//   document.title = data.value.topTitle + 'XXX'
// }

// function getContFromDB () {
//   var args = {}
//   args.vm = this
//   // console.debug(' -- params', $router.params)
//   // if ($router.params.cat !== undefined) {
//   if (this.isSearch()) {
//     args.flag = 'search'
//     args.path = process.env.API + '/arts/search/' + tag.value + '/' + ymd.value
//   } else {
//     args.flag = 'cont'
//     args.path = process.env.API + '/arts/getCont/' + tag.value + '/' + ymd.value
//   }
//   axiosLoad(args)
//   // document.title = this.$store.state.arts.topTit
// }

// $router.replace({ path: '/aut/' + searchQuery.value })
// function searchAut () {
//   loadAut.value = true
//   setTimeout(() => {
//     $router.replace({ path: '/aut/' + this.searchQuery })
//     loadAut.value = false
//   }, 2000)
// }

// function searchTit () {
//   this.loadTit = true
//   setTimeout(() => {
//     $router.replace({ path: '/tit/' + this.searchQuery })
//     this.loadTit = false
//   }, 1000)
// }

// function searchTxt () {
//   this.loadTxt = true
//   setTimeout(() => {
//     $router.replace({ path: '/txt/' + this.searchQuery })
//     this.loadTxt = false
//   }, 4000)
// }

// function changeTxt () { this.searchCat = '' }
// console.info('-ST-ArtCont')
// console.info('=cr= ArtsCont params:', $router.params)

// getCont('from created()')
setTimeout(() => { scrollToClickedCont() }, 190)

// const isPrevActive = computed(() => { return prevYmd.value === undefined ? 'invisible' : 'visible' })
// const isNextActive = computed(() => { return nextYmd.value === undefined ? 'invisible' : 'visible' })
// // const isDesk = computed(() => { return is.desk() })
// // const isFone = computed(() => { return is.fone() })
// // const tag = computed(() => { return $router.params.tag })
// // const ymd = computed(() => { return $router.params.ymd })
// const highlit = computed(() => { return data.value.clickedIndex })
// const totalArts = computed(() => { return data.value.titles === undefined ? 0 : data.value.titles.length })
const getElement = computed(() => {
  // const idx = store.clickedIndex
  const tagymd = tag.value + ymd.value
  const idx = store.clickedArt[tagymd]
  const elId = 'cont_' + idx
  const ele = document.getElementById(elId)
  console.log(`-cp-%cgetElement idx=${idx} elId=${elId}`, 'color:pink', ele)
  return ele
})
</script>

<style>
.q-toolbar-title {
  font-family: youyuan;
  font-size: 20px;
  font-weight: 700;
  line-height: 1.6;
  padding: 5px 20px 5px 20px;
  text-align: justify;
}
.q-item-label {
  font-family: youyuan;
  font-size: 21.9px;
  font-weight: 600;
  color: white;
}
.subtits {
  font-family: stzhongsong;
  font-size: 17.5px;
  font-weight: 500;
  color: yellow;
}
.lit-index {
  font-family: stfangsong;
  font-size: 26px;
  font-weight: 400;
  color: lightcyan;
}
.dim-index {
  /* font-family: youyuan; */
  font-family: stfangsong;
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

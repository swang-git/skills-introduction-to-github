<template>
 <div class="q-pa-md">
  <q-page>
    <q-item :id="'cont_'+i" v-for="(lnk, i) in data.links" :key=lnk.x :to="{ name: 'text', params: {'tag':lnk.tag, 'ymd':lnk.ymd, 'qid':lnk.qid}}">
      <q-item-section>
        <q-item-label :class="{ 'dim-index':highlit===i, 'lit-index':highlit!==i }">
          <span style="color:lime">{{i+1}}.</span>
          <span class="text-bold" style="font-size:28.8px">{{ data.titles[i] }}</span>
        </q-item-label>
        <q-item-label class="subtits" caption>{{ data.subtits[i].replace(/\(|\W.\W.\W.\W\)/g, '') }} </q-item-label>
      </q-item-section>
      <!-- <span v-if="data.cons[i]==='photo'"> <q-icon name="photo" color="cyan-3" size="md" /></span> -->
      <!-- <span v-else-if="data.cons[i]==='videocam'"><q-icon name="videocam" color="yellow-3" size="md" /></span> -->
      <q-icon :name="data.cons[i]" color="cyan-3" size="md" />
    </q-item>
    <q-footer reveal elevated bordered v-model="footerState">
      <q-toolbar class="bg-teal-10 glossy" style="height:30px">
        <q-toolbar-title class="row" style="padding:2px 0 0 10px">
          <div class="col-12 q-pt-xs" align="right">
            <q-btn flat round dense icon="arrow_back" @click="showPrev" :class="isPrevActive" />
            <span class="q-pl-md q-pr-md">共 {{ totalArts }} 篇</span>
            <q-btn flat round dense icon="arrow_forward" @click="showNext" :class="isNextActive" />
          </div>
        </q-toolbar-title>
      </q-toolbar>
    </q-footer>
    <q-page-scroller position="bottom-right" :scroll-offset="350" :offset="[0, -5]">
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
import { libFunctions } from 'src/composables/libFunctions'
const { store } = libFunctions()
import { axiosFunctions } from 'src/composables/axiosFunctions'
const { gaxios } = axiosFunctions()
// import { scroll } from 'quasar'
// const { getScrollTarget, setVerticalScrollPosition } = scroll

// name: 'ArtsCont',
const footerState = ref(true) // might be used some times
const prevYmd = ref(undefined)
const nextYmd = ref(undefined)
const tag = ref(undefined)
const ymd = ref(undefined)
// const searchCat = ref('')
// const searchQuery = ref('数学')
const data = ref({})

emitter.on('get-cont', () => getCont())
emitter.on('arts-getCont', (da) => setCont(da))
function setCont(da) {
  console.log(`-fn-setCont topTitle=${da.cont.topTitle}`, da)
  tag.value = route.params.tag
  ymd.value = route.params.ymd
  data.value = da.cont
  // document.title = data.value.topTitle
  document.title = da.cont.topTitle
  store.clickedCont = da.cont
  store.topTit = da.cont.topTitle
  setPrevNextYmds()
}

const isPrevActive = computed(() => { return prevYmd.value === undefined ? 'invisible' : 'visible' })
const isNextActive = computed(() => { return nextYmd.value === undefined ? 'invisible' : 'visible' })
const highlit = computed(() => { return data.value.clickedIndex })
const totalArts = computed(() => { return data.value.titles === undefined ? 0 : data.value.titles.length })

console.info('-ST-ArtCont')
getCont()

function getCont () {
  console.log('-fn-getCont', route)
  tag.value = route.params.tag
  ymd.value = route.params.ymd
  const path = process.env.API + '/arts/getCont/' + tag.value + '/' + ymd.value
  gaxios(path)
}

watch(
  () => route.path, // Watch the `path` property of the route
  (newPath, oldPath) => {
    console.log('Route changed from', oldPath, 'to', newPath);
    getCont()
  }
)

function showPrev () { $router.push({ name: 'cont', params: { tag: tag.value, ymd: prevYmd.value } }) }
function showNext () { $router.push({ name: 'cont', params: { tag: tag.value, ymd: nextYmd.value } }) }

function setPrevNextYmds () {
  if (data.value.ymds.length <= 0) {
    prevYmd.value = undefined
    nextYmd.value = undefined
    return
  }
  prevYmd.value = data.value.ymds.filter(d => d < ymd.value).shift()
  nextYmd.value = data.value.ymds.filter(d => d > ymd.value).pop()
  console.log(`prevYmd=${prevYmd.value} ymd=${ymd.value}`)
  console.log(`nextYmd=${nextYmd.value}`)
}

// function isSearch () {
//   const re = /aut|tit|txt/gi
//   return re.test(tag.value)
// }

// function scrollToClickedCont () {
//   const ele = getElement.value // You need to get your element here
//   if (ele !== null) {
//     const target = getScrollTarget(ele)
//     const offset = ele.offsetTop - ele.scrollHeight - 150
//     const duration = 200
//     setVerticalScrollPosition(target, offset, duration)
//   }
// }
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
// // console.info('=cr= ArtsCont params:', $router.params)
// getCont('from created()')
// setTimeout(() => {
//   scrollToClickedCont()
// }, 50)

// const isPrevActive = computed(() => { return prevYmd.value === undefined ? 'invisible' : 'visible' })
// const isNextActive = computed(() => { return nextYmd.value === undefined ? 'invisible' : 'visible' })
// // const isDesk = computed(() => { return is.desk() })
// // const isFone = computed(() => { return is.fone() })
// // const tag = computed(() => { return $router.params.tag })
// // const ymd = computed(() => { return $router.params.ymd })
// const highlit = computed(() => { return data.value.clickedIndex })
// const totalArts = computed(() => { return data.value.titles === undefined ? 0 : data.value.titles.length })
// const getElement = computed(() => {
//   const idx = data.value.clickedIndex
//   const elId = 'cont_' + idx
//   const ele = document.getElementById(elId)
//   return ele
// })

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
    width: 0px;  /* Remove scrollbar space */
    background: transparent;  /* Optional: just make scrollbar invisible */
}
/* Optional: show position indicator in red */
::-webkit-scrollbar-thumb {
    background: #FF0000;
}
::-moz-scrollbar {
    width: 0px;  /* Remove scrollbar space */
    background: transparent;  /* Optional: just make scrollbar invisible */
}
/* Optional: show position indicator in red */
::-moz-scrollbar-thumb {
    background: #FF0000;
}
::-ms-scrollbar {
    width: 0px;  /* Remove scrollbar space */
    background: transparent;  /* Optional: just make scrollbar invisible */
}
/* Optional: show position indicator in red */
::-ms-scrollbar-thumb {
    background: #FF0000;
}
</style>

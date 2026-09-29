<template>
  <div id="pageId">
    <q-scroll-observer @scroll="scrollHandler" />
    <div v-if="isLocal" class="text-center text-lime cursor-pointer text-h6" @click="openArtLink()" >
      <q-item-section>
        <q-item-label>{{ art.sub }}</q-item-label>
      </q-item-section>
    </div>
    <q-item v-else-if="isIM" class="text-center cursor-pointer text-lime" @click="openArtLink()" >
      <q-item-section>
        <q-item-label class="truncate">{{ art.sub }}</q-item-label>
      </q-item-section>
    </q-item>
    <q-item v-else class="text-center text-h6 text-cyan-2">
      <q-item-section>
        <q-item-label>{{ art.sub }}</q-item-label>
      </q-item-section>
    </q-item>
    <div class="arts-text" v-html="getArtTxt()" />
    <hr v-if="flw.length > 0" />
    <div v-for="(ff, i) in flwups" :key="ff.x">
      <div class="arts-text" v-html="ff.txt" />
      <div class="arts-sub" v-if="isLocal" style="cursor: pointer" @click="editFlw(i)" >
        {{ ff.sub }}
      </div>
      <div class="arts-sub" v-else>{{ ff.sub }}</div>
    </div>
    <hr />
    <br />
    <q-footer elevated bordered v-model="footerState">
      <q-toolbar class="bg-teal-9 glossy">
        <q-btn v-show="isLocal" round dense flat icon="edit" @click="editTxt" />
        <span class="text-h6" style="white-space: nowrap" >第 {{ readArticle }} 篇</span>
        <q-btn dense flat @click="toggleHeadEnd">
          <q-knob :angle="90" v-model="readPercent" size="30px" :thickness="0.33" color="orange" track-color="white" />
        </q-btn>
        <q-toolbar-title />
        <span class="cursor-pointer text-yellow text-h6 nowrap" @click="backToCont()">{{ getSub() }}</span>
        <q-toolbar-title />
        <q-btn round dense glossy icon="help_outline" @click="showArtInfo" /> &nbsp;
        <q-btn v-if="prevQid" round dense flat icon="arrow_back" @click="showPrev" />
        <q-btn v-else round dense flat />
        <q-btn round dense glossy icon="list" @click="backToCont()" /> &nbsp;&nbsp;
        <q-btn v-if="nextQid" round dense flat icon="arrow_forward" @click="showNext" />
        <q-btn v-else round dense flat />
      </q-toolbar>
    </q-footer>
  </div>
</template>

<script setup>
import emitter from 'tiny-emitter/instance'
import { openURL, scroll } from 'quasar'
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
const route = useRoute()
const $router = useRouter()
import { libFunctions } from '../../src/composables/libFunctions'
const { isDesk, isIM, isLocal, $q, DEV_API } = libFunctions()
import { axiosFunctions } from '../../src/composables/axiosFunctions'
const { gaxios } = axiosFunctions()
import { useArtStore } from '../stores/art.js'
const store = useArtStore()
// const { getScrollTarget, setVerticalScrollPosition, getScrollPosition } = scroll
const { getScrollTarget, setVerticalScrollPosition } = scroll

// name: 'ArtsText'

const footerState = ref(true) // can be controlled by user input
const art = ref({})
const flw = ref([])
const scrollElm = ref({})
const totalHeight = ref(0)
const readPctStr = ref('X')
const readPercent = ref(0)
const readArticle = ref(0)
const prevTag = ref('')
const prevYmd = ref('')
const prevQid = ref(false)
const tag = ref(null)
const ymd = ref(null)
const qid = ref(0)
// const nextTag = ref('')
// const nextYmd = ref(false)
const nextQid = ref(false)
const articlePosition = ref(null)
// destroyed () { window.removeEventListener('scroll', this.scrollHandler) // }

let handler = null
onMounted(() => {
  if (!handler) {
    handler = (da) => setText(da)
    emitter.on('arts-getText', handler)
  }
})

onUnmounted(() => {
  emitter.off('arts-getText', handler)
  handler = null
})
// emitter.on('arts-getText', da => setText(da))
// emitter.on('get-text', () => getText())

const contKey = computed(() => { return tag.value + ymd.value })

console.info('-ST-ArtsText')
// getText('-cr-ArtsText')
getText()

function getText() {
  tag.value = route.params.tag
  ymd.value = route.params.ymd
  qid.value = route.params.qid.trim()
  store.tag = tag.value
  store.ymd = ymd.value
  store.qid = qid.value
  console.log(`-fn-getText tag=${tag.value} ymd=${ymd.value} qid=${qid.value}`, store.qids)
  // console.warn('document.body.scrollHeight:', document.body.scrollHeight, 'window.innerHeight:', window.innerHeight)
  totalHeight.value = document.body.scrollHeight - window.innerHeight
  // console.warn(`totalHeight=${totalHeight.value}`)
  // setPrevNextQids()
  const path = DEV_API + '/arts/getText/' + tag.value + '/' + ymd.value + '/' + qid.value
  gaxios(path)
}
function setText(da) {
  console.log(`-fn-setText`, da.text)
  // if (da.text == null) {
  //   art.value.txt = 'article missing'
  //   return
  // }
  art.value = da.text.art
  flw.value = da.text.flw
  store.topTit = art.value.tit
  tag.value = route.params.tag
  ymd.value = route.params.ymd
  qid.value = route.params.qid
  add_api_for_testing()
  const qids = store.qids[contKey.value]
  if (qids != undefined) setPrevNextQids()
}

const flwups = computed(() => {
  if (isIM) {
    const re = /(.*)\d{4}-(.*):\d\d(\s+)/g
    flw.value.forEach(ff => { ff.sub = ff.sub.replace(re, '$1$2$3') })
  }
  return flw.value
})

function getArtTxt() {
  return art.value.txt
}
// function getArtTxt () {
//   if (tag.value === 'PXWX') return art.value.modifiedTxt
//   else return art.value.txt
//   // return art.value.modifiedTxt
// }

function openArtLink() {
  console.info(`lnk=${art.value.lnk}`)
  openURL(art.value.lnk)
  // window.location.href = art.value.lnk
  // $router.replace({ path: lnk })
}

function getSub() {
  const sub = art.value.sub
  if (!isDesk || sub === undefined) return null
  else if (sub.search(/图片.*文章字数/) >= 0)
    return sub.replace( /^\d{4}-.*\d\d:\d\d:\d\d\s+作者:(.*)\s+(\d+)图片\s+文章字数:(.*)/, '$1 $2图')
  else if (sub.search('文章字数') >= 0)
    return sub.replace( /^\d{4}-.*\s+\d\d:\d\d:\d\d\s+作者:(.*)\s+文章字数:(.*)/, '$1')
  else if (sub.indexOf('图片') >= 0)
    return sub.replace( /^\d{4}-.*\d\d:\d\d:\d\d\s+作者:(.*)\s+(\d+)图片\s+文章字数:(.*)/, '$1 $2图')
  else return sub.replace(/^\d{4}-.*\d\d:\d\d:\d\d\s+作者:(.*)/, '$1')
}

function toggleHeadEnd() {
  scrollElm.value = getScrollTarget(document.getElementById('pageId'))
  totalHeight.value = 10000 // fake the number to trigger the jump back and forth
  // console.warn(`=0=readPercent=${readPercent.value} articlePostion=${articlePosition.value} totalHeight=${totalHeight.value}`, scrollElm.value)
  if (totalHeight.value <= 0) {
    // console.info(`=1= readPercent=${readPercent.value} articlePostion=${articlePosition.value} totalHeight=${totalHeight.value}`, scrollElm.value)
    // console.info('=M= readPercent', readPercent.value, articlePosition.value, totalHeight.value, scrollElm.value.pageYOffset)
    setVerticalScrollPosition(scrollElm.value, 1, 0) // move to get document.body.scrollHeight
    setTimeout(() => {
      if (
        articlePosition.value === 'articleStart' ||
        articlePosition.value === undefined
      ) {
        articlePosition.value = 'articleEnd'
        // console.info(`=Z= readPercent=${readPercent.value} articlePostion=${articlePosition.value} totalHeight=${totalHeight.value}`, scrollElm.value)
        // console.info('=Z= readPercent', readPercent.value, articlePosition.value, totalHeight.value, scrollElm.value.pageYOffset)
        setVerticalScrollPosition(scrollElm.value, totalHeight.value, 0)
      }
    }, 100)
  } else if (articlePosition.value === 'articleStart') {
    articlePosition.value = 'articleEnd'
    // console.info('=A= readPercent', readPercent.value, articlePosition.value, totalHeight.value, scrollElm.value.pageYOffset)
    setVerticalScrollPosition(scrollElm.value, totalHeight.value, 0)
  } else {
    articlePosition.value = 'articleStart'
    // console.info('=B= readPercent', readPercent.value, articlePosition.value, totalHeight.value, scrollElm.value.pageYOffset)
    setVerticalScrollPosition(scrollElm.value, 0, 0)
  }
}

function editFlw(i) {
  store.art = art.value
  store.flw = flw.value
  store.tag = tag.value
  store.ymd = ymd.value
  store.qid = qid.value
  $router.push({
    name: 'editFlw',
    params: { tag: tag.value, ymd: ymd.value, qid: qid.value, flwIdx: i }
  })
}

function editTxt() {
  console.warn('store.art', art.value)
  store.art = art.value
  store.flw = flw.value
  store.tag = tag.value
  store.ymd = ymd.value
  store.qid = qid.value
  $router.push({
    name: 'editTxt',
    params: { tag: tag.value, ymd: ymd.value, qid: qid.value }
  })
}

function scrollHandler(scroll) {
  // scrollElm.value = this.$ids.pageId
  // scrollElm.value = getScrollTarget(this.$ids.pageId)
  scrollElm.value = getScrollTarget(document.getElementById('pageId'))
  totalHeight.value = document.body.scrollHeight - window.innerHeight
  var readPct = (100 * scroll.position.top) / totalHeight.value
  // console.warn(`=S=docHeight=${document.documentElement.scrollHeight} document.body.scrollHeight=${document.body.scrollHeight}`, scroll)
  // console.info(' == readPct, totalHeight, scroll.position', readPct.toFixed(2), totalHeight.value, scroll.position.toFixed(2))
  if (isNaN(readPct) || readPct <= 0) {
    readPercent.value = 0
    readPctStr.value = '头'
  } else if (parseFloat(readPct) > 0 && parseFloat(readPct) < 99) {
    readPercent.value = readPct
    readPctStr.value = readPct.toFixed(0) + '%'
  } else if (readPct >= 99 && readPct < 110) {
    readPctStr.value = '尾'
    readPercent.value = 100
  } else {
    readPercent.value = 100
    readPctStr.value = '溢'
  }
  // console.info(`=S= readPercent=${readPercent.value} scroll.position.top=${scroll.position.top} articlePostion=${articlePosition.value} totalHeight=${totalHeight.value}`, scrollElm.value)
}

function showArtInfo() {
  $q.notify({
    timeout: 10000,
    closeBtn: 'close',
    color: 'purple-10',
    textColor: 'yellow',
    classes: 'notify-class',
    icon: '题',
    position: 'bottom',
    html: true,
    multiLine: true,
    // message: art.value.sub
    message:
      '<strong style="font-family:youyuan">' +
      art.value.tit +
      '</strong><p><p style="font-family:stfangsong">' +
      art.value.sub
  })
}

function backToCont() {
  if (store.isSearch) {
    $router.replace({ path: '/' + store.searchCat + '/' + store.searchTxt })
    return
  }
  let x = route.path.split('/')
  const contPath = '/' + x[1] + '/' + x[2]
  $router.replace({ path: contPath })
  console.error( `-CK-backToCont route.path=${route.path}`)
}

watch(
  () => route.path, // Watch the `path` property of the route
  (newPath, oldPath) => {
    console.log('watch: Route changed from', oldPath, 'to', newPath)
    // You can perform any action here when the route changes
    // const path = process.env.API + '/arts/getCont' + newPath
    // gaxios(path)
    getText()
  }
)

function getPrevQid() {
  console.log('-fn-getPrevQid', art.value.qids)
  let ckey = contKey.value
  if (store.isSearch) ckey = store.searchCat + store.searchTxt
  const qids = store.qids[ckey]
  let idx = qids.findIndex(q => parseInt(q) == qid.value)
  const pqids = qids.slice(0, idx)
  if (idx == 0) idx = qids.length - 1
  store.clickedIdx[ckey] = idx - 1
  console.log(`%c -fn-getPrevQid store.clickedIndex=${store.clickedIndex}`, 'color:lime')
  const conty = store.contDict[ckey].links[idx - 1]
  tag.value = conty.tag
  ymd.value = conty.ymd
  return pqids.pop()
}
function getNextQid() {
  // const qids = art.value.qids
  // const qids = store.qids[tag.value + ymd.value]
  // const qids = store.clickedCont.links.map(p => p.qid)
  let ckey = contKey.value
  if (store.isSearch) ckey = store.searchCat + store.searchTxt
  const qids = store.qids[ckey]
  let idx = qids.findIndex(q => parseInt(q) == qid.value)
  if (idx >= qids.length) idx = -1
  store.clickedIdx[ckey] = idx + 1
  console.log(`%c -fn-getNextQid store.clickedIndex=${store.clickedIndex}`, 'color:pink')
  const nqids = qids.slice(idx + 1)
  const conty = store.contDict[ckey].links[idx + 1]
  // console.log(`-fn-getNextQid qid=${qid.value}`, conty)
  tag.value = conty.tag
  ymd.value = conty.ymd
  return nqids.shift()
}
function showPrev() {
  // store.clickedArt[tag.value + ymd.value]--
  // $router.replace({ name: 'text', params: { tag: prevTag.value, ymd: prevYmd.value, qid: prevQid.value } })
  qid.value = getPrevQid()
  console.log(`-fn-showPrev name:text, tag=${tag.value}, ymd=${ymd.value}, qid=${qid.value}`)
  $router.push({ path: '/' + tag.value + '/' + ymd.value + '/' + qid.value })
}

function showNext() {
  // console.log(`-fn-showNext %cstore.isSearch=${store.isSearch} nextQid=${nextQid.value} store.clickedCont.key=${store.clickedCont.key}`, 'color:red')
  // store.clickedArt[tag.value + ymd.value]++
  qid.value = getNextQid()
  // if (store.isSearch) setNextTagYmd()
  console.log(`-fn-showNext store.clickedCont.key=${store.clickedCont.key} tag=${tag.value}, ymd=${ymd.value}, qid=${qid.value}`)
  $router.push({ path: '/' + tag.value + '/' + ymd.value + '/' + qid.value })
  // $router.replace({ name: 'text', params: { tag: nextTag.value, ymd: nextYmd.value, qid:nextQid.value } })
}

function setPrevNextQids() {
  // const contx = store.contDict[contKey.value]
  let ckey = contKey.value
  if (store.isSearch) ckey = store.searchCat + store.searchTxt
  const qids = store.qids[ckey]
  console.info(`-fn-setPrevNextQids qid=[${qid.value}] contKey=${ckey} isSearch=${store.isSearch} store.tag=${store.tag} store.ymd=${store.ymd}`, store.contDict)
  // const qids = store.contDict[contKey.value].links.map(p => p.qid)
  // const qids = store.qids[contKey.value]
  if (qids.length <= 0) {
    prevQid.value = undefined
    nextQid.value = undefined
    return
  }
  // const qids = [2847364, 2847302, 2847304, 2847314, 2847340, 2847338, 2847362, 2847312, 2847300, 2847310, 2847330, 2847360]
  // const pos = qids.indexOf(qid.value)
  prevTag.value = tag.value
  prevYmd.value = ymd.value
  const pos = qids.findIndex(q => parseInt(q) == parseInt(qid.value))
  readArticle.value = pos + 1
  const pqids = qids.slice(0, pos)
  const nqids = qids.slice(pos + 1)
  // console.log(`-CK-pos=${qids.findIndex((q) => parseInt(q) == parseInt(qid.value))}`, pqids, nqids)
  prevQid.value = pqids.pop()
  nextQid.value = nqids.length > 0 ? nqids.shift() : undefined
  // console.log(`qid=${qid.value}`, pqids, nqids)
  // console.log(`prevQid=${prevQid.value}`)
  // console.log(`nextQid=${nextQid.value}`)
  store.topTit = art.value.tit
}

function add_api_for_testing() {
  if (import.meta.env.PROD) return
  console.log(`-fn-add_api_for_testing import.meta.env.PROD=${import.meta.env.PROD}`)
  var re = /<img\s+src="\/daily_data/gi
  art.value.modifiedTxt = art.value.txt.replace(re, '<img src="' + DEV_API + '/daily_data')
  art.value.txt = art.value.modifiedTxt
  flw.value.forEach(f => {
    f.txt = f.txt.replace(re, '<img src="/daily_data')
  })

  articlePosition.value = 'articleEnd' // this make sure show the begging of the article - check function toggleHeadEnd() in else block
  toggleHeadEnd()
}
</script>
<style>
img {
  max-width: 100% !important;
  width: 100% !important;
  height: auto !important;
}
.truncate {
  width: 350px;
  text-align: center;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-family: stzhongs;
  font-size: 17.5px;
  font-weight: 500;
  color: yellow;
}
.notify-class {
  font-size: 24px;
  font-weight: 600;
}
div.arts-text {
  font-family: stfangso;
  font-size: 29.5px;
  font-weight: 600;
  line-height: 1.5;
  padding: 5px 20px 5px 20px;
  text-align: justify;
  overflow-wrap: break-word;
}
.arts-sub {
  font-family: stzhongs;
  /* font-family: stfangso; */
  font-size: 20px;
  font-weight: 600;
  padding: 5px 20px 5px 20px;
  text-align: justify;
  color: rgb(252, 252, 120);
}
.calcHeight {
  visibility: hidden;
  position: absolute;
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

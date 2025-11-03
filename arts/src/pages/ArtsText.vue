<template>
  <div id="pageId">
    <q-scroll-observer @scroll="scrollHandler" />
    <div v-if="isLocal" class="text-center text-lime cursor-pointer text-h6" @click="openArtLink()">
      <q-item-section>
        <q-item-label>{{ art.sub }}</q-item-label>
      </q-item-section>
    </div>
    <q-item v-else-if="isIM" class="text-center cursor-pointer text-lime" @click="openArtLink()">
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
    <hr v-if="flw.length>0">
    <div v-for="(ff, i) in flwups" :key=ff.x >
      <div class="arts-text" v-html="ff.txt" />
      <div class="arts-sub" v-if="isLocal" style="cursor:pointer" @click="editFlw(i)">{{ff.sub}}</div>
      <div class="arts-sub" v-else>{{ff.sub}}</div>
    </div>
    <hr>
    <br />
    <q-footer elevated bordered v-model="footerState">
      <q-toolbar class="bg-teal-9 glossy">
        <q-btn v-show="isLocal" round dense flat icon="edit" @click="editTxt" />
        <span class="text-h6" style="white-space:nowrap">第 {{ readArticle }} 篇</span>
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
import { ref, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
const route = useRoute()
const $router = useRouter()
import { libFunctions } from 'src/composables/libFunctions'
const { isDesk, isIM, isLocal, $q, store} = libFunctions()
import { axiosFunctions } from 'src/composables/axiosFunctions'
const { gaxios } = axiosFunctions()
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

console.info('-ST-ArtsText')
// getText('-cr-ArtsText')
getText()

function getText () {
  tag.value = route.params.tag
  ymd.value = route.params.ymd
  qid.value = route.params.qid.trim()
  console.log(`-fn-getText tag=${tag.value} ymd=${ymd.value} qid=${qid.value}`)
  // console.warn('document.body.scrollHeight:', document.body.scrollHeight, 'window.innerHeight:', window.innerHeight)
  totalHeight.value = document.body.scrollHeight - window.innerHeight
  // console.warn(`totalHeight=${totalHeight.value}`)
  // setPrevNextQids()
  const path = process.env.API + '/arts/getText/' + tag.value + '/' + ymd.value + '/' + qid.value
  gaxios(path)
}
emitter.on('arts-getText', (da) => setText(da))
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
  setPrevNextQids()
}

const flwups = computed(() => {
  if (isIM) {
    const re = /(.*)\d{4}-(.*):\d\d(\s+)/g
    flw.value.forEach(ff => { ff.sub = ff.sub.replace(re, '$1$2$3') })
  }
  return flw.value
})

function getArtTxt () {
  return art.value.txt
}
// function getArtTxt () {
//   if (tag.value === 'PXWX') return art.value.modifiedTxt
//   else return art.value.txt
//   // return art.value.modifiedTxt
// }

function
openArtLink () {
  console.info(`lnk=${art.value.lnk}`)
  openURL (art.value.lnk)
  // window.location.href = art.value.lnk
  // $router.replace({ path: lnk })
}

function getSub () {
  const sub = art.value.sub
  if (!isDesk || sub === undefined) return null
  else if (sub.search(/图片.*文章字数/) >= 0) return sub.replace(/^\d{4}-.*\d\d:\d\d:\d\d\s+作者:(.*)\s+(\d+)图片\s+文章字数:(.*)/, '$1 $2图 $3')
  else if (sub.search('文章字数') >= 0) return sub.replace(/^\d{4}-.*\s+\d\d:\d\d:\d\d\s+作者:(.*)\s+文章字数:(.*)/, '$1$2')
  else if (sub.indexOf('图片') >= 0) return sub.replace(/^\d{4}-.*\d\d:\d\d:\d\d\s+作者:(.*)\s+(\d+)图片\s+文章字数:(.*)/, '$1 $2图 $3')
  else return sub.replace(/^\d{4}-.*\d\d:\d\d:\d\d\s+作者:(.*)/, '$1')
}

function toggleHeadEnd () {
  scrollElm.value = getScrollTarget(document.getElementById('pageId'))
  totalHeight.value = 10000 // fake the number to trigger the jump back and forth
  // console.warn(`=0=readPercent=${readPercent.value} articlePostion=${articlePosition.value} totalHeight=${totalHeight.value}`, scrollElm.value)
  if (totalHeight.value <= 0) {
    // console.info(`=1= readPercent=${readPercent.value} articlePostion=${articlePosition.value} totalHeight=${totalHeight.value}`, scrollElm.value)
    // console.info('=M= readPercent', readPercent.value, articlePosition.value, totalHeight.value, scrollElm.value.pageYOffset)
    setVerticalScrollPosition(scrollElm.value, 1, 0) // move to get document.body.scrollHeight
    setTimeout(() => {
      if (articlePosition.value === 'articleStart' || articlePosition.value === undefined) {
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

function editFlw (i) {
  store.art = art.value
  store.flw = flw.value
  store.tag = tag.value
  store.ymd = ymd.value
  store.qid = qid.value
  $router.push({ name: 'editFlw', params: { tag: tag.value, ymd: ymd.value, qid: qid.value, flwIdx: i } })
}

function editTxt () {
  console.warn('store.art', art.value)
  store.art = art.value
  store.flw = flw.value
  store.tag = tag.value
  store.ymd = ymd.value
  store.qid = qid.value
  $router.push({ name: 'editTxt', params: { tag: tag.value, ymd: ymd.value, qid: qid.value } })
}

function scrollHandler (scroll) {
  // scrollElm.value = this.$ids.pageId
  // scrollElm.value = getScrollTarget(this.$ids.pageId)
  scrollElm.value = getScrollTarget(document.getElementById('pageId'))
  totalHeight.value = document.body.scrollHeight - window.innerHeight
  var readPct = 100 * scroll.position.top / totalHeight.value
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

function showArtInfo () {
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
    message: '<strong style="font-family:youyuan">' + art.value.tit + '</strong><p><p style="font-family:stfangsong">' + art.value.sub
  })
}


function backToCont () {
  const clickedCont = store.clickedCont
  // console.error('-CK-' clickedCont', clickedCont, $router)
  $router.replace({ path: clickedCont.key })
  // $router.replace({name: 'cont', params: {tag: tag.value, ymd: ymd.value}})
  // window.location.href = clickedCont.key
}

watch(
  () => route.path, // Watch the `path` property of the route
  (newPath, oldPath) => {
    console.log('Route changed from', oldPath, 'to', newPath);
    // You can perform any action here when the route changes
    // const path = process.env.API + '/arts/getCont' + newPath
    // gaxios(path)
    getText()
  }
)

function getPrevQid () {
  // console.log('-fn-getPrevQid', art.value.qids)
  const qids = art.value.qids
  const idx = qids.findIndex((q) => parseInt(q) == qid.value)
  const pqids = qids.slice(0, idx)
  return pqids.pop()
}
function getNextQid () {
  console.log(`-fn-getNextQid qid=${qid.value}`, art.value.qids)
  const qids = art.value.qids
  const idx = qids.findIndex((q) => parseInt(q) == qid.value)
  const nqids = qids.slice(idx + 1)
  return nqids.shift()
}
function showPrev () {
  // $router.replace({ name: 'text', params: { tag: prevTag.value, ymd: prevYmd.value, qid: prevQid.value } })
  qid.value = getPrevQid()
  console.log(`-fn-showPrev name:text, tag=${tag.value}, ymd=${ymd.value}, qid=${qid.value}`)
  $router.push({ path: '/' + tag.value + '/' + ymd.value + '/' + qid.value })
}

function showNext () {
  qid.value = getNextQid()
  console.log(`-fn-showNext name:text, tag=${tag.value}, ymd=${ymd.value}, qid=${qid.value}`)
  $router.push({ path: '/' + tag.value + '/' + ymd.value + '/' + qid.value })
  // console.log(`-fn-showNext name:text, nextTag=${nextTag.value}, nextYmd=${nextYmd.value}, nextQid=${nextQid.value}`)
  // $router.replace({ name: 'text', params: { tag: nextTag.value, ymd: nextYmd.value, qid:nextQid.value } })
}

// function getText (msg) {
//   // setVerticalScrollPosition(scrollElm.value, 0, 0)
//   // document.title = store.state.arts.topTitle
//   // // store.state.arts.clickedCont.ymd = ymd.value
//   // store.commit('arts/clickedCont_ymd', ymd.value)
//   // store.commit('arts/clickedCont_tag', tag.value)
//   // store.commit('arts/clickedCont_qid', qid.value)
//   // var cont = store.state.arts.clickedCont
//   // console.info(msg)
//   // console.warn('document.body.scrollHeight:', document.body.scrollHeight, 'window.innerHeight:', window.innerHeight)
//   totalHeight.value = document.body.scrollHeight - window.innerHeight
//   // console.warn(`totalHeight=${totalHeight.value}`)
//   var lnk = cont.links
//   if (lnk === undefined) {
//     var args = {}
//     args.flag = 'cont_text'
//     args.vm = this
//     args.path = process.env.API + '/arts/getCont/' + tag.value + '/' + ymd.value
//     gaxios(args)
//   } else {
//     // lnk = store.state.arts.clickedCont.links
//     setPrevNextQids(msg)
//   }
//   getTextFromDB()
// }

function setPrevNextQids () {
  console.info(`-fn-setPrevNextQids qid=[${qid.value}]`)
  if (art.value.qids.length <= 0) {
    prevQid.value = undefined
    nextQid.value = undefined
    return
  }
  const qids = art.value.qids
  // const qids = [2847364, 2847302, 2847304, 2847314, 2847340, 2847338, 2847362, 2847312, 2847300, 2847310, 2847330, 2847360]
  // const pos = qids.indexOf(qid.value)
  prevTag.value = tag.value
  prevYmd.value = ymd.value
  const pos = qids.findIndex((q) => parseInt(q) == parseInt(qid.value))
  readArticle.value = pos + 1
  const pqids = qids.slice(0, pos)
  const nqids = qids.slice(pos + 1)
  // console.log(`-CK-pos=${qids.findIndex((q) => parseInt(q) == parseInt(qid.value))}`, pqids, nqids)
  prevQid.value = pqids.pop()
  nextQid.value = nqids.length > 0 ? nqids.shift() : undefined
  // console.log(`qid=${qid.value}`, pqids, nqids)
  console.log(`prevQid=${prevQid.value}`)
  console.log(`nextQid=${nextQid.value}`)
  // var lnk = store.clickedCont.links
  // for (var i = 0; i < lnk.length; i++) {
  //   var qx = lnk[i].qid
  //   if (qx === parseInt(qid.value)) {
  //     store.clickedIndex = i
  //     readArticle.value = i + 1
  //     prevTag.value = (lnk[i - 1] === undefined) ? lnk[lnk.length - 1].tag : lnk[i - 1].tag
  //     prevYmd.value = (lnk[i - 1] === undefined) ? lnk[lnk.length - 1].ymd : lnk[i - 1].ymd
  //     prevQid.value = (lnk[i - 1] === undefined) ? lnk[lnk.length - 1].qid : lnk[i - 1].qid
  //     nextQid.value = (lnk[i + 1] === undefined) ? lnk[0].qid : lnk[i + 1].qid
  //     nextYmd.value = (lnk[i + 1] === undefined) ? lnk[0].ymd : lnk[i + 1].ymd
  //     nextTag.value = (lnk[i + 1] === undefined) ? lnk[0].tag : lnk[i + 1].tag
  //     break
  //   }
  // }
// function setPrevNextQids () {
//   console.info('-fn-setPrevNextQids', store.clickedCont)
//   var lnk = store.clickedCont.links
//   for (var i = 0; i < lnk.length; i++) {
//     var qx = lnk[i].qid
//     if (qx === parseInt(qid.value)) {
//       store.clickedIndex = i
//       readArticle.value = i + 1
//       prevTag.value = (lnk[i - 1] === undefined) ? lnk[lnk.length - 1].tag : lnk[i - 1].tag
//       prevYmd.value = (lnk[i - 1] === undefined) ? lnk[lnk.length - 1].ymd : lnk[i - 1].ymd
//       prevQid.value = (lnk[i - 1] === undefined) ? lnk[lnk.length - 1].qid : lnk[i - 1].qid
//       nextQid.value = (lnk[i + 1] === undefined) ? lnk[0].qid : lnk[i + 1].qid
//       nextYmd.value = (lnk[i + 1] === undefined) ? lnk[0].ymd : lnk[i + 1].ymd
//       nextTag.value = (lnk[i + 1] === undefined) ? lnk[0].tag : lnk[i + 1].tag
//       break
//     }
//   }

  // console.info(' == from', msg)
  // store.state.arts.topTitle = art.value.tit
  store.topTit = art.value.tit
  // var key = tag.value + ymd.value
  // var conts = store.conts
  // if (conts !== undefined && Object.prototype.hasOwnProperty.call(conts, key)) {
  //   conts[key].clicked = qid.value
  //   // store.commit('arts/updClicked', qid.value)
  // document.title = art.value.tit
}

// function restyleImage () {
//   if (process.env.API === '') return
//   var re = /<img\s+src="\/daily_data/gi
//   if (tag.value === 'PXWX') art.value.modifiedTxt = art.value.txt.replace(re, '<img src="' + process.env.API + '/daily_data')
//   else art.value.txt = art.value.txt.replace(re, '<img src="' + process.env.API + '/daily_data')
//   // else art.value.txt = art.value.txt.replace(re, '<img src="/daily_data')
//   flw.value.forEach(f => {
//     f.txt = f.txt.replace(re, '<img src="' + process.env.API + '/daily_data')
//   })
//   // store.commit('arts/art', art.value)
//   // store.commit('arts/flw', flw.value)
//   // store.commit('arts/sub', art.value.sub)
//   // console.warn(`=wn=restyleImage readPercent=${readPercent.value} totalHeight=${totalHeight.value}`)
//   articlePosition.value = 'articleEnd' // this make sure show the begging of the article - check function toggleHeadEnd() in else block
//   toggleHeadEnd()
// }

// function testing_restyleImage_repeated_img () {
//   const rex = /<img src="(.*?)"\s+style=(.*?)0">/gi
//   console.info('== regex match', art.value.txt.match(rex))
//   art.value.txt = art.value.txt.replace(rex, '<img src="$1" class="responsive">')
// }

// function testing_restyleImage_repeated_img_look_forward_regex () {
//   const pat = '<img src="(.*)" style(.*)0">'
//   const re = new RegExp(pat + '(?=' + pat + ')')
//   art.value.txt = art.value.txt.replace(re, '<img src="' + '$1" class="responsive" />')
// }

// function testing_restyleImage_repeated_imgXXX () {
//   // var re = /<img\s+src="(.*)"\s+(.*)>/gim
//   // var re = /<img\s+src="(.*)"\s+style(.*)0">(?=<img)/gi
//   // var re = /<img(.*)>(?=<img)/gi
//   // console.log(' === matched patterns', art.value.txt.match(re))

//   var rex = /><img/gi
//   const imgRepeator = art.value.txt.match(rex).length
//   // console.info(' == matched patterns', imgRepeator, art.value.txt.match(rex))
//   // console.log(' === matched patterns', art.value.txt.match(rex))
//   // art.value.txt.replace(rex, '>\\n\\n<img')
//   var re = /<img\s+src="(.*)"\s+style=(.*)0">/gi
//   // const txt = art.value.txt
//   for (let i = 0; i <= imgRepeator; i++) {
//     // console.log(' === matched patternsXX', art.value.txt.match(re))
//     art.value.txt = art.value.txt.replace(re, '<img src="' + '$1" class="responsive" />')
//   }
//   // art.value.txt = art.value.txt.replace(re, '<img src="' + '$1" class="responsive" />')
//   flw.value.forEach(f => {
//     f.txt = f.txt.replace(re, '<img src="' + '$1" class="responsive" />')
//   })
// }

// function restyleImageForFone () {
//   const rex = /<img src="(.*?)"\s+style=(.*?)0">/gi
//   if  tag.value === 'PXWX') art.value.modifiedTxt = art.value.modifiedTxt.replace(rex, '<img src="$1" class="responsive" />')
//   else art.value.txt = art.value.txt.replace(rex, '<img src="$1" class="responsive" />')
//   // art.value.txt = art.value.txt.replace(rex, '<img src="$1" class="img-circle">')

//   flw.value.forEach(f => {
//     f.txt = f.txt.replace(rex, '<img src="$1" class="responsive">')
//   })
// }

// function restyleVideo () {
//   var rex = /<iframe(.*)src="(.*)"><\/iframe>/gi
//   if (is.desk()) art.value.txt = art.value.txt.replace(rex, '<iframe frameborder="0" allowfullscreen height="450" width="800" src="$2"></iframe>')
//   else art.value.txt = art.value.txt.replace(rex, '<iframe frameborder="0" allowfullscreen width="340" src="$2"></iframe>')
//   flw.value.forEach(f => { f.txt = f.txt.replace(rex, '<div class="q-video"><iframe frameborder="0" allowfullscreen src="$2"></iframe></div>') })
// }

// function getTextFromDB () {
//   if (qid.value == undefined) return
//   var args = {}
//   args.flag = 'text'
//   args.vm = this
//   args.path = process.env.API + '/arts/getText/' + tag.value + '/' + ymd.value + '/' + qid.value
//   // args.path = process.env.API + '/arts/getText/' + tag.value + '/' + ymd.value + '/' + 2543671
//   axiosLoad(args)
// }

// openURL,
// function checkPlatform () {
//   if (is.desk()) alert('you are running on Desktop')
//   if (is.android()) alert('you are running on Android')
//   if (is.iPhone()) alert('you are running on iPhone')
//   if (is.iPad()) alert('you are running on iPad')
//   if (is.mobile()) alert('you are running on Mobile')
//   if (is.fone()) alert('you are running on Fone')
//   if (is.safari()) alert('you are running on Safari')
//   if (is.chromeExt()) alert('you are running on ChromeExt')
//   if (is.chrome()) alert('you are running on Chrome')
//   if (is.linux()) alert('you are running on Linux')
//   if (is.firefox()) alert('you are running on Firefox')
// }
</script>

<style>
.truncate {
  width: 350px;
  text-align: center;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-family: stzhongsong;
  font-size: 17.5px;
  font-weight: 500;
  color: yellow;
}
.notify-class {
  font-size: 24px;
  font-weight: 600;
}
div.arts-text {
  font-family: stfangsong;
  font-size: 29.5px;
  font-weight: 600;
  line-height: 1.5;
  padding: 5px 20px 5px 20px;
  text-align: justify;
  overflow-wrap: break-word;
}
.arts-sub {
  /* font-family: stzhongsong; */
  font-family: stfangsong;
  font-size: 20px;
  font-weight: 600;
  padding: 5px 20px 5px 20px;
  text-align: justify;
  color: rgb(252, 252, 120);
}
.calcHeight {
  visibility: hidden;
  position:absolute;
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

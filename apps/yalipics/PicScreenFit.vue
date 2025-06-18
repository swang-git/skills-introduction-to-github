<template>
  <div class="q-py-md">
    <div style="margin:-40px 0 0 -20px"><q-btn class="z-top" flat icon="chevron_left" size="30px" color="white" @click="picidx--" /> </div>
    <div style="margin:-77px -20px 0 0;float:right"><q-btn class="z-top" flat icon="chevron_right" size="30px" color="white" @click="picidx++" /> </div>
    <img id="imgId" class="fixed" :src="getPic()" :width="getWidth()" :height="getHeight()" style="left:50%; top:50%; transform:translate(-50%, -50%)" @click="toggleSlideshow()" />
    <div class="text-center text-black text-h4 z-max">{{ datlst[picidx] }}</div>
  </div>
<TxtPad />
<NumPad @set-pic-idx="((pix) => picidx = pix % piclst.length)" @set-interval-delay="((x) => intervalDelay = x )"/>
</template>
<script setup>
import { ref, computed } from 'vue'
import emitter from 'tiny-emitter/instance'
import { libFunctions } from "../src/composables/libFunctions"
const { isIM, isDesk, isIPad } = libFunctions()
import { axiosFunctions } from "../src/composables/axiosFunctions"
const { gaxios, paxios } = axiosFunctions();
import TxtPad from "../src/components/TxtPad"
import NumPad from "../src/components/NumPad"

const opened = ref(false)
const slide = ref(0)
const autoplay = ref(true)
const fullscreen = ref(false)
const slideshowing = ref(true)
const intervalId = ref(-1)
const intervalDelay = ref(1000)
const picidx = ref(0)
const piclst = ref([])
const datlst = ref([])
const ratlst = ref([])
const imgW = ref(0)
const imgH = ref(0)
const winW = sizes().windowWidth
// const winH = sizes().windowHeight - 100
const winH = sizes().windowHeight
defineExpose({ openIt })
console.log(`-CK-winW=${winW} winH=${winH}`)
emitter.on("yalipics-getList", (x) => setList(x))

const compIntervalDelay = computed(() => { return intervalDelay.value })
getList()
showScreenFit()

function getLeftCenter () {
  // return "margin:" + (sizes().windowHeight-200)/2 + 'px' + " 0 0 " + "-50px"
  return "margin:-40px 0 0 " + "-50px"
}
function getRightCenter () {
  // const x = "margin:" + (sizes().windowHeight-200)/2 + 'px ' + (sizes().windowWidth-100) + "px 0 0"
  const x = "'margin:" + (sizes().windowHeight-200)/2 + "px 20px 0 0'"
  console.info(`-fn-getRightCenter x=${x} ww=${sizes().windowWidth-100}`)
  return x
}
function getList() {
  console.log("-fn-getList")
  const path = process.env.API + "/yalipics/getList"
  gaxios(path)
}
function setList(da) {
  console.log(`-fn-setList total number of pics=${da.lst.length}`)
  piclst.value = da.lst.map((p) => p.replace('_thumbnail', ''))
  datlst.value = da.dates
  ratlst.value = da.ratios
}

function openNumPad () {
  const filenum = picidx.value + 1
  emitter.emit('open-num-pad', 1, '要看那幅画')
}
function openTxtPad () {
  const filenum = picidx.value + 1
  emitter.emit('open-TxtPad', -10, piclst.value[picidx.value], 'filename of ' + filenum )
}

function getWidth ()  { return ratlst.value[picidx.value] < 1 ? null : winW }
function getHeight () { return ratlst.value[picidx.value] < 1 ? winH : null }

function toggleSlideshow () {
  if (slideshowing.value) {
    clearInterval(intervalId.value)
    slideshowing.value = false
    return
  } else {
    showScreenFit()
    slideshowing.value = true
  }
}
function showScreenFit () {
  console.log(`-fn-showScreenFit picidx=${picidx.value}`)
    intervalId.value = setInterval(() => {
      if (picidx.value > piclst.value.length) picidx.value = 0;
      else picidx.value++
    }, compIntervalDelay.value)
}
function slideshow () {
  if (slideshowing.value) return
  slideshowing.value = true
  console.log(`-fn-slidshow picidx=${picidx.value}`)
    intervalId.value = setInterval(() => {
      if (picidx.value > piclst.value.length) picidx.value = 0;
      else picidx.value++
    }, compIntervalDelay.value)
}
function getPic () {
  console.log(`-fn-getPic picidx=${picidx.value}`)
  const pix = process.env.API + '/pics/yali/' + piclst.value[picidx.value]
  return pix
}
// const getMeta = (url, cb) => {
//   const img = new Image();
//   img.onload = () => cb(null, img);
//   img.onerror = (err) => cb(err);
//   img.src = url;
// }
// console.log(sizes())
function sizes () {
  const contentWidth = [...document.body.children].reduce(
    (a, el) => Math.max(a, el.getBoundingClientRect().right), 0)
    - document.body.getBoundingClientRect().x;

  return {
    windowWidth:  document.documentElement.clientWidth,
    windowHeight: document.documentElement.clientHeight,
    pageWidth:    Math.min(document.body.scrollWidth, contentWidth),
    pageHeight:   document.body.scrollHeight,
    screenWidth:  window.screen.width,
    screenHeight: window.screen.height,
    pageX:        document.body.getBoundingClientRect().x,
    pageY:        document.body.getBoundingClientRect().y,
    screenX:     -window.screenX,
    screenY:     -window.screenY - (window.outerHeight-window.innerHeight),
  }
}
// function callback(err, img) {
//   imgW.value = img.width
//   imgH.value = img.height
//   console.log(`-fn-open-it idx=${picidx.value} width=${imgW.value} height=${imgH.value}`)
// }
function openIt(idx, pics, dates, ratios) {
  // console.log(sizes())
  console.log(`-fn-openIt idx=${idx}`, dates[0], pics[0])
  piclst.value = pics.map(p => p.replace('_thumbnail', ''))
  datlst.value = dates
  ratlst.value = ratios
  picidx.value = idx
  // getMeta(getPic(), callback)
  opened.value = true

}

</script>

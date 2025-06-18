<template>
  <div class="q-pa-md" style="height:2000px">
    <q-carousel
      animated
      v-model="slide"
      navigation
      infinite
      :autoplay="autoplay"
      arrows
      transition-prev="slide-right"
      transition-next="slide-left"
      @mouseenter="autoplay = false"
      @mouseleave="autoplay = true"
      v-model:fullscreen="fullscreen"
    >
      <!-- <q-carousel-slide v-for="(p, i) in pics" :key=p :name="i" :img-src="getPix(p, i)" /> -->
      <!-- <q-carousel-slide v-for="(p, i) in pics" :key=i :name="i" img-src="/pics/yali/thumbnails/AD3CE79C-9885-459F-9DB9-AB04A661D44B_thumbnail.jpg" /> -->
      <!-- <div v-for="(p) in pics" :key=p> -->
      <q-carousel-slide :name="0" img-src="/api/pics/yali/AD3CE79C-9885-459F-9DB9-AB04A661D44B.jpg" />
      <q-carousel-slide :name="1" img-src="/api/pics/yali/B5E6FD83-4466-4056-96A6-33B9FCEA6AB3.jpg" />
      <q-carousel-slide :name="2" img-src="/api/pics/yali/6B2DCAA5-1085-4AB3-9760-BEB7F1A0CC17.jpg" />
      <!-- </div> -->
    </q-carousel>
  </div>
<TxtPad />
<NumPad @set-pic-idx="((pix) => picidx = pix % piclst.length)" @set-interval-delay="((x) => intervalDelay = x )"/>
</template>
<script setup>
import { ref, computed } from 'vue'
import emitter from 'tiny-emitter/instance'
import { libFunctions } from "../src/composables/libFunctions"
const { isIM, isDesk, isIPad } = libFunctions()
import TxtPad from "../src/components/TxtPad"
import NumPad from "../src/components/NumPad"

const props = defineProps({
  pics: { type: Array },
})

const opened = ref(false)
const slide = ref(0)
const autoplay = ref(true)
const fullscreen = ref(false)
const slideshowing = ref(false)
const intervalId = ref(-1)
const intervalDelay = ref(1000)
const picidx = ref(-1)
const piclst = ref([])
const datlst = ref([])
const ratlst = ref([])
const imgW = ref(0)
const imgH = ref(0)
const winW = sizes().windowWidth
const winH = sizes().windowHeight - 100
defineExpose({ openIt })
console.log(`-CK-winW=${winW} winH=${winH}`)

const compIntervalDelay = computed(() => { return intervalDelay.value })

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

function stopSlideshow () {
  slideshowing.value = false
  clearInterval(intervalId.value)
}
function slideshow () {
  if (slideshowing.value) return
  slideshowing.value = true
  // console.log(`-fn-slidshow picidx=${picidx.value}`)
    intervalId.value = setInterval(() => {
      if (picidx.value > piclst.value.length) picidx.value = 0;
      else picidx.value++
    }, compIntervalDelay.value)
}
function getPix (p, i) {
  const pix = process.env.API + '/pics/yali/thumbnails/' + p
  console.log(`-fn-getPix i=${i} pix=${pix}`, props.pics)
  return pix
}
function getPic () {
  return process.env.API + '/pics/yali/' + piclst.value[picidx.value]
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

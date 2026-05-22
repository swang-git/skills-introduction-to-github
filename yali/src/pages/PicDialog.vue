<template>
  <q-dialog v-model="opened" :transition-show="picidx % 2 == 0 ? 'slide-right' : 'slide-left'" maximized>
    <q-card class="bg-red-4 overflow-hidden" style="height: 45px; z-index: 10">
      <q-card-actions align="between" style="margin-top:-8px">
        <q-btn glossy rounded class="bg-teal" v-close-popup>
          <q-icon left name="cancel" size="md" color="lime" />
          <span class="text-bold text-cyan-2 text-body1" style="margin: 0 4px 0 -10px">关闭</span>
        </q-btn>
        <div class="text-h5 cursor-pointer" @click="openTxtPad">{{ datetms[picidx] }}</div>
        <q-btn-group glossy rounded>
          <q-btn round color="teal-8" text-color="lime" @click="openNumPad">
            <span class="text-bold text-cyan-2 text-body1" style="margin: 0 4px 0 4px">
              <span v-if="picidx == 0">第一</span>
              <span v-else-if="picidx == piclst.length - 1">最后</span>
              <span v-else>{{ picidx + 1 }}</span>
            </span>
          </q-btn>
          <q-btn round color="teal-9" @click="picidx > 0 ? (picidx = 0) : (picidx = piclst.length - 1)" >
            <q-icon :name=" picidx == 0 ? 'toggle_off' : picidx == piclst.length - 1 ? 'toggle_on' : 'radio_button_checked' " size="md" color="lime" />
          </q-btn>
        </q-btn-group>
      </q-card-actions>
    </q-card>
    <div class="bg-green-9" :transition-show="picidx % 2 == 0 ? 'rotate' : 'slide-left'">
      <div v-if="isDesk">
        <div class="row justify-between">
          <q-btn flat icon="" size="lg" @click="--picidx < 0 ? (picidx = piclst.length - 1) : picidx" style="display: flex; align-items: center; min-height: 100vh; z-index: 1" />
          <img id="imgId" class="fixed" :src="getPic()" :style="ratlst[picidx]>1? {'width':winW/ratlst[picidx]+'px'} : {'height':winH+'px'}" style="left: 50%; top: 50%; transform: translate(-50%, -50%)" @click="stopSlideshow()" />
          <!-- <img id="imgId" class="fixed" :height=winH :src="getPic()" style="left: 50%; top: 50%; transform: translate(-50%, -50%)" @click="stopSlideshow()" /> -->
          <q-btn flat icon="" size="lg" @click="++picidx >= piclst.length ? (picidx = 0) : picidx" />
        </div>
      </div>
      <div v-else>
        <div class="row justify-between">
          <q-btn flat icon="" size="lg" @click="--picidx < 0 ? (picidx = piclst.length - 1) : picidx" style="display: flex; align-items: center; min-height: 100vh; z-index: 1" />
          <!-- <q-img v-if="ratlst[picidx] > 1" id="imgId" class="q-pa-xs fixed" :src="getPic()" style="left: 50%; top: 50%; transform: translate(-50%, -50%)" @click="stopSlideshow()" /> -->
          <img id="imgId" class="q-pa-xs fixed" :src="getPic()" :width=winW style="left: 50%; top: 50%; transform: translate(-50%, -50%)" @click="stopSlideshow()" />
          <q-btn flat icon="" size="lg" @click="++picidx >= piclst.length ? (picidx = 0) : picidx" />
        </div>
      </div>
    </div>

    <q-card class="bg-cyan-4 overflow-hidden" style="margin: -150px 0 0 0; height: 50px">
      <q-card-actions align="between">
        <q-btn glossy rounded class="bg-teal-9" @click="--picidx < 0 ? (picidx = piclst.length - 1) : picidx" >
          <q-icon left name="arrow_circle_left" size="md" color="lime" />
          <span class="text-bold text-cyan-2 text-body1" style="margin: 0 4px 0 -10px">上一幅</span>
        </q-btn>
        <q-btn glossy rounded class="bg-teal-9" @click="slideshow()">
          <q-icon name="motion_photos_auto" size="md" color="yellow-9" />
          <span class="text-bold text-cyan-1 text-body1" style="margin: 0 4px 0 4px">幻灯片</span>
          <q-icon name="slow_motion_video" size="md" color="yellow-9" />
        </q-btn>
        <q-btn glossy rounded class="bg-teal-9" @click="++picidx >= piclst.length ? (picidx = 0) : picidx" >
          <span class="text-bold text-cyan-2 text-body1" style="margin: 0 4px 0 4px">下一幅</span>
          <q-icon name="arrow_circle_right" size="md" color="lime" />
        </q-btn>
      </q-card-actions>
    </q-card>
  </q-dialog>
  <NumPad @set-pic-idx="(pix) => (picidx = pix % piclst.length)" @set-interval-delay="(x) => (intervalDelay = x)" />
  <TxtPad />
</template>
<script setup>
import { ref, computed } from 'vue'
import emitter from 'tiny-emitter/instance'
import { libFunctions } from '../composables/libFunctions.js'
const { isIM, isDesk } = libFunctions()
import NumPad from '../../src/components/NumPad'
import TxtPad from '../../src/components/TxtPad'
const opened = ref(false)
const slideshowing = ref(false)
const intervalId = ref(-1)
const intervalDelay = ref(1500)
const picidx = ref(-1)
const piclst = ref([])
const datetms = ref([])
const fileszs = ref([])
const ratlst = ref([])
const winW = isIM ? sizes().windowWidth - 10 : sizes().windowWidth - 222
const winH = sizes().windowHeight - 122
const winR = winW / winH
defineExpose({ openIt })
console.log(`-CK-winW=${winW} winH=${winH} winR=${winR}`)

const compIntervalDelay = computed(() => {
  return intervalDelay.value
})

// emitter.on('open-PicDialog', (idx, pics, fileszs, datetms, ratios) => openIt(idx, pics, fileszs, datetms, ratios))
emitter.on('open-PicDialog', (idx, data) => openIt(idx, data))

function openNumPad() {
  // const filenum = picidx.value + 1
  console.log(`-fn-openNumPad totalPix=${piclst.value.length}`)
  emitter.emit('open-num-pad', 'YALI', '要看那幅画', piclst.value.length)
}
function openTxtPad() {
  const filenum = picidx.value + 1
  emitter.emit('open-TxtPad', piclst.value[picidx.value], fileszs.value[picidx.value], 'filename of ' + filenum)
}

// function getWidth() {
//   let wid = ratlst.value[picidx.value] < 1 ? null : winW
//   let ratio = ratlst.value[picidx.value]
//   if (ratio > 1) wid = Math.min(wid / ratio, winW)
//   console.log(`-fn-getWidth ratio=${ratio} wid=${wid} winW=${winW}`)
//   return wid
// }
// function getHeight() {
//   let hit = ratlst.value[picidx.value] < 1 ? winH : null
//   hit = Math.min(hit, winH)
//   return hit == 0 ? null : hit
// }

function stopSlideshow() {
  console.log(`-fn-stopSlideshow`)
  slideshowing.value = false
  clearInterval(intervalId.value)
}
// function toggleSlideshow () {
//   console.log(`-fn-toggleSlideshow`)
//   slideshowing.value = !slideshowing.value
//   if (slideshowing.value) slideshow()
//   else clearInterval(intervalId.value)
// }
function slideshow() {
  if (slideshowing.value) return
  slideshowing.value = true
  console.log(`-fn-slidshow() picidx=${picidx.value}`)
  intervalId.value = setInterval(() => {
    if (picidx.value > piclst.value.length - 1) {
      picidx.value = 0
    } else {
      picidx.value++
      // if (picidx.value > piclst.value.length) picidx.value--
    }
  }, compIntervalDelay.value)
}
function getPic() {
  // console.log(`jpgname=${piclst.value[picidx.value]} ratio=${ratlst.value[picidx.value]} wid=${getWidth()} hit=${getHeight()}` )
  // console.log(`jpgname=${piclst.value[picidx.value]} ratio=${ratlst.value[picidx.value]} wid=${getWidth()} hit=${getHeight()}, pixidx=${picidx.value}` )
  if (picidx.value > piclst.value.length - 1) picidx.value = 0
  // console.log(`-fn-getPic() picidx=${picidx.value}` )
  let picdir = isIM ? '/pics/yali/' : '/pics/yali/'
  return process.env.API + picdir + piclst.value[picidx.value]
}
// const getMeta = (url, cb) => {
//   const img = new Image();
//   img.onload = () => cb(null, img);
//   img.onerror = (err) => cb(err);
//   img.src = url;
// }
// console.log(sizes())
function sizes() {
  const contentWidth =
    [...document.body.children].reduce(
      (a, el) => Math.max(a, el.getBoundingClientRect().right),
      0,
    ) - document.body.getBoundingClientRect().x

  return {
    windowWidth: document.documentElement.clientWidth,
    windowHeight: document.documentElement.clientHeight,
    pageWidth: Math.min(document.body.scrollWidth, contentWidth),
    pageHeight: document.body.scrollHeight,
    screenWidth: window.screen.width,
    screenHeight: window.screen.height,
    pageX: document.body.getBoundingClientRect().x,
    pageY: document.body.getBoundingClientRect().y,
    screenX: -window.screenX,
    screenY: -window.screenY - (window.outerHeight - window.innerHeight),
  }
}
// function callback(err, img) {
//   imgW.value = img.width
//   imgH.value = img.height
//   console.log(`-fn-open-it idx=${picidx.value} width=${imgW.value} height=${imgH.value}`)
// }
function openIt(idx, data) {
  // console.log(sizes())
  // piclst.value = data.map((p) => p.replace('_thumbnail', ''))
  piclst.value = data.map(p => p.fnm)
  fileszs.value = data.map(p => p.fsz)
  datetms.value = data.map(p => p.dtm)
  ratlst.value = data.map(p => p.whr.width / p.whr.height)
  console.log(`-fn-openIt idx=${idx} width=${data[idx].whr.width} height=${data[idx].whr.height}`)
  picidx.value = idx
  opened.value = true
}
// function openIt(idx, pics, fszs, datms, ratios) {
//   // console.log(sizes())
//   console.log(`-fn-openIt idx=${idx} datetms[idx]=${datms[idx]}`, pics[0])
//   piclst.value = pics.map((p) => p.replace('_thumbnail', ''))
//   datetms.value = datms
//   fileszs.value = fszs
//   ratlst.value = ratios
//   picidx.value = idx
//   // getMeta(getPic(), callback)
//   opened.value = true
// }
</script>

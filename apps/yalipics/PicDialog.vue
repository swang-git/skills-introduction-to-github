<template>
<q-dialog v-model="opened" :transition-show="picidx%2==0 ? 'slide-right' : 'slide-left'" maximized>
  <q-card class="bg-red-4" style="height:45px">
    <q-card-actions align="between">
      <q-btn glossy rounded class="bg-teal" v-close-popup>
        <q-icon left name="cancel" size="md" color="lime" />
        <span class="text-bold text-cyan-2 text-body1" style="margin: 0 4px 0 -10px">关闭</span>
      </q-btn>
      <!-- <div class="text-h5">共 {{ piclst.length }} 幅 </div> -->
      <div class="text-h5" @click="openTxtPad"> {{ datlst[picidx] }} </div>
      <q-btn-group glossy rounded>
      <q-btn round color="teal-8" text-color="lime" @click="openNumPad">
        <span class="text-bold text-cyan-2 text-body1" style="margin: 0 4px 0 4px">
          <span v-if="picidx==0">第一</span>
          <span v-else-if="picidx==piclst.length-1">最后</span>
          <span v-else>{{ picidx + 1 }}</span>
          </span>
      </q-btn>
      <q-btn round color="teal-9" @click="picidx>0 ? picidx=0 : picidx=piclst.length-1">
        <q-icon :name="picidx==0 ? 'toggle_off' : picidx==piclst.length-1 ? 'toggle_on' : 'radio_button_checked'" size="md" color="lime" />
      </q-btn>
    </q-btn-group>
      <!-- <q-btn glossy rounded class="bg-teal-9" @click="picidx>0 ? picidx=0 : picidx=piclst.length-1">
        <span class="text-bold text-cyan-2 text-body1" style="margin: 0 4px 0 4px">
          <span v-if="picidx==0">第一</span>
          <span v-else-if="picidx==piclst.length-1">最后</span>
          <span v-else>{{ picidx + 1 }}</span>
          </span>
        <q-icon :name="picidx==0 ? 'toggle_off' : picidx==piclst.length-1 ? 'toggle_on' : 'radio_button_checked'" size="md" color="lime" />
      </q-btn> -->
    </q-card-actions>
  </q-card>
  <div class="bg-grey-4" :transition-show="picidx%2==0 ? 'rotate' : 'slide-left'">
    <img id="imgId" class="q-pa-xs fixed" :src="getPic()" :width="getWidth()" :height="getHeight()" style="left:50%; top:50%; transform:translate(-50%, -50%);" @click="stopSlideshow()" />
    <div v-show="showPicInfo" class="q-pt-md q-pl-md text-h6">{{ getPic() }}</div>
    <!-- <img id="imgId" loading="lazy" class="q-pa-xs fixed" :src="getPic()" :height="ratlst[picidx]<1 ? winH : winH / ratlst[picidx]" style="left:50%; top:50%; transform:translate(-50%, -50%);" @click="stopSlideshow()" /> -->
    <!-- <img id="imgId" class="q-pa-xs fixed" :src="getPic()" :width="getWidth()" :height="getHeight()"
    style="left:50%; top:50%; transform:translate(-50%, -50%)"
    fit="fill"
    @click="stopSlideshow()" /> -->
    <!-- <q-img :src="getPic()" @click="stopSlideshow()" /> -->
  </div>

  <q-card class="bg-cyan-4 q-px-sm" style="margin:-150px 0 0 0;height:50px">
    <q-card-actions align="between">
      <q-btn glossy rounded class="bg-teal-9" @click="--picidx<0 ? picidx=piclst.length-1 : picidx">
        <q-icon left name="arrow_circle_left" size="md" color="lime" />
        <span class="text-bold text-cyan-2 text-body1" style="margin: 0 4px 0 -10px">上一幅</span>
      </q-btn>
      <!-- <div class="text-h5" @click="slideshow()">第 {{ picidx + 1 }} 幅</div> -->
      <q-btn glossy rounded class="bg-teal-9" @click="slideshow()">
        <q-icon name="motion_photos_auto" size="md" color="yellow-9" />
        <span class="text-bold text-cyan-1 text-body1" style="margin: 0 4px 0 4px">幻灯片</span>
        <q-icon name="slow_motion_video" size="md" color="yellow-9" />
      </q-btn>
      <!-- <q-btn glossy rounded class="bg-red-9" @click="showPicInfo=!showPicInfo">
        <q-icon name="info" size="md" color="yellow-9" />
      </q-btn> -->
      <q-btn glossy rounded class="bg-teal-9" @click="++picidx>=piclst.length ? picidx=0 : picidx">
        <span class="text-bold text-cyan-2 text-body1" style="margin: 0 4px 0 4px">下一幅</span>
        <q-icon name="arrow_circle_right" size="md" color="lime" />
      </q-btn>
    </q-card-actions>
  </q-card>
</q-dialog>
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
const opened = ref(false)
const showPicInfo = ref(false)
const slideshowing = ref(false)
const intervalId = ref(-1)
const intervalDelay = ref(1500)
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
  emitter.emit('open-TxtPad', -9876, piclst.value[picidx.value], 'filename of ' + filenum )
}

function getWidth ()  {
  let wid = ratlst.value[picidx.value] < 1 ? null : winW
  let ratio = ratlst.value[picidx.value]
  if (ratio > 1) wid = Math.min(wid / ratio, winW)
  console.log(`-fn-getWidth ratio=${ratio} wid=${wid} winW=${winW}`)
  return wid
}
function getHeight () { 
  let hit = ratlst.value[picidx.value] < 1 ? winH : null
  hit = Math.min(hit, winH)
  return hit == 0 ? null : hit
}

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
function getPic () {
  console.log(`jpgname=${piclst.value[picidx.value]} ratio=${ratlst.value[picidx.value]} wid=${getWidth()} hit=${getHeight()}`)
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

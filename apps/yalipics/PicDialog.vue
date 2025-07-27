<template>
<div>
<q-dialog v-model="opened" maximized class="bg-teal-5">
  <q-card class="bg-cyan-4 q-px-sm" style="height:50px">
    <q-card-actions align="between">
      <q-btn glossy rounded class="bg-teal" v-close-popup>
        <q-icon left name="cancel" size="md" color="lime" />
        <span class="text-bold text-cyan-2 text-body1" style="margin: 0 4px 0 -10px">关闭</span>
      </q-btn>
      <div class="text-h5">共 {{ piclst.length }} 幅 </div>
      <!-- <q-btn glossy rounded class="bg-teal-9" @click="picidx>0 ? picidx=0 : picidx=piclst.length-1; getMeta(getPic(), callback)"> -->
      <q-btn glossy rounded class="bg-teal-9" @click="picidx>0 ? picidx=0 : picidx=piclst.length-1">
        <span class="text-bold text-cyan-2 text-body1" style="margin: 0 4px 0 4px">
          <span v-if="picidx==0">第一</span>
          <span v-else-if="picidx==piclst.length-1">最后</span>
          <span v-else>{{ picidx + 1 }}</span>
          </span>
        <q-icon :name="picidx==0 ? 'toggle_off' : picidx==piclst.length-1 ? 'toggle_on' : 'radio_button_checked'" size="md" color="lime" />
      </q-btn>
    </q-card-actions>
  </q-card>

  <q-card class="bg-teal">
    <q-card-section style="margin:-15px 0 0 -16px">
      <!-- <img id="imgId" :src="getPic()" :width="isIM ? 377 : 870" /> -->
      <!-- <img id="imgId" :src="getPic()" :width="imgW" :height="imgH" /> -->
      <img id="imgId" :src="getPic()" :width="winW" :height="winH" />
    </q-card-section>
  </q-card>
  <q-card class="bg-cyan-4 q-px-sm" style="margin:-150px 0 0 0;height:50px">
    <q-card-actions align="between">
      <!-- <q-btn glossy rounded class="bg-teal-9" @click="--picidx<0 ? picidx=piclst.length-1 : picidx; getMeta(getPic(), callback)"> -->
      <q-btn glossy rounded class="bg-teal-9" @click="--picidx<0 ? picidx=piclst.length-1 : picidx">
        <q-icon left name="arrow_circle_left" size="md" color="lime" />
        <span class="text-bold text-cyan-2 text-body1" style="margin: 0 4px 0 -10px">上一幅</span>
      </q-btn>
      <div class="text-h5">第 {{ picidx + 1 }} 幅 </div>
      <q-btn glossy rounded class="bg-teal-9" @click="++picidx>=piclst.length ? picidx=0 : picidx">
        <span class="text-bold text-cyan-2 text-body1" style="margin: 0 4px 0 4px">下一幅</span>
        <q-icon name="arrow_circle_right" size="md" color="lime" />
      </q-btn>
    </q-card-actions>
  </q-card>
</q-dialog>
</div>
</template>
<script setup>
import { ref } from 'vue'
import { libFunctions } from "../src/composables/libFunctions"
const { isIM, isDesk, isIPad } = libFunctions()
const opened = ref(false)
const picidx = ref(-1)
const piclst = ref([])
const imgW = ref(0)
const imgH = ref(0)
const winW = sizes().windowWidth
const winH = sizes().windowHeight - 100
defineExpose({ openIt })
console.log(`-CK-winW=${winW} winH=${winH}`)

function getPic() {
  return process.env.API + '/pics/yali/' + piclst.value[picidx.value]
}
const getMeta = (url, cb) => {
  const img = new Image();
  img.onload = () => cb(null, img);
  img.onerror = (err) => cb(err);
  img.src = url;
}
// console.log(sizes())
function sizes() {
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
function callback(err, img) {
  imgW.value = img.width 
  imgH.value = img.height
  console.log(`-fn-open-it idx=${picidx.value} width=${imgW.value} height=${imgH.value}`)
}
function openIt(idx, pics) {
  // console.log(sizes())
  console.log(`-fn-open-it idx=${idx}`, pics[0])
  piclst.value = pics
  picidx.value = idx
  // getMeta(getPic(), callback)
  opened.value = true
}

</script>

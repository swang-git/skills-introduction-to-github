<template>
  <q-dialog v-model="opened" maximized :transition-show="compPidx % 2 == 0 ? 'rotate' : 'slide-left'">
    <q-card class="bg-red-4 overflow-hidden" style="height: 45px; z-index: 10">
      <q-card-actions align="between" style="margin-top:-8px">
        <q-btn glossy rounded class="bg-teal" v-close-popup>
          <q-icon left name="cancel" size="md" color="lime" />
          <span class="text-bold text-cyan-2 text-body1" style="margin: 0 4px 0 -10px">关闭</span>
        </q-btn>
        <div class="text-h5 cursor-pointer" @click="openTxtPad">{{ datetms[pidx] }}</div>
        <q-btn-group glossy rounded>
          <q-btn round color="teal-8" text-color="lime" @click="openNumPad">
            <span class="text-bold text-cyan-2 text-body1" style="margin: 0 4px 0 4px">
              <span v-if="pidx == 0">第一</span>
              <span v-else-if="pidx == piclst.length - 1">最后</span>
              <span v-else>{{ pidx + 1 }}</span>
            </span>
          </q-btn>
          <q-btn round color="teal-9" @click="pidx > 0 ? (pidx = 0) : (pidx = piclst.length - 1)" >
            <q-icon :name=" pidx == 0 ? 'toggle_off' : pidx == piclst.length - 1 ? 'toggle_on' : 'radio_button_checked' " size="md" color="lime" />
          </q-btn>
        </q-btn-group>
      </q-card-actions>
    </q-card>
    <!-- <div class="bg-green-9" :transition-show="pidx % 2 == 0 ? 'rotate' : 'slide-left'"> -->
    <div :style="pageBackground">
      <div v-if="isDesk">
        <div class="row justify-between">
          <q-btn flat icon="" size="lg" @click="--pidx < 0 ? (pidx = piclst.length - 1) : pidx" style="height:95vh; z-index:1" />
          <!-- <img :src="getPic()" :style="{ width: ($q.screen.width - 150)+'px', height: 'auto' }" style="margin-top:-50px;object-fit:contain" @click="stopSlideshow()" /> -->
          <img class="fixed" :src="getPic()" :style="getStyle()" @click="stopSlideshow()" />
          <q-btn flat icon="" size="lg" @click="++pidx >= piclst.length ? (pidx = 0) : pidx" />
        </div>
      </div>
      <div v-else>
        <div class="row justify-between">
          <q-btn flat icon="" size="lg" @click="--pidx < 0 ? (pidx = piclst.length - 1) : pidx" style="height:95vh; z-index:1" />
          <!-- <img id="imgId" class="q-pa-xs fixed" :src="getPic()" :width=winW :style="getStyle()" @click="stopSlideshow()" /> -->
          <img id="imgId" class="q-pa-xs fixed" :src="getPic()" width="98%" :style="getStyle()" @click="stopSlideshow()" />
          <q-btn flat icon="" size="lg" @click="++pidx >= piclst.length ? (pidx = 0) : pidx" />
        </div>
      </div>
    </div>

    <q-card class="bg-cyan-4 overflow-hidden" style="margin: -150px 0 0 0; height: 50px">
      <q-card-actions align="between">
        <q-btn glossy rounded class="bg-teal-9" @click="--pidx < 0 ? (pidx = piclst.length - 1) : pidx" >
          <q-icon left name="arrow_circle_left" size="md" color="lime" />
          <span class="text-bold text-cyan-2 text-body1" style="margin: 0 4px 0 -10px">上一幅</span>
        </q-btn>
        <div v-show="admin.isCleanup">
          <q-btn glossy rounded class="bg-pink" @click="askRemoveDupFile">
            <q-icon left name="cancel" size="36px" color="yellow" />
            <span class="text-yellow text-h6" style="margin:4px 4px 0 -10px">删除</span>
          </q-btn>
          <q-btn v-if="compRemovedPidx>=0" glossy rounded class="bg-teal" @click="askUndoRemoveDupFile">
            <q-icon left name="info" size="36px" color="yellow-9" />
            <span class="text-cyan-2 text-h6" style="margin:4px 4px 0 -10px">确定</span>
          </q-btn>

          <!-- <q-btn v-if="compRemovedPidx>=0" glossy dense icon="cancel" class="text-h6 text-cyan-3" label="undo" color="cyan-10" @click="askUndoRemoveDupFile" />
          <q-btn rounded glossy dense icon="delete" class="text-h6 text-red" label="delete" color="cyan-10" @click="askRemoveDupFile" /> -->
        </div>
        <q-btn glossy rounded class="bg-teal-9" @click="slideshow()">
          <q-icon name="motion_photos_auto" size="md" color="yellow-9" />
          <span class="text-bold text-cyan-1 text-body1" style="margin: 0 4px 0 4px">幻灯片</span>
          <q-icon name="slow_motion_video" size="md" color="yellow-9" />
        </q-btn>
        <q-btn glossy rounded class="bg-teal-9" @click="++pidx >= piclst.length ? (pidx = 0) : pidx" >
          <span class="text-bold text-cyan-2 text-body1" style="margin: 0 4px 0 4px">下一幅</span>
          <q-icon name="arrow_circle_right" size="md" color="lime" />
        </q-btn>
      </q-card-actions>
    </q-card>
  </q-dialog>
  <TxtPad />
  <!-- <ConfirmDialog @user-confirmed = "(act) => removeDupFile(act)" /> -->
  <!-- <ConfirmDialog @user-confirmed-delete=removeDupFile @user-confirmed-undo=undoRemovedDupFile /> -->
  <ConfirmDialog @user-confirmed=confirmedAction />
</template>
<script setup>
import { ref, computed } from 'vue'
import emitter from 'tiny-emitter/instance'
import { libFunctions } from '../composables/libFunctions.js'
const { isDesk, DEV_API, $q } = libFunctions()
import { axiosFunctions } from '../composables/axiosFunctions.js'
const { gaxios } = axiosFunctions()
import TxtPad from '../components/TxtPad.vue'
import ConfirmDialog from '../components/ConfirmDialog.vue'
import { useAdminStore } from '../stores/adminStore.js'
const admin = useAdminStore()
emitter.on('pix-pidx', (idx) => { pidx.value = idx%piclst.value.length; console.log(`pidx=${pidx.value}`); getPic() })

const removedPidx = ref(-1)
const opened = ref(false)
const slideshowing = ref(false)
const intervalId = ref(-1)
const intervalDelay = ref(1500)
const pidx = ref(-1)
const piclst = ref([])
const datetms = ref([])
const fileszs = ref([])
const ratlst = ref([])
// const winW = window.innerWidth
// const winH = window.innerHeight
const winW = $q.screen.width
const winH = $q.screen.height
const imgWs = ref([])
const imgHs = ref([])
defineExpose({ openIt })
emitter.on('yali-undoRemovedDupFile', (x) => checkingRemovedFile(x))

const compRemovedPidx = computed(() => { return removedPidx.value })

function checkingRemovedFile (da) {
  if (da.status != "OK") {
    let filename = da.status
    alert(filename + ' does NOT exist, can not Undo')
    return
  }
}

function confirmedAction (act) {
  if (act == 'delete') removeDupFile()
  else if (act == 'undo') undoRemovedDupFile()
}

function askUndoRemoveDupFile () {
  // removedPidx.value = pidx.value
  let fnm = piclst.value[pidx.value]
  console.log(`-fn-askUndoRemoveDupFile removeePidx=${removedPidx.value} fnm=${fnm}`)
  emitter.emit('open-ConfirmDialog', 'Undo Removed Dup file', 'Already Removed File: ' + fnm, 'undo')
}

function undoRemovedDupFile () {
  let idx = pidx.value
  let fnm = piclst.value[idx]
  removedPidx.value = idx
  console.log(`-fn-undoRemovedDupFile pidx=${idx} fnm=${fnm}`)
  // const path = process.env.API + '/yali/undoRemovedDupFile/' + fnm
  const path = DEV_API + '/yali/undoRemovedDupFile/' + fnm
  gaxios(path)
}

function askRemoveDupFile () {
  let idx = pidx.value
  let fnm = piclst.value[idx]
  console.log(`-fn-askRemoveDupFile pidx=${idx} fnm=${fnm}`)
  emitter.emit('open-ConfirmDialog', 'Remove Dup file', 'Removing File: ' + fnm, 'delete')
}

function removeDupFile () {
  let idx = pidx.value
  let fnm = piclst.value[idx]
  removedPidx.value = idx
  // console.log(`-fn-removeDupFile action=${action} pidx=${idx} fnm=${fnm}`)
  console.log(`-fn-removeDupFile pidx=${idx} fnm=${fnm}`)
  // const path = process.env.API + '/yali/removeDupFile/' + fnm
  const path = DEV_API + '/yali/removeDupFile/' + fnm
  gaxios(path)
}

function getBGimg () {
  let idx = pidx.value % 7
  let istr0 = "bg-img-purple.png" + ')'
  let istr1 = "bg-img-black.png" + ')'
  let istr2 = "bg-img-beige.png" + ')'
  let istr3 = "bg-img-white.png" + ')'
  let istr4 = "bg-img-pink.png" + ')'
  let istr5 = "bg-img-grey.png" + ')'
  let istr6 = "bg-img-gold.png" + ')'
  return idx==0 ? istr0 : idx==1 ? istr1 : idx==2 ? istr2 : idx==3 ? istr3 : idx==4 ? istr4 : idx==5 ? istr5 : istr6
}
const pageBackground = computed(() => {
  return {
    // backgroundImage: 'url(' + process.env.API + "/yali/icons/" +  getBGimg(),
    backgroundImage: 'url(' + DEV_API + "/yali/assets/" +  getBGimg(),
    backgroundSize: 'cover',
    backgroundPosition: 'center',
    backgroundRepeat: 'no-repeat',
    backgroundColor: 'darkGreen'
  }
})

function getStyle() {
  let trans = "left: 50%; top: 50%; transform: translate(-50%, -50%)"
  if (!isDesk) return trans // + ';' + bgimg

  let idx = pidx.value
  // console.log(`-fn-getStyle(${idx})`)
  let imgAspectRatio = ratlst.value[idx]
  let viewW = (winW - 40)
  let viewH = (winH - 140) // top bar + bottom bar = 60 + 60 = 100px
  let viewAspectRatio = viewW / viewH
  let stystr = ''
  // console.log(`-CK- viewAspectRatio=${viewAspectRatio} imgAspectRatio=${imgAspectRatio} imgW=${imgW} viewW=${viewW} imgH=${imgH} viewH=${viewH}`)
  // console.log(`-CK- viewAspectRatio=${viewAspectRatio} imgAspectRatio=${imgAspectRatio} 1/imgAspectRation=${1/imgAspectRatio}`)
  if (viewAspectRatio > 1) {
    stystr = 'width:' + viewW / 1.2 + 'px'
    if (imgAspectRatio < 1) stystr = 'height:' + viewH + 'px'
  } else if (viewAspectRatio < 1) {
    stystr = 'height:' + viewH + 'px'
    if (imgAspectRatio > 1) stystr = 'width:' + viewW + 'px'
  } else {
    stystr = imgAspectRatio >= 1 ? 'width:' + viewW + 'px' : 'height:' + viewH + 'px'
  }
  return stystr + ";" + trans //+ ';' + bgimg
}

const compIntervalDelay = computed(() => { return intervalDelay.value })
const compPidx = computed(() => { return pidx.value })

emitter.on('open-PicDialog', (idx, imgdata) => openIt(idx, imgdata))

function openNumPad() {
  console.log(`-fn-openNumPad totalPix=${piclst.value.length}`)
  // numPadStore.open('YALI_PIX_PIDX', '要看哪幅画？', piclst.value.length)
  emitter.emit('open-NumPad', 'pix-pidx', '要看哪幅画？', piclst.value.length)
}

function openTxtPad() {
  const filenum = pidx.value + 1
  emitter.emit('open-TxtPad', piclst.value[pidx.value], fileszs.value[pidx.value], 'filename of ' + filenum)
}

function stopSlideshow() {
  console.log(`-fn-stopSlideshow`)
  slideshowing.value = false
  clearInterval(intervalId.value)
}

function slideshow() {
  if (slideshowing.value) return
  slideshowing.value = true
  console.log(`-fn-slidshow() pidx=${pidx.value} totalPix=${piclst.value.length}`)
  intervalId.value = setInterval(() => {
    if (pidx.value > piclst.value.length - 1) {
      pidx.value = 0
    } else {
      pidx.value++
      // if (pidx.value > piclst.value.length) pidx.value--
    }
  }, compIntervalDelay.value)
}
function getPic() {
  // console.log(`jpgname=${piclst.value[pidx.value]} ratio=${ratlst.value[pidx.value]} wid=${getWidth()} hit=${getHeight()}` )
  // console.log(`jpgname=${piclst.value[pidx.value]} ratio=${ratlst.value[pidx.value]} wid=${getWidth()} hit=${getHeight()}, pixidx=${pidx.value}` )
  if (pidx.value > piclst.value.length - 1) pidx.value = 0
  // console.log(`-fn-getPic() pidx=${pidx.value}` )
  // let picdir = isDesk ? '/pics/yali/' : '/pics/yaliIM/'
  let picdir = isDesk ? '/pics/yali/' : '/pics/yali/'
  // return process.env.API + picdir + piclst.value[pidx.value]
  return DEV_API + picdir + piclst.value[pidx.value]
}
// const getMeta = (url, cb) => {
//   const img = new Image();
//   img.onload = () => cb(null, img);
//   img.onerror = (err) => cb(err);
//   img.src = url;
// }
// console.log(sizes())
// function sizes() {
//   const contentWidth =
//     [...document.body.children].reduce(
//       (a, el) => Math.max(a, el.getBoundingClientRect().right),
//       0,
//     ) - document.body.getBoundingClientRect().x

//   return {
//     windowWidth: document.documentElement.clientWidth,
//     windowHeight: document.documentElement.clientHeight,
//     pageWidth: Math.min(document.body.scrollWidth, contentWidth),
//     pageHeight: document.body.scrollHeight,
//     screenWidth: screen.width,
//     screenHeight: screen.height,
//     // screenWidth: window.screen.width,
//     // screenHeight: window.screen.height,
//     layoutWidth: document.documentElement.clientWidth,
//     layoutHeight: document.documentElement.clientHeight,
//     // pageX: document.body.getBoundingClientRect().x,
//     // pageY: document.body.getBoundingClientRect().y,
//     // screenX: -window.screenX,
//     // screenY: -window.screenY - (window.outerHeight - window.innerHeight),
//   }
// }

// function callback(err, img) {
//   imgW.value = img.width
//   imgH.value = img.height
//   console.log(`-fn-open-it idx=${pidx.value} width=${imgW.value} height=${imgH.value}`)
// }
function openIt(idx, imgdata) {
  // console.log(sizes())
  // piclst.value = imgdata.map((p) => p.replace('_thumbnail', ''))
  piclst.value = imgdata.map(p => p.fnm)
  fileszs.value = imgdata.map(p => p.fsz)
  datetms.value = imgdata.map(p => p.dtm)
  imgWs.value = imgdata.map(p => p.whr.width)
  imgHs.value = imgdata.map(p => p.whr.height)
  ratlst.value = imgdata.map(p => p.whr.width / p.whr.height)
  // console.log(`${idx}:imgW=${imgWs.value[idx]} winW=${winW}`)
  // console.log(`${idx}:imgH=${imgHs.value[idx]} winH=${winH}`)
  pidx.value = idx
  opened.value = true
}
</script>

<style>
.bg-img-not-used {
  /* background-image: url('');
  background-image: url('https://cdn.quasar.dev/img/material.png');
  background-image: url('https://images.unsplash.com/photo-1557683316-973673baf926');
  background-image: url('https://images.unsplash.com/photo-1519681393784-d120267933ba');
  background-image: url('https://images.unsplash.com/photo-1507409611970-2599c616268a');
  background-image: url('https://images.unsplash.com/photo-1520034475321-cbe63696469a');
  background-image: url('http://shengli.cn.mt/pics/yali/bg-img-10.png');
  background-image: url('/api/yali/icons/bg-img-purple.png'); */
  background-size: cover;       /* Fills screen nicely */
  background-position: center;   /* Centers image */
  background-repeat: no-repeat;  /* No tiling */
  background-attachment: fixed;  /* Stays in place when scrolling */
  background-color: #f0f0f0;      /* Fallback color */
  background-color: darkgreen;      /* Fallback color */
}
</style>

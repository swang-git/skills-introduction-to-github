<template>
<div class="q-pl-sm">
  <q-item clickable v-ripple @click="openApp(appn, appl)">
    <q-item-section avatar>
      <q-btn round glossy :color="colr">
        <q-icon :name="icon" :color="iclr" :size="size" :style="styl"/>
      </q-btn>
    </q-item-section>
    <q-item-section>
      <div class="cfont">{{ appl }}</div>
    </q-item-section>
  </q-item>
  <q-tooltip class="text-h6 text-white" :class="'bg-' + colr + ' text-' + iclr">{{ appl }}</q-tooltip>
</div>
</template>
<script setup>
import { useRouter } from 'vue-router'
const router = useRouter()

const props = defineProps({
  size: { type: String },
  icon: { type: String },
  iclr: { type: String },
  colr: { type: String },
  // ttip: { type: String },
  // clas: { type: String },
  styl: { type: String },
  // labl: { type: String },
  appn: { type: String },
  appl: { type: String },
})
// data () {
//   return { }
//   // <RoundButton :size="22" :icon="login" :color="accent" :click="login()" :tip="'Admin Login'" />
// },
// console.log(`-ST-AppItem`)
// console.log(`-ST-RoundButton ${props.size} ${props.icon} ${props.colr} ${props.iclr} ${props.ttip} ${props.clas}`)

function openApp (app, appTitle) {
  if (app === '') return
  // console.log(`-fn-openApp, appTitle, app=${app},appTitle=${appTitle},drawer=${drawer},numItems=${numItems.value},itemsPerPage=${itemsPerPage.value}`)
  // curApp.value = app
  // for (const key of Object.keys(disableBtn)) disableBtn[key] = false
  // disableBtn[app] = true
  if (['../golf', '../arts', 'glucosecheck','exlist', 'relist'].includes(app)) { // no need to reload for chart
    window.location.href = app // this navigates to app and also trigger to loading. otherwise <canvas> not working
  }
  router.replace({ path: app })  // this is just navigating no loading
}

</script>


<!-- <q-item clickable v-ripple @click="openApp('tvmanager', 'TV Manager')">
  <q-item-section avatar>
    <q-btn round glossy color="amber-9">
      <q-icon name="视" color="white" size="24px" style="margin:-6px 0 0 0"/>
    </q-btn>
  </q-item-section>
  <q-item-section>
    <div class="cfont">电 视 列 表</div>
  </q-item-section>
</q-item> -->
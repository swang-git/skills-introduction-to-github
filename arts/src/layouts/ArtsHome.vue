<template>
  <q-layout view="lHh Lpr LFf">
    <q-header>
      <q-toolbar class="bg-teal-10 glossy">
        <q-btn v-if="!isTextPage" flat dense round @click="drawer=!drawer" aria-label="Menu" size="20px" :icon="getIcon()" />
          <q-toolbar-title>
            <div class="text-center cursor-pointer no-wrap" style="font-size:27px" @click="doAction()">{{ store.topTit }}</div>
          </q-toolbar-title>
      </q-toolbar>
    </q-header>

    <q-drawer v-model="drawer" :width="isDesk ? 338 : 225" :breakpoint="500" class="bg-teal-10" >
      <q-list no-border link inset-delimiter>
        <q-item-label>
          <div v-if="isDesk"><q-btn flat @click="goHome()" style="color:rgb(40,255,10);font-size:30px">省千里路 🏠 破万卷书</q-btn></div>
          <div v-else style="margin:0 0 0 18px"><q-btn round glossy @click="goHome()" color="green"><q-icon name="🏠" class="q-pb-sm" /></q-btn>
            <span class="q-pl-xl"><q-btn flat icon="天 天 看 看" class="q-pb-xs" /></span>
          </div>
        </q-item-label>
        <q-item v-for="(lnk, i) in data.links" :key="lnk.x" :to="{ name: 'cont', params: { tag: lnk.tag, ymd: lnk.ymd }}" :focused='clickedIdx==i' @click="clickedIdx=i">
          <!-- <q-item-section v-if="isDesk" @click="getArtstCont(i)"> -->
          <q-item-section v-if="isDesk">
            <q-item-label :class="{ 'art-tit':isDesk, 'fon-tit':isIM }">
              {{ data.titles[i].replace(/[(|)]/g, '').replace(/\d{4}年/, '').replace(/星期/, '周').replace(/天/, '日') }}
            </q-item-label>
            <q-item-label class="subtits"> {{ data.updtime[i] }} </q-item-label>
          </q-item-section>
          <q-item-section v-else>
            <!-- <q-item-label :class="{ 'art-tit':isDesk, 'fon-tit':isFone||isFirefox, 'mat-tit':isMate, 'edg-tit':isEdge }"> -->
            <q-item-label :class="{ 'art-tit':isDesk, 'fon-tit':isFone }">
              <q-btn :icon="data.titles[i].substring(0, 1)" round glossy class="q-pb-sm" :color="getColor(i)" />
              <q-btn v-if="isDesk" style="margin:0 0 7px 48px" flat :icon="data.titles[i].substring(1, 4) + ' ' + data.titles[i].substring(17, 20)" />
              <q-btn v-else style="margin:0 0 7px 18px" flat :icon="data.titles[i].substring(1, 2) + ' ' + data.titles[i].replace(/星期/, '周').replace(/天/, '日').substring(17, 19)" />
            </q-item-label>
          </q-item-section>
        </q-item>

        <q-item v-if="isLocal" clickable v-ripple @click="openApp('/apps/watcher')">
          <q-item-section avatar>
            <q-btn round glossy icon="img:/arts/icons/quasar-logo.svg" />
          </q-item-section>
          <q-item-section class="text-h6 text-lime" style="font-family:youyuan" v-if="!isIM">
            <q-tooltip class="bg-teal-10 text-h4 text-red">Build Ver: {{ compVer }}</q-tooltip>
            Quasar Version: {{ $q.version }}
          </q-item-section>
        </q-item>
      </q-list>
    </q-drawer>

    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
const route = useRoute()
const $router = useRouter()
import emitter from 'tiny-emitter/instance'
import { libFunctions } from 'src/composables/libFunctions'
import { axiosFunctions } from 'src/composables/axiosFunctions'
const { gaxios } = axiosFunctions()
const { isDesk, isFone, isIM, isLocal, store } = libFunctions()

// name: 'ArtsHome',
const clickedIdx = ref(-1)
const drawer = ref(true)
const data = ref({})
// const searchTxt = ref('数学')
// const  // topTit = ref('天 天 浏 览 - 省千里路 破万卷书'
const topTit = ref('省千里路 🏠 破万卷书')
document.title = topTit.value

console.info('-ST-ArtsHome')
drawer.value = isDesk

console.log(`-CK-getList route.name=${route.name} isDesk=${isDesk}`)
getList()

emitter.on('arts-getList', (da) => data.value = da)

function getIcon () { return drawer.value ? 'chevron_left' : 'menu' }

function getColor (i) {
  const tit = data.value.titles[i].substring(0, 4)
  if (tit === '华山论剑') return 'lime-10'
  else if (tit === '焦点新闻') return 'pink-10'
  else if (tit === '即时新闻') return 'blue-10'
  else if (tit === '强国论坛') return 'green-10'
  else if (tit === '军事历史') return 'red-10'
  return i % 2 === 0 ? 'lime-10' : 'pink-10'
}

function openApp (url) {
  // console.debug('acts', this.acts)
  window.location.href = url
}

function doAction () {
  if (!isTextPage.value) {
    $router.replace({ path: store.clickedCont.key })
  } else {
    drawer.value = !drawer.value
  }
}

function goHome () {
  console.debug('-fn-goHome()')
  document.title = topTit.value
  $router.push({ path: '/' })
}

function getList () {
  const path = process.env.API + '/arts/getList'
  gaxios(path)
}

const compVer = computed(() => { return process.env.VER })
const isTextPage = computed(() => { return route.name === 'text' })
</script>

<style>
body {
  background: rgb(2,49,45);
  color:white;
  font-family: stzhongsong;
}
.q-list {
  background: teal-9;
}
a { color: white; }
a:hover { color: yellow; }
a:visited { color: white; }
a:active { color: cyan; }
.art-tit {
  /* font-family: stfangsong; */
  font-family: youyuan;
  font-size: 29.8px;
  font-weight: 500;
  color: white;
}
.mat-tit {
  font-family: stfangsong;
  font-size: 30.1px;
  font-weight: 600;
  color: white;
  /* padding-left:1px; */
}
.fon-tit {
  font-family: stfangsong;
  font-size: 27.5px;
  font-weight: 600;
  color: white;
}
.edg-tit {
  font-family: stfangsong;
  font-size: 29.2px;
  font-weight: 600;
  color: white;
  /* padding-left:3px; */
}
</style>

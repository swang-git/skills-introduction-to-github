<template>
  <q-page class="flex flex-center">
    <img alt="vk-logo" src="../assets/vk-logo.png" />
    <q-footer>
      <q-toolbar class="bg-teal-10 glossy" style="height:30px">
        <q-toolbar-title class="row" style="padding: 22px 0 0 10px">
          <div class="col-7">
            <!-- <q-input class="text-h5" standout bottom-slots dark dense v-model="searchQuery" label="Search Query"> -->
            <q-input class="text-h5" standout bottom-slots dark dense v-model="searchQuery">
              <template v-slot:prepend>
                <q-icon name="search" color="cyan" />
              </template>
              <template v-slot:append>
                <q-icon name="close" @click="searchQuery = ''" class="cursor-pointer" />
              </template>
            </q-input>
          </div>
          <div class="col-5">
            <q-radio v-model="searchCat" val="aut" label="作者" @click="searchAut" keep-color color="green">
              <q-spinner-ios v-if="loadAut" color="green" size="3em" :thickness="5" />
            </q-radio>
            <q-radio v-model="searchCat" val="tit" label="标题" @click="searchTit" keep-color color="blue">
              <q-spinner v-if="loadTit" color="blue" size="3em" />
            </q-radio>
            <!-- <q-radio v-model="searchCat" val="txt" label="内容" @click="searchTxt" keep-color color="yellow">
              <q-spinner-pie v-if="loadTxt" color="yellow" size="3em" />
            </q-radio> -->
          </div>
        </q-toolbar-title>
      </q-toolbar>
    </q-footer>
  </q-page>
</template>

<style></style>

<script setup>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { libFunctions } from '../composables/libFunctions'
import { axiosFunctions } from '../composables/axiosFunctions'
const { gaxios } = axiosFunctions()
const { DEV_API, store } = libFunctions()
const route = useRoute()
const $router = useRouter()
const loadAut = ref(false)
const loadTit = ref(false)
const loadTxt = ref(false)
const searchCat = ref('')
// const searchQuery = ref('数学')
// const searchQuery = ref('纽约时报')
// const searchQuery = ref('渡川客')
const searchQuery = ref('采访')
// const searchQuery = ref('胜利')
// topTitle: '天 天 浏 览 - 破万卷书 省千里路'

console.info('-ST-Index')

function search () {
  console.info(`-fn-search searchQuery=${searchQuery.value}`)
  $router.replace({ path: '/' + searchCat.value + '/' + searchQuery.value })
}
function searchAut () {
  console.log(`-fn-searchAut searchQuery=${searchQuery.value}`)
  store.isSearch = true
  store.searchCat = 'aut'
  store.searchTxt = searchQuery.value
  let ckey = 'aut' + searchQuery.value
  $router.replace({ path: '/' + searchCat.value + '/' + searchQuery.value })
  let contx = store.clickedCont[ckey]
  if (contx != undefined) return
  const path = DEV_API + '/arts/searchATT/aut/' + searchQuery.value
  gaxios(path)
}

function searchTit () {
  console.log(`-fn-searchTit searchQuery=${searchQuery.value}`)
  store.isSearch = true
  store.searchCat = 'tit'
  store.searchTxt = searchQuery.value
  let ckey = 'tit' + searchQuery.value
  $router.replace({ path: '/' + searchCat.value + '/' + searchQuery.value })
  let contx = store.clickedCont[ckey]
  if (contx != undefined) return
  const path = DEV_API + '/arts/searchATT/tit/' + searchQuery.value
  gaxios(path)
}
function searchTxt () {
  console.log(`-fn-searchTxt searchQuery=${searchQuery.value}`)
  store.isSearch = true
  store.searchCat = 'txt'
  store.searchTxt = searchQuery.value
  let ckey = 'txt' + searchQuery.value
  $router.replace({ path: '/' + searchCat.value + '/' + searchQuery.value })
  let contx = store.clickedCont[ckey]
  if (contx != undefined) return
  const path = DEV_API + '/arts/searchATT/txt/' + searchQuery.value
  gaxios(path)
}
// function searchTxt () {
//   loadTxt.value = true
//   setTimeout(() => {
//     $router.replace({ path: '/txt/' + searchQuery.value })
//     loadTxt.value = false }, 3000)
// }
</script>

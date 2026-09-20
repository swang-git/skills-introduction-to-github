<template>
  <q-page class="flex flex-center">
    <img alt="vk-logo" src="../assets/vk-logo.png" />
    <q-footer>
      <q-toolbar class="bg-teal-10 glossy" style="height: 30px">
        <q-toolbar-title class="row" style="padding: 22px 0 0 10px">
          <div class="col-6">
            <q-input standout bottom-slots v-model="searchQuery" label="Search Query" dark dense>
              <template v-slot:prepend>
                <q-icon name="search" color="white" />
              </template>
              <template v-slot:append>
                <q-icon name="close" @click="searchQuery = ''" class="cursor-pointer" />
              </template>
            </q-input>
          </div>
          <div class="col-6">
            <q-radio v-model="searchCat" val="aut" label="作者" @click="searchAut" keep-color color="green">
              <q-spinner-ios v-if="loadAut" color="green" size="3em" :thickness="5" />
            </q-radio>
            <q-radio v-model="searchCat" val="tit" label="标题" @input="searchTit" keep-color color="blue">
              <q-spinner v-if="loadTit" color="blue" size="3em" />
            </q-radio>
            <q-radio v-model="searchCat" val="txt" label="内容" @input="searchTxt" keep-color color="yellow">
              <q-spinner-pie v-if="loadTxt" color="yellow" size="3em" />
            </q-radio>
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
const searchQuery = ref('渡川客')
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
  $router.replace({ path: '/' + searchCat.value + '/' + searchQuery.value })
  const path = DEV_API + '/arts/searchATT/aut/' + searchQuery.value
  gaxios(path)
}
// function searchAut () {
//   loadAut.value = true
//   setTimeout(() => {
//     $router.replace({ path: '/aut/' + searchQuery.value })
//     loadAut.value = false }, 2000)
// }

function searchTit () {
  loadTit.value = true
  setTimeout(() => {
    $router.replace({ path: '/tit/' + searchQuery.value })
    loadTit.value = false }, 1000)
}

function searchTxt () {
  loadTxt.value = true
  setTimeout(() => {
    $router.replace({ path: '/txt/' + searchQuery.value })
    loadTxt.value = false }, 3000)
}
</script>

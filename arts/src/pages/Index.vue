<template>
  <q-page class="flex flex-center">
    <img alt="VK logo" src="~assets/vk-logo.png">
    <q-footer>
      <q-toolbar class="bg-teal-10 glossy" style="height:30px">
        <q-toolbar-title class="row" style="padding:22px 0 0 10px">
          <div class="col-6">
            <q-input standout bottom-slots v-model="searchQuery" label="Search Query" dark dense>
              <template v-slot:prepend>
                <q-icon name="search" color="white" />
              </template>
              <template v-slot:append>
                <q-icon name="close" @click="searchQuery=''" class="cursor-pointer" />
              </template>
            </q-input>
          </div>
          <div class="col-6">
            <q-radio v-model="searchCat" val="aut" label="作者" @input="searchAut" keep-color color="green"> <q-spinner-ios v-if="loadAut" color="green"  size="3em" :thickness="5" /></q-radio>
            <q-radio v-model="searchCat" val="tit" label="标题" @input="searchTit" keep-color color="blue">  <q-spinner     v-if="loadTit" color="blue"   size="3em" /></q-radio>
            <q-radio v-model="searchCat" val="txt" label="内容" @input="searchTxt" keep-color color="yellow"><q-spinner-pie v-if="loadTxt" color="yellow" size="3em" /></q-radio>
          </div>
        </q-toolbar-title>
      </q-toolbar>
    </q-footer>
  </q-page>
</template>

<style>
</style>

<script>
// import { logClientPlatform } from '../myLibs'
export default {
  name: 'PageIndex',
  data () {
    return {
      loadAut: false,
      loadTit: false,
      loadTxt: false,
      searchCat: '',
      searchQuery: '数学'
      // topTitle: '天 天 浏 览 - 破万卷书 省千里路'
    }
  },
  methods: {
    // created () {
    //   console.info('pages/Index - created()')
    //   // document.title = this.topTitle
    //   // this.$store.state.arts.topTitle = this.topTitle
    //   // logClientPlatform()
    // },
    // search () {
    //   console.info(' == searchQuery', this.searchQuery, this.cat)
    //   this.$router.replace({ path: '/' + this.searchCat + '/' + this.searchQuery })
    // }
    searchAut () {
      this.loadAut = true
      setTimeout(() => {
        this.$router.replace({ path: '/aut/' + this.searchQuery })
        this.loadAut = false
      }, 2000)
    },
    searchTit () {
      this.loadTit = true
      setTimeout(() => {
        this.$router.replace({ path: '/tit/' + this.searchQuery })
        this.loadTit = false
      }, 1000)
    },
    searchTxt () {
      this.loadTxt = true
      setTimeout(() => {
        this.$router.replace({ path: '/txt/' + this.searchQuery })
        this.loadTxt = false
      }, 3000)
    }
  }
}
</script>

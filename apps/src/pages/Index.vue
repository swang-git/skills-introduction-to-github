<template>
<q-card class="flex flex-center" style="margin-top:-5px;height:500px;background-image:url('https://cdn.quasar.dev/img/material.png')">
  <img alt="Quasar logo" src="~assets/quasar-logo-vertical.svg" style="width:200px; height:200px" />
  <q-card-actions align="between">
    <RoundButton size="22px" icon="monetization_on"   clas="q-ma-xs" colr="purple-10" iclr="yellow" ttip="日 常 消 费" @click="openApp('exlist')" />
    <RoundButton size="22px" icon="add_shopping_cart" clas="q-ma-xs" colr="indigo-10"   ttip="采 购 清 单" @click="openApp('shopping')" />
    <RoundButton size="22px" icon="schedule"          clas="q-ma-xs" colr="cyan-10"    iclr="amber" ttip="温 馨 提 示" @click="openApp('reminder')" />
    <RoundButton size="22px" icon="assignment"        clas="q-ma-xs" colr="black"  ttip="备　忘　录" @click="openApp('memo')" />
    <RoundButton size="22px" icon="健" clas="q-ma-xs q-pb-sm" colr="red-10" iclr="yellow" ttip="每 天 看 看" @click="openApp('watcher')" />
    <RoundButton size="22px" icon="account_balance" clas="q-ma-xs" colr="indigo-10" iclr="amber"  ttip="银 行 月 报" @click="openApp('bankstatement')" />
    <RoundButton size="22px" icon="析" clas="q-ma-xs q-pb-sm" colr="green-10"  ttip="月 报 分 析" @click="openApp('holdings')" />
    <RoundButton size="22px" icon="bloodtype" clas="q-ma-xs" colr="red-10" iclr="lime" ttip="血 糖 控 制" @click="openApp('glucosecheck')" />
    <RoundButton size="22px" icon="文" clas="q-ma-xs q-pb-sm" colr="indigo-10"  ttip="网 上 阅 读" @click="openApp('arts')" />
    <RoundButton size="22px" icon="golf_course" clas="q-ma-xs" colr="teal-10" iclr="yellow"  ttip="高  尔  夫" @click="openApp('golf')" />
    <RoundButton size="22px" icon="translate" clas="q-ma-xs" colr="brown-10"  ttip="英 汉 字 典" @click="openApp('dictionary')" />
    <RoundButton size="22px" icon="card_giftcard" clas="q-ma-xs" colr="red-10"  ttip="联 邦 节 日" @click="showHolidays()" />
    <RoundButton size="22px" icon="palette" clas="q-ma-xs q-pb-x" colr="indigo-10"  ttip="Drawing" @click="openApp('painting')" />
    <RoundButton size="22px" icon="报" clas="q-ma-xs q-pb-sm" colr="purple-10"  ttip="信 用 卡 花 销" @click="openApp('bankstatementloader')" />
    <RoundButton size="22px" icon="转" clas="q-ma-xs q-pb-sm" colr="green-10"  ttip="Convert To Text" @click="openApp('totext')" />
    <RoundButton size="22px" icon="娅" clas="q-ma-xs q-pb-sm" colr="blue-10" iclr="amber" ttip="娅 莉 画 展" @click="openApp('yalipics')" />
    <RoundButton size="22px" icon="查" clas="q-ma-xs q-pb-sm" colr="green-10" iclr="cyan-2" ttip="健 康 检 查" @click="openApp('htlist')" />
    <RoundButton size="22px" icon="视" clas="q-ma-xs q-pb-sm" colr="amber-10" iclr="white" ttip="电 视 列 表" @click="openApp('tvmanager')" />
    <RoundButton size="22px" icon="group" clas="q-ma-xs" colr="indigo-10" iclr="amber" ttip="用 户 管 理" @click="refUserList.getUserList()" v-if="AppAdmin" />
    <RoundButton size="22px" icon="logout" clas="q-ma-xs" colr="amber-10" iclr="grey-10" ttip="系 统 Logout" @click="logout()" v-if="AppAdmin" />
    <RoundButton size="22px" icon="login" clas="q-ma-xs" colr="grey-10" iclr="amber" ttip="系 统 管 理" @click="login()" v-if="!AppAdmin" />
    <RoundButton size="22px" :icon="compVer" clas="q-ma-xs q-pb-sm" colr="blue-10" iclr="lime" ttip="系 统 信 息" @click="showSysInfo()" />
  </q-card-actions>
  <LoginAdmin />
  <UserList ref="refUserList" />
</q-card>
<PlatformDataPad ref="refPlatformDataPad" />
</template>
<script setup>
import { ref, onMounted, computed } from 'vue'
import emitter from 'tiny-emitter/instance'
import { useRouter } from 'vue-router'
const router = useRouter()

import { libFunctions } from '../../src/composables/libFunctions'
const { buildApp, userType, isDesk, q, $store, SysAdmin, AppAdmin } = libFunctions()
import { axiosFunctions } from '../../src/composables/axiosFunctions'
const { gaxios } = axiosFunctions()

import RoundButton from '../../src/components/RoundButton'
import LoginAdmin from '../../users/LoginDialog'
import UserList from '../../users/UserList'
import PlatformDataPad from '../components/PlatformDataPad'

const refPlatformDataPad = ref(null)

const refUserList = ref(null)
const compVer = computed(() => { return process.env.VER })

// console.log(`-ST-Index userType=${userType.value} isAdmin=${isAdmin.value}`)
console.log(`-ST-Index AppAdmin=${AppAdmin.value}`)
buildApp('Apps Home', '家庭应用')
emitter.on('user-type', (x) => userType.value = x)
onMounted(() => { refUserList; refPlatformDataPad })

//== function sections
function showSysInfo () { 
  // console.log(`-fn-showSysInfo ${compVER.value}`)
  refPlatformDataPad.value.openIt()
}
function openApp (app) {
   if (['../golf', '../arts', 'glucosecheck','exlist', 'reminder'].includes(app)) { // no need to reload for chart
    window.location.href = app // this navigates to app and also trigger to loading. otherwise <canvas> not working
  } else {
    router.replace({ path:app })
    console.log(`-CK-open app ${app}`)
  }
}
function login () {
  console.log('-CK-fn-login')
  emitter.emit('open-LoginDialog')
}
function showHolidays () {
  console.log('-CK-fn-showHolidays')
  emitter.emit('open-Holidays')
}
function logout () {
  console.log('-CK-fn-logout')
  const path = process.env.API + '/logout'
  userType.value = undefined
  q.localStorage.set('usertype', undefined)
  $store.commit('apps/setUserType', undefined)
  gaxios(path)
}
</script>

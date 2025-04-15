<template>
<div>
  <q-page padding style="background:white;line-height:1.5;font-size:20px;">
    <div class="q-pl-none">
      <!-- <q-input ref="edit" v-model="tit" :input-style="{ fontSize:'30px', color:'blue', fontWeight:500 }" autogrow /> -->
      <q-input v-model="tit" :input-style="{ fontSize:'30px', color:'blue', fontWeight:500 }" :disable="flwIdx>=0" />
      <!-- <textarea v-model="txt" :style="{ width:editWidth, height:editHeight }" style="padding:10px 10px;height:400px;line-height:1.8;font-size:22px;scrollbar" /> -->
      <!-- <textarea v-if="flwIdx>=0" v-model="store.flw[flwIdx].txt" style="padding:10px 10px;width:890px;height:700px;line-height:1.8;font-size:22px;scrollbar" /> -->
      <textarea v-model="txt" style="padding:10px 10px;width:890px;height:700px;line-height:1.8;font-size:22px;scrollbar" />
    </div>
  </q-page>
  <q-footer>
    <q-toolbar color="primary">
      <q-btn flat round icon="cancel" @click="closeEdit" />
      <q-toolbar-title  />
      <!-- <q-btn flat round icon="save" @click="saveEdit" :disable="isDeveloping" /> -->
      <q-btn v-if="isLocal" flat round icon="save" @click="saveEdit()" />
    </q-toolbar>
  </q-footer>
</div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
const route = useRoute()
const $router = useRouter()
import { axiosFunctions } from 'src/composables/axiosFunctions'
const { paxios } = axiosFunctions()
import { libFunctions } from 'src/composables/libFunctions'
const { store, isLocal } = libFunctions()
// name: 'ArtsEdit'

const flwIdx = computed(() => { return route.params.flwIdx })
const tit = computed({
  get: () => store.art.tit,
  set: (tit) => { console.log(`tit=${tit}`); store.art.tit = tit }
})

const txt = computed({
  get: () => {
    if (flwIdx.value >= 0) {
      var re = /<br \/>|☻|☺/gi
      return store.flw[flwIdx.value].txt.replace(re, '')
    } else {
      re = /<br \/>/gi
      return store.art.txt.replace(re, '')
    }
  },
  set (txt) {
    if (flwIdx.value >= 0) store.flw[flwIdx.value].txt = txt
    else store.art.txt = txt
  }
})

console.info(`-ST-ArtsEdit flwIdx=${flwIdx.value} tag=${store.tag} ymd=${store.ymd} qid=${store.qid} isLocal=${isLocal} txt=${txt.value} art=`, store.art)

function closeEdit () {
  $router.push({ name: 'text', params: { tag: store.tag, ymd: store.ymd, qid: store.qid } })
}
function saveEdit () {
  // flwIdx = route.params.flwIdx
  console.info(`-CK-saveEdit tag=${store.tag} ymd=${store.ymd} qid=${store.qid} art.tit=${store.art.tit} flwIdx=${flwIdx} art=`, store.art)
  var inData = {}
  if (flwIdx.value >= 0) { //. editing flw
    inData.flwIdx = flwIdx.value
    inData.artId = store.flw[flwIdx.value].id
    inData.txt = store.flw[flwIdx.value].txt
  } else {
    inData.datId = store.art.id
    inData.artId = store.art.artId
    inData.tit = tit.value
    inData.txt = txt.value
  }
  const path = process.env.API + '/arts/updText'
  console.log('-CK-inData', inData)
  paxios(path, inData)
  closeEdit()
}

// editWidth () { return is.fone() ? '330px' : '900px' },
// editHeight () { return is.fone() ? '330px' : '1500px' },
// const isDeveloping = computed(() => { return process.env.API === '/api' })
// const tag = computed(() => { return route.params.tag })
// const ymd = computed(() => { return route.params.ymd })
// const qid = computed(() => { return route.params.qid })
// const art = computed(() => { return store.art })
// const flw = computed(() => { return store.flw })


// const txt = computed({
//   get: () => {
//     if (flwIdx >= 0) {
//       var re = /<br \/>|☻|☺/gi
//       return store.flw[flwIdx].txt.replace(re, '')
//     } else {
//       re = /<br \/>/gi
//       return store.art.txt.replace(re, '')
//     }
//   },
//   set (txt) {
//     if (flwIdx >= 0) store.flw[flwIdx].txt = txt
//     else store.art.txt = txt
//   }
// })

// const artId = computed(() => { return flwIdx >= 0 ? flw[flwIdx].id : art.value.artId })
</script>

<style>
textarea::-webkit-scrollbar {
    width: 15px;  /* Remove scrollbar space */
    background: transparent;  /* Optional: just make scrollbar invisible */
}
/* Optional: show position indicator in red */
textarea::-webkit-scrollbar-thumb {
    background: #FF0000;
}
</style>

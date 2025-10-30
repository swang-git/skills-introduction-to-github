<template>
<q-dialog v-model="opened" :position="position">
  <div>
    <div class="q-py-sm text-h5 text-center bg-cyan-9 text-white" style="border-radius:50px">Setting {{ tag }} for {{ teebox.teebox }} </div>
    <table style="y-overflow:auto;margin:auto">
      <q-tr>
        <q-td><q-btn glossy icon="restore" round color="green-10" size="xl" @click="restoreOrigVal()" /></q-td>
        <q-td v-for="i in ['1', '2', '3']" :key=i.x><q-btn round class="num-pad" @click="setNumber(i)"><b class="text-h4">{{i}}</b></q-btn></q-td>
      </q-tr>
      <q-tr>
        <q-td v-for="i in ['0', '4', '5', '6']" :key=i.x><q-btn round class="num-pad" @click="setNumber(i)"><b class="text-h4">{{i}}</b></q-btn></q-td>
      </q-tr>
      <q-tr>
        <q-td><q-btn glossy icon="chevron_left" round color="amber" size="xl" v-close-popup /></q-td>
        <q-td v-for="i in ['7', '8', '9']"      :key=i.x><q-btn round class="num-pad" @click="setNumber(i)" v-on:dblclick="doNothing()"><b class="text-h4">{{i}}</b></q-btn></q-td>
      </q-tr>
    </table>
  </div>
</q-dialog>
</template>
<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'
import { axiosFunctions } from '../composables/axiosFunctions'
const { gaxios, paxios } = axiosFunctions()
const position = ref('standard')
const opened = ref(false)
var teebox = {}
var tag = null
var origTeebox = {}
var courseName = null

console.log('-ST-numberPadDecimal')

function doNothing () {
  console.log('double clicking ... do nothing')
}
emitter.on('golf-addTeebox', (x) => {
  teebox.id = x.tee.value
  console.log(`-CK-teebox.id=${teebox.id}`, x)
})
function restoreOrigVal () {
  console.log(`-CK-fn-restoreOrigVal tag=${tag} origTeebox=${origTeebox}`)
  teebox[tag] = origTeebox[tag]
  opened.value = false
}
function setNumber (np) {
  if (tag === 'rating') {
    let rating = String(teebox.rating).replace('.', '')
    rating += np
    if (parseFloat(rating) % 10 === 0) teebox.rating = rating/10 + '.0'
    else teebox.rating = parseFloat(rating)/10
  } else {
    if(teebox[tag] == undefined) teebox[tag] = ''
    teebox[tag] += np
    console.log(`-CK-fn-setNumber tag=${tag} np=${np} teebox[${tag}]=${teebox[tag]}`)
  }
  const yardage = parseInt(teebox['yardage'])
  const slope = parseInt(teebox['slope'])
  const par = parseInt(teebox['par'])
  const rating = parseFloat(teebox['rating'])
  if ( (tag === 'yardage' && yardage > 4000)
    || (tag === 'slope' && slope > 80)
    || (tag === 'par' && par > 60)
    || (tag === 'rating' && rating >= 60) ) {
      opened.value = false
      if (teebox.id > 0) {
        const path = process.env.API + '/golf/updCourseTee' + '/' + teebox.id + '/' + tag + '/' + teebox[tag]
        gaxios(path)
      } else {
        let inData = origTeebox
        inData.course = courseName
        inData[tag] = teebox[tag]
        const path = process.env.API + '/golf/addTeebox'
        paxios(path, inData)
      }
    }
  // if ((tag === 'yardage' && yardage > 4000) opened.value = false
  // else if (tag === 'slope' && slope > 80) opened.value = false
  // else if (tag === 'par' && par > 60) opened.value = false
  // else if(tag === 'rating' && rating > 60) opened.value = false
}
defineExpose({ openIt })
function openIt (pos, tg, cname, tbox) {
  console.log(`-CK-fn-openIt pos=${pos} tag=${tg} teebox=`, tbox)
  position.value = pos
  courseName = cname
  tag = tg
  origTeebox = JSON.parse(JSON.stringify(tbox))
  tbox[tg] = ''
  teebox = tbox
  opened.value = true
}
</script>
<style>
.num-pad {
  width: 60px;
  height: 60px;
  font-size: 25px;
  background: teal;
  color: white;
}
.num-pad-score {
  width: 60px;
  height: 60px;
  font-size: 25px;
  background: red;
}
</style>

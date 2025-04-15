<template>
<q-dialog v-model="opened">
  <div class="row bg-teal-9 q-pa-md text-cyan-1 text-h5" style="margin:-450px 0 0 200px;width:420px">
    <div class="col-3" v-for="colr in tees.slice(0, 4)"  :key=colr.x><q-btn flat dense class="color-pad" @click="setTeebox(colr)">{{colr}}</q-btn></div>
    <div class="col-3" v-for="colr in tees.slice(4, 9)"  :key=colr.x><q-btn flat dense class="color-pad" @click="setTeebox(colr)">{{colr}}</q-btn></div>
    <div class="col-3" v-for="colr in tees.slice(9, 12)" :key=colr.x><q-btn flat dense class="color-pad" @click="setTeebox(colr)">{{colr}}</q-btn></div>
    <div class="col-3" v-for="colr in tees.slice(12, 15)" :key=colr.x><q-btn flat dense class="color-pad" @click="setTeebox(colr)">{{colr}}</q-btn></div>
  </div>
</q-dialog>
</template>
<script setup>
import { ref } from 'vue'
var tees = []
var teebox =null
const opened = ref(false)

console.log(`-ST-TeeboxPad`)

function setTeebox (colr) {
  // console.log('-CK-setTeebox', this.teebox, this.tees, colr)
  teebox.teebox = colr
  opened.value = false
}
defineExpose({ openIt })
function openIt (tbox, tes) {
  // tes.push('Combo')
  console.log('-CK-openIt', tbox, tes)
  teebox = tbox
  tees = tes
  opened.value = true
}
</script>

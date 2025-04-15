<template>
<div style="position:relative;margin:auto">
  <div class="text-h6 text-cyan-3 q-pl-sm">Princeton Golf Club {{ year }} Tournaments</div>
  <div v-for="tmnt in selections" :key=tmnt>
    <div class="q-pa-sm q-pl-sm row items-start q-gutter-md">
      <q-card class="my-card text-white" :class="{ 'bg-cyan-9':todo=='Signup', 'bg-teal-9':todo=='Grouping'}" style="width:450px">
        <q-card-section>
          <div class="text-h6">Club {{ tmnt.game }}</div>
        </q-card-section>

        <q-separator dark />

        <q-card-section>
          <ul style="font-size:18px;line-height:1.4">
            <li>Game Time: <span>{{ tmnt.start_at }}</span> </li>
            <li>Men's tee: {{ tmnt.mtee }} </li>
            <li>Ladies tee: {{ tmnt.ltee }} </li>
            <li>Fees: ${{ tmnt.fees }} </li>
            <li v-if="tmnt.teetime_gap>0">Groups Tee Off: Every {{ tmnt.teetime_gap }} Minutes</li>
            <li v-else>Shutgun start at: {{ tmnt.start_at.substring(11, 16) }} </li>
            <li v-if="tmnt.note!=null">Notes: <div v-html="tmnt.note" /></li>
            <li>Course: <span>{{ tmnt.courseName }}</span> </li>
          </ul>
        </q-card-section>

        <q-separator dark />

        <q-card-actions align="right" class="">
          <q-btn glossy color="teal-10" :label="tmnt.game + ' Group Assignments'" @click="userSelected(tmnt)" />
        </q-card-actions>
      </q-card>
    </div>
  </div>
</div>
</template>
<script setup>
import { ref } from 'vue'
const emit = defineEmits(['user-selected'])
// const props = defineProps({
defineProps({
  selections: { type: Array },
  todo: { type: String },
})
var year = null
const selectedId = ref(-1)

console.log('-ST-CardSelection')
year = new Date().getFullYear()

function userSelected (tmt) {
  selectedId.value = tmt.id
  // console.log('-fn-user-selected', tmt)
  emit('user-selected', tmt)
}
</script>
<!-- <style lang="sass" scoped>
.my-card
  width: 100%
  max-width: 370px
</style> -->

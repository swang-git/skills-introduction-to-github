<template>
<div v-if="isDesk" class="row q-pr-none bg-teal-9">
  <div>
    <q-btn style="margin:-10px 0 0 -60px" class="text-yellow-4" flat round @click="openNumPad()" :label="pItemsPerPage" />
    <div class="q-pr-md q-pb-xs fixed-bottom-right">
      <q-pagination v-model="curPage" @click="sendCurPage()" color="amber-10" text-color="cyan-1" :min="1" :max="pNumPages" :max-pages="15" direction-links boundary-links />
    </div>
  </div>
</div>
<div v-else class="row q-pt-xs" style="height:49px">
  <q-btn style="margin:-5px 0px 0 -16px" class="text-white" flat round @click="openNumPad()">{{ pItemsPerPage }}</q-btn>
  <div class="q-pb-sm fixed-bottom-right">
    <q-pagination v-model="curPage" @click="sendCurPage()" color="amber-10" text-color="cyan-1" :min="1" :max="pNumPages" :max-pages="2"  direction-links boundary-links />
  </div>
</div>
<PageNumPad />
</template>
<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'
import { libFunctions } from '../../src/composables/libFunctions'
const { isDesk } = libFunctions()
import PageNumPad from './PageNumPad'

const props = defineProps ({
  pNumPages: { type: Number },
  pItemsPerPage: { type: Number }
})

const curPage = ref(1)

console.log('-ST-Pagination')
const emit = defineEmits(['cur-page'])

function openNumPad () { emitter.emit('open-PageNumPad') }
function sendCurPage () {
  console.log(`-fn-sendCurPage curPage=${curPage.value}, pItemsPerPage=${props.pItemsPerPage}`)
  emitter.emit('cur-page', curPage.value)
}
</script>

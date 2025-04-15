<template>
<q-dialog v-model="opened">
  <q-card class="bg-cyan-10 text-h6" dark square style="min-width:700px">
    <q-card-actions class="text-h5 bg-teal-10" align="between">
      <q-btn glossy round color="amber-10" icon="chevron_left" v-close-popup />
      Matched Spends
      <q-btn glossy round color="amber-10" icon="chevron_right" v-close-popup />
    </q-card-actions>
    <q-card-actions align="between">
      <div class="text-h6">{{ lookup[0] }}</div>
      <div class="text-h6">{{ lookup[1] }}</div>
      <div class="text-h6"><span class="ellipsis" style="width:388px">{{ lookup[3] }}</span></div>
      <div class="text-h6 q-pr-sm">{{ lookup[4] }}</div>
    </q-card-actions>
    <q-card-actions align="between">
      <div class="text-h6">{{ matched.purchasedate }}</div>
      <div class="text-h6" v-if="matched.postdate!=null">{{ matched.postdate }}</div>
      <div class="text-h6"><span class="ellipsis" style="width:222px">{{ matched.payee }}</span></div>
      <q-btn flat class="text-h6" @click="confirmSetPostDate(matched)">{{ matched.cost }}
        <q-tooltip v-if="matched.postdate==null" class="bg-green-10 text-h6 text-no-wrap">SET post_date={{ postDate }} for spending {{ matched.id }}</q-tooltip>
        <q-tooltip v-else class="bg-green-10 text-h6 text-no-wrap">post_date={{ postDate }}({{ matched.id }})</q-tooltip>
      </q-btn>
    </q-card-actions>
  </q-card>
</q-dialog>
<ConfirmDialog ref="refConfirmDialog" @user-confirmed="setPostDate" />
</template>
<script setup>
import { ref, onMounted } from 'vue'
import emitter from 'tiny-emitter/instance'
import { axiosFunctions } from '../src/composables/axiosFunctions'
import ConfirmDialog from '../src/components/ConfirmDialog'
const { gaxios } = axiosFunctions()

const opened = ref(false)
var postDate = null
var matched = ref({})
var lookup = []
var poMatched = null

defineExpose({openIt})

console.log(`-ST-MatchedSpendsDialog`)
// emitter.on('open-MatchedSpendsDialog', (x, y, z) => openIt(x, y, z))
const emit = defineEmits(['set-post-date'])
emitter.on('bankstatementloader-setPostDate', () => emit('set-post-date'))
emitter.on('user-confirmed', (act) => setPostDate(act))

function openIt(x, y, z) {
  console.log(`-CK-fn-openIt postDate=${x} lookup=${z}`, y)
  opened.value = true
  postDate = x
  matched.value = y
  lookup = z
}
// function getPostDate (p) {
//   console.log(`-fn-getPostDate need more work for the beginning/ending open/closeDate`)
//   let podate = new Date().getFullYear() + '-' + lookup[0].replace('/', '-')
//   return podate
// }
const refConfirmDialog = ref(null)
onMounted(() => {
  // refConfirmDialog.value
  console.log(`-MT-refConfirmDialog=${refConfirmDialog.value}`)
})
function confirmSetPostDate (p) {
  poMatched = p
  // let podate = new Date().getFullYear() + '-' + lookup[0].replace('/', '-')
  // let tit = 'Confirm to Set Post Date (id:' + p.id + ')'
  let tit = 'Confirm to Set Post Date for ' + p.id
  let msg = 'Please comfirm for setting post_date to 『' + postDate + '』 for spending at 『' + p.payee + '』 on ' + p.purchasedate
  // emitter.emit('open-ConfirmDialog', tit, msg, 'set-post-date')
  refConfirmDialog.value.openIt(tit, msg, 'set-post-date')
  console.log(`-fn-confirmSetPostDate id=${p.id}`)
  matched.value.postdate = postDate
}
function setPostDate (act) {
  if (act != 'set-post-date') return
  // let podate = new Date().getFullYear() + '-' + lookup[0].replace('/', '-')
  // console.log(`-fn-setPostDate pid=${matched[0].id} postDate=${postDate}`)
  // console.log(`-fn-setPostDate pid=${matched.id} postDate=${postDate}`)
  const path = process.env.API + '/bankstatementloader/setPostDate/' + matched.value.id + '/' + postDate
  gaxios(path)
  opened.value = false
}
</script>

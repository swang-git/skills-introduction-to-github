<template>
<q-dialog v-model="opened">
  <q-layout view="lHh lpr lFf" container style="height:600px;width:500px" class="bg-teal-8 shadow-3 rounded-borders fixed-center">
    <q-card class="bg-teal-8 fixed z-top" style="width:500px">
      <q-card-actions align="between" class="no-wrap">
        <q-btn round glossy color="amber-10" icon="chevron_left" v-close-popup />
        <div class="text-h5 text-center text-white text-no-wrap ellipsis"> {{ title }} </div>
        <q-btn round glossy color="green-10" :icon="compIcon" @click="saveUpdUserGuide()" />
      </q-card-actions>
    </q-card>
    <div class="q-pa-sm">
      <q-editor v-model="userguide" :min-height="isIM ? '10px' : '460px'"  style="margin-top:60px"
        :definitions="{ toolbar: { tip:'<b class=text-h6>Add the userguide here</b>', icon:icon, label:label, color:'cyan-10' }}"
        :toolbar="[ ['left', 'center', 'right', 'justify', 'bold', 'italic', 'strike', 'underline', 'token', 'hr', 'link', 'custom_btn', 'print', 'fullscreen',
          'quote', 'unordered', 'ordered', 'outdent', 'indent', 'undo', 'redo', 'viewsource',
          {
            label: $q.lang.editor.formatting,
            icon: $q.iconSet.editor.formatting,
            list: 'no-icons',
            options: [
              'p',
              'h1',
              'h2',
              'h3',
              'h4',
              'h5',
              'h6',
              'code'
            ]
          },
          {
          label: $q.lang.editor.fontSize,
          icon: $q.iconSet.editor.fontSize,
          fixedLabel: true,
          fixedIcon: true,
          list: 'no-icons',
          options: [
            'size-1',
            'size-2',
            'size-3',
            'size-4',
            'size-5',
            'size-6',
            'size-7'
          ]
        },
        'removeFormat'], ['toolbar']]"
          :fonts="{
          arial: 'Arial',
          arial_black: 'Arial Black',
          comic_sans: 'Comic Sans MS',
          courier_new: 'Courier New',
          impact: 'Impact',
          lucida_grande: 'Lucida Grande',
          times_new_roman: 'Times New Roman',
          verdana: 'Verdana'
        }"
      />
    </div>
  </q-layout>
</q-dialog>
</template>
<script setup>
/* eslint-disable */
import { ref, computed } from 'vue'
import { useQuasar } from 'quasar'
import emitter from 'tiny-emitter/instance'
import { axiosFunctions } from 'src/composables/axiosFunctions'
import { libFunctions } from 'src/composables/libFunctions'
console.info('-ST-UserGuidePad')
const $q = useQuasar()
const { isIM } = libFunctions()
const { paxios } = axiosFunctions()
const pagename = ref(null)
const icon = ref(null)
const label = ref(null)
const userguide = ref('')
const title = ref('')
const id = ref(0)
const opened = ref(false)
const compIcon = computed(() =>{
  if (userguide.value != '') return 'update'
  else return 'save'
})
emitter.on('open-UserGuidePad', (x,y,z) => openIt(x,y,z))
function openIt (pgname, Id=0, uguide='') {
  id.value = Id
  pagename.value = pgname
  userguide.value = uguide
  console.log(`-fn-openIt UserGuidePad id=${id.value}, userguide=${userguide.value}`)
  title.value = "Add User Guide for " + pagename.value + " Page"
  if (userguide.value !== '') title.value = "Update User Guide for (" + pagename.value + ")"
  opened.value = true
}
function saveUpdUserGuide () {
  if (id.value > 0) {
    console.info(`-fn-UPDATE UserGuide id=${id.value} pagename=${pagename.value} userguide=${userguide.value}`)
    const path = process.env.API + '/golf/updUserGuide'
  } else {
    console.info(`-fn-SAVE UserGuide id=${id.value} pagename=${pagename.value} userguide=${userguide.value}`)
    const path = process.env.API + '/golf/saveUserGuide'
  }

  const inData = { id:id.value, page_name:pagename.value, user_guide:userguide.value }
  paxios(path)
  opened.value = false
}
</script>

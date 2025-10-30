<template>
<q-dialog v-model="opened">
  <q-layout view="lHh lpr lFf" container style="height:300px" class="bg-teal-8 shadow-3 rounded-borders fixed-center">
    <div class="q-pa-sm text-body1">
      <q-editor v-model="details" :min-height="isIM ? '10px' : '70px'"
        :definitions="{ toolbar: { tip:'<b class=text-h6>Add the details here</b>', icon:icon, label:label, color:'cyan-10' }}"
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
    <q-card class="bg-teal-8">
      <q-card-actions align="between">
        <q-btn round glossy color="amber-10" icon="chevron_left" v-close-popup />
        <div class="text-h5 text-center text-white"> Fill out Details </div>
        <q-btn round glossy color="green-10" icon="save" @click="saveDetails()" />
      </q-card-actions>
    </q-card>
  </q-layout>
</q-dialog>
</template>
<script setup>
import { ref } from 'vue'
import { libFunctions } from '../composables/libFunctions'
const { isIM } = libFunctions()
import emitter from 'tiny-emitter/instance'
const emit = defineEmits(['save-details'])
const props = defineProps({
  icon: { type: String },
  label: { type: String },
})

const details = ref(null)
const opened = ref(false)

console.log('-ST-NotePad')
emitter.on('open-NotePad', (x) => openIt(x))

function saveDetails () {
  console.log('-fn-saveDetails')
  emit('save-details', details.value)
  opened.value = false
}
function openIt (det) {
  opened.value = true
  if (det == null) details.value = 'No details for this reminder'
  else details.value = det
}
</script>

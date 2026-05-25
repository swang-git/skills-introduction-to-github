<template>
<q-dialog v-model="opened">
  <q-card class="bg-teal-9" style="width:100%;height:300px" rounded>
    <div class="text-h5 q-pt-md bg-teal-9 text-cyan-2 text-center">{{ tit }}</div>
    <q-btn class="q-pr-sm float-right" style="margin:-35px 14px 0 0" flat icon="content_copy" color="cyan-2" @click="copyToClipboard(fnm)" />
    <q-card-section>
      <!-- <q-input re="inpref" v-model='fnm' type="textarea" autogrow dark counter input-style="line-height:1.2;min-height:140px" input-class="text-h6 bg-teal-10 q-px-xs" /> -->
       <div class="text-h5 text-cyan-3">File Name: {{ fnm }}</div>
       <div class="text-h5 text-cyan-3">File Size: {{ fsz }}</div>
    </q-card-section>
    <!-- <q-card-actions v-if="idx != -9876" align="between" class="bg-teal-9 q-pb-xs">
      <q-btn round glossy color="amber-9" icon="chevron_left" v-close-popup />
      <q-btn round glossy color="green-9" icon="add_circle" @click="saveLnk()" />
    </q-card-actions> -->
  </q-card>
</q-dialog>
</template>
<script setup>
import emitter from 'tiny-emitter/instance'
import { ref, onMounted } from 'vue'
// const emit = defineEmits(['upd-lnk', 'upd-selected-opt'])
// const idx = ref(-1)
const fnm = ref(null)
const fsz = ref(null)
const tit = ref(null)
const opened = ref(false)
const inpref = ref(null)

onMounted(() => inpref)

// console.log('-ST-TxtPad')

emitter.on('open-TxtPad', (fn, fz, ft) => openIt(fn, fz, ft))

async function copyToClipboard(text) {
  // Solution 1: Check if Clipboard API is available
  if (navigator.clipboard) {
    try {
      await navigator.clipboard.writeText(text);
      return true;
    } catch (err) {
      console.warn('Clipboard API failed, trying fallback', err);
    }
  }
  // Solution 2: Textarea fallback method
  try {
    const textarea = document.createElement('textarea');
    textarea.value = fnm.value;
    textarea.style.position = 'fixed'; // Prevent scrolling
    textarea.style.opacity = '0'; // Make invisible
    document.body.appendChild(textarea);
    textarea.select();
    
    // Fallback for older browsers
    let success = false;
    if (document.queryCommandSupported('copy')) {
      success = document.execCommand('copy');
    }
    
    document.body.removeChild(textarea);
    console.info('txt copied');
    return success;
  } catch (err) {
    console.error('All copy methods failed', err);
    return false;
  }
}

// Usage example
// document.getElementById('copyButton').addEventListener('click', async () => {
//   const success = await copyToClipboard('Text to copy');
//   if (success) {
//     alert('Copied successfully!');
//   } else {
//     alert('Copy failed. Please manually select and copy.');
//   }
// });

function openIt(fn, fz, ft) {
  console.log(`-fn-TxtPad.openIt fn=${fn} fz=${fz}, ft=${ft}`)
  fnm.value = fn
  fsz.value = fz
  tit.value = ft
  opened.value = true
}
// function saveLnk () {
//   console.log(`-fn-saveLnk idx=${idx.value} txt=${txt.value} title=${title.value}`)
//   if (Number.isInteger(idx.value)) emit('upd-lnk', idx.value, txt.value)
//   else emit('upd-selected-opt', idx.value, txt.value)
//   opened.value = false
// }
</script>

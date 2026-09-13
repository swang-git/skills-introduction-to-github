<template>
  <q-dialog v-model="opened" maximized>
    <q-layout class="q-pa-md bg-teal-10 text-cyan-2 text-h6" v-close-popup>
      <q-card flat class="bg-teal-10 text-white text-h6 justify-center" style="border:cyan 2px solid; border-radius:0%">
        <ShadowBox class="float-right" style="margin:-8px -5px 0 0;border-radius:50%;border:yellow 2px solid" :val="compVer" />
        <tbody v-if="isDesk" class="text-h6">
          <tr v-for="(chunk, idx) in platformChunks" :key="idx">
            <td class="text-right q-px-sm">{{ chunk[0][0] }}:</td>
            <td>{{ chunk[0][1] ? '✅ Yes' : '❌ No' }}</td>
            <!-- <td>{{ chunk[0][1] }}</td> -->

            <template v-if="chunk[1]">
              <!-- <td class="q-pl-xl text-right q-px-sm">{{ chunk[1][0].toUpperCase() }}:</td> -->
              <td class="q-pl-xl text-right q-px-sm">{{ chunk[1][0] }}:</td>
              <td>{{ chunk[1][1] ? '✅ Yes' : '❌ No' }}</td>
              <!-- <td>{{ chunk[1][1] }}</td> -->
            </template>
            <template v-if="chunk[2]">
              <td class="q-pl-xl text-right q-px-sm">{{ chunk[2][0] }}:</td>
              <td>{{ chunk[2][1] ? '✅ Yes' : '❌ No' }}</td>
              <!-- <td>{{ chunk[1][1] }}</td> -->
            </template>
            <template v-if="chunk[3]">
              <td class="q-pl-xl text-right q-px-sm">{{ chunk[3][0] }}:</td>
              <td>{{ chunk[3][1] ? '✅ Yes' : '❌ No' }}</td>
              <!-- <td>{{ chunk[1][1] }}</td> -->
            </template>
            <template v-else>
              <td></td>
              <td></td>
            </template>
          </tr>
        </tbody>
        <tbody v-else class="text-h6">
          <tr v-for="(value, prop) in $q.platform.is" :key="prop"><td class="text-right q-px-sm">{{ prop.toUpperCase() }}:</td><td>{{ value }}</td></tr>
        </tbody>
      </q-card>
      <q-card flat class="q-mt-xs bg-teal-10 text-white text-h6 justify-center" style="border:cyan 2px solid; border-radius:0%">
        <tr v-for="(entr, idx) in nameEntries" :key="idx">
          <td class="text-right q-px-md" style="width:215px">{{ entr[0] }}</td><td>{{ typeof entr[1] === 'boolean' ? entr[1] ? '✅ Yes' : '❌ No' : entr[1] }}</td>
        </tr>
        <tr><td class="text-right q-px-md" style="width:215px">Screen Width</td><td>{{ screenwidth }}</td></tr>
        <tr><td class="text-right q-px-md" style="width:215px">Screen Height</td><td>{{ screenheight }}</td></tr>
        <tr><td class="text-right q-px-md" style="width:215px">Touch Screen</td><td>The device <strong>{{ touch }}</strong> touch capability.</td></tr>
        <tr><td class="text-right q-px-md" style="width:215px">Browser User Agent</td><td>{{ $q.platform.userAgent }}</td></tr>
      </q-card>
    </q-layout>
  </q-dialog>
</template>

<script setup>
import { computed, ref, onMounted } from "vue";
import { Platform } from "quasar";
import { libFunctions } from "../composables/libFunctions"
const { screenwidth, screenheight, $q, isDesk } = libFunctions()
import ShadowBox from "../components/ShadowBox.vue"
const compVer = computed(() => { return import.meta.env.VITE_BUILD_TAG })
const touch = computed(() => ($q.platform.has.touch ? "has" : "does not have"));

const opened = ref(false);
const isLinux = ref(false)
const isMac = ref(false)
const UA = ref(null)
defineExpose({ openIt });

onMounted(() => {
  // Evaluate only after browser mounts
  isLinux.value = $q.platform.is.linux
  isMac.value = $q.platform.is.mac
  UA.value = navigator.userAgent
  console.log({ isLinux: isLinux.value, isMac: isMac.value })
})
console.log(`-ST-PlatformDataPad`, Platform.is)

// turn object to sorted array [ [prop,value], ... ]
// const entries = Object.entries(Platform.is).sort(([a], [b]) => a.localeCompare(b))
// const entries = Object.entries(Platform.is).sort(([a], [b]) => a[1] - b[1])
const boolEntries = Object.entries(Platform.is).filter(p =>!['versionNumber','platform','version','name','nativeMobile','capacitor','desktop'].includes(p[0])).sort((a, b) => b[1] - a[1])
const nameEntries = Object.entries(Platform.is).filter(p => ['versionNumber','platform','version','name','nativeMobile','capacitor','desktop'].includes(p[0]))

// chunk array into groups of 4
const platformChunks = []
for (let i = 0; i < boolEntries.length; i += 4) {
  platformChunks.push(boolEntries.slice(i, i + 4))
}
console.log(`-ST-PlatformDataPad pltformChunks:`, platformChunks)

function openIt() {
  opened.value = true;
}
</script>

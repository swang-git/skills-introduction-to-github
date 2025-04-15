<template>
  <q-dialog v-model="opened" maximized>
    <q-layout class="q-pa-md bg-teal-10 text-cyan-2 text-h6" v-close-popup>
      <q-card flat class="bg-teal-10 text-white text-h6 justify-center" style="border:cyan 2px solid; border-radius:8%">
        <ShadowBox class="float-right" style="border-radius:50%;border:yellow 2px solid" :val="compVer" />
        <tbody class="text-h6">
          <tr v-for="(value, prop) in $q.platform.is" :key="prop"><td class="text-right q-px-sm">{{ prop.toUpperCase() }}:</td><td>{{ value }}</td></tr>
          <tr><td class="text-right q-px-md">Screen Width</td><td>{{ screenwidth }}</td></tr>
          <tr><td class="text-right q-px-md">Screen Height</td><td>{{ screenheight }}</td></tr>
          <!-- <tr><td class="text-right q-px-md">iPhone</td><td>{{ iPhone }}</td></tr> -->
          <tr><td class="text-right q-px-md">iPhone13</td><td>{{ iPhone13 }}</td></tr>
          <tr><td class="text-right q-px-md">isMate</td><td>{{ isMate }}</td></tr>
        </tbody>
      </q-card>

      <div class="q-mt-md">
        The device <strong>{{ touch }}</strong> touch capability.
      </div>
      <div class="row justify-center"> 
        Browser User Agent: {{ $q.platform.userAgent }}
      </div>
    </q-layout>
  </q-dialog>
</template>

<script setup>
import { computed, ref } from "vue";
import { useQuasar } from "quasar";
import { libFunctions } from "../composables/libFunctions"
import ShadowBox from "../components/ShadowBox"
const compVer = computed(() => { return process.env.VER })

const $q = useQuasar();
const touch = computed(() => ($q.platform.has.touch ? "has" : "does not have"));
const { isMate, iPhone13, screenwidth, screenheight } = libFunctions()

const opened = ref(false);
defineExpose({ openIt });

function openIt() {
  opened.value = true;
}
</script>

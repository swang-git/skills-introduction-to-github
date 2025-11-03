<template>
  <q-dialog v-model="opened" :full-width="isDesk" :maximized="isIM" position="top">
    <div :class="{ 'q-ml-xl': isDesk }">
      <TeamMatchPlayDataChart :scores="scores" :name="name" />
    </div>
  </q-dialog>
</template>
<script setup>
import { ref, createApp } from "vue";
import emitter from "tiny-emitter/instance";
import { libFunctions } from "src/composables/libFunctions";
import TeamMatchPlayDataChart from "../pages/TeamMatchPlayDataChart";
defineProps({
  scores: { type: Array },
  name: { type: String },
});
const app = createApp({});
app.component("TeamMatchPlayDataChart", TeamMatchPlayDataChart);
const { isDesk, isIM } = libFunctions();
emitter.on("open-ChartsProxy", () => {
  opened.value = true;
});
const opened = ref(false);
console.log(`-ST-ChartsProxy`);
</script>

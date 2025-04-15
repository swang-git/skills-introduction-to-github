<template>
<q-card square class="bg-cyan-2">
  <q-card-actions v-if="chart=='cata'" class="text-h6" align="between">
    <ButtonOne chart="mybb" cicon="bubble_chart" />
    <ButtonOne chart="moyr" cicon="月/年" csize="16px" />
    <ButtonOne chart="year" cicon="年" />
    <ButtonOne chart="cats" cicon="donut_small" csize="25px" />
    <ButtonOne chart="yrmo" cicon="年/月" csize="16px" />
    <q-btn round glossy icon="cancel" color="amber-10" v-close-popup />
  </q-card-actions>
  <q-card-actions v-if="chart=='mybb'" class="text-h6" align="between">
    <ButtonOne chart="cats" cicon="donut_small" csize="25px" />
    <ButtonOne chart="moyr" cicon="月/年" csize="16px" />
    <ButtonOne chart="year" cicon="年" />
    <ButtonOne chart="cata" cicon="全" />
    <ButtonOne chart="yrmo" cicon="年/月" csize="16px" />
    <q-btn round glossy icon="cancel" color="amber-10" v-close-popup />
  </q-card-actions>
  <q-card-actions v-if="chart=='cats'" class="text-h6" align="between">
    <ButtonOne chart="mybb" cicon="bubble_chart" />
    <ButtonOne chart="moyr" cicon="月/年" csize="16px" />
    <ButtonTwo chart="cats" @move-chart="moveChart" />
    <ButtonOne chart="cata" cicon="全" cstyl="'padding: -100px 1px 9px 1px'" />
    <ButtonOne chart="yrmo" cicon="年/月" csize="16px" />
    <q-btn round glossy icon="cancel" color="amber-10" v-close-popup />
  </q-card-actions>
  <q-card-actions v-if="chart=='moyr'" class="text-h6" align="between">
    <ButtonOne chart="mybb" cicon="bubble_chart" />
    <ButtonOne chart="cats" cicon="donut_small" csize="25px" />
    <ButtonTwo chart="moyr" @move-chart="moveChart" />
    <ButtonOne chart="cata" cicon="全" />
    <ButtonOne chart="yrmo" cicon="年/月" csize="16px" />
    <q-btn round glossy icon="cancel" color="amber-10" v-close-popup />
  </q-card-actions>
  <q-card-actions v-if="chart=='yrmo'" class="text-h6" align="between">
    <ButtonOne chart="mybb" cicon="bubble_chart" />
    <ButtonOne chart="cats" cicon="donut_small" csize="25px" />
    <ButtonTwo chart="yrmo" @move-chart="moveChart" />
    <ButtonOne chart="cata" cicon="全" />
    <ButtonOne chart="moyr" cicon="月/年" csize="16px" />
    <q-btn round glossy icon="cancel" color="amber-10" v-close-popup />
  </q-card-actions>
  <q-card-actions v-if="chart=='year'" class="text-h6" align="between">
    <ButtonOne chart="mybb" cicon="bubble_chart" />
    <ButtonOne chart="yrmo" cicon="年/月" csize="16px" />
    <ButtonOne chart="cata" cicon="全" />
    <ButtonOne chart="moyr" cicon="月/年" csize="16px" />
    <ButtonOne chart="cats" cicon="donut_small" csize="25px" />
    <q-btn round glossy icon="cancel" color="amber-10" v-close-popup />
  </q-card-actions>
  <q-card-actions v-if="chart=='ympi'" class="text-h6" align="between">
    <ButtonOne chart="mybb" cicon="bubble_chart" />
    <ButtonOne chart="cats" cicon="donut_small" csize="25px" />
    <ButtonTwo chart="ympi" @move-chart="moveChart" />
    <ButtonOne chart="cata" cicon="全" :cstyl="'padding: -4px 1px 9px 1px'" />
    <ButtonOne chart="moyr" cicon="月/年" csize="16px" />
    <q-btn round glossy icon="cancel" color="amber-10" v-close-popup />
  </q-card-actions>
</q-card>
</template>
<script setup>
// import { ref, createApp } from 'vue'
import ButtonOne from './ButtonOne'
import ButtonTwo from './ButtonTwo'
const props = defineProps({ chart: { type: String } })
const emit = defineEmits(['move-chart'])
// const app = createApp({})
// app.component(ButtonOne, ButtonOne) // no need to register or cause warning
// app.component(ButtonTwo, ButtonTwo)
// const ptag = ref(null)
// const ntag = ref(null)
// const nx = ref({ year:true, yrmo:true, moyr:true, mybb:true, ympi:true, cats:true, cata:true })
// console.log(`-ST-AllButtons chart=${props.chart} ptag=${ptag.value} ntag=${ntag.value}`);
console.log(`-ST-AllButtons chart=${props.chart}`);
function moveChart (pn) {
  console.log(`-fn-moveChart emit to Chart moveChart pn=${pn}`);
  emit('move-chart', pn)
}
</script>

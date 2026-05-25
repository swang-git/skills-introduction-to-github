<!-- <template>
  <router-view />
</template>

<script setup>
//
</script> -->

<!-- App.vue -->
<template>
  <q-layout>
    <router-view />
    <!-- ONLY ONE Acompo instance in the entire app -->
    <NumPad @jump-to-page = "(idx) => emitter.emit('jump-to-page', idx)"
            @per-page = "(idx) => emitter.emit('per-page', idx)"
            @pix-pidx = "(idx) => emitter.emit('pix-pidx', idx)"
    />
  </q-layout>
</template>111

<script setup>
import emitter from 'tiny-emitter/instance.js'
import NumPad from '../src/components/NumPad.vue'
</script>


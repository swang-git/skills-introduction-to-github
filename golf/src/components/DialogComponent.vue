<template>
<q-dialog v-model="opened">
  <q-card class="bg-cyan-2 q-pa-xs" style="max-width:440px">
    <q-card-section class="text-h6 text-center">
      {{ title }}
    </q-card-section>
    <q-card-section class="text-h6 text-bold text-center">
      {{ subs }}
    </q-card-section>
    <q-card-section>
      <ul v-for="msg in message" :key="msg">
        <li class="text-body1">{{ msg }}</li>
      </ul> 
    </q-card-section>
    <q-card-actions align="between">
      <q-btn label="Cancel" color="secondary" @click="closeOnCancel()" />
      <q-btn label="Ok" color="primary" v-close-popup />
    </q-card-actions>
  </q-card>
</q-dialog>
</template>
<script>
import emitter from 'tiny-emitter/instance'
export default {
  props: {
    title: { type: String },
    subs: { type: String },
    message: { type: Array },
  },
  data () {
    return  {
      opened: false,
    }
  },
  methods: {
    closeOnCancel () {
      emitter.emit('dismiss-on', 'Cancel')
      this.opened = false
    }
  }
}
</script>
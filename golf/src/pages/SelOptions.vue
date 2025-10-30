<template>
  <div class="q-pa-md q-gutter-sm">
    <q-dialog v-model="opened" persistent transition-show="rotate" transition-hide="flip-up">
      <q-card>
        <q-card-section class="row text-h6">
            <q-icon :name="iconName" color="green-8" left size="30px" /> <div style="margin:auto"> {{ label }} </div>
        </q-card-section>

        <q-separator />

        <q-card-section style="max-height:40vh" class="scroll">
          <div class="scroll" v-for="o in cspOptions" :key=o.value >
            <q-radio v-model="cspId" :val="o.value" :label="o.label" @input="selCSP(o)" />
          </div>
        </q-card-section>

        <q-separator />

        <q-card-actions align="right">
          <q-btn flat label="close" color="red" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script>
export default {
  name: 'SelOptions',
  data () {
    return {
      cspId: 0,
      cspOptions: [],
      label: null,
      iconName: null,
      opened: false
    }
  },
  created () {
    // this.cspId = 0
    console.info('SelOptions created', this.cspId)
  },
  methods: {
    selCSP (opt) {
      console.info('user selected option:', this.cspId, opt)
      this.$emit('selected-option', this.label, opt)
      this.opened = false
    },
    openIt (iconName, label, opts) {
      console.info('opening selOptions dialog', label, opts)
      this.cspOptions = opts
      this.label = label
      this.iconName = iconName
      this.cspId = 0
      this.opened = true
    }
  }
}
</script>

<style>
</style>

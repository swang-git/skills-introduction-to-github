<template>
  <q-dialog v-model="opened" persistent transition-show="rotate" transition-hide="rotate">
    <q-card style="min-height:238px">
      <q-card-section>
        <table style="y-overflow:auto;margin:auto" class="bg-teal">
          <tr><td v-for="i in ['5', '10', '15']"  :key=i.x><q-btn color="teal-10" class="num-pad" @click="setNumber(i)">{{i}}</q-btn></td></tr>
          <tr><td v-for="i in ['20', '25', '30']" :key=i.x><q-btn color="teal-10" class="num-pad" @click="setNumber(i)">{{i}}</q-btn></td></tr>
          <tr><td v-for="i in ['40', '60', '90']" :key=i.x><q-btn color="teal-10" class="num-pad" @click="setNumber(i)">{{i}}</q-btn></td>
          </tr>
        </table>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script>
export default {
  data () {
    return {
      opened: false
    }
  },
  created () {
    console.log('numberPad created()')
  },
  methods: {
    setNumber (np) {
      console.info('setNumber', np)
      this.$emit('set-duration-days', np)
      this.opened = false
    },
    openIt () {
      console.log('open DurationPad')
      this.opened = true
    }
  }
}
</script>
<style>
.num-pad {
  width: 60px;
  height: 60px;
  font-size: 25px;
  background: red;
}
.num-pad-score {
  width: 60px;
  height: 60px;
  font-size: 25px;
  background: red;
}
</style>

<template>
  <div>
    <q-dialog v-model="opened" minimized :style="{ position:'absolute', top: topPos }">
      <div class="bg-teal">
        <q-toolbar class="bg-teal-9">
          <div style="margin-left:-10px"><q-btn color="amber-10" glossy size="lg" dense icon="chevron_left" round v-close-popup /></div>
          <q-toolbar-title class="text-h6 text-white">Number of Groups</q-toolbar-title>
        </q-toolbar>
        <table style="y-overflow:auto;margin:auto">
          <tr><td v-for="i in compPadNum.slice(0,  4)" :key=i.x><q-btn size="lg" :color="getColor(i)" round @click="setNumGroups(i)">{{i}}</q-btn></td></tr>
          <tr><td v-for="i in compPadNum.slice(4,  8)" :key=i.x><q-btn size="lg" :color="getColor(i)" round @click="setNumGroups(i)">{{i}}</q-btn></td></tr>
          <tr><td v-for="i in compPadNum.slice(8, 12)" :key=i.x><q-btn size="lg" :color="getColor(i)" round @click="setNumGroups(i)">{{i}}</q-btn></td></tr>
          <tr><td v-for="i in compPadNum.slice(12,16)" :key=i.x><q-btn size="lg" :color="getColor(i)" round @click="setNumGroups(i)">{{i}}</q-btn></td></tr>
        </table>
      </div>
    </q-dialog>
  </div>
</template>
<script>
import libs from '../mixins/libs'
export default {
  mixins: [libs],
  data () {
    return {
      tmntId: -1,
      padNum: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
      topPos: '10px',
      opened: false
    }
  },
  created() {
    console.info('-cr-NumPad16')
  },
  computed: {
    compPadNum () { return this.padNum }
  },
  methods: {
    setNumGroups(i) {
      // console.info('-fn-seNumtGroups', i)
      this.opened = false
      // this.$emit('set-num-groups', i)
      const args = { vm: this.$parent }
      args.inData = { tmntId: this.tmntId, numGroups: i }
      args.path = process.env.API + '/golf/setTmntNumGroups'
      args.target = 'golf.setTmntNumGroups'
      this.axiosPost(args)
    },
    openIt(tmntId) {
      this.tmntId = tmntId
      this.opened = true
    }
  }
}
</script>
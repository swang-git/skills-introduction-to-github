<template>
<q-dialog v-model="opened">
  <q-card>
    <div class="text-h6 text-center bg-teal-10 text-white q-py-xs">{{ playerName }}'s Score</div>
    <q-card-section class="bg-teal-9 q-pa-sm">
      <table style="y-overflow:auto;margin:auto" class="bg-teal-7">
        <tr><td v-for="i in [1, 2, 3, 8]"  :key=i.x><q-btn size="lg" color="teal-10" round :label="i" @click="setNumber(i)" /></td></tr>
        <tr><td v-for="i in [4, 5, 6, 9]" :key=i.x><q-btn size="lg" color="teal-10" round :label="i" @click="setNumber(i)" /></td></tr>
        <tr><td v-for="i in [7, 8, 9, 0]" :key=i.x><q-btn size="lg" color="teal-10" round :label="i" @click="setNumber(i)" /></td></tr>
        <tr>
          <td><q-btn round color="amber" size="lg" icon="chevron_left" class="text-red" v-close-popup /></td>
          <td><q-btn round color="red" size="lg" icon="delete" class="text-amber" @click="setNumber('delete')" /></td>
          <td v-for="i in [0]" :key=i.x><q-btn size="lg" color="teal-10" round :label="i" @click="setNumber(i)" /></td>
          <td><q-btn round color="teal-10" size="lg" icon="save" class="text-amber" @click="setNumber('save')"/></td>
        </tr>
      </table>
    </q-card-section>
  </q-card>
  <infoDialog ref="infoDialog" />
</q-dialog>
</template>

<script>
import libs from '../mixins/libs'
import infoDialog from '../src/components/InfoDialog'
export default {
  progName: 'TeamMatchPlayerScorePad',
  mixins: [libs],
  components: { infoDialog },
  data() {
    return {
      player: null,
      playerName: null,
      opened: false,
    }
  },
  created() {
    console.info('-cr-TeamMatchPlayerScorePad')
  },
  methods: {
    axiosBack(target, da) {
      if (target === 'golf.setPlayerScore') {
        console.info('-ab-setPlayerscore', da.ret)
      }
    },
    setNumber(i) {
      if (i === 'delete') this.player.pscore = null
      if (i === 'save' || i === 'delete') {
        if (i === 'save' && this.player.pscore < 65) {
          const tit = 'Score (' + this.player.pscore + ') Too Low ?'
          const msg = '"CLOSE" and re-enter the score'
          this.$refs.infoDialog.openIt(tit, msg)
          this.player.pscore = null
          return
        }
        const args = { vm: this }
        args.inData = this.player
        args.path = process.env.API + '/golf/setPlayerScore'
        args.target = 'golf.setPlayerScore'
        this.axiosPost(args)
        this.opened = false
        return
      }
      console.info('-fn-setNumber', i)
      if (this.player.pscore === null) this.player.pscore = ''
      this.player.pscore += '' + i
      console.info('-fn-setNumber score', i, this.player.pscore)
      this.player.pscore = parseInt(this.player.pscore)
    },
    openIt(p) {
      console.info('-fn-TeamMatchPlayerScorePad.openIt(player)', p)
      this.player = p
      this.playerName = p.name
      this.player.pscore = ''
      this.opened = true
    }
  }
}
</script>
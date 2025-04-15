<template>
  <q-dialog ref="dialog" @hide="onDialogHide">
    <div class="q-pa-xs q-gutter- bg-yellow">
      <q-card class="bg-cyan-8">
        <q-card-section class="bg-primary text-white">
          <div v-if="player.captain==1" class="text-h6">Reset <span class="text-amber text-bold">{{ player.fullname }}</span> to (Group {{ player.grp }} Captain)</div>
          <div v-else class="text-h6">Reset <span class="text-amber text-bold">{{ player.fullname }}</span> to (in Group {{player.grp}} non-Captain)</div>
        </q-card-section>

        <q-separator />
        <q-card-section>
          <div v-for="grp in grpIdx" :key="grp.x" class="text-h6 text-white">
            <q-radio v-if="grp != player.grp" color="amber" :label="'Group ' + grp" v-model="newVal" :val="grp" @click="doAction('group')" />
          </div>
          <div class="text-h6 text-white">
            <q-radio v-if="player.captain==1" color="amber" label="Non-Captain" v-model="newVal" val="null" @click="doAction('captain')" />
            <q-radio v-else color="amber" label="Captain" v-model="newVal" val="1" @click="doAction('captain')" /><br />
            <q-radio color="red" label="Group None" v-model="newVal" val=0 @click="doAction('group')" />
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn color="teal-9" label="Cancel" @click="onCancelClick" />
        </q-card-actions>
      </q-card>
    </div>
  </q-dialog>
</template>

<script>
import libs from '../mixins/libs'
export default {
  mixins: [libs],
  props: {
    // ...your custom props
  },

  emits: [
    // REQUIRED
    'ok', 'hide', 'reset-grp-cpt'
  ],
  data () {
    return {
      player: null,
      grpIdx: [],
      newVal: -1,
      // tmntId: -1,
      // gameId: -1,
    }
  },
  methods: {
    doAction(grpcpt) {
      console.info('-fn-doAction reset-grp-cpt', grpcpt, this.newVal, this.player)
      this.$emit('reset-grp-cpt', grpcpt, parseInt(this.newVal), this.player)
      this.hide()
    },
    show (p, grpIdx) {
      console.info('-fn-show grpIdx, player', grpIdx, p)
      // this.tmntId = tmntId
      // this.gameId = gameId
      this.player = p
      this.grpIdx = grpIdx
      this.$refs.dialog.show()
    },

    // following method is REQUIRED
    // (don't change its name --> "hide")
    hide () {
      this.$refs.dialog.hide()
    },

    onDialogHide () {
      // required to be emitted
      // when QDialog emits "hide" event
      this.$emit('hide')
    },

    onOKClick () {
      console.info('-dl-onOKClick')
      // on OK, it is REQUIRED to
      // emit "ok" event (with optional payload)
      // before hiding the QDialog
      this.$emit('ok')
      // or with payload: this.$emit('ok', { ... })

      // then hiding dialog
      this.hide()
    },

    onCancelClick () {
      // we just need to hide the dialog
      this.hide()
    }
  }
}
</script>
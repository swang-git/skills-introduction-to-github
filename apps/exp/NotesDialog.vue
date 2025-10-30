<template>
  <div class="q-pa-sm">
    <q-dialog v-model="opened" transition-show="slide-right" persistent>
      <q-layout container class="bg-teal-10" style="height:496px">
        <q-header elevated class="bg-teal-9">
          <q-toolbar>
          <q-toolbar-title class="text-center">Add (Doc Name) Notes</q-toolbar-title>
          </q-toolbar>
        </q-header>
        <q-footer class="bg-teal-9">
          <q-toolbar>
            <q-toolbar-title>
              <div class="row">
                <div class="col-6"> <q-btn glossy @click="opened=false" style="float:left"  label="Cancel" color="yellow-9" /> </div>
                <div class="col-6"> <q-btn glossy @click="setNotes" style="float:right" label="done"   color="primary" /> </div>
              </div>
            </q-toolbar-title>
          </q-toolbar>
        </q-footer>
        <div class="q-pa-sm" style="margin-top:49px">
          <div class="col-12">
            <q-input :input-style="{ fontSize:'18px' }" standout v-model="data.link" label="Document Name" dark>
              <template v-slot:prepend>
                <q-icon name="link" color="yellow" />
              </template>
              <template v-slot:append>
                <q-icon name="close" @click="data.link=''" class="cursor-pointer" />
              </template>
            </q-input>
          </div>
          <div class="col-12 q-pt-xs">
            <q-input :input-style="{ fontSize:'18px', height:'300px' }" type="textarea" standout v-model="data.note" label="Notes" dense autogrow dark>
              <template v-slot:prepend>
                <q-icon name="note" color="cyan" />
              </template>
              <template v-slot:append>
                <q-icon name="close" @click="data.note=''" class="cursor-pointer" />
              </template>
            </q-input>
          </div>
        </div>
      </q-layout>
    </q-dialog>
  </div>
</template>
<script>
export default {
  data () {
    return {
      data: { link: null, note: null },
      opened: false
    }
  },
  created () {},
  computed: {},
  methods: {
    setNotes () {
      console.info(this.data)
      this.$emit('set-link-notes', this.data)
      this.opened = false
    },
    openIt (inOutData) {
      this.data = inOutData
      console.info(' ++ NotesDialog.openIt() called', this.data)
      this.opened = true
    }
  }
}
</script>
<style>
</style>

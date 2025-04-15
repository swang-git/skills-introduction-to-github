<template>
  <div style="padding-top:1px">
    <div v-for="g in selections" :key=g.x :class="{ 'text-grey':g.start_at < now }">
      <q-radio v-if="isIM" color="green" keep-color inverted dark checked-icon="check" v-model="theId" :val="g.id" :label="g.start_at + ' at ' + g.courseName" @click="checked(g)" />
      <q-radio v-else color="green" keep-color inverted dark checked-icon="check" v-model="theId" :val="g.id" :label="g.start_at + ' at ' + g.courseName" @click="checked(g)" />
    </div>
  </div>
</template>
<script>
import libs from '../mixins/libs'
export default {
  mixins: [libs],
  props: {
    selections: Object
  },
  data () {
    return {
      // now: this.yyyymmddHHMM(new Date()),
      theId: this.selections.id
    }
  },
  created () {
    console.log('-cr-RadioSelection')
  },
  methods: {
    checked (tmnt) {
      // this.theId = tmnt.id
      console.log('-et-selected-tmnt', tmnt)
      this.$q.localStorage.set('tournament', tmnt)
      this.$emit('user-selected', tmnt)
    }
  }
}
</script>

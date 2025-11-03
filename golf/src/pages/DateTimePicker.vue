<template>
  <div>
    <q-input filled v-model="datetime" dark class="bg-teal-10" :class="txsz" :label="label">
      <template v-slot:prepend>
        <q-icon name="access_time" size="lg" color="cyan" class="cursor-pointer">
          <!-- <q-popup-proxy transition-show="scale" transition-hide="scale"> -->
          <q-popup-proxy>
            <div class="q-gutter-xs row">
              <q-date v-model="datetime" mask="YYYY-MM-DD HH:mm" dark today-btn />
              <q-time v-model="datetime" mask="YYYY-MM-DD HH:mm" dark now-btn>
              <!-- <q-date v-model="datetime" mask="YYYY-MM-DD HH:mm" dark @update="updDatetime()" today-btn />
              <q-time v-model="datetime" mask="YYYY-MM-DD HH:mm" dark @click="updDatetime()" now-btn> -->
                <div class="row items-center justify-end">
                  <q-btn label="OK" color="green" glossy round @click="updDatetime()" v-close-popup />
                </div>
              </q-time>
            </div>
          </q-popup-proxy>
        </q-icon>
      </template>
    </q-input>
  </div>
</template>
<script>
import libs from '../mixins/libs'
import emitter from 'tiny-emitter/instance'
export default {
  mixins: [libs],
  props: {
    dateTime: { type: String },
    txsz: { type: String },
    label: { type: String }
  },
  data () {
    return {
      datetime: this.dateTime.replace('T', ' ')
    }
  },
  created () {
    console.info('vue.bits/DateTimePicker created()', this.datetime)
    emitter.on('upd-dt', (dt) => this.datetime = dt)
  },
  methods: {
    updDatetime () {
      console.info('new datetime', this.datetime, this.dateTime)
      this.$emit('upd-dt', this.datetime)
    }
  }
}
</script>

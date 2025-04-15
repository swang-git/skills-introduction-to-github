<template>
  <q-input rounded outlined class="q-pa-xs bg-teal-10" v-model="compInput" :label="label" type="textarea" dark autogrow>
    <template v-slot:prepend>
      <q-icon :name="icon" :color="iColor" size="40px" />
    </template>
  </q-input>
</template>
<script>
export default {
  props: [
    'label',
    'icon',
    'iColor',
    'obj'
  ],
  computed: {
    compObj () { return this.obj },
    compInput: {
      get () {
        if (this.label === 'english word') return this.obj.english
        if (this.label === 'chinese word') return this.obj.chinese
        if (this.label === 'Gift Card Number') return this.obj.gcardNum
        if (this.label === 'Document Link') return this.obj.link
        if (this.label === 'Type') return this.obj.tag
        if (this.label === 'Message') return this.obj.message
        if (this.label === 'Notes') return this.obj.note
        if (this.label === 'Food') return this.obj.food
        if (this.label === 'Drink') return this.obj.drink
        if (this.label === 'Fruit') return this.obj.fruit
        if (this.label === 'Glucose Check Notes') return this.obj.note
        if (this.label === 'Dictionary Notes') return this.obj.note
        return this.label
      },
      set (val) {
        // console.info('set compInput, label', this.label, val)
        if (this.label === 'Gift Card Number') {
          this.compObj.gcardNum = val
          this.compObj.gcardId = -1
        } else if (this.label === 'Document Link') {
          this.compObj.link = val
        } else if (this.label === 'Type') {
          this.compObj.tag = val
        } else if (this.label === 'Message') this.compObj.message = val
        else if (this.label === 'Notes') this.compObj.note = val
        else if (this.label === 'english word') this.compObj.english = val
        else if (this.label === 'chinese word') this.compObj.chinese = val
        else if (this.label === 'Food') this.compObj.food = val
        else if (this.label === 'Drink') this.compObj.drink = val
        else if (this.label === 'Fruit') this.compObj.fruit = val
        else if (this.label === 'Glucose Check Notes') this.compObj.note = val
        else if (this.label === 'Dictionary Notes') this.compObj.note = val
      }
    }
  },
  created () {
    console.info('-cr-TextInput', this.label, this.icon, this.obj)
  },
  methods: { }
}
</script>

<template>
  <div class="q-px-sm">
    <!-- <div :class="{ 'wid-border':isDesk, 'border-no-wid':isIM }" style="width:780px" :style="{ position:getPos() }"> -->
    <div class="wid-border" :style="{ position:getPos(), margin:isIM ? '0 0 0 0' : '8px 0 0 4px' }">
      <q-list v-for="(e, i) in palist" :key=e.x class="row-border text-h6">
        <q-expansion-item group header-style="line-height:19px;margin-left:-10px" dense @show="showRow(i,e)" @hide="hideRow(i,e)">
          <template v-slot:header>
            <tr v-if="isDesk" :style="{ color:e.color, fontWeight:e.weight }" class="q-pt-xs" :class="{ 'blink-line':e.oColor }" style="width:80850px">
              <td class="text-no-wrap">{{ e.due_date }}</td>
              <td class="text-no-wrap q-pl-sm text-no-wrap">{{ e.day }}</td>
              <td class="q-pl-sm text-no-wrap text-center">{{ e.due_in }}</td>
              <td class="q-pl-sm text-no-wrap text-right">{{ e.recursive }}</td>
              <!-- <td class="q-pl-sm re_truncate-dsk">{{ e.tag.substr(0, 42) }}</td> -->
              <td class="q-pl-sm re_truncate-dsk ellipsis">{{ e.tag }}</td>
            </tr>
            <tr v-else :style="{ color:e.color, fontWeight:e.weight }">
              <td class="q-pl-0 text-no-wrap" style="font-family:new courier">{{ e.due_date.substring(5, 20) }}</td>
              <td class="q-pl-xs text-no-wrap" style="width:35px">{{ e.due_in }}</td>
              <!-- <td class="q-pl-xs text-no-wrap" >{{ e.due_in }}</td> -->
              <td class="q-pl-xs text-left ellipsis">{{ e.tag }}</td>
            </tr>
          </template>
          <q-card style="padding-left:1px;background:rgb(28, 88, 78);color:white">
            <q-card-section>
              <div v-if="e.message=='Golf'" class="q-pl-xs text-h6" >
                <tr v-if="hasLength(e.details)">
                  <td class="text-right text-no-wrap q-pl-xs">Players:</td>
                  <td class="q-pl-xs" v-html="e.details"></td>
                </tr>
              </div>
              <div v-else-if="e.message==='Appointment'" class="q-pl-xs row">
                <div class="col-11 text-h6">
                  <tr><td class="text-right text-no-wrap q-pl-xs">Due Date:</td><td class="q-pl-xs"> {{ e.due_date }} </td></tr>
                  <tr><td class="text-right text-no-wrap q-pl-xs">Due in:</td><td class="q-pl-xs"> {{ e.due_in }} </td></tr>
                  <tr><td class="text-right text-no-wrap q-pl-xs">Where:</td><td class="q-pl-xs"> {{ e.tag }} </td></tr>
                  <tr v-if="hasLength(e.message)">  <td class="text-right text-no-wrap q-pl-xs">What:</td><td class="q-pl-xs">Purchasing in the above store</td></tr>
                  <tr v-if="hasLength(e.recursive) || e.recursive>0"><td class="text-right text-no-wrap q-pl-xs">When:</td><td class="q-pl-xs"> {{ e.recursive }}</td></tr>
                  <tr v-if="hasLength(e.details)">  <td class="text-right text-no-wrap q-pl-xs">Details:</td><td class="q-pl-xs" v-html="e.details"></td></tr>
                  <tr v-if="hasLength(e.lnk)">  <td class="text-right text-no-wrap q-pl-xs">Doc Link:</td><td class="q-pl-xs" v-html="e.lnk"></td></tr>
                </div>
                <div class="col-1"><q-btn round glossy size="md" icon="edit" color="teal" @click="showUserInput(e, i)" /></div>
              </div>
              <div v-else class="q-pl-none row">
                <div class="col-10 text-h6">
                  <tr><td class="text-right text-no-wrap q-pl-xs">Due Date:</td><td class="q-pl-xs"> {{ e.due_date }} </td></tr>
                  <tr><td class="text-right text-no-wrap q-pl-xs">Due in:</td><td class="q-pl-xs"> {{ e.due_in }} </td></tr>
                  <tr><td class="text-right text-no-wrap q-pl-xs">Type:</td><td class="q-pl-xs"> {{ e.tag }} </td></tr>
                  <tr v-if="hasLength(e.message)">  <td class="text-right text-no-wrap q-pl-xs">Message:</td><td class="q-pl-xs"> {{ e.message }} </td></tr>
                  <tr v-if="hasLength(e.recursive) || e.recursive>0"><td class="text-right text-no-wrap q-pl-xs">Recursive:</td><td class="q-pl-xs"> {{ parseInt(e.recursive) }}</td></tr>
                  <tr v-if="hasLength(e.details)">  <td class="text-right text-no-wrap q-pl-xs">Details:</td><td class="q-pl-xs" v-html="e.details"></td></tr>
                  <tr v-if="hasLength(e.lnk)">  <td class="text-right text-no-wrap q-pl-xs">Link:</td><td class="q-pl-xs" v-html="e.lnk"></td></tr>
                </div>
                <div class="col-2"><q-btn round glossy size="md" icon="edit" color="teal" @click="showUserInput(e, i)" /></div>
              </div>
            </q-card-section>
          </q-card>
        </q-expansion-item>
      </q-list>
      <user-input ref="userInput" @user-confirmed="delRow" @added-reminder="addRow" @upded-reminder="updRow" />
    </div>
  </div>
</template>

<script type="text/javascript">
import emitter from 'tiny-emitter/instance'
import libs from '../mixins/libs'
import userInput from './UserInput'
export default {
  components: {
    userInput
  },
  mixins: [libs],
  data () {
    return {
      isEdit: true,
      clickedRowIdx: 0,
      dats: [],
    }
  },
  created () {
    this.createApp('温馨提示', 'Reminder')
    emitter.emit('items-per-page', this.isIM ? 15 : 28)
  },
  methods: {
    getPos() {
      if (this.clickedRowIdx > 18) return 'relative'
      else return 'fixed'
    },
    geoFindMe () {
      const status = {}
      // const status = document.querySelector('#status');
      // const mapLink = document.querySelector('#map-link');
      // mapLink.href = '';
      // mapLink.textContent = '';
      status.textContent = ''

      function success (position) {
        const latitude = position.coords.latitude
        const longitude = position.coords.longitude
        console.info('latitude, longitude', latitude, longitude)
        status.textContent = ''
        // mapLink.href = `https://www.openstreetmap.org/#map=18/${latitude}/${longitude}`
        // mapLink.textContent = `Latitude: ${latitude} °, Longitude: ${longitude} °`
      }

      function error () {
        status.textContent = 'Unable to retrieve your location'
      }

      if (!navigator.geolocation) {
        status.textContent = 'Geolocation is not supported by your browser'
      } else {
        status.textContent = 'Locating…'
        navigator.geolocation.getCurrentPosition(success, error)
      }
    },
    getList () {
      const args = { vm: this }
      args.path = process.env.API + '/reminder/getList'
      args.target = 'getList'
      this.axiosGet(args)
    },
    axiosBack (target, da) {
      if (target === 'getList') {
        console.info('-ab-reminder getList returns:', da.lst)
        this.dats = da.lst
        this.dats.forEach((p, i) => {
          p.day = this.getDay(p.due_date)
          p.color = this.getColor(p, i)
          p.weight = this.getWeight(p)
        })
        // console.info('-dg-reminder getList returns:', this.dats)
        emitter.emit('num-items', this.dats.length)
      }
    },
    hideRow (rowIdx, row) {
      // if (Object.hasOwnProperty.call(row, 'oColor') && row.oColor) {
      if (row.oColor) {
        // console.info('=== oColor', row)
        row.color = row.oColor
        row.weight = 400
        delete row.oColor
      } else {
        row.oColor = row.color
        row.color = 'orange'
        row.weight = 500
      }
      console.info('-dg-hideRow', rowIdx, row.color, row.type)
      this.palist.splice(rowIdx, 1, row)
    },
    showRow (rowIdx, row) {
      this.clickedRowIdx = rowIdx
      this.clickedRow = row
      row.oColor = row.color
      row.color = 'orange'
      row.weight = 600
      console.info('-fn-showRow rowIdx', rowIdx, 'row', row)
      this.palist.splice(rowIdx, 1, row)
    },
    getColor (e, i) {
      // return e.dueInDays === 0 ? 'lime' : e.dueInDays === 1 ? 'orange' : e.dueInDays === 2 ? 'pink' : e.recursive === 'Golf' ? 'yellow' : i % 2 === 0 ? 'cyan' : 'white'
      return e.dueInDays === 0 ? 'lime' : e.dueInDays === 1 ? 'orange' : e.dueInDays === 2 ? 'pink' : e.tag.substr(0, 7) === 'Play at' ? 'yellow' : i % 2 === 0 ? 'cyan' : 'white'
    },
    getWeight (e) {
      return e.dueInDays === 0 ? 700 : e.dueInDays === 1 ? 600 : e.dueInDays === 2 ? 600 : 400
    },
    hasLength (str) {
      return str !== undefined && str !== null && str.length > 0 || parseInt(str) > 0
    },
    isToday (e) {
      const dueDt = new Date(e.due_date.replace('-', ','))
      const today = new Date(this.today.replace('-', ','))
      const dateDiff = parseInt((dueDt - today) / (1000 * 60 * 60 * 24))
      // console.info('dateDiff', e.due_date, this.today, dateDiff)
      return dateDiff === 0
    },
    isTmrrw (e) {
      const dueDt = new Date(e.due_date.replace('-', ','))
      const today = new Date(this.today.replace('-', ','))
      const dateDiff = parseInt((dueDt - today) / (1000 * 60 * 60 * 24))
      return dateDiff === 1
    },
    XXsetNumItemsPerPage (pageNumber) {
      this.itemsPerPage = parseInt(pageNumber) > this.dalist.length ? this.dalist.length : parseInt(pageNumber)
      console.info('setNumItemsPerPage', this.numItemsPerPage)
    },
    getRowIdx () {
      // console.info(' ++++++ getRowIdx dats', this.dats)
      const id = this.clickedRow.id
      for (var i = 0; i < this.dats.length; i++) {
        var p = this.dats[i]
        if (p.id === id) {
          return i
        }
      }
      return -1
    },
    addRow (row) {
      row.color = 'orange'
      console.info('-fn-addRow', row)
      row.day = this.getDay(row.due_date)
      this.dats.unshift(row)
    },
    updRow (row) {
      row.oColor = true
      row.color = 'yellow'
      row.day = this.getDay(row.due_date)
      // console.info(' == updRow', row)
      this.dats.splice(this.getRowIdx(), 1, row)
    },
    delRow () {
      this.$refs.userInput.opened = false
      console.info('user confirmed to delete row', this.clickedRow)
      this.dats.splice(this.getRowIdx(), 1)
    },
    showUserInput (row, rowIdx) {
      console.log(' -- showUserInput', rowIdx, row)
      // this.clickedRow = row
      this.$refs.userInput.openIt(row)
    }
  }
}
</script>

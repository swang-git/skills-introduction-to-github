<template>
  <div class="q-px-none bg-teal-9">
    <div style="margin-top:-5px">
      <q-btn-group spread class="col-12 bg-teal-10 text-white" dense>
        <q-btn label="dates" icon-right="history" @click="showSelOpt('Date')" color="primary" />
        <q-btn :class="{'text-red':!isEdit, 'text-white':isEdit}" icon="update" @click="isEdit=!isEdit" style="max-width:40px" color="amber" />
        <q-btn label="item" icon="history" @click="showSelOpt('Item')" color="secondary" />
        <q-btn label="store" icon-right="store" @click='showStoreList()' color="blue-10" />
      </q-btn-group>
      <num-pad ref="numPad" @upd-item="updItem" @restore-num="restoreNum" />
      <uni-pad ref="uniPad" @upd-item="updItem" />
      <tax-pad ref="taxPad" @upd-item="updItem" />
      <selOptPad @selected-opt="setSelectedOpt" />
      <storeList ref="storeList" />
    </div>
    <q-table style="margin-top:-18px" class="sh-sticky-header-table" :rows="palist" :columns="columns"
      card-class="bg-teal-9 text-white" rows-per-page-label="r/p:" auto-width dense :separator="separator" hide-pagination
      row-key="name" :pagination="{rowsPerPage:compRowsPerPage}" bordered :visible-columns="visibleColumns">
      <template v-slot:top="p">
        <q-select
          v-model="visibleColumns"
          multiple
          dense
          options-dense
          :display-value="$q.lang.table.columns"
          :options="columns"
          emit-value
          map-options
          option-value="name"
          options-cover
          outlined
          rounded
          dark
          popup-content-class="text-h5 bg-amber-10 text-white, text-center"
          table-colspan="1"
          label="select"
          label-color="cyan-2"
          color="green-5"
          hide-bottom-space
          item-aligned
          input-style="width:200px"
        />
        <q-btn flat :icon="p.inFullscreen ? 'fullscreen_exit' : 'fullscreen'" @click="p.toggleFullscreen"  class="on-right" />
        <div class="col-8 text-h6">{{ date }}号的购物单</div>
        <div class="col-4 text-right text-bold text-h6" align="float-right">${{ compTotal }}</div>
      </template>
      <template v-slot:body="p">
        <q-tr :props="p" v-if="isEdit">
          <q-td key="name"  :props="p" @click="delPurchasedItemDialog(p.row)" style="min-width:20px" class="col-6 ellipsis">{{ p.row.name }}</q-td>
          <q-td key="price" :props="p" @click="openNumPad('price', p.row)" class="col-3">{{ p.row.price }}</q-td>
          <q-td key="units" :props="p" @click="openNumPad('units', p.row)">{{ p.row.units }}</q-td>
          <q-td key="uni"   :props="p" @click="openUniPad('uni',   p.row)" style="text-align:center">{{ p.row.uni }}</q-td>
          <q-td key="tax"   :props="p" @click="openTaxPad('tax',   p.row)">{{ p.row.tax }}</q-td>
          <q-td key="disct" :props="p" @click="openNumPad('disct', p.row)">{{ p.row.disct }}</q-td>
          <q-td key="costs" :props="p" @click="openNumPad('costs', p.row)" class="col-3">{{ p.row.costs }}</q-td>
        </q-tr>
        <q-tr :props="p" v-else>
          <q-td v-for="col in p.cols" :key=col :class="getClass(col.name, p.row)" :style="getStyle(col.name)">{{ col.name=='price' ? parseFloat(col.value).toFixed(2) : col.value }}</q-td>
        </q-tr>
      </template>
      <template v-slot:header-cell-name="props">
        <!-- <q-th :props="props">采购{{compItemList.length}}项</q-th> -->
        <q-th :props="props">采购{{dats.length}}项</q-th>
      </template>
    </q-table>
    <div v-if="compTotal>0" class="row text-body1 text-amber">
      <div class="col-9"><q-option-group dense inline :options="storeOpt" type="checkbox" v-model="chkdStores" 
          @update:model-value="showItemsBoughtInStores()" :disable="storeOpt.length==1" /></div>
    </div>
  </div>
</template>
<script>
import { defineAsyncComponent } from 'vue'
import emitter from 'tiny-emitter/instance'
import libs from '../mixins/libs'
import methods from './Shopping4Methods'
import computed from './Shopping4Computed'
import data from './Shopping4Data'
import storeList from './StoreList'
import numPad from './PUCPad'
import uniPad from './UniPad'
import taxPad from './TaxPad'
export default {
  mixins: [libs],
  components: {
    storeList,
    numPad,
    uniPad,
    taxPad,
    selOptPad: defineAsyncComponent(() => import('../vue.bits/SelOptPad')),
  },
  methods,
  computed,
  data,
  created () {
    this.isEdit = this.date === this.isToday
    this.createApp('采购记录', 'Shopping') // <- Shopping needed for pagination
    // emitter.emit('items-per-page', this.isIM ? 8 : 20)
    emitter.emit('items-per-page', this.compRowsPerPage)
    emitter.on('search', (searchQuery) => { this.searchQuery = searchQuery })
  }
}
</script>
<style>
div.q-select__dialog {
  max-width:190px;
}
</style>
<!-- <style>
.shopping-im-table {
  /* specifying max-width so the example can
  highlight the sticky column on any browser window */
  /* this will be the loading indicator */
  /* height or max-height is important */
  height: 490px;
}

.shopping-im-table thead tr:first-child th {
  /* bg color is important for th; just specify one */
  background-color: indigo;
  text-align: right;
  color: white;
  font-size: 15px;
}

.shopping-im-table tbody tr td {
  /* bg color is important for th; just specify one */
  background-color: teal;
  color: white;
  font-size: 18px;
}

.shopping-im-table td:first-child {
  /* bg color is important for td; just specify one */
  background-color: #c1f4cd !important;
  color: black;
  text-align: left;
  width: 100px;
}

.shopping-im-table tr th {
  position: -webkit-sticky;
  position: sticky;
  /* higher than z-index for td below */
  z-index: 2;
  /* bg color is important; just specify one */
  background: #fff;
}

.shopping-im-table thead tr:last-child th {
  /* height of all previous header rows */
  top: 48px;
  /* highest z-index */
  z-index: 3;
}

.shopping-im-table thead tr:first-child th {
  top: 0;
  z-index: 1;
}

.shopping-im-table tr:first-child th:first-child {
  /* highest z-index */
  z-index: 3;
}

.shopping-im-table td:first-child {
  z-index: 1;
}

.shopping-im-table td:first-child, .shopping-im-table th:first-child {
  position: -webkit-sticky;
  position: sticky;
  left: 0;
}
</style> -->
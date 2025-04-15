<template>
  <div class="q-px-md">
    <div>
      <q-btn-group spread glossy class="col-12">
        <!-- <q-btn dense no-wrap color="teal-10" label="select purchase dates" icon-right="history" @click="getShoppingDates" /> -->
        <q-btn dense no-wrap color="teal-10" label="select purchase dates" icon-right="history" @click="openSelOptPad('Date')" />
        <q-btn :class="{'text-red':!isEdit, 'text-amber':isEdit}" icon="update" @click="isEdit=!isEdit" style="max-width:40px" />
        <!-- <q-btn dense no-wrap color="teal-10" label="select purchased itms" icon="history" @click="getAllItems" /> -->
        <q-btn dense no-wrap color="teal-10" label="select purchased itms" icon="history" @click="openSelOptPad('Item')" />
        <q-btn dense no-wrap color="teal-10" label="select store" icon-right="store" @click='$refs.storeList.openIt()' />
      </q-btn-group>
      <num-pad ref="numPad" @upd-item="updItem" @restore-num="restoreNum" />
      <uni-pad ref="uniPad" @upd-item="updItem" />
      <tax-pad ref="taxPad" @upd-item="updItem" />
      <selOptPad :opts="getSelOpt()" :title="selOptTitle" @selected-opt="setSelectedOpt" />
      <storeList ref="storeList" />
    </div>
    <div v-if="compTotal>0" class="row q-pl-xs q-pr-sm text-h6 text-amber" style="margin-top:-25px">
      <div class="col-5"><q-option-group dense inline :options="options" type="checkbox" v-model="chkdStores" /></div>
      <div class="col-3 text-right q-pr-xs">{{ date }} 采购单</div>
      <div class="col-4 text-right q-pr-xs">Total {{ compSpent }}: $ {{ compTotal }}</div>
    </div>
    <q-table class="sh-sticky-header-table" :rows="compItemList" :columns="columns"
      card-class="bg-teal-9 text-white" dense auto-width
      row-key="name" :pagination="pagination" :separator="separator">
    <template v-slot:body="p">
      <q-tr :props="p" v-if="isEdit">
        <q-td key="name"  :props="p" class="edt" @click="delPurchasedItemDialog(p.row)">{{ p.row.name }}</q-td>
        <q-td key="price" :props="p" class="edt" @click="openNumPad('price', p.row)">{{ p.row.price }}</q-td>
        <q-td key="units" :props="p" class="edt" @click="openNumPad('units', p.row)">{{ p.row.units }}</q-td>
        <q-td key="uni"   :props="p" class="edt" @click="openUniPad('uni',   p.row)">{{ p.row.uni }}</q-td>
        <q-td key="tax"   :props="p" class="edt" @click="openTaxPad('tax',   p.row)">{{ p.row.tax }}</q-td>
        <q-td key="disct" :props="p" class="edt" @click="openNumPad('disct', p.row)">{{ p.row.disct }}</q-td>
        <q-td key="costs" :props="p" class="edt" @click="openNumPad('costs', p.row)">{{ p.row.costs }}</q-td>
      </q-tr>
      <q-tr :props="p" v-else>
        <q-td key="name"  :props="p" :class="getTXTcolor(p.row)">{{ p.row.name }}</q-td>
        <q-td key="price" :props="p">{{ p.row.price }}</q-td>
        <q-td key="units" :props="p">{{ p.row.units }}</q-td>
        <q-td key="uni"   :props="p"><span class="q-mr-sm">{{ p.row.uni }}</span></q-td>
        <q-td key="tax"   :props="p">{{ p.row.tax }}</q-td>
        <q-td key="disct" :props="p">{{ p.row.disct }}</q-td>
        <q-td key="costs" :props="p">{{ p.row.costs }}</q-td>
      </q-tr>
    </template>
    <template v-slot:header-cell-name="props">
      <q-th :props="props"> 共采购 {{compItemList.length}} 种商品 </q-th>
    </template>
    </q-table>
    <q-option-group v-model="separator" inline class="text-h6 text-white" :options="[
      { label: 'Horizontal', value: 'horizontal' },
      { label: 'Vertical', value: 'vertical' },
      { label: 'Cell (Default)', value: 'cell' },
      { label: 'None', value: 'none' },
    ]"
    />
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
    'selOptPad': defineAsyncComponent(() => import('../vue.bits/SelOptPad')),
    storeList,
    numPad,
    uniPad,
    taxPad
  },
  methods,
  computed,
  data,
  created () {
    console.info('-cr-Shopping4Desk')
    // this.$options.components.selOptPad = () => { import('../vue.bits/SelOptPad') }
    this.isEdit = this.date === this.isToday
    this.createApp('采购记录', 'Shopping')
    emitter.on('search', (searchQuery) => { this.searchQuery = searchQuery })
  },
}
</script>

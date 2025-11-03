<template>
  <div :class="{ flayout:isFone, dlayout:isDesk }">
    <q-layout view="hHh Lpr fFf" container style="min-height:1500px">
      <q-header elevated class="bg-teal-10">
        <q-toolbar>
          <q-btn glossy @click="drawer = !drawer" round dense icon="img:https://cdn.quasar.dev/logo/svg/quasar-logo.svg" size="18px" />
          <q-toolbar-title>{{ compAppTitle }}</q-toolbar-title>
        </q-toolbar>
      </q-header>

      <q-drawer v-model="drawer" :mini="!drawer || miniState" @click.capture="drawerClick" :width="230" :breakpoint="500" show-if-above mini-to-overlay content-class="bg-teal-10" >
        <q-scroll-area class="fit" style="font-family:youyuan">
          <q-list padding>
            <q-item clickable v-ripple @click.native="openApp('/expense/list', 'expense', '日 常 消 费 Expense')">
              <q-item-section avatar>
                <q-icon name="monetization_on" color="yellow" size="md" />
              </q-item-section>
              <q-item-section no-wrap>
                <div class="cfont">日常消费 Expense</div>
              </q-item-section>
            </q-item>

            <q-item clickable v-ripple  @click.native="openApp('/shopping/list', 'shopping', 'Shopping List')">
              <q-item-section avatar>
                <q-icon name="add_shopping_cart" color="cyan" size="md" />
              </q-item-section>
              <q-item-section no-wrap>
                <div class="cfont">采购清单 Shopping</div>
              </q-item-section>
            </q-item>

            <q-item clickable v-ripple @click.native="openApp('/reminder/list', 'reminder', '温 馨 提 示 Reminder')">
              <q-item-section avatar>
                <q-icon name="alarm" color="pink-4" size="md" />
              </q-item-section>
              <q-item-section no-wrap>
                <div class="cfont">温馨提示 Reminder</div>
              </q-item-section>
            </q-item>

            <q-item clickable v-ripple @click.native="openApp('/memo/list', 'memo', '备 忘 录 Memo')">
              <q-item-section avatar>
                <q-icon name="help" color="blue" size="md" />
              </q-item-section>
              <q-item-section no-wrap>
                <div class="cfont">备 忘 录 Memos</div>
              </q-item-section>
            </q-item>
            <q-item clickable v-ripple  @click.native="openApp('/watcher/list', 'watcher', '每 天 看 看 Watcher')">
              <q-item-section avatar>
                <q-icon name="健" color="teal-3" size="28px" class="q-pb-sm q-pr-xs" />
              </q-item-section>
              <q-item-section no-wrap>
                <div class="cfont">每天看看 Watcher</div>
              </q-item-section>
            </q-item>
            <q-item clickable v-ripple  @click.native="openApp('/bank/list', 'bank', '银 行 月 报 Bank Statements')">
              <q-item-section avatar>
                <q-icon name="account_balance" color="yellow-9" size="md" />
              </q-item-section>
              <q-item-section no-wrap>
                <div class="cfont">银行月报 Banking</div>
              </q-item-section>
            </q-item>

            <q-item clickable v-ripple @click.native="openApp('/golf', '高尔夫 Golf')">
              <q-item-section avatar>
                <q-icon name="sports_golf" color="green" size="lg" class="q-pr-xs" />
              </q-item-section>
              <q-item-section no-wrap>
                <div class="cfont">高 尔 夫 Golfing</div>
              </q-item-section>
            </q-item>

            <q-item clickable v-ripple @click.native="openApp('/arts')">
              <q-item-section avatar>
                <q-icon name="文" color="cyan-2" size="sm" class="q-pb-sm q-pr-xs" />
              </q-item-section>
              <q-item-section no-wrap>
                <div class="cfont">网络文章 Reading</div>
              </q-item-section>
            </q-item>

            <q-item clickable v-ripple  @click.native="openApp('/spend')">
              <q-item-section avatar>
                <q-icon name="img:https://cdn.quasar.dev/logo/svg/quasar-logo.svg" class="q-pr-xs" size="28px" />
              </q-item-section>
              <q-item-section no-wrap>
                <div class="cfont">Quasar Ver {{ $q.version }}</div>
              </q-item-section>
            </q-item>
          </q-list>
        </q-scroll-area>

        <!--
          in this case, we use a button (can be anything)
          so that user can switch back
          to mini-mode
        -->
        <div class="q-mini-drawer-hide absolute" style="top: 15px; right: -17px">
          <q-btn
            dense
            round
            unelevated
            color="accent"
            icon="chevron_left"
            @click="miniState = true"
          />
        </div>
      </q-drawer>

      <q-page-container>
        <q-page class="q-py-xs">
          <router-view />
        </q-page>
      </q-page-container>
    </q-layout>
  </div>
</template>
<script>
import { is } from '../myLibs'
export default {
  data () {
    return {
      appTitle: '家 庭 应 用',
      drawer: true,
      miniState: true
    }
  },
  created () {
    if (!this.isDesk) {
      this.drawer = false
      this.miniState = true
      console.info('===ZZ===reminder.layout', this.isFone, this.isDesk, this.drawer, this.miniState)
    }
  },
  computed: {
    compAppTitle () {
      if (this.$q.localStorage.has('appName')) return this.appTitle + ' ~ ' + this.$q.localStorage.getItem('appName')
      else return this.appTitle
    },
    isDesk () { return is.desk() },
    isFone () { return is.fone() }
  },
  methods: {
    openApp (url, appName, appTitle) {
      this.$q.localStorage.set('appName', appTitle)
      window.location.href = url
    },
    drawerClick (e) {
      if (this.miniState) {
        this.miniState = false
        // notice we have registered an event with capture flag;
        // we need to stop further propagation as this click is
        // intended for switching drawer to "normal" mode only
        e.stopPropagation()
      }
    }
  }
}
</script>
<style>
div.q-item__section.column.q-item__section--nowrap.q-item__section--main.justify-center {
  font-size: 16px;
  font-family: stzhongsong;
  font-weight: 500;
}
.flayout {
  background:rgb(18,58,58)
}
.dlayout {
  background:rgb(28,68,78)
}
.hdiv:hover {
  background-color: RGBA(100, 200, 300, 0.3);
  color:yellow;
}
::-webkit-scrollbar { display: none; }
html,div.absolute-full { /* this for firefox to hide the scrollbar */
  overflow: auto;
  /* the line that rules them all */
  scrollbar-width: none;
  /* */
}
.cfont {
  font-family:youyuan;
  /* font-family:stfangsong; */
  /* font-family:stzhongsong; */
  font-size:19px;
  font-weight:700;
  margin-left:-23px;
  color:white;
}
</style>

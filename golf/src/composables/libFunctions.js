import { ref, computed } from 'vue'
import emitter from 'tiny-emitter/instance.js'
// import { useStore } from 'vuex'
import { Platform, useQuasar } from 'quasar'
import { useGolfStore } from 'src/stores/golf'
export function libFunctions() {
  // const $store = useStore()
  const store = useGolfStore()
  const $q = useQuasar()
  const searchQuery = ref('')
  const dats = ref([])

  const screen_height = $q.screen.height
  const screen_width = $q.screen.width
  // const iphone11ProMax = computed(() => { return screen_width == 414 && screen_height == 708 })
  // const iPhoneX = computed(() => { return screen_width == 375 && screen_height == 626 })

  function desk() {
    return (
      Platform.is.desktop &&
      (Platform.is.platform === 'linux' || Platform.is.platform === 'win' || Platform.is.platform === 'mac') &&
      !Platform.has.touch
    )
  }
  function iphoneX() {
    return screen_width == 375 && screen_height == 626
  }
  function iphone11ProMax() {
    return screen_width == 414 && screen_height == 708
  }
  // function Mate60ProMax () { return  screen_width == 457 && screen_height == 805 }
  function Mate60ProMax() {
    return screen_width == 457
  }
  function iphone13() {
    return screen_width == 390 && screen_height == 659
  }
  // function ScreenWidth () { return  Math.min(screen_width, 390) }
  function ScreenHeight() {
    return Math.min(screen_height, 708)
  }
  function ScreenWidth() {
    return Math.min(screen_width, 414)
  }
  // function android() {
  //   return Platform.is.android
  // }
  // function mate () { return Platform.is.android }
  function mate() {
    return Platform.userAgent.includes('HarmonyOS') && Platform.has.touch
  }
  function iPad() {
    return Platform.is.ipad
  }
  function IPhone() {
    return Platform.is.iphone
  }
  // function mobile() {
  //   return Platform.is.mobile
  // }
  // function safari() {
  //   return Platform.is.safari
  // }
  // function chromeExt() {
  //   return Platform.is.chromeExt
  // }
  // function chrome() {
  //   return Platform.is.chrome
  // }
  // function linux() {
  //   return Platform.is.linux
  // }
  // function firefox() {
  //   return Platform.is.mozilla
  // }
  // function touchable() {
  //   return Platform.has.touch
  // }
  // function edge() {
  //   return Platform.is.edge
  // }
  // function fone () { return iPhone() || mate9() }
  // function fone() {
  //   return iPhone()
  // }
  // function whatPlatform() {
  //   console.log(
  //     ' ==== platform',
  //     Platform,
  //     desk(),
  //     Platform.userAgent.indexOf('HUAWEI'),
  //     'not undefined = ',
  //     !undefined,
  //   )
  // }
  // function showUserAgent() {
  //   alert(Platform.userAgent)
  // }
  // function showPlatform() {
  //   alert(
  //     'isAndroid:' +
  //       Platform.is.android +
  //       ' isMobile:' +
  //       Platform.is.mobile +
  //       ' hasTouch:' +
  //       Platform.has.touch +
  //       ' Platform:' +
  //       Platform.is.platform,
  //   )
  // }
  // function checkiPhone() {
  //   alert('is iPhone ' + iPhone())
  // }
  // function checkMate9() {
  //   alert('is Mate9 ' + mate9())
  // }
  // function checkFone() {
  //   alert('is fone ' + fone())
  // }
  // function checkDesk() {
  //   alert('is Desk ' + desk())
  // }
  function local() {
    const localhosts = /http:\/\/(prod|devx|192.168.|localhost|127.0.0.1)/gi
    // console.log('-lb-local', window.location.href, localhosts.test(window.location.href))
    return localhosts.test(window.location.href)
  }

  // const is_sysdmin_cookie = computed(() => {
  //   // console.log(`%c-CK- isSysAdminCookie=${$q.cookies.get('sid_system_admin')}`, 'color:lime;font-size:medium')
  //   return (
  //     $q.cookies.get('sid_system_admin') === 'system_admin_' + screen_height + '_' + screen_width
  //   )
  // })

  const opened = ref(false)
  // const isDesk = computed(() => { return desk() || iPad() })
  // const isDesk = reactive(desk() || iPad())
  // const isSysAdminCookie = is_sysdmin_cookie.value
  const isLocal = local()
  const isDesk = desk() || iPad()
  const isIM = !isDesk
  const iPhoneX = iphoneX()
  const iPhone11ProMax = iphone11ProMax()
  const mate60ProMax = Mate60ProMax()
  const iPhone13 = iphone13()
  const iPhone = IPhone()
  const isMate = mate()
  const screenwidth = ScreenWidth()
  const screenheight = ScreenHeight()
  // const isFone = computed(() => { return fone() })
  // const isIM   = computed(() => { return !isDesk.value })
  // const isIM   = reactive(!isDesk)
  const dalist = computed(() => {
    var filterKey = searchQuery.value.length > 0 && searchQuery.value.toLowerCase()
    var data = dats.value
    if (filterKey.length > 0) {
      var words = filterKey.split(' ')
      words.forEach((word) => {
        data = data.filter((row) => {
          return Object.keys(row)
            .filter((key) => {
              return !['id', 'catsId', 'subcId', 'payeId', 'paymId'].includes(key)
            })
            .some((key) => {
              return String(row[key]).toLowerCase().indexOf(word) >= 0
            })
        })
      })
    }
    // console.info('-dalist-num of items', data.length)
    // emitter.emit('num-items', data.length)
    return data
  })
  // const golfUserType = computed({
  //   get: () => store.usertype,
  //   set: (val) => store.userType = val,
  //   // set: val => $store.commit('golf/setGolfUserType', val)
  // })
  // const doGroup = computed(() => {
  //   return golfUserType.value === 'doGroup'
  // })
  // const SYSAdmin = (() => { return golfUserType.value == undefined ? false : golfUserType.value === 'SysAdmin' })
  // const SysAdmin = SYSAdmin()

  const SysAdmin = computed(() => { return store.usertype === 'SysAdmin' })
  const JZsAdmin = computed(() => { return store.usertype === 'JZsAdmin' })
  const KJsAdmin = computed(() => { return store.usertype === 'KJsAdmin' })
  const ALsAdmin = computed(() => { return store.usertype === 'ALsAdmin' })
  const PGCsAdmin = computed(() => { return store.usertype === 'PGCsAdmin' })

  const pagename = computed({
    get: () => store.page,
    set: (val) => store.page = val,
  })
  const userGuidePage = computed({
    get: () => store.userGuidePage,
    set: (val) => store.userGuidePage = val,
  })
  function getAvatar(m) {
    return m.gender === 'F' ? 'icons/girl.png' : 'icons/boy.png'
  }

  function buildApp(tit, app) {
    // console.log(`-fn-buildApp() tit=${tit}, curApp=${app}`)
    emitter.on('search', (txt) => {
      searchQuery.value = txt
      console.log('search', txt)
    })
    // emitter.emit('cur-tit', tit + ' ' + app)
    emitter.emit('cur-tit', tit)
    emitter.emit('cur-app', tit + ' ' + app, 'EMIT-FROM libs')
    // emitter.emit('items-per-page', (x) => itemsPerPage = x)
    // emitter.on('items-per-page', (itpp) => { console.log('-fn-buildApp.on-itemsPerPage', itpp); itemsPerPage.value = itpp })
    // emitter.on('items-per-page', (itpp) => (itemsPerPage = itpp))
    // emitter.on('auth-getUsertype', (x) => { setUsertype(x) })
    // emitter.on('dats', (x) => { dats.value = x; emitter.emit('num-items', x.length) })
    // emitter.on('dats', (x) => setDats(x))
    // emitter.on('cur-page', (cpage) => (curPage.value = cpage))
    // getUsertype()
    // getList()
  }
  // function JZsAdmin () { return store.usertype === 'JZsAdmin' }
  // function KJsAdmin () { return store.usertype === 'KJsAdmin' }
  // function ALsAdmin () { return store.usertype === 'ALsAdmin' }
  // function PGCsAdmin () { return store.usertype === 'PGCsAdmin' }
  // function PGCsAdmin () { return store.usertype === 'PGCsAdmin' }
  return {
    userGuidePage,
    $q,
    store,
    opened,
    getAvatar,
    isLocal,
    // isSysAdminCookie,
    pagename,
    JZsAdmin,
    KJsAdmin,
    ALsAdmin,
    SysAdmin,
    PGCsAdmin,
    isDesk,
    iPhone,
    iPhoneX,
    iPhone13,
    iPhone11ProMax,
    mate60ProMax,
    screenwidth,
    screenheight,
    isIM,
    isMate,
    // golfUserType,
    searchQuery,
    dats,
    dalist,
    buildApp,
  }
}

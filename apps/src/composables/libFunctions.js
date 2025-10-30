import { ref, computed } from 'vue'
// import { useStore } from 'vuex'
// import { useRouter } from 'vue-router'
// const router = useRouter()
import { Platform, useQuasar } from 'quasar'
import emitter from 'tiny-emitter/instance'
import { useAppsStore } from 'stores/apps'
// export function libFunctions(initialSearchQuery='', initialDats=[], initialGolfUserType='') {
export function libFunctions() {
  const $q = useQuasar()
  const store = useAppsStore()
  const searchQuery = ref('')
  const dats = ref([])
  const testX = ref(0)
  const curPage = ref(1)
  const itemsPerPage = ref(null)
  const screen_height = $q.screen.height
  const screen_width = $q.screen.width

  const compTestX = computed(() => { return testX })
  //function desk () { return Platform.is.desktop && (Platform.is.platform === 'linux' || Platform.is.platform === 'win') && !Platform.has.touch }
  function desk () { return Platform.is.desktop || Platform.is.ipad }
  function android () { return Platform.is.android }
  function mate () { return Platform.is.android }
  function mate9 () { return Platform.userAgent.includes('windows') && Platform.has.touch }
  function iPad () { return Platform.is.ipad }
  function IPhone () { return Platform.is.iphone }
  function mobile () { return Platform.is.mobile }
  function safari () { return Platform.is.safari }
  function chromeExt () { return Platform.is.chromeExt }
  function chrome () { return Platform.is.chrome }
  function linux () { return Platform.is.linux }
  function firefox () { return Platform.is.mozilla }
  function touchable () { return Platform.has.touch }
  function edge () { return Platform.is.edge }
  function fone () { return IPhone() || mate9() }
  function whatPlatform () { console.log(' ==== platform', Platform, desk(), Platform.userAgent.indexOf('HUAWEI'), 'not undefined = ', !undefined) }
  function showUserAgent () { alert(Platform.userAgent) }
  function showPlatform () { alert('isAndroid:' + Platform.is.android + ' isMobile:' + Platform.is.mobile + ' hasTouch:' + Platform.has.touch + ' Platform:' + Platform.is.platform) }
  function checkiPhone () { alert('is iPhone ' + iPhone()) }
  function checkMate9 () { alert('is Mate9 ' + mate9()) }
  function checkFone () { alert('is fone ' + fone()) }
  function checkDesk () { alert('is Desk ' + desk()) }
  function local () {
    const localhosts = /http:\/\/(prod|devx|divx|192.168.|localhost|127.0.0.1)/gi
    // console.log('-lb-local', window.location.href, localhosts.test(window.location.href))
    return localhosts.test(window.location.href)
  }
  function ScreenWidth () { return $q.screen.width }
  function ScreenHeight () { return $q.screen.height }
  // function ScreenHeight () { return  Math.min(screen_height, 708) }
  // function ScreenWidth () { return  Math.min(screen_width, 414) }

  function iphone13 () { return  screen_width == 390 && screen_height == 659}

  // const isDesk = computed(() => { return desk() || iPad() })
  // const isFone = computed(() => { return fone() })
  // const isIM   = computed(() => { return !isDesk.value })
  const opened = ref(false)
  const isDesk = desk() || iPad()
  const isIM = !isDesk
  const isFone = fone()
  const iPhone = IPhone()
  const iPhone13 = iphone13()

  const firstOnPage = computed(() => { return (curPage.value - 1) * itemsPerPage.value })
  emitter.on('dats', (x) => dats.value = x)
  const palist = computed(() => {
    const plst = dalist.value.slice(firstOnPage.value, firstOnPage.value + itemsPerPage.value)
    // console.log(`-CK-comp-palist is triggered firstOnPage=${firstOnPage.value} itemsPerPage=${itemsPerPage.value}`, dalist.value)
    return plst
  })
  const dalist = computed(() => {
    var filterKey = searchQuery.value.length > 0 && searchQuery.value.toLowerCase()
    var data = dats.value
    if (filterKey.length > 0) {
      var words = filterKey.split(' ')
      words.forEach(word => {
        data = data.filter(row => {
          return Object.keys(row).filter(key => { return ![
            'id', 'catsId', 'subcId', 'payeId', 'paymId', 'note', 'link', 'post_date', 'created_at', 'updated_at', 'deleted_at'
            ].includes(key) }).some(key => {
            return String(row[key]).toLowerCase().indexOf(word) >= 0
          })
        })
      })
    }
    emitter.emit('num-items', data.length)
    return data
  })

  const doGroup = computed(() => { return golfUserType.value === 'doGroup' })
  const SysAdmin = computed(() => { return golfUserType.value === 'SysAdmin' || userType.value === 'yadmin' })
  const JZsAdmin = computed(() => { return golfUserType.value === 'JZsAdmin' })
  const PGCsAdmin = computed(() => { return golfUserType.value === 'PGCsAdmin' })
  const isAdmin = computed(() => { return userType.value === 'yadmin' })

  const AppAdmin = computed(() => { return userType.value === 'yadmin' })
  const userType = computed({
    get: () => store.userType || $q.localStorage.getItem('userType'),
    set: val => store.userType = val
  })
  const screenwidth = ScreenWidth()
  const screenheight = ScreenHeight()
  const golfUserType = computed({
    get: () => store.usertype,
    set: val => store.userType = val
  })
  function buildApp (tit, app) {
    // console.log(`-fn-buildApp() tit=${tit}, curApp=${app}`)
    emitter.on('search', (txt) => { searchQuery.value = txt; console.log('search', txt) })
    // emitter.emit('cur-tit', tit + ' ' + app)
    emitter.emit('cur-tit', tit)
    emitter.emit('cur-app', tit + ' ' + app, 'EMIT-FROM libs')
    // emitter.emit('items-per-page', this.itemsPerPage)
    // emitter.on('items-per-page', (itpp) => { console.log('-fn-buildApp.on-itemsPerPage', itpp); itemsPerPage.value = itpp })
    emitter.on('items-per-page', (itpp) => itemsPerPage.value = itpp)
    // emitter.on('auth-getUsertype', (x) => { setUsertype(x) })
    // emitter.on('dats', (x) => { dats.value = x; emitter.emit('num-items', x.length) })
    emitter.on('dats', (x) => setDats(x))
    emitter.on('cur-page', (cpage) => curPage.value = cpage)
    // getUsertype()
    // getList()
  }
  function setDats (x) {
    dats.value = x
    // console.log(`-CK-dalist.length=${dalist.value.length}`)
    // emitter.emit('num-items', dalist.value.length)
  }
  function getLineBackground (i) {
    const sty = i % 2 === 0 ? 'background: RGB(18,48,68); color: cyan' : 'background: RGB(18,68,88); color: lightcyan'
    return sty + '; height:33.2px'
  }
  function formatCurrency (n) {
    if (n === 0) return '0.00'
    else if (n === 1) return '1.00'
    const val = Number(n).toLocaleString('en-US')
    const pos = val.indexOf('.')
    if (pos < 0) return val + '.00'
    const dec = val.substring(pos+1, pos+3)
    if (dec.length < 2) return val + '0'
    return val
  }
  function fmtcy (n) {
    if (n === 0) return '0.00'
    else if (n === 1) return '1.00'
    const val = Number(n).toLocaleString('en-US')
    const pos = val.indexOf('.')
    if (pos < 0) return val + '.00'
    const dec = val.substring(pos+1, pos+3)
    if (dec.length < 2) return val + '0'
    else if (dec.length >= 2) return val.substring(0, pos+3)
    console.log(`n=${n} val=${val} dec=${dec}`)
    return val
  }
  function fmtpt (n, d) {
    if (n === 0) return '0.'.padEnd(2+d, '0')
    else if (n === 1) return '1.'.padEnd(2+d, '0')
    const val = Number(n).toLocaleString('en-US')
    const pos = val.indexOf('.')
    if (pos < 0) return (val + '.').padEnd(val.length+1+d, '0')
    const x = val.split('.')
    let dec = x[1].slice(0, d)
    if (dec.length < d) dec = dec.padEnd(d, '0')
    return x[0] + '.' + dec
    // const dec = val.substring(pos+1, pos+3)
    // if (dec.length < 2) return val + '0'
    // else if (dec.length >= 2) return val.substring(0, pos+3)
    console.log(`n=${n} val=${val} dec=${dec}`)
    return val
  }
  function deepClone (obj) {
    if (Array.isArray(obj)) {
      const arr = []
      for (var i = 0; i < obj.length; i++) {
        arr[i] = deepClone(obj[i])
      }
      return arr
    }
    if (obj === null || obj === '') {
      return null
    }
    if (typeof (obj) === 'object') {
      var cloned = {}
      for (const key in obj) {
        cloned[key] = deepClone(obj[key])
      }
      return cloned
    }
    return obj
  }
  function decimal2 (n) {
    if (n.length === 1) return '0.0' + n
    else if (n.length === 2) return '0.' + n
    else if (n.length >= 3) return (parseInt(n) / 100.00)
  }
  return {
    getLineBackground,formatCurrency,fmtcy,fmtpt,deepClone,decimal2,isAdmin,userType,
    buildApp,opened,
    store,
    $q,
    screenwidth,
    screenheight,
    iPhone,
    iPhone13,
    AppAdmin,
    isDesk,
    isIM,
    isFone,
    searchQuery,
    dats,
    dalist,
    palist,
  }
}

import { Platform } from 'quasar'
function cube (x) { return x * x * x }
const foo = Math.PI + Math.SQRT2
var is = {
  desk () { return Platform.is.desktop },
  android () { return Platform.is.android },
  mate9 () { return Platform.is.android },
  iPad () { return Platform.is.ipad },
  iPhone () { return Platform.is.iphone },
  mobile () { return Platform.is.mobile },
  fone () { return this.iPhone() || this.mate9() }
}
function openApp (url) {
  window.location.href = url
}
export { foo, cube, is }

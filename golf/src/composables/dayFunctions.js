export function dayFunctions() {
  const today = () => { return yyyymmdd(new Date()) }
  const getDay = (dat) => { // format is 2017-02-12
    if (dat === null || dat === '') return null
    const weekday = ['周日', '周一', '周二', '周三', '周四', '周五', '周六']
    const dx = dat.split('-')
    const year = parseInt(dx[0])
    const mIdx = parseInt(dx[1]) - 1
    const datx = parseInt(dx[2])
    return weekday[(new Date(year, mIdx, datx)).getDay()]
  }
  const getDay1 = (dat) => { // format is 2017-02-12
    if (dat === null || dat === '') return null
    const weekday = ['日', '一', '二', '三', '四', '五', '六']
    const dx = dat.split('-')
    const year = parseInt(dx[0])
    const mIdx = parseInt(dx[1]) - 1
    const datx = parseInt(dx[2])
    return weekday[(new Date(year, mIdx, datx)).getDay()]
  }
  const getDay2 = (dat) => {
    if (dat === null || dat === '') return null
    const weekday = ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六']
    const dx = dat.split('-')
    const year = parseInt(dx[0])
    const mIdx = parseInt(dx[1]) - 1
    const datx = parseInt(dx[2])
    return weekday[(new Date(year, mIdx, datx)).getDay()]
  }
  const yyyymmdd = (d) => {
    let dd = d.getDate()
    let mm = d.getMonth() + 1
    const yy = d.getFullYear() + ''
    dd = dd < 10 ? '0' + dd : dd + ''
    mm = mm < 10 ? '0' + mm : mm + ''
    return yy + '-' + mm + '-' + dd
  }
  function yyyymmddHHMM (d) {
    // console.info('-fn-d', d)
    return yyyymmdd(d) + ' ' + (d.getHours() < 10 ? '0' + d.getHours() : d.getHours()) + ':' + (d.getMinutes() === 0 ? '00' : (d.getMinutes() < 10 ? '0' + d.getMinutes() : d.getMinutes()))
  }
  function getDateGap (d1, d2) {
    let gap = (new Date(d2) - new Date(d1))/(24*60*60*1000)
    // console.log('-fn- d2 - d1', d1, d2, gap)
    return gap
  }
  function gameExpired (d) {
    const now = new Date()
    const day = new Date(d)
    return day.getTime() < now.getTime()
  }
  function todayGame (d) {
    const now = new Date()
    const dat = new Date(d)
    return dat.getDate() === now.getDate() && dat.getMonth() === now.getMonth() && dat.getFullYear() === now.getFullYear()
  }
  function getNextSunday () {
    const dt = new Date() // now
    dt.setDate(dt.getDate() + (7 - dt.getDay()) % 7) // next next sunday
    const nSundayNoon = new Date(dt.getFullYear(), dt.getMonth(), dt.getDate(), 12, 30, 0)
    console.log('next sunday noon', nSundayNoon)
    return nSundayNoon
  }
  function getNNextSunday () {
    const today = new Date()
    today.setDate(today.getDate() + (7 - today.getDay()) % 7 + 7) // next next sunday
    return today
  }
  String.prototype.yyyymm = function() {
    return this.substring(0, 7)
  }
  String.prototype.yyyymmdd = function() {
    console.log(`this=${this}`)
    return this.substring(0, 10)
  }
  String.prototype.chwk3 = function() {
    if (this === null || this === '') return null
    const weekday = ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六']
    const dx = this.split('-')
    const year = parseInt(dx[0])
    const mIdx = parseInt(dx[1]) - 1
    const datx = parseInt(dx[2])
    return weekday[(new Date(year, mIdx, datx)).getDay()]
  }
  String.prototype.chwk2 = function() {
    if (this == null || this == '') return null
    const weekday = ['周日', '周一', '周二', '周三', '周四', '周五', '周六']
    const dx = this.split('-')
    const year = parseInt(dx[0])
    const mIdx = parseInt(dx[1]) - 1
    const datx = parseInt(dx[2])
    return weekday[(new Date(year, mIdx, datx)).getDay()]
  }
  String.prototype.chwk1 = function() {
    if (this == null || this == '') return null
    const weekday = ['日', '一', '二', '三', '四', '五', '六']
    const dx = this.split('-')
    const year = parseInt(dx[0])
    const mIdx = parseInt(dx[1]) - 1
    const datx = parseInt(dx[2])
    return weekday[(new Date(year, mIdx, datx)).getDay()]
  }
  String.prototype.isWeekend = function() {
    if (this == null || this == '') return null
    // const weekday = ['日', '一', '二', '三', '四', '五', '六']
    let wkd = this.chwk1()
    return wkd == '日' || wkd == '六'
  }
  String.prototype.yyyymmddHHMMSS = function() {
    return this.yyyymmdd() + ' ' + this.getHours() + ':' + (this.getMinutes() === 0 ? '00' : (this.getMinutes() < 10 ? '0' + this.getMinutes() : this.getMinutes() + ':' + this.getSeconds()))
  }
  Date.prototype.yyyymmddHHMMSS = function() {
    return this.yyyymmdd() + ' ' + this.getHours() + ':' + (this.getMinutes() === 0 ? '00' : (this.getMinutes() < 10 ? '0' + this.getMinutes() : this.getMinutes() + ':' + this.getSeconds()))
  }
  Date.prototype.yyyymmdd = function() {
    let mx = this.getMonth() + 1
    mx = mx >= 10 ? mx : '0' + mx
    var yyyymmdd = this.getFullYear() + '-' + mx + '-' + this.getDate()
    console.log(`-pt-yyyymmdd=${yyyymmdd}`)
    return yyyymmdd
  }
  return {
    gameExpired,todayGame,getNextSunday,getNNextSunday,
    today,
    getDay,
    getDay1,
    getDay2,
    yyyymmdd,
    yyyymmddHHMM,
    getDateGap,
  }
}
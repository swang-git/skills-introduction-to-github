export function dayFunctions() {
  String.prototype.year = function () {
    return this.substring(0, 4)
  }
  String.prototype.yyyymm = function() {
    if (/\d{4}-\d\d/.test(this)) {
      return this.substring(0, 7)
    }
  }
  String.prototype.yyyymmdd = function() {
    if (/\d\d\/\d\d\/\d{4}/.test(this)) {
      let x = this.split('/')
      let ndate = x[2] + '-' + x[0] + '-' + x[1]
      return ndate
    } else if (/\d{4}-\d\d-\d\d/.test(this)) {
      let ndate = this.substring(0, 10)
      // console.log(`-fn-yyyymmdd this=${this} ndate=${ndate}`)
      return ndate
    }
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
  String.prototype.yyyymmddHHMM = function() {
    const d = new Date(this)
    return this.yyyymmdd() + ' ' + d.getHours() + ':' + (d.getMinutes() === 0 ? '00' : (d.getMinutes() < 10 ? '0' + d.getMinutes() : d.getMinutes()))
  }
  String.prototype.yyyymmddHHMMSS = function() {
    const d = new Date(this)
    return this.yyyymmdd() + ' ' + d.getHours() + ':' + (d.getMinutes() === 0 ? '00' : (d.getMinutes() < 10 ? '0' + d.getMinutes() : d.getMinutes() + ':' + d.getSeconds()))
  }
  Date.prototype.yyyymmddHHMMSS = function() {
    return this.yyyymmdd() + ' ' + this.getHours() + ':' + (this.getMinutes() === 0 ? '00' : (this.getMinutes() < 10 ? '0' + this.getMinutes() : this.getMinutes() + ':' + this.getSeconds()))
  }
  String.prototype.addDays = function(days){
    let d = new Date(this + 'T00:00') // local time
    let ndate = d.setDate(d.getDate() + days)
    let newDate = new Date(ndate).yyyymmdd()
    // console.log(`-CK-addDays days=${days} in=${this} newDate=${newDate}`, d)
    return newDate
  }
  String.prototype.addMonthsKeepDay = function(monthsToAdd) {
    // Parse the original date
    const date = new Date(this);
    
    // Store the original day
    const originalDay = date.getDate();
    
    // Add months (this might change the day if the new month has fewer days)
    date.setMonth(date.getMonth() + monthsToAdd);
    
    // Get the actual days in the new month
    const daysInNewMonth = new Date(
      date.getFullYear(),
      date.getMonth() + 1,
      0
    ).getDate();
    
    // Adjust if the original day exceeds days in new month
    const newDay = Math.min(originalDay, daysInNewMonth);
    
    // Set the day (either original day or max day of new month)
    date.setDate(newDay);
    
    // Format back to YYYY-MM-DD
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    
    return `${year}-${month}-${day}`;
    // // Example usage:
    // console.log(addMonthsKeepDay('2023-01-31', 1));  // "2023-02-28" (February doesn't have 31 days)
    // console.log(addMonthsKeepDay('2023-05-15', 3));  // "2023-08-15" (day unchanged)
    // console.log(addMonthsKeepDay('2023-11-30', 4));  // "2024-03-30" (day unchanged)
    // console.log(addMonthsKeepDay('2023-01-30', 1));  // "2023-02-28" (February 2023 has 28 days)
  }
  

  const today = () => { return (new Date()).yyyymmdd() }
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
  Date.prototype.yyyymmdd = function() {
    let dd = this.getDate()
    let mm = this.getMonth() + 1
    const yy = this.getFullYear() + ''
    dd = dd < 10 ? '0' + dd : dd + ''
    mm = mm < 10 ? '0' + mm : mm + ''
    return yy + '-' + mm + '-' + dd
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
  function yyyymmddHHMMSS (d) {
    return yyyymmdd(d) + ' ' + d.getHours() + ':' + (d.getMinutes() === 0 ? '00' : (d.getMinutes() < 10 ? '0' + d.getMinutes() : d.getMinutes() + ':' + d.getSeconds()))
  }
  function getDateGap (d1, d2) {
    let gap = (new Date(d2) - new Date(d1))/(24*60*60*1000)
    // console.log('-fn- d2 - d1', d1, d2, gap)
    return gap
  }
  function getNextPrevMonth(pn, mnth, year, lowYear, theYear) {
    mnth += pn
    if (mnth > 12) {
      mnth = 1
      if (++year > theYear) year = lowYear
    } else if (mnth < 1) {
      mnth = 12
      if (--year < lowYear) year = theYear
    }
    return [year, mnth]
  }
  function getFutureDate (days) {
    const dt = new Date() // now
    dt.setDate(dt.getDate() + days)
    // const futureDate = yyyymmdd(new Date(dt.getFullYear(), dt.getMonth(), dt.getDate(), 12, 30, 0))
    const futureDate = (new Date(dt.getFullYear(), dt.getMonth(), dt.getDate(), 12, 30, 0)).yyyymmdd()
    // console.log('future date(now + ' + days + ') ', futureDate)
    return futureDate
  }
  function between (value, num1, num2) {
    return value > num1 && value < num2
  }
  return {
    today,getNextPrevMonth,getFutureDate,between,
    getDay,
    getDay1,
    getDay2,
    yyyymmdd,
    yyyymmddHHMM,yyyymmddHHMMSS,
    getDateGap,
  }
}

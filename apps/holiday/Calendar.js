export class CONST {
  static JAN = 0
  static get FEB () { return 1 }
  static get MAR () { return 2 }
  static get APR () { return 3 }
  static get MAY () { return 4 }
  static get JUN () { return 5 }
  static get JUL () { return 6 }
  static get AUG () { return 7 }
  static get SEP () { return 8 }
  static get OCT () { return 9 }
  static get NOV () { return 10 }
  static get DEC () { return 11 }

  static get SUN () { return 0 }
  static get MON () { return 1 }
  static get TUE () { return 2 }
  static get WED () { return 3 }
  static get THU () { return 4 }
  static get FRI () { return 5 }
  static get SAT () { return 6 }

  static get FIRST () { return 1 }
  static get SECOND () { return 2 }
  static get THIRD () { return 3 }
  static get FOURTH () { return 4 }
  static get LAST () { return 5 }

  static get SHIFT_HOLIDAYS () {
    return {
      'Martin Luther King, Jr. Day': [this.THIRD, this.MON, this.JAN, 'The 3rd Monday of January'],
      "Presidents' Day": [this.THIRD, this.MON, this.FEB, 'The 3rd Monday of February'],
      'Memorial Day': [this.LAST, this.MON, this.MAY, 'The Last Monday of May'],
      'Labor Day': [this.FIRST, this.MON, this.SEP, 'The 1st Monday in September'],
      'Columbus Day': [this.SECOND, this.MON, this.OCT, 'The 2nd Monday in October'],
      'Thanksgiving Day': [this.FOURTH, this.THU, this.NOV, 'The 4th Thursday of November']
    }
  }

  static get FIXED_DATE_HOLIDSYS () {
    return {
      "New Year's Day": [this.JAN, 1, "January 1, New Year's Day"],
      "Washington's Birthday": [this.FEB, 17],
      'Independence Day': [this.JUL, 4],
      'Veterans Day': [this.NOV, 11],
      Christmas: [this.DEC, 25]
    }
  }
}
class Calendar {
  constructor (date) {
    if (date === undefined) this.theDate = new Date()
    this.theDate = date
    this.day = date.getDay()
    this.date = date.getDate()
    this.month = date.getMonth()
    this.year = date.getFullYear()
    this.weekOccurrence = Math.ceil(this.date / 7)
  }

  isTheLastDayOccurrence (wkocc) {
    const da = new Date(this.theDate)
    da.setDate(this.date + 7)
    return this.month !== da.getMonth() ? wkocc === CONST.LAST : false
  }

  isWeekend () {
    if (this.date === undefined) this.date = new Date()
    return this.day === CONST.SAT || this.day === CONST.SUN
  }

  getHoliday () { return this.isHoliday('name') }

  isHoliday (flag=null) {
    const ho = CONST.SHIFT_HOLIDAYS
    const ret = Object.keys(ho).filter(key => {
      return (this.month === ho[key][2] && this.day === ho[key][1] && this.isTheLastDayOccurrence(ho[key][0])) ||
             (this.month === ho[key][2] && this.day === ho[key][1] && ho[key][0] === this.weekOccurrence)
    })
    const hx = CONST.FIXED_DATE_HOLIDSYS
    const rex = Object.keys(hx).filter(key => {
      return this.month === hx[key][0] && this.date === hx[key][1]
    })
    // const results = ret.concat(rex)
    const results = rex
    // console.info('---results', results)
    if (flag === 'name') return results.length > 0 ? results[0] : null
    // return typeof(results)
    return results.length > 0
  }

  isTheLastDayOcc (date, wkocc) {
    const dn = new Date(date)
    dn.setDate(date.getDate() + 7)
    return date.getMonth() !== dn.getMonth() ? wkocc === CONST.LAST : false
  }

  yyyymmdd (har) {
    const occ = har[0]
    const day = har[1]
    const monthIdx = har[2]
    for (var i = 1; i <= 31; i++) {
      var da = new Date(this.year, monthIdx, i)
      if (da.getDay() === day && da.getMonth() === monthIdx &&
        (this.isTheLastDayOcc(da, occ) || occ === Math.ceil(da.getDate() / 7))) {
        // return da.toDateString()
        return da
      }
    }
  }

  getHolidays () {
    const retho = []
    const ho = CONST.SHIFT_HOLIDAYS
    Object.keys(ho).forEach(key => {
      const val = ho[key]
      const nxt = val[3]
      const dat = this.yyyymmdd(val)
      // console.info('=== date', dat.toDateString(), key)
      // retho.push([dat.toDateString(), key + ' (' + nxt + ')'])
      // retho.push([dat, key + ' (' + nxt + ')'])
      retho.push([dat, key, nxt])
    })
    const hx = CONST.FIXED_DATE_HOLIDSYS
    Object.keys(hx).forEach(key => {
      // retho.push([new Date(this.year, hx[key][0], hx[key][1]).toDateString(), key])
      retho.push([new Date(this.year, hx[key][0], hx[key][1]), key, 'This is the fixed holiday'])
    })
    const x = retho.sort((a, b) => { return a[0] < b[0] ? -1 : 1 })
    const holidays = []
    x.forEach(p => { holidays.push([p[0], p[1], p[2]]) })
    // console.info(holidays)
    return holidays
  }
}
export { Calendar }

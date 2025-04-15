import { libFunctions } from "./libFunctions.js"
// import { storeFunctions } from "./storeFunctions.js"
// import { getCurrentInstance } from "vue"
export function cssFunctions() {
  // const app = getCurrentInstance()
  // const store = app.appContext.config.globalProperties.$store
  const { store } = libFunctions()
  const shadow = (classname) => { return classname + ' inset-shadow-down' }
  const getAvatar = (m) => { return m.gender === 'F' ? 'icons/girl.png' : 'icons/boy.png' }
  const teamColor = (p) => { return /A\d/.test(p.team) ? 'bg-blue-9' : 'bg-red-9' }
  const getNature = (gender) => { return gender === 'M' ? 'nature_people' : 'nature' }
  function getAvgColor(avg) {
    const cls = 'bg-teal-10 ' + getGSColor(avg)
    // let cls = 'text-h6 bg-teal-10 ' + getGSColor(avg)
    // if (iPhoneX) cls = 'text-body1 bg-teal-10 ' + getGSColor(avg)
    return cls
  }
  const getGSColor = (score) => {
    let cls = 'text-green-3' // < 80
    if (score < 80) cls = 'text-pink-2'
    else if (score >= 80 && score < 85) cls = 'text-cyan-2'
    else if (score >= 85 && score < 95) cls = 'text-yellow'
    else if (score >= 95 && score < 100) cls = 'text-amber-10'
    else if (score >= 100) cls = 'text-red'
    return cls
  }
  const getTeeColor = (tee) => {
    if (tee === 'Player') tee = 'bg-purple-2'
    else if (tee === 'Blue') tee = 'bg-blue-10 text-white'
    else if (tee === 'Green') tee = 'bg-green-8 text-white'
    else if (tee === 'Gold') tee = 'bg-yellow-10'
    else if (tee === 'White') tee = 'bg-white'
    else if (tee === 'Black') tee = 'bg-grey-10 text-cyan-3'
    else if (tee === 'Red') tee = 'bg-red'
    else if (tee === 'Combo') tee = 'bg-purple-10 text-white'
    return tee
  }
  const condShadow = (val, cls1, cls2, cls3) => {
    let xal = val
    if (Math.abs(xal) < 0.0001) xal = 0
    const r1 = 'inset-shadow-down '
    const r2 = xal>0 ? cls1 : xal<0 ? cls2 : cls3
    return r1 + r2
  }
  const zhcharRegExp = new RegExp('[\u3040-\u30ff\u3400-\u4dbf\u4e00-\u9fff\uf900-\ufaff\uff66-\uff9f]')
  function getStrokePadClass (scoreName) {
    // console.log(`-fn-getStrokePadClass-${scoreName}`)
    if (scoreName === 0) return '_zero'
    else if (/bird|bogy/.test(scoreName) || scoreName === 'par')  return '__' + scoreName
    else return '_' + scoreName
  }
  function getFBClass (fb9) { 
    let ret = fb9 > 39 || fb9 == 0 ? 'shadow-box-56-grey' : 'shadow-box-56-red'
    return ret += ' inset-shadow-down flex flex-center'
  }
  function getGScoreClassM(score, i, holeIdx) {
    const scoreName = getScoreName(score, i, holeIdx) 
    console.log(`-fn-getGScoreClassM score=${score} scoreName=${scoreName} i=${i} holeIdx=${holeIdx}`)
    if (scoreName == 0 || scoreName == null) return '_zero'
    return '_X' + scoreName + ' q-pl-md inset-shadow-down cursor-pointer'
  }
  function getGScoreClass11(score, i, holeIdx) {
    const scoreName = getScoreName(score, i, holeIdx) 
    // console.log(`-fn-getGScoreClass11 score=${score} scoreName=${scoreName} i=${i} holeIdx=${holeIdx}`)
    if (scoreName === 0 || scoreName == null) return '_zero'
    return '_X' + scoreName + ' q-pl-sm inset-shadow-down cursor-pointer'
  }
  function getGScoreClass(score, i, holeIdx) {
    const scoreName = getScoreName(score, i, holeIdx) 
    // console.log(`-fn-getGScoreClass score=${score} scoreName=${scoreName} i=${i} holeIdx=${holeIdx}`)
    if (scoreName === 0 || scoreName == null) return '_zero'
    return '_X' + scoreName + ' q-pl-sm q-pt-xs inset-shadow-down cursor-pointer'
  }
  function getScoreClass(score, i, holeIdx) {
    const scoreName = getScoreName(score, i, holeIdx) 
    // console.log(`-fn-getScoreClass score=${score} scoreName=${scoreName} i=${i} holeIdx=${holeIdx}`)
    if (scoreName === 0 || scoreName == null) return '_zero'
    return '_' + scoreName + ' q-pl-sm q-pt-xs inset-shadow-down cursor-pointer'
  }
  function hole (i) { return store.holes['h' + i]}
  function getScoreName (score, i, holeIdx=0) {
    // console.log(`-getScoreName score=${score}, i=${i}, holeIdx=${holeIdx}`)
    // if (score === 0 || score == null) return 'zero'
    var scoreName = null
    if (score === 0 && i === holeIdx) scoreName = 'enter'
    else if (score === 0 || score == null) scoreName = 'zero'
    const par = hole(i)
    // console.log(`-fn-getScoreName score=${score}, par=${par} i=${i}, holeIdx=${holeIdx}`)
    const eagl = par - 2
    const bird = par - 1
    const bogy = par + 1
    const dobl = par + 2
    const trpl = par + 3
    const quad = par + 4
    const dpar = 2 * par
    if (score === par) scoreName = 'par'
    else if (score === bird) scoreName = 'bird'
    else if (score === eagl) scoreName = 'eagl'
    else if (score === bogy) scoreName = 'bogy'
    else if (score === dobl) scoreName = 'dobl'
    else if (score === trpl) scoreName = 'trpl'
    else if (score === quad) scoreName = 'quad'
    else if (score === dpar) scoreName = 'dpar'
    return scoreName
  }
  return {
    shadow,getGScoreClass,getScoreName,getScoreClass,getGScoreClass11,getGScoreClassM,
    getAvatar,
    teamColor,
    condShadow,
    getTeeColor,
    getNature,
    zhcharRegExp,
    getAvgColor,
    getGSColor,getStrokePadClass,getFBClass,
  }
}
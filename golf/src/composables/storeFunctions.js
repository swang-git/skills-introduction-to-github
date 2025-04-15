import { computed } from "vue"
import { libFunctions } from "./libFunctions.js"
export function storeFunctions() {
  // const app = getCurrentInstance()
  // const store = app.appContext.config.globalProperties.$store
  const { store } = libFunctions()
  const holes = computed(() => { return store.holes })
  const hcaps = computed(() => { return store.hcaps })
  const yards = computed(() => { return store.yards })
  const yardage = computed(() => { return store.yardage })
  const slope = computed(() => { return store.slope })
  const par = computed(() => { return store.par })
  const rating = computed(() => { return store.rating })
  const hole = (i) => { return holes.value['h' + i] }
  const hcap = (i) => { return hcaps.value['p' + i] }
  const yard = (i) => { return yards.value['y' + i] }
  const getScore = (scoreName, holeIdx) => {
    const par = hole(holeIdx)
    // console.log(`-fn-getScore scoreName=${scoreName}, hole=${holeIdx} par=${par}`, holes())
    let score = -1
    if (scoreName === 'par') score = par
    else if (scoreName === 'bird') score = par - 1
    else if (scoreName === 'eagl') score = par - 2
    else if (scoreName === 'bogy') score = par + 1
    else if (scoreName === 'dobl') score = par + 2
    else if (scoreName === 'trpl') score = par + 3
    else if (scoreName === 'quad') score = par + 4
    else if (scoreName === 'dpar') score = 2 * par
    else if (scoreName === 0) score = 0
    return score
  }
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
  function calcHL(ABscores, par) {
    // console.log(`-ck-calcHL, teamscore=`, ABscores)
    if (ABscores == null) return null
    else if (ABscores.every((p, i, ar) => p === ar[0])) return 0
    const birdie = par - 1
    const eagle = par - 2
    const Ascores = ABscores.slice(0, 2)
    const Bscores = ABscores.slice(2, 4)
    const [A0, A1] = Ascores.sort((a, b) => a < b ? -1 : 1)
    const [B0, B1] = Bscores.sort((a, b) => a < b ? -1 : 1)
    if (A0 === B0 && A1 === B1) return 0
    var teamscore = 0
    teamscore  = A0 < B0 ? 1 : A0 === B0 ? 0 : -1
    teamscore += A1 < B1 ? 1 : A1 === B1 ? 0 : -1
    teamscore += A1 < B0 ? 1 : B1 < A0 ? -1 : 0
    // console.log(`-CK-A-calcHL- check birdie teamscore=${teamscore}`, ABscores.every(p => p === eagle))
    
    if (A0 === eagle)  teamscore += 5
    if (A1 === eagle)  teamscore += 5
    if (B0 === eagle)  teamscore -= 5
    if (B1 === eagle)  teamscore -= 5
    // if (A0 === birdie && B0 === birdie) teamscore += 0
    if (A0 === birdie && B0 > birdie) teamscore += 1
    else if (B0 === birdie && A0 > birdie) teamscore += -1
    // if (A1 === birdie && B1 === birdie) teamscore += 0
    if (A1 === birdie && B1 > birdie) teamscore += 1
    else if (B1 === birdie && A1 > birdie) teamscore += -1

    // if (A1 === birdie && B0 > birdie) teamscore += 1
    // if (B0 === birdie && A0 > birdie) teamscore -= 1
    // if (B1 === birdie && A0 > birdie) teamscore -= 1
    // teamscore += A0 === birdie && B0 > birdie ? 1 : A0 === birdie && B0 === birdie ? 0 : -1
    // teamscore += A1 === birdie && B1 > birdie ? 1 : A1 === birdie && B1 === birdie ? 0 : -1
    // console.log(`-CK-B-calcHL- check birdie teamscore=${teamscore}`, Ascores, Bscores)
    return teamscore
  }
  return {
    holes,hole,hcap,yard,getScore,calcHL,getScoreName,
    hcaps,
    yards,
    yardage,
    slope,
    par,
    rating,
  }
}
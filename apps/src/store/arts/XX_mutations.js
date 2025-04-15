/*
export const someMutation = (state) => {}
 */

// export const updateClickedLine = (state, line) => { state.clickedLine = line }
export const setPageType = (state, pt) => { state.pageType = pt }
export const setTxtId = (state, id) => { state.txtId = id }
export const updTopTitle = (state, tit) => { state.topTitle = tit }
export const setTopTitle = (state, tit) => { state.topTitle = tit }
export const setArt = (state, x) => { state.art = x }
export const setFlw = (state, x) => { state.flw = x }
export const setSub = (state, x) => { state.sub = x }
export const setTag = (state, x) => { state.tag = x }
export const setYmd = (state, x) => { state.ymd = x }
export const setQid = (state, x) => { state.qid = x }
export const setIdx = (state, x) => { state.idx = x }
export const setContLinks = (state, x) => { state.clickedCont.links = x }
// export const updCurrentQid = (state, qid) => { state.currentQid = qid }
export const updClickedCont = (state, cont) => { state.clickedCont = cont }
// export const updClicked = (state, qid) => { state.clickedCont.clicked = qid }
// export const updPrevDay = (state, prevDay) => { state.prevDay = prevDay }
// export const updNextDay = (state, nextDay) => { state.nextDay = nextDay }
// export const updPrevTxt = (state, prevTxt) => { state.prevTxt = prevTxt }
// export const updNextTxt = (state, nextTxt) => { state.nextTxt = nextTxt }
// export const updPageType = (state, ptype) => { state.pageType = ptype }
export const updClickedIndex = (state, idx) => { state.clickedCont.clickedIndex = idx }
// export const updReadArticle = (state, idx) => { state.readArticle = idx }
// export const updTextIndex = (state, i) => { state.clickedCont.textIndex = i }
export const addCont = (state, cont) => {
  if (state.conts === undefined) state.conts = {}
  state.conts[cont.key] = cont
  // console.info(' == conts from mutations.js', state.conts)
}
// export const addCont = (state, cont) => {
//   var key = cont.tag + cont.links[0].ymd
//   if (state.conts === undefined) state.conts = {}
//   state.conts[key] = cont
// }

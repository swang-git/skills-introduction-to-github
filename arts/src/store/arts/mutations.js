/*
export const someMutation = (state) => {}
 */

export const pageType = (state, x) => { state.pageType = x }
export const clickedCont_tag = (state, x) => { state.clickedCont.tag = x }
export const clickedCont_ymd = (state, x) => { state.clickedCont.ymd = x }
export const clickedCont_qid = (state, x) => { state.clickedCont.qid = x }
export const topTitle = (state, x) => { state.topTitle = x }
export const art_tit = (state, x) => { state.art.tit = x }
export const art_txt = (state, x) => { state.art.txt = x }
export const art = (state, x) => { state.art = x }
export const flw = (state, x) => { state.flw = x }
export const sub = (state, x) => { state.sub = x }

// export const updateClickedLine = (state, line) => { state.clickedLine = line }
export const updTopTitle = (state, tit) => { state.topTitle = tit }
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

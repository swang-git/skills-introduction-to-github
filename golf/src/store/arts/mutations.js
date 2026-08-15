/*
export const someMutation = (state) => {}
 */

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

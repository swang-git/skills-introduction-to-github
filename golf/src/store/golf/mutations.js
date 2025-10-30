/*
export const someMutation = (state) => {}
 */

// src/store/showcase/mutations.js
export const isAdmin = (state, isAdmin, userId) => {
  state.isAdmin = isAdmin
  state.userId = userId
}
export const user = (state, user) => {
  state.user = user
  // state.isAdmin = user === undefined ? false : user.usertype === 'gadmin'
  if (user !== undefined && user.usertype === 'gadmin') state.isAdmin = true
  else state.isAdmin = false
  console.log('-st-current user isAdmin:', state.isAdmin, user)
}
export const setPageTitle = (state, x) => { state.pageTitle = x }
export const setPage = (state, x) => { state.page = x }
export const setHcaps = (state, x) => { state.hcaps = x }
export const setYards = (state, x) => { state.yards = x }
export const setHoles = (state, x) => { state.holes = x }
export const setRating = (state, x) => { state.rating = x }
export const setSlope = (state, x) => { state.slope = x }
export const setYardage = (state, x) => { state.yardage = x }
export const par = (state, x) => { state.par = x }
export const setCourseId = (state, x) => { state.courseId = x }
export const setShowSelectedGame = (state, x) => { state.showSelectedGame = x }
export const setTournament = (state, x) => { state.tournament = x }
export const setUserType = (state, x) => { state.usertype = x }
export const setGameId = (state, x) => { state.gameId = x }
export const setUserGuideId = (state, x) => { state.userGuideId = x }
export const setPageInDb = (state, x) => { state.pageInDb = x }
export const setUserGuide = (state, x) => { state.userGuide = x }
export const setUserGuidePage = (state, x) => { state.userGuidePage = x }
// export const setGolfUserType = (state, x) => { state.golfUserType = x }


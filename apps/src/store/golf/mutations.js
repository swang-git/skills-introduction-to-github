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
  console.log(' == current user isAdmin:', state.isAdmin, user)
}
export const setPageTitle = (state, pt) => { state.pageTitle = pt }
export const setPage = (state, pt) => { state.page = pt }
export const setTournament = (state, x) => { state.tournament = x }
export const setShowSelectedGame = (state, x) => { state.showSelectedGame = x }
export const setHoles = (state, x) => { state.holes = x }
export const setHcaps = (state, x) => { state.hcaps = x }
export const setYardM = (state, x) => { state.yardM = x }
export const setYardF = (state, x) => { state.yardF = x }
export const setCourseId = (state, x) => { state.courseId = x }
export const setUserType = (state, x) => { state.usertype = x }

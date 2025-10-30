// export const someGetter = (state) => {}

// export const isAdmin = (state) => {
//   console.info('isAdmin() called')
//   // return this.state.userId > 8000
//   return this.state.usertype === 'gadmin'
// }

// export const isAdmin = (state) => {
//   console.info('isAdmin() called usertype', this.state.usertype)
//   // return this.state.userId > 8000
//   return this.state.logins.includes(this.state.usertype) 
// }

export const getPagename = (state) => {
  console.info(`-fn-getPagename pagename=${this.state.page}`)
  return this.state.page
}

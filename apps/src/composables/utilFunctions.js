export function utilFunctions() {
  const clog1 = (str) => { console.log(`%c${str}`, "color:red") }
  const clog2 = (str, o1) => { console.log(str, o1) }
  const clog3 = (str, o1, o2) => { console.log(str, o1, o2) }
  const clog4 = (str, o1, o2, o3) => { console.log(str, o1, o2, o3) }
  function getInit(name) { return name.split(', ')[1].charAt(0) + name.charAt(0) }
  return {
    clog1,
    clog2,
    clog3,
    clog4,
    getInit,
  }
}
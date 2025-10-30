import { getCurrentInstance } from 'vue'
import { libFunctions } from './libFunctions'
import emitter from 'tiny-emitter/instance'
// import { utilFunctions } from '../composables/utilFunctions'
export function axiosFunctions() {
  const { $q } = libFunctions()
  const app = getCurrentInstance()
  const axios = app.appContext.config.globalProperties.$axios
  // const $q = app.appContext.config.globalProperties.$q
  function gaxios(path) {
    let target = null
    const x = path.split('/')
    x.shift()
    if (x[0] === 'api') target =  x.length === 2 ? x[1] : x[1] + '-' + x[2]
    else target = x[0] + '-' + x[1]
    // if (x[0] === 'api') target = x[1] + x.length === 2 ? '-' + x[2] : null
    // else target = x[0] + x.length === 2 ? '-' + x[1] : null
    // console.log(`gaxios path=${path}, target=${target}`)
    const pathx = path.replace(/^\/api\/\w+\/(.*)/, "$1")
    console.log(`%cGATH:${pathx}`, "font-size:10px;font-weight:600;color:yellow")
    
    axios.get(path).then((response) => {
      const da = response.data
      console.log(`%cGTGT:${target}(${da.status})`, "font-size:10px;font-weight:600;color:yellow")
      if (da.status === 'FAILED') {
        notifyFunc(path, target, da.errmsg)
      } else {
        emitter.emit(target, da)
        return
      }
    }).catch(error => { notifyFunc(path, target, error) })
  }
  function paxios(path, data) {
    // console.log(`-XO-paxios path=${path}`, data)
    // console.log(`-path=${path}`)
    console.log(`%cPATH:${path}`, "font-size:14px;font-weight:600;color:lime")
    const x = path.split('/')
    console.log(`%cPATH:${path} AFTER split`, "font-size:14px;font-weight:800;color:yellow")
    x.shift()
    let target = null
    if (x[0] === 'api') target = x[1] + '-' + x[2]
    else target = x[0] + '-' + x[1]
    
    axios.post(path, data).then((response) => {
      const da = response.data
      console.log(`-XO-CK-paxios ${target} return status=${da.status}`, da)
      console.log(`%cPTGT:${target}(${da.status})`, "font-size:14px;font-weight:600;color:lime")
      emitter.emit(target, da)
      // console.log(`%cRETURN status=${da.status}`, "font-size:11px;font-weight:600;color:red")
      if (da.status === 'FAILED') notifyFunc(path, target, da.errmsg)
    }).catch(error => { notifyFunc(path, target, error) })
  }
  const notifyFunc = (path, target, error) => {
    // console.error(`FAILED Loading with path=${path} target=${target} error=${error}`)
    $q.notify({
      color: 'yellow-9',
      position: 'bottom',
      message: '<strong class="text-h5 text-black"> FAILED for ' + target + '</strong>',
      icon: 'report_problem',
      html: true
    })
    $q.dialog({
      color: 'teal-9',
      fullWidth: true,
      title: '<strong class="text-cyan text-h5">' + target + ' status:</strong>',
      // message: '<strong class="text-white text-h6">' + error + '. Please Login First</strong>',
      message: '<strong class="text-white text-h6">' + error + '</strong>',
      icon: 'info',
      ok: { label: '关闭', size: '18px' },
      style: { background: 'navy' },
      html: true
    })
  }
  return {
    gaxios,
    paxios,
  }
}

import emitter from 'tiny-emitter/instance'
import { getCurrentInstance } from 'vue'
import axios from 'axios'

// const api = axios.create({
//   baseURL: window.location.origin,
//   withCredentials: true, // Mandatory: send laravel_session cookie
//   headers: {
//     'X-Requested-With': 'XMLHttpRequest',
//   }
// });

// // Auto attach XSRF token from browser cookie
// api.interceptors.request.use((config) => {
//   const token = document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1];
//   if (token) {
//     config.headers['X-XSRF-TOKEN'] = decodeURIComponent(token);
//   }
//   return config;
// });

export function axiosFunctions() {
  const app = getCurrentInstance()
  // const axios = app.appContext.config.globalProperties.$axios
  const q = app.appContext.config.globalProperties.$q
  function gaxios(path) {
    // console.log(`=====gaxios path=${path}`)
    let target = null
    const x = path.split('/')
    x.shift()
    if (x[0] === 'api') target = x[1] + '-' + x[2]
    else target = x[0] + '-' + x[1]
    const pathx = path.replace(/^\/api\/\w+\/(.*)/, '$1')
    console.log(
      `%cGATH:${pathx}`,
      'font-size:10px;font-weight:200;color:yellow'
    )

    axios.get(path).then(response => {
        const da = response.data
        console.log(
          `%cGTGT:${target}(${da.status})`,
          'font-weight:300;color:yellow;font-size:10px'
        )
        // console.log(`-CK-fn-target=${target} axios return status=${da.status}`, da)
        if (da.status === 'FAILED') {
          notifyFunc(path, target, da.errmsg)
        } else {
          emitter.emit(target, da)
          return
        }
      })
      .catch(error => {
        notifyFunc(path, target, error)
      })
  }

  function paxios(path, data) {
    // console.log(`-fn-paxios path=${path}`, data)
    console.log(
      `%cPATH:${path}`,
      'font-size:10px;font-weight:600;color:lime;font-size:medium'
    )
    let target = null
    const x = path.split('/')
    x.shift()
    if (x[0] === 'api') target = x[1] + '-' + x[2]
    else target = x[0] + '-' + x[1]

    axios.post(path, data).then(response => {
        const da = response.data
        // console.log(`-fn-target=${target} axios return status=${da.status}`)
        console.log(
          `%cPTGT:${target}(${da.status})`,
          'font-size:11px;font-weight:600;color:lime;font-size:medium'
        )
        if (da.status !== 'OK')
          console.log(
            `%cGAXIO error:${target}(${da.error})`,
            'font-size:11px;font-weight:600;color:red'
          )
        emitter.emit(target, da)
        if (da.status === 'FAILED') notifyFunc(path, null, da.errmsg)
      })
      .catch(error => {
        notifyFunc(path, null, error)
      })
  }
  const notifyFunc = (path, target, error) => {
    console.error(path, 'Failed loading', target, 'with ', error)
    q.notify({
      color: 'yellow-9',
      position: 'bottom',
      message:
        '<strong class="text-h5 text-black"> FAILED for ' +
        target +
        '</strong>',
      icon: 'report_problem',
      html: true
    })
    q.dialog({
      color: 'teal-9',
      fullWidth: true,
      title:
        '<strong class="text-white text-h6">' + target + ' status:</strong>',
      message: '<strong class="text-white text-h6">' + error + '</strong>',
      icon: 'info',
      ok: { label: '关闭', size: '16px' },
      style: { background: 'navy', padding: '0 0px 0 0' },
      html: true
    })
  }
  return {
    gaxios,
    paxios
  }
}

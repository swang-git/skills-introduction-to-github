import emitter from 'tiny-emitter/instance'
export function infoFunctions() {
  function getA1cDefinitions () {
      let tit = '糖化血红蛋白 6.1 是否正常'
      let msg = '糖化血红蛋白(HbA1c)亦称糖化血红素6.1，通常不正常。糖化血红蛋白的正常标准是4%-6%，因此糖化血红蛋白6.1%超过了正常高限。'
      msg += '糖化血红蛋白是血红蛋白与葡萄糖进行了非酶参与的糖基化反应，结果是反映三个月当中平均血糖水平，如果糖化血红蛋白超过了正常高限，意味着患者体内存在着三个月以上血糖增高。'
      msg += '<div class="text-h5 text-amber text-center">中国使用的度量(mmol/L)</div>'
      msg += 'mmol/l stands for <b class="text-amber">millimoles per litre</b>. A mole is a scientific unit often used to measure chemicals.(below 6.5 is good)'
      msg += '<table>'
      msg += '<tr><li>A1C Below 5.7% is normal.</li></tr>'
      msg += '<tr><li>5.7% to 6.4% is diagnosed as prediabetes.</li></tr>'
      msg += '<tr><li>6.5% or higher on two separate tests indicates diabetes.</li></tr>'
      msg += '<tr class="text-center"><th class="text-cyan-4">A1C</th><th class="text-yellow">Estimated Avg Sugar Level</th></tr>'
      msg += '<tr><td class="text-cyan-4 text-center">6%</td><td class="text-yellow text-left">126 mg/dL(7.0 mmol/L)</td></tr>'
      msg += '<tr><td class="text-cyan-4 text-center">7%</td><td class="text-yellow text-left">154 mg/dL(8.6 mmol/L)</td></tr>'
      msg += '</table>'
      emitter.emit('open-InfoDisplay', tit, msg)
      // return {tit:tit, msg:msg}
  }
  return {
    getA1cDefinitions,
  }
}
const unitName = () => { 
  // let cktm = null
  // tooltipItems.forEach(item=> {
  //   cktm = item.dataset.cktm[item.parsed.x]
  // })
  return '(mmol/L)'
} 
const checkingTime = (tooltipItems) => { 
  let cktm = null
  tooltipItems.forEach(item=> {
    cktm = item.dataset.cktm[item.parsed.x]
  })
  return 'Checking Time: ' + cktm
} 
const lastDinner = (tooltipItems) => {
  let note = null
  let food = null
  let fdtm = null
  let gptm = null
  let retv = '昨 天 的 三 顿 饭：\n'
  tooltipItems.forEach(item=> {
    // console.log('item', item)
    food = item.dataset.food[item.parsed.x]
    fdtm = item.dataset.fdtm[item.parsed.x]
    gptm = item.dataset.gptm[item.parsed.x]
    note = item.dataset.note[item.parsed.x]
  })
  // if (food != null) retv = food + ' at ' + fdtm + '(' + gptm + '小时前) '
  if (note != null) retv += note.replace('<br />', '\n').replace('<br />', '\n')
  return retv
} 
const data = {
  labels: ['Mercury', 'Venus', 'Earth', 'Mars', 'Jupiter', 'Saturn', 'Uranus', 'Neptune'],
  datasets: [{ 
    label: 'Number of Moons',
    data: [61000, 72000, 23000, 29000, 67000, 62000, 27000, 14000],
    backgroundColor: [ ],
    borderColor: 'cyan',
    borderWidth: 2,
    borderRadius: 20,
  }],
}
const options = {
  // tension: 0.4,
  // responsive: true,
  scales: {
    x: {
      ticks: {
        color: 'red',
        font: { size:14, weight:500 }
      }
    },
    y: {
      // beginAtZero: false,
      max: 10,
      // min: 3,
      // ticks: { callback: (value) => { return value + 2 } }
      // ticks: { callback: (value, index, ticks) => { return value/1000 + 'K' } }
    }
  },
  plugins: {
    legend: {
      labels: {
        font: { size: 24 },
        color: 'darkblue',
      },
      title: {
        font: { size: 28 },
        color: 'red',
      },
      text: 'XXXXX'
    },
    tooltip: {
      titleFont: { size: 20 },
      bodyFont: { size: 24 },
      footerFont: { size: 30 },
      callbacks: {
        footer: lastDinner,
        // afterBody: unitName,
        title: checkingTime,
      }
    },
    datalabels: {
      // offset: 100,
      // clamp: true,
      anchor: 'center',
      align: 'top',
      color: '#FFF',
      font: { size:18, weight:600 },
      labels: {
        value: {},
        title: {
          // color: 'red'
        }
      }
    }
  }
}
export const config = {
  type: 'bar',
  // type: 'line',
  data: data,
  opts: options,
}
export default config

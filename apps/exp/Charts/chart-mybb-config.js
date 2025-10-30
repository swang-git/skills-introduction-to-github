import emitter from 'tiny-emitter/instance'
export const backgroundColor = [
  'rgba(105, 209, 102, 0.3)',
  'rgba(174, 192, 105, 0.3)',
  'rgba(255, 106, 106, 0.3)',
  'rgba(245, 192, 192, 0.3)',
  'rgba(193, 102, 255, 0.3)',
  'rgba(154, 222, 235, 0.3)',
  'rgba(195, 119, 154, 0.3)',
  'rgba(195, 129, 234, 0.3)',
  'rgba(175, 192, 192, 0.3)',
  'rgba(195, 149, 114, 0.3)',
  'rgba(185, 176, 244, 0.3)',
  'rgba(195, 169, 144, 0.3)'
]
export const borderColor = [
  'rgba(255, 159, 100, 1.0)',
  'rgba(255, 106, 107, 1.0)',
  'rgba(153, 102, 255, 1.0)',
  'rgba(255, 159, 104, 1.0)',
  'rgba(255, 100, 132, 1.0)',
  'rgba(255, 106, 106, 1.0)',
  'rgba(175, 155, 255, 1.0)',
  'rgba(104, 162, 235, 1.0)',
  'rgba(153, 102, 255, 1.0)',
  'rgba(255, 159, 104, 1.0)',
  'rgba(255, 159, 164, 1.0)',
  'rgba(255, 100, 132, 1.0)',
  'rgba(154, 162, 235, 1.0)',
  'rgba(175, 192, 192, 1.0)',
]
const data = {
  datasets: [{
    valr: [],
    backgroundColor: backgroundColor[0],
    borderColor: 'red',
    borderWidth: 0.7,
    label: '单 值 消 费',
    data: [
      { x: 20, y: 30, r: 20 }, 
      { x: 30, y: 15, r: 15 },
      { x: 30, y: 20, r: 10 },
      { x: 40, y: 10, r: 40 }
    ],
  }, {
    valr: [],
    backgroundColor: backgroundColor[2],
    borderColor: 'navy',
    borderWidth: 0.7,
    label: '双 值 消 费',
    data: [
      { x: 25, y: 32, r: 20 }, 
      { x: 35, y: 17, r: 15 },
      { x: 35, y: 22, r: 10 },
      { x: 40, y: 12, r: 40 }
    ],
  }]
}
const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
const options = {
  onClick: (event, element, chart) => {
    if (element.length === 0) return
    // console.log('click', event, element[0].element.$context.parsed, chart)
    // if (event.y > chart.height - 100) return
    if (element[0] == undefined) return
    // console.log('click', event.y, chart.height)
    console.log('-CK-mybb-config', element[0].element.$context.parsed, event.y, chart.height)
    const year = element[0].element.$context.parsed.x
    const mnth = element[0].element.$context.parsed.y
    emitter.emit('open-ChartsProxy2', 'ympi', mnth, year)
  },
  responsive: true,
  scales: {
    x: {
      ticks: {
        callback: (value, index, ticks) => { return value.toFixed(0) },
        font: { size: 16, color: 'navy', weight: 'bold', family: 'stzhongsong' }
      },
    },
    y: {
      beginAtZero: false,
      min: 0.5,
      ticks: {
        callback: (value, index, ticks) => { return monthNames[value-1] },
        font: { size: 16, weight: 'bold', family:'stzhongsong' }
      }
    }
  },
  plugins: {
    legend: {
      position: 'bottom',
      labels: {
        color: 'rgb(55, 99, 232)',
        font: { size: 16, weight: 'bold', family: 'stzhongsong'}
      },
    },
    tooltip: {
      bodyFont: { size: 20, color: '#fff', weight: 'bold', family: 'stzhongsong' },
      callbacks: {
        label: function(ctx) {
        const month = parseInt(ctx.label)
        const p = ctx.dataset.data[ctx.dataIndex]
        return p.x + '年' + p.y + '月份支出: $' + ctx.dataset.valr[ctx.dataIndex].toFixed(2)
      }
      },
    },
    datalabels: {
      anchor: function(context) {
        var value = context.dataset.data[context.dataIndex];
        return value.r <= 8 ? 'end' : 'center';
      },
      align: function(context) {
        var value = context.dataset.data[context.dataIndex];
        return value.r <= 8 ? 'end' : 'center';
      },
      color: function(context) {
        var value = context.dataset.data[context.dataIndex];
        return value.r <= 8 ? 'red' : 'navy';
      },
      font: {
        weight: 'normal'
      },
      formatter: function(value) {
        // console.log(`value.r = ${value.r}`)
        // return value.r > 600 ? Math.round(value.r/10) : Math.round(value.r);
        return Math.round(value.r);
      },
      offset: -13,
      padding: 10
    }
  },
  // Core options
  aspectRatio: 6 / 6.9,
  layout: {
    padding: 0,
  },
}

const config = {
  type: 'bubble',
  // type: 'pie',
  // type: 'line',
  data: data,
  opts: options,
}
export default config
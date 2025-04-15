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
  labels: ['Mercury', 'Venus', 'Earth', 'Mars', 'Jupiter', 'Saturn', 'Uranus', 'Neptune'],
  datasets: [{ 
    label: 'Number of Moons',
    year: [],
    data: [61000, 72000, 23000, 29000, 67000, 62000, 27000, 14000],
    vals: [],
    backgroundColor: backgroundColor,
    borderColor: borderColor,
    borderWidth: 1.5,
    borderRadius: 20,
  }],
}
const options = {
  onClick: function(event, element, chart) {
    if (element.length === 0) return
    const years = chart.data.labels
    // const years = element[0].element.$datalabels[0].$context.dataset.year
    const yidx = element[0].index
    const year = parseInt(years[yidx])
    console.log(`yidx=${yidx} year=${year}`)
    // emitter.emit('show-chart', 'cats', null, year)
    emitter.emit('open-ChartsProxy1', 'cats', year)
  },
  responsive: true,
  scales: {
    x: {
      display: true,
      ticks: {
        font: { size: 16, color: 'navy', weight: 'bold', family: 'stzhongsong' }
      },
      // title: {
      //   display: true,
      //   text: 'years',
      //   font: { size: 18, weight: 'bold', family: 'stzhongsong' },
      // }
    },
    // y: { display: false }
    y: {
      beginAtZero: false,
      ticks: {
        callback: (value, index, ticks) => { return '' },
        // callback: (value, index, ticks) => { return value/1000 + 'K' },
        // font: { size: 16, weight: 'bold', family:'stzhongsong' }
      }
    }
  },
  plugins: {
    title: {
      display: true,
      position: 'top',
      text: "年 度 消 费 一 览 表",
      color: 'red',
      font: {
        size: 30, weight:'bold', family: 'stzhongsong'
      },
    },
    legend: {
      display: false,
      labels: {
        font: { size: 24, weight: 500 },
        color: 'darkblue',
      },
    },
    tooltip: {
      titleFont: { size: 0 }, // disable title
      bodyFont: { size: 24 },
      // footerFont: { size: 18 },
      callbacks: {
        label: (ctx) => {
          // const idx = ctx.dataIndex
          const year = ctx.label
          // console.log('-CK-', ctx.dataset.vals)
          const va = ctx.dataset.vals[ctx.dataIndex]
          return year + '年 总支出: $' + va
        }
      },
    },
    datalabels: {
      align: 'end',
      anchor: 'end',
      offset: -5,
      // color: 'nevy',
      color: function(context) {
        const idx = context.dataIndex
        const da = context.dataset.data[idx]
        const va = context.dataset.vals[idx]
        return Math.abs(da - va) > 2 ? 'red' : 'darkgreen'
      },
      font: function(context) {
        var w = context.chart.width;
        return {
          size: w < 1000 ? 16 : 20,
          weight: 'normal',
        };
      },
      // font: { size: 14, weight: 'bold'},
      // labels: {
      //   name: {
      //     formatter: (value, ctx) => {
      //       const year = ctx.dataset.year[ctx.dataIndex]
      //       console.log(year, ctx)
      //     },
      //   },
      // }
    }
  }
}
export const config = {
  type: 'bar',
  // type: 'pie',
  // type: 'doughnut',
  // type: 'line',
  data: data,
  opts: options,
}
export default (config)

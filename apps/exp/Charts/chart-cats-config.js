import emitter from "tiny-emitter/instance"
// import { createTextVNode } from "vue";
// import { libFunctions } from "src/composables/libFunctions" // cause inject warnings

function shuffleArray(a) {
  "use strict"
  var i, t, j;
  for (i = a.length - 1; i > 0; i -= 1) {
      t = a[i]
      j = Math.floor(Math.random() * (i + 1))
      a[i] = a[j]
      a[j] = t
  }
  return a
}
const colors0 = [
  'rgba(255, 199, 132, 0.4)',
  'rgba(154, 162, 235, 0.4)',
  'rgba(255, 106, 100, 0.4)',
  'rgba(245, 192, 192, 0.4)',
  'rgba(193, 102, 255, 0.4)',
  'rgba(154, 222, 235, 0.4)',
  'rgba(195, 119, 154, 0.4)',
  'rgba(195, 129, 234, 0.4)',
  'rgba(175, 192, 192, 0.4)',
  'rgba(195, 149, 114, 0.4)',
  'rgba(185, 176, 244, 0.4)',
  'rgba(195, 169, 144, 0.4)'
  ]
  // 'rgba(54, 162, 235, 0.8)',
  // 'rgba(255, 106, 86, 0.8)',
  // 'rgba(75, 192, 192, 0.8)',
  // 'rgba(193, 102, 255, 0.8)',
  // 'rgba(54, 162, 235, 0.8)',
  // 'rgba(195, 119, 154, 0.8)',
  // 'rgba(195, 129, 134, 0.8)',
  // 'rgba(75, 192, 192, 0.8)',
  // 'rgba(195, 149, 114, 0.8)',
  // 'rgba(255, 206, 86, 0.8)',
  // 'rgba(195, 169, 944, 0.8)',
  // 'rgba(100, 169, 244, 0.8)',
  // 'rgba(255, 99, 132, 0.8)',
// ]
const colors1 = [
  'rgba(255, 99, 132, 0.8)',
  'rgba(54, 162, 235, 0.8)',
  'rgba(255, 106, 86, 0.8)',
  'rgba(75, 192, 192, 0.8)',
  'rgba(193, 102, 255, 0.8)',
  'rgba(54, 162, 235, 0.8)',
  'rgba(195, 119, 154, 0.8)',
  'rgba(195, 129, 134, 0.8)',
  'rgba(75, 192, 192, 0.8)',
  'rgba(195, 149, 114, 0.8)',
  'rgba(255, 206, 86, 0.8)',
  'rgba(195, 169, 944, 0.8)',
  'rgba(100, 169, 244, 0.8)',
]
export const borderColor = [
  'rgba(75, 192, 192, 1)',
  'rgba(153, 102, 255, 1)',
  'rgba(255, 159, 64, 1)',
  'rgba(255, 159, 64, 1)',
  'rgba(255, 206, 86, 1)',
  'rgba(255,99,132,1)',
  'rgba(54, 162, 235, 1)',
  'rgba(255, 206, 86, 1)',
  'rgba(75, 192, 192, 1)',
  'rgba(153, 102, 255, 1)',
  'rgba(255, 159, 64, 1)',
  'rgba(255, 159, 64, 1)',
  'rgba(255,99,132,1)',
  'rgba(54, 162, 235, 1)',
]
const data = {
  // labels: ['Mercury', 'Venus', 'Earth', 'Mars', 'Jupiter', 'Saturn', 'Uranus', 'Neptune'],
  datasets: [{ 
    cats: [],
    vals: [],
    pcts: [],
    label: 'ZZZ',
    data: [61000, 72000, 23000, 29000, 67000, 62000, 27000, 14000],
    // backgroundColor: shuffleArray(colors),
    // backgroundColor: colors0,
    // borderColor: 'navy',
    // borderWidth: 1.4,
    borderColor: borderColor,
    borderWidth: 1.5,
    borderRadius: 15,
    datalabels: {
      font: { size: 18, weight: 'bold' },
      labels: {
        cats: {
          align: 'middle',
          // anchor: 'end',
          // offset: 100,
          color: 'blue',
          font: {size: 18},
          formatter: function(value, ctx) {
            const val = ctx.dataset.vals[ctx.dataIndex]
            return val == 0 ? null : ctx.dataset.cats[ctx.dataIndex]
          },
          padding: -10,
        },
        value: {
          align: 'top',
          offset: 0,
          font: {size: 18},
          color: 'black',
          formatter: function(value, ctx) {
            // const { fmtcy } = libFunctions()
            let val = ctx.dataset.vals[ctx.dataIndex]
            // return '$' + fmtcy(val)
            return '$' + val
          },
          padding: 10,
        },
        pect: {
          align: 'bottom',
          offset: 0,
          font: {size: 18},
          color: 'red',
          formatter: function(value, ctx) {
            const val = ctx.dataset.vals[ctx.dataIndex]
            const pct = ctx.dataset.pcts[ctx.dataIndex]
            // const total = ctx.chart.data.total
            // return (val/total * 100).toFixed(2) + '%'
            return pct + '%'
            // return val == null ? ctx.dataset.data[ctx.dataIndex] : (val/total * 100).toFixed(2) + '%'
          },
          padding: 10,
        }
      }
    }
  }, {
    cats: [],
    vals: [],
    pcts: [],
    label: 'XXX',
    data: [61000, 72000, 23000, 29000, 67000, 62000, 27000, 14000],
    backgroundColor: colors1.sort((a, b) => { return 0.5 - Math.random() }),
    // borderColor: 'red',
    // borderColor: borderColor,
    // borderWidth: 0.4,
    borderColor: borderColor,
    borderWidth: 1.5,
    borderRadius: 15,

    datalabels: {
      // align: 'top',
      // anchor: 'end',
      font: { size: 18, weight: 'bold' },
      labels: {
        value: {
          align: 'top',
          offset: 0,
          font: {size: 18},
          color: 'black',
          formatter: function(value, ctx) {
            // const { fmtcy } = libFunctions()
            let val = ctx.dataset.vals[ctx.dataIndex]
            // return val == null ? ctx.dataset.data[ctx.dataIndex] : val.toFixed(2)
            return '$' + val
          },
          padding: 10
        },
        cats: {
          align: 'middle',
          offset: 20,
          color: 'blue',
          font: {size: 18},
          formatter: function(value, ctx) {
            // const { fmtcy } = libFunctions()
            const val = ctx.dataset.vals[ctx.dataIndex]
            return val == 0 ? null : ctx.dataset.cats[ctx.dataIndex]
            // return '$' + fmtcy(val)
          },
          padding: 20,
        },
        pect: {
          align: 'bottom',
          color: 'red',
          font: { size:18, weight:'bold' },
          formatter: function(value, ctx) {
            const val = ctx.dataset.vals[ctx.dataIndex]
            const pct = ctx.dataset.pcts[ctx.dataIndex]
            // console.log(`val=${val}`)
            // return val == 0 ? null : (val / ctx.chart.data.total * 100).toFixed(3) + '%'
            return pct + '%'
          },
          padding: 8,
        },
      },
      padding: 60,
      // formatter: Math.round
    }
  }]
}
const options = {
  // responsive: false,
  onClick: (event, element, chart) => {
    if (element.length === 0) return
    const idx = element[0].index
    const cats = element[0].element.$datalabels[0].$context.dataset.cats[idx]
    const year = chart.data.year
    // console.warn(`cat year=${year} cat=${cat}`, element, chart)
    console.log(`cats-chart-config year=${year} cats=${cats}`)
    emitter.emit('open-ChartsProxy2', 'suba', cats, year)
    // emitter.emit('show-exp-details', cat, year) ---
    // emitter.emit('show-cat-sub-chart', cats, year)
    // emitter.emit('open-ChartsProxy2', 'cata', cats, year)
  // onClick:(event, chart) => {
  //   const idx = chart[0].index
  //   const cat = chart[0].element.$datalabels[0].$context.dataset.cats[idx]
  //   console.warn(`-wn-idx=${idx} cat=${cat}`)
  //   emitter.emit('show-details-cat', cat)
  },
  plugins: {
    title: {
      display: true,
      position: 'top',
      text: " 消 费 分 类 一 览",
      color: 'indianred',
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
      titleFont: { size: 24 },
      bodyFont: { size: 22 },
      callbacks: {
        label: (ctx) => {
          const val = ctx.dataset.vals[ctx.dataIndex]
          const dat = ctx.dataset.data[ctx.dataIndex]
          const cat = ctx.dataset.cats[ctx.dataIndex]
          const spending = val != null ? val : dat
          // const note = val != null ? ' *' : ''
          const note = val != dat ? ' *' : ''
          return ' Spending on ' + cat + ': $ ' + spending + note
        }
      }
    },
  },
  aspectRatio: 6 / 6,
  cutout: 40,
  layout: {
    padding: 16
  },
}
export const config = {
  // type: 'polarArea',
  type: 'doughnut',
  data: data,
  opts: options,
}
export default (config)

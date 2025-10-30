import emitter from "tiny-emitter/instance";

function shuffleArray(a) {
  "use strict";
  var i, t, j;
  for (i = a.length - 1; i > 0; i -= 1) {
      t = a[i];
      j = Math.floor(Math.random() * (i + 1));
      a[i] = a[j];
      a[j] = t;
  }
  return a;
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
    data: [61000, 72000, 23000, 29000, 67000, 62000, 27000, 14000],
    backgroundColor: shuffleArray(colors0),
    backgroundColor: colors0,
    // borderColor: 'navy',
    // borderWidth: 0.4,
    borderColor: borderColor,
    borderWidth: 1.5,
    borderRadius: 15,
    datalabels: {
      // align: 'top',
      // anchor: 'center',
      font: { size: 18, weight: 'bold' },
      labels: {
        cats: {
          align: 'bottom',
          // anchor: 'end',
          // offset: 100,
          color: 'blue',
          font: {size: 18},
          formatter: function(value, ctx) {
            return ctx.dataset.cats[ctx.dataIndex]
          },
          padding: -10,
        },
        value: {
          align: 'top',
          offset: 0,
          font: {size: 18},
          color: 'black',
          formatter: function(value, ctx) {
            let val = ctx.dataset.vals[ctx.dataIndex]
            return val == null ? ctx.dataset.data[ctx.dataIndex] : val
          },
          padding: 10,
        },
        pect: {
          align: 'bottom',
          offset: 10,
          font: {size: 18},
          color: 'red',
          formatter: function(value, ctx) {
            let dat = ctx.dataset.data[ctx.dataIndex]
            let val = ctx.dataset.vals[ctx.dataIndex]
            const total = ctx.chart.data.total
            if (total == 0) return
            return val == null ? (dat/total * 100).toFixed(2) + '%' : (val/total * 100).toFixed(2) + '%'
          },
          padding: 10,
        }
      }
    }
  }]
}
const options = {
  onClick:(event, element, chart) => {
    if (element.length === 0) return
    // const idx = chart[0].index
    // const cat = chart[0].element.$datalabels[0].$context.dataset.cats[idx]
    const idx = element[0].index
    const subc = chart.data.datasets[0].cats[idx]
    // const subc = chart.data.datasets[0].subc[idx]
    const year = chart.data.ym[0]
    const mnth = chart.data.ym[1]
    console.log(`-CK-subc year=${year} mnth=${mnth} subc=${subc}`)
    // console.log(`-CK-subc year=${year} mnth=${mnth} subc=${subc} `)
    emitter.emit('open-ChartsProxy3', 'expx', null, subc, year, mnth)
  },
  // onClick:(event, element, chart) => {
  //   if (element.length === 0) return
  //   // const idx = chart[0].index
  //   // const cat = chart[0].element.$datalabels[0].$context.dataset.cats[idx]
  //   const idx = element[0].index
  //   const subc = chart.data.datasets[0].cats[idx]
  //   const cats = chart.data.cats
  //   // const subc = chart.data.subc
  //   const year = chart.data.year
  //   // const mnth = chart.data.ym[1]
  //   // console.warn(`-wn-cat=`, event, element[0], chart.data)
  //   // console.log(`-wn-ympi year=${year} month=${mnth} cat=${cat}`)
  //   // console.warn(`-wn-cat=${cat}`, element[0])
  //   // console.log(`-CK-to ChartSubA subc=${subc} cats=${cats} year=${year}`, chart.data)
  //   console.log(`-CK-to ChartSubc subc=${subc} cats=${cats} year=${year}`)
  //   // emitter.emit('show-exp-details', cats, subc, year)
  //   // emitter.emit('show-exp-paye', cats, subc, year)
  //   // emitter.emit('open-ExpDetailsPad', cats, subc, year)
  //   // emitter.emit('get-exp-data', cats, subc, year)
  //   emitter.emit('open-ChartsProxy3', 'paye', cats, subc, year)
  // },
  plugins: {
    title: {
      display: true,
      // align: 'end',
      position: 'top',
      text: " 消 费 分 类 一 览",
      color: 'indianred',
      font: {
        size: 26, weight:'bold', family: 'stzhongsong'
      },
    },
    legend: {
      display: false,
    },
    tooltip: {
      enabled: false,
      titleFont: { size: 24 },
      bodyFont: { size: 22 },
      callbacks: {
        label: (ctx) => {
          const val = ctx.dataset.vals[ctx.dataIndex]
          const dat = ctx.dataset.data[ctx.dataIndex]
          const cat = ctx.dataset.cats[ctx.dataIndex]
          const spending = val != null ? val : dat
          const note = val != null ? ' *' : ''
          return ' Spending on ' + cat + ': $ ' + spending + note
        }
      }
    },
  },
  aspectRatio: 6 / 6,
  cutout: 60,
  layout: {
    padding: 16
  },
}
export const config = {
  // type: 'polarArea',
  type: 'doughnut',
  // type: 'pie',
  data: data,
  opts: options,
}
export default (config)

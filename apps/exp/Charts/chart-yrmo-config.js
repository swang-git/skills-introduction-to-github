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
    vals: [61000, 72000, 23000, 29000, 67000, 62000, 27000, 14000],
    backgroundColor: backgroundColor,
    borderColor: borderColor,
    borderWidth: 2,
    borderRadius: 30,
  }],
}
const options = {
  onClick: (event, element, chart) => {
    if (element.length === 0) return
    const idx = element[0].index
    const mnth = parseInt(chart.data.labels[idx])
    const year = chart.data.year
    console.log(`mnth=${mnth}, year=${year}`)
    // emitter.emit('show-chart', 'ympi', mnth, year)
    // emitter.emit('open-ChartsProxy1', 'ympi', mnth, year)
    emitter.emit('open-ChartsProxy2', 'ympi', mnth, year)
  },
  responsive: true,
  scales: {
    x: {
      // display: false, // to show the labels
      ticks: {
        font: { size: 16, color: 'navy', weight: 'bold', family: 'stzhongsong' }
      },
      // title: {
      //   display: true,
      //   text: 'years',
      //   font: { size: 18, weight: 'bold', family: 'stzhongsong' },
      // }
    },
    y: {
      display: false,
      // beginAtZero: false,
      // ticks: {
      //   // callback: (value, index, ticks) => { return value/1000 + 'K' },
      //   font: { size: 16, weight: 'bold', family:'stzhongsong' }
      // }
    }
  },
  plugins: {
    title: {
      display: true,
      position: 'top',
      text: "年 度 消 费 一 览 表",
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
      titleFont: { size: 0 }, // no show title
      bodyFont: { size: 24 },
      callbacks: {
        label: (ctx) => {
          const mnth = parseInt(ctx.label)
          const year = parseInt(ctx.dataset.year)
          // console.log('-CK-', ctx.dataset)
          const spend = ctx.dataset.vals[ctx.dataIndex]
          return year + '年 ' + mnth + '月份支出: $' + spend
        }
      },
      // footerFont: { size: 18 },
      // callbacks: {
      //   footer: lastDinner,
      //   // afterBody: unitName,
      //   title: checkingTime,
      // }
    },
    datalabels: {
      align: 'end',
      anchor: 'end',
      offset: -6,
      // color: 'navy',
      // color: function(context) { return context.dataset.backgroundColor; },
      color: function(ctx) {
        const idx = ctx.dataIndex
        var da = ctx.dataset.data[idx]
        var va = ctx.dataset.vals[idx]
        return Math.abs(da - va) > 10 ? 'red' : 'green'
      },
      font: function(context) {
        var w = context.chart.width;
        return {
          size: w < 1000 ? 16 : 20,
          weight: 'bold',
          family:'youyuan',
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
  // type: 'line',
  data: data,
  opts: options,
}
export default (config)

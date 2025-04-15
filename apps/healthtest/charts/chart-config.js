const PortfolioGL = (tooltipItems) => {
  let normal = 'normal<=5.7'
  let prediabetes = 'prediabetes>5.7<=6.2'
  let diabetes = 'diabetes>6.2'
  tooltipItems.forEach(item => {
    // console.log('item', item)
    // pval = item.dataset.pdata[item.parsed.x]
    // pdif = item.dataset.pdiff[item.parsed.x]
  })
  // return 'GL/Portf: ' + pdif + ' / ' + pval
  return normal + ' | ' + prediabetes + ' | ' + diabetes
} 
const data = {
  labels: [],
  datasets: [
    { backgroundColor: "rgb(155, 100, 195, 0.2)", borderColor: "blue",  borderWidth: 1, pointRadius: 5, fill: true, tension: 0.3, }, // handis
    // { backgroundColor: "rgb(250, 210, 100, 0.2)", borderColor: "red",   borderWidth: 1, pointRadius: 5, fill: true, tension: 0.4, }, // scores
    // { backgroundColor: "rgb(100, 220, 100, 0.2)", borderColor: "black", borderWidth: 1, pointRadius: 5, fill: true, tension: 0.4, }, // rating
    // { backgroundColor: "rgb(155, 150, 155, 0.2)", borderColor: "green", borderWidth: 1, pointRadius: 5, fill: true, tension: 0.4, }, // slope
  ],
}
const options = {
  layout: {
    padding: {
      right: 30
    }
  },
  responsive: true,
  scales: {
    x: {
      ticks: {
        color: 'red',
        type: 'timeseries',
        font: { size:18, weight:500 }
      }
    },
    y: {
      beginAtZero: true,
      suggestedMax: +3,
      suggestedMin: -3,
      // ticks: { callback: (value) => { return value + 2 } }
      // ticks: { callback: (value, index, ticks) => { return value/1000 + 'K' } }
    }
  },
  interaction: {
    intersect: false,
    mode: 'index',
  },
  plugins: {
    title: {
      display: true,
      text: () => {
        // const {axis = 'xy', intersect, mode} = ctx.chart.options.interaction;
        // return 'Mode: ' + mode + ', axis: ' + axis + ', intersect: ' + intersect;
        // return 'Play Data: Index Differentials, Strokes, Course Rating/Slope'
      }
    },
    legend: {
      labels: {
        font: { size:24, weight:500 },
        color: 'darkblue',
      }
    },
    tooltip: {
      titleFont: { size: 24 },
      bodyFont: { size: 20 },
      footerFont: { size: 20 },
      callbacks: {
        footer: PortfolioGL,
        // afterBody: unitName,
        // title: checkingTime,
      }
    },
    datalabels: {
      align: 'top',
      anchor: 'end',
      color: '#000',
      font: { size: 18, weight: 400 },
      labels: {
        value: {},
        title: {
          color: 'red'
        }
      }
    }
  },
}
export const config = {
  type: 'line',
  data: data,
  options: options,
}
export default config

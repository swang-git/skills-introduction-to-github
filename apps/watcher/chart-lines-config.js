const tpval = (tooltipItems) => {
  let ppval = null
  tooltipItems.forEach(item => {
    console.log('item', item)
    ppval = item.dataset.tpval[item.parsed.x]
  })
  return 'prtf: ' + ppval
} 
const data = {
  labels: [],
  datasets: [
    { backgroundColor: "rgb(250, 210, 100, 0.2)", borderColor: "red",   borderWidth: 1, pointRadius: 5, fill: true, tension: 0.4, }, // gl
    { backgroundColor: "rgb(155, 100, 195, 0.2)", borderColor: "blue",  borderWidth: 1, pointRadius: 5, fill: true, tension: 0.4, }, // dj
    { backgroundColor: "rgb(100, 220, 180, 0.2)", borderColor: "black", borderWidth: 1, pointRadius: 5, fill: true, tension: 0.4, }, // ns
    { backgroundColor: "rgb(185, 100, 100, 0.2)", borderColor: "green", borderWidth: 1, pointRadius: 5, fill: true, tension: 0.4, }, // sp
    { backgroundColor: "rgb(250, 110, 177, 0.2)", borderColor: "pink",  borderWidth: 1, pointRadius: 5, fill: true, tension: 0.4, }, // ft
    { backgroundColor: "rgb(100, 120, 254, 0.2)", borderColor: "purple",borderWidth: 1, pointRadius: 5, fill: true, tension: 0.4, }, // nk
  ],
}
const options = {
  responsive: true,
  scales: {
    x: {
      ticks: {
        color: 'red',
        font: { size:14, weight:500 }
      }
    },
    y: {
      beginAtZero: true,
      suggestedMax: +2,
      suggestedMin: -2,
      // min: -10,
      // ticks: { callback: (value) => { return value * 20 } }
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
        return 'Play Data: Index Differentials, Strokes, Course Rating/Slope'
      }
    },
    legend: {
      labels: {
        font: { size:6, weight:400 },
        color: 'darkblue',
      }
    },
    tooltip: {
      titleFont: { size: 27 },
      bodyFont: { size: 18 },
      footerFont: { size: 18 },
      callbacks: {
        footer: tpval,
        // afterBody: unitName,
        // title: checkingTime,
      }
    },
    datalabels: {
      align: 'top',
      anchor: 'end',
      color: '#000',
      font: { size: 12, weight: 400 },
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

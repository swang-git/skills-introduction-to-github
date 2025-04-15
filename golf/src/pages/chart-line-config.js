const coursePlayed = (tooltipItems) => {
  let course = null
  tooltipItems.forEach(item => {
    // console.log('item', item)
    course = item.dataset.course[item.parsed.x]
  })
  return 'Played at ' + course
} 
const data = {
  labels: [],
  datasets: [
    { backgroundColor: "rgb(155, 100, 195, 0.2)", borderColor: "blue",  borderWidth: 1, pointRadius: 5, fill: true, tension: 0.4, }, // handis
    { backgroundColor: "rgb(250, 210, 100, 0.2)", borderColor: "red",   borderWidth: 1, pointRadius: 5, fill: true, tension: 0.4, }, // scores
    { backgroundColor: "rgb(100, 220, 100, 0.2)", borderColor: "black", borderWidth: 1, pointRadius: 5, fill: true, tension: 0.4, }, // rating
    { backgroundColor: "rgb(155, 150, 155, 0.2)", borderColor: "green", borderWidth: 1, pointRadius: 5, fill: true, tension: 0.4, }, // slope
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
      max: 144,
      // min: 1,
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
        return 'Play Data: Index Differentials, Strokes, Course Rating/Slope'
      }
    },
    legend: {
      labels: {
        font: { size:20, weight:500 },
        color: 'darkblue',
      }
    },
    tooltip: {
      titleFont: { size: 20 },
      bodyFont: { size: 24 },
      footerFont: { size: 18 },
      callbacks: {
        footer: coursePlayed,
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

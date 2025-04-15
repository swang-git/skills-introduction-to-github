const data = {
  labels: [],
  datasets: [{
    datalabels: {
      labels: {
        title: null
      },
    },
    // borderColor: "rgb(155, 99, 255)",
    borderColor: "lightcyan",
    segment: {
      backgroundColor: ctx => {
        const py = ctx.p0.parsed.y
        // console.log('-ck-ctx.p0', py)
        const a1ps = config.data.datasets[0].a1ps
        // console.log('-ck-a1pX', typeof(a1ps), a1ps.map(p => { return p[0] == 6.6 ? p[1] : 0 }))
        if ((a1ps.map(p => { return p[0] == 6.6 ? p[1] : 0})).indexOf(py) >= 0) return  'rgba(225, 12, 84,0.5)'
        else if ((a1ps.map(p => { return p[0] == 6.5 ? p[1] : 0})).includes(py)) return 'rgba(255,106, 86,0.5)'
        else if ((a1ps.map(p => { return p[0] == 6.4 ? p[1] : 0})).includes(py)) return 'rgba(245,160, 90,0.5)'
        else if ((a1ps.map(p => { return p[0] == 6.3 ? p[1] : 0})).includes(py)) return 'rgba(226,235,169,0.5)'
        else if ((a1ps.map(p => { return p[0] == 6.2 ? p[1] : 0})).includes(py)) return 'rgba(100,209, 17,0.5)'
        else if ((a1ps.map(p => { return p[0] == 6.1 ? p[1] : 0})).includes(py)) return 'rgba(75, 192,192,0.5)'
        else if ((a1ps.map(p => { return p[0] == 6.0 ? p[1] : 0})).includes(py)) return 'rgba(90, 220,180,0.5)'
        else if ((a1ps.map(p => { return p[0] == 5.9 ? p[1] : 0})).includes(py)) return 'rgba(150,240,190,0.5)'
        else return 'lightcyan'
        // else return 'rgb(25,95,132,0.5)'
      }
    },
    borderWidth: 1,
    pointRadius: 0,
    fill: true,
    tension: 0.1,
  }]
}
const options= {
  responsive: true,
  scales: {
    x: {
      ticks: {
        color: 'red',
        font: { size:14, weight:500 }
      }
    },
    y: {
      beginAtZero: false,
      // max: 10,
      // min: 3,
      // ticks: { callback: (value) => { return value + 2 } }
      // ticks: { callback: (value, index, ticks) => { return value/1000 + 'K' } }
    }
  },
  plugins: {
    legend: {
      labels: {
        font: { size: 20, weight: 500 },
        color: 'darkblue',
      }
    },
    // tooltip: {
    //   titleFont: { size: 20 },
    //   bodyFont: { size: 24 },
    //   footerFont: { size: 18 },
    //   callbacks: {
    //     // footer: lastDinner,
    //     // afterBody: unitName,
    //     // title: checkingTime,
    //   }
    // },
    // datalabels: {
    //   align: 'top',
    //   anchor: 'end',
    //   color: '#000',
    //   font: { size: 12, weight: 400 },
    //   labels: {
    //     value: {},
    //     title: {
    //       color: 'red'
    //   }
    // }
  },
}
export const config = {
  type: 'line',
  data: data,
  opts: options,
}
export default config

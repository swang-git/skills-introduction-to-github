import emitter from 'tiny-emitter/instance'
import A1Cp_COLORS from './colors'
const chartConfig = {
  Desk: {
    type: 'doughnut',
    data: {
      labels: [],
      datasets: [{
        backgroundColor: A1Cp_COLORS,
        hoverBorderColor: 'white',
        datalabels: {
          labels: {
            index: {
              align: 'end',
              anchor: 'end',
              color: function(ctx) {
                return ctx.dataset.backgroundColor;
              },
              font: {size: 18},
              formatter: function(value, ctx) {
                // let eag1 = ctx.dataset.eag1s[ctx.dataIndex]
                let exg = ctx.dataset.eagx[ctx.dataIndex]
                let eag = ctx.dataset.eags[ctx.dataIndex]
                let glu = ctx.dataset.glus[ctx.dataIndex]
                return ctx.active ?
                  emitter.emit('dcenter-txt', [
                    ctx.dataset.data[ctx.dataIndex] + ' / ' + ctx.dataset.a1cp[ctx.dataIndex] + '(%)', 
                    ctx.dataset.dgap[ctx.dataIndex], 
                    ctx.dataset.days[ctx.dataIndex],
                    // eag + ' ~ ' + eag1 + ' mmol/L',
                    ctx.dataset.eagx[ctx.dataIndex] + '(mg/dL)',
                    ctx.dataset.backgroundColor[ctx.dataIndex],
                    glu + '(mmol/dL)',
                  ])
                  : ctx.dataset.days[ctx.dataIndex] + '天'
              },
              offset: 8,
              opacity: function(ctx) {
                return ctx.active ? 1 : 0.5;
              }
            },
            name: {
              align: 'top',
              font: {size: 18},
              formatter: function(value, ctx) {
                return ctx.active ? value + ' mmol/mol' : ctx.chart.data.labels[ctx.dataIndex]
              }
            },
            value: {
              align: 'bottom',
              font: {size: 18},
              formatter: function(value, ctx) {
                return ctx.active ? '糖化血红蛋白' : value + ' / ' + ctx.dataset.a1cp[ctx.dataIndex] + '(%)'
              },
              padding: 4
            },
            eags: {
              align: 'bottom',
              offset: 35,
              font: {size: 18},
              formatter: function(value, ctx) {
                let eag = ctx.dataset.eags[ctx.dataIndex]
                return ctx.active ? 'mg/dL mmol/L' : eag + ' / ' + (eag/18.015).toFixed(1)
              },
              padding: 4
            }
          }
        }
      }]
    },
    options: {
      plugins: {
        tooltip: { enabled: false },
        legend: { display: false },
        datalabels: {
          color: 'lightcyan',
          display: function(ctx) {
            return ctx.dataset.data[ctx.dataIndex] > 10;
          },
          font: {
            weight: 'bold',
          },
          offset: 0,
          padding: 0
        }
      },
      aspectRatio: 6 / 5,
      cutoutPercentage: 8,
      layout: {
        padding: 16
      },
      elements: {
        line: {
          fill: false,
          tension: 0.4
        },
        point: {
          hoverRadius: 7,
          radius: 5
        }
      },
    }
  },
  // this is IM
  IM: {
    type: 'doughnut',
    data: {
      labels: [1,2,3,4,5,6,7],
      datasets: [{
        backgroundColor: A1Cp_COLORS,
        hoverBorderColor: 'white',
        a1cp: [1,2,3,4,5,6,7],
        days: [1,2,3,4,5,6,7],
        dgap: [1,2,3,4,5,6,7],
        datalabels: {
          labels: {
            index: {
              align: 'end',
              anchor: 'end',
              color: function(ctx) {
                return ctx.dataset.backgroundColor;
              },
              font: {size: 14},
              formatter: function(value, ctx) {
                let eag = ctx.dataset.eags[ctx.dataIndex]
                let glu = ctx.dataset.glus[ctx.dataIndex]
                return ctx.active ?
                  emitter.emit('icenter-txt', [
                    ctx.dataset.data[ctx.dataIndex] + ' / ' + ctx.dataset.a1cp[ctx.dataIndex] + '(%)', 
                    ctx.chart.data.labels[ctx.dataIndex], 
                    ctx.dataset.days[ctx.dataIndex],
                    glu + 'mmol/L',
                    ctx.dataset.backgroundColor[ctx.dataIndex],
                  ])
                  : ctx.dataset.days[ctx.dataIndex] + ' 天'
              },
              offset: 8,
              opacity: function(ctx) {
                return ctx.active ? 1 : 0.5;
              }
            },
            name: {
              align: 'center',
              color: 'lightcyan',
              font: {size: 18},
              formatter: function(value, ctx) {
                return ctx.active ? null : ctx.dataset.a1cp[ctx.dataIndex] + '%'
              }
            },
            value: {
              align: 'center',
              font: {size: 16},
              color: 'lightcyan',
              formatter: function(value, ctx) {
                let eag = ctx.dataset.eags[ctx.dataIndex]
                // return ctx.active ? 'A1C(%)' : null
                return ctx.active ? eag + 'mg/dL' : null
              },
              padding: 4
            }
          }
        }
      }]
    },
    options: {
      plugins: {
        tooltip: { enabled: false },
        legend: { display: false },
        datalabels: {
          color: '#000',
          display: function(ctx) {
            return ctx.dataset.data[ctx.dataIndex] > 10;
          },
          offset: 0,
          padding: 0
        }
      },
      // Core options
      aspectRatio: 6 / 5,
      cutoutPercentage: 8,
      layout: {
        padding: 16
      },
      elements: {
        line: {
          fill: false,
          tension: 0.4
        },
        point: {
          hoverRadius: 7,
          radius: 5
        }
      },
    }
  }
}
export default chartConfig
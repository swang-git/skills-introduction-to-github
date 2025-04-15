// import 'chartjs-plugin-datalabels'
// import openURL from 'quasar'
export const chartData = {
  type: 'bar',
  // type: 'horizontalBar',
  data: {
    labels: ['Mercury', 'Venus', 'Earth', 'Mars', 'Jupiter', 'Saturn', 'Uranus', 'Neptune'],
    datasets: [{ // one line graph
      label: 'Number of Moons',
      data: [61000, 72000, 23000, 29000, 67000, 62000, 27000, 14000],
      backgroundColor: [
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
        'rgba(195, 169, 944, 0.8)'
      ],
      borderColor: [
        'rgba(255,99,132,1)',
        'rgba(54, 162, 235, 1)',
        'rgba(255, 206, 86, 1)',
        'rgba(75, 192, 192, 1)',
        'rgba(153, 102, 255, 1)',
        'rgba(255, 159, 64, 1)',
        'rgba(255, 159, 64, 1)',
        'rgba(255,99,132,1)',
        'rgba(54, 162, 235, 1)',
        'rgba(255, 206, 86, 1)',
        'rgba(75, 192, 192, 1)',
        'rgba(153, 102, 255, 1)',
        'rgba(255, 159, 64, 1)',
        'rgba(255, 159, 64, 1)'
      ],
      borderWidth: 1
    }]
  },
  options: {
    // responsive: true,
    // maxBarThickness: 30,
    // lineTension: 1,
    scales: {
      y: {
        beginAtZero: true,
      }
    }
    // scales: {
    //   y: [{
    //     ticks: {
    //       beginAtZero: true,
    //       padding: 5
    //     }
    //   }]
    // },
    // plugins: {
    //   datalabels: {
    //     anchor: 'end',
    //     align: 'top',
    //     formatter: Math.round,
    //     font: {
    //       weight: 'bold'
    //     }
    //   }
    // }
    // onClick: function (e, myChart) {
    //   let i = myChart[0]._index
    //   // openURL('/spend')
    //   // this.$router.push({ path: 'spend' })
    //   console.log('in data', i, this.data.labels[i])
    //   // console.log('in data', i, this.data.labels[i], myChart)
    //   // this.$router.push({ path: 'chart' })
    // }
  }
}

export default chartData

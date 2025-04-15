
const data = {
  datasets: [
    {
      label: 'a1ch',
      data: [],
      backgroundColor:'red',
      borderColor: 'yellow',
      borderWidth: 3,
      borderRadius: 35,
      borderSkipped: false,
    },
    {
      label: 'a1c',
      data: [],
      backgroundColor: 'blue',
      borderColor: 'white',
      borderWidth: 2,
      borderRadius: 35,
      borderSkipped: false,
    }
  ]
}
const config = {
  type: 'bar',
  data: data,
  options: {
    responsive: true,
    scales: {
      x: {
        ticks: {
          color: 'red',
          font: { size: 15 }
        }
      },
      y: {
        // beginAtZero: false,
        max: 9,
        min: 1,
      }
    },
    plugins: {
      legend: {
        labels: {
          font: { size:18, weight:500 },
          color: 'darkblue',
        }
      },
      tooltip: {
        titleFont: { size: 30 },
        bodyFont: { size: 24 },
        // footerFont: { size: 18 },
        callbacks: {
          // footer: lastDinner,
          // afterBody: unitName,
          // title: checkingTime,
        }
      },
      datalabels: {
        align: 'top',
        anchor: 'center',
        color: '#FFF',
        font: { size:19, weight:500 },
        labels: {
          value: {},
          title: {
            // color: 'red'
          }
        }
      }
    }
  },
};
export default config;
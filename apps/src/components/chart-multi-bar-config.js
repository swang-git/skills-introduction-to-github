const data = {
  labels: ['Mercury', 'Venus', 'Earth', 'Mars', 'Jupiter', 'Saturn', 'Uranus', 'Neptune'],
  datasets: [{ // one line graph
    datalabels: {
      // display: false,
      color: '#000',
      font: {
        size: 16,
      }
    },
    tension: 0.5,
    label: 'Number of Moons',
    data: [61000, 72000, 23000, 29000, 67000, 62000, 27000, 14000],
    backgroundColor: [
      'rgba(100, 255, 230, 0.2)',
    ],
    borderColor: [
      'rgba(200, 55, 10, 0.9)',
    ],
    borderWidth: 1
  },
  {
    // tension: 0.5,
    hidden: true,
    backgroundColor: [
      'rgba(255, 100, 100, 0.3)',
    ],
    borderColor: [
      'rgba(255, 206, 86, 0.9)',
    ],
    borderWidth: 1
  },
  {
    // tension: 0.5,
    hidden: true,
    backgroundColor: [
      'rgba(100, 120, 255, 0.3)',
    ],
    borderColor: [
      'rgba(255, 159, 64, 1)',
    ],
    borderWidth: 1
  }],
}
const opts = {
  fill: true,
  responsive: true,
  plugins: {
    legend: {
      position: 'top',
    },
    title: {
      display: false,
      text: 'Chart.js Bar Chart'
    }
  },
  scales: {
    x: {
      ticks: {
        color: 'red',
        font: { size: 15 }
      }
    }
  }
}
const config = {
  type: 'bar',
  data: data,
  options: opts
}
export default config

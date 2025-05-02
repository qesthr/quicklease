// Customer Satisfaction Chart
function initializeSatisfactionChart() {
    const satisfactionCtx = document.getElementById('satisfactionChart');
    if (!satisfactionCtx) return;

    // Get data from data attributes
    const labels = JSON.parse(satisfactionCtx.dataset.labels || '[]');
    const data = JSON.parse(satisfactionCtx.dataset.data || '[]');
    
    new Chart(satisfactionCtx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: [
                    '#4e73df',
                    '#1cc88a',
                    '#36b9cc',
                    '#f6c23e',
                    '#e74a3b',
                    '#858796'
                ],
                hoverBackgroundColor: [
                    '#2e59d9',
                    '#17a673',
                    '#2c9faf',
                    '#dda20a',
                    '#be2617',
                    '#6b6d7d'
                ],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }],
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.raw + '%';
                        }
                    }
                }
            },
            cutout: '70%',
        },
    });
}

// Monthly Bookings Chart
function initializeBookingsChart() {
    const bookingsCtx = document.getElementById('bookingsChart');
    if (!bookingsCtx) return;

    new Chart(bookingsCtx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Bookings',
                backgroundColor: '#4e73df',
                hoverBackgroundColor: '#2e59d9',
                borderColor: '#4e73df',
                data: [12, 19, 15, 21, 25, 28, 31, 27, 23, 19, 15, 18],
            }],
        },
        options: {
            maintainAspectRatio: false,
            scales: {
                x: {
                    grid: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        maxTicksLimit: 6
                    },
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: "rgb(234, 236, 244)",
                        zeroLineColor: "rgb(234, 236, 244)",
                        drawBorder: false,
                        borderDash: [2],
                        zeroLineBorderDash: [2]
                    },
                    ticks: {
                        maxTicksLimit: 5,
                        padding: 10,
                    }
                },
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyColor: "#858796",
                    titleMarginBottom: 10,
                    titleColor: '#6e707e',
                    titleFontSize: 14,
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    intersect: false,
                    mode: 'index',
                    caretPadding: 10,
                    callbacks: {
                        label: function(context) {
                            return 'Bookings: ' + context.raw;
                        }
                    }
                }
            }
        }
    });
}

// Initialize all charts when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeSatisfactionChart();
    initializeBookingsChart();
});
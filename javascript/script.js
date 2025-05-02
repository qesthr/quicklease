// Main application initialization
document.addEventListener('DOMContentLoaded', function() {
    // Print button functionality
    const printBtn = document.getElementById('print-btn');
    if (printBtn) {
        printBtn.addEventListener('click', function() {
            window.print();
        });
    }

    // Simulate loading data
    function simulateLoading() {
        const metricCards = document.querySelectorAll('.metric-card');
        metricCards.forEach(card => {
            card.style.opacity = '0';
        });

        setTimeout(() => {
            metricCards.forEach(card => {
                card.style.opacity = '1';
                card.style.transition = 'opacity 0.5s ease';
            });
        }, 300);
    }

    simulateLoading();
});


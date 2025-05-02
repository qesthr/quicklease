// Sidebar toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.sidebar');
    const sidebarToggle = document.createElement('button');
    
    sidebarToggle.innerHTML = '<i class="fas fa-bars"></i>';
    sidebarToggle.classList.add('sidebar-toggle');
    sidebarToggle.style.display = 'none';
    document.body.appendChild(sidebarToggle);

    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
    });

    function checkMobileView() {
        if (window.innerWidth <= 768) {
            sidebarToggle.style.display = 'block';
            sidebar.classList.add('collapsed');
        } else {
            sidebarToggle.style.display = 'none';
            sidebar.classList.remove('collapsed');
        }
    }

    window.addEventListener('resize', checkMobileView);
    checkMobileView();
});
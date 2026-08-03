<!-- Scripts Start -->
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- Bootstrap 5 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<!-- Custom Scripts -->
<script src="<?php echo ASSETS_URL; ?>/js/app.js"></script>
<script>
$(document).ready(function() {
    // ============================================================
    // SIDEBAR TOGGLE
    // ============================================================
    $('#sidebarToggle').on('click', function() {
        $('#appSidebar').toggleClass('collapsed');
        $('.app-content').toggleClass('expanded');
    });
    
    // ============================================================
    // DARK MODE TOGGLE
    // ============================================================
    $('#darkModeToggle').on('click', function() {
        $('body').toggleClass('dark-mode');
        const icon = $(this).find('i');
        icon.toggleClass('fa-moon fa-sun');
        localStorage.setItem('darkMode', $('body').hasClass('dark-mode'));
    });
    
    // Check saved dark mode preference
    if (localStorage.getItem('darkMode') === 'true') {
        $('body').addClass('dark-mode');
        $('#darkModeToggle i').removeClass('fa-moon').addClass('fa-sun');
    }
    
    // ============================================================
    // GLOBAL SEARCH
    // ============================================================
    $('#globalSearch').on('keyup', function(e) {
        if (e.key === 'Enter') {
            performSearch($(this).val());
        }
    });
    
    // Keyboard shortcut: Ctrl + K
    $(document).on('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            $('#globalSearch').focus();
        }
        if (e.key === 'Escape') {
            $('#globalSearch').blur();
        }
    });
    
    function performSearch(query) {
        if (query.length > 0) {
            window.location.href = '<?php echo BASE_URL; ?>/search?q=' + encodeURIComponent(query);
        }
    }
    
    // ============================================================
    // TOOLTIPS
    // ============================================================
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // ============================================================
    // NOTIFICATIONS
    // ============================================================
    // Load notifications via AJAX
    function loadNotifications() {
        $.ajax({
            url: '<?php echo BASE_URL; ?>/api/notifications',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    updateNotificationBadge(response.data.unread_count);
                    updateNotificationList(response.data.notifications);
                }
            }
        });
    }
    
    function updateNotificationBadge(count) {
        $('#notificationBadge').text(count).toggle(count > 0);
    }
    
    function updateNotificationList(notifications) {
        // Update notification list dynamically
    }
    
    // Load notifications on page load
    if ($('#notificationBadge').length) {
        loadNotifications();
    }
    
    // Mark all notifications as read
    $('.mark-all-read').on('click', function(e) {
        e.preventDefault();
        $.ajax({
            url: '<?php echo BASE_URL; ?>/api/notifications/read-all',
            method: 'POST',
            data: {
                _csrf: '<?php echo $csrf_token ?? ''; ?>'
            },
            success: function(response) {
                if (response.success) {
                    updateNotificationBadge(0);
                    $('.notification-item.unread').removeClass('unread');
                }
            }
        });
    });
    
    // ============================================================
    // REFRESH BUTTON
    // ============================================================
    $('#refreshBtn').on('click', function() {
        const btn = $(this);
        const icon = btn.find('i');
        icon.addClass('fa-spin');
        setTimeout(function() {
            location.reload();
        }, 500);
    });
    
    // ============================================================
    // EXPORT BUTTON
    // ============================================================
    $('#exportBtn').on('click', function() {
        const format = $('#exportFormat').val() || 'pdf';
        window.location.href = '<?php echo BASE_URL; ?>/export?format=' + format;
    });
    
    // ============================================================
    // RESPONSIVE SIDEBAR
    // ============================================================
    function handleSidebarResponsive() {
        const width = window.innerWidth;
        if (width < 992) {
            $('#appSidebar').addClass('collapsed');
            $('.app-content').addClass('expanded');
        } else {
            if (!localStorage.getItem('sidebarCollapsed') === 'true') {
                $('#appSidebar').removeClass('collapsed');
                $('.app-content').removeClass('expanded');
            }
        }
    }
    
    // Handle window resize
    let resizeTimer;
    $(window).on('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(handleSidebarResponsive, 250);
    });
    
    handleSidebarResponsive();
    
    // ============================================================
    // AUTO REFRESH FOR DASHBOARD
    // ============================================================
    <?php if (isset($auto_refresh) && $auto_refresh): ?>
    setInterval(function() {
        $.ajax({
            url: window.location.href,
            method: 'GET',
            data: { ajax: 1 },
            success: function(response) {
                // Update dashboard widgets
            }
        });
    }, 30000); // 30 seconds
    <?php endif; ?>
    
    // ============================================================
    // CSRF TOKEN SETUP FOR AJAX
    // ============================================================
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
});
</script>
<!-- Scripts End -->
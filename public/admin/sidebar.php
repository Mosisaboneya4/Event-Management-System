<aside class="sidebar">
    <div class="logo">
        <h1>EMS Admin</h1>
    </div>
    <nav class="admin-sidebar">
        <div class="sidebar-logo">
            <img src="../../assets/images/logo.png" alt="EMS Logo">
            <h2>Admin Panel</h2>
        </div>
        <ul class="sidebar-menu">
            <?php
            $menu_items = [
                ['url' => 'dashboard.php', 'icon' => 'fas fa-tachometer-alt', 'title' => 'Dashboard', 'badge' => ''],
                ['url' => 'user_management.php', 'icon' => 'fas fa-users', 'title' => 'User Management', 'badge' => 'New'],
                ['url' => 'ticket_management.php', 'icon' => 'fas fa-ticket-alt', 'title' => 'Ticket Management', 'badge' => ''],
                ['url' => 'system_settings.php', 'icon' => 'fas fa-cogs', 'title' => 'System Settings', 'badge' => ''],
                ['url' => '../logout.php', 'icon' => 'fas fa-sign-out-alt', 'title' => 'Logout', 'badge' => '']
            ];

            foreach ($menu_items as $item) {
                $active = (basename($_SERVER['PHP_SELF']) == $item['url']) ? 'active' : '';
                echo "<li class='menu-item $active'>
                        <a href='{$item['url']}'>
                            <div class='menu-icon'>
                                <i class='{$item['icon']}'></i>
                            </div>
                            <span class='menu-title'>{$item['title']}</span>
                            " . ($item['badge'] ? "<span class='badge'>{$item['badge']}</span>" : "") . "
                        </a>
                    </li>";
            }
            ?>
        </ul>
    </nav>
</aside>

<style>
.sidebar {
    background: linear-gradient(180deg, #2c3e50 0%, #3498db 100%);
    min-height: 100vh;
    width: 250px;
    transition: all 0.3s ease;
}

.sidebar-menu {
    padding: 0;
    margin: 0;
    list-style: none;
}

.menu-item {
    margin: 8px 0;
    transition: all 0.3s ease;
}

.menu-item a {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    color: #fff;
    text-decoration: none;
    border-radius: 8px;
    margin: 0 10px;
}

.menu-item:hover a {
    background: rgba(255, 255, 255, 0.1);
    transform: translateX(5px);
}

.menu-item.active a {
    background: rgba(255, 255, 255, 0.2);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.menu-icon {
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    margin-right: 12px;
}

.menu-title {
    font-weight: 500;
    font-size: 15px;
}

.badge {
    background: #e74c3c;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
    margin-left: auto;
}

.sidebar-logo {
    padding: 20px;
    text-align: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    margin-bottom: 20px;
}

.sidebar-logo img {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    margin-bottom: 10px;
}

.sidebar-logo h2 {
    color: white;
    font-size: 18px;
    margin: 0;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const menuItems = document.querySelectorAll('.menu-item');
    
    menuItems.forEach(item => {
        item.addEventListener('mouseenter', () => {
            item.querySelector('.menu-icon').style.transform = 'scale(1.1) rotate(5deg)';
        });
        
        item.addEventListener('mouseleave', () => {
            item.querySelector('.menu-icon').style.transform = 'scale(1) rotate(0deg)';
        });
    });
});
</script>

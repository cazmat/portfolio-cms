<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>" href="index.php">
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['projects.php', 'project-add.php', 'project-edit.php']) ? 'active' : ''; ?>" href="projects.php">
                    Projects
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['schedule.php', 'schedule-add.php', 'schedule-edit.php']) ? 'active' : ''; ?>" href="schedule.php">
                    Schedule
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['users.php', 'user-add.php', 'user-edit.php']) ? 'active' : ''; ?>" href="users.php">
                    Users
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'about.php' ? 'active' : ''; ?>" href="about.php">
                    About Page
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['messages.php', 'message-view.php']) ? 'active' : ''; ?>" href="messages.php">
                    Messages
                    <?php
                    $unreadCount = $db->fetchOne("SELECT COUNT(*) as count FROM messages WHERE status = 'unread'")['count'];
                    if ($unreadCount > 0):
                    ?>
                        <span class="badge bg-warning text-dark"><?php echo $unreadCount; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'whitelist.php' ? 'active' : ''; ?>" href="whitelist.php">
                    🛡️ Whitelist
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : ''; ?>" href="settings.php">
                    Settings
                </a>
            </li>
        </ul>
    </div>
</nav>

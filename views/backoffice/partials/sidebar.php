<?php
$sidebarSection = $sidebarSection ?? '';
$sidebarCourseId = isset($sidebarCourseId) ? (int) $sidebarCourseId : null;
$quizzesHref = $sidebarCourseId
    ? backofficeRoute('courses', 'quizzes', ['course_id' => $sidebarCourseId])
    : backofficeRoute('courses', 'index');

$navItems = [
    [
        'key' => 'dashboard',
        'label' => 'Dashboard',
        'icon' => 'fa-chart-line',
        'href' => backofficeRoute('dashboard', 'index'),
    ],
    [
        'key' => 'courses',
        'label' => 'Courses',
        'icon' => 'fa-book',
        'href' => backofficeRoute('courses', 'index'),
    ],
    [
        'key' => 'quizzes',
        'label' => 'Quizzes',
        'icon' => 'fa-question-circle',
        'href' => $quizzesHref,
    ],
];
?>
<aside class="sidebar">
    <div class="sidebar-shell">
        <div class="sidebar-header">
            <a class="sidebar-brand" href="<?= htmlspecialchars(backofficeRoute('dashboard', 'index')); ?>">
                <span class="brand-mark">
                    <i class="fas fa-graduation-cap"></i>
                </span>
                <span class="brand-copy">
                    <strong>EduMatch</strong>
                    <small>Backoffice</small>
                </span>
            </a>
        </div>

        <nav class="sidebar-menu" aria-label="Backoffice navigation">
            <?php foreach ($navItems as $item): ?>
                <a class="menu-item<?= $sidebarSection === $item['key'] ? ' active' : ''; ?>" href="<?= htmlspecialchars($item['href']); ?>">
                    <span class="menu-icon">
                        <i class="fas <?= htmlspecialchars($item['icon']); ?>"></i>
                    </span>
                    <span class="menu-label"><?= htmlspecialchars($item['label']); ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="sidebar-meta">
            
        </div>
    </div>
</aside>

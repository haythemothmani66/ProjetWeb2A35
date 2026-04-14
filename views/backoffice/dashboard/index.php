<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Jost:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/backoffice.css">
</head>
<body>
    <div class="admin-wrapper">
        <?php
        $sidebarSection = 'dashboard';
        $sidebarCourseId = null;
        require dirname(__DIR__) . '/partials/sidebar.php';
        ?>

        <div class="main-content">
            <header class="top-bar">
                <h1>Dashboard</h1>
                <div class="user-profile">
                    <span>Admin User</span>
                </div>
            </header>

            <div class="content">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= htmlspecialchars($stats['totalCourses'] ?? 0); ?></h3>
                            <p>Total Courses</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= htmlspecialchars($stats['publishedCourses'] ?? 0); ?></h3>
                            <p>Published Courses</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= htmlspecialchars($stats['totalQuizzes'] ?? 0); ?></h3>
                            <p>Total Quizzes</p>
                        </div>
                    </div>
                </div>

                <div class="quick-actions">
                    <h2>Quick Actions</h2>
                    <div class="action-buttons">
                        <a href="<?= htmlspecialchars(backofficeRoute('courses', 'create')); ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Course
                        </a>
                        <a href="<?= htmlspecialchars(backofficeRoute('quizzes', 'create')); ?>" class="btn btn-secondary">
                            <i class="fas fa-plus"></i> Add Quiz
                        </a>
                        <a href="<?= htmlspecialchars(frontofficeRoute('courses', 'index')); ?>" class="btn btn-outline">
                            <i class="fas fa-eye"></i> View Frontoffice
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            background: #f0f4ff;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: #00d4ff;
        }

        .stat-info h3 {
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 5px 0;
        }

        .stat-info p {
            color: #666;
            margin: 0;
            font-size: 14px;
        }

        .quick-actions {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .quick-actions h2 {
            margin: 0 0 20px 0;
            font-size: 20px;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 20px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            transition: 0.3s;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: #00d4ff;
            color: white;
        }

        .btn-primary:hover {
            background: #00b8d4;
        }

        .btn-secondary {
            background: #0b104a;
            color: white;
        }

        .btn-secondary:hover {
            background: #0a0a35;
        }

        .btn-outline {
            background: transparent;
            color: #0b104a;
            border: 2px solid #0b104a;
        }

        .btn-outline:hover {
            background: #f0f4ff;
        }
    </style>
</body>
</html>

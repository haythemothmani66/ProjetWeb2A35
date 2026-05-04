<?php $sidebarSection = 'certificates'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificates - Backoffice</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --primary: #4e73df;
            --primary-dark: #2e59d9;
            --sidebar-bg: #4e73df;
            --sidebar-hover: #2e59d9;
            --bg-light: #f8f9fc;
            --text-dark: #5a5c69;
            --text-light: #858796;
            --card-border: #e3e6f0;
            --white: #ffffff;
            --shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        body {
            margin: 0;
            padding: 0;
            background-color: var(--bg-light);
            color: var(--text-dark);
            font-family: 'DM Sans', sans-serif;
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar styles copied from standard layout */
        .sidebar {
            width: 250px;
            background-color: var(--sidebar-bg);
            color: var(--white);
            flex-shrink: 0;
            transition: all 0.3s;
        }
        .sidebar-shell {
            position: sticky;
            top: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }
        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .sidebar-brand {
            color: var(--white);
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 1.2rem;
        }
        .sidebar-brand:hover {
            color: var(--white);
            text-decoration: none;
        }
        .brand-mark i {
            font-size: 2rem;
        }
        .brand-copy {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        .brand-copy small {
            font-size: 0.7rem;
            opacity: 0.8;
        }
        .sidebar-menu {
            padding: 20px 0;
            flex-grow: 1;
        }
        .menu-item {
            padding: 15px 20px;
            display: flex;
            align-items: center;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.2s;
            gap: 15px;
        }
        .menu-item:hover, .menu-item.active {
            color: var(--white);
            background-color: var(--sidebar-hover);
            text-decoration: none;
        }
        .menu-icon {
            width: 20px;
            text-align: center;
        }

        .main-wrapper {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .header {
            background-color: var(--white);
            height: 70px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            display: flex;
            align-items: center;
            padding: 0 25px;
            z-index: 10;
        }

        .page-content {
            padding: 25px;
            flex-grow: 1;
        }

        .page-title {
            color: var(--text-dark);
            font-size: 1.75rem;
            font-weight: 400;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card {
            background-color: var(--white);
            border-radius: 0.35rem;
            border: 1px solid var(--card-border);
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 25px;
        }
        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid var(--card-border);
            padding: 15px 20px;
            font-weight: 600;
            color: var(--primary);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-body {
            padding: 20px;
        }

        .table th {
            color: var(--text-dark);
            font-weight: 600;
            border-top: none;
        }
        .table td {
            vertical-align: middle;
            color: var(--text-light);
        }
        .action-btns .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            margin-right: 5px;
        }
    </style>
</head>
<body>

    <?php require dirname(__DIR__) . '/partials/sidebar.php'; ?>

    <main class="main-wrapper">
        <header class="header">
            <h5 class="mb-0 text-gray-800">Certificates</h5>
        </header>

        <div class="page-content">
            <div class="page-title">
                <h1>Manage Certificates</h1>
            </div>

            <div class="card">
                <div class="card-header">
                    <span><i class="fas fa-certificate me-2"></i> All Certificates</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Student Name</th>
                                    <th>Course</th>
                                    <th>Quiz</th>
                                    <th>Issued At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($certificates)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No certificates issued yet.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($certificates as $cert): ?>
                                        <tr>
                                            <td><?= (int) $cert['id'] ?></td>
                                            <td><strong><?= htmlspecialchars((string) $cert['student_name']) ?></strong></td>
                                            <td><?= htmlspecialchars((string) $cert['course_title']) ?></td>
                                            <td><?= htmlspecialchars((string) $cert['quiz_title']) ?></td>
                                            <td><?= htmlspecialchars((string) $cert['issued_at']) ?></td>
                                            <td class="action-btns">
                                                <a href="<?= frontofficeRoute('certificates', 'show', ['id' => $cert['id']]) ?>" target="_blank" class="btn btn-sm btn-info text-white" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <form action="<?= htmlspecialchars(backofficeRoute('certificates', 'delete')); ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this certificate?');">
                                                    <input type="hidden" name="id" value="<?= (int) $cert['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

</body>
</html>

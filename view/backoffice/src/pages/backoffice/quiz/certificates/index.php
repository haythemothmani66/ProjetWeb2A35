<?php $BO = '/gestion_users/view/backoffice/src'; ?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Back Office - Certificates | EduMatch Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&display=swap" />
    <link rel="stylesheet" href="<?= $BO ?>/assets/css/theme.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simplebar@6.2.5/dist/simplebar.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.47.0/tabler-icons.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <script src="<?= $BO ?>/assets/js/vendors/color-modes.js"></script>
    <script>
        if (localStorage.getItem('sidebarExpanded') === 'false') { document.documentElement.classList.add('collapsed'); document.documentElement.classList.remove('expanded'); }
        else { document.documentElement.classList.remove('collapsed'); document.documentElement.classList.add('expanded'); }
    </script>
    <style>


        /* Sidebar styles copied from standard layout */
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
        .card-

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
    <div>
        <?php include __DIR__ . '/../../../../partials_php/sidebar.php'; ?>
        <div id="content" class="position-relative h-100">
            <?php include __DIR__ . '/../../../../partials_php/topbar.php'; ?>
            <div class="custom-container">

                <div class="row mb-6 g-6 align-items-end">
                    <div class="col-lg-8">
                        <p class="text-uppercase text-secondary small mb-2">Module Quiz</p>
                        <h1 class="mb-0">Manage Certificates</h1>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <a href="javascript:history.back()" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
                    </div>
                </div>

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

            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simplebar@6.2.5/dist/simplebar.min.js"></script>
    <script src="<?= $BO ?>/assets/js/main.js"></script>
    <script src="<?= $BO ?>/assets/js/vendors/sidebarnav.js"></script>

</body>
</html>
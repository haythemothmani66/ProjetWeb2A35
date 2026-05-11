<?php
// Protection admin (au cas ou ce layout serait charge directement)
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: /gestion_users/view/template/sign-in.php');
    exit;
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="EduMatch" name="author">
    <title>Administration Evenements | EduMatch Admin</title>

    <!-- Fonts Public Sans (Dasher) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&display=swap">

    <!-- Dasher theme CSS (le meme que dashboard.php) -->
    <link rel="stylesheet" href="/gestion_users/view/backoffice/src/assets/css/theme.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simplebar@6.2.5/dist/simplebar.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.47.0/tabler-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- Bootstrap 5 (Dasher utilise BS5) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <!-- CSS du module evenement (pour styles specifiques) -->
    <link rel="stylesheet" href="<?php echo PROJECT_URL; ?>/public/css/style.css">

    <!-- jsPDF pour les exports -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

    <!-- Color modes Dasher -->
    <script src="/gestion_users/view/backoffice/src/assets/js/vendors/color-modes.js"></script>
    <script>
        if (localStorage.getItem("sidebarExpanded") === "false") {
            document.documentElement.classList.add("collapsed");
            document.documentElement.classList.remove("expanded");
        } else {
            document.documentElement.classList.remove("collapsed");
            document.documentElement.classList.add("expanded");
        }
    </script>

    <style>
      /* Garde compat avec les classes utilisees dans le module evenement */
      .template-admin-content { padding-top: 0 !important; padding-bottom: 1.5rem; }
      .custom-container { padding: 1.5rem 2rem; }

      /* Bouton custom du module mappes sur Bootstrap si necessaire */
      .btn-admin-site { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1rem; border-radius: 50px; background: linear-gradient(135deg, #6366f1, #8B5CF6); color: white !important; text-decoration: none; font-weight: 600; transition: all 0.3s ease; }
      .btn-admin-site:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(99,102,241,0.3); color: white; text-decoration: none; }
    </style>
</head>

<body>
    <div>
        <!-- Sidebar Dasher unifie -->
        <?php include __DIR__ . '/../../../view/backoffice/src/partials_php/sidebar.php'; ?>

        <div id="content" class="position-relative h-100">
            <!-- Topbar Dasher unifie -->
            <?php include __DIR__ . '/../../../view/backoffice/src/partials_php/topbar.php'; ?>

            <!-- Contenu du module evenement -->
            <div class="custom-container template-admin-content">
                <?php echo $content; ?>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation suppression (utilise par le module) -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Confirmation de suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <p id="deleteConfirmMessage" class="mb-0 text-muted">Voulez-vous confirmer la suppression ?</p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger rounded-pill" id="deleteConfirmYesBtn">Oui</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle + Simplebar (necessaires pour Dasher sidebar) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simplebar@6.2.5/dist/simplebar.min.js"></script>
    <script src="/gestion_users/view/backoffice/src/assets/js/main.js"></script>
    <script src="/gestion_users/view/backoffice/src/assets/js/vendors/sidebarnav.js"></script>

    <!-- Validation JS du module evenement -->
    <script src="<?php echo PROJECT_URL; ?>/public/js/validation.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Modal de suppression
        var confirmModalEl = document.getElementById('deleteConfirmModal');
        var confirmMessageEl = document.getElementById('deleteConfirmMessage');
        var confirmYesBtn = document.getElementById('deleteConfirmYesBtn');
        var pendingDeleteUrl = null;

        if (confirmModalEl && confirmMessageEl && confirmYesBtn) {
            var deleteModal = new bootstrap.Modal(confirmModalEl);
            document.querySelectorAll('.js-confirm-delete').forEach(function (link) {
                link.addEventListener('click', function (event) {
                    event.preventDefault();
                    pendingDeleteUrl = link.getAttribute('href');
                    confirmMessageEl.textContent = link.getAttribute('data-confirm-message') || 'Voulez-vous confirmer la suppression ?';
                    deleteModal.show();
                });
            });

            confirmYesBtn.addEventListener('click', function () {
                if (pendingDeleteUrl) {
                    window.location.href = pendingDeleteUrl;
                }
            });
        }

        // Export PDF
        document.querySelectorAll('.js-export-pdf').forEach(function (button) {
            button.addEventListener('click', function () {
                var tableId = button.getAttribute('data-table-id');
                var title = button.getAttribute('data-title') || 'Export PDF';
                var table = document.getElementById(tableId);
                if (!table || !window.jspdf || !window.jspdf.jsPDF) {
                    return;
                }

                var headers = [];
                table.querySelectorAll('thead th').forEach(function (th) {
                    headers.push(th.innerText.trim());
                });

                var rows = [];
                table.querySelectorAll('tbody tr').forEach(function (tr) {
                    if (tr.classList.contains('participants-row') && tr.classList.contains('d-none')) {
                        return;
                    }
                    var cells = [];
                    tr.querySelectorAll('td').forEach(function (td) {
                        cells.push(td.innerText.replace(/\s+/g, ' ').trim());
                    });
                    if (cells.length > 0) {
                        rows.push(cells);
                    }
                });

                var doc = new window.jspdf.jsPDF();
                doc.setFontSize(14);
                doc.text(title, 14, 16);
                doc.autoTable({
                    head: [headers],
                    body: rows,
                    startY: 22,
                    styles: { fontSize: 9, cellPadding: 2 }
                });
                doc.save(title.toLowerCase().replace(/[^a-z0-9]+/g, '_') + '.pdf');
            });
        });
    });
    </script>
</body>
</html>

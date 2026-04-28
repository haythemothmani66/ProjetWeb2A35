<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Module Événement</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark edumatch-admin-navbar">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>/AdminEvenement/index">Admin Événements</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/AdminCategorie/index">Catégories</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/AdminEvenement/index">Événements</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/AdminParticipation/index">Participants</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/Home/index">Voir le site</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php echo $content; ?>
    </div>

    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmation de suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <p id="deleteConfirmMessage" class="mb-0">Voulez-vous confirmer la suppression ?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger" id="deleteConfirmYesBtn">Oui</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
    <script src="<?php echo BASE_URL; ?>/public/js/validation.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
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

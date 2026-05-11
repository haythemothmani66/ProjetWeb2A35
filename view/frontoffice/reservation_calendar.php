<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controller/ReservationController.php';

$baseUrl = '/gestion_users';

// Protection : seuls etudiants/admin peuvent reserver
if (empty($_SESSION['user_id'])) {
    header('Location: /gestion_users/view/template/sign-in.php');
    exit;
}
$userRole = $_SESSION['user_role'] ?? '';
if (!in_array($userRole, ['etudiant', 'admin'], true)) {
    header('Location: /gestion_users/view/frontoffice/encadrants_list.php?error=role');
    exit;
}

$ctrl = new ReservationController();

$encadrantId = (int)($_GET['encadrant'] ?? 0);
if ($encadrantId <= 0) {
    header('Location: /gestion_users/view/frontoffice/encadrants_list.php');
    exit;
}

$encadrant = $ctrl->getEncadrantById($encadrantId);
if (!$encadrant) {
    header('Location: /gestion_users/view/frontoffice/encadrants_list.php?error=notfound');
    exit;
}

// Traitement POST (creer la reservation)
$flash = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = trim((string)($_POST['date'] ?? ''));
    $heureDebut = trim((string)($_POST['heure_debut'] ?? ''));
    $heureFin = trim((string)($_POST['heure_fin'] ?? ''));
    $matiere = trim((string)($_POST['matiere'] ?? ''));
    $sujet = trim((string)($_POST['sujet'] ?? ''));
    $mode = trim((string)($_POST['mode'] ?? 'en_ligne'));
    $notes = trim((string)($_POST['notes'] ?? ''));

    $result = $ctrl->createReservation(
        $encadrantId, $date, $heureDebut, $heureFin, $matiere, $sujet ?: null, $mode, $notes ?: null
    );
    $flash = $result;
}

// Generer les creneaux disponibles
$slots = $ctrl->generateAvailableSlots($encadrantId, 4);

// Photo encadrant
$photoPlaceholder = 'data:image/svg+xml;utf8,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200"><defs><linearGradient id="g" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#6366f1"/><stop offset="100%" stop-color="#8B5CF6"/></linearGradient></defs><rect width="200" height="200" fill="url(#g)"/><text x="100" y="135" font-size="100" fill="white" text-anchor="middle" font-weight="700">👤</text></svg>');
$photoSrc = !empty($encadrant['photo']) && $encadrant['photo'] !== 'default.png'
    ? $baseUrl . '/uploads/photos/' . htmlspecialchars($encadrant['photo'])
    : $photoPlaceholder;
$nomComplet = trim(($encadrant['prenom'] ?? '') . ' ' . ($encadrant['nom'] ?? ''));

// Convertir les slots en events FullCalendar
$calendarEvents = [];
foreach ($slots as $s) {
    $calendarEvents[] = [
        'title' => $s['available'] ? 'Disponible' : 'Reserve',
        'start' => $s['date'] . 'T' . $s['heure_debut'],
        'end'   => $s['date'] . 'T' . $s['heure_fin'],
        'backgroundColor' => $s['available'] ? '#10b981' : '#9ca3af',
        'borderColor'     => $s['available'] ? '#059669' : '#6b7280',
        'textColor'       => '#ffffff',
        'extendedProps'   => [
            'available'    => $s['available'],
            'date'         => $s['date'],
            'heure_debut'  => $s['heure_debut'],
            'heure_fin'    => $s['heure_fin'],
        ],
    ];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Reserver - <?= htmlspecialchars($nomComplet) ?> - EduMatch</title>
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/bootstrap/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/fonts/themify-icons.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/jquery-simple-mobilemenu.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/animate.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <style>
        .header-group { display: flex; flex-direction: row; align-items: center; gap: 10px; }
        .btn-backoffice { background: linear-gradient(135deg, #6366f1, #8B5CF6); color: white; padding: 10px 20px; border-radius: 2px; text-decoration: none; font-weight: 600; font-size: 13px; transition: all 0.3s ease; display: inline-block; }
        .btn-backoffice:hover { background: linear-gradient(135deg, #4f46e5, #7c3aed); color: white; text-decoration: none; }
        .user-dropdown { position: relative; display: flex; align-items: center; gap: 8px; cursor: pointer; }
        .user-dropdown .user-avatar { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; border: 2px solid #525fe1; }
        .user-dropdown .user-name { font-weight: 600; font-size: 14px; color: #0b104a; white-space: nowrap; }
        .user-dropdown .dropdown-caret { font-size: 10px; color: #6c757d; transition: transform 0.2s; }
        .user-dropdown:hover .dropdown-caret { transform: rotate(180deg); }
        .user-dropdown-menu { display: none; position: absolute; top: 100%; right: 0; background: white; border-radius: 10px; box-shadow: 0 8px 25px rgba(0,0,0,0.12); min-width: 200px; padding: 8px 0; z-index: 1000; margin-top: 8px; }
        .user-dropdown-menu.show { display: block; }
        .user-dropdown-menu a { display: flex; align-items: center; gap: 10px; padding: 10px 18px; color: #333; text-decoration: none; font-size: 14px; font-weight: 500; transition: background 0.2s; }
        .user-dropdown-menu a:hover { background: #f5f7fa; color: #525fe1; }
        .user-dropdown-menu a i { width: 18px; text-align: center; }
        .user-dropdown-menu hr { margin: 6px 0; border-color: #eee; }

        body { background: linear-gradient(135deg, #f8fafc 0%, #eef2f7 100%); min-height: 100vh; }
        .encadrant-info-card { background: white; border-radius: 1.25rem; padding: 2rem; box-shadow: 0 8px 30px rgba(15,23,42,0.08); margin-bottom: 2rem; }
        .encadrant-photo { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 4px solid #8B5CF6; }
        .legend-pill { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.4rem 0.9rem; border-radius: 50px; font-size: 0.85rem; font-weight: 600; }
        .legend-pill .dot { width: 12px; height: 12px; border-radius: 50%; }
        #calendar { background: white; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 4px 20px rgba(15,23,42,0.06); }
        .fc-event { cursor: pointer; padding: 4px 6px; font-weight: 600; }
        .fc-event:not(.slot-available) { cursor: not-allowed !important; opacity: 0.6; }

        .reserve-form { background: white; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 4px 20px rgba(15,23,42,0.06); position: sticky; top: 1rem; }
        .reserve-form .selected-slot { background: linear-gradient(135deg, #ddd6fe, #c7d2fe); padding: 1rem; border-radius: 0.75rem; margin-bottom: 1rem; }
        .reserve-form .selected-slot.empty { background: #f3f4f6; color: #6b7280; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../template/_navbar.php'; ?>

    <div class="container py-4">
        <!-- Back link -->
        <a href="<?= $baseUrl ?>/view/frontoffice/encadrants_list.php" class="btn btn-light mb-3">
            <i class="fas fa-arrow-left me-2"></i>Retour a la liste
        </a>

        <!-- Flash messages -->
        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['success'] ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?= $flash['success'] ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i>
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
        <?php endif; ?>

        <!-- Encadrant info -->
        <div class="encadrant-info-card">
            <div class="row align-items-center">
                <div class="col-auto">
                    <img src="<?= $photoSrc ?>" alt="<?= htmlspecialchars($nomComplet) ?>" class="encadrant-photo" onerror="this.onerror=null;this.src='<?= htmlspecialchars($photoPlaceholder) ?>';">
                </div>
                <div class="col">
                    <h2 class="fw-bold mb-1"><?= htmlspecialchars($nomComplet) ?></h2>
                    <?php if (!empty($encadrant['specialite'])): ?>
                        <p class="text-primary fw-semibold mb-2"><i class="fas fa-star me-1"></i><?= htmlspecialchars($encadrant['specialite']) ?></p>
                    <?php endif; ?>
                    <p class="text-muted mb-0">
                        <i class="fas fa-envelope me-1"></i><?= htmlspecialchars($encadrant['email']) ?>
                        <?php if (!empty($encadrant['etablissement_ecole'])): ?>
                            &nbsp;|&nbsp; <i class="fas fa-university me-1"></i><?= htmlspecialchars($encadrant['etablissement_ecole']) ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Calendar -->
            <div class="col-lg-8">
                <div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
                    <h4 class="fw-bold mb-0"><i class="fas fa-calendar-alt me-2 text-primary"></i>Creneaux disponibles</h4>
                    <span class="legend-pill" style="background: #d1fae5; color: #065f46;"><span class="dot" style="background: #10b981;"></span>Disponible</span>
                    <span class="legend-pill" style="background: #f3f4f6; color: #6b7280;"><span class="dot" style="background: #9ca3af;"></span>Reserve</span>
                </div>
                <p class="text-muted small mb-3">Cliquez sur un creneau vert pour le selectionner.</p>
                <div id="calendar"></div>
            </div>

            <!-- Reservation form -->
            <div class="col-lg-4">
                <div class="reserve-form">
                    <h5 class="fw-bold mb-3"><i class="fas fa-pen me-2 text-primary"></i>Confirmer la reservation</h5>

                    <div id="selectedSlotDisplay" class="selected-slot empty">
                        <i class="fas fa-info-circle me-2"></i>Aucun creneau selectionne
                    </div>

                    <form method="POST" id="reserveForm">
                        <input type="hidden" name="date" id="formDate">
                        <input type="hidden" name="heure_debut" id="formHeureDebut">
                        <input type="hidden" name="heure_fin" id="formHeureFin">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Matiere <span class="text-danger">*</span></label>
                            <input type="text" name="matiere" class="form-control" placeholder="Ex : Mathematiques, Programmation..." required minlength="2">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Sujet de la seance</label>
                            <textarea name="sujet" class="form-control" rows="3" placeholder="Decrivez brievement le sujet a aborder..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Mode <span class="text-danger">*</span></label>
                            <select name="mode" id="modeSelect" class="form-control" required>
                                <option value="en_ligne">En ligne (visio)</option>
                                <option value="presentiel" <?= empty($encadrant['adresse']) ? 'disabled' : '' ?>>
                                    Presentiel<?= empty($encadrant['adresse']) ? ' (indisponible - adresse manquante)' : '' ?>
                                </option>
                            </select>
                        </div>

                        <!-- Adresse de l'encadrant : visible seulement si mode=presentiel -->
                        <?php if (!empty($encadrant['adresse'])): ?>
                          <div class="mb-3" id="adresseEncadrantBlock" style="display: none; background: linear-gradient(135deg, #ddd6fe, #c7d2fe); border-radius: 0.75rem; padding: 1rem; border-left: 4px solid #6366f1;">
                              <div class="fw-bold mb-1" style="color: #4338ca;">
                                  <i class="fas fa-map-marker-alt me-2"></i>Adresse de la seance
                              </div>
                              <p class="mb-1" style="font-size: 0.92rem;"><?= htmlspecialchars($encadrant['adresse']) ?></p>
                              <small class="text-muted">Veuillez vous y rendre a l'heure de la seance. L'adresse sera rappelee dans l'email de confirmation.</small>
                          </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Notes complementaires</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Niveau, documents a apporter..."></textarea>
                        </div>

                        <button type="submit" class="btn w-100" id="submitBtn" style="background: linear-gradient(135deg, #6366f1, #8B5CF6); color: white; padding: 0.85rem; border-radius: 0.75rem; font-weight: 700; box-shadow: 0 4px 15px rgba(99,102,241,0.3);" disabled>
                            <i class="fas fa-paper-plane me-2"></i>Envoyer la reservation
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= $baseUrl ?>/assets/js/jquery-1.12.4.min.js"></script>
    <script src="<?= $baseUrl ?>/assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/fr.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var calEl = document.getElementById('calendar');
        if (!calEl) return;

        var events = <?= json_encode($calendarEvents, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

        var calendar = new FullCalendar.Calendar(calEl, {
            locale: 'fr',
            initialView: 'timeGridWeek',
            slotMinTime: '07:00:00',
            slotMaxTime: '20:00:00',
            allDaySlot: false,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'timeGridWeek,timeGridDay,listWeek'
            },
            events: events,
            eventClassNames: function(arg) {
                return arg.event.extendedProps.available ? ['slot-available'] : ['slot-reserved'];
            },
            eventClick: function(info) {
                if (!info.event.extendedProps.available) {
                    alert('Ce creneau est deja reserve.');
                    return;
                }
                var date = info.event.extendedProps.date;
                var hStart = info.event.extendedProps.heure_debut;
                var hEnd = info.event.extendedProps.heure_fin;

                document.getElementById('formDate').value = date;
                document.getElementById('formHeureDebut').value = hStart;
                document.getElementById('formHeureFin').value = hEnd;

                var display = document.getElementById('selectedSlotDisplay');
                display.classList.remove('empty');
                display.innerHTML = '<i class="fas fa-check-circle me-2 text-success"></i><strong>Creneau selectionne :</strong><br>' +
                                    '<i class="fas fa-calendar me-1"></i>' + date + '<br>' +
                                    '<i class="fas fa-clock me-1"></i>' + hStart.substring(0,5) + ' - ' + hEnd.substring(0,5);

                document.getElementById('submitBtn').disabled = false;
                document.getElementById('reserveForm').scrollIntoView({behavior: 'smooth', block: 'nearest'});
            }
        });
        calendar.render();

        // Form validation
        document.getElementById('reserveForm').addEventListener('submit', function(e) {
            var date = document.getElementById('formDate').value;
            if (!date) {
                e.preventDefault();
                alert('Veuillez d\'abord selectionner un creneau dans le calendrier.');
                return false;
            }
        });

        // Toggle affichage adresse selon mode (presentiel)
        var modeSelect = document.getElementById('modeSelect');
        var adresseBlock = document.getElementById('adresseEncadrantBlock');
        function toggleAdresseBlock() {
            if (!adresseBlock || !modeSelect) return;
            adresseBlock.style.display = (modeSelect.value === 'presentiel') ? 'block' : 'none';
        }
        if (modeSelect) {
            modeSelect.addEventListener('change', toggleAdresseBlock);
            toggleAdresseBlock();
        }
    });
    </script>
</body>
</html>

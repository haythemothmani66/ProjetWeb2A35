<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . '/../../../../../config/database.php';

// --- Protection admin : seul un admin peut acceder au backoffice ---
if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: /gestion_users/view/template/sign-in.php');
    exit;
}

// Configuration de la base de données
$conn = getDBConnection();





// URLs dynamiques
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$pageMarker = '/view/backoffice/src/pages/backoffice/';
$appBaseUrl = '';
$markerPos = strpos($scriptName, $pageMarker);

if ($markerPos !== false) {
    $appBaseUrl = substr($scriptName, 0, $markerPos);
} else {
    $appBaseUrl = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
    if ($appBaseUrl === '.' || $appBaseUrl === '/') {
        $appBaseUrl = '';
    }
}

$backofficeSrcBaseUrl = $appBaseUrl . '/view/backoffice/src';
$backofficePageBaseUrl = $backofficeSrcBaseUrl . '/pages/backoffice';
$controllerUrl = $appBaseUrl . '/controller/devoirs.php';
$submitScriptVersion = @filemtime(__DIR__ . '/../../assets/js/backoffice-submit.js');
if ($submitScriptVersion === false) {
    $submitScriptVersion = time();
}

// Récupérer les devoirs pour le select
$devoirs = $conn->query("SELECT * FROM devoirs ORDER BY id_devoir DESC")->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les stats
$totalDevoirs = $conn->query("SELECT COUNT(*) FROM devoirs")->fetchColumn();
$totalCorrections = $conn->query("SELECT COUNT(*) FROM correction")->fetchColumn();

// Récupérer les paramètres d'édition (GET ou POST)
$editType = $_POST['edit'] ?? $_GET['edit'] ?? '';
$editType = in_array($editType, ['devoir', 'correction'], true) ? $editType : '';

$devoirEditId = (int)($_POST['id_devoir'] ?? $_GET['id'] ?? 0);
$correctionEditId = (int)($_POST['id_correction'] ?? $_POST['id'] ?? $_GET['id'] ?? 0);
$editId = $editType === 'devoir' ? $devoirEditId : $correctionEditId;

$requestedDevoirTitle = trim((string)($_POST['devoir_titre'] ?? $_GET['devoir_titre'] ?? ''));
$requestedDevoirId = $_POST['add_correction_for'] ?? $_GET['add_correction_for'] ?? '';
$selectedCorrectionDevoirId = $requestedDevoirId === '' ? '' : (string)((int)$requestedDevoirId);

$editDevoir = null;
$editCorrection = null;

// Charger les données à modifier
if ($editType === 'devoir' && $editId > 0) {
    $stmt = $conn->prepare("SELECT * FROM devoirs WHERE id_devoir = ?");
    $stmt->execute([$editId]);
    $editDevoir = $stmt->fetch(PDO::FETCH_ASSOC);
} elseif ($editType === 'correction' && $editId > 0) {
    $stmt = $conn->prepare("
        SELECT c.*, d.titre AS devoir_titre
        FROM correction c
        LEFT JOIN devoirs d ON d.id_devoir = c.id_devoir
        WHERE c.id_correction = ?
    ");
    $stmt->execute([$editId]);
    $editCorrection = $stmt->fetch(PDO::FETCH_ASSOC);

    if (is_array($editCorrection)) {
        $selectedCorrectionDevoirId = (string)($editCorrection['id_devoir'] ?? '');
    }
}

if ($selectedCorrectionDevoirId === '' && $requestedDevoirTitle !== '') {
    foreach ($devoirs as $devoirOption) {
        $devoirOptionTitle = trim((string)($devoirOption['titre'] ?? ''));
        if ($devoirOptionTitle === $requestedDevoirTitle) {
            $selectedCorrectionDevoirId = (string)($devoirOption['id_devoir'] ?? '');
            break;
        }
    }
}



// Modifier l'action du formulaire selon le mode (ajout ou modification)
$devoirFormAction = $editDevoir ? "{$controllerUrl}?action=updatedevoir" : "{$controllerUrl}?action=submit";
$correctionFormAction = $editCorrection ? "{$controllerUrl}?action=updatecorrection" : "{$controllerUrl}?action=correct";
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta content="EduMatch" name="author" />
    <title>EduMatch Submit Devoir & Correction</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&display=swap" />
    <link rel="stylesheet" href="../../assets/css/theme.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simplebar@6.2.5/dist/simplebar.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.47.0/tabler-icons.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <script src="../../assets/js/vendors/color-modes.js"></script>
    <script>
        if (localStorage.getItem("sidebarExpanded") === "false") {
            document.documentElement.classList.add("collapsed");
            document.documentElement.classList.remove("expanded");
        } else {
            document.documentElement.classList.remove("collapsed");
            document.documentElement.classList.add("expanded");
        }
    </script>
</head>

<body>
    <div>
        <div data-include-html="../../partials/sidebar-collapse.html"></div>

        <div id="content" class="position-relative h-100">
            <div data-include-html="../../partials/topbar-second.html"></div>

            <div class="custom-container">
                <div class="row mb-6 g-6 align-items-center">
                    <div class="col-lg-8 col-12">
                        <p class="text-uppercase text-secondary small mb-2">EduMatch BackOffice</p>
                        <h1 class="mb-2">Submit Devoir and Correction</h1>
                        <p class="mb-0 text-secondary">Admin can submit new devoirs and corrections directly from the dashboard.</p>
                    </div>
                    <div class="col-lg-4 col-12 text-lg-end">
                        <a href="./feed_back.html" class="btn btn-dark">
                            <i class="ti ti-rss me-1"></i>
                            Open Feed Management
                        </a>
                    </div>
                </div>

                <!-- STATS CARDS -->
                <div class="row row-cols-1 row-cols-md-2 mb-6 g-6">
                    <div class="col">
                        <div class="card card-lg h-100">
                            <div class="card-body">
                                <div class="text-secondary mb-2">Total Devoirs</div>
                                <div id="statTotalDevoirs" class="fs-2 fw-bold"><?= $totalDevoirs ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card card-lg h-100">
                            <div class="card-body">
                                <div class="text-secondary mb-2">Total Corrections</div>
                                <div id="statTotalCorrections" class="fs-2 fw-bold text-success"><?= $totalCorrections ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-6 mb-6">
                    <!-- FORMULAIRE DEVOIR -->
<div class="col-xl-6 col-12">
    <div class="card card-lg">
        <div class="card-header border-bottom-0">
            <h5 class="mb-0">
                <i class="ti ti-file-upload me-2"></i>
                <?= $editDevoir ? 'Modifier le Devoir' : 'Soumettre un Devoir' ?>
            </h5>
        </div>
        <div class="card-body">
            <form id="devoirForm" action="<?= $devoirFormAction ?>" method="POST" enctype="multipart/form-data" novalidate>
                
                <?php if ($editDevoir): ?>
                    <input type="hidden" name="id_devoir" value="<?= $editDevoir['id_devoir'] ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="titre" class="form-label">Titre</label>
                    <input type="text" id="titre" name="titre" class="form-control" 
                           value="<?= htmlspecialchars($editDevoir['titre'] ?? '') ?>"
                           />
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="4" 
                             ><?= htmlspecialchars($editDevoir['description'] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="file1" class="form-label">Fichier <?= $editDevoir ? '(laisser vide pour conserver l\'existant)' : '' ?></label>
                    <input type="file" id="file1" name="file1" class="form-control" 
                           data-required="<?= $editDevoir ? '0' : '1' ?>"
                           accept=".py,.js,.java,.cpp,.c,.png,.jpg,.jpeg" 
                            />
                    <?php if ($editDevoir && !empty($editDevoir['fichier'])): ?>
                        <small class="text-muted">Fichier actuel : <?= htmlspecialchars($editDevoir['fichier']) ?></small>
                    <?php endif; ?>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="date_soumission" class="form-label">Date de soumission</label>
                        <input type="date" id="date_soumission" name="date_soumission" class="form-control" 
                               value="<?= htmlspecialchars($editDevoir['date_soumission'] ?? '') ?>"
                               />
                    </div>
                    <div class="col-md-6">
                        <label for="niveau_difficulte" class="form-label">Niveau</label>
                        <select id="niveau_difficulte" name="niveau_difficulte" class="form-select">
                            <option value="">Select</option>
                            <option value="facile" <?= ($editDevoir['niveau_difficulte'] ?? '') == 'facile' ? 'selected' : '' ?>>Facile</option>
                            <option value="moyen" <?= ($editDevoir['niveau_difficulte'] ?? '') == 'moyen' ? 'selected' : '' ?>>Moyen</option>
                            <option value="difficile" <?= ($editDevoir['niveau_difficulte'] ?? '') == 'difficile' ? 'selected' : '' ?>>Difficile</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mt-0">
                    <div class="col-md-6">
                        <label for="type_erreur_predominant" class="form-label">Type erreur</label>
                        <select id="type_erreur_predominant" name="type_erreur_predominant" class="form-select">
                            <option value="">Select</option>
                            <option value="logique" <?= ($editDevoir['type_erreur_predominant'] ?? '') == 'logique' ? 'selected' : '' ?>>Logique</option>
                            <option value="syntaxe" <?= ($editDevoir['type_erreur_predominant'] ?? '') == 'syntaxe' ? 'selected' : '' ?>>Syntaxe</option>
                            <option value="comprehension" <?= ($editDevoir['type_erreur_predominant'] ?? '') == 'comprehension' ? 'selected' : '' ?>>Compréhension</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="urgence" class="form-label">Urgence</label>
                        <select id="urgence" name="urgence" class="form-select">
                            <option value="">Select</option>
                            <option value="faible" <?= ($editDevoir['urgence'] ?? '') == 'faible' ? 'selected' : '' ?>>Faible</option>
                            <option value="moyenne" <?= ($editDevoir['urgence'] ?? '') == 'moyenne' ? 'selected' : '' ?>>Moyenne</option>
                            <option value="urgente" <?= ($editDevoir['urgence'] ?? '') == 'urgente' ? 'selected' : '' ?>>Urgente</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mt-0">
                    <div class="col-md-6">
                        <label for="temps_estime_resolution" class="form-label">Temps estimé (min)</label>
                        <input type="number" id="temps_estime_resolution" name="temps_estime_resolution" class="form-control" 
                               value="<?= htmlspecialchars($editDevoir['temps_estime_resolution'] ?? '') ?>"
                               />
                    </div>
                    <div class="col-md-6">
                        <label for="progression_eleve" class="form-label">Progression (%)</label>
                        <input type="number" id="progression_eleve" name="progression_eleve" class="form-control" 
                               value="<?= htmlspecialchars($editDevoir['progression_eleve'] ?? '') ?>"
                               />
                    </div>
                </div>

                <div class="mb-3 mt-3">
                    <label for="mots_cles" class="form-label">Mots clés</label>
                    <input type="text" id="mots_cles" name="mots_cles" class="form-control" 
                           value="<?= htmlspecialchars($editDevoir['mots_cles'] ?? '') ?>"
                           placeholder="sql, joins, recursion" />
                </div>

                <button type="submit" class="btn btn-dark w-100" id="submitDevoirBtn">
                    <i class="ti ti-send me-1"></i>
                    <?= $editDevoir ? 'Mettre à jour le Devoir' : 'Publier le Devoir' ?>
                </button>
            </form>
        </div>
    </div>
</div>

                    <!-- FORMULAIRE CORRECTION -->
<div class="col-xl-6 col-12">
    <div class="card card-lg">
        <div class="card-header border-bottom-0">
            <h5 class="mb-0">
                <i class="ti ti-checkup-list me-2"></i>
                <?= $editCorrection ? 'Modifier la Correction' : 'Soumettre une Correction' ?>
            </h5>
        </div>
        <div class="card-body">
            <form id="correctionForm" action="<?= $correctionFormAction ?>" method="POST" enctype="multipart/form-data" novalidate>
                
                <?php if ($editCorrection): ?>
                    <input type="hidden" name="id_correction" value="<?= $editCorrection['id_correction'] ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="id_devoir" class="form-label">Devoir concerné</label>
                    <select id="id_devoir" name="id_devoir" class="form-select" data-selected-id="<?= htmlspecialchars($selectedCorrectionDevoirId, ENT_QUOTES) ?>">
                        <option value="">-- Sélectionnez un devoir --</option>
                        <?php foreach ($devoirs as $d): ?>
                            <option value="<?= $d['id_devoir'] ?>" 
                                <?= ($selectedCorrectionDevoirId !== '' && $selectedCorrectionDevoirId === (string)$d['id_devoir']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($d['titre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="commentaire" class="form-label">Commentaire</label>
                    <textarea id="commentaire" name="commentaire" class="form-control" rows="4" 
                             ><?= htmlspecialchars($editCorrection['commentaire'] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="file2" class="form-label">Fichier corrigé <?= $editCorrection ? '(laisser vide pour conserver l\'existant)' : '' ?></label>
                    <input type="file" id="file2" name="file2" class="form-control" 
                           data-required="<?= $editCorrection ? '0' : '1' ?>"
                           accept=".py,.js,.java,.cpp,.c,.png,.jpg,.jpeg" 
                            />
                    <?php if ($editCorrection && !empty($editCorrection['fichier_corrige'])): ?>
                        <small class="text-muted">Fichier actuel : <?= htmlspecialchars($editCorrection['fichier_corrige']) ?></small>
                    <?php endif; ?>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="date_correction" class="form-label">Date correction</label>
                        <input type="date" id="date_correction" name="date_correction" class="form-control" 
                               value="<?= htmlspecialchars($editCorrection['date_correction'] ?? '') ?>"
                               />
                    </div>
                    <div class="col-md-6">
                        <label for="type_feedback" class="form-label">Type feedback</label>
                        <select id="type_feedback" name="type_feedback" class="form-select">
                            <option value="">Select</option>
                            <option value="explicatif" <?= ($editCorrection['type_feedback'] ?? '') == 'explicatif' ? 'selected' : '' ?>>Explicatif</option>
                            <option value="direct" <?= ($editCorrection['type_feedback'] ?? '') == 'direct' ? 'selected' : '' ?>>Direct</option>
                            <option value="guide" <?= ($editCorrection['type_feedback'] ?? '') == 'guide' ? 'selected' : '' ?>>Guidé</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mt-0">
                    <div class="col-md-6">
                        <label for="note_estimee" class="form-label">Note /20</label>
                        <input type="number" id="note_estimee" name="note_estimee" class="form-control" 
                               value="<?= htmlspecialchars($editCorrection['note_estimee'] ?? '') ?>"
                               />
                    </div>
                    <div class="col-md-6">
                        <label for="nombre_iterations" class="form-label">Iterations</label>
                        <input type="number" id="nombre_iterations" name="nombre_iterations" class="form-control" 
                               value="<?= htmlspecialchars($editCorrection['nombre_iterations'] ?? '') ?>"
                               />
                    </div>
                </div>

                <div class="mb-3 mt-3">
                    <label for="competences_evaluees" class="form-label">Compétences évaluées</label>
                    <input type="text" id="competences_evaluees" name="competences_evaluees" class="form-control" 
                           value="<?= htmlspecialchars($editCorrection['competences_evaluees'] ?? '') ?>"
                           />
                </div>

                <div class="mb-3">
                    <label for="suggestions_personnalisees" class="form-label">Suggestions personnalisées</label>
                    <textarea id="suggestions_personnalisees" name="suggestions_personnalisees" class="form-control" rows="3"><?= htmlspecialchars($editCorrection['suggestions_personnalisees'] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="ressources_recommandees" class="form-label">Ressources recommandées</label>
                    <input type="text" id="ressources_recommandees" name="ressources_recommandees" class="form-control" 
                           value="<?= htmlspecialchars($editCorrection['ressources_recommandees'] ?? '') ?>"
                           placeholder="https://... , https://..." />
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="rapidite_correction" class="form-label">Rapidité (min)</label>
                        <input type="number" id="rapidite_correction" name="rapidite_correction" class="form-control" 
                               value="<?= htmlspecialchars($editCorrection['rapidite_correction'] ?? '') ?>"
                               />
                    </div>
                    <div class="col-md-6">
                        <label for="ton_feedback" class="form-label">Ton feedback</label>
                        <select id="ton_feedback" name="ton_feedback" class="form-select">
                            <option value="">Select</option>
                            <option value="encourageant" <?= ($editCorrection['ton_feedback'] ?? '') == 'encourageant' ? 'selected' : '' ?>>Encourageant</option>
                            <option value="strict" <?= ($editCorrection['ton_feedback'] ?? '') == 'strict' ? 'selected' : '' ?>>Strict</option>
                            <option value="neutre" <?= ($editCorrection['ton_feedback'] ?? '') == 'neutre' ? 'selected' : '' ?>>Neutre</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-success w-100 mt-3" id="submitCorrectionBtn">
                    <i class="ti ti-check me-1"></i>
                    <?= $editCorrection ? 'Mettre à jour la Correction' : 'Publier la Correction' ?>
                </button>
            </form>
        </div>
    </div>
</div>
    <div class="toast-container position-fixed top-0 end-0 p-3" id="toastContainer"></div>

    <script>
        async function loadHtmlIncludes() {
            const includeTargets = document.querySelectorAll("[data-include-html]");
            for (const target of includeTargets) {
                const includePath = target.getAttribute("data-include-html");
                const response = await fetch(includePath);
                if (!response.ok) {
                    target.outerHTML = "";
                    continue;
                }
                let html = await response.text();
                html = html.replaceAll("@@webRoot/", "../../");
                html = html.replaceAll("@@webRoot", "../..");
                target.outerHTML = html;
            }
        }

        function loadScript(src) {
            return new Promise((resolve, reject) => {
                const script = document.createElement("script");
                script.src = src;
                script.onload = resolve;
                script.onerror = () => reject(new Error("Failed to load " + src));
                document.body.appendChild(script);
            });
        }

        async function bootSubmitPage() {
            await loadHtmlIncludes();

            const scripts = [
                "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js",
                "https://cdn.jsdelivr.net/npm/simplebar@6.2.5/dist/simplebar.min.js",
                "../../assets/js/main.js",
                "../../assets/js/vendors/sidebarnav.js",
                "../../assets/js/backoffice-submit.js?v=<?= (int)$submitScriptVersion ?>",
            ];

            for (const scriptPath of scripts) {
                try {
                    await loadScript(scriptPath);
                } catch (error) {
                    console.error(error);
                }
            }
        }

        bootSubmitPage();
    </script>
    <script>
    (function () {
        const select = document.getElementById('id_devoir');
        if (!select) {
            return;
        }

        const params = new URLSearchParams(window.location.search);
        const urlDevoirId = String(params.get('add_correction_for') || '').trim();
        const dataSelectedId = String(select.dataset.selectedId || '').trim();
        const targetId = dataSelectedId || urlDevoirId;

        function normalizeOptionLabels() {
            const options = Array.from(select.options || []);
            options.forEach((option, index) => {
                if (index === 0) {
                    return;
                }
                option.textContent = String(option.textContent || '')
                    .replace(/^#\d+\s*-\s*/, '')
                    .trim();
            });
        }

        function applySelection() {
            if (targetId === '') {
                return;
            }

            const hasOption = Array.from(select.options || []).some(
                (option) => String(option.value) === targetId,
            );

            if (!hasOption) {
                return;
            }

            select.value = targetId;
            select.dispatchEvent(new Event('change', { bubbles: true }));
            select.dispatchEvent(new Event('input', { bubbles: true }));
        }

        const syncSelectState = () => {
            normalizeOptionLabels();
            applySelection();
        };

        syncSelectState();

        const observer = new MutationObserver(() => {
            syncSelectState();
        });

        observer.observe(select, { childList: true });

        setTimeout(() => {
            observer.disconnect();
        }, 8000);
    })();
    </script>
</body>
</html>


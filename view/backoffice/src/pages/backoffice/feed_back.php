<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . '/../../../../../config/database.php';

// --- Protection admin : seul un admin peut acceder au backoffice ---
if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: /gestion_users/view/template/sign-in.php');
    exit;
}

// URLs dynamiques (calcul du base path du projet)
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$pageMarker = '/view/backoffice/src/pages/backoffice/';
$markerPos = strpos($scriptName, $pageMarker);
$appBaseUrl = '';
if ($markerPos !== false) {
    $appBaseUrl = substr($scriptName, 0, $markerPos);
} else {
    $appBaseUrl = '/gestion_users';
}
$backofficeSrcBaseUrl = $appBaseUrl . '/view/backoffice/src';
$backofficePageBaseUrl = $backofficeSrcBaseUrl . '/pages/backoffice';
$controllerUrl = $appBaseUrl . '/controller/devoirs.php';
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta content="EduMatch" name="author" />
    <title>EduMatch Feed Management</title>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&display=swap" />
    <link rel="stylesheet" href="<?= htmlspecialchars($backofficeSrcBaseUrl) ?>/assets/css/theme.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simplebar@6.2.5/dist/simplebar.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.47.0/tabler-icons.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <script src="<?= htmlspecialchars($backofficeSrcBaseUrl) ?>/assets/js/vendors/color-modes.js"></script>
    <script>
      if (localStorage.getItem("sidebarExpanded") === "false") {
        document.documentElement.classList.add("collapsed");
        document.documentElement.classList.remove("expanded");
      } else {
        document.documentElement.classList.remove("collapsed");
        document.documentElement.classList.add("expanded");
      }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
      canvas { max-height: 300px; width: 100%; }
      .card-header { border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 0.75rem; }
      .form-select.w-auto { width: auto; min-width: 120px; }

      @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
      }
      .card { animation: fadeInUp 0.4s ease-out; }

      .form-select { cursor: pointer; transition: all 0.2s ease; border: 1px solid #e2e8f0; background-color: white; }
      .form-select:hover { border-color: #10b981; }
      .form-select:focus { border-color: #10b981; box-shadow: 0 0 0 0.2rem rgba(16, 185, 129, 0.25); }

      .badge { font-weight: 500; padding: 0.35rem 0.75rem; }

      .btn-pdf { background: linear-gradient(135deg, #dc2626, #b91c1c); color: white; border: none; border-radius: 0.75rem; padding: 0.75rem 1.5rem; font-weight: 700; font-size: 0.95rem; text-decoration: none; transition: all 0.3s; display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer; }
      .btn-pdf:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(220,38,38,0.3); color: white; background: linear-gradient(135deg, #b91c1c, #991b1b); }

      .pdf-loader { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); display: flex; align-items: center; justify-content: center; z-index: 9999; color: white; font-weight: bold; font-size: 1.2rem; flex-direction: column; gap: 1rem; }
      .pdf-loader .spinner { width: 50px; height: 50px; border: 5px solid rgba(255,255,255,0.3); border-top: 5px solid white; border-radius: 50%; animation: spin 1s linear infinite; }
      @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

      @media print {
        .btn-delete, .btn-edit, .btn-add-correction, .btn-pdf,
        .btn-white, .btn-dark, .delete-devoir, .delete-correction,
        .edit-devoir, .edit-correction, #refreshFeedBtn, #clearFiltersBtn {
          display: none !important;
        }
        .devoir-card, .correction-card { break-inside: avoid; page-break-inside: avoid; }
      }

      .btn-add-correction { background: #10b981; color: white; border: none; border-radius: 0.5rem; padding: 0.3rem 0.8rem; font-size: 0.75rem; cursor: pointer; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 0.3rem; text-decoration: none; }
      .btn-add-correction:hover { background: #059669; transform: scale(1.05); color: white; text-decoration: none; }

      .devoir-card { margin-bottom: 2rem; border: 1px solid #e0e0e0; border-radius: 0.5rem; overflow: hidden; }
      .devoir-header { background: #f8f9fa; padding: 1rem; border-bottom: 1px solid #e0e0e0; }
      .devoir-body { padding: 1rem; }
      .corrections-container { margin-left: 2rem; padding-left: 1rem; border-left: 2px solid #10b981; }
      .correction-card { background: #f9f9f9; margin-bottom: 1rem; border-radius: 0.5rem; padding: 1rem; border: 1px solid #e0e0e0; }

      .urgence-faible { background: #d1fae5; color: #065f46; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; }
      .urgence-moyenne { background: #fed7aa; color: #92400e; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; }
      .urgence-urgente { background: #fee2e2; color: #991b1b; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; }

      .note-badge { background: linear-gradient(135deg, #f59e0b, #ef4444); color: white; border-radius: 999px; padding: 0.25rem 0.75rem; font-weight: bold; font-size: 0.8rem; }

      .tag { background: #e0e7ff; color: #4338ca; padding: 0.2rem 0.6rem; border-radius: 0.25rem; font-size: 0.7rem; display: inline-block; margin: 0.2rem; }

      .detail-chip { background: #f3f4f6; padding: 0.5rem; border-radius: 0.5rem; margin-bottom: 0.5rem; }

      .search-bar { margin-bottom: 2rem; }
    </style>
  </head>

  <body>
    <div>
      <?php include __DIR__ . '/../../../src/partials_php/sidebar.php'; ?>

      <div id="content" class="position-relative h-100">
        <?php include __DIR__ . '/../../../src/partials_php/topbar.php'; ?>

        <div class="custom-container">
          <div class="row mb-6 g-6 align-items-center">
            <div class="col-lg-8 col-12">
              <p class="text-uppercase text-secondary small mb-2">EduMatch BackOffice</p>
              <h1 class="mb-2">Devoir and Correction Feed</h1>
              <p class="mb-0 text-secondary">View, edit, and delete devoirs and corrections from one dashboard page.</p>
            </div>
            <div class="col-lg-4 col-12 text-lg-end d-flex justify-content-lg-end gap-2">
              <button type="button" id="exportPDFBtn" class="btn-pdf">
                <i class="ti ti-file-pdf"></i> Export PDF
              </button>
              <a href="<?= htmlspecialchars($backofficePageBaseUrl) ?>/submit_back.php" class="btn btn-dark">
                <i class="ti ti-plus me-1"></i> Add Submission
              </a>
              <button type="button" id="refreshFeedBtn" class="btn btn-white">Refresh</button>
            </div>
          </div>

          <!-- Stats cards -->
          <div class="row row-cols-1 row-cols-md-2 mb-6 g-6">
            <div class="col">
              <div class="card card-lg h-100">
                <div class="card-body">
                  <div class="text-secondary mb-2">Total Devoirs</div>
                  <div id="statTotalDevoirs" class="fs-2 fw-bold">0</div>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="card card-lg h-100">
                <div class="card-body">
                  <div class="text-secondary mb-2">Total Corrections</div>
                  <div id="statTotalCorrections" class="fs-2 fw-bold text-success">0</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Charts -->
          <div class="row row-cols-1 row-cols-xl-2 mb-6 g-6">
            <div class="col">
              <div class="card card-lg h-100">
                <div class="card-header bg-transparent">
                  <h5 class="mb-0"><i class="ti ti-calendar-stats me-2 text-primary"></i> Devoirs par mois</h5>
                  <p class="text-secondary small mb-0">Repartition mensuelle des soumissions</p>
                </div>
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <select id="chartPeriodSelect" class="form-select w-auto">
                      <option value="month">Par mois</option>
                      <option value="week">Par semaine</option>
                    </select>
                    <span class="badge bg-primary" id="chartTotalLabel">Total: 0 devoirs</span>
                  </div>
                  <canvas id="devoirsPieChart" style="max-height: 300px; width: 100%;"></canvas>
                </div>
              </div>
            </div>

            <div class="col">
              <div class="card card-lg h-100">
                <div class="card-header bg-transparent">
                  <h5 class="mb-0"><i class="ti ti-chart-line me-2 text-success"></i> Activite des eleves</h5>
                  <p class="text-secondary small mb-0">Evolution du nombre de devoirs soumis</p>
                </div>
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <select id="activityPeriodSelect" class="form-select w-auto">
                      <option value="6months">6 derniers mois</option>
                      <option value="12months">12 derniers mois</option>
                      <option value="all">Toute la periode</option>
                    </select>
                    <span class="badge bg-success" id="activityTotalLabel">Total: 0 devoirs</span>
                  </div>
                  <canvas id="activityLineChart" style="max-height: 300px; width: 100%;"></canvas>
                </div>
              </div>
            </div>
          </div>

          <!-- Search Bar -->
          <div class="card card-lg mb-6 search-bar">
            <div class="card-body">
              <div class="row g-3 align-items-end">
                <div class="col-lg-6 col-12">
                  <label for="searchInput" class="form-label">Search</label>
                  <input id="searchInput" type="text" class="form-control" placeholder="Search by title, keywords, commentaire..." />
                </div>
                <div class="col-lg-4 col-12">
                  <label for="sortBy" class="form-label">Sort by</label>
                  <select id="sortBy" class="form-select">
                    <option value="date_desc">Recent d'abord</option>
                    <option value="date_asc">Ancien d'abord</option>
                    <option value="difficulty_asc">Niveau croissant</option>
                    <option value="difficulty_desc">Niveau decroissant</option>
                    <option value="urgence">Par urgence</option>
                  </select>
                </div>
                <div class="col-lg-2 col-12 d-grid">
                  <button type="button" id="clearFiltersBtn" class="btn btn-white">Reset</button>
                </div>
              </div>
            </div>
          </div>

          <!-- Feed Container -->
          <div id="feedContainer">
            <div class="text-center py-5">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <p class="mt-2">Loading devoirs and corrections...</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="detailsModalLabel">Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="detailsModalBody"></div>
        </div>
      </div>
    </div>

    <div class="toast-container position-fixed top-0 end-0 p-3" id="toastContainer"></div>

    <script>
      // URL du controller (passee depuis PHP)
      const CONTROLLER_URL = <?= json_encode($controllerUrl) ?>;
      const SUBMIT_URL = <?= json_encode($backofficePageBaseUrl . '/submit_back.php') ?>;

      function getControllerUrl() {
        return CONTROLLER_URL;
      }

      function escapeHtml(value) {
        if (value === null || value === undefined) return "";
        return String(value)
          .replaceAll("&", "&amp;")
          .replaceAll("<", "&lt;")
          .replaceAll(">", "&gt;")
          .replaceAll('"', "&quot;")
          .replaceAll("'", "&#39;");
      }

      function showToast(message, variant) {
        const toastContainer = document.getElementById("toastContainer");
        const color = variant === "danger" ? "danger" : "success";
        const toastId = `toast-${Date.now()}`;
        const toastMarkup = `
          <div id="${toastId}" class="toast align-items-center text-bg-${color} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
              <div class="toast-body">${escapeHtml(message)}</div>
              <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
          </div>
        `;
        toastContainer.insertAdjacentHTML("beforeend", toastMarkup);
        const toastElement = document.getElementById(toastId);
        const toast = bootstrap.Toast.getOrCreateInstance(toastElement, { delay: 2800 });
        toast.show();
        toastElement.addEventListener("hidden.bs.toast", () => toastElement.remove());
      }

      async function fetchJson(url) {
        const response = await fetch(url, {
          headers: { "X-Requested-With": "XMLHttpRequest" }
        });
        const raw = await response.text();
        let payload = null;
        try {
          payload = JSON.parse(raw);
        } catch (error) {
          const jsonStart = raw.indexOf("{");
          const jsonEnd = raw.lastIndexOf("}");
          if (jsonStart !== -1 && jsonEnd !== -1 && jsonEnd > jsonStart) {
            try {
              payload = JSON.parse(raw.slice(jsonStart, jsonEnd + 1));
            } catch (innerError) {
              payload = null;
            }
          }
        }
        if (!payload) {
          throw new Error("Invalid JSON response from server.");
        }
        if (!response.ok || payload.success === false) {
          throw new Error(payload.message || "Request failed.");
        }
        return payload;
      }

      function urgenceBadge(urgence) {
        const normalized = String(urgence || "").toLowerCase();
        if (normalized === "urgente") return '<span class="urgence-urgente">Urgente</span>';
        if (normalized === "moyenne") return '<span class="urgence-moyenne">Moyenne</span>';
        return '<span class="urgence-faible">Faible</span>';
      }

      function sortDevoirs(devoirs, sortType) {
        const sortedDevoirs = [...devoirs];
        const urgenceOrder = { 'urgente': 3, 'moyenne': 2, 'faible': 1 };
        const difficultyOrder = { 'debutant': 1, 'facile': 2, 'moyen': 3, 'difficile': 4, 'expert': 5 };

        switch (sortType) {
          case 'date_desc': return sortedDevoirs.sort((a, b) => new Date(b.date_soumission) - new Date(a.date_soumission));
          case 'date_asc': return sortedDevoirs.sort((a, b) => new Date(a.date_soumission) - new Date(b.date_soumission));
          case 'difficulty_asc': return sortedDevoirs.sort((a, b) => (difficultyOrder[a.niveau_difficulte?.toLowerCase()] || 3) - (difficultyOrder[b.niveau_difficulte?.toLowerCase()] || 3));
          case 'difficulty_desc': return sortedDevoirs.sort((a, b) => (difficultyOrder[b.niveau_difficulte?.toLowerCase()] || 3) - (difficultyOrder[a.niveau_difficulte?.toLowerCase()] || 3));
          case 'urgence': return sortedDevoirs.sort((a, b) => (urgenceOrder[b.urgence?.toLowerCase()] || 0) - (urgenceOrder[a.urgence?.toLowerCase()] || 0));
          default: return sortedDevoirs;
        }
      }

      function renderFeed(devoirs, corrections) {
        const container = document.getElementById("feedContainer");
        const sortType = document.getElementById("sortBy")?.value || "date_desc";
        const sortedDevoirs = sortDevoirs(devoirs, sortType);

        const correctionsByDevoir = {};
        corrections.forEach((corr) => {
          if (!correctionsByDevoir[corr.id_devoir]) correctionsByDevoir[corr.id_devoir] = [];
          correctionsByDevoir[corr.id_devoir].push(corr);
        });

        if (!sortedDevoirs || sortedDevoirs.length === 0) {
          container.innerHTML = `
            <div class="text-center py-5">
              <i class="ti ti-inbox" style="font-size: 3rem;"></i>
              <h4 class="mt-3">No devoirs found</h4>
              <p class="text-secondary">Click "Add Submission" to create your first devoir.</p>
            </div>
          `;
          return;
        }

        let html = "";
        sortedDevoirs.forEach((devoir) => {
          const devoirCorrections = correctionsByDevoir[devoir.id_devoir] || [];
          html += `
            <div class="devoir-card" data-devoir-id="${devoir.id_devoir}" data-devoir-title="${escapeHtml(devoir.titre).toLowerCase()}" data-devoir-keywords="${escapeHtml(devoir.mots_cles || "").toLowerCase()}">
              <div class="devoir-header">
                <div class="d-flex justify-content-between align-items-start flex-wrap">
                  <div>
                    <h4 class="mb-1">${escapeHtml(devoir.titre)}</h4>
                    <div class="d-flex gap-2 flex-wrap mt-2">
                      ${urgenceBadge(devoir.urgence)}
                      <span class="badge bg-secondary">${escapeHtml(devoir.niveau_difficulte)}</span>
                      <span class="text-secondary"><i class="ti ti-calendar"></i> ${escapeHtml(devoir.date_soumission)}</span>
                    </div>
                  </div>
                  <div class="d-flex gap-2">
                    <a href="${SUBMIT_URL}?add_correction_for=${devoir.id_devoir}&title=${encodeURIComponent(devoir.titre)}" class="btn-add-correction">
                      <i class="ti ti-plus"></i> Ajouter correction
                    </a>
                    <button class="btn btn-sm btn-white edit-devoir" data-id="${devoir.id_devoir}">
                      <i class="ti ti-edit"></i> Edit
                    </button>
                    <button class="btn btn-sm btn-danger delete-devoir" data-id="${devoir.id_devoir}">
                      <i class="ti ti-trash"></i> Delete
                    </button>
                  </div>
                </div>
              </div>
              <div class="devoir-body">
                <p class="text-secondary">${escapeHtml(devoir.description)}</p>
                <div class="row">
                  <div class="col-md-4"><div class="detail-chip"><i class="ti ti-alert-triangle"></i> <strong>Erreur:</strong> ${escapeHtml(devoir.type_erreur_predominant)}</div></div>
                  <div class="col-md-4"><div class="detail-chip"><i class="ti ti-clock"></i> <strong>Temps:</strong> ${escapeHtml(devoir.temps_estime_resolution)} min</div></div>
                  <div class="col-md-4"><div class="detail-chip"><i class="ti ti-chart-bar"></i> <strong>Progression:</strong> ${escapeHtml(devoir.progression_eleve)}%</div></div>
                </div>
                ${devoir.mots_cles ? `
                  <div class="mt-2">
                    <strong>Mots cles:</strong>
                    <div>${devoir.mots_cles.split(",").map(tag => `<span class="tag">${escapeHtml(tag.trim())}</span>`).join("")}</div>
                  </div>
                ` : ""}
                ${devoir.fichier ? `
                  <div class="mt-2">
                    <a href="<?= htmlspecialchars($appBaseUrl) ?>/uploads/devoirs/${escapeHtml(devoir.fichier)}" target="_blank" class="text-primary">
                      <i class="ti ti-file"></i> ${escapeHtml(devoir.fichier)}
                    </a>
                  </div>
                ` : ""}
              </div>
              <div class="corrections-container">
                ${devoirCorrections.length === 0 ? `
                  <div class="text-secondary py-2"><i class="ti ti-message-circle-off"></i> Aucune correction pour ce devoir</div>
                ` : `
                  <div class="mb-2"><strong><i class="ti ti-check-circle text-success"></i> ${devoirCorrections.length} correction(s)</strong></div>
                  ${devoirCorrections.map(corr => `
                    <div class="correction-card" data-correction-id="${corr.id_correction}" data-correction-comment="${escapeHtml(corr.commentaire || "").toLowerCase()}">
                      <div class="d-flex justify-content-between align-items-start flex-wrap">
                        <div>
                          <span class="note-badge">${escapeHtml(corr.note_estimee)}/20</span>
                          <span class="badge bg-info ms-2">${escapeHtml(corr.type_feedback)}</span>
                          <span class="badge bg-secondary ms-2">${escapeHtml(corr.ton_feedback)}</span>
                          <span class="text-secondary ms-2"><i class="ti ti-calendar"></i> ${escapeHtml(corr.date_correction)}</span>
                        </div>
                        <div class="d-flex gap-2">
                          <button class="btn btn-sm btn-white edit-correction" data-id="${corr.id_correction}" data-devoir-title="${escapeHtml(devoir.titre)}">
                            <i class="ti ti-edit"></i> Edit
                          </button>
                          <button class="btn btn-sm btn-danger delete-correction" data-id="${corr.id_correction}">
                            <i class="ti ti-trash"></i> Delete
                          </button>
                        </div>
                      </div>
                      <p class="mt-2 mb-1">${escapeHtml(corr.commentaire)}</p>
                      <div class="row small">
                        <div class="col-md-6"><strong>Iterations:</strong> ${escapeHtml(corr.nombre_iterations)}</div>
                        <div class="col-md-6"><strong>Rapidite:</strong> ${escapeHtml(corr.rapidite_correction)} min</div>
                      </div>
                      ${corr.competences_evaluees ? `
                        <div class="mt-2">
                          <strong>Competences:</strong>
                          ${corr.competences_evaluees.split(",").map(comp => `<span class="tag">${escapeHtml(comp.trim())}</span>`).join("")}
                        </div>
                      ` : ""}
                      ${corr.suggestions_personnalisees ? `
                        <div class="mt-2 p-2 bg-light rounded">
                          <strong>Suggestions:</strong><br>${escapeHtml(corr.suggestions_personnalisees)}
                        </div>
                      ` : ""}
                    </div>
                  `).join("")}
                `}
              </div>
            </div>
          `;
        });

        container.innerHTML = html;

        const currentPeriod = document.getElementById('chartPeriodSelect')?.value || 'month';
        const currentActivityPeriod = document.getElementById('activityPeriodSelect')?.value || '6months';
        updatePieChart(devoirs, currentPeriod);
        updateLineChart(devoirs, currentActivityPeriod);

        document.getElementById("statTotalDevoirs").textContent = devoirs.length;
        document.getElementById("statTotalCorrections").textContent = corrections.length;
      }

      async function loadFeedData() {
        const controllerUrl = getControllerUrl();
        try {
          const [devoirPayload, correctionPayload] = await Promise.all([
            fetchJson(`${controllerUrl}?action=listdevoirs`),
            fetchJson(`${controllerUrl}?action=listcorrections`),
          ]);

          const devoirs = Array.isArray(devoirPayload.data) ? devoirPayload.data : [];
          const corrections = Array.isArray(correctionPayload.data) ? correctionPayload.data : [];

          renderFeed(devoirs, corrections);
          attachEventHandlers();

          const chartPeriodSelect = document.getElementById('chartPeriodSelect');
          if (chartPeriodSelect && !chartPeriodSelect.dataset.bound) {
            chartPeriodSelect.dataset.bound = '1';
            chartPeriodSelect.addEventListener('change', async () => {
              const period = chartPeriodSelect.value;
              const data = await fetchJson(`${getControllerUrl()}?action=listdevoirs`);
              updatePieChart(Array.isArray(data.data) ? data.data : [], period);
            });
          }

          const activityPeriodSelect = document.getElementById('activityPeriodSelect');
          if (activityPeriodSelect && !activityPeriodSelect.dataset.bound) {
            activityPeriodSelect.dataset.bound = '1';
            activityPeriodSelect.addEventListener('change', async () => {
              const period = activityPeriodSelect.value;
              const data = await fetchJson(`${getControllerUrl()}?action=listdevoirs`);
              updateLineChart(Array.isArray(data.data) ? data.data : [], period);
            });
          }

          const sortSelect = document.getElementById("sortBy");
          if (sortSelect && !sortSelect.dataset.bound) {
            sortSelect.dataset.bound = '1';
            sortSelect.addEventListener("change", () => loadFeedData());
          }
        } catch (error) {
          console.error("Error loading feed:", error);
          document.getElementById("feedContainer").innerHTML = `
            <div class="alert alert-danger">
              <i class="ti ti-alert-circle"></i> Error loading feed: ${escapeHtml(error.message)}
            </div>
          `;
        }
      }

      async function deleteItem(type, id) {
        const label = type === "devoir" ? "devoir" : "correction";
        if (!confirm(`Supprimer ce ${label} ?`)) return;

        const controllerUrl = getControllerUrl();
        const action = type === "devoir" ? "delete" : "deletecorrection";

        try {
          await fetch(`${controllerUrl}?action=${action}&id=${encodeURIComponent(id)}`, {
            headers: { "X-Requested-With": "XMLHttpRequest" }
          });
          showToast(`${label} supprime avec succes`, "success");
          await loadFeedData();
        } catch (error) {
          showToast(error.message || `Impossible de supprimer ${label}.`, "danger");
        }
      }

      function attachEventHandlers() {
        document.querySelectorAll(".delete-devoir").forEach(btn => {
          btn.addEventListener("click", e => {
            e.preventDefault();
            deleteItem("devoir", btn.dataset.id);
          });
        });

        document.querySelectorAll(".delete-correction").forEach(btn => {
          btn.addEventListener("click", e => {
            e.preventDefault();
            deleteItem("correction", btn.dataset.id);
          });
        });

        document.querySelectorAll(".edit-devoir").forEach(btn => {
          btn.addEventListener("click", e => {
            e.preventDefault();
            window.location.href = `${SUBMIT_URL}?edit=devoir&id=${btn.dataset.id}`;
          });
        });

        document.querySelectorAll(".edit-correction").forEach(btn => {
          btn.addEventListener("click", e => {
            e.preventDefault();
            const id = btn.dataset.id;
            const devoirTitle = btn.dataset.devoirTitle || "";
            const form = document.createElement("form");
            form.method = "POST";
            form.action = SUBMIT_URL;
            const fields = { edit: "correction", id_correction: id, devoir_titre: devoirTitle };
            Object.entries(fields).forEach(([name, value]) => {
              const input = document.createElement("input");
              input.type = "hidden";
              input.name = name;
              input.value = value;
              form.appendChild(input);
            });
            document.body.appendChild(form);
            form.submit();
          });
        });

        const searchInput = document.getElementById("searchInput");
        if (searchInput && !searchInput.dataset.bound) {
          searchInput.dataset.bound = '1';
          searchInput.addEventListener("input", () => {
            const searchTerm = searchInput.value.toLowerCase();
            const cards = document.querySelectorAll(".devoir-card");
            cards.forEach(card => {
              const devoirTitle = card.dataset.devoirTitle || "";
              const devoirKeywords = card.dataset.devoirKeywords || "";
              const corrections = card.querySelectorAll(".correction-card");
              let devoirMatch = searchTerm === "" || devoirTitle.includes(searchTerm) || devoirKeywords.includes(searchTerm);
              let correctionMatch = false;
              corrections.forEach(correction => {
                const comment = correction.dataset.correctionComment || "";
                if (searchTerm !== "" && comment.includes(searchTerm)) {
                  correctionMatch = true;
                  correction.style.display = "block";
                } else if (searchTerm === "") {
                  correction.style.display = "block";
                } else {
                  correction.style.display = "none";
                }
              });
              card.style.display = (devoirMatch || correctionMatch || searchTerm === "") ? "block" : "none";
            });
          });
        }

        const clearBtn = document.getElementById("clearFiltersBtn");
        if (clearBtn && !clearBtn.dataset.bound) {
          clearBtn.dataset.bound = '1';
          clearBtn.addEventListener("click", () => {
            if (searchInput) {
              searchInput.value = "";
              searchInput.dispatchEvent(new Event("input"));
            }
            const sortSelect = document.getElementById("sortBy");
            if (sortSelect) sortSelect.value = "date_desc";
            loadFeedData();
            showToast("Filtres reinitialises", "success");
          });
        }
      }

      // ==================== EXPORT PDF ====================
      async function exportToPDF() {
        const feedContainer = document.getElementById('feedContainer');
        if (!feedContainer) {
          showToast('Contenu non trouve', 'danger');
          return;
        }

        const loader = document.createElement('div');
        loader.className = 'pdf-loader';
        loader.innerHTML = '<div class="spinner"></div><div>Generation du PDF...</div>';
        document.body.appendChild(loader);

        const buttonsToHide = document.querySelectorAll(
          '.btn-delete, .btn-edit, .btn-add-correction, .delete-devoir, ' +
          '.delete-correction, .edit-devoir, .edit-correction, #refreshFeedBtn, #clearFiltersBtn'
        );
        buttonsToHide.forEach(btn => { if (btn) btn.style.display = 'none'; });

        const opt = {
          margin: [0.5, 0.5, 0.5, 0.5],
          filename: 'EduFeed_' + new Date().toISOString().slice(0, 19).replace(/:/g, '-') + '.pdf',
          image: { type: 'jpeg', quality: 0.98 },
          html2canvas: { scale: 2, letterRendering: true },
          jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
        };

        try {
          await html2pdf().set(opt).from(feedContainer).save();
          showToast('PDF exporte avec succes !', 'success');
        } catch (error) {
          console.error('PDF Error:', error);
          showToast('Erreur lors de la generation du PDF', 'danger');
        } finally {
          buttonsToHide.forEach(btn => { if (btn) btn.style.display = ''; });
          if (loader) loader.remove();
        }
      }

      function attachPDFButtonEvent() {
        const pdfBtn = document.getElementById('exportPDFBtn');
        if (pdfBtn) pdfBtn.addEventListener('click', exportToPDF);
      }

      // ==================== CHARTS ====================
      let devoirsPieChart = null;
      let activityLineChart = null;

      function getWeekNumber(date) {
        const d = new Date(date);
        d.setHours(0, 0, 0, 0);
        d.setDate(d.getDate() + 3 - (d.getDay() + 6) % 7);
        const week1 = new Date(d.getFullYear(), 0, 4);
        return 1 + Math.round(((d - week1) / 86400000 - 3 + (week1.getDay() + 6) % 7) / 7);
      }

      function countDevoirsByMonth(devoirs) {
        const counts = {};
        devoirs.forEach(devoir => {
          const date = new Date(devoir.date_soumission);
          const key = `${date.getFullYear()}-${date.getMonth() + 1}`;
          const label = date.toLocaleString('fr-FR', { month: 'short', year: 'numeric' });
          if (!counts[key]) counts[key] = { count: 0, label: label };
          counts[key].count++;
        });
        return counts;
      }

      function countDevoirsByWeek(devoirs) {
        const counts = {};
        devoirs.forEach(devoir => {
          const date = new Date(devoir.date_soumission);
          const weekNumber = getWeekNumber(date);
          const key = `${date.getFullYear()}-S${weekNumber}`;
          const label = `S${weekNumber} ${date.getFullYear()}`;
          if (!counts[key]) counts[key] = { count: 0, label: label };
          counts[key].count++;
        });
        return counts;
      }

      function countDevoirsByPeriod(devoirs, periodType = 'month') {
        const counts = {};
        devoirs.forEach(devoir => {
          const date = new Date(devoir.date_soumission);
          let key, label;
          if (periodType === 'month') {
            key = `${date.getFullYear()}-${date.getMonth() + 1}`;
            label = date.toLocaleString('fr-FR', { month: 'short', year: 'numeric' });
          } else {
            const weekNumber = getWeekNumber(date);
            key = `${date.getFullYear()}-S${weekNumber}`;
            label = `S${weekNumber}`;
          }
          if (!counts[key]) counts[key] = { count: 0, label: label, fullDate: date };
          counts[key].count++;
        });
        const sorted = Object.values(counts).sort((a, b) => a.fullDate - b.fullDate);
        return { labels: sorted.map(item => item.label), data: sorted.map(item => item.count) };
      }

      function updatePieChart(devoirs, period = 'month') {
        const counts = period === 'month' ? countDevoirsByMonth(devoirs) : countDevoirsByWeek(devoirs);
        const labels = Object.values(counts).map(item => item.label);
        const data = Object.values(counts).map(item => item.count);
        const total = data.reduce((a, b) => a + b, 0);

        const totalLabel = document.getElementById('chartTotalLabel');
        if (totalLabel) totalLabel.textContent = `Total: ${total} devoirs`;

        if (devoirsPieChart) devoirsPieChart.destroy();

        const canvas = document.getElementById('devoirsPieChart');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        devoirsPieChart = new Chart(ctx, {
          type: 'pie',
          data: {
            labels: labels,
            datasets: [{
              data: data,
              backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6', '#ec489a', '#06b6d4', '#84cc16', '#f97316', '#6366f1', '#14b8a6', '#d946ef'],
              borderWidth: 2,
              borderColor: '#fff'
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
              legend: { position: 'right', labels: { font: { size: 11 }, boxWidth: 12 } },
              tooltip: {
                callbacks: {
                  label: function(context) {
                    const label = context.label || '';
                    const value = context.raw || 0;
                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                    const percentage = ((value / total) * 100).toFixed(1);
                    return `${label}: ${value} devoir(s) (${percentage}%)`;
                  }
                }
              }
            }
          }
        });
      }

      function updateLineChart(devoirs, periodRange = '6months') {
        let filteredDevoirs = [...devoirs];
        if (periodRange !== 'all') {
          const monthsToShow = periodRange === '6months' ? 6 : 12;
          const cutoffDate = new Date();
          cutoffDate.setMonth(cutoffDate.getMonth() - monthsToShow);
          filteredDevoirs = devoirs.filter(d => new Date(d.date_soumission) >= cutoffDate);
        }
        const { labels, data } = countDevoirsByPeriod(filteredDevoirs, 'month');
        const total = data.reduce((a, b) => a + b, 0);

        const totalLabel = document.getElementById('activityTotalLabel');
        if (totalLabel) totalLabel.textContent = `Total: ${total} devoirs`;

        if (activityLineChart) activityLineChart.destroy();

        const canvas = document.getElementById('activityLineChart');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        activityLineChart = new Chart(ctx, {
          type: 'line',
          data: {
            labels: labels,
            datasets: [{
              label: 'Nombre de devoirs soumis',
              data: data,
              borderColor: '#10b981',
              backgroundColor: 'rgba(16, 185, 129, 0.1)',
              borderWidth: 3,
              fill: true,
              tension: 0.3,
              pointBackgroundColor: '#10b981',
              pointBorderColor: '#fff',
              pointBorderWidth: 2,
              pointRadius: 4,
              pointHoverRadius: 6
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
              legend: { position: 'top' },
              tooltip: {
                callbacks: { label: function(context) { return `${context.raw} devoir(s) soumis`; } }
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                title: { display: true, text: 'Nombre de devoirs', font: { weight: 'bold' } },
                ticks: { stepSize: 1, precision: 0 }
              },
              x: { title: { display: true, text: 'Periode', font: { weight: 'bold' } } }
            }
          }
        });
      }

      // ==================== BOOT ====================
      function loadScript(src) {
        return new Promise((resolve, reject) => {
          const script = document.createElement("script");
          script.src = src;
          script.onload = resolve;
          script.onerror = () => reject(new Error("Failed to load " + src));
          document.body.appendChild(script);
        });
      }

      (async function bootFeedPage() {
        const scripts = [
          "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js",
          "https://cdn.jsdelivr.net/npm/simplebar@6.2.5/dist/simplebar.min.js"
        ];
        for (const scriptPath of scripts) {
          try { await loadScript(scriptPath); } catch (error) { console.error(error); }
        }

        await loadFeedData();

        const refreshBtn = document.getElementById("refreshFeedBtn");
        if (refreshBtn) {
          refreshBtn.addEventListener("click", async () => {
            await loadFeedData();
            showToast("Feed actualise", "success");
          });
        }

        attachPDFButtonEvent();
      })();
    </script>
  </body>
</html>

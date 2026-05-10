<?php
$placeholderImg = 'https://via.placeholder.com/400x200?text=Evenement';
$data['extra_head'] = '<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet" />';
ob_start();
?>

<div class="row mb-5 text-center">
    <div class="col-lg-8 mx-auto">
        <h1 class="display-4 fw-bold">Découvrez nos Événements</h1>
        <p class="lead text-muted">Webinaires, ateliers et conférences pour booster votre apprentissage.</p>
    </div>
</div>

<?php if (!empty($data['flash'])): ?>
<div class="alert alert-<?php echo htmlspecialchars($data['flash']['type']); ?> alert-dismissible fade show" role="alert">
    <?php echo htmlspecialchars($data['flash']['message']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<?php if (!empty($data['map_locations'])): ?>
<?php $firstMap = $data['map_locations'][0]['maps_url']; ?>
<div class="row g-4 mb-5 align-items-start">
    <div class="col-lg-7 col-md-6">
        <h2 class="h4 fw-bold mb-4">Événements</h2>
        <div class="row row-cols-1 row-cols-md-2 g-4">
            <?php foreach ($data['evenements'] as $ev):
                $imgSrc = !empty($ev['image_display']) ? $ev['image_display'] : $placeholderImg;
            ?>
            <div class="col">
                <div class="card h-100 shadow-sm border-0">
                    <img src="<?php echo htmlspecialchars($imgSrc); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($ev['titre']); ?>" referrerpolicy="no-referrer" onerror="this.onerror=null;this.src='<?php echo htmlspecialchars($placeholderImg, ENT_QUOTES, 'UTF-8'); ?>';">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge" style="background-color: <?php echo htmlspecialchars($ev['couleur'] ?: '#0d6efd'); ?>;"><?php echo htmlspecialchars($ev['nom_categorie']); ?></span>
                            <small class="text-muted"><?php echo htmlspecialchars($ev['date_debut']); ?></small>
                        </div>
                        <h5 class="card-title fw-bold"><?php echo htmlspecialchars($ev['titre']); ?></h5>
                        <p class="card-text text-muted"><?php echo htmlspecialchars(substr($ev['description'], 0, 100)) . '...'; ?></p>
                        <p class="small text-secondary mb-2">
                            <strong><?php echo (int) $ev['nb_participants']; ?></strong> participant<?php echo ((int) $ev['nb_participants'] > 1) ? 's' : ''; ?> inscrit<?php echo ((int) $ev['nb_participants'] > 1) ? 's' : ''; ?>
                        </p>
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <?php if ((int) $ev['nb_places_disponibles'] > 0): ?>
                            <span class="text-primary fw-bold"><?php echo (int) $ev['nb_places_disponibles']; ?> places restantes</span>
                            <?php else: ?>
                            <span class="text-danger fw-bold">Complet</span>
                            <?php endif; ?>
                            <a href="<?php echo BASE_URL; ?>/Home/detail/<?php echo $ev['id_evenement']; ?>" class="btn btn-outline-primary btn-sm">Détails</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="col-lg-5 col-md-6">
        <div class="card shadow-sm border-0 h-100 roadmap-aside-card">
            <div class="card-body p-4 d-flex flex-column">
                <h2 class="h4 fw-bold mb-3">Roadmap des localisations</h2>
                <p class="text-muted small mb-3">Lieux utilisés par au moins un événement actif (liste unique par adresse).</p>
                <div class="row g-3 roadmap-split align-items-lg-stretch flex-grow-1">
                    <div class="col-12 col-md-5 col-lg-5 order-lg-1 order-2">
                        <h3 class="h6 text-uppercase text-muted fw-semibold mb-2">Lieux disponibles</h3>
                        <div class="list-group roadmap-venues-scroll" id="roadmapList">
                            <?php foreach ($data['map_locations'] as $idx => $loc): ?>
                            <div class="list-group-item list-group-item-action roadmap-item <?php echo $idx === 0 ? 'active' : ''; ?>" data-map-url="<?php echo htmlspecialchars($loc['maps_url'], ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                    <strong class="text-break"><?php echo htmlspecialchars($loc['lieu']); ?></strong>
                                    <span class="badge flex-shrink-0" style="background-color: <?php echo htmlspecialchars($loc['couleur'] ?: '#0d6efd'); ?>;">Lieu</span>
                                </div>
                                <?php if (!empty($loc['titre_evenements'])): ?>
                                <div class="small text-muted mb-1"><?php echo htmlspecialchars($loc['titre_evenements']); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($loc['date_debut'])): ?>
                                <div class="small text-muted">Échéance la plus proche : <?php echo htmlspecialchars(date('d/m/Y', strtotime($loc['date_debut']))); ?></div>
                                <?php endif; ?>
                                <a class="small d-inline-block mt-2 roadmap-external-map" href="<?php echo htmlspecialchars($loc['maps_link']); ?>" target="_blank" rel="noopener">Ouvrir dans Google Maps</a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="col-12 col-md-7 col-lg-7 order-lg-2 order-1">
                        <h3 class="h6 text-uppercase text-muted fw-semibold mb-2">Carte</h3>
                        <div class="roadmap-map-wrap border rounded overflow-hidden bg-light">
                            <iframe id="roadmapFrame" class="roadmap-google-iframe" title="Carte Google Maps — lieu sélectionné" src="<?php echo htmlspecialchars($firstMap); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="row row-cols-1 row-cols-md-3 g-4">
    <?php foreach ($data['evenements'] as $ev):
        $imgSrc = !empty($ev['image_display']) ? $ev['image_display'] : $placeholderImg;
    ?>
    <div class="col">
        <div class="card h-100 shadow-sm border-0">
            <img src="<?php echo htmlspecialchars($imgSrc); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($ev['titre']); ?>" referrerpolicy="no-referrer" onerror="this.onerror=null;this.src='<?php echo htmlspecialchars($placeholderImg, ENT_QUOTES, 'UTF-8'); ?>';">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge" style="background-color: <?php echo htmlspecialchars($ev['couleur'] ?: '#0d6efd'); ?>;"><?php echo htmlspecialchars($ev['nom_categorie']); ?></span>
                    <small class="text-muted"><?php echo htmlspecialchars($ev['date_debut']); ?></small>
                </div>
                <h5 class="card-title fw-bold"><?php echo htmlspecialchars($ev['titre']); ?></h5>
                <p class="card-text text-muted"><?php echo htmlspecialchars(substr($ev['description'], 0, 100)) . '...'; ?></p>
                <p class="small text-secondary mb-2">
                    <strong><?php echo (int) $ev['nb_participants']; ?></strong> participant<?php echo ((int) $ev['nb_participants'] > 1) ? 's' : ''; ?> inscrit<?php echo ((int) $ev['nb_participants'] > 1) ? 's' : ''; ?>
                </p>
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <?php if ((int) $ev['nb_places_disponibles'] > 0): ?>
                    <span class="text-primary fw-bold"><?php echo (int) $ev['nb_places_disponibles']; ?> places restantes</span>
                    <?php else: ?>
                    <span class="text-danger fw-bold">Complet</span>
                    <?php endif; ?>
                    <a href="<?php echo BASE_URL; ?>/Home/detail/<?php echo $ev['id_evenement']; ?>" class="btn btn-outline-primary btn-sm">Détails</a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card shadow-sm border-0 mb-5">
    <div class="card-body p-4">
        <h2 class="h4 fw-bold mb-3">Calendrier des événements</h2>
        <p class="text-muted small mb-3">Cliquez sur un événement pour ouvrir la fiche détail. En vue Mois : la durée de chaque événement est indiquée sur le créneau. En vue Planning (liste) : début et fin détaillés.</p>
        <div id="homeCalendar"></div>
    </div>
</div>

<?php
$content = ob_get_clean();
$eventsJson = json_encode($data['calendar_events'] ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$hasRoadmap = !empty($data['map_locations']) ? '1' : '0';
$data['footer_scripts'] = '<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>'
    . '<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/fr.js"></script>'
    . '<script>'
    . 'document.addEventListener("DOMContentLoaded", function() {'
    . 'var el = document.getElementById("homeCalendar");'
    . 'if (el) {'
    . 'var events = ' . $eventsJson . ';'
    . 'var calendar = new FullCalendar.Calendar(el, {'
    . 'locale: "fr",'
    . 'initialView: "dayGridMonth",'
    . 'headerToolbar: { left: "prev,next today", center: "title", right: "dayGridMonth,listMonth" },'
    . 'height: "auto",'
    . 'events: events,'
    . 'displayEventEnd: true,'
    . 'eventClick: function(info) {'
    . 'var u = info.event.extendedProps.detailUrl;'
    . 'if (u) { window.location.href = u; }'
    . '},'
    . 'eventContent: function(info) {'
    . 'var vt = info.view.type || "";'
    . 'var p = info.event.extendedProps || {};'
    . 'if (vt.indexOf("list") !== -1) {'
    . 'var wrap = document.createElement("div");'
    . 'wrap.className = "home-cal-list-rows";'
    . 'var r = document.createElement("small");'
    . 'r.className = "text-muted d-block";'
    . 'var ds = p.dateDebutLabel != null ? String(p.dateDebutLabel) : "";'
    . 'var df = p.dateFinLabel != null ? String(p.dateFinLabel) : "";'
    . 'r.textContent = "Début : " + ds + " — Fin : " + df;'
    . 'var t = document.createElement("span");'
    . 't.className = "fw-semibold";'
    . 't.textContent = info.event.title || "";'
    . 'wrap.appendChild(r);'
    . 'wrap.appendChild(t);'
    . 'return { domNodes: [wrap] };'
    . '}'
    . 'if (vt === "dayGridMonth") {'
    . 'var dur = p.dureeLabel != null ? String(p.dureeLabel) : "";'
    . 'var box = document.createElement("div");'
    . 'box.className = "home-cal-month-cell";'
    . 'var titleLine = document.createElement("div");'
    . 'titleLine.className = "home-cal-month-title";'
    . 'titleLine.textContent = info.event.title || "";'
    . 'box.appendChild(titleLine);'
    . 'if (dur) {'
    . 'var durLine = document.createElement("div");'
    . 'durLine.className = "home-cal-month-dur";'
    . 'durLine.textContent = dur;'
    . 'box.appendChild(durLine);'
    . '}'
    . 'return { domNodes: [box] };'
    . '}'
    . 'return null;'
    . '},'
    . 'eventDidMount: function(info) {'
    . 'var p = info.event.extendedProps;'
    . 'var ds = (p.dateDebutLabel != null) ? String(p.dateDebutLabel) : "";'
    . 'var df = (p.dateFinLabel != null) ? String(p.dateFinLabel) : "";'
    . 'var du = (p.dureeLabel != null) ? String(p.dureeLabel) : "";'
    . 'var t = info.event.title + (du ? (" — " + du) : "") + ". Début : " + ds + ", fin : " + df + ". Places : " + p.places + "/" + p.capacite + ", inscrits : " + p.participants;'
    . 'info.el.setAttribute("title", t);'
    . '}'
    . '});'
    . 'calendar.render();'
    . '}'
    . 'if ("' . $hasRoadmap . '" === "1") {'
    . 'var frame = document.getElementById("roadmapFrame");'
    . 'var list = document.getElementById("roadmapList");'
    . 'if (frame && list) {'
    . 'list.addEventListener("click", function(e) {'
    . 'if (e.target.closest("a.roadmap-external-map")) return;'
    . 'var item = e.target.closest(".roadmap-item");'
    . 'if (!item) return;'
    . 'var mapUrl = item.getAttribute("data-map-url");'
    . 'if (mapUrl) frame.src = mapUrl;'
    . 'var active = list.querySelector(".roadmap-item.active");'
    . 'if (active) active.classList.remove("active");'
    . 'item.classList.add("active");'
    . '});'
    . '}'
    . '}'
    . '});'
    . '</script>';
include __DIR__ . '/../layouts/front_layout.php';
?>

<?php
$candidature = $candidature ?? [];
$reply = $_GET['reply'] ?? null;
$error = $_GET['error'] ?? null;
$formatDateTime = static function ($value): string {
    $value = trim((string) $value);

    if ($value === '') {
        return '';
    }

    try {
        return (new DateTimeImmutable($value))->format('d/m/Y H:i');
    } catch (Throwable $exception) {
        return $value;
    }
};
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Administration - Details candidature</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body { background: #f6f8fc; font-family: 'Public Sans', sans-serif; }
        .shell { min-height: 100vh; }
        .sidebar { width: 280px; background: #0f172a; color: #fff; }
        .sidebar a { color: rgba(255,255,255,.82); text-decoration: none; }
        .sidebar a:hover, .sidebar .active { color: #fff; }
        .main { flex: 1; min-width: 0; }
        .topbar { background: #fff; border-bottom: 1px solid #e5e7eb; }
        .metric-card { border: 0; box-shadow: 0 10px 30px rgba(15, 23, 42, .06); }
        @media (max-width: 991.98px) { .sidebar { width: 100%; } }
    </style>
</head>
<body>
    <div class="shell d-flex">
        <aside class="sidebar d-none d-lg-flex flex-column">
            <?php include __DIR__ . '/../partials/brand.php'; ?>
            <?php $activeTab = 'candidatures'; include __DIR__ . '/../partials/nav.php'; ?>
        </aside>

        <div class="main">
            <header class="topbar px-4 py-3 d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 text-secondary small">Module Candidature</p>
                    <h1 class="h4 mb-0">Details de la candidature</h1>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a class="btn btn-outline-secondary" href="index.php?espace=back&module=candidature&action=liste">Retour a la liste</a>
                    <a class="btn btn-primary" href="index.php?espace=back&module=candidature&action=modifier&id=<?= (int) $candidature['id'] ?>">Modifier</a>
                </div>
            </header>

            <main class="container-fluid p-4 p-lg-5">
                <?php if ($reply === 'acceptee'): ?>
                    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                        <strong>✓ Candidature acceptée !</strong> Le statut a été mis à jour.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php elseif ($reply === 'refusee'): ?>
                    <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
                        <strong>✓ Candidature refusée !</strong> Le statut a été mis à jour.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php elseif ($reply === 'enattente'): ?>
                    <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
                        <strong>✓ Statut restauré !</strong> La candidature est à nouveau en attente.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php elseif ($error === 'update'): ?>
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <strong>Erreur !</strong> Impossible de mettre à jour le statut.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card metric-card">
                            <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-start flex-wrap gap-2">
                                <div>
                                    <h3 class="h5 mb-1"><?= htmlspecialchars((string) ($candidature['offre_titre'] ?? 'Offre inconnue')) ?></h3>
                                    <p class="mb-0 text-secondary">Candidat: <?= htmlspecialchars(trim((string) ($candidature['prenom'] ?? '') . ' ' . (string) ($candidature['nom'] ?? ''))) ?></p>
                                </div>
                                <span class="badge <?= ($candidature['statut'] === 'enattente') ? 'text-bg-warning' : (($candidature['statut'] === 'acceptée' || $candidature['statut'] === 'acceptee') ? 'text-bg-success' : 'text-bg-danger') ?>">
                                    <?= htmlspecialchars((string) $candidature['statut']) ?>
                                </span>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <div class="row g-3">
                                    <div class="col-md-6"><strong>Nom:</strong> <?= htmlspecialchars((string) ($candidature['nom'] ?? '')) ?></div>
                                    <div class="col-md-6"><strong>Prenom:</strong> <?= htmlspecialchars((string) ($candidature['prenom'] ?? '')) ?></div>
                                    <div class="col-md-6"><strong>Email:</strong> <?= htmlspecialchars((string) $candidature['email']) ?></div>
                                    <div class="col-md-6"><strong>CV:</strong> <a href="<?= htmlspecialchars((string) $candidature['cvurl']) ?>" target="_blank" rel="noopener">Ouvrir le CV</a></div>
                                    <div class="col-md-6"><strong>Date de depot:</strong> <?= htmlspecialchars($formatDateTime($candidature['datecandidature'])) ?></div>
                                    <div class="col-md-6"><strong>Date de reponse:</strong> <?= htmlspecialchars(isset($candidature['datereponse']) ? $formatDateTime($candidature['datereponse']) : 'En attente') ?></div>
                                </div>

                                <div class="mt-4">
                                    <h4 class="h6">Lettre de motivation</h4>
                                    <p class="text-secondary mb-0" style="white-space: pre-wrap;"><?= htmlspecialchars((string) $candidature['lettremotivation']) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card metric-card h-100">
                            <div class="card-body d-flex flex-column gap-2">
                                <h4 class="h6 mb-3">Offre liee</h4>
                                <p class="mb-1"><strong>Titre:</strong> <?= htmlspecialchars((string) ($candidature['offre_titre'] ?? 'N/A')) ?></p>
                                <p class="mb-1"><strong>Lieu:</strong> <?= htmlspecialchars((string) ($candidature['offre_lieu'] ?? 'N/A')) ?></p>
                                <p class="mb-1"><strong>Contrat:</strong> <?= htmlspecialchars((string) ($candidature['offre_typecontrat'] ?? 'N/A')) ?></p>
                                <p class="mb-3"><strong>Suivi:</strong> En cours</p>

                                <?php if ($candidature['statut'] === 'enattente'): ?>
                                    <div class="d-grid gap-2">
                                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#acceptModal">Accepter</button>
                                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">Refuser</button>
                                    </div>
                                <?php else: ?>
                                    <p class="mb-0 text-secondary small">
                                        <strong>Status:</strong>
                                        <?php if ($candidature['statut'] === 'acceptee' || $candidature['statut'] === 'acceptée'): ?>
                                            <span class="badge text-bg-success">Acceptée</span>
                                        <?php else: ?>
                                            <span class="badge text-bg-danger">Refusée</span>
                                        <?php endif; ?>
                                    </p>
                                    <button type="button" class="btn btn-outline-warning btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#revertModal">Revenir à En attente</button>
                                <?php endif; ?>

                                <a class="btn btn-outline-primary" href="index.php?espace=back&module=candidature&action=modifier&id=<?= (int) $candidature['id'] ?>">Modifier</a>
                                <a class="btn btn-outline-danger" href="index.php?espace=back&module=candidature&action=supprimer&id=<?= (int) $candidature['id'] ?>" onclick="return confirm('Supprimer cette candidature ?');">Supprimer</a>
                            </div>
                        </div>
                    </div>

                    <!-- Accept Modal -->
                    <div class="modal fade" id="acceptModal" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header border-0">
                                    <h5 class="modal-title">Accepter la candidature & Planifier l'entretien</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="post" action="index.php?espace=back&module=candidature&action=repondre&id=<?= (int) $candidature['id'] ?>">
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="dateEntretien">Date d'entretien *</label>
                                                    <input type="date" class="form-control" id="dateEntretien" name="date_entretien" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="heureEntretien">Heure d'entretien *</label>
                                                    <input type="time" class="form-control" id="heureEntretien" name="heure_entretien" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="modeEntretien">Mode *</label>
                                                    <select class="form-select" id="modeEntretien" name="mode_entretien" required>
                                                        <option value="">-- Sélectionner --</option>
                                                        <option value="presentiel">Présentiel</option>
                                                        <option value="distanciel">Distanciel (Zoom/Teams)</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="lieuEntretien">Lieu (si présentiel) *</label>
                                                    <input type="text" class="form-control" id="lieuEntretien" name="lieu_entretien" placeholder="Ex: Salle 102, 5 rue...">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                        <input type="hidden" name="statut" value="acceptee">
                                        <button type="submit" class="btn btn-success">Confirmer l'acceptation & Notifier</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Reject Modal -->
                    <div class="modal fade" id="rejectModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header border-0">
                                    <h5 class="modal-title">Refuser la candidature</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="post" action="index.php?espace=back&module=candidature&action=repondre&id=<?= (int) $candidature['id'] ?>">
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label" for="motifRefus">Motif de refus (optionnel)</label>
                                            <textarea class="form-control" id="motifRefus" name="motif_refus" rows="4" placeholder="Expliquer le motif du refus (sera envoyé au candidat)..."></textarea>
                                            <small class="form-text text-muted">Ce motif sera inclus dans l'e-mail de refus envoyé au candidat.</small>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                        <input type="hidden" name="statut" value="refusee">
                                        <button type="submit" class="btn btn-danger">Confirmer le refus & Notifier</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Revert Modal -->
                    <div class="modal fade" id="revertModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header border-0">
                                    <h5 class="modal-title">Revenir à En attente</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="post" action="index.php?espace=back&module=candidature&action=repondre&id=<?= (int) $candidature['id'] ?>">
                                    <div class="modal-body">
                                        <p class="text-secondary">Êtes-vous sûr de vouloir revenir à "En attente" ?</p>
                                    </div>
                                    <div class="modal-footer border-0">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                        <input type="hidden" name="statut" value="enattente">
                                        <button type="submit" class="btn btn-warning">Revenir à En attente</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

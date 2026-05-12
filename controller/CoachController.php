<?php
/**
 * Routeur Coach IA Adaptatif
 *
 * Metier avance gestion_devoirs : genere un plan personnalise sur 7 jours
 * a partir de l'historique de l'etudiant via Groq llama-3.3-70b-versatile.
 *
 * URLs :
 *   ?action=dashboard       -> Page d'accueil Coach IA (bilan + bouton generer) [etudiant + admin]
 *   ?action=generate (POST) -> Genere un nouveau plan IA (insert coach_plans)
 *   ?action=plan&id=X       -> Affiche le plan genere (timeline 7 jours)
 *   ?action=mark_complete (POST &id=X) -> Marque un plan comme complete
 *   ?action=delete (POST &id=X) -> Supprime un plan
 */
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../api/CoachIAService.php';

$pdo = Config::getConnexion();

// Securite : seuls etudiant + admin peuvent acceder
if (empty($_SESSION['user_id'])) {
    header('Location: /gestion_users/view/template/sign-in.php');
    exit;
}
$role = $_SESSION['user_role'] ?? '';
if (!in_array($role, ['etudiant', 'admin'], true)) {
    header('Location: /gestion_users/view/template/index.php?error=role_coach');
    exit;
}

$userId = (int) $_SESSION['user_id'];
$action = $_GET['action'] ?? 'dashboard';

// ============================================================
// HELPER : agreger les donnees etudiant pour le snapshot
// ============================================================
function buildStudentSnapshot(PDO $pdo, int $userId): array
{
    // 1. Nombre de devoirs + note moyenne
    $stmt = $pdo->prepare("
        SELECT
            COUNT(DISTINCT d.id_devoir) AS nb_devoirs,
            COUNT(DISTINCT c.id_correction) AS nb_corrections,
            AVG(c.note_estimee) AS note_moyenne
        FROM devoirs d
        LEFT JOIN correction c ON c.id_devoir = d.id_devoir
        WHERE d.id_eleve = :uid
    ");
    $stmt->execute(['uid' => $userId]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    // 2. Competences evaluees (faibles vs fortes) - depuis correction.competences_evaluees + note_estimee
    $stmt = $pdo->prepare("
        SELECT c.competences_evaluees, c.note_estimee
        FROM correction c
        INNER JOIN devoirs d ON c.id_devoir = d.id_devoir
        WHERE d.id_eleve = :uid AND c.competences_evaluees IS NOT NULL AND c.competences_evaluees != ''
        ORDER BY c.date_correction DESC
        LIMIT 50
    ");
    $stmt->execute(['uid' => $userId]);
    $compRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $competenceScores = []; // [competence => [sum, count]]
    foreach ($compRows as $row) {
        $tokens = array_filter(array_map('trim', explode(',', (string) $row['competences_evaluees'])));
        $note = (float) ($row['note_estimee'] ?? 10);
        foreach ($tokens as $comp) {
            if ($comp === '') continue;
            if (!isset($competenceScores[$comp])) {
                $competenceScores[$comp] = ['sum' => 0, 'count' => 0];
            }
            $competenceScores[$comp]['sum'] += $note;
            $competenceScores[$comp]['count']++;
        }
    }

    $competenceAverages = [];
    foreach ($competenceScores as $comp => $data) {
        $competenceAverages[$comp] = round($data['sum'] / $data['count'], 2);
    }
    asort($competenceAverages); // tri ascendant (faibles en premier)

    $competencesFaibles = array_slice(array_keys($competenceAverages), 0, 5);
    $competencesFortes = array_slice(array_keys(array_reverse($competenceAverages, true)), 0, 5);

    // 3. Types d'erreurs frequents
    $stmt = $pdo->prepare("
        SELECT type_erreur_predominant, COUNT(*) AS cnt
        FROM devoirs
        WHERE id_eleve = :uid AND type_erreur_predominant IS NOT NULL AND type_erreur_predominant != ''
        GROUP BY type_erreur_predominant
        ORDER BY cnt DESC
        LIMIT 5
    ");
    $stmt->execute(['uid' => $userId]);
    $typesErreurs = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'type_erreur_predominant');

    // 4. Sentiment dominant 30 derniers jours
    $stmt = $pdo->prepare("
        SELECT sentiment, COUNT(*) AS cnt
        FROM devoirs
        WHERE id_eleve = :uid
          AND date_soumission >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
          AND sentiment IS NOT NULL
        GROUP BY sentiment
        ORDER BY cnt DESC
        LIMIT 1
    ");
    $stmt->execute(['uid' => $userId]);
    $sentRow = $stmt->fetch(PDO::FETCH_ASSOC);
    $sentimentDominant = $sentRow['sentiment'] ?? 'neutre';

    // 5. Nom etudiant
    $stmt = $pdo->prepare("SELECT prenom, nom FROM user WHERE id = :uid");
    $stmt->execute(['uid' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $studentName = trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? ''));

    return [
        'student_name' => $studentName ?: 'Etudiant',
        'nb_devoirs' => (int) ($stats['nb_devoirs'] ?? 0),
        'nb_corrections' => (int) ($stats['nb_corrections'] ?? 0),
        'note_moyenne' => $stats['note_moyenne'] !== null ? round((float) $stats['note_moyenne'], 2) : 0,
        'competences_top' => $competencesFaibles,
        'competences_strong' => $competencesFortes,
        'competence_averages' => $competenceAverages,
        'types_erreurs' => $typesErreurs,
        'sentiment_dominant' => $sentimentDominant,
    ];
}

// ============================================================
// DISPATCH
// ============================================================
switch ($action) {

    // ---------------------------------------------------------
    case 'dashboard':
        $snapshot = buildStudentSnapshot($pdo, $userId);

        // Liste des plans precedents
        $stmt = $pdo->prepare("
            SELECT id, overall_score, recommendation_level, status, generated_at,
                   nb_devoirs_analyses, note_moyenne
            FROM coach_plans
            WHERE user_id = :uid
            ORDER BY generated_at DESC
            LIMIT 10
        ");
        $stmt->execute(['uid' => $userId]);
        $previousPlans = $stmt->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../view/frontoffice/coach/dashboard.php';
        break;

    // ---------------------------------------------------------
    case 'generate':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /gestion_users/controller/CoachController.php?action=dashboard');
            exit;
        }

        try {
            $snapshot = buildStudentSnapshot($pdo, $userId);

            if ((int) ($snapshot['nb_devoirs'] ?? 0) === 0) {
                header('Location: /gestion_users/controller/CoachController.php?action=dashboard&error=no_devoirs');
                exit;
            }

            $service = new CoachIAService();
            $plan = $service->generatePlan($snapshot);

            // Insert
            $stmt = $pdo->prepare("
                INSERT INTO coach_plans (user_id, plan_json, snapshot_json, nb_devoirs_analyses, nb_corrections_analysees, note_moyenne, overall_score, recommendation_level)
                VALUES (:uid, :plan, :snap, :nbd, :nbc, :note, :score, :reco)
            ");
            $stmt->execute([
                'uid' => $userId,
                'plan' => json_encode($plan, JSON_UNESCAPED_UNICODE),
                'snap' => json_encode($snapshot, JSON_UNESCAPED_UNICODE),
                'nbd' => $snapshot['nb_devoirs'],
                'nbc' => $snapshot['nb_corrections'],
                'note' => $snapshot['note_moyenne'],
                'score' => $plan['overall_score'],
                'reco' => $plan['recommendation_level'],
            ]);
            $newId = (int) $pdo->lastInsertId();

            header('Location: /gestion_users/controller/CoachController.php?action=plan&id=' . $newId . '&success=1');
            exit;
        } catch (Throwable $e) {
            error_log("Coach generate error: " . $e->getMessage());
            $errMsg = urlencode($e->getMessage());
            header('Location: /gestion_users/controller/CoachController.php?action=dashboard&error=' . $errMsg);
            exit;
        }
        break;

    // ---------------------------------------------------------
    case 'plan':
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            header('Location: /gestion_users/controller/CoachController.php?action=dashboard');
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM coach_plans WHERE id = :id AND user_id = :uid");
        $stmt->execute(['id' => $id, 'uid' => $userId]);
        $coachPlan = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$coachPlan) {
            header('Location: /gestion_users/controller/CoachController.php?action=dashboard&error=not_found');
            exit;
        }

        $plan = json_decode((string) $coachPlan['plan_json'], true) ?: [];
        $snapshot = json_decode((string) $coachPlan['snapshot_json'], true) ?: [];
        $success = isset($_GET['success']);

        include __DIR__ . '/../view/frontoffice/coach/plan.php';
        break;

    // ---------------------------------------------------------
    case 'mark_complete':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /gestion_users/controller/CoachController.php?action=dashboard');
            exit;
        }
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE coach_plans SET status = 'completed', completed_at = NOW() WHERE id = :id AND user_id = :uid");
            $stmt->execute(['id' => $id, 'uid' => $userId]);
        }
        header('Location: /gestion_users/controller/CoachController.php?action=dashboard');
        exit;

    // ---------------------------------------------------------
    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /gestion_users/controller/CoachController.php?action=dashboard');
            exit;
        }
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM coach_plans WHERE id = :id AND user_id = :uid");
            $stmt->execute(['id' => $id, 'uid' => $userId]);
        }
        header('Location: /gestion_users/controller/CoachController.php?action=dashboard');
        exit;

    // ---------------------------------------------------------
    default:
        header('Location: /gestion_users/controller/CoachController.php?action=dashboard');
        exit;
}

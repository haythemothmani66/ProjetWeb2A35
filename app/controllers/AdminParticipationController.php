<?php
class AdminParticipationController extends Controller {
    private function db() {
        $database = new Database();
        return $database->getConnection();
    }

    private function getAllParticipations($search = '', $sort = 'nom_asc', $statusFilter = '') {
        $allowedSorts = [
            'date_desc' => 'p.date_inscription DESC',
            'date_asc' => 'p.date_inscription ASC',
            'nom_asc' => 'p.nom_participant ASC',
            'nom_desc' => 'p.nom_participant DESC',
            'statut_asc' => 'p.statut_participation ASC',
            'statut_desc' => 'p.statut_participation DESC',
            'event_asc' => 'e.titre ASC',
            'event_desc' => 'e.titre DESC'
        ];
        $orderBy = $allowedSorts[$sort] ?? $allowedSorts['nom_asc'];

        $query = "SELECT p.*, e.titre AS titre_evenement
                  FROM participations p
                  LEFT JOIN evenements e ON p.id_evenement = e.id_evenement";
        $conditions = [];
        if ($search !== '') {
            $conditions[] = "(p.nom_participant LIKE :search
                        OR p.email LIKE :search
                        OR p.statut_participation LIKE :search
                        OR p.mode_participation LIKE :search
                        OR e.titre LIKE :search)";
        }
        if ($statusFilter !== '') {
            $conditions[] = "p.statut_participation = :status_filter";
        }
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }
        $query .= " ORDER BY " . $orderBy;

        $stmt = $this->db()->prepare($query);
        if ($search !== '') {
            $stmt->bindValue(':search', '%' . $search . '%');
        }
        if ($statusFilter !== '') {
            $stmt->bindValue(':status_filter', $statusFilter);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getAllParticipationsRaw() {
        $stmt = $this->db()->prepare("SELECT p.*, e.titre AS titre_evenement
                  FROM participations p
                  LEFT JOIN evenements e ON p.id_evenement = e.id_evenement
                  ORDER BY p.nom_participant ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function buildParticipationStats($participations) {
        $stats = [
            'total' => count($participations),
            'inscrit' => 0,
            'confirme' => 0,
            'present' => 0,
            'absent' => 0,
            'annule' => 0
        ];

        foreach ($participations as $participation) {
            $statut = $participation['statut_participation'] ?? '';
            if ($statut === 'inscrit') {
                $stats['inscrit']++;
            } elseif ($statut === 'confirmé') {
                $stats['confirme']++;
            } elseif ($statut === 'présent') {
                $stats['present']++;
            } elseif ($statut === 'absent') {
                $stats['absent']++;
            } elseif ($statut === 'annulé') {
                $stats['annule']++;
            }
        }

        return $stats;
    }

    private function getParticipationById($id) {
        $stmt = $this->db()->prepare('SELECT * FROM participations WHERE id_participation = :id LIMIT 1');
        $stmt->bindValue(':id', (int) $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function updateParticipation(Participation $participation) {
        $query = "UPDATE participations
                  SET id_evenement = :id_evenement, id_user = :id_user, nom_participant = :nom,
                      email = :email, telephone = :telephone, statut_participation = :statut,
                      mode_participation = :mode, feedback = :feedback, note = :note
                  WHERE id_participation = :id";
        $stmt = $this->db()->prepare($query);
        $stmt->bindValue(':id_evenement', (int) $participation->id_evenement, PDO::PARAM_INT);
        $stmt->bindValue(':id_user', (int) $participation->id_user, PDO::PARAM_INT);
        $stmt->bindValue(':nom', $participation->nom_participant);
        $stmt->bindValue(':email', $participation->email);
        $stmt->bindValue(':telephone', $participation->telephone);
        $stmt->bindValue(':statut', $participation->statut_participation);
        $stmt->bindValue(':mode', $participation->mode_participation);
        $stmt->bindValue(':feedback', $participation->feedback);
        $stmt->bindValue(':note', $participation->note);
        $stmt->bindValue(':id', (int) $participation->id_participation, PDO::PARAM_INT);
        return $stmt->execute();
    }

    private function deleteParticipationById($id) {
        $stmt = $this->db()->prepare('DELETE FROM participations WHERE id_participation = :id');
        $stmt->bindValue(':id', (int) $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function index() {
        $search = trim($_GET['search'] ?? '');
        $sort = trim($_GET['sort'] ?? 'nom_asc');
        $statusFilter = trim($_GET['status_filter'] ?? '');

        $allowedStatusFilters = ['', 'inscrit', 'confirmé', 'présent', 'absent', 'annulé'];
        if (!in_array($statusFilter, $allowedStatusFilters, true)) {
            $statusFilter = '';
        }

        $participations = $this->getAllParticipations($search, $sort, $statusFilter);
        $stats = $this->buildParticipationStats($this->getAllParticipationsRaw());

        $this->view('back/participations/index', [
            'participations' => $participations,
            'stats' => $stats,
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'status_filter' => $statusFilter
            ],
            'flash' => $this->getFlash()
        ]);
    }

    public function edit($id) {
        $participation = $this->model('Participation');
        $record = $this->getParticipationById($id);
        if (!$record) {
            $this->setFlash('danger', 'Participation introuvable.');
            $this->redirect('/AdminParticipation/index');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $errors = $this->validateParticipation($_POST);
            if (!empty($errors)) {
                $record = array_merge($record, $_POST);
                $this->view('back/participations/edit', ['participation' => $record, 'errors' => $errors]);
                return;
            }

            $participation->id_participation = (int) $id;
            $participation->id_evenement = (int) $record['id_evenement'];
            $participation->id_user = (int) $record['id_user'];
            $participation->nom_participant = trim($_POST['nom_participant']);
            $participation->email = trim($_POST['email']);
            $participation->telephone = trim($_POST['telephone']);
            $participation->mode_participation = trim($_POST['mode_participation']);
            $participation->statut_participation = trim($_POST['statut_participation']);
            $participation->feedback = trim($_POST['feedback']);
            $participation->note = $_POST['note'] === '' ? null : (int) $_POST['note'];

            if ($this->updateParticipation($participation)) {
                $this->setFlash('success', 'Participation modifiee avec succes.');
                $this->redirect('/AdminParticipation/index');
            }

            $errors['general'] = 'Impossible de modifier la participation.';
            $record = array_merge($record, $_POST);
            $this->view('back/participations/edit', ['participation' => $record, 'errors' => $errors]);
            return;
        }
        $this->view('back/participations/edit', ['participation' => $record, 'errors' => []]);
    }

    public function delete($id) {
        if ($this->deleteParticipationById($id)) {
            $this->setFlash('success', 'Participation supprimee.');
        } else {
            $this->setFlash('danger', 'Suppression impossible.');
        }
        $this->redirect('/AdminParticipation/index');
    }

    private function validateParticipation($input) {
        $errors = [];
        $nom = trim($input['nom_participant'] ?? '');
        $email = trim($input['email'] ?? '');
        $telephone = trim($input['telephone'] ?? '');
        $mode = trim($input['mode_participation'] ?? '');
        $statut = trim($input['statut_participation'] ?? '');
        $noteRaw = trim((string) ($input['note'] ?? ''));

        if (mb_strlen($nom) < 3) {
            $errors['nom_participant'] = 'Le nom complet doit contenir au moins 3 caracteres.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Adresse email invalide.';
        }

        if ($telephone !== '' && !preg_match('/^[0-9+\s-]{8,20}$/', $telephone)) {
            $errors['telephone'] = 'Numero de telephone invalide.';
        }

        if (!in_array($mode, ['en ligne', 'présentiel', 'hybride'], true)) {
            $errors['mode_participation'] = 'Mode de participation invalide.';
        }

        if (!in_array($statut, ['inscrit', 'confirmé', 'présent', 'absent', 'annulé'], true)) {
            $errors['statut_participation'] = 'Statut participation invalide.';
        }

        if ($noteRaw !== '') {
            $note = (int) $noteRaw;
            if ($note < 0 || $note > 5) {
                $errors['note'] = 'La note doit etre comprise entre 0 et 5.';
            }
        }

        return $errors;
    }

    public function stats() {
        $participations = $this->getAllParticipationsRaw();
        $stats = $this->buildParticipationStats($participations);

        $evolution = [];
        foreach ($participations as $participation) {
            $dateKey = substr((string) ($participation['date_inscription'] ?? ''), 0, 10);
            if ($dateKey === '') {
                continue;
            }
            if (!isset($evolution[$dateKey])) {
                $evolution[$dateKey] = 0;
            }
            $evolution[$dateKey]++;
        }
        ksort($evolution);

        $this->view('back/participations/stats', [
            'stats' => $stats,
            'evolutionLabels' => array_keys($evolution),
            'evolutionData' => array_values($evolution)
        ]);
    }
}
?>

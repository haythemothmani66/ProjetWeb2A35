<?php
/**
 * ReservationController
 * Gere les reservations de seances entre etudiants et encadrants
 * Architecture MVC stricte : pas de SQL dans les views
 */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../model/Reservation.php';
require_once __DIR__ . '/../model/Disponibilite.php';
require_once __DIR__ . '/../api/MailHelper.php';

class ReservationController
{
    private PDO $conn;
    private int $userId;
    private string $userRole;

    public function __construct()
    {
        $this->conn = Config::getConnexion();
        $this->userId = (int)($_SESSION['user_id'] ?? 0);
        $this->userRole = (string)($_SESSION['user_role'] ?? '');
    }

    // ============================================================
    // ENCADRANTS LIST (pour la page grid)
    // ============================================================

    /**
     * Liste tous les encadrants actifs avec leurs informations de profil
     * @return array Liste d'encadrants
     */
    public function getAllEncadrants(?string $search = null, ?string $specialite = null): array
    {
        $sql = "
            SELECT u.id, u.nom, u.prenom, u.email, u.photo, u.telephone,
                   p.bio_text, p.niveau, p.specialite, p.etablissement_ecole, p.ville, p.pays
            FROM user u
            LEFT JOIN profil p ON p.user_id = u.id
            WHERE u.role = 'encadrant' AND u.statut = 1
        ";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (u.nom LIKE :s1 OR u.prenom LIKE :s2 OR p.specialite LIKE :s3 OR p.niveau LIKE :s4)";
            $like = '%' . $search . '%';
            $params[':s1'] = $like;
            $params[':s2'] = $like;
            $params[':s3'] = $like;
            $params[':s4'] = $like;
        }

        if (!empty($specialite)) {
            $sql .= " AND p.specialite = :specialite";
            $params[':specialite'] = $specialite;
        }

        $sql .= " ORDER BY u.nom ASC";

        $stmt = $this->conn->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recupere un encadrant par son ID
     */
    public function getEncadrantById(int $id): ?array
    {
        $stmt = $this->conn->prepare("
            SELECT u.id, u.nom, u.prenom, u.email, u.photo, u.telephone,
                   p.bio_text, p.niveau, p.specialite, p.etablissement_ecole, p.ville, p.pays
            FROM user u
            LEFT JOIN profil p ON p.user_id = u.id
            WHERE u.id = ? AND u.role = 'encadrant'
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    /**
     * Liste les specialites distinctes pour le filtre
     */
    public function getAllSpecialites(): array
    {
        $stmt = $this->conn->query("
            SELECT DISTINCT p.specialite
            FROM profil p
            INNER JOIN user u ON u.id = p.user_id
            WHERE u.role = 'encadrant' AND p.specialite IS NOT NULL AND p.specialite <> ''
            ORDER BY p.specialite
        ");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // ============================================================
    // DISPONIBILITES
    // ============================================================

    /**
     * Liste les disponibilites d'un encadrant
     */
    public function getDisponibilitesByEncadrant(int $idEncadrant): array
    {
        $stmt = $this->conn->prepare("
            SELECT * FROM disponibilites
            WHERE id_encadrant = ? AND actif = 1
            ORDER BY FIELD(jour_semaine,'lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche'), heure_debut
        ");
        $stmt->execute([$idEncadrant]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ajoute une disponibilite (encadrant gere ses propres creneaux)
     */
    public function addDisponibilite(int $idEncadrant, string $jour, string $hDebut, string $hFin): bool
    {
        $stmt = $this->conn->prepare("
            INSERT INTO disponibilites (id_encadrant, jour_semaine, heure_debut, heure_fin, actif)
            VALUES (?, ?, ?, ?, 1)
        ");
        return $stmt->execute([$idEncadrant, $jour, $hDebut, $hFin]);
    }

    /**
     * Supprime une disponibilite (verif ownership)
     */
    public function deleteDisponibilite(int $idDispo, int $idEncadrant): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM disponibilites WHERE id_disponibilite = ? AND id_encadrant = ?");
        return $stmt->execute([$idDispo, $idEncadrant]);
    }

    /**
     * Genere les creneaux disponibles d'un encadrant pour les 4 prochaines semaines
     * (pour affichage dans FullCalendar)
     * @return array Creneaux au format ['date'=>'2026-05-15', 'heure_debut'=>'08:00:00', 'heure_fin'=>'09:00:00', 'available'=>true]
     */
    public function generateAvailableSlots(int $idEncadrant, int $weeksAhead = 4): array
    {
        $dispos = $this->getDisponibilitesByEncadrant($idEncadrant);
        if (empty($dispos)) return [];

        // Reservations existantes pour cet encadrant (non refusees/annulees)
        $stmt = $this->conn->prepare("
            SELECT date_reservation, heure_debut, heure_fin
            FROM reservations
            WHERE id_encadrant = ?
              AND statut IN ('en_attente','acceptee')
              AND date_reservation >= CURDATE()
        ");
        $stmt->execute([$idEncadrant]);
        $reserved = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Index pour lookup rapide
        $reservedMap = [];
        foreach ($reserved as $r) {
            $key = $r['date_reservation'] . '|' . substr($r['heure_debut'], 0, 5);
            $reservedMap[$key] = true;
        }

        $slots = [];
        $today = new DateTime('today');
        $end = (clone $today)->modify('+' . $weeksAhead . ' weeks');

        // Pour chaque jour entre aujourd'hui et la fin
        $period = new DatePeriod($today, new DateInterval('P1D'), $end);
        foreach ($period as $day) {
            $iso = (int) $day->format('N');           // 1=lundi, 7=dimanche
            $jourNom = Disponibilite::isoToJour($iso);

            // Filtrer les dispos qui correspondent a ce jour
            foreach ($dispos as $d) {
                if ($d['jour_semaine'] !== $jourNom) continue;

                // Decouper le creneau en sous-creneaux d'1h
                $hStart = new DateTime($d['heure_debut']);
                $hEnd = new DateTime($d['heure_fin']);
                $current = clone $hStart;
                while ($current < $hEnd) {
                    $next = (clone $current)->modify('+1 hour');
                    if ($next > $hEnd) break;

                    $dateStr = $day->format('Y-m-d');
                    $hStr = $current->format('H:i:00');
                    $hStrShort = $current->format('H:i');
                    $key = $dateStr . '|' . $hStrShort;

                    $slots[] = [
                        'date'         => $dateStr,
                        'heure_debut'  => $hStr,
                        'heure_fin'    => $next->format('H:i:00'),
                        'available'    => !isset($reservedMap[$key]),
                    ];

                    $current = $next;
                }
            }
        }

        return $slots;
    }

    // ============================================================
    // RESERVATIONS - CRUD
    // ============================================================

    /**
     * Cree une nouvelle reservation par un etudiant
     * @return array ['success'=>bool, 'message'=>string, 'reservation_id'=>int|null]
     */
    public function createReservation(
        int $idEncadrant,
        string $date,
        string $heureDebut,
        string $heureFin,
        string $matiere,
        ?string $sujet,
        string $mode,
        ?string $notesEtudiant
    ): array {
        // Validations
        if ($this->userId <= 0) {
            return ['success' => false, 'message' => 'Vous devez etre connecte.', 'reservation_id' => null];
        }
        if (!in_array($this->userRole, ['etudiant', 'admin'], true)) {
            return ['success' => false, 'message' => 'Seuls les etudiants peuvent reserver.', 'reservation_id' => null];
        }
        if (empty($matiere) || strlen($matiere) < 2) {
            return ['success' => false, 'message' => 'La matiere est requise (min 2 caracteres).', 'reservation_id' => null];
        }
        if (!in_array($mode, ['en_ligne', 'presentiel'], true)) {
            return ['success' => false, 'message' => 'Mode invalide.', 'reservation_id' => null];
        }

        // Verifier que la date n'est pas dans le passe
        $dateRes = new DateTime($date . ' ' . $heureDebut);
        if ($dateRes < new DateTime('now')) {
            return ['success' => false, 'message' => 'Cette date est dans le passe.', 'reservation_id' => null];
        }

        // Verifier que l'encadrant existe
        $enc = $this->getEncadrantById($idEncadrant);
        if (!$enc) {
            return ['success' => false, 'message' => 'Encadrant introuvable.', 'reservation_id' => null];
        }

        // Verifier que le creneau n'est pas deja reserve (UNIQUE KEY le garantit, mais on prefiltre)
        $check = $this->conn->prepare("
            SELECT COUNT(*) FROM reservations
            WHERE id_encadrant = ? AND date_reservation = ? AND heure_debut = ?
              AND statut IN ('en_attente','acceptee')
        ");
        $check->execute([$idEncadrant, $date, $heureDebut]);
        if ((int) $check->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'Ce creneau est deja reserve.', 'reservation_id' => null];
        }

        // Generer le token unique
        $token = Reservation::generateToken();

        // INSERT
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO reservations
                    (id_etudiant, id_encadrant, date_reservation, heure_debut, heure_fin, matiere, sujet, mode, statut, notes_etudiant, token_action)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'en_attente', ?, ?)
            ");
            $stmt->execute([
                $this->userId,
                $idEncadrant,
                $date,
                $heureDebut,
                $heureFin,
                $matiere,
                $sujet,
                $mode,
                $notesEtudiant,
                $token,
            ]);
            $resId = (int) $this->conn->lastInsertId();

            // Recuperer infos etudiant pour le mail
            $studStmt = $this->conn->prepare("SELECT nom, prenom FROM user WHERE id = ?");
            $studStmt->execute([$this->userId]);
            $stud = $studStmt->fetch(PDO::FETCH_ASSOC);
            $nomEtudiant = trim(($stud['prenom'] ?? '') . ' ' . ($stud['nom'] ?? ''));

            // Envoi mail a l'encadrant
            MailHelper::sendReservationToEncadrant(
                $enc['email'],
                trim(($enc['prenom'] ?? '') . ' ' . ($enc['nom'] ?? '')),
                $nomEtudiant,
                $date,
                substr($heureDebut, 0, 5),
                substr($heureFin, 0, 5),
                $matiere,
                $sujet ?? '',
                $mode,
                $token
            );

            return ['success' => true, 'message' => 'Reservation envoyee ! L\'encadrant a recu votre demande par email.', 'reservation_id' => $resId];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur DB : ' . $e->getMessage(), 'reservation_id' => null];
        }
    }

    /**
     * Liste les reservations d'un etudiant
     */
    public function getReservationsByEtudiant(int $idEtudiant): array
    {
        $stmt = $this->conn->prepare("
            SELECT r.*, u.nom AS encadrant_nom, u.prenom AS encadrant_prenom, u.email AS encadrant_email, u.photo AS encadrant_photo,
                   p.specialite AS encadrant_specialite
            FROM reservations r
            INNER JOIN user u ON u.id = r.id_encadrant
            LEFT JOIN profil p ON p.user_id = u.id
            WHERE r.id_etudiant = ?
            ORDER BY r.date_reservation DESC, r.heure_debut DESC
        ");
        $stmt->execute([$idEtudiant]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Liste les reservations recues par un encadrant
     */
    public function getReservationsByEncadrant(int $idEncadrant): array
    {
        $stmt = $this->conn->prepare("
            SELECT r.*, u.nom AS etudiant_nom, u.prenom AS etudiant_prenom, u.email AS etudiant_email, u.photo AS etudiant_photo
            FROM reservations r
            INNER JOIN user u ON u.id = r.id_etudiant
            WHERE r.id_encadrant = ?
            ORDER BY r.date_reservation DESC, r.heure_debut DESC
        ");
        $stmt->execute([$idEncadrant]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recupere une reservation par son token (pour le lien email)
     */
    public function getReservationByToken(string $token): ?array
    {
        $stmt = $this->conn->prepare("
            SELECT r.*,
                   ue.nom AS encadrant_nom, ue.prenom AS encadrant_prenom, ue.email AS encadrant_email,
                   us.nom AS etudiant_nom, us.prenom AS etudiant_prenom, us.email AS etudiant_email
            FROM reservations r
            INNER JOIN user ue ON ue.id = r.id_encadrant
            INNER JOIN user us ON us.id = r.id_etudiant
            WHERE r.token_action = ?
            LIMIT 1
        ");
        $stmt->execute([$token]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    /**
     * Met a jour le statut d'une reservation (accept/refuse par l'encadrant)
     * @return array ['success'=>bool, 'message'=>string]
     */
    public function updateStatus(int $idReservation, string $newStatut, ?string $notesEncadrant = null): array
    {
        if (!in_array($newStatut, ['acceptee', 'refusee'], true)) {
            return ['success' => false, 'message' => 'Statut invalide.'];
        }

        $stmt = $this->conn->prepare("
            UPDATE reservations
            SET statut = ?, notes_encadrant = ?, date_reponse = NOW()
            WHERE id_reservation = ? AND statut = 'en_attente'
        ");
        $ok = $stmt->execute([$newStatut, $notesEncadrant, $idReservation]);

        if (!$ok || $stmt->rowCount() === 0) {
            return ['success' => false, 'message' => 'Reservation introuvable ou deja traitee.'];
        }

        return ['success' => true, 'message' => 'Statut mis a jour avec succes.'];
    }

    /**
     * L'etudiant annule sa propre reservation (avec delai 24h avant)
     */
    public function cancelReservation(int $idReservation, int $idEtudiant): array
    {
        // Recuperer la reservation
        $stmt = $this->conn->prepare("
            SELECT r.*, ue.email AS encadrant_email, ue.nom AS encadrant_nom, ue.prenom AS encadrant_prenom,
                   us.nom AS etudiant_nom, us.prenom AS etudiant_prenom
            FROM reservations r
            INNER JOIN user ue ON ue.id = r.id_encadrant
            INNER JOIN user us ON us.id = r.id_etudiant
            WHERE r.id_reservation = ? AND r.id_etudiant = ?
            LIMIT 1
        ");
        $stmt->execute([$idReservation, $idEtudiant]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$res) {
            return ['success' => false, 'message' => 'Reservation introuvable ou non autorisee.'];
        }

        if (in_array($res['statut'], ['annulee', 'refusee', 'terminee'], true)) {
            return ['success' => false, 'message' => 'Cette reservation ne peut plus etre annulee.'];
        }

        // Verifier delai 24h
        $resDate = new DateTime($res['date_reservation'] . ' ' . $res['heure_debut']);
        $now = new DateTime('now');
        $diff = $resDate->getTimestamp() - $now->getTimestamp();
        if ($diff < 24 * 3600) {
            return ['success' => false, 'message' => 'Vous ne pouvez plus annuler (moins de 24h avant la seance).'];
        }

        // UPDATE statut = annulee
        $upd = $this->conn->prepare("UPDATE reservations SET statut = 'annulee', date_reponse = NOW() WHERE id_reservation = ?");
        $upd->execute([$idReservation]);

        // Mail a l'encadrant
        MailHelper::sendCancellationToEncadrant(
            $res['encadrant_email'],
            trim($res['encadrant_prenom'] . ' ' . $res['encadrant_nom']),
            trim($res['etudiant_prenom'] . ' ' . $res['etudiant_nom']),
            $res['date_reservation'],
            substr($res['heure_debut'], 0, 5),
            $res['matiere']
        );

        return ['success' => true, 'message' => 'Reservation annulee. L\'encadrant a ete informe.'];
    }
}

<?php

require_once dirname(__DIR__) . '/helpers/ImageHelper.php';
require_once dirname(__DIR__) . '/helpers/EmailValidationHelper.php';

class HomeController extends Controller {
    private function db() {
        $database = new Database();
        return $database->getConnection();
    }

    private function getActiveEvenements() {
        $query = "SELECT e.*, c.nom_categorie, c.couleur,
                  (SELECT COUNT(*) FROM participations p
                   WHERE p.id_evenement = e.id_evenement AND p.statut_participation <> 'annulé') AS nb_participants
                  FROM evenements e
                  LEFT JOIN categories c ON e.id_categorie = c.id_categorie
                  WHERE c.statut = 'actif' AND e.statut IN ('planifié', 'en cours')
                  ORDER BY e.date_debut ASC";
        $stmt = $this->db()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getEvenementById($id) {
        $query = "SELECT e.*, c.nom_categorie, c.couleur,
                  (SELECT COUNT(*) FROM participations p
                   WHERE p.id_evenement = e.id_evenement AND p.statut_participation <> 'annulé') AS nb_participants
                  FROM evenements e
                  LEFT JOIN categories c ON e.id_categorie = c.id_categorie
                  WHERE e.id_evenement = :id LIMIT 1";
        $stmt = $this->db()->prepare($query);
        $stmt->bindValue(':id', (int) $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function enrichEvenementRow(array $record) {
        $record['image_display'] = ImageHelper::normalizeEventImageUrl($record['image_evenement'] ?? '');
        $record['is_metiers_avances'] = ImageHelper::isMetiersAvancesCategory($record['nom_categorie'] ?? '');
        $maps = $this->buildMapsUrls($record['lien_acces'] ?? '', $record['lieu'] ?? '');
        $record['maps_url'] = $maps['embed'] ?? '';
        $record['maps_link'] = $maps['link'] ?? $record['maps_url'];
        return $record;
    }

    private function buildMapsUrls($lienAcces, $lieu) {
        $lienAcces = trim((string) $lienAcces);
        $lieu = trim((string) $lieu);
        
        // Toujours utiliser le lieu pour l'iframe (les URLs goo.gl ne peuvent pas être embeddées)
        // Mais utiliser le lienAcces pour le lien d'ouverture direct
        if ($lienAcces !== '' && preg_match('#^https?://#i', $lienAcces)) {
            if ($this->isGoogleMapsLink($lienAcces)) {
                // Utiliser le lieu pour l'iframe embeddable
                if ($lieu !== '') {
                    $query = rawurlencode($lieu);
                    return [
                        'embed' => 'https://www.google.com/maps?q=' . $query . '&hl=fr&z=14&output=embed',
                        'link' => $lienAcces,
                    ];
                } else {
                    // Fallback au lien d'accès si pas de lieu
                    return [
                        'embed' => $this->toEmbedMapsUrl($lienAcces),
                        'link' => $lienAcces,
                    ];
                }
            }
        }

        if ($lieu === '') {
            return ['embed' => '', 'link' => ''];
        }

        $query = rawurlencode($lieu);
        return [
            'embed' => 'https://www.google.com/maps?q=' . $query . '&hl=fr&z=14&output=embed',
            'link' => 'https://www.google.com/maps/search/?api=1&query=' . $query,
        ];
    }

    private function isGoogleMapsLink($url) {
        return preg_match('#(google\.[^/]+|goo\.gl|maps\.app\.goo\.gl)#i', $url) === 1;
    }

    private function toEmbedMapsUrl($url) {
        if (strpos($url, 'output=embed') !== false) {
            return $url;
        }
        return $url . (strpos($url, '?') !== false ? '&output=embed' : '?output=embed');
    }

    private function createParticipation(Participation $participation) {
        $pdo = $this->db();
        $query = "INSERT INTO participations (id_evenement, id_user, nom_participant, email, telephone, statut_participation, mode_participation, feedback, note)
                  VALUES (:id_ev, :id_user, :nom, :email, :telephone, :statut, :mode, :feedback, :note)";
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':id_ev', (int) $participation->id_evenement, PDO::PARAM_INT);
        $stmt->bindValue(':id_user', (int) $participation->id_user, PDO::PARAM_INT);
        $stmt->bindValue(':nom', $participation->nom_participant);
        $stmt->bindValue(':email', $participation->email);
        $stmt->bindValue(':telephone', $participation->telephone);
        $stmt->bindValue(':statut', $participation->statut_participation);
        $stmt->bindValue(':mode', $participation->mode_participation);
        $stmt->bindValue(':feedback', $participation->feedback);
        $stmt->bindValue(':note', $participation->note);
        if ($stmt->execute()) {
            return (int) $pdo->lastInsertId();
        }
        return 0;
    }

    private function decrementEvenementPlaces($id) {
        $query = "UPDATE evenements
                  SET nb_places_disponibles = nb_places_disponibles - 1
                  WHERE id_evenement = :id AND nb_places_disponibles > 0";
        $stmt = $this->db()->prepare($query);
        $stmt->bindValue(':id', (int) $id, PDO::PARAM_INT);
        if (!$stmt->execute()) {
            return false;
        }
        return $stmt->rowCount() > 0;
    }

    private function isEmailAlreadyRegistered($eventId, $email) {
        $stmt = $this->db()->prepare(
            'SELECT 1 FROM participations WHERE id_evenement = :e AND LOWER(TRIM(email)) = :m LIMIT 1'
        );
        $stmt->bindValue(':e', (int) $eventId, PDO::PARAM_INT);
        $stmt->bindValue(':m', EmailValidationHelper::normalize($email));
        $stmt->execute();
        return (bool) $stmt->fetchColumn();
    }

    public function index() {
        $evenements = $this->getActiveEvenements();
        foreach ($evenements as &$row) {
            $row = $this->enrichEvenementRow($row);
        }
        unset($row);

        $calendarEvents = [];
        foreach ($evenements as $ev) {
            $calendarEvents[] = [
                'title' => $ev['titre'],
                'start' => $ev['date_debut'] . 'T' . trim($ev['heure_debut']),
                'end' => $ev['date_fin'] . 'T' . trim($ev['heure_fin']),
                'backgroundColor' => $ev['couleur'] ?: '#0d6efd',
                'borderColor' => $ev['couleur'] ?: '#0d6efd',
                'extendedProps' => [
                    'places' => (int) $ev['nb_places_disponibles'],
                    'capacite' => (int) $ev['capacite_max'],
                    'participants' => (int) $ev['nb_participants'],
                    'detailUrl' => BASE_URL . '/Home/detail/' . (int) $ev['id_evenement'],
                    'dateDebutLabel' => $this->formatCalendarDateTimeRangePart($ev['date_debut'], $ev['heure_debut']),
                    'dateFinLabel' => $this->formatCalendarDateTimeRangePart($ev['date_fin'], $ev['heure_fin']),
                    'dureeLabel' => $this->formatEventDurationLabel(
                        $ev['date_debut'],
                        $ev['heure_debut'],
                        $ev['date_fin'],
                        $ev['heure_fin']
                    ),
                ],
            ];
        }

        $mapLocations = $this->buildDistinctMapLocationsByVenue();

        $this->view('front/index', [
            'evenements' => $evenements,
            'calendar_events' => $calendarEvents,
            'map_locations' => $mapLocations,
            'flash' => $this->getFlash(),
        ]);
    }

    /** Lieux géographiques : événements actifs avec un lieu renseigné (pas d’entrée hors événement). */
    private function getMapEvenements() {
        $query = "SELECT e.*, c.nom_categorie, c.couleur
                  FROM evenements e
                  INNER JOIN categories c ON e.id_categorie = c.id_categorie AND c.statut = 'actif'
                  WHERE e.statut IN ('planifié', 'en cours')
                    AND TRIM(COALESCE(e.lieu, '')) <> ''
                  ORDER BY e.date_debut ASC";
        $stmt = $this->db()->prepare($query);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$row) {
            $row = $this->enrichEvenementRow($row);
        }
        unset($row);
        return $rows;
    }

    private function formatCalendarDateTimeRangePart($datePart, $timePart) {
        $datePart = trim((string) $datePart);
        $timePart = trim((string) $timePart);
        $ts = strtotime($datePart . ' ' . $timePart);
        if ($ts === false && $datePart !== '') {
            $ts = strtotime($datePart);
        }
        if ($ts === false) {
            return $datePart;
        }

        return date('d/m/Y', $ts) . ' · ' . date('H\hi', $ts);
    }

    /** Libellé de durée (vue mois calendrier) : jours + heures + minutes. */
    private function formatEventDurationLabel($dateDebut, $heureDebut, $dateFin, $heureFin) {
        $dateDebut = trim((string) $dateDebut);
        $heureDebut = trim((string) $heureDebut);
        $dateFin = trim((string) $dateFin);
        $heureFin = trim((string) $heureFin);
        $t0 = strtotime($dateDebut . ' ' . $heureDebut);
        $t1 = strtotime($dateFin . ' ' . $heureFin);
        if ($t0 === false || $t1 === false || $t1 <= $t0) {
            return '';
        }
        $totalMins = (int) (($t1 - $t0) / 60);
        $days = intdiv($totalMins, 1440);
        $rem = $totalMins % 1440;
        $hours = intdiv($rem, 60);
        $mins = $rem % 60;
        $parts = [];
        if ($days > 0) {
            $parts[] = $days . ' jour' . ($days > 1 ? 's' : '');
        }
        if ($hours > 0) {
            $parts[] = $hours . ' h';
        }
        if ($mins > 0 || empty($parts)) {
            $parts[] = $mins . ' min';
        }

        return 'Durée : ' . implode(' ', $parts);
    }

    /** Un même lieu physique = une entrée ; événements regroupés. */
    private function buildDistinctMapLocationsByVenue() {
        $byLieu = [];
        foreach ($this->getMapEvenements() as $ev) {
            if (empty($ev['maps_url'])) {
                continue;
            }
            $lieu = trim((string) ($ev['lieu'] ?? ''));
            if ($lieu === '') {
                continue;
            }

            $key = strtolower($lieu);
            $titreEvt = trim((string) ($ev['titre'] ?? ''));
            $d = (string) ($ev['date_debut'] ?? '');

            if (!isset($byLieu[$key])) {
                $byLieu[$key] = [
                    'lieu' => $lieu,
                    'titres' => ($titreEvt !== '') ? [$titreEvt] : [],
                    'date_debut' => $d,
                    'couleur' => (string) ($ev['couleur'] ?: '#0d6efd'),
                    'maps_url' => (string) $ev['maps_url'],
                    'maps_link' => (string) ($ev['maps_link'] ?? $ev['maps_url']),
                ];
                continue;
            }

            if ($titreEvt !== '' && !in_array($titreEvt, $byLieu[$key]['titres'], true)) {
                $byLieu[$key]['titres'][] = $titreEvt;
            }

            if ($d !== ''
                && ($byLieu[$key]['date_debut'] === '' || strcmp($d, $byLieu[$key]['date_debut']) < 0)) {
                $byLieu[$key]['date_debut'] = $d;
            }
        }

        $out = [];
        foreach ($byLieu as $m) {
            $out[] = [
                'lieu' => $m['lieu'],
                'titre_evenements' => implode(' · ', $m['titres']),
                'date_debut' => $m['date_debut'],
                'couleur' => $m['couleur'],
                'maps_url' => $m['maps_url'],
                'maps_link' => $m['maps_link'],
            ];
        }

        usort($out, static function ($a, $b) {
            return strcmp((string) $a['date_debut'], (string) $b['date_debut']);
        });

        return $out;
    }

    public function detail($id) {
        $record = $this->getEvenementById($id);

        if (!$record) {
            $this->setFlash('danger', "L'événement demandé est introuvable.");
            $this->redirect('/Home/index');
        }

        $record = $this->enrichEvenementRow($record);

        $this->view('front/detail', [
            'evenement' => $record,
            'flash' => $this->getFlash(),
        ]);
    }

    public function register($id) {
        $record = $this->getEvenementById($id);

        if (!$record) {
            $this->setFlash('danger', "L'événement demandé est introuvable.");
            $this->redirect('/Home/index');
        }

        $record = $this->enrichEvenementRow($record);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ((int) $record['nb_places_disponibles'] <= 0) {
                $msg = !empty($record['is_metiers_avances'])
                    ? 'Cet événement Métiers avancés est complet. Aucune inscription supplémentaire n\'est possible.'
                    : 'Cet événement est complet.';
                $this->setFlash('warning', $msg);
                $this->redirect('/Home/detail/' . (int) $id);
            }
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ((int) $record['nb_places_disponibles'] <= 0) {
                $msg = !empty($record['is_metiers_avances'])
                    ? 'Cet événement Métiers avancés est complet.'
                    : 'Il n\'y a plus de places disponibles.';
                $this->setFlash('danger', $msg);
                $this->redirect('/Home/detail/' . (int) $id);
            }

            $errors = $this->validateRegistration($_POST, (int) $id);
            if (!empty($errors)) {
                $this->view('front/register', [
                    'evenement' => $record,
                    'errors' => $errors,
                    'old' => $_POST,
                    'flash' => null,
                ]);
                return;
            }

            $participation = $this->model('Participation');
            $participation->id_evenement = (int) $id;
            $participation->id_user = 1;
            $participation->nom_participant = trim($_POST['nom_participant']);
            $participation->email = EmailValidationHelper::normalize(trim($_POST['email'] ?? ''));
            $participation->telephone = trim($_POST['telephone']);
            $participation->mode_participation = trim($_POST['mode_participation']);
            $participation->statut_participation = 'inscrit';
            $participation->feedback = '';
            $participation->note = null;

            $participationId = $this->createParticipation($participation);
            if ($participationId && $this->decrementEvenementPlaces($id)) {
                $this->setFlash('success', 'Votre inscription a été enregistrée.');
                $this->redirect('/Home/index');
            }

            $errors['general'] = "Une erreur est survenue pendant l'inscription.";
            $this->view('front/register', [
                'evenement' => $record,
                'errors' => $errors,
                'old' => $_POST,
                'flash' => null,
            ]);
            return;
        }

        $this->view('front/register', [
            'evenement' => $record,
            'errors' => [],
            'old' => [],
            'flash' => null,
        ]);
    }

    private function validateRegistration($input, $eventId) {
        $errors = [];

        $nom = trim($input['nom_participant'] ?? '');
        $email = EmailValidationHelper::normalize(trim($input['email'] ?? ''));
        $telephone = trim($input['telephone'] ?? '');
        $mode = trim($input['mode_participation'] ?? '');

        if (mb_strlen($nom) < 3) {
            $errors['nom_participant'] = 'Le nom doit contenir au moins 3 caracteres.';
        }

        if ($email === '' || !EmailValidationHelper::isValidParticipantGmail($email)) {
            $errors['email'] = 'Adresse Gmail invalide. Format attendu : nom-utilisateur@gmail.com (ex. jean-paul@gmail.com).';
        } elseif ($this->isEmailAlreadyRegistered($eventId, $email)) {
            $errors['email'] = 'Cette adresse est déjà inscrite pour cet événement.';
        }

        if (!preg_match('/^[0-9+\s]{8,20}$/', $telephone)) {
            $errors['telephone'] = 'Telephone invalide (8 chiffres minimum).';
        }

        if (!in_array($mode, ['en ligne', 'présentiel'], true)) {
            $errors['mode_participation'] = 'Mode de participation invalide.';
        }

        return $errors;
    }
}
?>

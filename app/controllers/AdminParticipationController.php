<?php
class AdminParticipationController extends Controller {
    public function index() {
        $participationModel = $this->model('Participation');
        $participations = $participationModel->read()->fetchAll(PDO::FETCH_ASSOC);
        $this->view('back/participations/index', [
            'participations' => $participations,
            'flash' => $this->getFlash()
        ]);
    }

    public function edit($id) {
        $participation = $this->model('Participation');
        $record = $participation->find((int) $id);
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
            $participation->nom_participant = $record['nom_participant'];
            $participation->email = $record['email'];
            $participation->telephone = $record['telephone'];
            $participation->mode_participation = $record['mode_participation'];
            $participation->statut_participation = trim($_POST['statut_participation']);
            $participation->feedback = trim($_POST['feedback']);
            $participation->note = $_POST['note'] === '' ? null : (int) $_POST['note'];

            if ($participation->update()) {
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
        $participation = $this->model('Participation');
        $participation->id_participation = (int) $id;
        if ($participation->delete()) {
            $this->setFlash('success', 'Participation supprimee.');
        } else {
            $this->setFlash('danger', 'Suppression impossible.');
        }
        $this->redirect('/AdminParticipation/index');
    }

    private function validateParticipation($input) {
        $errors = [];
        $statut = trim($input['statut_participation'] ?? '');
        $noteRaw = trim((string) ($input['note'] ?? ''));

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
}
?>

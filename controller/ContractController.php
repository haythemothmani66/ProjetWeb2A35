<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/model/Contract.php';
require_once dirname(__DIR__) . '/model/Partenaire.php';

require_once dirname(__DIR__) . '/api/MailHelper.php';

class ContractController
{
    private Contract $model;
    private Partenaire $partenaireModel;

    public function __construct()
    {
        $pdo = Config::getConnexion();
        $this->model = new Contract($pdo);
        $this->partenaireModel = new Partenaire($pdo);
    }

    public function handleRequest(string $action): void
    {
        $normalizedAction = strtolower(trim($action));

        switch ($normalizedAction) {
            case 'add':
            case 'create':
            case 'new':
                $this->showAddForm();
                return;

            case 'store':
            case 'save':
            case 'addcontract':
                $this->storeContract();
                return;

            case 'edit':
                $this->showUpdateForm();
                return;

            case 'update':
            case 'updatecontract':
                $this->updateContract();
                return;

            case 'delete':
            case 'deletecontract':
                $this->deleteContract();
                return;

            case 'verification':
                $this->showVerificationPage();
                return;

            case 'verify':
                $this->verifyContract();
                return;

            case 'list':
            case 'listcontracts':
            default:
                $this->listContracts();
                return;
        }
    }

    private function listContracts(): void
    {
        $contracts = $this->model->listContracts();

        $this->render('BackOffice/contract/listContract.php', [
            'pageTitle' => 'List Contracts',
            'contracts' => $contracts,
            'messages' => pullFlashMessages(),
        ]);
    }

    private function showAddForm(): void
    {
        $partners = $this->partenaireModel->listPartenaires();

        $this->render('BackOffice/contract/addContract.php', [
            'pageTitle' => 'Add Contract',
            'partners' => $partners,
            'oldInput' => pullOldInput(),
            'messages' => pullFlashMessages(),
        ]);
    }

    private function storeContract(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirectTo(['controller' => 'contract', 'action' => 'add']);
        }

        $input = $this->collectInput();
        $errors = $this->validateInput($input, false);

        if (!empty($errors)) {
            rememberOldInput($input);
            addFlashMessage('danger', implode(' ', $errors));
            redirectTo(['controller' => 'contract', 'action' => 'add']);
        }

        try {
            $pdfPath = $this->handlePdfUpload($_FILES['document_pdf'] ?? null, null, false);

            $entity = new Contract(getConnexion());
            $entity->setContractRef($input['contract_ref']);
            $entity->setCompanyName($input['company_name']);
            $entity->setTypeContrat($input['type_contrat']);
            $entity->setStatut($input['statut']);
            $entity->setDateDebut($input['date_debut']);
            $entity->setDateFin($input['date_fin']);
            $entity->setDateSignature($input['date_signature']);
            $entity->setRenouvellementAuto($input['renouvellement_auto']);
            $entity->setDetails($input['details']);
            $entity->setPdfFileName($pdfPath);
            $entity->setAuthKeyHash($this->buildAuthHash($input['auth_key'] ?? ''));

            if ($entity->addContract()) {
                addFlashMessage('success', 'Contract saved successfully.');
                redirectTo(['controller' => 'contract', 'action' => 'list']);
            }

            rememberOldInput($input);
            addFlashMessage('danger', 'Unable to save contract.');
            redirectTo(['controller' => 'contract', 'action' => 'add']);
        } catch (Throwable $exception) {
            rememberOldInput($input);
            addFlashMessage('danger', 'Error while saving contract: ' . $exception->getMessage());
            redirectTo(['controller' => 'contract', 'action' => 'add']);
        }
    }

    private function showUpdateForm(): void
    {
        $id = $this->getRequestedId();
        if ($id <= 0) {
            addFlashMessage('warning', 'Invalid contract identifier.');
            redirectTo(['controller' => 'contract', 'action' => 'list']);
        }

        $contract = $this->model->findContractById($id);
        if ($contract === null) {
            addFlashMessage('warning', 'Contract not found.');
            redirectTo(['controller' => 'contract', 'action' => 'list']);
        }

        $oldInput = pullOldInput();
        if (!empty($oldInput)) {
            $contract = array_merge($contract, $oldInput);
        }

        $partners = $this->partenaireModel->listPartenaires();

        $this->render('BackOffice/contract/updateContract.php', [
            'pageTitle' => 'Update Contract',
            'contract' => $contract,
            'partners' => $partners,
            'messages' => pullFlashMessages(),
        ]);
    }

    private function updateContract(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirectTo(['controller' => 'contract', 'action' => 'list']);
        }

        $id = $this->getRequestedId();
        if ($id <= 0) {
            addFlashMessage('warning', 'Invalid contract identifier.');
            redirectTo(['controller' => 'contract', 'action' => 'list']);
        }

        $existing = $this->model->findContractById($id);
        if ($existing === null) {
            addFlashMessage('warning', 'Contract not found.');
            redirectTo(['controller' => 'contract', 'action' => 'list']);
        }

        $input = $this->collectInput();
        $errors = $this->validateInput($input, true);

        if (!empty($errors)) {
            rememberOldInput($input);
            addFlashMessage('danger', implode(' ', $errors));
            redirectTo(['controller' => 'contract', 'action' => 'edit', 'id' => $id]);
        }

        try {
            $pdfPath = $this->handlePdfUpload($_FILES['document_pdf'] ?? null, $existing['pdf_file_name'] ?? null, true);

            $entity = new Contract(getConnexion());
            $entity->setContractRef($input['contract_ref'] !== '' ? $input['contract_ref'] : (string)($existing['contract_ref'] ?? ''));
            $entity->setCompanyName($input['company_name']);
            $entity->setTypeContrat($input['type_contrat']);
            $entity->setStatut($input['statut']);
            $entity->setDateDebut($input['date_debut']);
            $entity->setDateFin($input['date_fin']);
            $entity->setDateSignature($input['date_signature']);
            $entity->setRenouvellementAuto($input['renouvellement_auto']);
            $entity->setDetails($input['details']);
            $entity->setPdfFileName($pdfPath);

            $authHash = $existing['auth_key_hash'] ?? null;
            if (trim((string)($input['auth_key'] ?? '')) !== '') {
                $authHash = $this->buildAuthHash($input['auth_key']);
            }
            $entity->setAuthKeyHash($authHash);

            if ($entity->updateContract($id)) {
                addFlashMessage('success', 'Contract updated successfully.');
                redirectTo(['controller' => 'contract', 'action' => 'list']);
            }

            rememberOldInput($input);
            addFlashMessage('danger', 'Unable to update contract.');
            redirectTo(['controller' => 'contract', 'action' => 'edit', 'id' => $id]);
        } catch (Throwable $exception) {
            rememberOldInput($input);
            addFlashMessage('danger', 'Error while updating contract: ' . $exception->getMessage());
            redirectTo(['controller' => 'contract', 'action' => 'edit', 'id' => $id]);
        }
    }

    private function deleteContract(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirectTo(['controller' => 'contract', 'action' => 'list']);
        }

        $id = $this->getRequestedId();
        if ($id <= 0) {
            addFlashMessage('warning', 'Invalid contract identifier.');
            redirectTo(['controller' => 'contract', 'action' => 'list']);
        }

        $contract = $this->model->findContractById($id);
        if ($contract === null) {
            addFlashMessage('warning', 'Contract not found.');
            redirectTo(['controller' => 'contract', 'action' => 'list']);
        }

        if ($this->model->deleteContract($id)) {
            $this->deleteUploadedFile($contract['pdf_file_name'] ?? null);
            addFlashMessage('success', 'Contract deleted successfully.');
        } else {
            addFlashMessage('danger', 'Unable to delete contract.');
        }

        redirectTo(['controller' => 'contract', 'action' => 'list']);
    }

    private function showVerificationPage(): void
    {
        $contracts = $this->model->listContracts();

        $this->render('BackOffice/contract/verificationContract.php', [
            'pageTitle' => 'Verify Contracts',
            'contracts' => $contracts,
            'messages' => pullFlashMessages(),
        ]);
    }

    private function verifyContract(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirectTo(['controller' => 'contract', 'action' => 'verification']);
        }

        $id = $this->getRequestedId();
        $status = trim((string)($_POST['status'] ?? ''));

        if (!in_array($status, ['Actif', 'Expire', 'Expiré', 'Suspendu', 'Rejeté'], true)) {
            addFlashMessage('danger', 'Invalid contract status.');
            redirectTo(['controller' => 'contract', 'action' => 'verification']);
        }

        if ($id <= 0) {
            addFlashMessage('danger', 'Invalid contract identifier.');
            redirectTo(['controller' => 'contract', 'action' => 'verification']);
        }

        if ($this->model->setStatusById($id, $status)) {
            addFlashMessage('success', 'Contract status updated successfully.');
            
            // If the contract is approved (Active), send an email to the partner
            if (in_array($status, ['Actif', 'Active'], true)) {
                $contract = $this->model->findContractById($id);
                if ($contract && !empty($contract['company_name'])) {
                    $partner = $this->partenaireModel->findPartenaireByName($contract['company_name']);
                    if ($partner && !empty($partner['email'])) {
                        error_log("Triggering approval email for: " . $partner['email']);
                        MailHelper::sendContractFinalizedEmail(
                            $partner['email'],
                            $partner['organization_name'],
                            $contract['contract_ref'] ?? "CTR-$id"
                        );
                    } else {
                        error_log("Partner not found or email empty for company: " . ($contract['company_name'] ?? 'N/A'));
                    }
                }
            }
            // If the contract is rejected or suspended
            elseif (in_array($status, ['Rejeté', 'Suspendu'], true)) {
                $contract = $this->model->findContractById($id);
                if ($contract && !empty($contract['company_name'])) {
                    $partner = $this->partenaireModel->findPartenaireByName($contract['company_name']);
                    if ($partner && !empty($partner['email'])) {
                        error_log("Triggering rejection email for: " . $partner['email']);
                        MailHelper::sendContractRejectedEmail(
                            $partner['email'],
                            $partner['organization_name'],
                            $contract['contract_ref'] ?? "CTR-$id"
                        );
                    } else {
                        error_log("Partner not found or email empty for company: " . ($contract['company_name'] ?? 'N/A'));
                    }
                }
            }
        } else {
            addFlashMessage('danger', 'Unable to update contract status.');
        }

        redirectTo(['controller' => 'contract', 'action' => 'verification']);
    }

    private function collectInput(): array
    {
        return [
            'contract_ref' => trim((string)($_POST['contract_ref'] ?? '')),
            'company_name' => trim((string)($_POST['company_name'] ?? '')),
            'type_contrat' => trim((string)($_POST['type_contrat'] ?? '')),
            'statut' => trim((string)($_POST['statut'] ?? 'Actif')),
            'date_debut' => trim((string)($_POST['date_debut'] ?? '')),
            'date_fin' => trim((string)($_POST['date_fin'] ?? '')),
            'date_signature' => trim((string)($_POST['date_signature'] ?? '')),
            'renouvellement_auto' => trim((string)($_POST['renouvellement_auto'] ?? '')),
            'details' => trim((string)($_POST['details'] ?? '')),
            'auth_key' => trim((string)($_POST['auth_key'] ?? '')),
        ];
    }

    private function validateInput(array $input, bool $isUpdate): array
    {
        $errors = [];

        if ($input['company_name'] === '' || strlen($input['company_name']) < 2) {
            $errors[] = 'Company name must contain at least 2 characters.';
        }

        if ($input['type_contrat'] === '') {
            $errors[] = 'Contract type is required.';
        }

        if ($input['statut'] === '') {
            $errors[] = 'Contract status is required.';
        }

        if ($input['date_debut'] === '') {
            $errors[] = 'Start date is required.';
        }

        if ($input['date_fin'] === '') {
            $errors[] = 'End date is required.';
        }

        if ($input['date_signature'] === '') {
            $errors[] = 'Signature date is required.';
        }

        if ($input['renouvellement_auto'] === '' || !in_array($input['renouvellement_auto'], ['oui', 'non'], true)) {
            $errors[] = 'Auto renewal must be yes or no.';
        }

        if ($input['details'] === '' || strlen($input['details']) < 15) {
            $errors[] = 'Contract details must contain at least 15 characters.';
        }

        if ($input['date_debut'] !== '' && $input['date_fin'] !== '' && $input['date_fin'] < $input['date_debut']) {
            $errors[] = 'End date must be after or equal to start date.';
        }

        if ($input['date_signature'] !== '' && $input['date_fin'] !== '' && $input['date_signature'] > $input['date_fin']) {
            $errors[] = 'Signature date must not be after end date.';
        }

        if (!$isUpdate && $input['auth_key'] === '') {
            $errors[] = 'Authentication key is required when creating a contract.';
        }

        return $errors;
    }

    private function handlePdfUpload(?array $file, ?string $currentFile, bool $isUpdate): ?string
    {
        if ($file === null || !isset($file['error'])) {
            return $currentFile;
        }

        if ((int)$file['error'] === UPLOAD_ERR_NO_FILE) {
            return $currentFile;
        }

        if ((int)$file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('PDF upload failed.');
        }

        $maxSize = 5 * 1024 * 1024;
        if ((int)$file['size'] > $maxSize) {
            throw new RuntimeException('PDF file must not exceed 5MB.');
        }

        $extension = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
        if ($extension !== 'pdf') {
            throw new RuntimeException('Only PDF documents are allowed.');
        }

        $uploadDir = dirname(__DIR__) . '/uploads/contracts';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
            throw new RuntimeException('Unable to create PDF upload directory.');
        }

        $targetFileName = sprintf('contract_%s.pdf', bin2hex(random_bytes(8)));
        $targetPath = $uploadDir . '/' . $targetFileName;

        if (!move_uploaded_file((string)$file['tmp_name'], $targetPath)) {
            throw new RuntimeException('Unable to move uploaded PDF file.');
        }

        if ($isUpdate && $currentFile !== null && $currentFile !== '') {
            $this->deleteUploadedFile($currentFile);
        }

        return 'uploads/contracts/' . $targetFileName;
    }

    private function buildAuthHash(string $rawKey): ?string
    {
        $normalized = trim($rawKey);
        if ($normalized === '') {
            return null;
        }

        return hash('sha256', $normalized);
    }

    private function deleteUploadedFile(?string $relativePath): void
    {
        if ($relativePath === null || $relativePath === '') {
            return;
        }

        $normalizedRelative = str_replace('\\', '/', ltrim($relativePath, '/'));
        if (strpos($normalizedRelative, 'uploads/contracts/') !== 0) {
            return;
        }

        $fullPath = dirname(__DIR__) . '/' . $normalizedRelative;
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    private function getRequestedId(): int
    {
        $source = $_POST['id'] ?? $_GET['id'] ?? 0;
        return (int)$source;
    }

    private function render(string $viewPath, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        require dirname(__DIR__) . '/View/' . $viewPath;
    }
}

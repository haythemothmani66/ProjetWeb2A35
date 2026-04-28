<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/Model/Partenaire.php';

class PartenaireController
{
    private Partenaire $model;

    public function __construct()
    {
        $this->model = new Partenaire(getConnexion());
    }

    public function handleRequest(string $action): void
    {
        $normalizedAction = strtolower(trim($action));

        // Frontend Actions
        switch ($normalizedAction) {
            case 'preview':
            case 'partnerspreview':
                $this->showPartnersPreview();
                return;

            case 'viewall':
            case 'allpartners':
                $this->showAllPartners();
                return;

            case 'apply':
            case 'applyform':
            case 'partnerapplication':
                $this->showPartnershipForm();
                return;

            case 'submitapplication':
            case 'submitpartnership':
                $this->submitPartnershipApplication();
                return;
        }

        // Backend Actions
        switch ($normalizedAction) {
            case 'add':
            case 'create':
            case 'new':
                $this->showAddForm();
                return;

            case 'store':
            case 'save':
            case 'addpartenaire':
                $this->storePartenaire();
                return;

            case 'edit':
                $this->showUpdateForm();
                return;

            case 'update':
            case 'updatepartenaire':
                $this->updatePartenaire();
                return;

            case 'delete':
            case 'deletepartenaire':
                $this->deletePartenaire();
                return;

            case 'verification':
                $this->showVerificationPage();
                return;

            case 'verify':
                $this->verifyPartenaire();
                return;

            case 'list':
            case 'listpartenaires':
            default:
                $this->listPartenaires();
                return;
        }
    }

    private function listPartenaires(): void
    {
        $partners = $this->model->listPartenaires();

        $this->render('BackOffice/partenaire/listPartenaire.php', [
            'pageTitle' => 'List Partenaires',
            'partners' => $partners,
            'messages' => pullFlashMessages(),
        ]);
    }

    private function showAddForm(): void
    {
        $this->render('BackOffice/partenaire/addPartenaire.php', [
            'pageTitle' => 'Add Partenaire',
            'oldInput' => pullOldInput(),
            'messages' => pullFlashMessages(),
        ]);
    }

    private function storePartenaire(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirectTo(['controller' => 'partenaire', 'action' => 'add']);
        }

        $input = $this->collectInput();
        $errors = $this->validateInput($input, false);

        if (!empty($errors)) {
            rememberOldInput($input);
            addFlashMessage('danger', implode(' ', $errors));
            redirectTo(['controller' => 'partenaire', 'action' => 'add']);
        }

        try {
            $logoPath = $this->handleLogoUpload($_FILES['logo'] ?? null, null, false);

            $entity = new Partenaire(getConnexion());
            $entity->setOrganizationName($input['organization_name']);
            $entity->setPartnerType($input['partner_type']);
            $entity->setEmail($input['email']);
            $entity->setTelephone($input['telephone']);
            $entity->setAddress($input['address']);
            $entity->setCountry($input['country']);
            $entity->setDomain($input['domain']);
            $entity->setLogo($logoPath);
            $entity->setDescription($input['description']);
            $entity->setStatus($input['status'] ?: 'pending');
            $entity->setAuthKeyHash($this->buildAuthHash($input['auth_key'] ?? ''));

            if ($entity->addPartenaire()) {
                addFlashMessage('success', 'Partner request saved successfully.');
                redirectTo(['controller' => 'partenaire', 'action' => 'list']);
            }

            rememberOldInput($input);
            addFlashMessage('danger', 'Unable to save partner request.');
            redirectTo(['controller' => 'partenaire', 'action' => 'add']);
        } catch (Throwable $exception) {
            rememberOldInput($input);
            addFlashMessage('danger', 'Error while saving partner request: ' . $exception->getMessage());
            redirectTo(['controller' => 'partenaire', 'action' => 'add']);
        }
    }

    private function showUpdateForm(): void
    {
        $id = $this->getRequestedId();
        if ($id <= 0) {
            addFlashMessage('warning', 'Invalid partner identifier.');
            redirectTo(['controller' => 'partenaire', 'action' => 'list']);
        }

        $partner = $this->model->findPartenaireById($id);
        if ($partner === null) {
            addFlashMessage('warning', 'Partner not found.');
            redirectTo(['controller' => 'partenaire', 'action' => 'list']);
        }

        $oldInput = pullOldInput();
        if (!empty($oldInput)) {
            $partner = array_merge($partner, $oldInput);
        }

        $this->render('BackOffice/partenaire/updatePartenaire.php', [
            'pageTitle' => 'Update Partenaire',
            'partner' => $partner,
            'messages' => pullFlashMessages(),
        ]);
    }

    private function updatePartenaire(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirectTo(['controller' => 'partenaire', 'action' => 'list']);
        }

        $id = $this->getRequestedId();
        if ($id <= 0) {
            addFlashMessage('warning', 'Invalid partner identifier.');
            redirectTo(['controller' => 'partenaire', 'action' => 'list']);
        }

        $existing = $this->model->findPartenaireById($id);
        if ($existing === null) {
            addFlashMessage('warning', 'Partner not found.');
            redirectTo(['controller' => 'partenaire', 'action' => 'list']);
        }

        $input = $this->collectInput();
        $errors = $this->validateInput($input, true);

        if (!empty($errors)) {
            rememberOldInput($input);
            addFlashMessage('danger', implode(' ', $errors));
            redirectTo(['controller' => 'partenaire', 'action' => 'edit', 'id' => $id]);
        }

        try {
            $logoPath = $this->handleLogoUpload($_FILES['logo'] ?? null, $existing['logo'] ?? null, true);

            $entity = new Partenaire(getConnexion());
            $entity->setOrganizationName($input['organization_name']);
            $entity->setPartnerType($input['partner_type']);
            $entity->setEmail($input['email']);
            $entity->setTelephone($input['telephone']);
            $entity->setAddress($input['address']);
            $entity->setCountry($input['country']);
            $entity->setDomain($input['domain']);
            $entity->setLogo($logoPath);
            $entity->setDescription($input['description']);
            $entity->setStatus($input['status'] ?: (string)($existing['status'] ?? 'pending'));

            $authHash = $existing['auth_key_hash'] ?? null;
            if (trim((string)($input['auth_key'] ?? '')) !== '') {
                $authHash = $this->buildAuthHash($input['auth_key']);
            }
            $entity->setAuthKeyHash($authHash);

            if ($entity->updatePartenaire($id)) {
                addFlashMessage('success', 'Partner request updated successfully.');
                redirectTo(['controller' => 'partenaire', 'action' => 'list']);
            }

            rememberOldInput($input);
            addFlashMessage('danger', 'Unable to update partner request.');
            redirectTo(['controller' => 'partenaire', 'action' => 'edit', 'id' => $id]);
        } catch (Throwable $exception) {
            rememberOldInput($input);
            addFlashMessage('danger', 'Error while updating partner request: ' . $exception->getMessage());
            redirectTo(['controller' => 'partenaire', 'action' => 'edit', 'id' => $id]);
        }
    }

    private function deletePartenaire(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirectTo(['controller' => 'partenaire', 'action' => 'list']);
        }

        $id = $this->getRequestedId();
        if ($id <= 0) {
            addFlashMessage('warning', 'Invalid partner identifier.');
            redirectTo(['controller' => 'partenaire', 'action' => 'list']);
        }

        $partner = $this->model->findPartenaireById($id);
        if ($partner === null) {
            addFlashMessage('warning', 'Partner not found.');
            redirectTo(['controller' => 'partenaire', 'action' => 'list']);
        }

        if ($this->model->deletePartenaire($id)) {
            $this->deleteUploadedFile($partner['logo'] ?? null);
            addFlashMessage('success', 'Partner request deleted successfully.');
        } else {
            addFlashMessage('danger', 'Unable to delete partner request.');
        }

        redirectTo(['controller' => 'partenaire', 'action' => 'list']);
    }

    private function showVerificationPage(): void
    {
        $allPartners = $this->model->listPartenaires();
        $pendingPartners = array_values(array_filter($allPartners, static function (array $partner): bool {
            return strtolower((string)($partner['status'] ?? 'pending')) === 'pending';
        }));

        $this->render('BackOffice/partenaire/verificationPartenaire.php', [
            'pageTitle' => 'Verify Partenaires',
            'partners' => $pendingPartners,
            'messages' => pullFlashMessages(),
        ]);
    }

    private function verifyPartenaire(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirectTo(['controller' => 'partenaire', 'action' => 'verification']);
        }

        $id = $this->getRequestedId();
        $status = strtolower(trim((string)($_POST['status'] ?? '')));

        if (!in_array($status, ['approved', 'rejected', 'pending'], true)) {
            addFlashMessage('danger', 'Invalid verification status.');
            redirectTo(['controller' => 'partenaire', 'action' => 'verification']);
        }

        if ($id <= 0) {
            addFlashMessage('danger', 'Invalid partner identifier.');
            redirectTo(['controller' => 'partenaire', 'action' => 'verification']);
        }

        if ($this->model->setStatusById($id, $status)) {
            addFlashMessage('success', 'Partner status updated successfully.');
        } else {
            addFlashMessage('danger', 'Unable to update partner status.');
        }

        redirectTo(['controller' => 'partenaire', 'action' => 'verification']);
    }

    /**
     * Frontend: Show partner preview on homepage (featured partners)
     */
    private function showPartnersPreview(): void
    {
        $partners = $this->model->getApprovedPartnersForDisplay(6); // Show 6 partners max

        $this->render('FrontOffice/partenaire/partnersPreview.php', [
            'pageTitle' => 'Partners Preview',
            'partners' => $partners,
        ]);
    }

    /**
     * Frontend: Show all approved partners
     */
    private function showAllPartners(): void
    {
        $partners = $this->model->getAllApprovedPartners();

        $this->render('FrontOffice/partenaire/allPartners.php', [
            'pageTitle' => 'All Partners',
            'partners' => $partners,
        ]);
    }

    /**
     * Frontend: Show partnership application form (combined request + contract)
     */
    private function showPartnershipForm(): void
    {
        $this->render('FrontOffice/partenaire/partnershipForm.php', [
            'pageTitle' => 'Apply as Partner',
            'oldInput' => pullOldInput(),
            'messages' => pullFlashMessages(),
        ]);
    }

    /**
     * Frontend: Handle partnership application submission
     */
    private function submitPartnershipApplication(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirectTo(['controller' => 'partenaire', 'action' => 'apply']);
        }

        $input = $this->collectFrontendInput();
        $errors = $this->validateFrontendInput($input);

        if (!empty($errors)) {
            rememberOldInput($input);
            addFlashMessage('danger', implode(' | ', $errors));
            redirectTo(['controller' => 'partenaire', 'action' => 'apply']);
        }

        try {
            $logoPath = $this->handleLogoUpload($_FILES['logo'] ?? null, null, false);
            
            $this->model->setOrganizationName($input['organization_name']);
            $this->model->setPartnerType($input['partner_type']);
            $this->model->setEmail($input['email']);
            $this->model->setTelephone($input['telephone']);
            $this->model->setAddress($input['address'] ?? null);
            $this->model->setCountry($input['country'] ?? null);
            $this->model->setDomain($input['domain'] ?? null);
            $this->model->setLogo($logoPath);
            $this->model->setDescription($input['description'] ?? null);
            $this->model->setStatus('pending');

            if ($this->model->addPartenaire()) {
                addFlashMessage('success', 'Your partnership application has been submitted successfully. We will review it shortly.');
                redirectTo(['controller' => 'partenaire', 'action' => 'apply']);
            } else {
                rememberOldInput($input);
                addFlashMessage('danger', 'Failed to submit your partnership application. Please try again.');
                redirectTo(['controller' => 'partenaire', 'action' => 'apply']);
            }
        } catch (Throwable $exception) {
            rememberOldInput($input);
            addFlashMessage('danger', 'Error submitting partnership application: ' . $exception->getMessage());
            redirectTo(['controller' => 'partenaire', 'action' => 'apply']);
        }
    }

    /**
     * Collect input from frontend partnership form
     */
    private function collectFrontendInput(): array
    {
        return [
            'organization_name' => trim((string)($_POST['organization_name'] ?? '')),
            'partner_type' => trim((string)($_POST['partner_type'] ?? '')),
            'email' => trim((string)($_POST['email'] ?? '')),
            'telephone' => trim((string)($_POST['telephone'] ?? '')),
            'address' => trim((string)($_POST['address'] ?? '')),
            'country' => trim((string)($_POST['country'] ?? '')),
            'domain' => trim((string)($_POST['domain'] ?? '')),
            'description' => trim((string)($_POST['description'] ?? '')),
        ];
    }

    /**
     * Validate frontend partnership form input
     */
    private function validateFrontendInput(array $input): array
    {
        $errors = [];

        if (empty($input['organization_name'])) {
            $errors[] = 'Organization name is required.';
        } elseif (strlen($input['organization_name']) < 2) {
            $errors[] = 'Organization name must be at least 2 characters.';
        }

        if (empty($input['partner_type'])) {
            $errors[] = 'Partner type is required.';
        }

        if (empty($input['email'])) {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email is invalid.';
        }

        if (empty($input['telephone'])) {
            $errors[] = 'Telephone is required.';
        } elseif (strlen($input['telephone']) < 5) {
            $errors[] = 'Telephone must be at least 5 characters.';
        }

        if (empty($input['description'])) {
            $errors[] = 'Description is required.';
        } elseif (strlen($input['description']) < 10) {
            $errors[] = 'Description must be at least 10 characters.';
        }

        return $errors;
    }

    private function collectInput(): array
    {
        return [
            'organization_name' => trim((string)($_POST['organization_name'] ?? '')),
            'partner_type' => trim((string)($_POST['partner_type'] ?? '')),
            'email' => trim((string)($_POST['email'] ?? '')),
            'telephone' => trim((string)($_POST['telephone'] ?? '')),
            'address' => trim((string)($_POST['address'] ?? '')),
            'country' => trim((string)($_POST['country'] ?? '')),
            'domain' => trim((string)($_POST['domain'] ?? '')),
            'description' => trim((string)($_POST['description'] ?? '')),
            'status' => trim((string)($_POST['status'] ?? 'pending')),
            'auth_key' => trim((string)($_POST['auth_key'] ?? '')),
        ];
    }

    private function validateInput(array $input, bool $isUpdate): array
    {
        $errors = [];

        if ($input['organization_name'] === '' || strlen($input['organization_name']) < 3) {
            $errors[] = 'Organization name must contain at least 3 characters.';
        }

        if ($input['partner_type'] === '') {
            $errors[] = 'Partner type is required.';
        }

        if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email address is required.';
        }

        if (!preg_match('/^\d{7,15}$/', $input['telephone'])) {
            $errors[] = 'Telephone must contain 7 to 15 digits.';
        }

        if ($input['address'] !== '' && strlen($input['address']) < 5) {
            $errors[] = 'Address must contain at least 5 characters when provided.';
        }

        if ($input['country'] !== '' && strlen($input['country']) < 2) {
            $errors[] = 'Country must contain at least 2 characters when provided.';
        }

        if ($input['domain'] !== '' && filter_var($input['domain'], FILTER_VALIDATE_URL) === false) {
            $errors[] = 'Website domain must be a valid URL.';
        }

        if ($input['description'] !== '' && strlen($input['description']) < 10) {
            $errors[] = 'Description must contain at least 10 characters when provided.';
        }

        if (!$isUpdate && $input['auth_key'] === '') {
            $errors[] = 'Authentication key is required when creating a partner request.';
        }

        return $errors;
    }

    private function handleLogoUpload(?array $file, ?string $currentLogo, bool $isUpdate): ?string
    {
        if ($file === null || !isset($file['error'])) {
            return $currentLogo;
        }

        if ((int)$file['error'] === UPLOAD_ERR_NO_FILE) {
            return $currentLogo;
        }

        if ((int)$file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Logo upload failed.');
        }

        $maxSize = 2 * 1024 * 1024;
        if ((int)$file['size'] > $maxSize) {
            throw new RuntimeException('Logo file must not exceed 2MB.');
        }

        $extension = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'];
        if (!in_array($extension, $allowedExtensions, true)) {
            throw new RuntimeException('Logo must be an image format (png, jpg, jpeg, gif, webp, svg).');
        }

        $uploadDir = dirname(__DIR__) . '/assets/uploads/partners';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
            throw new RuntimeException('Unable to create logo upload directory.');
        }

        $targetFileName = sprintf('partner_%s.%s', bin2hex(random_bytes(8)), $extension);
        $targetPath = $uploadDir . '/' . $targetFileName;

        if (!move_uploaded_file((string)$file['tmp_name'], $targetPath)) {
            throw new RuntimeException('Unable to move uploaded logo file.');
        }

        if ($isUpdate && $currentLogo !== null && $currentLogo !== '') {
            $this->deleteUploadedFile($currentLogo);
        }

        return 'assets/uploads/partners/' . $targetFileName;
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
        if (strpos($normalizedRelative, 'assets/uploads/partners/') !== 0) {
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

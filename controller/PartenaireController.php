<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/model/Partenaire.php';
require_once dirname(__DIR__) . '/api/MailHelper.php';
require_once dirname(__DIR__) . '/api/RecommendationService.php';

class PartenaireController
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = getConnexion();
        $this->ensurePartenaireTable();
    }

    private function ensurePartenaireTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `partenaires` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `client_id` INT NULL,
            `organization_name` VARCHAR(255) NOT NULL,
            `partner_type` VARCHAR(100) NOT NULL,
            `email` VARCHAR(255) NOT NULL,
            `telephone` VARCHAR(50) NOT NULL,
            `address` VARCHAR(255) DEFAULT NULL,
            `country` VARCHAR(100) DEFAULT NULL,
            `domain` VARCHAR(255) DEFAULT NULL,
            `logo` VARCHAR(255) DEFAULT NULL,
            `description` TEXT DEFAULT NULL,
            `status` VARCHAR(50) NOT NULL DEFAULT 'pending',
            `auth_key_hash` CHAR(64) DEFAULT NULL,
            `embedding_vector` TEXT DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `uniq_partenaires_client_id` (`client_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $this->pdo->exec($sql);
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

            case 'recommendations':
            case 'smartrecommendations':
            case 'partnersyoumaylike':
                $this->showRecommendations();
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
        $partners = $this->getAllPartenaires();

        $this->render('backoffice/partenaire/listPartenaire.php', [
            'pageTitle' => 'List Partenaires',
            'partners' => $partners,
            'messages' => pullFlashMessages(),
        ]);
    }

    private function getAllPartenaires(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM `partenaires` ORDER BY `created_at` DESC, `id` DESC');
        return $stmt->fetchAll();
    }

    private function findPartenaireById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM `partenaires` WHERE `id` = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    private function deletePartenaireRecord(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM `partenaires` WHERE `id` = :id');
        return $stmt->execute([':id' => $id]);
    }

    private function updatePartenaireRecord(int $id, Partenaire $partenaire): bool
    {
        $sql = 'UPDATE `partenaires` SET
            `organization_name` = :organization_name,
            `partner_type` = :partner_type,
            `email` = :email,
            `telephone` = :telephone,
            `address` = :address,
            `country` = :country,
            `domain` = :domain,
            `logo` = :logo,
            `description` = :description,
            `status` = :status,
            `auth_key_hash` = :auth_key_hash,
            `embedding_vector` = :embedding_vector,
            `updated_at` = NOW()
        WHERE `id` = :id';

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':organization_name' => $partenaire->getOrganizationName(),
            ':partner_type' => $partenaire->getPartnerType(),
            ':email' => $partenaire->getEmail(),
            ':telephone' => $partenaire->getTelephone(),
            ':address' => $partenaire->getAddress(),
            ':country' => $partenaire->getCountry(),
            ':domain' => $partenaire->getDomain(),
            ':logo' => $partenaire->getLogo(),
            ':description' => $partenaire->getDescription(),
            ':status' => $partenaire->getStatus(),
            ':auth_key_hash' => $partenaire->getAuthKeyHash(),
            ':embedding_vector' => $partenaire->getEmbeddingVector(),
            ':id' => $id,
        ]);
    }

    private function updatePartenaireStatus(int $id, string $status): bool
    {
        $stmt = $this->pdo->prepare('UPDATE `partenaires` SET `status` = :status, `updated_at` = NOW() WHERE `id` = :id');
        return $stmt->execute([
            ':status' => $status,
            ':id' => $id,
        ]);
    }

    private function getNextClientId(): int
    {
        $stmt = $this->pdo->query('SELECT COALESCE(MAX(`client_id`), 0) + 1 AS next_id FROM `partenaires`');
        $nextId = $stmt->fetchColumn();

        return max(1, (int)$nextId);
    }

    private function showAddForm(): void
    {
        $this->render('backoffice/partenaire/addPartenaire.php', [
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
            $clientId = $this->getNextClientId();

            $partenaire = new Partenaire(
                null, // id
                $clientId,
                $input['organization_name'],
                $input['partner_type'],
                $input['email'],
                $input['telephone'],
                $input['address'] ?? null,
                $input['country'] ?? null,
                $input['domain'] ?? null,
                $logoPath,
                $input['description'] ?? null,
                $input['status'] ?: 'pending',
                $this->buildAuthHash($input['auth_key'] ?? ''),
                null,
                RecommendationService::generateEmbedding($input)
            );

            $sql = 'INSERT INTO `partenaires` (
                `client_id`, `organization_name`, `partner_type`, `email`, `telephone`, `address`,
                `country`, `domain`, `logo`, `description`, `status`, `auth_key_hash`, `embedding_vector`, `created_at`, `updated_at`
            ) VALUES (
                :client_id, :organization_name, :partner_type, :email, :telephone, :address,
                :country, :domain, :logo, :description, :status, :auth_key_hash, :embedding_vector, NOW(), NOW()
            )';

            $stmt = $this->pdo->prepare($sql);
            $success = $stmt->execute([
                ':client_id' => $partenaire->getClientId(),
                ':organization_name' => $partenaire->getOrganizationName(),
                ':partner_type' => $partenaire->getPartnerType(),
                ':email' => $partenaire->getEmail(),
                ':telephone' => $partenaire->getTelephone(),
                ':address' => $partenaire->getAddress(),
                ':country' => $partenaire->getCountry(),
                ':domain' => $partenaire->getDomain(),
                ':logo' => $partenaire->getLogo(),
                ':description' => $partenaire->getDescription(),
                ':status' => $partenaire->getStatus(),
                ':auth_key_hash' => $partenaire->getAuthKeyHash(),
                ':embedding_vector' => $partenaire->getEmbeddingVector(),
            ]);

            if ($success) {
                // Send pending confirmation email
                MailHelper::sendPendingEmail(
                    $partenaire->getEmail(),
                    $partenaire->getOrganizationName()
                );
                addFlashMessage('success', 'Partner request saved successfully. A confirmation email has been sent.');
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

        $partner = $this->findPartenaireById($id);
        if ($partner === null) {
            addFlashMessage('warning', 'Partner not found.');
            redirectTo(['controller' => 'partenaire', 'action' => 'list']);
        }

        $oldInput = pullOldInput();
        if (!empty($oldInput)) {
            $partner = array_merge($partner, $oldInput);
        }

        $this->render('backoffice/partenaire/updatePartenaire.php', [
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

        $existing = $this->findPartenaireById($id);
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
            
            $authHash = $existing['auth_key_hash'] ?? null;
            if (trim((string)($input['auth_key'] ?? '')) !== '') {
                $authHash = $this->buildAuthHash($input['auth_key']);
            }

            $partenaire = new Partenaire(
                $id,
                $existing['client_id'] ?? null,
                $input['organization_name'],
                $input['partner_type'],
                $input['email'],
                $input['telephone'],
                $input['address'] ?? null,
                $input['country'] ?? null,
                $input['domain'] ?? null,
                $logoPath,
                $input['description'] ?? null,
                $input['status'] ?: ($existing['status'] ?? 'pending'),
                $authHash,
                null,
                RecommendationService::generateEmbedding($input)
            );

            if ($this->updatePartenaireRecord($id, $partenaire)) {
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

        $partner = $this->findPartenaireById($id);
        if ($partner === null) {
            addFlashMessage('warning', 'Partner not found.');
            redirectTo(['controller' => 'partenaire', 'action' => 'list']);
        }

        if ($this->deletePartenaireRecord($id)) {
            $this->deleteUploadedFile($partner['logo'] ?? null);
            addFlashMessage('success', 'Partner request deleted successfully.');
        } else {
            addFlashMessage('danger', 'Unable to delete partner request.');
        }

        redirectTo(['controller' => 'partenaire', 'action' => 'list']);
    }

    private function showVerificationPage(): void
    {
        $allPartners = $this->getAllPartenaires();
        $pendingPartners = array_values(array_filter($allPartners, static function (array $partner): bool {
            return strtolower((string)($partner['status'] ?? 'pending')) === 'pending';
        }));

        $this->render('backoffice/partenaire/verificationPartenaire.php', [
            'pageTitle' => 'Verify Partenaires',
            'partners' => $pendingPartners,
            'messages' => pullFlashMessages(),
        ]);
    }

    private function changePartenaireStatus(int $id, string $status): void
    {
        // Get the partner details before updating
        $partner = $this->findPartenaireById($id);
        if (!$partner) {
            addFlashMessage('danger', 'Partner not found.');
            redirectTo(['controller' => 'partenaire', 'action' => 'verification']);
        }
        
        $oldStatus = $partner['status'];
        
        // Update status in database
        $success = $this->updatePartenaireStatus($id, $status);
        
        if ($success) {
            // Send email notification only if status actually changed
            if ($oldStatus !== $status) {
                if ($status === 'approved') {
                    $emailSent = MailHelper::sendApprovalEmail(
                        $partner['email'],
                        $partner['organization_name']
                    );
                    if ($emailSent) {
                        addFlashMessage('success', 'Partner approved and email notification sent.');
                    } else {
                        addFlashMessage('warning', 'Partner approved but email notification failed to send.');
                    }
                } elseif ($status === 'rejected') {
                    $emailSent = MailHelper::sendRejectionEmail(
                        $partner['email'],
                        $partner['organization_name']
                    );
                    if ($emailSent) {
                        addFlashMessage('success', 'Partner rejected and email notification sent.');
                    } else {
                        addFlashMessage('warning', 'Partner rejected but email notification failed to send.');
                    }
                }
            } else {
                addFlashMessage('info', 'Partner status unchanged.');
            }
        } else {
            addFlashMessage('danger', 'Unable to update partner status.');
        }
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

        $this->changePartenaireStatus($id, $status);
        redirectTo(['controller' => 'partenaire', 'action' => 'verification']);
    }

    /**
     * Frontend: Show partner preview on homepage (featured partners)
     */
    private function showPartnersPreview(): void
    {
        $partners = $this->getApprovedPartnersForDisplay(6);

        $this->render('frontoffice/partenaire/partnersPreview.php', [
            'pageTitle' => 'Partners Preview',
            'partners' => $partners,
        ]);
    }

    private function getApprovedPartnersForDisplay(int $limit = 0): array
    {
        $sql = 'SELECT `id`, `organization_name`, `logo`, `description`, `partner_type` 
                FROM `partenaires` 
                WHERE `status` = :status 
                ORDER BY `created_at` DESC, `id` DESC';
        
        if ($limit > 0) {
            $sql .= ' LIMIT ' . (int)$limit;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':status' => 'approved']);
        return $stmt->fetchAll();
    }

    /**
     * Frontend: Show all approved partners
     */
    private function showAllPartners(): void
    {
        $partners = $this->getAllApprovedPartners();

        $this->render('frontoffice/partenaire/allPartners.php', [
            'pageTitle' => 'All Partners',
            'partners' => $partners,
        ]);
    }

    private function getAllApprovedPartners(): array
    {
        $sql = 'SELECT `id`, `organization_name`, `logo`, `description`, `partner_type`, 
                       `email`, `country`, `domain`, `created_at` 
                FROM `partenaires` 
                WHERE `status` = :status 
                ORDER BY `created_at` DESC, `id` DESC';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':status' => 'approved']);
        return $stmt->fetchAll();
    }

    /**
     * Frontend: Show partnership application form (combined request + contract)
     */
    private function showPartnershipForm(): void
    {
        $this->render('frontoffice/partenaire/partnershipForm.php', [
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
            $clientId = $this->getNextClientId();
            
            // Generate a random auth key for frontend submissions
            $randomAuthKey = bin2hex(random_bytes(16));
            
            $partenaire = new Partenaire(
                null,
                $clientId,
                $input['organization_name'],
                $input['partner_type'],
                $input['email'],
                $input['telephone'],
                $input['address'] ?? null,
                $input['country'] ?? null,
                $input['domain'] ?? null,
                $logoPath,
                $input['description'] ?? null,
                'pending',
                $this->buildAuthHash($randomAuthKey),
                null,
                RecommendationService::generateEmbedding($input)
            );

            $sql = 'INSERT INTO `partenaires` (
                `client_id`, `organization_name`, `partner_type`, `email`, `telephone`, `address`,
                `country`, `domain`, `logo`, `description`, `status`, `auth_key_hash`, `embedding_vector`, `created_at`, `updated_at`
            ) VALUES (
                :client_id, :organization_name, :partner_type, :email, :telephone, :address,
                :country, :domain, :logo, :description, :status, :auth_key_hash, :embedding_vector, NOW(), NOW()
            )';

            $stmt = $this->pdo->prepare($sql);
            $success = $stmt->execute([
                ':client_id' => $partenaire->getClientId(),
                ':organization_name' => $partenaire->getOrganizationName(),
                ':partner_type' => $partenaire->getPartnerType(),
                ':email' => $partenaire->getEmail(),
                ':telephone' => $partenaire->getTelephone(),
                ':address' => $partenaire->getAddress(),
                ':country' => $partenaire->getCountry(),
                ':domain' => $partenaire->getDomain(),
                ':logo' => $partenaire->getLogo(),
                ':description' => $partenaire->getDescription(),
                ':status' => $partenaire->getStatus(),
                ':auth_key_hash' => $partenaire->getAuthKeyHash(),
                ':embedding_vector' => $partenaire->getEmbeddingVector(),
            ]);

            if ($success) {
                // Send pending confirmation email
                MailHelper::sendPendingEmail(
                    $partenaire->getEmail(),
                    $partenaire->getOrganizationName()
                );
                addFlashMessage('success', 'Your partnership application has been submitted successfully. A confirmation email has been sent. We will review it shortly.');
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
     * Frontend: Show smart recommendations for a specific partner or general suggestions
     */
    private function showRecommendations(): void
    {
        $targetId = $this->getRequestedId();
        $recommendations = [];
        $targetPartner = null;

        if ($targetId > 0) {
            $targetPartner = $this->findPartenaireById($targetId);
            if ($targetPartner) {
                $recommendations = RecommendationService::getRecommendations($this->pdo, $targetId, 6);
            }
        } else {
            // General recommendations for the user (e.g., based on recent approved partners)
            $allApproved = $this->getAllApprovedPartners();
            if (!empty($allApproved)) {
                // For demo: just pick a random one to show recommendations for
                $randomPartner = $allApproved[array_rand($allApproved)];
                $recommendations = RecommendationService::getRecommendations($this->pdo, (int)$randomPartner['id'], 6);
            }
        }

        $this->render('frontoffice/partenaire/recommendations.php', [
            'pageTitle' => 'Partners You May Like',
            'recommendations' => $recommendations,
            'targetPartner' => $targetPartner
        ]);
    }

    // ==================== HELPER METHODS ====================

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
        require dirname(__DIR__) . '/view/' . $viewPath;
    }
}
<?php

class BackofficeCertificatesController
{
    private CertificateRepository $certificates;
    private string $viewsPath;

    public function __construct()
    {
        $this->certificates = new CertificateRepository();
        $this->viewsPath = dirname(dirname(__DIR__)) . '/view/backoffice/src/pages/backoffice/quiz';
    }

    public function index(array $params = []): void
    {
        $certificates = $this->certificates->getAll();
        require $this->viewsPath . '/certificates/index.php';
    }

    public function delete(array $data = []): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }

        $id = isset($data['id']) ? (int) $data['id'] : 0;
        
        if ($id) {
            $this->certificates->delete($id);
        }

        header('Location: ' . backofficeRoute('certificates', 'index'));
        exit;
    }
}

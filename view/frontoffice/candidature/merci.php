<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidature reçue</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .thank-you-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            text-align: center;
            max-width: 500px;
        }
        .checkmark {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #28a745;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .checkmark svg {
            width: 50px;
            height: 50px;
            stroke: white;
            stroke-width: 2;
            fill: none;
        }
        h1 {
            color: #333;
            font-size: 28px;
            margin: 20px 0;
        }
        p {
            color: #666;
            font-size: 16px;
            line-height: 1.6;
            margin: 15px 0;
        }
        .btn-group-vertical {
            margin-top: 30px;
            gap: 10px;
        }
        .btn {
            padding: 10px 30px;
            font-size: 16px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="thank-you-card">
        <div class="checkmark">
            <svg viewBox="0 0 50 50">
                <polyline points="12,24 20,32 38,14"></polyline>
            </svg>
        </div>
        
        <h1>Merci pour votre candidature!</h1>
        
        <p>Votre dossier a été reçu avec succès.</p>
        <p>Notre équipe examinera votre candidature et vous contactera très prochainement.</p>
        
        <div class="btn-group-vertical">
            <a href="/gestion_users/controller/OffreEmploiController.php?espace=front&action=liste" class="btn btn-primary">
                Voir d'autres offres
            </a>
            <a href="/gestion_users/controller/OffreEmploiController.php?espace=front&action=liste" class="btn btn-outline-primary">
                Mes candidatures
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

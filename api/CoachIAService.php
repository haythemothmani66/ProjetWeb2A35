<?php

declare(strict_types=1);

/**
 * CoachIAService - Genere un plan de progression personnalise sur 7 jours
 * a partir de l'historique des devoirs + corrections d'un etudiant.
 *
 * Utilise Groq (API compatible OpenAI chat completions) avec llama-3.3-70b-versatile.
 * Configuration : lit GROQ_API_KEY depuis .env (deja gitignored).
 */
class CoachIAService
{
    private string $apiKey;
    private string $apiUrl;
    private string $model;
    private float $temperature;
    private int $timeout;

    public function __construct()
    {
        // Charge .env si pas deja fait
        require_once dirname(__DIR__) . '/config.php';

        $this->apiKey = $_ENV['GROQ_API_KEY'] ?? getenv('GROQ_API_KEY') ?: '';
        $this->model = $_ENV['GROQ_MODEL'] ?? 'llama-3.3-70b-versatile';
        $this->apiUrl = $_ENV['GROQ_API_URL'] ?? 'https://api.groq.com/openai/v1/chat/completions';
        $this->temperature = 0.6;
        $this->timeout = 45;
    }

    /**
     * Genere un plan personnalise de 7 jours
     *
     * @param array $snapshot Donnees agregees de l'etudiant :
     *   - competences_top (array) : top competences faibles
     *   - competences_strong (array) : top competences fortes
     *   - types_erreurs (array) : top types d'erreurs frequents
     *   - sentiment_dominant (string) : sentiment dominant des 30 derniers jours
     *   - note_moyenne (float)
     *   - nb_devoirs (int)
     *   - student_name (string)
     * @return array Plan structure : { overall_score, recommendation_level, summary, weak_areas[], strong_areas[], days[7] }
     * @throws Exception
     */
    public function generatePlan(array $snapshot): array
    {
        if ($this->apiKey === '') {
            throw new Exception("GROQ_API_KEY non configuree dans .env");
        }

        $prompt = $this->buildPrompt($snapshot);

        $payload = [
            'model' => $this->model,
            'temperature' => $this->temperature,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => "Tu es un coach pedagogique expert. Tu generes des plans de progression personnalises pour des etudiants en te basant sur leurs donnees academiques reelles. Tes plans sont concrets, motivants et realisables. Tu reponds UNIQUEMENT en JSON strict, sans markdown, sans commentaire."
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ],
            ],
            'response_format' => ['type' => 'json_object'],
            'max_tokens' => 4000,
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err !== '') {
            error_log("CoachIA cURL: $err");
            throw new Exception("Erreur connexion IA: $err");
        }
        if ($httpCode !== 200) {
            error_log("CoachIA HTTP $httpCode: $response");
            $errData = json_decode((string) $response, true);
            $msg = $errData['error']['message'] ?? "Erreur IA (HTTP $httpCode)";
            throw new Exception("Service IA: $msg");
        }

        $result = json_decode((string) $response, true);
        if (!isset($result['choices'][0]['message']['content'])) {
            throw new Exception("Format reponse IA inattendu");
        }

        $jsonText = (string) $result['choices'][0]['message']['content'];
        // Cleanup eventuel markdown
        $jsonText = preg_replace('/^\s*```(json)?\s*/i', '', $jsonText);
        $jsonText = preg_replace('/\s*```\s*$/i', '', $jsonText);

        $plan = json_decode($jsonText, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("CoachIA JSON parse error: " . json_last_error_msg() . " | Raw: " . substr($jsonText, 0, 300));
            throw new Exception("IA a retourne un JSON invalide: " . json_last_error_msg());
        }

        // Validation minimale de la structure
        if (!isset($plan['days']) || !is_array($plan['days']) || count($plan['days']) === 0) {
            throw new Exception("Le plan genere n'a pas de jours valides");
        }

        // Garantir les champs attendus
        $plan['overall_score'] = (int) ($plan['overall_score'] ?? 50);
        $plan['recommendation_level'] = $plan['recommendation_level'] ?? 'medium';
        $plan['summary'] = (string) ($plan['summary'] ?? '');
        $plan['weak_areas'] = $plan['weak_areas'] ?? [];
        $plan['strong_areas'] = $plan['strong_areas'] ?? [];

        return $plan;
    }

    /**
     * Construit le prompt en injectant les donnees reelles de l'etudiant
     */
    private function buildPrompt(array $snapshot): string
    {
        $studentName = (string) ($snapshot['student_name'] ?? 'Etudiant');
        $noteMoyenne = (float) ($snapshot['note_moyenne'] ?? 0);
        $nbDevoirs = (int) ($snapshot['nb_devoirs'] ?? 0);
        $sentiment = (string) ($snapshot['sentiment_dominant'] ?? 'neutre');

        $competencesFaibles = implode(', ', array_slice($snapshot['competences_top'] ?? [], 0, 5));
        if ($competencesFaibles === '') $competencesFaibles = '(non identifiees)';

        $competencesFortes = implode(', ', array_slice($snapshot['competences_strong'] ?? [], 0, 5));
        if ($competencesFortes === '') $competencesFortes = '(non identifiees)';

        $typesErreurs = implode(', ', array_slice($snapshot['types_erreurs'] ?? [], 0, 3));
        if ($typesErreurs === '') $typesErreurs = '(varies)';

        return <<<PROMPT
Genere un PLAN DE PROGRESSION PERSONNALISE sur 7 JOURS pour cet etudiant, en exploitant ses donnees reelles ci-dessous.

# Profil de l'etudiant
- Nom : {$studentName}
- Nombre de devoirs analyses : {$nbDevoirs}
- Note moyenne : {$noteMoyenne}/20
- Sentiment dominant des 30 derniers jours : {$sentiment}
- Competences faibles (a renforcer en priorite) : {$competencesFaibles}
- Competences fortes (a consolider) : {$competencesFortes}
- Types d'erreurs frequentes : {$typesErreurs}

# Structure JSON ATTENDUE (strict, sans markdown)

{
  "overall_score": 0-100 (score global de progression actuel base sur la note moyenne et la diversite des competences),
  "recommendation_level": "high" | "medium" | "low" (high = bonne progression, low = besoin urgent),
  "summary": "Analyse en 2-3 phrases du profil de l'etudiant, encourageante et factuelle",
  "weak_areas": ["competence ou erreur 1", "competence ou erreur 2", "competence ou erreur 3"],
  "strong_areas": ["competence forte 1", "competence forte 2"],
  "days": [
    {
      "day": 1,
      "theme": "Theme principal du jour (ex: Renforcement logique)",
      "duration_minutes": 60,
      "objective": "Objectif SMART precis et mesurable du jour",
      "actions": [
        "Action concrete 1 (ex: Revoir le cours sur les boucles)",
        "Action concrete 2 (ex: Faire 3 exercices de la fiche X)",
        "Action concrete 3"
      ],
      "resources": [
        "Ressource type chapitre/video/article (ex: Chapitre 5 du manuel d'algo)",
        "Ressource 2"
      ],
      "motivation": "Phrase d'encouragement personnalisee (1 ligne)"
    },
    // ... 7 jours au total
  ]
}

# Regles strictes
- EXACTEMENT 7 jours dans le tableau "days".
- Chaque jour cible une thematique differente, en alternant : renforcement competences faibles (jours 1, 3, 5), consolidation competences fortes (jours 2, 6), pratique mixte (jour 4), bilan + auto-evaluation (jour 7).
- Duration entre 30 et 90 minutes par jour (realiste).
- Tout doit etre en FRANCAIS.
- Adapter le ton si le sentiment dominant est "stress" ou "frustration" : etre rassurant, decomposer en petites etapes.
- Si l'etudiant a moins de 3 devoirs analyses, indiquer dans summary qu'il faut soumettre plus de devoirs pour affiner le plan.
- Les "actions" doivent etre VERIFIABLES (ex: "Resoudre 3 problemes" et non "Travailler la logique").

Retourne UNIQUEMENT le JSON, aucun texte autour.
PROMPT;
    }
}

<?php

declare(strict_types=1);

class CvMatchService
{
    private array $config;

    public function __construct(?array $config = null)
    {
        $this->config = $config ?? require __DIR__ . '/../config/ai.php';
    }

    public function isEnabled(): bool
    {
        return !empty($this->config['enabled'])
            && !empty($this->config['api_key'])
            && !empty($this->config['model']);
    }

    public function analyze(array $cvData, array $offer): array
    {
        $cvPath = (string) ($cvData['cv_file_path'] ?? '');
        $absolutePath = $this->resolveCvPath($cvPath);
        $cvText = $this->truncateText(
            $this->extractTextFromFile($absolutePath, (string) ($cvData['cv_mime'] ?? ''), (string) ($cvData['cv_original_name'] ?? '')),
            (int) ($this->config['max_cv_chars'] ?? 12000)
        );
        $offerText = $this->truncateText($this->buildOfferText($offer), (int) ($this->config['max_offer_chars'] ?? 6000));

        if (!$this->isEnabled()) {
            return $this->buildErrorResult('Le service IA n\'est pas configure.', $cvText, $offerText, $cvPath);
        }

        try {
            $payload = $this->requestStructuredAnalysis($cvText, $offerText, $offer);
            $payload['cv_excerpt_chars'] = $this->textLength($cvText);
            $payload['offer_excerpt_chars'] = $this->textLength($offerText);
            $payload['generated_at'] = date('Y-m-d H:i:s');

            return $payload;
        } catch (Throwable $exception) {
            if (!empty($this->config['fallback_enabled'])) {
                return $this->buildFallbackResult($cvText, $offerText, $exception->getMessage());
            }

            return $this->buildErrorResult($exception->getMessage(), $cvText, $offerText, $cvPath);
        }
    }

    private function requestStructuredAnalysis(string $cvText, string $offerText, array $offer): array
    {
        $prompt = $this->buildPrompt($cvText, $offerText, $offer);
        $endpoint = $this->config['base_url'] . '/chat/completions';

        $payload = [
            'model' => (string) $this->config['model'],
            'temperature' => (float) $this->config['temperature'],
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a recruitment analyst. Compare the CV text against the job offer text and return strict JSON only. Provide fields overall_score (0-100), matched_points (array), missing_points (array), summary (short), and recommendation_level (e.g., high/medium/low).',
                ],
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
        ];

        if (($this->config['response_format'] ?? '') === 'json_object') {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        $response = $this->postJson($endpoint, $payload);
        $content = $this->extractMessageContent($response);
        $decoded = json_decode($content, true);

        if (!is_array($decoded)) {
            throw new RuntimeException('La reponse du modele IA n\'est pas un JSON valide.');
        }

        // Prefer Groq-style fields: overall_score, matched_points, missing_points, recommendation_level
        $score = $this->normalizeScore($decoded['overall_score'] ?? $decoded['match_score'] ?? $decoded['score'] ?? null);

        $matchedPoints = [];
        if (isset($decoded['matched_points']) && is_array($decoded['matched_points'])) {
            $matchedPoints = $this->normalizeStringList($decoded['matched_points']);
        }

        $missingPoints = [];
        if (isset($decoded['missing_points']) && is_array($decoded['missing_points'])) {
            $missingPoints = $this->normalizeStringList($decoded['missing_points']);
        }

        $summary = $this->normalizeSummary($decoded['summary'] ?? null, $decoded);
        $recommendation = trim((string) ($decoded['recommendation_level'] ?? $decoded['recommendation'] ?? ''));

        return [
            'status' => 'success',
            'provider' => (string) ($this->config['provider'] ?? 'groq'),
            'model' => (string) $this->config['model'],
            'match_score' => $score,
            'match_details' => [
                'matched_points' => $matchedPoints,
                'missing_points' => $missingPoints,
                'summary' => $summary,
                'recommendation_level' => $recommendation,
                'analysis' => trim((string) ($decoded['analysis'] ?? $decoded['explanation'] ?? $decoded['rationale'] ?? '')),
                'raw' => $this->normalizeRawResponse($decoded),
            ],
        ];
    }

    private function buildPrompt(string $cvText, string $offerText, array $offer): string
    {
        return sprintf(
            "Job offer metadata:\n- Title: %s\n- Location: %s\n- Contract: %s\n- Deadline: %s\n\nJob offer text:\n%s\n\nCV text:\n%s\n\nReturn strict JSON with the following shape:\n{\n  \"overall_score\": 0-100,\n  \"matched_points\": [\"...\"],\n  \"missing_points\": [\"...\"],\n  \"summary\": \"...\",\n  \"recommendation_level\": \"high|medium|low\"\n}\n\nScoring Guidelines:\n- 0-20: Completely unrelated field or fundamental skill mismatch (e.g. sports teacher for physics teacher)\n- 21-40: Different field with minimal transferable skills\n- 41-60: Related field but significant gaps in key requirements\n- 61-80: Good fit with minor gaps\n- 81-100: Excellent fit with most/all requirements met\n\nRules:\n- Use only the provided texts.\n- If the primary field/discipline differs fundamentally from the offer, score STRICTLY in 0-20 range.\n- List specific reasons for low scores (e.g. \"different discipline\", \"missing core expertise\").\n- Be explicit: if it's a poor fit, say \"Not recommended\" or \"Poor fit\" in the recommendation.\n- Keep explanations concise and actionable.\n- Do not include markdown or code fences.\n",
            $this->stringValue($offer['titre'] ?? ''),
            $this->stringValue($offer['lieu'] ?? ''),
            $this->stringValue($offer['typecontrat'] ?? ''),
            $this->stringValue($offer['datelimite'] ?? ''),
            $offerText,
            $cvText
        );
    }

    private function normalizeBreakdown(mixed $breakdown): array
    {
        if (!is_array($breakdown)) {
            return [];
        }

        $normalized = [];
        foreach ($breakdown as $key => $value) {
            $normalized[(string) $key] = $this->normalizeScore($value);
        }

        return $normalized;
    }

    private function normalizeSummary(mixed $summary, array $decoded): array
    {
        $summaryArray = is_array($summary) ? $summary : [];
        $strengths = $summaryArray['strengths'] ?? $decoded['strengths'] ?? $decoded['matched_points'] ?? [];
        $missingPoints = $summaryArray['missing_points'] ?? $decoded['missing_points'] ?? [];
        $recommendation = $summaryArray['recommendation'] ?? $decoded['recommendation'] ?? $decoded['recommendation_level'] ?? '';

        if ($recommendation === '' && is_string($summary)) {
            $recommendation = $summary;
        }

        return [
            'strengths' => $this->normalizeStringList($strengths),
            'missing_points' => $this->normalizeStringList($missingPoints),
            'recommendation' => trim((string) $recommendation),
        ];
    }

    private function normalizeStringList(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $items = [];
        foreach ($value as $item) {
            $item = trim((string) $item);
            if ($item !== '') {
                $items[] = $item;
            }
        }

        return array_values(array_unique($items));
    }

    private function normalizeRawResponse(array $decoded): array
    {
        $raw = $decoded;
        if (isset($raw['summary']) && is_array($raw['summary'])) {
            unset($raw['summary']['strengths'], $raw['summary']['missing_points']);
        }

        return $raw;
    }

    private function normalizeScore(mixed $value): ?float
    {
        if ($value === null || $value === '' || !is_numeric($value)) {
            return null;
        }

        $score = (float) $value;
        if ($score < 0) {
            $score = 0.0;
        }
        if ($score > 100) {
            $score = 100.0;
        }

        return round($score, 2);
    }

    private function buildErrorResult(string $message, string $cvText, string $offerText, string $cvPath): array
    {
        return [
            'status' => 'error',
            'provider' => (string) ($this->config['provider'] ?? 'groq'),
            'model' => (string) ($this->config['model'] ?? ''),
            'match_score' => null,
            'match_details' => [
                'analysis' => 'Analyse IA indisponible.',
                'summary' => [
                    'strengths' => [],
                    'missing_points' => [],
                    'recommendation' => 'La note ne peut pas etre calculee tant que le service IA n\'est pas configure ou disponible.',
                ],
                'breakdown' => [],
                'error' => $message,
                'cv_text_chars' => $this->textLength($cvText),
                'offer_text_chars' => $this->textLength($offerText),
                'cv_path' => $cvPath,
            ],
        ];
    }

    private function buildFallbackResult(string $cvText, string $offerText, string $errorMessage): array
    {
        $cvTokens = $this->tokenize($cvText);
        $offerTokens = $this->tokenize($offerText);
        $sharedTokens = array_values(array_intersect($cvTokens, $offerTokens));
        $coverage = $offerTokens === [] ? 0.0 : (count($sharedTokens) / max(1, count(array_unique($offerTokens)))) * 100;
        $score = $this->normalizeScore($coverage) ?? 0.0;

        return [
            'status' => 'fallback',
            'provider' => 'debug-fallback',
            'model' => 'keyword-debug',
            'match_score' => $score,
            'match_details' => [
                'analysis' => 'Fallback de debug utilise uniquement en cas d\'indisponibilite du LLM.',
                'summary' => [
                    'strengths' => array_slice(array_values(array_unique($sharedTokens)), 0, 8),
                    'missing_points' => array_slice(array_values(array_diff(array_unique($offerTokens), array_unique($cvTokens))), 0, 8),
                    'recommendation' => 'Verifier la qualite du CV et reessayer avec le service IA principal.',
                ],
                'breakdown' => [
                    'fallback_coverage' => $score,
                ],
                'error' => $errorMessage,
            ],
        ];
    }

    private function tokenize(string $text): array
    {
        $text = mb_strtolower($text, 'UTF-8');
        $text = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $text) ?? $text;
        $parts = preg_split('/\s+/', trim($text)) ?: [];
        $filtered = [];
        foreach ($parts as $part) {
            if (mb_strlen($part, 'UTF-8') >= 3) {
                $filtered[] = $part;
            }
        }

        return array_values(array_unique($filtered));
    }

    private function buildOfferText(array $offer): string
    {
        $parts = array_filter([
            'Titre: ' . $this->stringValue($offer['titre'] ?? ''),
            'Description: ' . $this->stringValue($offer['description'] ?? ''),
            'Competences requises: ' . $this->stringValue($offer['competencesrequises'] ?? ''),
            'Lieu: ' . $this->stringValue($offer['lieu'] ?? ''),
            'Type contrat: ' . $this->stringValue($offer['typecontrat'] ?? ''),
            'Date limite: ' . $this->stringValue($offer['datelimite'] ?? ''),
        ]);

        return trim(implode("\n", $parts));
    }

    private function truncateText(string $text, int $limit): string
    {
        $text = trim($text);
        if ($limit <= 0 || $text === '') {
            return $text;
        }

        if (function_exists('mb_substr') && mb_strlen($text, 'UTF-8') > $limit) {
            return mb_substr($text, 0, $limit, 'UTF-8');
        }

        return strlen($text) > $limit ? substr($text, 0, $limit) : $text;
    }

    private function textLength(string $text): int
    {
        return function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text);
    }

    private function extractMessageContent(array $response): string
    {
        if (isset($response['choices'][0]['message']['content'])) {
            return trim((string) $response['choices'][0]['message']['content']);
        }

        if (isset($response['output_text'])) {
            return trim((string) $response['output_text']);
        }

        if (isset($response['content']) && is_string($response['content'])) {
            return trim($response['content']);
        }

        throw new RuntimeException('La reponse du service IA ne contient pas de texte exploitable.');
    }

    private function postJson(string $url, array $payload): array
    {
        $body = json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
        );
        if ($body === false) {
            throw new RuntimeException('Impossible de preparer la requete IA: ' . json_last_error_msg());
        }

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->config['api_key'],
        ];

        if ($this->config['provider'] === 'openai' && !empty($_ENV['OPENAI_ORGANIZATION'])) {
            $headers[] = 'OpenAI-Organization: ' . (string) $_ENV['OPENAI_ORGANIZATION'];
        }

        if (function_exists('curl_init')) {
            $curl = curl_init($url);
            if ($curl === false) {
                throw new RuntimeException('Impossible de demarrer la requete IA.');
            }

            curl_setopt_array($curl, [
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_POSTFIELDS => $body,
                CURLOPT_CONNECTTIMEOUT => (int) $this->config['timeout'],
                CURLOPT_TIMEOUT => (int) $this->config['timeout'],
            ]);

            $responseBody = curl_exec($curl);
            $statusCode = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);

            if ($responseBody === false) {
                $error = curl_error($curl);
                curl_close($curl);
                throw new RuntimeException('Erreur HTTP IA: ' . $error);
            }

            curl_close($curl);
            $decoded = json_decode((string) $responseBody, true);
            if (!is_array($decoded)) {
                throw new RuntimeException('La reponse IA n\'est pas un JSON valide.');
            }

            if ($statusCode >= 400) {
                $message = $decoded['error']['message'] ?? ('HTTP ' . $statusCode);
                throw new RuntimeException('Erreur IA: ' . $message);
            }

            return $decoded;
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $headers) . "\r\n",
                'content' => $body,
                'timeout' => (int) $this->config['timeout'],
                'ignore_errors' => true,
            ],
        ]);

        $responseBody = @file_get_contents($url, false, $context);
        if ($responseBody === false) {
            throw new RuntimeException('Impossible de contacter le service IA.');
        }

        $decoded = json_decode($responseBody, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('La reponse IA n\'est pas un JSON valide.');
        }

        return $decoded;
    }

    private function extractTextFromFile(string $path, string $mimeType = '', string $originalName = ''): string
    {
        if ($path === '' || !is_file($path)) {
            return '';
        }

        $extension = strtolower(pathinfo($originalName !== '' ? $originalName : $path, PATHINFO_EXTENSION));
        if ($extension === 'docx') {
            return $this->extractDocxText($path);
        }

        if ($extension === 'pdf') {
            $pdfText = $this->extractPdfText($path);
            if ($pdfText !== '') {
                return $pdfText;
            }
        }

        // Legacy .doc files are binary and frequently produce invalid UTF-8 noise without dedicated parsers.
        if ($extension === 'doc') {
            return '';
        }

        $contents = @file_get_contents($path);
        if ($contents === false) {
            return '';
        }

        $text = trim(strip_tags((string) $contents));
        if ($text !== '') {
            return preg_replace('/\s+/u', ' ', $text) ?? $text;
        }

        return '';
    }

    private function extractDocxText(string $path): string
    {
        if (!class_exists('ZipArchive')) {
            return '';
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return '';
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($xml === false || $xml === '') {
            return '';
        }

        $text = html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }

    private function extractPdfText(string $path): string
    {
        $pdftotext = (string) ($this->config['pdftotext_path'] ?? 'pdftotext');
        $command = escapeshellarg($pdftotext) . ' -layout ' . escapeshellarg($path) . ' - 2>&1';
        $output = @shell_exec($command);

        if (is_string($output)) {
            $trimmed = trim($output);
            if ($trimmed !== '' && stripos($trimmed, 'not recognized') === false && stripos($trimmed, 'cannot find') === false) {
                return preg_replace('/\s+/u', ' ', $trimmed) ?? $trimmed;
            }
        }

        $contents = @file_get_contents($path);
        if ($contents === false) {
            return '';
        }

        $text = '';
        if (preg_match_all('/\(([^\\()]*(?:\\.[^\\()]*)*)\)\s*T[Jj]/s', $contents, $matches)) {
            foreach ($matches[1] as $chunk) {
                $text .= $this->unescapePdfText((string) $chunk) . ' ';
            }
        }

        if ($text === '' && preg_match_all('/\[((?:.|\n)*?)\]\s*TJ/s', $contents, $arrayMatches)) {
            foreach ($arrayMatches[1] as $arrayChunk) {
                if (preg_match_all('/\(([^\\()]*(?:\\.[^\\()]*)*)\)/s', (string) $arrayChunk, $segmentMatches)) {
                    foreach ($segmentMatches[1] as $segment) {
                        $text .= $this->unescapePdfText((string) $segment) . ' ';
                    }
                }
            }
        }

        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? $text);

        return $text;
    }

    private function unescapePdfText(string $text): string
    {
        $text = str_replace(['\\n', '\\r', '\\t', '\\f', '\\b', '\\(', '\\)', '\\\\'], ["\n", "\r", "\t", "\f", "\b", '(', ')', '\\'], $text);
        $text = preg_replace_callback('/\\([0-7]{1,3})/', static function (array $matches): string {
            return chr((int) octdec($matches[1]));
        }, $text) ?? $text;

        return trim($text);
    }

    private function resolveCvPath(string $relativePath): string
    {
        $relativePath = ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath), DIRECTORY_SEPARATOR);
        if ($relativePath === '') {
            return '';
        }

        return dirname(__DIR__) . DIRECTORY_SEPARATOR . $relativePath;
    }

    private function stringValue(mixed $value): string
    {
        return trim((string) $value);
    }
}
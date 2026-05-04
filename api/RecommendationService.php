<?php
declare(strict_types=1);

class RecommendationService
{
    private static function log(string $message): void
    {
        $logFile = __DIR__ . '/recommendation_debug.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
    }

    public static function generateEmbedding(array $data): string
    {
        $text = strtolower(implode(' ', [
            $data['organization_name'] ?? '',
            $data['partner_type'] ?? '',
            $data['description'] ?? '',
            $data['country'] ?? ''
        ]));

        $words = str_word_count($text, 1);
        $frequencies = array_count_values($words);
        $frequencies = array_filter($frequencies, function($word) {
            return strlen($word) > 2;
        }, ARRAY_FILTER_USE_KEY);

        return json_encode($frequencies);
    }

    public static function calculateSimilarity(string $vec1Json, string $vec2Json): float
    {
        $v1 = json_decode($vec1Json, true) ?: [];
        $v2 = json_decode($vec2Json, true) ?: [];

        if (empty($v1) || empty($v2)) return 0.0;

        $dotProduct = 0;
        $mag1 = 0;
        $mag2 = 0;

        $allWords = array_unique(array_merge(array_keys($v1), array_keys($v2)));

        foreach ($allWords as $word) {
            $val1 = $v1[$word] ?? 0;
            $val2 = $v2[$word] ?? 0;
            $dotProduct += $val1 * $val2;
            $mag1 += $val1 * $val1;
            $mag2 += $val2 * $val2;
        }

        $magnitude = sqrt($mag1) * sqrt($mag2);
        return ($magnitude == 0) ? 0.0 : round(($dotProduct / $magnitude) * 100, 2);
    }

    public static function getRecommendations(PDO $pdo, int $targetId, int $limit = 5): array
    {
        self::log("Getting recommendations for targetId=$targetId, limit=$limit");

        // 1. Try to get target partner
        $target = null;
        if ($targetId > 0) {
            $stmt = $pdo->prepare("SELECT id, embedding_vector FROM partenaires WHERE id = ?");
            $stmt->execute([$targetId]);
            $target = $stmt->fetch();
        }

        // 2. If no target or no vector, return partners grouped by "theme" discovered from descriptions
        if (!$target || empty($target['embedding_vector'])) {
            self::log("No target. Returning grouped partners by theme.");
            $stmt = $pdo->prepare("SELECT id, organization_name, logo, description, partner_type, embedding_vector FROM partenaires WHERE status = 'approved' ORDER BY id DESC");
            $stmt->execute();
            $allApproved = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Simple clustering by common keywords
            $themes = [
                'Artificial Intelligence & Tech' => ['ai', 'intelligence', 'tech', 'software', 'data', 'programming', 'machine'],
                'Sustainable & Organic' => ['organic', 'farming', 'food', 'agricultural', 'green', 'harvest'],
                'Education & Research' => ['university', 'academy', 'research', 'education', 'learning', 'students']
            ];

            $results = [];
            foreach ($allApproved as $partner) {
                $desc = strtolower($partner['description'] . ' ' . $partner['organization_name'] . ' ' . $partner['partner_type']);
                $foundTheme = 'Other Partners';
                
                foreach ($themes as $themeName => $keywords) {
                    foreach ($keywords as $kw) {
                        if (strpos($desc, $kw) !== false) {
                            $foundTheme = $themeName;
                            break 2;
                        }
                    }
                }
                
                $partner['theme'] = $foundTheme;
                $partner['similarity_score'] = 100;
                unset($partner['embedding_vector']);
                $results[] = $partner;
            }

            // Sort results so they appear grouped in the UI
            usort($results, function($a, $b) {
                return strcmp($a['theme'], $b['theme']);
            });

            return $results;
        }

        // 3. Find similar partners
        $targetVector = $target['embedding_vector'];
        $stmt = $pdo->prepare("SELECT id, organization_name, logo, description, partner_type, embedding_vector FROM partenaires WHERE id != ? AND status = 'approved'");
        $stmt->execute([$targetId]);
        $others = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $recommendations = [];
        foreach ($others as $other) {
            if (empty($other['embedding_vector'])) continue;

            $score = self::calculateSimilarity($targetVector, $other['embedding_vector']);
            if ($score > 5) { // Lowered threshold for testing
                $other['similarity_score'] = $score;
                unset($other['embedding_vector']);
                $recommendations[] = $other;
            }
        }

        // Sort
        usort($recommendations, function($a, $b) {
            return $b['similarity_score'] <=> $a['similarity_score'];
        });

        $results = array_slice($recommendations, 0, $limit);

        // 4. Final Fallback if empty
        if (empty($results)) {
            self::log("No similar partners found. Falling back to latest approved.");
            $stmt = $pdo->prepare("SELECT id, organization_name, logo, description, partner_type FROM partenaires WHERE id != :id AND status = 'approved' ORDER BY id DESC LIMIT :limit");
            $stmt->bindValue(':id', $targetId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($results as &$r) { $r['similarity_score'] = 50; }
        }

        self::log("Returning " . count($results) . " results.");
        return $results;
    }
}

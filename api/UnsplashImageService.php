<?php

declare(strict_types=1);

/**
 * UnsplashImageService - Recherche automatique d'une image Unsplash
 * a partir d'un titre/description de cours.
 *
 * Utilise l'API officielle Unsplash (gratuite, 50 req/h en dev).
 * Cle Access Key lue depuis .env (UNSPLASH_ACCESS_KEY).
 *
 * Strategie de matching :
 *  1. Extraire les mots-cles techniques du titre + description
 *  2. Mapper certains mots-cles courants vers des termes de recherche optimises
 *     (ex: 'HTML' -> 'html code programming', 'Python' -> 'python programming code')
 *  3. Requete API GET /search/photos?query=...&per_page=1&orientation=landscape
 *  4. Fallback vers banque locale d'images si l'API echoue ou ne trouve rien
 */
class UnsplashImageService
{
    private string $accessKey;
    private string $apiUrl;
    private int $timeout;
    private string $imageSize;

    /**
     * Banque locale de fallback - images Unsplash deja testees, hardcoded.
     * Utilises si l'API echoue ou si on veut un fallback rapide par mot-cle.
     */
    private const FALLBACK_IMAGES = [
        'default'    => 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=800&h=500&fit=crop',
        'html'       => 'https://images.unsplash.com/photo-1542831371-29b0f74f9713?w=800&h=500&fit=crop',
        'css'        => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&h=500&fit=crop',
        'javascript' => 'https://images.unsplash.com/photo-1579468118864-1b9ea3c0db4a?w=800&h=500&fit=crop',
        'php'        => 'https://images.unsplash.com/photo-1633356122544-f134324a6cee?w=800&h=500&fit=crop',
        'python'     => 'https://images.unsplash.com/photo-1526379095098-d400fd0bf935?w=800&h=500&fit=crop',
        'react'      => 'https://images.unsplash.com/photo-1633356122102-3fe601e05bd2?w=800&h=500&fit=crop',
        'sql'        => 'https://images.unsplash.com/photo-1544383835-bda2bc66a55d?w=800&h=500&fit=crop',
        'database'   => 'https://images.unsplash.com/photo-1544383835-bda2bc66a55d?w=800&h=500&fit=crop',
    ];

    /**
     * Mapping de mots-cles courants vers des termes de recherche Unsplash optimises.
     * Permet d'avoir de meilleures images en utilisant des termes anglais + contexte.
     */
    private const KEYWORD_MAP = [
        'html'         => 'html code web development',
        'css'          => 'css web design styles',
        'javascript'   => 'javascript code programming',
        'js'           => 'javascript code programming',
        'typescript'   => 'typescript code',
        'php'          => 'php code server programming',
        'python'       => 'python code programming',
        'java'         => 'java code programming',
        'ruby'         => 'ruby code programming',
        'go'           => 'golang code',
        'rust'         => 'rust programming',
        'c++'          => 'cpp code programming',
        'c#'           => 'csharp code',
        'react'        => 'react javascript framework',
        'vue'          => 'vuejs framework',
        'angular'      => 'angular framework code',
        'node'         => 'nodejs server',
        'sql'          => 'database sql code',
        'database'     => 'database server',
        'mysql'        => 'database mysql',
        'mongodb'      => 'database nosql',
        'data science' => 'data science analytics',
        'data'         => 'data analytics charts',
        'machine learning' => 'machine learning ai',
        'deep learning'    => 'deep learning ai',
        'ia'           => 'artificial intelligence',
        'ai'           => 'artificial intelligence',
        'design'       => 'design ui ux',
        'ui'           => 'ui design interface',
        'ux'           => 'ux design',
        'mobile'       => 'mobile app development',
        'android'      => 'android development',
        'ios'          => 'ios development swift',
        'web'          => 'web development code',
        'devops'       => 'devops cloud server',
        'docker'       => 'docker containers',
        'kubernetes'   => 'kubernetes cloud',
        'security'     => 'cybersecurity code',
        'algorithme'   => 'algorithm programming',
        'algorithm'    => 'algorithm programming',
        'microcontroleur' => 'electronics circuit board',
        'electronique' => 'electronics components',
        'reseau'       => 'network technology',
        'cloud'        => 'cloud computing',
        'blockchain'   => 'blockchain crypto',
        'game'         => 'game development',
        'jeu'          => 'game development',
    ];

    public function __construct()
    {
        require_once dirname(__DIR__) . '/config.php';

        $this->accessKey = (string) ($_ENV['UNSPLASH_ACCESS_KEY'] ?? getenv('UNSPLASH_ACCESS_KEY') ?: '');
        $this->apiUrl    = (string) ($_ENV['UNSPLASH_API_URL'] ?? 'https://api.unsplash.com');
        $this->timeout   = 10;
        $this->imageSize = 'regular';
    }

    /**
     * Trouve une image Unsplash pertinente pour un cours donne.
     *
     * @param string $title       Titre du cours (obligatoire)
     * @param string $description Description optionnelle (aide au matching)
     * @return string URL absolue de l'image (Unsplash CDN) ou fallback local
     */
    public function findImageForCourse(string $title, string $description = ''): string
    {
        $query = $this->buildSearchQuery($title, $description);

        // Si pas de cle API configuree, fallback direct
        if ($this->accessKey === '') {
            error_log("UnsplashImageService: pas de cle API, fallback");
            return $this->getFallbackByKeyword($title);
        }

        try {
            $url = $this->apiUrl . '/search/photos?'
                . http_build_query([
                    'query'       => $query,
                    'per_page'    => 1,
                    'orientation' => 'landscape',
                    'content_filter' => 'high', // Filtre contenu approprie
                ]);

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Client-ID ' . $this->accessKey,
                    'Accept-Version: v1',
                ],
                CURLOPT_TIMEOUT => $this->timeout,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            curl_close($ch);

            if ($err !== '' || $httpCode !== 200) {
                error_log("UnsplashImageService HTTP $httpCode: $err | $response");
                return $this->getFallbackByKeyword($title);
            }

            $data = json_decode((string) $response, true);
            if (!isset($data['results'][0]['urls'][$this->imageSize])) {
                error_log("UnsplashImageService: pas de resultat pour query='$query'");
                return $this->getFallbackByKeyword($title);
            }

            $imageUrl = (string) $data['results'][0]['urls'][$this->imageSize];

            // Ajouter parametres de resize pour controler la taille (Unsplash supporte ?w=&h=)
            // Note: urls.regular fait deja ~1080px de large, suffisant pour nos cards
            return $imageUrl;
        } catch (Throwable $e) {
            error_log("UnsplashImageService exception: " . $e->getMessage());
            return $this->getFallbackByKeyword($title);
        }
    }

    /**
     * Construit une requete de recherche pertinente a partir du titre/description
     */
    private function buildSearchQuery(string $title, string $description = ''): string
    {
        $text = strtolower(trim($title . ' ' . $description));

        // Cherche les mots-cles connus dans le titre/description
        foreach (self::KEYWORD_MAP as $keyword => $searchTerm) {
            if (strpos($text, $keyword) !== false) {
                return $searchTerm;
            }
        }

        // Sinon, utilise les premiers mots du titre (en supprimant les mots vides)
        $stopWords = ['le', 'la', 'les', 'un', 'une', 'des', 'de', 'du', 'et', 'ou',
                      'au', 'aux', 'a', 'pour', 'avec', 'sans', 'introduction',
                      'apprendre', 'maitriser', 'decouvrir', 'cours', 'formation',
                      'the', 'a', 'an', 'of', 'for', 'with', 'and', 'or', 'in', 'on',
                      'fondamentaux', 'avance', 'avances', 'debutant', 'debutants',
                      'expert', 'experts'];

        $words = preg_split('/[\s,;:\-_()]+/', strtolower($title)) ?: [];
        $keywords = array_filter($words, static function ($w) use ($stopWords) {
            $w = trim($w);
            return strlen($w) >= 3 && !in_array($w, $stopWords, true);
        });

        $query = implode(' ', array_slice(array_values($keywords), 0, 3));

        // Si on a moins de 2 caracteres significatifs, fallback generique
        if (strlen($query) < 3) {
            $query = 'education learning programming';
        } else {
            // Ajoute 'code programming' pour favoriser les images tech
            $query .= ' programming code';
        }

        return $query;
    }

    /**
     * Retourne une URL de fallback basee sur des mots-cles trouves dans le titre.
     * Utilise quand l'API Unsplash echoue ou retourne 0 resultat.
     */
    private function getFallbackByKeyword(string $title): string
    {
        $lower = strtolower($title);
        foreach (self::FALLBACK_IMAGES as $keyword => $url) {
            if ($keyword === 'default') continue;
            if (strpos($lower, $keyword) !== false) {
                return $url;
            }
        }
        return self::FALLBACK_IMAGES['default'];
    }
}

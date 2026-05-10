<?php

class ImageHelper {
    private static function extractGoogleDriveFileId($url) {
        if (preg_match('#drive\.google\.com/file/d/([a-zA-Z0-9_-]+)#', $url, $m)) {
            return $m[1];
        }
        if (preg_match('#drive\.google\.com/open\?id=([a-zA-Z0-9_-]+)#', $url, $m)) {
            return $m[1];
        }
        if (preg_match('#drive\.google\.com/(?:uc|thumbnail)\?[^"\']*?\bid=([a-zA-Z0-9_-]+)#', $url, $m)) {
            return $m[1];
        }
        if (preg_match('#[?&]id=([a-zA-Z0-9_-]+)#', $url, $m) && strpos($url, 'drive.google.com') !== false) {
            return $m[1];
        }
        return '';
    }

    /**
     * Transforme les liens de partage Google Drive en URL utilisables dans une balise <img>.
     * Le fichier doit être partagé avec « Toute personne disposant du lien ».
     */
    public static function normalizeEventImageUrl($url) {
        $url = trim((string) $url);
        if ($url === '') {
            return '';
        }

        $fileId = self::extractGoogleDriveFileId($url);
        if ($fileId !== '') {
            // Le format "thumbnail" est généralement plus fiable côté navigateur/email.
            return 'https://drive.google.com/thumbnail?id=' . $fileId . '&sz=w1600';
        }
        return $url;
    }

    public static function isMetiersAvancesCategory($nomCategorie) {
        $n = mb_strtolower((string) $nomCategorie, 'UTF-8');
        return (strpos($n, 'métiers avancés') !== false || strpos($n, 'metiers avances') !== false);
    }
}

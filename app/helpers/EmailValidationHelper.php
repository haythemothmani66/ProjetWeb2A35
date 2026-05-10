<?php

class EmailValidationHelper {
    /** Normalisation pour comparaisons et stockage (Gmail ignore la casse). */
    public static function normalize(string $email): string {
        return strtolower(trim($email));
    }

    /**
     * Gmail uniquement ; partie locale = segments alphanumériques séparés par . ou _ ou -
     * (ex. nom-user@gmail.com, prenom.nom@gmail.com).
     */
    public static function isValidParticipantGmail(string $email): bool {
        $e = self::normalize($email);
        if ($e === '') {
            return false;
        }
        if (strlen($e) > 254) {
            return false;
        }

        return (bool) preg_match('/^[a-z0-9]+([._-][a-z0-9]+)*@gmail\.com$/', $e);
    }
}

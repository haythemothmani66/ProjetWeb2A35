<?php

declare(strict_types=1);

class Candidature
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAll(): array
    {
        $sql = 'SELECT
                    c.id,
                    c.nom,
                    c.prenom,
                    c.lettremotivation,
                    c.cvurl,
                    c.email,
                    c.statut,
                    c.datecandidature,
                    c.datereponse,
                    c.offreid,
                    o.titre AS offre_titre,
                    o.lieu AS offre_lieu,
                    o.typecontrat AS offre_typecontrat
                FROM candidature c
                LEFT JOIN offreemploi o ON o.id = c.offreid
                ORDER BY c.datecandidature DESC';

        $statement = $this->pdo->query($sql);
        return $statement ? $statement->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function getById(int $id): ?array
    {
        $sql = 'SELECT
                    c.id,
                    c.nom,
                    c.prenom,
                    c.lettremotivation,
                    c.cvurl,
                    c.email,
                    c.statut,
                    c.datecandidature,
                    c.datereponse,
                    c.offreid,
                    o.titre AS offre_titre,
                    o.description AS offre_description,
                    o.lieu AS offre_lieu,
                    o.typecontrat AS offre_typecontrat,
                    o.datelimite AS offre_datelimite
                FROM candidature c
                LEFT JOIN offreemploi o ON o.id = c.offreid
                WHERE c.id = :id
                LIMIT 1';

        $statement = $this->pdo->prepare($sql);
        $statement->execute(['id' => $id]);
        $candidature = $statement->fetch(PDO::FETCH_ASSOC);

        return $candidature ?: null;
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO candidature
                    (nom, prenom, lettremotivation, cvurl, email, statut, offreid)
                VALUES
                    (:nom, :prenom, :lettremotivation, :cvurl, :email, :statut, :offreid)';

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'lettremotivation' => $data['lettremotivation'],
            'cvurl' => $data['cvurl'],
            'email' => $data['email'],
            'statut' => $data['statut'] ?? 'enattente',
            'offreid' => $data['offreid'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE candidature
                SET nom = :nom,
                    prenom = :prenom,
                    lettremotivation = :lettremotivation,
                    cvurl = :cvurl,
                    email = :email,
                    statut = :statut,
                    datereponse = :datereponse,
                    offreid = :offreid
                WHERE id = :id';

        $statement = $this->pdo->prepare($sql);
        return $statement->execute([
            'id' => $id,
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'lettremotivation' => $data['lettremotivation'],
            'cvurl' => $data['cvurl'],
            'email' => $data['email'],
            'statut' => $data['statut'] ?? 'enattente',
            'datereponse' => $data['datereponse'] !== '' ? ($data['datereponse'] ?? null) : null,
            'offreid' => $data['offreid'],
        ]);
    }

    public function delete(int $id): bool
    {
        $sql = 'DELETE FROM candidature WHERE id = :id';
        $statement = $this->pdo->prepare($sql);

        return $statement->execute(['id' => $id]);
    }
}

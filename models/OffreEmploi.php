<?php

declare(strict_types=1);

class OffreEmploi
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAll(): array
    {
        $sql = 'SELECT * FROM offreemploi ORDER BY datecreation DESC';
        $statement = $this->pdo->query($sql);

        return $statement ? $statement->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function getById(int $id): ?array
    {
        $sql = 'SELECT * FROM offreemploi WHERE id = :id LIMIT 1';
        $statement = $this->pdo->prepare($sql);
        $statement->execute(['id' => $id]);
        $offre = $statement->fetch(PDO::FETCH_ASSOC);

        return $offre ?: null;
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO offreemploi
            (titre, description, competencesrequises, lieu, typecontrat, salairemin, salairemax, datelimite, statut)
                VALUES
            (:titre, :description, :competencesrequises, :lieu, :typecontrat, :salairemin, :salairemax, :datelimite, :statut)';

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            'titre' => $data['titre'],
            'description' => $data['description'],
            'competencesrequises' => $data['competencesrequises'] ?? null,
            'lieu' => $data['lieu'],
            'typecontrat' => $data['typecontrat'],
            'salairemin' => $data['salairemin'] ?? null,
            'salairemax' => $data['salairemax'] ?? null,
            'datelimite' => $data['datelimite'],
            'statut' => $data['statut'] ?? 'ouverte',
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE offreemploi
                SET titre = :titre,
                    description = :description,
                    competencesrequises = :competencesrequises,
                    lieu = :lieu,
                    typecontrat = :typecontrat,
                    salairemin = :salairemin,
                    salairemax = :salairemax,
                    datelimite = :datelimite,
                    statut = :statut
                WHERE id = :id';

        $statement = $this->pdo->prepare($sql);

        return $statement->execute([
            'id' => $id,
            'titre' => $data['titre'],
            'description' => $data['description'],
            'competencesrequises' => $data['competencesrequises'] ?? null,
            'lieu' => $data['lieu'],
            'typecontrat' => $data['typecontrat'],
            'salairemin' => $data['salairemin'] ?? null,
            'salairemax' => $data['salairemax'] ?? null,
            'datelimite' => $data['datelimite'],
            'statut' => $data['statut'] ?? 'ouverte',
        ]);
    }

    public function delete(int $id): bool
    {
        $sql = 'DELETE FROM offreemploi WHERE id = :id';
        $statement = $this->pdo->prepare($sql);

        return $statement->execute(['id' => $id]);
    }
}

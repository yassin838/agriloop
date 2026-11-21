<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';

final class Don
{
    public function __construct(private PDO $pdo)
    {
    }

    public static function createWithDatabase(): self
    {
        return new self(Database::getConnexion());
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO don
                (quantite, date_retrait, remarque, statut, id_association, id_user)
                VALUES
                (:quantite, :date_retrait, :remarque, :statut, :id_association, :id_user)';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'quantite' => $data['quantite'],
            'date_retrait' => $data['date_retrait'],
            'remarque' => $data['remarque'],
            'statut' => $data['statut'] ?? 'en_attente',
            'id_association' => $data['id_association'],
            'id_user' => $data['id_user'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function allWithJointure(?string $statut = null): array
    {
        $sql = 'SELECT d.*,
                       a.nom AS association_nom,
                       a.responsable AS association_responsable
                FROM don d
                INNER JOIN association a ON a.id = d.id_association';

        $params = [];
        if ($statut !== null && $statut !== '') {
            $sql .= ' WHERE d.statut = :statut';
            $params['statut'] = $statut;
        }

        $sql .= ' ORDER BY d.created_at DESC, d.id DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $sql = 'SELECT d.*,
                       a.nom AS association_nom
                FROM don d
                INNER JOIN association a ON a.id = d.id_association
                WHERE d.id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE don SET
                    quantite = :quantite,
                    date_retrait = :date_retrait,
                    remarque = :remarque,
                    statut = :statut,
                    id_association = :id_association
                WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'id' => $id,
            'quantite' => $data['quantite'],
            'date_retrait' => $data['date_retrait'],
            'remarque' => $data['remarque'],
            'statut' => $data['statut'],
            'id_association' => $data['id_association'],
        ]);
    }

    public function updateStatut(int $id, string $statut): bool
    {
        $allowed = ['en_attente', 'confirme', 'annule'];
        if (!in_array($statut, $allowed, true)) {
            return false;
        }

        $stmt = $this->pdo->prepare('UPDATE don SET statut = :statut WHERE id = :id');
        return $stmt->execute(['statut' => $statut, 'id' => $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM don WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function stats(): array
    {
        $sql = 'SELECT
                    COUNT(*) AS total,
                    SUM(statut = "en_attente") AS en_attente,
                    SUM(statut = "confirme") AS confirmes,
                    COALESCE(SUM(CASE WHEN statut = "confirme" THEN quantite ELSE 0 END), 0) AS quantite_confirme
                FROM don';

        return $this->pdo->query($sql)->fetch();
    }

    public function associationsActives(): array
    {
        return $this->pdo->query(
            'SELECT id, nom FROM association WHERE statut = "active" ORDER BY nom'
        )->fetchAll();
    }
}

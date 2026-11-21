<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';

final class Association
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getConnexion();
    }

    public static function createWithDatabase(): self
    {
        return new self(Database::getConnexion());
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO association
                (nom, domaine, ville, telephone, email, responsable, statut, id_user)
                VALUES (:nom, :domaine, :ville, :telephone, :email, :responsable, :statut, :id_user)';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'nom' => $data['nom'],
            'domaine' => $data['domaine'],
            'ville' => $data['ville'],
            'telephone' => $data['telephone'],
            'email' => $data['email'],
            'responsable' => $data['responsable'],
            'statut' => $data['statut'] ?? 'en_attente',
            'id_user' => $data['id_user'] ?? null,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function all(): array
    {
        return $this->pdo->query(
            'SELECT * FROM association ORDER BY created_at DESC, id DESC'
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM association WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE association SET
                    nom = :nom,
                    domaine = :domaine,
                    ville = :ville,
                    telephone = :telephone,
                    email = :email,
                    responsable = :responsable,
                    statut = :statut
                WHERE id = :id';

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'id' => $id,
            'nom' => $data['nom'],
            'domaine' => $data['domaine'],
            'ville' => $data['ville'],
            'telephone' => $data['telephone'],
            'email' => $data['email'],
            'responsable' => $data['responsable'],
            'statut' => $data['statut'],
        ]);
    }

    public function updateStatut(int $id, string $statut): bool
    {
        if (!in_array($statut, ['en_attente', 'active', 'inactive'], true)) {
            return false;
        }

        $stmt = $this->pdo->prepare(
            'UPDATE association SET statut = :statut WHERE id = :id'
        );
        return $stmt->execute(['id' => $id, 'statut' => $statut]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM association WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function emailExists(string $email, ?int $exceptId = null): bool
    {
        if ($exceptId === null) {
            $stmt = $this->pdo->prepare('SELECT 1 FROM association WHERE email = :email');
            $stmt->execute(['email' => $email]);
        } else {
            $stmt = $this->pdo->prepare(
                'SELECT 1 FROM association WHERE email = :email AND id <> :id'
            );
            $stmt->execute(['email' => $email, 'id' => $exceptId]);
        }

        return (bool) $stmt->fetchColumn();
    }
}

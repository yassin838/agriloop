<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';

final class Utilisateur
{
    public function __construct(private PDO $pdo)
    {
    }

    public static function createWithDatabase(): self
    {
        return new self(Database::getConnexion());
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM utilisateur WHERE email = :email'
        );
        $stmt->execute(['email' => strtolower(trim($email))]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM utilisateur WHERE email = :email');
        $stmt->execute(['email' => strtolower(trim($email))]);
        return (bool) $stmt->fetchColumn();
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO utilisateur
             (nom, prenom, email, mot_de_passe, telephone, ville, role)
             VALUES (:nom, :prenom, :email, :mot_de_passe, :telephone, :ville, :role)'
        );
        $stmt->execute([
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'email' => strtolower(trim($data['email'])),
            'mot_de_passe' => password_hash($data['mot_de_passe'], PASSWORD_DEFAULT),
            'telephone' => $data['telephone'],
            'ville' => $data['ville'],
            'role' => $data['role'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT id, nom, prenom, email, telephone, ville, role, actif, created_at, updated_at
             FROM utilisateur
             ORDER BY id ASC'
        );
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, nom, prenom, email, telephone, ville, role, actif, created_at, updated_at
             FROM utilisateur
             WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function emailExistsExcept(int $id, string $email): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM utilisateur
             WHERE email = :email AND id <> :id
             LIMIT 1'
        );
        $stmt->execute([
            'email' => strtolower(trim($email)),
            'id' => $id,
        ]);
        return (bool) $stmt->fetchColumn();
    }

    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE utilisateur
                SET nom = :nom,
                    prenom = :prenom,
                    email = :email,
                    telephone = :telephone,
                    ville = :ville,
                    role = :role';
        $params = [
            'id' => $id,
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'email' => strtolower(trim($data['email'])),
            'telephone' => $data['telephone'],
            'ville' => $data['ville'],
            'role' => $data['role'],
        ];

        if (!empty($data['mot_de_passe'])) {
            $sql .= ', mot_de_passe = :mot_de_passe';
            $params['mot_de_passe'] = password_hash((string) $data['mot_de_passe'], PASSWORD_DEFAULT);
        }

        $sql .= ' WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM utilisateur WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function countAdministrateurs(): int
    {
        $stmt = $this->pdo->query(
            "SELECT COUNT(*) FROM utilisateur WHERE role = 'administrateur'"
        );
        return (int) $stmt->fetchColumn();
    }

    public function setActive(int $id, bool $active): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE utilisateur SET actif = :actif WHERE id = :id'
        );
        return $stmt->execute([
            'actif' => $active ? 1 : 0,
            'id' => $id,
        ]);
    }

    public function updateProfile(int $id, array $data): bool
    {
        $sql = 'UPDATE utilisateur
                SET nom = :nom,
                    prenom = :prenom,
                    email = :email,
                    telephone = :telephone,
                    ville = :ville';
        $params = [
            'id' => $id,
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'email' => strtolower(trim($data['email'])),
            'telephone' => $data['telephone'],
            'ville' => $data['ville'],
        ];

        if (!empty($data['mot_de_passe'])) {
            $sql .= ', mot_de_passe = :mot_de_passe';
            $params['mot_de_passe'] = password_hash((string) $data['mot_de_passe'], PASSWORD_DEFAULT);
        }

        $sql .= ' WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

}

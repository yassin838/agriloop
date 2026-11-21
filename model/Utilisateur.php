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

}

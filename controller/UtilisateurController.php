<?php

declare(strict_types=1);

require_once __DIR__ . '/../model/Utilisateur.php';

final class UtilisateurController
{
    public function __construct(private Utilisateur $utilisateur)
    {
    }

    public function register(array $input): array
    {
        $errors = [];
        $nom = trim((string) ($input['nom'] ?? ''));
        $prenom = trim((string) ($input['prenom'] ?? ''));
        $email = strtolower(trim((string) ($input['email'] ?? '')));
        $telephone = trim((string) ($input['telephone'] ?? ''));
        $ville = trim((string) ($input['ville'] ?? ''));
        $password = (string) ($input['mot_de_passe'] ?? '');
        $passwordConfirm = (string) ($input['mot_de_passe_confirmation'] ?? '');
        $role = (string) ($input['role'] ?? '');

        if (mb_strlen($nom) < 2 || mb_strlen($nom) > 100) {
            $errors['nom'] = 'Le nom doit contenir entre 2 et 100 caracteres.';
        }
        if (mb_strlen($prenom) < 2 || mb_strlen($prenom) > 100) {
            $errors['prenom'] = 'Le prenom doit contenir entre 2 et 100 caracteres.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 180) {
            $errors['email'] = 'Saisissez une adresse email valide.';
        } elseif (strtolower($email) === 'admin.agriloop@agriloop.tn') {
            $errors['email'] = 'Cet email est reserve au compte administrateur.';
        } elseif ($this->utilisateur->emailExists($email)) {
            $errors['email'] = 'Cette adresse email est deja utilisee.';
        }
        if (!preg_match('/^\+?[0-9][0-9 .-]{7,29}$/', $telephone)) {
            $errors['telephone'] = 'Saisissez un numero de telephone valide.';
        }
        if (mb_strlen($ville) < 2 || mb_strlen($ville) > 100) {
            $errors['ville'] = 'Saisissez une ville valide.';
        }
        if (!in_array($role, ['agriculteur', 'acheteur'], true)) {
            $errors['role'] = 'Choisissez un role valide.';
        }
        if (mb_strlen($password) < 8) {
            $errors['mot_de_passe'] = 'Le mot de passe doit contenir au moins 8 caracteres.';
        } elseif ($password !== $passwordConfirm) {
            $errors['mot_de_passe_confirmation'] = 'Les mots de passe ne correspondent pas.';
        }

        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $id = $this->utilisateur->create([
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'mot_de_passe' => $password,
            'telephone' => $telephone,
            'ville' => $ville,
            'role' => $role,
        ]);

        return ['success' => true, 'id' => $id];
    }

    public function login(string $email, string $password): array
    {
        $user = $this->utilisateur->findByEmail($email);
        if (!$user || !password_verify($password, (string) $user['mot_de_passe'])) {
            return [
                'success' => false,
                'errors' => ['identifiants' => 'Email ou mot de passe incorrect.'],
            ];
        }
        if ((int) ($user['actif'] ?? 1) !== 1) {
            return [
                'success' => false,
                'errors' => ['identifiants' => 'Ce compte est desactive. Contactez l administrateur.'],
            ];
        }

        return ['success' => true, 'user' => $user];
    }

    public function all(): array
    {
        return $this->utilisateur->findAll();
    }

    public function find(int $id): ?array
    {
        return $id > 0 ? $this->utilisateur->findById($id) : null;
    }

    public function createByAdmin(array $input): array
    {
        $nom = trim((string) ($input['nom'] ?? ''));
        $prenom = trim((string) ($input['prenom'] ?? ''));
        $email = strtolower(trim((string) ($input['email'] ?? '')));
        $telephone = trim((string) ($input['telephone'] ?? ''));
        $ville = trim((string) ($input['ville'] ?? ''));
        $role = (string) ($input['role'] ?? '');
        $password = (string) ($input['mot_de_passe'] ?? '');
        $errors = [];

        if (mb_strlen($nom) < 2 || mb_strlen($nom) > 100) {
            $errors['nom'] = 'Le nom doit contenir entre 2 et 100 caracteres.';
        }
        if (mb_strlen($prenom) < 2 || mb_strlen($prenom) > 100) {
            $errors['prenom'] = 'Le prenom doit contenir entre 2 et 100 caracteres.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 180) {
            $errors['email'] = 'Saisissez une adresse email valide.';
        } elseif ($this->utilisateur->emailExists($email)) {
            $errors['email'] = 'Cette adresse email est deja utilisee.';
        }
        if (!preg_match('/^\+?[0-9][0-9 .-]{7,29}$/', $telephone)) {
            $errors['telephone'] = 'Saisissez un numero de telephone valide.';
        }
        if (mb_strlen($ville) < 2 || mb_strlen($ville) > 100) {
            $errors['ville'] = 'Saisissez une ville valide.';
        }
        if (!in_array($role, ['agriculteur', 'acheteur', 'administrateur'], true)) {
            $errors['role'] = 'Choisissez un role valide.';
        }
        if (mb_strlen($password) < 8) {
            $errors['mot_de_passe'] = 'Le mot de passe doit contenir au moins 8 caracteres.';
        }
        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $id = $this->utilisateur->create([
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'mot_de_passe' => $password,
            'telephone' => $telephone,
            'ville' => $ville,
            'role' => $role,
        ]);
        return ['success' => true, 'id' => $id];
    }

    public function update(int $id, array $input, int $currentUserId): array
    {
        $errors = [];
        $user = $this->utilisateur->findById($id);
        if (!$user) {
            return ['success' => false, 'errors' => ['utilisateur' => 'Utilisateur introuvable.']];
        }

        $nom = trim((string) ($input['nom'] ?? ''));
        $prenom = trim((string) ($input['prenom'] ?? ''));
        $email = strtolower(trim((string) ($input['email'] ?? '')));
        $telephone = trim((string) ($input['telephone'] ?? ''));
        $ville = trim((string) ($input['ville'] ?? ''));
        $role = (string) ($input['role'] ?? '');
        $password = (string) ($input['mot_de_passe'] ?? '');

        if (mb_strlen($nom) < 2 || mb_strlen($nom) > 100) {
            $errors['nom'] = 'Le nom doit contenir entre 2 et 100 caracteres.';
        }
        if (mb_strlen($prenom) < 2 || mb_strlen($prenom) > 100) {
            $errors['prenom'] = 'Le prenom doit contenir entre 2 et 100 caracteres.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 180) {
            $errors['email'] = 'Saisissez une adresse email valide.';
        } elseif ($this->utilisateur->emailExistsExcept($id, $email)) {
            $errors['email'] = 'Cette adresse email est deja utilisee.';
        }
        if (!preg_match('/^\+?[0-9][0-9 .-]{7,29}$/', $telephone)) {
            $errors['telephone'] = 'Saisissez un numero de telephone valide.';
        }
        if (mb_strlen($ville) < 2 || mb_strlen($ville) > 100) {
            $errors['ville'] = 'Saisissez une ville valide.';
        }
        if (!in_array($role, ['agriculteur', 'acheteur', 'administrateur'], true)) {
            $errors['role'] = 'Choisissez un role valide.';
        }
        if ($password !== '' && mb_strlen($password) < 8) {
            $errors['mot_de_passe'] = 'Le nouveau mot de passe doit contenir au moins 8 caracteres.';
        }
        if ($id === $currentUserId && $role !== 'administrateur') {
            $errors['role'] = 'Vous ne pouvez pas retirer votre propre role administrateur.';
        }
        if ($user['role'] === 'administrateur'
            && $role !== 'administrateur'
            && $this->utilisateur->countAdministrateurs() <= 1) {
            $errors['role'] = 'Le projet doit conserver au moins un administrateur.';
        }

        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $updated = $this->utilisateur->update($id, [
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'telephone' => $telephone,
            'ville' => $ville,
            'role' => $role,
            'mot_de_passe' => $password,
        ]);
        return $updated
            ? ['success' => true]
            : ['success' => false, 'errors' => ['utilisateur' => 'Modification impossible.']];
    }

    public function delete(int $id, int $currentUserId): array
    {
        if ($id <= 0) {
            return ['success' => false, 'errors' => ['utilisateur' => 'Utilisateur introuvable.']];
        }
        if ($id === $currentUserId) {
            return ['success' => false, 'errors' => ['utilisateur' => 'Vous ne pouvez pas supprimer votre propre compte.']];
        }

        $user = $this->utilisateur->findById($id);
        if (!$user) {
            return ['success' => false, 'errors' => ['utilisateur' => 'Utilisateur introuvable.']];
        }
        if ($user['role'] === 'administrateur'
            && $this->utilisateur->countAdministrateurs() <= 1) {
            return ['success' => false, 'errors' => ['utilisateur' => 'Le dernier administrateur ne peut pas etre supprime.']];
        }

        try {
            $deleted = $this->utilisateur->delete($id);
        } catch (PDOException $exception) {
            return [
                'success' => false,
                'errors' => ['utilisateur' => 'Utilisateur lie a des associations ou des dons. Supprimez ou reassignez ces donnees avant cet utilisateur.'],
            ];
        }

        return $deleted
            ? ['success' => true]
            : ['success' => false, 'errors' => ['utilisateur' => 'Suppression impossible.']];
    }

    public function updateProfile(int $id, array $input): array
    {
        $user = $this->utilisateur->findById($id);
        if (!$user) {
            return ['success' => false, 'errors' => ['profil' => 'Utilisateur introuvable.']];
        }

        $nom = trim((string) ($input['nom'] ?? ''));
        $prenom = trim((string) ($input['prenom'] ?? ''));
        $email = strtolower(trim((string) ($input['email'] ?? '')));
        $telephone = trim((string) ($input['telephone'] ?? ''));
        $ville = trim((string) ($input['ville'] ?? ''));
        $password = (string) ($input['mot_de_passe'] ?? '');
        $passwordConfirmation = (string) ($input['mot_de_passe_confirmation'] ?? '');
        $errors = [];

        if (mb_strlen($nom) < 2 || mb_strlen($nom) > 100) {
            $errors['nom'] = 'Le nom doit contenir entre 2 et 100 caracteres.';
        }
        if (mb_strlen($prenom) < 2 || mb_strlen($prenom) > 100) {
            $errors['prenom'] = 'Le prenom doit contenir entre 2 et 100 caracteres.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 180) {
            $errors['email'] = 'Saisissez une adresse email valide.';
        } elseif ($this->utilisateur->emailExistsExcept($id, $email)) {
            $errors['email'] = 'Cette adresse email est deja utilisee.';
        }
        if (!preg_match('/^\+?[0-9][0-9 .-]{7,29}$/', $telephone)) {
            $errors['telephone'] = 'Saisissez un numero de telephone valide.';
        }
        if (mb_strlen($ville) < 2 || mb_strlen($ville) > 100) {
            $errors['ville'] = 'Saisissez une ville valide.';
        }
        if ($password !== '' && mb_strlen($password) < 8) {
            $errors['mot_de_passe'] = 'Le nouveau mot de passe doit contenir au moins 8 caracteres.';
        } elseif ($password !== $passwordConfirmation) {
            $errors['mot_de_passe_confirmation'] = 'Les mots de passe ne correspondent pas.';
        }

        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $updated = $this->utilisateur->updateProfile($id, [
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'telephone' => $telephone,
            'ville' => $ville,
            'mot_de_passe' => $password,
        ]);
        return $updated
            ? ['success' => true]
            : ['success' => false, 'errors' => ['profil' => 'Modification impossible.']];
    }

    public function setActive(int $id, bool $active, int $currentUserId): array
    {
        if ($id === $currentUserId) {
            return ['success' => false, 'errors' => ['utilisateur' => 'Vous ne pouvez pas modifier l activation de votre propre compte.']];
        }
        $user = $this->utilisateur->findById($id);
        if (!$user) {
            return ['success' => false, 'errors' => ['utilisateur' => 'Utilisateur introuvable.']];
        }
        if (!$active && $user['role'] === 'administrateur'
            && $this->utilisateur->countAdministrateurs() <= 1) {
            return ['success' => false, 'errors' => ['utilisateur' => 'Le dernier administrateur ne peut pas etre desactive.']];
        }
        $updated = $this->utilisateur->setActive($id, $active);
        return $updated
            ? ['success' => true]
            : ['success' => false, 'errors' => ['utilisateur' => 'Modification impossible.']];
    }
}

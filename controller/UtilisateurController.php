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

        return ['success' => true, 'user' => $user];
    }
}

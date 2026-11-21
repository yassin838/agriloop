<?php

declare(strict_types=1);

require_once __DIR__ . '/../model/Association.php';

final class AssociationController
{
    public function __construct(private Association $association)
    {
    }

    public function index(): array
    {
        return $this->association->all();
    }

    public function find(int $id): ?array
    {
        return $this->association->find($id);
    }

    public function store(array $input): array
    {
        $errors = $this->validate($input);
        $email = strtolower(trim((string) ($input['email'] ?? '')));

        if ($email !== '' && $this->association->emailExists($email)) {
            $errors['email'] = 'Cette adresse email est deja utilisee.';
        }

        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $id = $this->association->create([
            'nom' => trim((string) $input['nom']),
            'domaine' => trim((string) $input['domaine']),
            'ville' => trim((string) $input['ville']),
            'telephone' => trim((string) $input['telephone']),
            'email' => $email,
            'responsable' => trim((string) $input['responsable']),
            'statut' => 'en_attente',
            'id_user' => $input['id_user'] ?? null,
        ]);

        return ['success' => true, 'id' => $id];
    }

    public function update(int $id, array $input): array
    {
        $errors = $this->validate($input);
        $email = strtolower(trim((string) ($input['email'] ?? '')));

        if ($email !== '' && $this->association->emailExists($email, $id)) {
            $errors['email'] = 'Cette adresse email est deja utilisee.';
        }

        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $updated = $this->association->update($id, [
            'nom' => trim((string) $input['nom']),
            'domaine' => trim((string) $input['domaine']),
            'ville' => trim((string) $input['ville']),
            'telephone' => trim((string) $input['telephone']),
            'email' => $email,
            'responsable' => trim((string) $input['responsable']),
            'statut' => in_array($input['statut'] ?? '', ['en_attente', 'active', 'inactive'], true)
                ? $input['statut']
                : 'active',
        ]);

        return ['success' => $updated];
    }

    public function updateStatut(int $id, string $statut): array
    {
        return ['success' => $this->association->updateStatut($id, $statut)];
    }

    public function delete(int $id): array
    {
        try {
            $deleted = $this->association->delete($id);
            return ['success' => $deleted];
        } catch (PDOException $exception) {
            return [
                'success' => false,
                'errors' => ['association' => 'Impossible de supprimer: des dons sont lies a cette association.'],
            ];
        }
    }

    private function validate(array $input): array
    {
        $errors = [];
        $nom = trim((string) ($input['nom'] ?? ''));
        $domaine = trim((string) ($input['domaine'] ?? ''));
        $ville = trim((string) ($input['ville'] ?? ''));
        $telephone = trim((string) ($input['telephone'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));
        $responsable = trim((string) ($input['responsable'] ?? ''));

        if (mb_strlen($nom) < 3 || mb_strlen($nom) > 150) {
            $errors['nom'] = 'Le nom doit contenir entre 3 et 150 caracteres.';
        }
        if ($domaine === '') {
            $errors['domaine'] = 'Choisissez un domaine d activite.';
        }
        if ($ville === '') {
            $errors['ville'] = 'Choisissez une ville.';
        }
        if (!preg_match('/^\+?[0-9][0-9 .-]{7,29}$/', $telephone)) {
            $errors['telephone'] = 'Saisissez un numero de telephone valide.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 180) {
            $errors['email'] = 'Saisissez une adresse email valide.';
        }
        if (mb_strlen($responsable) < 3 || mb_strlen($responsable) > 150) {
            $errors['responsable'] = 'Le responsable doit contenir entre 3 et 150 caracteres.';
        }

        return $errors;
    }
}

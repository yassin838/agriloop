<?php

declare(strict_types=1);

require_once __DIR__ . '/../model/Don.php';

final class DonController
{
    public function __construct(private Don $don)
    {
    }

    public function index(?string $statut = null): array
    {
        return $this->don->allWithJointure($statut);
    }

    public function find(int $id): ?array
    {
        return $this->don->find($id);
    }

    public function associationsActives(): array
    {
        return $this->don->associationsActives();
    }

    public function store(array $input): array
    {
        $errors = $this->validate($input);
        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $id = $this->don->create([
            'quantite' => (float) $input['quantite'],
            'date_retrait' => $input['date_retrait'],
            'remarque' => trim((string) ($input['remarque'] ?? '')),
            'statut' => 'en_attente',
            'id_association' => (int) $input['id_association'],
            'id_user' => (int) $input['id_user'],
        ]);

        return ['success' => true, 'id' => $id];
    }

    public function update(int $id, array $input): array
    {
        $errors = $this->validate($input);
        $statut = (string) ($input['statut'] ?? '');
        if (!in_array($statut, ['en_attente', 'confirme', 'annule'], true)) {
            $errors['statut'] = 'Statut invalide.';
        }
        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $updated = $this->don->update($id, [
            'quantite' => (float) $input['quantite'],
            'date_retrait' => $input['date_retrait'],
            'remarque' => trim((string) ($input['remarque'] ?? '')),
            'statut' => $statut,
            'id_association' => (int) $input['id_association'],
        ]);

        return ['success' => $updated];
    }

    public function updateStatut(int $id, string $statut): array
    {
        return ['success' => $this->don->updateStatut($id, $statut)];
    }

    public function delete(int $id): array
    {
        return ['success' => $this->don->delete($id)];
    }

    private function validate(array $input): array
    {
        $errors = [];
        $quantite = filter_var($input['quantite'] ?? null, FILTER_VALIDATE_FLOAT);
        $date = (string) ($input['date_retrait'] ?? '');
        $associationId = filter_var($input['id_association'] ?? null, FILTER_VALIDATE_INT);

        if ($quantite === false || $quantite <= 0 || $quantite > 1000000) {
            $errors['quantite'] = 'La quantite doit etre un nombre positif inferieur ou egal a 1 000 000.';
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $errors['date_retrait'] = 'Saisissez une date valide.';
        } else {
            [$year, $month, $day] = array_map('intval', explode('-', $date));
            if (!checkdate($month, $day, $year)) {
                $errors['date_retrait'] = 'Saisissez une date valide.';
            }
        }
        if ($associationId === false || $associationId <= 0) {
            $errors['id_association'] = 'Choisissez une association.';
        }
        if (mb_strlen((string) ($input['remarque'] ?? '')) > 1000) {
            $errors['remarque'] = 'La remarque ne doit pas depasser 1000 caracteres.';
        }

        return $errors;
    }
}

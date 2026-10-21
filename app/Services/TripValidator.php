<?php

declare(strict_types=1);

namespace App\Services;

use DateTimeImmutable;

/** Validation des données du formulaire de création d'un trajet. */
final class TripValidator
{
    private const MAX_SEATS = 20;
    private const INPUT_FORMAT = 'Y-m-d\TH:i';

    /** 
     * @param array<string, string> $input
     * @param list<int> $agencyIds
     * @return array<string, string> Messages d'erreur indexés par champ (vide si valide).
     */
    public function validate(array $input, array $agencyIds, DateTimeImmutable $now): array
    {
        $errors = [];

        $departureAgency = $this->toInt($input['agence_depart'] ?? '');
        $arrivalAgency = $this->toInt($input['agence_arrivee'] ?? '');

        if ($departureAgency === null || !in_array($departureAgency, $agencyIds, true)) {
            $errors['agence_depart'] = "L'agence de départ est invalide.";
        }

        if ($arrivalAgency === null || !in_array($arrivalAgency, $agencyIds, true)) {
            $errors['agence_arrivee'] = "L'agence d'arrivée est invalide.";
        }

        if ($errors === [] && $departureAgency === $arrivalAgency) {
            $errors['agence_arrivee'] = "L'agence d'arrivée doit être différente de l'agence de départ.";
        }

        $departure = $this->parseDate($input['date_depart'] ?? '');
        $arrival = $this->parseDate($input['date_arrivee'] ?? '');

        if ($departure === null) {
            $errors['date_depart'] = 'La date de départ est invalide.';
        } elseif ($departure < $now) {
            $errors['date_depart'] = 'Le départ ne peut pas être dans le passé.';
        }

        if ($arrival === null) {
            $errors['date_arrivee'] = "La date d'arrivée est invalide.";
        } elseif ($departure !== null && $arrival <= $departure) {
            $errors['date_arrivee'] = "L'arrivée doit être postérieure au départ.";
        }

        $total = $this->toInt($input['places_total'] ?? '');
        $available = $this->toInt($input['places_disponibles'] ?? '');

        if ($total === null || $total < 1 || $total > self::MAX_SEATS) {
            $errors['places_total'] = sprintf('Le nombre total de places doit être compris entre 1 et %d.', self::MAX_SEATS);
        }

        if ($available === null) {
            $errors['places_disponibles'] = 'Le nombre de places disponibles est invalide.';
        } elseif ($total !== null && $available > $total) {
            $errors['places_disponibles'] = 'Les places disponibles ne peuvent pas dépasser le total.';
        }

        return $errors;
    }

    private function toInt(string $value): ?int
    {
        return preg_match('/\A\d{1,4}\z/', $value) === 1 ? (int) $value : null;
    }

    private function parseDate(string $value): ?DateTimeImmutable
    {
        $date = DateTimeImmutable::createFromFormat(self::INPUT_FORMAT, $value);

        return $date !== false && $date->format(self::INPUT_FORMAT) === $value ? $date : null;
    }
}
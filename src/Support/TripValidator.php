<?php

declare(strict_types=1);

namespace App\Support;

use DateTimeImmutable;

/** Enforces the same business rules for creation and modification. */
final class TripValidator
{
    /** @param array<string,mixed> $data
     * @return list<string>
     */
    public static function validate(array $data, DateTimeImmutable $now): array
    {
        $errors = [];
        $departure = filter_var($data['departure_agency_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $arrival = filter_var($data['arrival_agency_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($departure === false || $arrival === false) {
            $errors[] = 'Choisissez les deux agences.';
        } elseif ($departure === $arrival) {
            $errors[] = 'Les agences de départ et d’arrivée doivent être différentes.';
        }
        $start = self::date((string) ($data['departure_at'] ?? ''));
        $end = self::date((string) ($data['arrival_at'] ?? ''));
        if ($start === null || $end === null) {
            $errors[] = 'Renseignez des dates et heures valides.';
        } else {
            if ($start <= $now) {
                $errors[] = 'Le départ doit être dans le futur.';
            }
            if ($end <= $start) {
                $errors[] = 'L’arrivée doit suivre le départ.';
            }
        }
        $total = filter_var($data['total_seats'] ?? null, FILTER_VALIDATE_INT);
        $available = filter_var($data['available_seats'] ?? null, FILTER_VALIDATE_INT);
        if ($total === false || $total < 1 || $total > 50 || $available === false || $available < 0 || $available > $total) {
            $errors[] = 'Les places doivent être comprises entre 0 et le total (1 à 50).';
        }
        return $errors;
    }

    private static function date(string $value): ?DateTimeImmutable
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $value);
        return $date !== false && $date->format('Y-m-d\TH:i') === $value ? $date : null;
    }
}

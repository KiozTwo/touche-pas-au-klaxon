<?php

declare(strict_types=1);

use App\Support\TripValidator;
use PHPUnit\Framework\TestCase;

/** Covers invalid write requests before a repository can persist them. */
final class TripValidatorTest extends TestCase
{
    public function testRejectsInvalidAgenciesDatesAndSeatCounts(): void
    {
        $errors = TripValidator::validate([
            'departure_agency_id' => '1', 'arrival_agency_id' => '1',
            'departure_at' => '2026-01-01T10:00', 'arrival_at' => '2026-01-01T09:00',
            'total_seats' => '2', 'available_seats' => '3',
        ], new DateTimeImmutable('2026-01-02T00:00'));
        self::assertCount(4, $errors);
    }

    public function testAcceptsValidTrip(): void
    {
        self::assertSame([], TripValidator::validate([
            'departure_agency_id' => '1', 'arrival_agency_id' => '2',
            'departure_at' => '2026-01-03T10:00', 'arrival_at' => '2026-01-03T12:00',
            'total_seats' => '4', 'available_seats' => '3',
        ], new DateTimeImmutable('2026-01-02T00:00')));
    }
}

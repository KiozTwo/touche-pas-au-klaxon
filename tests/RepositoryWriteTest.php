<?php

declare(strict_types=1);

use App\Repositories\AgencyRepository;
use App\Repositories\TripRepository;
use App\Support\Database;
use PHPUnit\Framework\TestCase;

/** Real MySQL integration test; every write is rolled back after the test. */
final class RepositoryWriteTest extends TestCase
{
    public function testAgencyAndTripCreateUpdateDelete(): void
    {
        if (!getenv('TEST_DB_NAME')) {
            self::markTestSkipped('Set TEST_DB_NAME to a dedicated test database.');
        }
        putenv('DB_NAME=' . getenv('TEST_DB_NAME'));
        $db = Database::connect(dirname(__DIR__));
        $db->beginTransaction();
        try {
            $agencies = new AgencyRepository($db);
            $trips = new TripRepository($db);
            $agencies->create('Agence temporaire A');
            $firstId = (int) $db->lastInsertId();
            $agencies->create('Agence temporaire B');
            $secondId = (int) $db->lastInsertId();
            $agencies->update($firstId, 'Agence corrigée');
            self::assertSame('Agence corrigée', $agencies->find($firstId)['name']);
            $db->prepare('INSERT INTO users (last_name,first_name,phone,email,password_hash,role) VALUES (?,?,?,?,?,?)')
                ->execute(['Test', 'Auteur', '0102030405', 'integration-' . bin2hex(random_bytes(6)) . '@example.test', password_hash('secret', PASSWORD_DEFAULT), 'employee']);
            $authorId = (int) $db->lastInsertId();
            $start = (new DateTimeImmutable('+2 days'))->format('Y-m-d\TH:i');
            $end = (new DateTimeImmutable('+2 days +2 hours'))->format('Y-m-d\TH:i');
            $data = ['departure_agency_id' => $firstId, 'arrival_agency_id' => $secondId,
                'departure_at' => $start, 'arrival_at' => $end, 'total_seats' => 4, 'available_seats' => 3];
            $trips->create($data, $authorId);
            $tripId = (int) $db->lastInsertId();
            self::assertSame($authorId, (int) $trips->find($tripId)['author_id']);
            $data['available_seats'] = 2;
            $trips->update($tripId, $data);
            self::assertSame(2, (int) $trips->find($tripId)['available_seats']);
            $trips->delete($tripId);
            self::assertFalse($trips->find($tripId));
            $agencies->delete($firstId);
            $agencies->delete($secondId);
            self::assertFalse($agencies->find($firstId));
        } finally {
            $db->rollBack();
        }
    }
}

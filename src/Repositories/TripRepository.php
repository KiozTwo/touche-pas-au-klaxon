<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/** Read and write operations for trips; all variable SQL values are bound. */
final class TripRepository
{
    public function __construct(private PDO $db) {}

    /** @return list<array<string,mixed>> */
    public function all(bool $public = true): array
    {
        $where = $public ? 'WHERE t.departure_at > NOW() AND t.available_seats > 0' : '';
        $statement = $this->db->query("SELECT t.*, d.name AS departure_name, a.name AS arrival_name,
            u.first_name, u.last_name, u.phone, u.email
            FROM trips t JOIN agencies d ON d.id=t.departure_agency_id
            JOIN agencies a ON a.id=t.arrival_agency_id JOIN users u ON u.id=t.author_id
            {$where} ORDER BY t.departure_at ASC, t.id ASC");
        return $statement->fetchAll();
    }

    /** @return array<string,mixed>|false */
    public function find(int $id): array|false
    {
        $statement = $this->db->prepare('SELECT * FROM trips WHERE id = ?');
        $statement->execute([$id]);
        return $statement->fetch();
    }

    /** @param array<string,mixed> $data */
    public function create(array $data, int $authorId): void
    {
        $statement = $this->db->prepare('INSERT INTO trips (departure_agency_id,arrival_agency_id,departure_at,arrival_at,total_seats,available_seats,author_id) VALUES (?,?,?,?,?,?,?)');
        $statement->execute([$data['departure_agency_id'], $data['arrival_agency_id'], self::sqlDate($data['departure_at']), self::sqlDate($data['arrival_at']), $data['total_seats'], $data['available_seats'], $authorId]);
    }

    /** @param array<string,mixed> $data */
    public function update(int $id, array $data): void
    {
        $statement = $this->db->prepare('UPDATE trips SET departure_agency_id=?,arrival_agency_id=?,departure_at=?,arrival_at=?,total_seats=?,available_seats=? WHERE id=?');
        $statement->execute([$data['departure_agency_id'], $data['arrival_agency_id'], self::sqlDate($data['departure_at']), self::sqlDate($data['arrival_at']), $data['total_seats'], $data['available_seats'], $id]);
    }

    public function delete(int $id): void
    {
        $statement = $this->db->prepare('DELETE FROM trips WHERE id=?');
        $statement->execute([$id]);
    }

    private static function sqlDate(string $date): string
    {
        return str_replace('T', ' ', $date) . ':00';
    }
}

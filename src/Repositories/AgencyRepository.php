<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/** CRUD restricted to administrators by the controller. */
final class AgencyRepository
{
    public function __construct(private PDO $db) {}

    /** @return list<array<string,mixed>> */
    public function all(): array
    {
        return $this->db->query('SELECT * FROM agencies ORDER BY name')->fetchAll();
    }

    /** @return array<string,mixed>|false */
    public function find(int $id): array|false
    {
        $statement = $this->db->prepare('SELECT * FROM agencies WHERE id=?');
        $statement->execute([$id]);
        return $statement->fetch();
    }

    public function create(string $name): void
    {
        $statement = $this->db->prepare('INSERT INTO agencies (name) VALUES (?)');
        $statement->execute([$name]);
    }

    public function update(int $id, string $name): void
    {
        $statement = $this->db->prepare('UPDATE agencies SET name=? WHERE id=?');
        $statement->execute([$name, $id]);
    }

    public function delete(int $id): void
    {
        $statement = $this->db->prepare('DELETE FROM agencies WHERE id=?');
        $statement->execute([$id]);
    }

    public function exists(int $id): bool
    {
        return (bool) $this->find($id);
    }
}

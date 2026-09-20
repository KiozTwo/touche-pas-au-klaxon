<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/** Employee data is imported from HR; there are no web write methods. */
final class UserRepository
{
    public function __construct(private PDO $db) {}

    /** @return array<string,mixed>|false */
    public function byEmail(string $email): array|false
    {
        $statement = $this->db->prepare('SELECT * FROM users WHERE email=?');
        $statement->execute([$email]);
        return $statement->fetch();
    }

    /** @return array<string,mixed>|false */
    public function find(int $id): array|false
    {
        $statement = $this->db->prepare('SELECT id,first_name,last_name,email,phone,role FROM users WHERE id=?');
        $statement->execute([$id]);
        return $statement->fetch();
    }

    /** @return list<array<string,mixed>> */
    public function all(): array
    {
        return $this->db->query('SELECT id,first_name,last_name,email,phone,role FROM users ORDER BY last_name,first_name')->fetchAll();
    }
}

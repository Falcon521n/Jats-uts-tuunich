<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class AccountModel
{
    public function __construct(private PDO $pdo)
    {
    }

    public function createCustomerUser(array $user): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO users (name, last_name, email, password_hash, `role`)
             VALUES (:name, :last_name, :email, :password_hash, \'customer\')'
        );
        $statement->execute([
            'name' => $user['name'],
            'last_name' => $user['last_name'],
            'email' => $user['email'],
            'password_hash' => $user['password_hash'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function createAdminUser(string $name, string $email, string $passwordHash): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO users (name, email, password_hash, `role`, is_active)
             VALUES (:name, :email, :password_hash, \'admin\', 1)'
        );
        $statement->execute(['name' => $name, 'email' => $email, 'password_hash' => $passwordHash]);

        return (int) $this->pdo->lastInsertId();
    }
}

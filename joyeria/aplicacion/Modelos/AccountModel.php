<?php

declare(strict_types=1);

namespace Aplicacion\Modelos;

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

    public function adminExists(): bool
    {
        return (bool) $this->pdo->query(
            'SELECT 1 FROM users WHERE username = \'admin\' OR email = \'admin@local.test\' LIMIT 1'
        )->fetchColumn();
    }

    public function createAdminUser(string $username, string $name, string $email, string $passwordHash): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO users (username, name, email, password_hash, `role`, is_active)
             VALUES (:username, :name, :email, :password_hash, \'admin\', 1)'
        );
        $statement->execute([
            'username' => $username,
            'name' => $name,
            'email' => $email,
            'password_hash' => $passwordHash,
        ]);

        return (int) $this->pdo->lastInsertId();
    }
}

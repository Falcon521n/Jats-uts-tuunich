<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class UserModel
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findActiveByEmail(string $email): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, name, email, password_hash, `role` FROM users WHERE email = :email AND is_active = 1'
        );
        $statement->execute(['email' => $email]);

        return $statement->fetch() ?: null;
    }

    public function findStatus(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT name, `role`, is_active FROM users WHERE id = :id');
        $statement->execute(['id' => $id]);

        return $statement->fetch() ?: null;
    }

    public function updatePasswordHash(int $id, string $hash): void
    {
        $statement = $this->pdo->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
        $statement->execute(['hash' => $hash, 'id' => $id]);
    }

    public function create(array $user): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO users (name, last_name, email, password_hash, `role`, is_active)
             VALUES (:name, :last_name, :email, :password_hash, :role, :is_active)'
        );
        $statement->execute([
            'name' => $user['name'],
            'last_name' => $user['last_name'],
            'email' => $user['email'],
            'password_hash' => $user['password_hash'],
            'role' => $user['role'],
            'is_active' => $user['is_active'] ?? 1,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $user): void
    {
        $sql = 'UPDATE users SET name = :name, last_name = :last_name, email = :email,
                `role` = :role, is_active = :is_active';
        $parameters = [
            'name' => $user['name'],
            'last_name' => $user['last_name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'is_active' => $user['is_active'],
            'id' => $id,
        ];
        if (array_key_exists('password_hash', $user)) {
            $sql .= ', password_hash = :password_hash';
            $parameters['password_hash'] = $user['password_hash'];
        }
        $sql .= ' WHERE id = :id';
        $this->pdo->prepare($sql)->execute($parameters);
    }

    public function findForAdministration(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT u.id, u.name, u.last_name, u.email, u.`role` AS `role`, u.is_active,
                    u.created_at, COALESCE(c.phone, s.phone) AS phone, c.address,
                    COALESCE(cc.credit_limit, 0) AS credit_limit, COALESCE(cc.balance, 0) AS balance
             FROM users u
             LEFT JOIN customers c ON c.user_id = u.id
             LEFT JOIN sellers s ON s.user_id = u.id
             LEFT JOIN customer_credit cc ON cc.customer_id = c.id
             WHERE u.id = :id'
        );
        $statement->execute(['id' => $id]);

        return $statement->fetch() ?: null;
    }

    public function listForAdministration(int $limit = 300): array
    {
        $statement = $this->pdo->query(
            'SELECT u.id, u.name, u.last_name, u.email, u.`role` AS `role`, u.is_active,
                    u.created_at, COALESCE(c.phone, s.phone) AS phone,
                    COALESCE(cc.credit_limit, 0) AS credit_limit, COALESCE(cc.balance, 0) AS balance
             FROM users u
             LEFT JOIN customers c ON c.user_id = u.id
             LEFT JOIN sellers s ON s.user_id = u.id
             LEFT JOIN customer_credit cc ON cc.customer_id = c.id
             ORDER BY u.created_at DESC LIMIT ' . max(1, min($limit, 1000))
        );

        return $statement->fetchAll();
    }

    public function findCustomerRoleAndBalance(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT u.`role` AS `role`, COALESCE(cc.balance, 0) AS balance
             FROM users u
             LEFT JOIN customers c ON c.user_id = u.id
             LEFT JOIN customer_credit cc ON cc.customer_id = c.id
             WHERE u.id = :id'
        );
        $statement->execute(['id' => $id]);

        return $statement->fetch() ?: null;
    }
}

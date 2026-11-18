<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class CustomerModel
{
    public function __construct(private PDO $pdo)
    {
    }

    public function createForUser(int $userId, ?string $phone, ?string $address): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO customers (user_id, phone, address) VALUES (:user_id, :phone, :address)'
        );
        $statement->execute(['user_id' => $userId, 'phone' => $phone, 'address' => $address]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findIdByUserId(int $userId): ?int
    {
        $statement = $this->pdo->prepare('SELECT id FROM customers WHERE user_id = :user_id');
        $statement->execute(['user_id' => $userId]);
        $customerId = $statement->fetchColumn();

        return $customerId === false ? null : (int) $customerId;
    }

    public function lockActive(int $customerId): bool
    {
        $statement = $this->pdo->prepare(
            'SELECT c.id FROM customers c
             JOIN users u ON u.id = c.user_id
             WHERE c.id = :customer_id AND u.`role` = \'customer\' AND u.is_active = 1
             FOR UPDATE'
        );
        $statement->execute(['customer_id' => $customerId]);

        return $statement->fetchColumn() !== false;
    }

    public function profileForUser(int $userId): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT u.id AS user_id, c.id AS customer_id, u.name, u.last_name, u.email, c.phone, c.address
             FROM users u JOIN customers c ON c.user_id = u.id WHERE u.id = :user_id AND u.`role` = \'customer\''
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetch() ?: null;
    }

    public function updateProfile(int $userId, string $name, string $lastName, ?string $phone, ?string $address): void
    {
        $this->pdo->beginTransaction();
        try {
            $userStatement = $this->pdo->prepare(
                'UPDATE users SET name = :name, last_name = :last_name
                 WHERE id = :user_id AND `role` = \'customer\''
            );
            $userStatement->execute(['name' => $name, 'last_name' => $lastName, 'user_id' => $userId]);
            $this->updateForUser($userId, $phone, $address);
            $this->pdo->commit();
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function updateForUser(int $userId, ?string $phone, ?string $address): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE customers SET phone = :phone, address = :address WHERE user_id = :user_id'
        );
        $statement->execute(['phone' => $phone, 'address' => $address, 'user_id' => $userId]);
    }

    public function hasRelationships(int $customerId): bool
    {
        $statement = $this->pdo->prepare(
            'SELECT
                EXISTS(SELECT 1 FROM sales WHERE customer_id = :sales_customer_id)
                OR EXISTS(SELECT 1 FROM carts WHERE customer_id = :carts_customer_id)
                OR EXISTS(SELECT 1 FROM credit_payments WHERE customer_id = :payments_customer_id) AS has_data'
        );
        $statement->execute([
            'sales_customer_id' => $customerId,
            'carts_customer_id' => $customerId,
            'payments_customer_id' => $customerId,
        ]);

        return (bool) $statement->fetchColumn();
    }

    public function deleteByUserId(int $userId): void
    {
        $statement = $this->pdo->prepare('DELETE FROM customers WHERE user_id = :user_id');
        $statement->execute(['user_id' => $userId]);
    }

    public function activeCustomers(): array
    {
        return $this->pdo->query(
            "SELECT c.id, u.name, u.last_name, u.email FROM customers c
             JOIN users u ON u.id = c.user_id
             WHERE u.`role` = 'customer' AND u.is_active = 1
             ORDER BY u.name"
        )->fetchAll();
    }
}

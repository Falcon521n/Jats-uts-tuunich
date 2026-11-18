<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class SellerModel
{
    public function __construct(private PDO $pdo)
    {
    }

    public function createForUser(int $userId, ?string $phone): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO sellers (user_id, phone, hired_at, status)
             VALUES (:user_id, :phone, CURRENT_DATE, \'active\')'
        );
        $statement->execute(['user_id' => $userId, 'phone' => $phone]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findIdByUserId(int $userId): ?int
    {
        $statement = $this->pdo->prepare(
            'SELECT s.id FROM sellers s JOIN users u ON u.id = s.user_id
             WHERE s.user_id = :user_id AND s.status = \'active\' AND u.is_active = 1'
        );
        $statement->execute(['user_id' => $userId]);
        $sellerId = $statement->fetchColumn();

        return $sellerId === false ? null : (int) $sellerId;
    }

    public function lockActive(int $sellerId): bool
    {
        $statement = $this->pdo->prepare(
            'SELECT s.id FROM sellers s JOIN users u ON u.id = s.user_id
             WHERE s.id = :seller_id AND s.status = \'active\' AND u.is_active = 1
             FOR UPDATE'
        );
        $statement->execute(['seller_id' => $sellerId]);

        return $statement->fetchColumn() !== false;
    }

    public function updateForUser(int $userId, ?string $phone, string $status): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE sellers SET phone = :phone, status = :status WHERE user_id = :user_id'
        );
        $statement->execute(['phone' => $phone, 'status' => $status, 'user_id' => $userId]);
    }

    public function deleteByUserId(int $userId): void
    {
        $statement = $this->pdo->prepare('DELETE FROM sellers WHERE user_id = :user_id');
        $statement->execute(['user_id' => $userId]);
    }
}

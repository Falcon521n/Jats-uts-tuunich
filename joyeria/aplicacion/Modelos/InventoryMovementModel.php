<?php

declare(strict_types=1);

namespace Aplicacion\Modelos;

use PDO;

final class InventoryMovementModel
{
    public function __construct(private PDO $pdo)
    {
    }

    public function record(int $productId, ?int $userId, int $quantityChange, string $reason, string $note): void
    {
        if ($quantityChange === 0) {
            return;
        }
        $statement = $this->pdo->prepare(
            'INSERT INTO inventory_movements (product_id, user_id, quantity_change, reason, note)
             VALUES (:product_id, :user_id, :quantity_change, :reason, :note)'
        );
        $statement->execute([
            'product_id' => $productId,
            'user_id' => $userId,
            'quantity_change' => $quantityChange,
            'reason' => $reason,
            'note' => $note,
        ]);
    }
}

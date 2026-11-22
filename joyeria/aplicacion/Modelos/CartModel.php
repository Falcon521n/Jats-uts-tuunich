<?php

declare(strict_types=1);

namespace Aplicacion\Modelos;

use PDO;

final class CartModel
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findOrCreateActive(int $customerId): int
    {
        $ownsTransaction = !$this->pdo->inTransaction();
        if ($ownsTransaction) {
            $this->pdo->beginTransaction();
        }
        try {
            $customerLock = $this->pdo->prepare('SELECT id FROM customers WHERE id = :id FOR UPDATE');
            $customerLock->execute(['id' => $customerId]);
            if (!$customerLock->fetch()) {
                throw new \DomainException('No se encontró la cuenta de cliente.');
            }
            $statement = $this->pdo->prepare(
                'SELECT id FROM carts WHERE customer_id = :customer_id AND state = \'active\'
                 ORDER BY id DESC LIMIT 1'
            );
            $statement->execute(['customer_id' => $customerId]);
            $cartId = $statement->fetchColumn();
            if ($cartId === false) {
                $this->pdo->prepare('INSERT INTO carts (customer_id) VALUES (:customer_id)')
                    ->execute(['customer_id' => $customerId]);
                $cartId = $this->pdo->lastInsertId();
            }
            if ($ownsTransaction) {
                $this->pdo->commit();
            }

            return (int) $cartId;
        } catch (\Throwable $exception) {
            if ($ownsTransaction && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function isOwnedByCustomer(int $cartId, int $customerId): bool
    {
        $statement = $this->pdo->prepare(
            'SELECT id FROM carts WHERE id = :id AND customer_id = :customer_id AND state = \'active\''
        );
        $statement->execute(['id' => $cartId, 'customer_id' => $customerId]);

        return $statement->fetchColumn() !== false;
    }

    public function lockOwnedActive(int $cartId, int $customerId): bool
    {
        $statement = $this->pdo->prepare(
            'SELECT id FROM carts
             WHERE id = :id AND customer_id = :customer_id AND state = \'active\' FOR UPDATE'
        );
        $statement->execute(['id' => $cartId, 'customer_id' => $customerId]);

        return $statement->fetchColumn() !== false;
    }

    public function items(int $cartId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT ci.product_id, ci.quantity FROM cart_items ci WHERE ci.cart_id = :cart_id'
        );
        $statement->execute(['cart_id' => $cartId]);

        return $statement->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function add(int $cartId, int $productId, int $quantity): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (:cart_id, :product_id, :quantity)
             ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)'
        );
        $statement->execute(['cart_id' => $cartId, 'product_id' => $productId, 'quantity' => $quantity]);
    }

    public function updateQuantity(int $cartId, int $productId, int $quantity): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE cart_items SET quantity = :quantity WHERE cart_id = :cart_id AND product_id = :product_id'
        );
        $statement->execute(['quantity' => $quantity, 'cart_id' => $cartId, 'product_id' => $productId]);
    }

    public function remove(int $cartId, int $productId): void
    {
        $statement = $this->pdo->prepare(
            'DELETE FROM cart_items WHERE cart_id = :cart_id AND product_id = :product_id'
        );
        $statement->execute(['cart_id' => $cartId, 'product_id' => $productId]);
    }

    public function finalize(int $cartId): void
    {
        $statement = $this->pdo->prepare('UPDATE carts SET state = \'finalized\' WHERE id = :id');
        $statement->execute(['id' => $cartId]);
    }

    public function clear(int $cartId): void
    {
        $statement = $this->pdo->prepare('DELETE FROM cart_items WHERE cart_id = :cart_id');
        $statement->execute(['cart_id' => $cartId]);
    }
}

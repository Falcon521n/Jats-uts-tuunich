<?php

declare(strict_types=1);

namespace Aplicacion\Modelos;

use PDO;

final class CreditModel
{
    public function __construct(private PDO $pdo)
    {
    }

    public function createForCustomer(int $customerId): void
    {
        $this->pdo->prepare('INSERT INTO customer_credit (customer_id) VALUES (:customer_id)')
            ->execute(['customer_id' => $customerId]);
    }

    public function forCustomer(int $customerId, bool $lock = false): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, customer_id, credit_limit, balance, state
             FROM customer_credit WHERE customer_id = :customer_id' . ($lock ? ' FOR UPDATE' : '')
        );
        $statement->execute(['customer_id' => $customerId]);

        return $statement->fetch() ?: null;
    }

    public function assignLimit(int $customerId, string $creditLimit): void
    {
        $this->pdo->prepare(
            'INSERT INTO customer_credit (customer_id, credit_limit)
             VALUES (:customer_id, :credit_limit)
             ON DUPLICATE KEY UPDATE
             credit_limit = VALUES(credit_limit),
             state = IF(state = \'suspended\', state, \'active\')'
        )->execute(['customer_id' => $customerId, 'credit_limit' => $creditLimit]);
    }

    public function increaseBalance(int $customerId, string $amount): void
    {
        $this->pdo->prepare(
            'UPDATE customer_credit SET balance = balance + :amount, state = \'active\'
             WHERE customer_id = :customer_id'
        )->execute(['amount' => $amount, 'customer_id' => $customerId]);
    }

    public function applyPayment(int $customerId, string $amount, int $receivedBy, ?string $note): void
    {
        $this->pdo->prepare(
            'UPDATE customer_credit
             SET state = IF(balance <= :settlement_amount, \'settled\', state),
                 balance = balance - :deduct_amount
             WHERE customer_id = :customer_id'
        )->execute([
            'settlement_amount' => $amount,
            'deduct_amount' => $amount,
            'customer_id' => $customerId,
        ]);
        $statement = $this->pdo->prepare(
            'INSERT INTO credit_payments (customer_id, received_by, amount, note)
             VALUES (:customer_id, :received_by, :amount, :note)'
        );
        $statement->execute([
            'customer_id' => $customerId,
            'received_by' => $receivedBy,
            'amount' => $amount,
            'note' => $note,
        ]);
    }
}

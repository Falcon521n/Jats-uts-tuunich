<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class SaleModel
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(int $customerId, int $paymentMethodId, string $total): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO sales (customer_id, payment_method_id, status, total)
             VALUES (:customer_id, :payment_method_id, \'completed\', :total)'
        );
        $statement->execute([
            'customer_id' => $customerId,
            'payment_method_id' => $paymentMethodId,
            'total' => $total,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function addItem(int $saleId, array $item): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO sale_items (sale_id, product_id, product_name, unit_price, quantity, subtotal)
             VALUES (:sale_id, :product_id, :product_name, :unit_price, :quantity, :subtotal)'
        );
        $statement->execute([
            'sale_id' => $saleId,
            'product_id' => $item['id'],
            'product_name' => $item['name'],
            'unit_price' => $item['unit_price'],
            'quantity' => $item['quantity'],
            'subtotal' => $item['subtotal'],
        ]);
    }

    public function customerHistory(int $customerId, string $period): array
    {
        $sql = 'SELECT s.id, s.payment_method_id, pm.name AS payment_method, s.total, s.created_at,
                       GROUP_CONCAT(CONCAT(si.product_name, \' × \', si.quantity) ORDER BY si.id SEPARATOR \', \') AS items
                FROM sales s
                JOIN payment_methods pm ON pm.id = s.payment_method_id
                LEFT JOIN sale_items si ON si.sale_id = s.id
                WHERE s.customer_id = :customer_id AND s.status = \'completed\'';
        if ($period !== 'all') {
            $sql .= ' AND s.created_at >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL ' . (int) $period . ' DAY)';
        }
        $sql .= '         GROUP BY s.id, pm.name ORDER BY s.created_at DESC LIMIT 50';
        $statement = $this->pdo->prepare($sql);
        $statement->execute(['customer_id' => $customerId]);

        return $statement->fetchAll();
    }

    public function dailyDetails(string $start, string $end): array
    {
        $statement = $this->pdo->prepare(
            'SELECT s.id, s.total, pm.name AS payment_method, s.created_at, u.name AS customer_name,
                    GROUP_CONCAT(CONCAT(si.product_name, \' × \', si.quantity) ORDER BY si.id SEPARATOR \', \') AS items
             FROM sales s
             LEFT JOIN customers c ON c.id = s.customer_id
             LEFT JOIN users u ON u.id = c.user_id
             JOIN payment_methods pm ON pm.id = s.payment_method_id
             LEFT JOIN sale_items si ON si.sale_id = s.id
             WHERE s.status = \'completed\' AND s.created_at >= :start AND s.created_at < :end
             GROUP BY s.id, u.name, pm.name ORDER BY s.created_at DESC'
        );
        $statement->execute(['start' => $start, 'end' => $end]);

        return $statement->fetchAll();
    }

    public function monthlyByDay(string $start, string $end): array
    {
        $statement = $this->pdo->prepare(
            'SELECT DATE(created_at) AS sale_day, COUNT(*) AS sale_count, SUM(total) AS total
             FROM sales WHERE status = \'completed\' AND created_at >= :start AND created_at < :end
             GROUP BY DATE(created_at) ORDER BY sale_day'
        );
        $statement->execute(['start' => $start, 'end' => $end]);

        return $statement->fetchAll();
    }

    public function summary(string $start, string $end): array
    {
        $statement = $this->pdo->prepare(
            'SELECT COUNT(*) AS sale_count, COALESCE(SUM(total), 0) AS total
             FROM sales WHERE status = \'completed\' AND created_at >= :start AND created_at < :end'
        );
        $statement->execute(['start' => $start, 'end' => $end]);

        return $statement->fetch();
    }
}

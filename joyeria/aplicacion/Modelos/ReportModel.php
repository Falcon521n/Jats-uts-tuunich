<?php

declare(strict_types=1);

namespace Aplicacion\Modelos;

use PDO;

final class ReportModel
{
    public function __construct(private PDO $pdo)
    {
    }

    public function dailySummary(string $start, string $end): array
    {
        return $this->summary($start, $end);
    }

    public function monthlySummary(string $start, string $end): array
    {
        return $this->summary($start, $end);
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

    private function summary(string $start, string $end): array
    {
        $statement = $this->pdo->prepare(
            'SELECT COUNT(*) AS sale_count, COALESCE(SUM(total), 0) AS total
             FROM sales WHERE status = \'completed\' AND created_at >= :start AND created_at < :end'
        );
        $statement->execute(['start' => $start, 'end' => $end]);

        return $statement->fetch();
    }
}

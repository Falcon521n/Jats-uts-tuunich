<?php

declare(strict_types=1);

namespace Aplicacion\Modelos;

use PDO;

final class PaymentMethodModel
{
    public function __construct(private PDO $pdo)
    {
    }

    public function idFor(string $name): ?int
    {
        $statement = $this->pdo->prepare(
            'SELECT id FROM payment_methods WHERE name = :name AND is_active = 1'
        );
        $statement->execute(['name' => $name]);
        $id = $statement->fetchColumn();

        return $id === false ? null : (int) $id;
    }
}

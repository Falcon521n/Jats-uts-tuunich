<?php

declare(strict_types=1);

namespace Aplicacion\Modelos;

use PDO;

final class CategoryModel
{
    public function __construct(private PDO $pdo)
    {
    }

    public function all(): array
    {
        return $this->pdo->query('SELECT id, name, description FROM categories ORDER BY name')->fetchAll();
    }

    public function findIdByName(string $name): ?int
    {
        $statement = $this->pdo->prepare('SELECT id FROM categories WHERE name = :name');
        $statement->execute(['name' => $name]);
        $id = $statement->fetchColumn();

        return $id === false ? null : (int) $id;
    }
}
